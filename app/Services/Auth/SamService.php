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
 * @version      1.6.2
 *
 * @creado       2026-05-17
 *
 * @modificado   2026-05-26
 *
 * @cambios      2026-05-21 - Refactor: cookies SAM en cache con UUID, sesión vía cookie propia
 *               2026-05-22 - Extracción de token robusta multicapa con logs de diagnóstico y captura de HTML crudo ante errores.
 *               2026-05-22 - Reescritura robusta de obtenerPerfil() con fallbacks secuenciales, parsing multinivel de JSON y logging quirúrgico.
 *               2026-05-24 - Timeout estricto vía config (5s/3s) + reintentos con requestWithRetry y logs en canal sam.
 *               2026-05-24 - Corrección: soporte de RequestException en retry y timeouts estandarizados en solicitudes.
 *               2026-05-24 - Forzar IPv4 para Windows, resolver localhost a 127.0.0.1, requestWithRetry mejorado y logs de error.
 *               2026-05-24 - Corrección de deadlock en obtenerPerfil al redireccionar consultas locales al servidor SAM correcto y forzar IPv4.
 *               2026-05-25 - Renombrado canal de log sam.debug a sam_debug.
 *               2026-05-26 - Propagar excepciones de red/timeout en logout para alineación con RF-02.
 */

declare(strict_types=1);

namespace App\Services\Auth;

