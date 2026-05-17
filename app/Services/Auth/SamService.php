<?php

/**
 * @descripcion  Servicio que encapsula el protocolo HTTP con SAM (5 pasos: captcha, validar captcha,
 *              login, extraer token, obtener perfil). Usa Guzzle con CookieJar para persistir JSESSIONID.
 *
 * @autor        Ghael Garcia Manjarrez <ghael.engineer@gmail.com>
 *
 * @autorizador  Ruben Alejandro Nolasco Ruiz <correo@dominio.com>
 *
 * @prueba       Ghael Garcia Manjarrez <ghael.engineer@gmail.com>
 *
 * @mantenimiento Ghael Garcia Manjarrez <ghael.engineer@gmail.com>
 *
 * @version      1.0.0
 *
 * @creado       2026-05-17
 *
 * @modificado   2026-05-17
 *
 * @cambios      2026-05-17 - Creación inicial del servicio SAM
 */

declare(strict_types=1);

namespace App\Services\Auth;

use GuzzleHttp\Client;
use GuzzleHttp\Cookie\CookieJar;
use GuzzleHttp\Cookie\SetCookie;

class SamService
{
    private Client $client;

    private CookieJar $cookieJar;

    public function __construct()
    {
        $this->cookieJar = new CookieJar;
        $this->client = new Client([
            'base_uri' => rtrim((string) env('SAM_URL', 'http://192.168.1.74:8090/SAM'), '/'),
            'timeout' => 30,
            'connect_timeout' => 10,
            'cookies' => $this->cookieJar,
            'http_errors' => false,
            'verify' => false,
        ]);
    }

    public function obtenerCaptcha(): ?string
    {
        try {
            $response = $this->client->get('/app/login/captcha.png');
            $this->guardarCookies();

            if ($response->getStatusCode() !== 200) {
                return null;
            }

            return (string) $response->getBody();
        } catch (\Throwable) {
            return null;
        }
    }

    public function validarCaptcha(string $codigo): bool
    {
        try {
            $this->restaurarCookies();
            $response = $this->client->post('/app/login/validarCaptcha.do', [
                'form_params' => ['inpCaptcha' => $codigo],
            ]);
            $this->guardarCookies();

            $body = trim((string) $response->getBody());

            return $body === 'si';
        } catch (\Throwable) {
            return false;
        }
    }

    public function login(string $usuario, string $password, string $captcha): array
    {
        try {
            $this->restaurarCookies();
            $response = $this->client->post('/app/empleado.do?accion=verificar', [
                'form_params' => [
                    'itt_username' => $usuario,
                    'itt_password' => $password,
                    'inpCaptcha' => $captcha,
                ],
            ]);
            $this->guardarCookies();

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

    public function obtenerPerfil(string $token, string $sistemaUrl): ?array
    {
        try {
            $response = $this->client->post('/app/obtenerDatosMaster.do', [
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

    public function logout(): void
    {
        try {
            $this->client->get('/app/login.do?accion=salir');
        } catch (\Throwable) {
            // Ignorar errores en logout SAM
        }

        session()->forget('sam_cookies');
    }

    private function guardarCookies(): void
    {
        $cookies = [];
        foreach ($this->cookieJar as $cookie) {
            $cookies[] = [
                'name' => $cookie->getName(),
                'value' => $cookie->getValue(),
                'domain' => $cookie->getDomain(),
                'path' => $cookie->getPath(),
            ];
        }
        session(['sam_cookies' => $cookies]);
    }

    private function restaurarCookies(): void
    {
        $cookies = session('sam_cookies', []);
        foreach ($cookies as $c) {
            $this->cookieJar->setCookie(new SetCookie([
                'Name' => $c['name'],
                'Value' => $c['value'],
                'Domain' => $c['domain'],
                'Path' => $c['path'],
            ]));
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
