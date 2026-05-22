<?php

/**
 * @descripcion  Servicio que encapsula el protocolo HTTP con SAM (5 pasos: captcha, validar captcha,
 *              login, extraer token, obtener perfil). Usa Guzzle con CookieJar para persistir JSESSIONID.
 *              Las cookies SAM se guardan en cache (no session) para no depender de session middleware.
 *
 * @autor        Ghael Garcia Manjarrez <ghael.engineer@gmail.com>
 *
 * @autorizador  Ruben Alejandro Nolasco Ruiz <correo@dominio.com>
 *
 * @prueba       Ghael Garcia Manjarrez <ghael.engineer@gmail.com>
 *
 * @mantenimiento Ghael Garcia Manjarrez <ghael.engineer@gmail.com>
 *
 * @version      1.1.0
 *
 * @creado       2026-05-17
 *
 * @modificado   2026-05-21
 *
 * @cambios      2026-05-21 - Refactor: cookies SAM en cache con UUID, sesión vía cookie propia
 */

declare(strict_types=1);

namespace App\Services\Auth;

use GuzzleHttp\Client;
use GuzzleHttp\Cookie\CookieJar;
use GuzzleHttp\Cookie\SetCookie;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class SamService
{
    private Client $client;

    private CookieJar $cookieJar;

    private const CACHE_PREFIX = 'sam_cookies_';

    private const COOKIE_NAME = 'sam_session';

    private const CACHE_TTL = 600;

    public function __construct(
        private readonly Request $request
    ) {
        $this->cookieJar = new CookieJar;
        $this->client = new Client([
            'base_uri' => rtrim((string) env('SAM_URL', 'http://192.168.1.74:8090/SAM'), '/').'/',
            'timeout' => 30,
            'connect_timeout' => 10,
            'cookies' => $this->cookieJar,
            'http_errors' => false,
            'verify' => false,
        ]);

        $this->restoreFromCache();
    }

    /**
     * Obtiene el nombre de la cookie de sesión SAM.
     */
    public function getSessionCookieName(): string
    {
        return self::COOKIE_NAME;
    }

    /**
     * Obtiene el ID de sesión SAM desde la cookie.
     */
    public function getSessionId(): ?string
    {
        return $this->request->cookie(self::COOKIE_NAME);
    }

    /**
     * Obtiene el captcha PNG desde SAM.
     *
     * @return array{png: string|null, sessionId: string|null}
     */
    public function obtenerCaptcha(): array
    {
        try {
            $response = $this->client->get('app/login/captcha.png');

            if ($response->getStatusCode() !== 200) {
                return ['png' => null, 'sessionId' => null];
            }

            $sessionId = $this->saveToCache();

            return [
                'png' => (string) $response->getBody(),
                'sessionId' => $sessionId,
            ];
        } catch (\Throwable) {
            return ['png' => null, 'sessionId' => null];
        }
    }

    /**
     * Valida el código captcha contra SAM.
     */
    public function validarCaptcha(string $codigo): bool
    {
        try {
            $response = $this->client->post('app/login/validarCaptcha.do', [
                'form_params' => ['inpCaptcha' => $codigo],
            ]);
            $this->saveToCache();

            $body = trim((string) $response->getBody());
            $decoded = json_decode($body, true);

            if (is_string($decoded)) {
                return $decoded === 'si';
            }

            return $body === 'si';
        } catch (\Throwable) {
            return false;
        }
    }

    /**
     * Ejecuta login contra SAM.
     *
     * @return array{success: bool, rol?: string, token?: string|null, sistemaUrl?: string|null, error?: string|null}
     */
    public function login(string $usuario, string $password, string $captcha): array
    {
        try {
            $response = $this->client->post('app/empleado.do?accion=verificar', [
                'form_params' => [
                    'itt_username' => $usuario,
                    'itt_password' => $password,
                    'inpCaptcha' => $captcha,
                ],
            ]);
            $this->saveToCache();

            $body = (string) $response->getBody();

            if (str_contains($body, 'login.css')) {
                return ['success' => false, 'error' => 'Credenciales inválidas'];
            }

            if (str_contains($body, '<title>MASTER</title>')) {
                return ['success' => true, 'rol' => 'master', 'token' => null, 'sistemaUrl' => null, 'error' => null];
            }

            if (str_contains($body, '<title>Enlaces</title>') || str_contains($body, 'recuadros.css')) {
                $tokenData = $this->extraerToken($body);
                if ($tokenData === null) {
                    return ['success' => false, 'error' => 'Token SAM no encontrado en la respuesta'];
                }

                return [
                    'success' => true,
                    'rol' => 'empleado',
                    'token' => $tokenData['token'],
                    'sistemaUrl' => $tokenData['sistemaUrl'],
                    'error' => null,
                ];
            }

            return ['success' => false, 'error' => 'Respuesta SAM inesperada'];
        } catch (\Throwable) {
            return ['success' => false, 'error' => 'Servicio SAM no disponible'];
        }
    }

    /**
     * Obtiene el perfil del usuario desde SAM mediante token.
     *
     * @return array<string, mixed>|null
     */
    public function obtenerPerfil(string $token, string $sistemaUrl): ?array
    {
        try {
            $response = $this->client->post('app/obtenerDatosMaster.do', [
                'form_params' => [
                    'token' => $token,
                    'sistema' => $sistemaUrl,
                ],
            ]);

            if ($response->getStatusCode() !== 200) {
                return null;
            }

            $json = json_decode((string) $response->getBody(), true);

            if (! is_array($json) || ! isset($json['responseObject'])) {
                return null;
            }

            return $json['responseObject'];
        } catch (\Throwable) {
            return null;
        }
    }

    /**
     * Cierra sesión en SAM y limpia la caché de cookies.
     */
    public function logout(): void
    {
        try {
            $this->client->get('app/login.do?accion=salir');
        } catch (\Throwable) {
            // Ignorar errores en logout SAM
        }

        $this->clearCache();
    }

    private function restoreFromCache(): void
    {
        $sessionId = $this->getSessionId();
        if ($sessionId === null) {
            return;
        }

        $cookies = Cache::get(self::CACHE_PREFIX.$sessionId, []);
        foreach ($cookies as $c) {
            $this->cookieJar->setCookie(new SetCookie([
                'Name' => $c['name'],
                'Value' => $c['value'],
                'Domain' => $c['domain'],
                'Path' => $c['path'],
            ]));
        }
    }

    private function saveToCache(): string
    {
        $sessionId = $this->getSessionId() ?? (string) Str::uuid();
        $cookies = [];
        foreach ($this->cookieJar as $cookie) {
            $cookies[] = [
                'name' => $cookie->getName(),
                'value' => $cookie->getValue(),
                'domain' => $cookie->getDomain(),
                'path' => $cookie->getPath(),
            ];
        }
        Cache::put(self::CACHE_PREFIX.$sessionId, $cookies, now()->addSeconds(self::CACHE_TTL));

        return $sessionId;
    }

    private function clearCache(): void
    {
        $sessionId = $this->getSessionId();
        if ($sessionId !== null) {
            Cache::forget(self::CACHE_PREFIX.$sessionId);
        }
    }

    private function extraerToken(string $html): ?array
    {
        $pattern = '/href="([^"]+\?token=([a-fA-F0-9\-]{36}))"/';
        if (! preg_match($pattern, $html, $matches)) {
            return null;
        }

        return [
            'token' => $matches[2],
            'sistemaUrl' => explode('?token=', $matches[1])[0],
        ];
    }
}
