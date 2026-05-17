<?php

/**
 * @descripcion  Controlador API para autenticación SAM.
 *              Métodos: captcha, validateCaptcha, login, logout, me.
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
 * @cambios      2026-05-17 - Creación inicial del controlador
 */

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Resources\Auth\AuthResource;
use App\Http\Resources\Auth\SamProfileResource;
use App\Services\Auth\SamAuthService;
use App\Services\Auth\SamService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class AuthController extends Controller
{
    use ApiResponse;

    public function __construct(
        private readonly SamService $samService,
        private readonly SamAuthService $samAuthService
    ) {}

    public function captcha(): Response|JsonResponse
    {
        $png = $this->samService->obtenerCaptcha();

        if ($png === null) {
            return $this->error('Servicio SAM no disponible.', 503);
        }

        return response($png, 200)->header('Content-Type', 'image/png');
    }

    public function validateCaptcha(Request $request): JsonResponse
    {
        $request->validate(['captchaCode' => 'required|string']);

        $valido = $this->samService->validarCaptcha($request->input('captchaCode'));

        return $this->success(['valid' => $valido], $valido ? 'CAPTCHA válido.' : 'CAPTCHA incorrecto.', $valido ? 200 : 422);
    }

    public function login(LoginRequest $request): JsonResponse
    {
        $result = $this->samAuthService->orquestarLogin(
            $request->input('username'),
            $request->input('password'),
            $request->input('captchaCode')
        );

        if (! $result['success']) {
            return $this->error($result['message'], $result['statusCode']);
        }

        return $this->success(
            new AuthResource($result['data']),
            $result['message'],
            $result['statusCode'],
        );
    }

    public function logout(Request $request): JsonResponse
    {
        $this->samAuthService->logout($request);

        return $this->success(null, 'Sesión cerrada exitosamente.');
    }

    public function me(Request $request): JsonResponse
    {
        return $this->success(
            new SamProfileResource($request->user()),
            'Perfil obtenido exitosamente.'
        );
    }
}
