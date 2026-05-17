<?php

/**
 * @descripcion  Servicio que orquesta el flujo completo de login/logout SAM.
 *              Valida CAPTCHA, ejecuta login SAM, extrae perfil, crea/actualiza SamIdentity,
 *              emite token Sanctum con abilities según rol.
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
 * @cambios      2026-05-17 - Creación inicial del servicio de autenticación SAM
 */

declare(strict_types=1);

namespace App\Services\Auth;

use App\Enums\Auth\SamRole;
use App\Models\SamIdentity;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SamAuthService
{
    public function __construct(
        private readonly SamService $samService
    ) {}

    public function orquestarLogin(string $username, string $password, string $captcha): array
    {
        $captchaValido = $this->samService->validarCaptcha($captcha);
        if (! $captchaValido) {
            return [
                'success' => false,
                'statusCode' => 422,
                'message' => 'Código de verificación incorrecto.',
            ];
        }

        $loginResult = $this->samService->login($username, $password, $captcha);
        if (! $loginResult['success']) {
            $statusCode = $loginResult['error'] === 'Credenciales inválidas' ? 401 : 503;

            return [
                'success' => false,
                'statusCode' => $statusCode,
                'message' => $loginResult['error'] ?? 'Error de autenticación.',
            ];
        }

        $rolSam = $loginResult['rol'];
        $tokenSam = $loginResult['token'];
        $sistemaUrl = $loginResult['sistemaUrl'] ?? config('app.url');

        if ($rolSam === 'master') {
            $rolLocal = SamRole::ADMIN;
            $perfil = [
                'nombre' => $username,
                'apellidoPa' => '',
                'apellidoMa' => '',
                'correo' => $username.'@toluca.tecnm.mx',
                'nombre_puesto_empleado' => 'Administrador',
                'nombre_departamento_empleado' => '',
                'edificio_empleado' => '',
                'rol' => 'master',
            ];
        } else {
            if ($tokenSam === null || $sistemaUrl === null) {
                return [
                    'success' => false,
                    'statusCode' => 401,
                    'message' => 'Token SAM no disponible.',
                ];
            }

            $perfil = $this->samService->obtenerPerfil($tokenSam, $sistemaUrl);
            if ($perfil === null) {
                return [
                    'success' => false,
                    'statusCode' => 503,
                    'message' => 'Error al obtener perfil de SAM.',
                ];
            }

            $rolLocal = $this->mapearRolLocal($perfil['rol'] ?? 'empleado');
            if ($rolLocal === null) {
                return [
                    'success' => false,
                    'statusCode' => 403,
                    'message' => 'Rol no autorizado en este sistema.',
                ];
            }
        }

        $identity = $this->crearOActualizarIdentidad($perfil, $rolSam, $rolLocal);

        $abilities = $rolLocal === SamRole::ADMIN ? ['*'] : ['teacher'];
        $token = $identity->createToken('sam-token', $abilities);
        $expiresAt = $token->accessToken->expires_at;

        $redirectUrl = $rolLocal === SamRole::ADMIN ? '/admin/dashboard' : '/teacher/dashboard';

        $fullName = trim(
            ($perfil['nombre'] ?? '')
            .' '.($perfil['apellidoPa'] ?? '')
            .' '.($perfil['apellidoMa'] ?? '')
        );

        return [
            'success' => true,
            'statusCode' => 200,
            'message' => 'Login exitoso.',
            'data' => [
                'accessToken' => $token->plainTextToken,
                'tokenType' => 'Bearer',
                'expiresAt' => $expiresAt?->toIso8601String(),
                'role' => $rolLocal->value,
                'redirectUrl' => $redirectUrl,
                'user' => [
                    'externalId' => $identity->external_id,
                    'fullName' => $fullName ?: $identity->full_name,
                    'email' => $identity->email,
                    'position' => $perfil['nombre_puesto_empleado'] ?? null,
                    'department' => $perfil['nombre_departamento_empleado'] ?? null,
                    'building' => $perfil['edificio_empleado'] ?? null,
                    'role' => $rolLocal->value,
                ],
            ],
        ];
    }

    public function logout(Request $request): void
    {
        $this->samService->logout();
        $request->user()->currentAccessToken()->delete();
        session()->forget('sam_cookies');
    }

    private function mapearRolLocal(string $rolSam): ?SamRole
    {
        return match ($rolSam) {
            'master' => SamRole::ADMIN,
            default => SamRole::TEACHER,
        };
    }

    private function crearOActualizarIdentidad(array $perfil, string $rolSam, SamRole $rolLocal): SamIdentity
    {
        $correo = $perfil['correo'] ?? '';
        $nombre = trim(
            ($perfil['nombre'] ?? '')
            .' '.($perfil['apellidoPa'] ?? '')
            .' '.($perfil['apellidoMa'] ?? '')
        );
        $externalId = $perfil['numero_empleado'] ?? $correo;

        return DB::transaction(function () use ($externalId, $correo, $nombre, $rolSam, $rolLocal) {
            $identity = SamIdentity::where('external_id', $externalId)->first();

            if ($identity === null) {
                $identity = SamIdentity::create([
                    'external_id' => $externalId,
                    'email' => $correo,
                    'full_name' => $nombre ?: null,
                    'role' => $rolSam === 'master' ? SamRole::ADMIN : $rolLocal,
                    'last_login_at' => now(),
                ]);
            } else {
                $identity->update([
                    'email' => $correo,
                    'full_name' => $nombre ?: $identity->full_name,
                    'last_login_at' => now(),
                ]);
            }

            return $identity;
        });
    }
}