use GuzzleHttp\Client;
use GuzzleHttp\Cookie\CookieJar;
use GuzzleHttp\Cookie\SetCookie;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Psr\Http\Message\ResponseInterface;

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
            'base_uri' => str_replace('localhost', '127.0.0.1', config('sam.url')),
            'timeout' => config('sam.timeout', 5.0),
            'connect_timeout' => config('sam.connect_timeout', 3.0),
            'force_ip_resolve' => config('sam.force_ip_resolve', 'v4'),
            'cookies' => $this->cookieJar,
            'http_errors' => false,
            'verify' => config('sam.verify_ssl', false),
            'headers' => [
                'bypass-tunnel-reminder' => 'true',
                'User-Agent' => 'GAMA-Client/1.0',
            ],
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
     * Verifica la conectividad con el servidor SAM externo.
     */
    public function checkConnection(): bool
    {
        try {
            $response = $this->requestWithRetry('GET', 'app/login/captcha.png', [
                'timeout' => config('sam.timeout', 5.0),
                'connect_timeout' => config('sam.connect_timeout', 3.0),
            ]);

            return $response->getStatusCode() === 200;
        } catch (\Throwable $e) {
            Log::channel('sam')->error('[SAM] Conectividad fallida con el servidor SAM: '.$e->getMessage());

            return false;
        }
    }

    /**
     * Obtiene el captcha PNG desde SAM.
     *
     * @return array{png: string|null, sessionId: string|null}
     */
    public function obtenerCaptcha(): array
    {
        try {
            $response = $this->requestWithRetry('GET', 'app/login/captcha.png');

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
            $response = $this->requestWithRetry('POST', 'app/login/validarCaptcha.do', [
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
     * @return array{success: bool, rol?: string, token?: string|null, sistemaUrl?: string|null, sessionId?: string|null, error?: string|null}
     */
    public function login(string $usuario, string $password, string $captcha): array
    {
        try {
            $response = $this->requestWithRetry('POST', 'app/empleado.do?accion=verificar', [
                'form_params' => [
                    'itt_username' => $usuario,
                    'itt_password' => $password,
                    'inpCaptcha' => $captcha,
                ],
            ]);
            $this->saveToCache();

            $body = (string) $response->getBody();

            // Guardar copia del HTML si es ambiente local
            $rawLogPath = null;
            if (app()->isLocal()) {
                $timestamp = now()->format('Ymd_His');
                $rawLogPath = storage_path("logs/sam_raw_response_{$timestamp}.html");
                @file_put_contents($rawLogPath, $body);
            }

            // Loguear respuesta cruda
            Log::channel('sam_debug')->debug('[SAM] Respuesta recibida de empleado.do?accion=verificar', [
                'url' => $this->client->getConfig('base_uri').'app/empleado.do?accion=verificar',
                'status_code' => $response->getStatusCode(),
                'html_length' => strlen($body),
                'html_file' => $rawLogPath,
                'snippet' => strlen($body) < 5120 ? $body : substr($body, 0, 3000),
            ]);

            // Detección preliminar de errores conocidos en HTML
            $errorDetectado = null;
            if (str_contains($body, 'Usuario no encontrado')) {
                $errorDetectado = 'Usuario no encontrado en SAM';
            } elseif (str_contains($body, 'Clave incorrecta') || str_contains($body, 'Contraseña incorrecta')) {
                $errorDetectado = 'Clave incorrecta';
            } elseif (str_contains($body, 'Sesión expirada') || str_contains($body, 'sesión ha expirado')) {
                $errorDetectado = 'Sesión expirada en SAM';
            } elseif (str_contains($body, 'No tiene permisos') || str_contains($body, 'Acceso denegado')) {
                $errorDetectado = 'No tiene permisos en SAM';
            }

            if ($errorDetectado) {
                Log::channel('sam_debug')->warning('[SAM] Error detectado en HTML: '.$errorDetectado);
            }

            if (str_contains($body, 'login.css')) {
                return ['success' => false, 'error' => $errorDetectado ?? 'Credenciales inválidas'];
            }

            if (str_contains($body, '<title>MASTER</title>')) {
                $sessionId = $this->saveToCache(); // update cache after login

                return ['success' => true, 'rol' => 'master', 'token' => null, 'sistemaUrl' => null, 'sessionId' => $sessionId, 'error' => null];
            }

            if (str_contains($body, '<title>Enlaces</title>') || str_contains($body, 'recuadros.css')) {
                $tokenData = $this->extraerToken($body);
                if ($tokenData === null) {
                    $errorMsg = $errorDetectado ?? 'Token SAM no encontrado en la respuesta';
                    Log::channel('sam_debug')->error('[SAM] Error de autenticación docente: '.$errorMsg);

                    return ['success' => false, 'error' => $errorMsg];
                }

                $sessionId = $this->saveToCache(); // update cache after login

                return [
                    'success' => true,
                    'rol' => 'empleado',
                    'token' => $tokenData['token'],
                    'sistemaUrl' => $tokenData['sistemaUrl'],
                    'sessionId' => $sessionId,
                    'error' => null,
                ];
            }

            return ['success' => false, 'error' => $errorDetectado ?? 'Respuesta SAM inesperada'];
        } catch (\Throwable $e) {
            $errorMsg = $e instanceof \RuntimeException ? $e->getMessage() : 'Servicio SAM no disponible';
            Log::channel('sam_debug')->error('[SAM] Excepción en login: '.$e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);

            return ['success' => false, 'error' => $errorMsg];
        }
    }

    /**
     * Obtiene el perfil del usuario desde SAM mediante token de forma robusta y con fallbacks secuenciales.
     *
     * @throws \RuntimeException|\Throwable
     */
    public function obtenerPerfil(string $tokenSam, string $sistemaUrl): ?array
    {
        $logContext = [
            'token_prefix' => substr($tokenSam, 0, 8).'...',
            'sistemaUrl' => $sistemaUrl,
        ];
        Log::channel('sam')->info('[SAM-PERFIL] Iniciando obtención de perfil', $logContext);
        Log::channel('sam_debug')->info('[SAM-PERFIL] Iniciando obtención de perfil', $logContext);

        try {
            // 1. Construcción segura de URL
            $parsedUrl = parse_url($sistemaUrl);
            $scheme = $parsedUrl['scheme'] ?? 'http';
            $host = $parsedUrl['host'] ?? 'localhost';
            $port = isset($parsedUrl['port']) ? ':'.$parsedUrl['port'] : '';
            $path = rtrim($parsedUrl['path'] ?? '', '/');

            // Evitar deadlock en desarrollo local si apunta a la app local
            $samParsed = parse_url(config('sam.url'));
            $samHost = $samParsed['host'] ?? '127.0.0.1';
            if ($samHost === 'localhost') {
                $samHost = '127.0.0.1';
            }
            $samPort = isset($samParsed['port']) ? ':'.$samParsed['port'] : '';
            $samScheme = $samParsed['scheme'] ?? 'http';

            if (($host === 'localhost' || $host === '127.0.0.1') && $port === ':8000') {
                $host = $samHost;
                $port = $samPort;
                $scheme = $samScheme;
                $samPath = rtrim($samParsed['path'] ?? '', '/');
                $basePath = $samPath.'/app';
            } else {
                if (preg_match('#^(.*?/app)#i', $path, $matches)) {
                    $basePath = $matches[1];
                } else {
                    $basePath = preg_replace('#/[^/]+\.do$#i', '', $path);
                    $basePath = rtrim($basePath, '/').'/app';
                }
            }

            if ($host === 'localhost') {
                $host = '127.0.0.1';
            }

            $baseUrl = "{$scheme}://{$host}{$port}".rtrim($basePath, '/');
            $endpoint = $baseUrl.'/obtenerDatosMaster.do';

            // 2. Definición de intentos de fallback secuenciales
            $sistemaIdentificador = $this->resolverIdentificadorSistema($sistemaUrl);
            $intentos = [
                // Intento 1: POST tradicional con identificador de sistema resuelto (ej: EMPLEADO)
                [
                    'method' => 'POST',
                    'query' => [],
                    'form_params' => [
                        'token' => $tokenSam,
                        'sistema' => $sistemaIdentificador,
                    ],
                    'headers' => [],
                ],
                // Intento 2: POST con URL de sistema original
                [
                    'method' => 'POST',
                    'query' => [],
                    'form_params' => [
                        'token' => $tokenSam,
                        'sistema' => $sistemaUrl,
                    ],
                    'headers' => [],
                ],
                // Intento 3: GET con parámetros en query string
                [
                    'method' => 'GET',
                    'query' => [
                        'token' => $tokenSam,
                        'sistema' => $sistemaIdentificador,
                    ],
                    'form_params' => [],
                    'headers' => [],
                ],
                // Intento 4: POST hardcodeado con 'EMPLEADO'
                [
                    'method' => 'POST',
                    'query' => [],
                    'form_params' => [
                        'token' => $tokenSam,
                        'sistema' => 'EMPLEADO',
                    ],
                    'headers' => [],
                ],
            ];

            $perfil = null;
            $estrategia = null;
            $intentoExitoso = null;
            $lastException = null;

            foreach ($intentos as $index => $intento) {
                $numIntento = $index + 1;
                Log::channel('sam')->debug("[SAM-PERFIL] Ejecutando intento #{$numIntento}", [
                    'method' => $intento['method'],
                    'endpoint' => $endpoint,
                    'query' => $intento['query'],
                    'form_params_keys' => array_keys($intento['form_params']),
                ]);
                Log::channel('sam_debug')->debug("[SAM-PERFIL] Ejecutando intento #{$numIntento}", [
                    'method' => $intento['method'],
                    'endpoint' => $endpoint,
                    'query' => $intento['query'],
                    'form_params_keys' => array_keys($intento['form_params']),
                ]);

                try {
                    $options = [
                        'timeout' => config('sam.timeout', 5.0),
                        'connect_timeout' => config('sam.connect_timeout', 3.0),
                        'force_ip_resolve' => config('sam.force_ip_resolve', 'v4'),
                        'allow_redirects' => [
                            'track_redirects' => true,
                        ],
                        'headers' => array_merge([
                            'Accept' => 'application/json, text/javascript, */*',
                            'Content-Type' => 'application/x-www-form-urlencoded',
                        ], $intento['headers']),
                    ];

                    if (! empty($intento['query'])) {
                        $options['query'] = $intento['query'];
                    }

                    if (! empty($intento['form_params'])) {
                        $options['form_params'] = $intento['form_params'];
                    }

                    $response = $this->requestWithRetry($intento['method'], $endpoint, $options);
                    $status = $response->getStatusCode();
                    $body = (string) $response->getBody();

                    // Guardar respuesta cruda para depuración si es ambiente local
                    $debugFile = null;
                    if (app()->isLocal()) {
                        $timestamp = now()->format('Ymd_His');
                        $uniqueId = uniqid();
                        $debugFile = storage_path("logs/sam_perfil_{$timestamp}_{$uniqueId}_intento_{$numIntento}.txt");
                        @file_put_contents($debugFile, "STATUS: {$status}\nHEADERS: ".json_encode($response->getHeaders())."\n\nBODY:\n{$body}");
                    }

                    Log::channel('sam')->debug("[SAM-PERFIL] Intento #{$numIntento} respuesta recibida", [
                        'status' => $status,
                        'body_length' => strlen($body),
                        'debug_file' => $debugFile,
                    ]);
                    Log::channel('sam_debug')->debug("[SAM-PERFIL] Intento #{$numIntento} respuesta recibida", [
                        'status' => $status,
                        'body_length' => strlen($body),
                        'debug_file' => $debugFile,
                    ]);

                    if ($status !== 200) {
                        throw new \RuntimeException("SAM perfil respondió HTTP {$status}");
                    }

                    if (str_starts_with(trim($body), '<') || str_contains($body, '<html') || str_contains($body, '<!DOCTYPE')) {
                        Log::channel('sam')->error("[SAM-PERFIL] Intento #{$numIntento} devolvió HTML en vez de JSON", [
                            'primeros_200_chars' => substr(strip_tags($body), 0, 200),
                        ]);
                        Log::channel('sam_debug')->error("[SAM-PERFIL] Intento #{$numIntento} devolvió HTML en vez de JSON", [
                            'primeros_200_chars' => substr(strip_tags($body), 0, 200),
                        ]);
                        throw new \RuntimeException('SAM devolvió página HTML en lugar de JSON de perfil.');
                    }

                    $data = json_decode($body, true);
                    if (json_last_error() !== JSON_ERROR_NONE) {
                        Log::channel('sam')->error("[SAM-PERFIL] Intento #{$numIntento} JSON inválido", [
                            'json_error' => json_last_error_msg(),
                            'body_sample' => substr($body, 0, 300),
                        ]);
                        Log::channel('sam_debug')->error("[SAM-PERFIL] Intento #{$numIntento} JSON inválido", [
                            'json_error' => json_last_error_msg(),
                            'body_sample' => substr($body, 0, 300),
                        ]);
                        throw new \RuntimeException('Respuesta de SAM no es JSON válido: '.json_last_error_msg());
                    }

                    Log::channel('sam')->debug("[SAM-PERFIL] Intento #{$numIntento} JSON parseado", [
                        'top_level_keys' => is_array($data) ? array_keys($data) : 'no-array',
                    ]);
                    Log::channel('sam_debug')->debug("[SAM-PERFIL] Intento #{$numIntento} JSON parseado", [
                        'top_level_keys' => is_array($data) ? array_keys($data) : 'no-array',
                    ]);

                    // Intentar extraer perfil con múltiples estrategias de mapeo
                    $candidatos = [
                        'responseObject',
                        'responseObject.usuario',
                        'responseObject.empleado',
                        'responseObject.datos',
                        'data',
                        'result',
                        'usuario',
                        'empleado',
                        'perfil',
                    ];

                    foreach ($candidatos as $path) {
                        $valor = $this->getNestedValue($data, $path);
                        if (is_array($valor) && ! empty($valor)) {
                            $perfil = $valor;
                            $estrategia = $path;
                            break;
                        }
                    }

                    // Fallback: si el JSON raíz es un array plano con información básica del usuario
                    if (! $perfil && is_array($data) && (isset($data['cedula']) || isset($data['id']) || isset($data['correo']) || isset($data['numero_empleado']))) {
                        $perfil = $data;
                        $estrategia = 'root_array';
                    }

                    if ($perfil) {
                        $intentoExitoso = $numIntento;
                        Log::channel('sam')->info("[SAM-PERFIL] Éxito en intento #{$numIntento}", [
                            'estrategia' => $estrategia,
                            'perfil_keys' => array_keys($perfil),
                        ]);
                        Log::channel('sam_debug')->info("[SAM-PERFIL] Éxito en intento #{$numIntento}", [
                            'estrategia' => $estrategia,
                            'perfil_keys' => array_keys($perfil),
                        ]);
                        break;
                    }

                    Log::channel('sam')->error("[SAM-PERFIL] Intento #{$numIntento} sin estructura de perfil", [
                        'json_keys' => array_keys($data),
                    ]);
                    Log::channel('sam_debug')->error("[SAM-PERFIL] Intento #{$numIntento} sin estructura de perfil", [
                        'json_keys' => array_keys($data),
                    ]);
                    throw new \RuntimeException('SAM respondió JSON válido pero sin estructura de perfil reconocida');
                } catch (\Throwable $e) {
                    Log::channel('sam')->warning("[SAM-PERFIL] Falló intento #{$numIntento}", [
                        'error' => $e->getMessage(),
                    ]);
                    Log::channel('sam_debug')->warning("[SAM-PERFIL] Falló intento #{$numIntento}", [
                        'error' => $e->getMessage(),
                    ]);
                    $lastException = $e;
                }
            }

            if (! $perfil) {
                throw $lastException ?? new \RuntimeException('No se pudo obtener el perfil de SAM tras intentar todos los fallbacks.');
            }

            return $perfil;

        } catch (RequestException $e) {
            Log::channel('sam')->error('[SAM-PERFIL] Error de conexión Guzzle', [
                'message' => $e->getMessage(),
                'has_response' => $e->hasResponse(),
                'response_status' => $e->hasResponse() ? $e->getResponse()->getStatusCode() : null,
            ]);
            Log::channel('sam_debug')->error('[SAM-PERFIL] Error de conexión Guzzle', [
                'message' => $e->getMessage(),
                'has_response' => $e->hasResponse(),
                'response_status' => $e->hasResponse() ? $e->getResponse()->getStatusCode() : null,
            ]);
            throw new \RuntimeException('Error de conexión con SAM al obtener perfil: '.$e->getMessage(), 0, $e);
        } catch (\Throwable $e) {
            Log::channel('sam')->error('[SAM-PERFIL] Error inesperado al obtener perfil', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            Log::channel('sam_debug')->error('[SAM-PERFIL] Error inesperado al obtener perfil', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }

    /**
     * Resuelve de manera segura el identificador de sistema corto a partir de una URL.
     */
    private function resolverIdentificadorSistema(string $sistemaUrl): string
    {
        $path = parse_url($sistemaUrl, PHP_URL_PATH) ?? '';
        $fileName = basename($path);

        $name = preg_replace('/\.do$/i', '', $fileName);

        if (empty($name) || $name === 'app') {
            return 'EMPLEADO';
        }

        return strtoupper($name);
    }

    /**
     * Helper para obtener de forma segura valores anuidos en arrays usando dot-notation.
     */
    private function getNestedValue(array $array, string $path): mixed
    {
        $keys = explode('.', $path);
        $current = $array;
        foreach ($keys as $key) {
            if (! is_array($current) || ! array_key_exists($key, $current)) {
                return null;
            }
            $current = $current[$key];
        }

        return $current;
    }

    public function logout(): void
    {
        try {
            $this->requestWithRetry('GET', 'app/login.do?accion=salir');
        } catch (ConnectException|RequestException $e) {
            throw $e;
        } catch (\Throwable) {
            // Ignorar otros errores en logout SAM
        } finally {
            $this->clearCache();
        }
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

    /**
     * Ejecuta una petición HTTP con reintentos automáticos ante errores de conexión o timeout.
     */
    private function requestWithRetry(string $method, string $uri, array $options = []): ResponseInterface
    {
        $lastException = null;

        for ($attempt = 1; $attempt <= 2; $attempt++) {
            try {
                $mergedOptions = array_merge([
                    'timeout' => config('sam.timeout', 5.0),
                    'connect_timeout' => config('sam.connect_timeout', 3.0),
                    'force_ip_resolve' => config('sam.force_ip_resolve', 'v4'),
                ], $options);

                return $this->client->request($method, $uri, $mergedOptions);
            } catch (ConnectException|RequestException $e) {
                $lastException = $e;

                Log::channel('sam')->warning("SAM request attempt {$attempt} failed", [
                    'uri' => $uri,
                    'method' => $method,
                    'error' => $e->getMessage(),
                ]);

                if ($attempt === 1) {
                    usleep(500000); // 500ms antes de reintentar
                }
            }
        }

        Log::channel('sam')->error('SAM request failed', [
            'uri' => $uri,
            'method' => $method,
            'attempt' => 2,
            'error' => $lastException->getMessage(),
        ]);

        throw $lastException;
    }

    private function extraerToken(string $html): ?array
    {
        Log::channel('sam_debug')->debug('[SAM] Iniciando extracción de token', [
            'html_length' => strlen($html),
            'snippet' => substr(strip_tags($html), 0, 300),
        ]);

        $patrones = [
            // 1. Patrón original con comillas dobles
            'href_doble' => '/href="([^"]+\?token=([a-fA-F0-9\-]{36}))"/',

            // 2. Comillas simples
            'href_simple' => "/href='([^']*\?token=([a-fA-F0-9\-]{36}))'/i",

            // 3. Input hidden con name="token" y value="UUID"
            'input_hidden' => '/<input[^>]*name=["\']?token["\']?[^>]*value=["\']?([a-fA-F0-9\-]{36})["\']?/i',
            'input_hidden_alt' => '/<input[^>]*value=["\']?([a-fA-F0-9\-]{36})["\']?[^>]*name=["\']?token["\']?/i',

            // 4. Meta tag
            'meta' => '/<meta[^>]*name=["\']?token["\']?[^>]*content=["\']?([a-fA-F0-9\-]{36})["\']?/i',

            // 5. JSON / texto inline: "token":"UUID"
            'json_token' => '/["\']token["\']\s*:\s*["\']([a-fA-F0-9\-]{36})["\']/i',

            // 6. UUID genérico de 36 caracteres con guiones
            'uuid_generico' => '/\b([a-fA-F0-9]{8}-[a-fA-F0-9]{4}-[a-fA-F0-9]{4}-[a-fA-F0-9]{4}-[a-fA-F0-9]{12})\b/i',

            // 7. Token plano (UUID sin guiones o hash de 32 caracteres)
            'token_plano' => '/\b([a-fA-F0-9]{32})\b/i',
        ];

        foreach ($patrones as $nombre => $regex) {
            if (preg_match($regex, $html, $matches)) {
                $token = null;
                $sistemaUrl = null;

                if ($nombre === 'href_doble' || $nombre === 'href_simple') {
                    $token = $matches[2] ?? null;
                    $sistemaUrl = isset($matches[1]) ? explode('?token=', $matches[1])[0] : null;
                } else {
                    $token = $matches[1] ?? null;
                }

                if ($token && strlen($token) >= 32) {
                    $tokenPrefix = substr($token, 0, 8).'...';
                    $sistemaUrlResolved = $sistemaUrl ?? env('SAM_SYSTEM_URL', 'http://localhost:8000');

                    Log::channel('sam_debug')->info('[SAM] Token extraído exitosamente', [
                        'patron' => $nombre,
                        'token_prefix' => $tokenPrefix,
                        'sistema_url' => $sistemaUrlResolved,
                    ]);

                    return [
                        'token' => $token,
                        'sistemaUrl' => $sistemaUrlResolved,
                    ];
                }
            }
        }

        // Si nada funcionó, guardar HTML para análisis manual y lanzar excepción informativa
        $archivo = storage_path('logs/sam_failed_html_'.now()->format('Ymd_His').'.html');
        @file_put_contents($archivo, $html);

        Log::channel('sam_debug')->error('[SAM] No se pudo extraer token de ningún patrón', [
            'html_file' => $archivo,
            'html_sample' => substr(strip_tags($html), 0, 500),
        ]);

        throw new \RuntimeException(
            'No se encontró token SAM en la respuesta HTML. '.
            "HTML guardado en: {$archivo}. ".
            'Revisar si el formato del token cambió o si la autenticación SAM devolvió error en lugar de éxito.'
        );
    }
}
