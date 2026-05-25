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
 * @version      1.4.2
 *
 * @creado       2026-05-17
 *
 * @modificado   2026-05-25
 *
 * @cambios      2026-05-17 - Creación inicial del servicio de autenticación SAM
 *               2026-05-22 - Captura robusta de excepciones al obtener el perfil de SAM y validación de campos mínimos.
 *               2026-05-24 - Traducción de excepciones de red/timeout a mensajes de error en español con códigos HTTP 502/503.
 *               2026-05-24 - Adición de fallback automático a mock en caso de indisponibilidad del servidor SAM real.
 *               2026-05-24 - Extracción de loginWithSamReal y loginWithMock, manejo de excepciones de conexión y traducciones al español.
 *               2026-05-24 - Remoción de mock fallback en orquestarLogin para que los fallos del servidor real se propaguen al controlador.
 *               2026-05-25 - Refactorización de redirecciones post-login usando la expresión match sobre el enum SamRole.
 *               2026-05-25 - Implementación de fallback local en login con SAM real ante fallos en obtenerPerfil, y corrección de bug de email duplicado.
 */

declare(strict_types=1);

namespace App\Services\Auth;

use App\Enums\Auth\SamRole;
use App\Models\SamIdentity;
use App\Repositories\Contracts\SamIdentityRepositoryInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SamAuthService
{
    public function __construct(
        private readonly SamService $samService,
        private readonly SamIdentityRepositoryInterface $identityRepository
    ) {}

    /**
     * Ejecuta la lógica de login mockeado (de respaldo o normal).
     *
     * @return array{success: bool, statusCode: int, message: string, data: array<string, mixed>}
     */
    private function loginWithMock(string $username, bool $usingMockFallback = false): array
    {
        $identity = $this->identityRepository->findByExternalId($username);
        $isSamMaster = ($username === 'admin');

        if ($identity === null) {
            $identity = new SamIdentity;
            $identity->external_id = $username;
            $identity->email = str_contains($username, '@') ? $username : $username.'@toluca.tecnm.mx';
            $identity->full_name = null;
            $identity->role = $isSamMaster ? SamRole::ADMIN : null;
            $identity->last_login_at = now();
            $identity->save();
        } else {
            if ($isSamMaster && $identity->role === null) {
                $identity->role = SamRole::ADMIN;
                $identity->save();
            } else {
                $identity->last_login_at = now();
                $identity->save();
            }
        }

        $needsRole = $identity->role === null;
        $abilities = $identity->role === SamRole::ADMIN ? ['*'] : ($identity->role === SamRole::TEACHER ? ['teacher'] : []);
        $token = $identity->createToken('sam-token', $abilities);
        $expiresAt = $token->accessToken->expires_at;
        $redirectUrl = match ($identity->role) {
            SamRole::ADMIN => '/admin/dashboard',
            SamRole::TEACHER => '/docente/dashboard',
            default => '/espera-rol',
        };

        return [
            'success' => true,
            'statusCode' => 200,
            'message' => $usingMockFallback ? 'Login exitoso (Modo Contingencia).' : 'Login exitoso (mock).',
            'data' => [
                'accessToken' => $token->plainTextToken,
                'tokenType' => 'Bearer',
                'expiresAt' => $expiresAt?->toIso8601String(),
                'role' => $identity->role?->value,
                'needsRole' => $needsRole,
                'redirectUrl' => $redirectUrl,
                'using_mock_fallback' => $usingMockFallback,
                'user' => [
                    'externalId' => $identity->external_id,
                    'fullName' => $identity->full_name ?? $username,
                    'email' => $identity->email,
                    'position' => $isSamMaster ? 'Administrador' : 'Docente',
                    'department' => 'TI',
                    'building' => null,
                    'role' => $identity->role?->value,
                    'needsRole' => $needsRole,
                ],
            ],
        ];
    }

    /**
     * Orquesta el flujo completo de login (mock o SAM real).
     *
     * @return array{success: bool, statusCode: int, message: string, data?: array<string, mixed>}
     */
    public function orquestarLogin(string $username, string $password, string $captcha): array
    {
        if (config('sam.mock_enabled')) {
            return $this->loginWithMock($username, false);
        }

        return $this->loginWithSamReal($username, $password, $captcha);
    }

    /**
     * Ejecuta la autenticación contra el servidor SAM real.
     */
    private function loginWithSamReal(string $username, string $password, string $captcha): array
    {
        $loginResult = $this->samService->login($username, $password, $captcha);
        if (! $loginResult['success']) {
            $credError = ['Credenciales inválidas', 'Usuario no encontrado en SAM', 'Clave incorrecta', 'Contraseña incorrecta', 'Usuario no encontrado'];
            if (in_array($loginResult['error'], $credError, true)) {
                return [
                    'success' => false,
                    'statusCode' => 401,
                    'message' => 'Usuario o contraseña incorrectos.',
                ];
            }

            throw new \RuntimeException($loginResult['error'] ?? 'Error desconocido de SAM real.');
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
                'correo' => str_contains($username, '@') ? $username : $username.'@toluca.tecnm.mx',
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
                    'message' => 'El token de autenticación no está disponible.',
                ];
            }

            try {
                $perfil = $this->samService->obtenerPerfil($tokenSam, $sistemaUrl);

                if (empty($perfil['cedula']) && empty($perfil['id']) && empty($perfil['codigo']) && empty($perfil['numero_empleado']) && empty($perfil['correo'])) {
                    throw new \RuntimeException('Perfil incompleto obtenido de SAM real.');
                }
            } catch (\Throwable $e) {
                Log::channel('sam')->warning('SAM perfil no disponible, usando BD local como fallback', [
                    'external_id' => $username,
                    'error' => $e->getMessage(),
                ]);

                $existing = $this->identityRepository->findByExternalId($username);
                if ($existing !== null) {
                    $perfil = [
                        'nombre' => $existing->full_name ?? $username,
                        'apellidoPa' => '',
                        'apellidoMa' => '',
                        'correo' => $existing->email ?? (str_contains($username, '@') ? $username : $username.'@toluca.tecnm.mx'),
                        'numero_empleado' => $existing->external_id,
                        'rol' => $existing->role === SamRole::ADMIN ? 'master' : 'empleado',
                    ];
                } else {
                    $perfil = [
                        'nombre' => $username,
                        'apellidoPa' => '',
                        'apellidoMa' => '',
                        'correo' => str_contains($username, '@') ? $username : $username.'@toluca.tecnm.mx',
                        'numero_empleado' => $username,
                        'rol' => $rolSam,
                    ];
                }
            }

            $rolLocal = $this->mapearRolLocal($perfil);
        }

        $identity = $this->crearOActualizarIdentidad($perfil, $rolSam, $rolLocal);

        $needsRole = $identity->role === null;
        $abilities = $identity->role === SamRole::ADMIN ? ['*'] : ($identity->role === SamRole::TEACHER ? ['teacher'] : []);
        $token = $identity->createToken('sam-token', $abilities);
        $expiresAt = $token->accessToken->expires_at;

        $redirectUrl = match ($identity->role) {
            SamRole::ADMIN => '/admin/dashboard',
            SamRole::TEACHER => '/docente/dashboard',
            default => '/espera-rol',
        };

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
                'role' => $identity->role?->value,
                'needsRole' => $needsRole,
                'redirectUrl' => $redirectUrl,
                'using_mock_fallback' => false,
                'user' => [
                    'externalId' => $identity->external_id,
                    'fullName' => $fullName ?: $identity->full_name,
                    'email' => $identity->email,
                    'position' => $perfil['nombre_puesto_empleado'] ?? null,
                    'department' => $perfil['nombre_departamento_empleado'] ?? null,
                    'building' => $perfil['edificio_empleado'] ?? null,
                    'role' => $identity->role?->value,
                    'needsRole' => $needsRole,
                ],
            ],
        ];
    }

    /**
     * Cierra sesión en SAM y revoca el token Sanctum actual.
     */
    public function logout(Request $request): void
    {
        $this->samService->logout();
        $request->user()->currentAccessToken()->delete();
        session()->forget('sam_cookies');
    }

    private function mapearRolLocal(array $perfil): ?SamRole
    {
        $rolSam = $perfil['rol'] ?? null;

        if ($rolSam === 'master') {
            return SamRole::ADMIN;
        }

        if ($rolSam === 'empleado') {
            // Evaluar permisos CRUD del perfil SAM para decidir si es admin local
            $crear = filter_var($perfil['crear'] ?? false, FILTER_VALIDATE_BOOLEAN);
            $leer = filter_var($perfil['leer'] ?? false, FILTER_VALIDATE_BOOLEAN);
            $editar = filter_var($perfil['editar'] ?? false, FILTER_VALIDATE_BOOLEAN);
            $eliminar = filter_var($perfil['eliminar'] ?? false, FILTER_VALIDATE_BOOLEAN);

            // Si es empleado y tiene permisos CRUD completos (crear, leer, editar), es ADMIN.
            if ($crear && $leer && $editar) {
                return SamRole::ADMIN;
            }

            return SamRole::TEACHER;
        }

        return null;
    }

    private function crearOActualizarIdentidad(array $perfil, string $rolSam, ?SamRole $rolLocal): SamIdentity
    {
        $correo = $perfil['correo'] ?? '';
        $externalId = $perfil['numero_empleado'] ?? $correo;

        return DB::transaction(function () use ($externalId, $rolSam, $rolLocal) {
            $identity = $this->identityRepository->findByExternalId($externalId);
            $isSamMaster = ($rolSam === 'master' || $externalId === 'admin');

            if ($identity === null) {
                $identity = new SamIdentity;
                $identity->external_id = $externalId;
                $identity->email = str_contains($externalId, '@') ? $externalId : $externalId.'@toluca.tecnm.mx';
                $identity->full_name = null;
                $identity->role = $isSamMaster ? SamRole::ADMIN : $rolLocal;
                $identity->last_login_at = now();
                $identity->save();
            } else {
                if ($isSamMaster && $identity->role === null) {
                    $identity->role = SamRole::ADMIN;
                } elseif ($identity->role === null) {
                    $identity->role = $rolLocal;
                }
                $identity->last_login_at = now();
                $identity->save();
            }

            return $identity;
        });
    }
}
