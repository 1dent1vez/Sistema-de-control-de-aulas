<?php

/**
 * @descripcion  Controlador API para autenticación SAM.
 *
 * @autor        Ghael Garcia Manjarrez <ghael.engineer@gmail.com>
 *
 * @autorizador  Ruben Alejandro Nolasco Ruiz <correo@dominio.com>
 *
 * @prueba       Ghael Garcia Manjarrez <ghael.engineer@gmail.com>
 *
 * @mantenimiento Ghael Garcia Manjarrez <ghael.engineer@gmail.com>
 *
 * @version      1.2.0
 *
 * @creado       2026-05-17
 *
 * @modificado   2026-05-24
 *
 * @cambios      2026-05-18 - Refactorización: compactación
 *               2026-05-22 - Ajuste dinámico del atributo secure de la cookie sam_token según el entorno
 *               2026-05-24 - Simplificación para cumplir con el límite de 100 líneas por controlador API
 *               2026-05-24 - Traducción de mensajes y excepciones Guzzle en login
 */

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\ValidateCaptchaRequest;
use App\Http\Resources\Auth\AuthResource;
use App\Http\Resources\Auth\SamProfileResource;
use App\Services\Auth\SamAuthService;
use App\Services\Auth\SamService;
use App\Traits\ApiResponse;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class AuthController extends Controller
{
    use ApiResponse;

    public function __construct(private readonly SamService $samService, private readonly SamAuthService $samAuthService) {}

    public function captcha(): Response|JsonResponse
    {
        if (config('sam.mock_enabled')) {
            return response(base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg=='), 200)->header('Content-Type', 'image/png');
        }
        $result = $this->samService->obtenerCaptcha();
        if ($result['png'] === null) {
            return $this->error('El sistema de autenticación no está disponible.', 503);
        }

        return response($result['png'], 200)->header('Content-Type', 'image/png')->withCookie(cookie($this->samService->getSessionCookieName(), $result['sessionId'], 10));
    }

    public function validateCaptcha(ValidateCaptchaRequest $request): JsonResponse
    {
        $valido = $this->samService->validarCaptcha($request->input('captchaCode'));

        return $this->success(['valid' => $valido], $valido ? 'CAPTCHA válido.' : 'El captcha ingresado es incorrecto.', $valido ? 200 : 422);
    }

    public function login(LoginRequest $request): JsonResponse
    {
        try {
            $result = $this->samAuthService->orquestarLogin($request->input('username'), $request->input('password'), $request->input('captchaCode'));
            if (! $result['success']) {
                return $this->error($result['message'], $result['statusCode']);
            }
            $data = $result['data'];
            $response = $this->success(new AuthResource($data), $result['message'], $result['statusCode']);
            if ($token = $data['accessToken'] ?? null) {
                $response->withCookie(cookie('sam_token', $token, 1440, '/', null, config('app.env') === 'production', true, false, 'Lax'));
            }
            if (isset($data['sessionId'])) {
                $response->withCookie(cookie('sam_session', $data['sessionId'], 10));
            }

            return $response;
        } catch (ConnectException $e) {
            return $this->error('No se pudo conectar con el servidor de autenticación. Verifica tu conexión o inténtalo más tarde.', 503);
        } catch (RequestException $e) {
            $status = ($e->hasResponse() && $e->getResponse()->getStatusCode() >= 500) ? 503 : 502;
            $msg = $status === 503 ? 'El servidor de autenticación está experimentando problemas. Inténtalo más tarde.' : 'Error al comunicarse con el servidor de autenticación.';

            return $this->error($msg, $status);
        } catch (\Throwable $e) {
            return $this->error('Ocurrió un error inesperado. Contacta al administrador.', 500);
        }
    }

    public function logout(Request $request): JsonResponse
    {
        $this->samAuthService->logout($request);

        return $this->success(null, 'Sesión cerrada exitosamente.');
    }

    public function me(Request $request): JsonResponse
    {
        return $this->success(new SamProfileResource($request->user()), 'Perfil obtenido exitosamente.');
    }
}
