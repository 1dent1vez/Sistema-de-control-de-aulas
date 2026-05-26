<?php

/**
 * @descripcion  Configuración de bootstrap y manejo global de excepciones de la aplicación.
 *
 * @autor        Antigravity <support@google.com>
 *
 * @autorizador  Ruben Alejandro Nolasco Ruiz <correo@dominio.com>
 *
 * @prueba       Antigravity <support@google.com>
 *
 * @mantenimiento Antigravity <support@google.com>
 *
 * @version      1.4.0
 *
 * @creado       2026-05-26
 *
 * @modificado   2026-05-26
 *
 * @cambios      2026-05-26 - Actualización de manejadores de excepciones de autenticación y roles según RF-01 y RF-02.
 *               2026-05-26 - Actualización de manejadores de excepciones de base de datos y ModelNotFound según requerimientos de edificios y aulas.
 *               2026-05-26 - Adición de manejo de ModelNotFoundException para Semester y ClassSchedule.
 */

declare(strict_types=1);

use App\Exceptions\SamOfflineException;
use App\Http\Middleware\CheckRole;
use App\Http\Middleware\SamAuthMiddleware;
use App\Http\Middleware\SecurityHeadersMiddleware;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\TooManyRequestsHttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->encryptCookies(except: [
            'sam_token',
        ]);

        $middleware->alias([
            'sam.auth' => SamAuthMiddleware::class,
            'role' => CheckRole::class,
        ]);

        $middleware->append(SecurityHeadersMiddleware::class);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (NotFoundHttpException $e, Request $request) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'statusCode' => 404,
                    'message' => 'Recurso no encontrado.',
                    'data' => null,
                    'errors' => [],
                ], 404);
            }
        });

        $exceptions->render(function (AuthorizationException $e, Request $request) {
            Log::channel('security')->warning('Acceso denegado (403)', [
                'ip' => $request->ip(),
                'endpoint' => $request->fullUrl(),
                'method' => $request->method(),
                'user' => $request->user()?->external_id ?? 'guest',
                'timestamp' => now()->toIso8601String(),
            ]);

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'statusCode' => 403,
                    'message' => 'Rol no autorizado en este sistema. Su perfil no tiene permisos para acceder. Contacte al administrador.',
                    'data' => null,
                    'errors' => [],
                ], 403);
            }
        });

        $exceptions->render(function (AuthenticationException $e, Request $request) {
            Log::channel('security')->warning('Intento de acceso sin autenticar', [
                'ip' => $request->ip(),
                'endpoint' => $request->fullUrl(),
                'method' => $request->method(),
                'timestamp' => now()->toIso8601String(),
            ]);

            if ($request->expectsJson()) {
                $message = 'Sesion expirada. Su sesion ha caducado o el token es invalido. Inicie sesion nuevamente.';
                if ($request->is('*logout*') || $request->routeIs('*logout*')) {
                    $hasToken = $request->bearerToken() || $request->cookie('sam_token');
                    if ($hasToken) {
                        $message = 'Su sesion ya habia expirado. Inicie sesion nuevamente.';
                    } else {
                        $message = 'No hay una sesion activa. Será redirigido al inicio de sesion.';
                    }
                }

                return response()->json([
                    'success' => false,
                    'statusCode' => 401,
                    'message' => $message,
                    'data' => null,
                    'errors' => [],
                ], 401);
            }
        });

        $exceptions->render(function (ValidationException $e, Request $request) {
            Log::channel('security')->info('Error de validación', [
                'ip' => $request->ip(),
                'endpoint' => $request->fullUrl(),
                'fields' => array_keys($e->errors()),
                'timestamp' => now()->toIso8601String(),
            ]);

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'statusCode' => 422,
                    'message' => 'Error de validación.',
                    'data' => null,
                    'errors' => $e->errors(),
                ], 422);
            }
        });

        $exceptions->render(function (SamOfflineException $e, Request $request) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'statusCode' => 503,
                    'message' => $e->getMessage(),
                    'data' => null,
                    'errors' => [],
                ], 503);
            }
        });

        $exceptions->render(function (ValueError $e, Request $request) {
            if ($request->expectsJson()) {
                if (str_contains($e->getMessage(), 'App\\Enums\\Auth\\SamRole') || str_contains($e->getMessage(), 'SamRole')) {
                    return response()->json([
                        'success' => false,
                        'statusCode' => 422,
                        'message' => 'Rol no autorizado en este sistema. Su perfil no tiene permisos para acceder. Contacte al administrador.',
                        'data' => null,
                        'errors' => ['role' => ['Rol no autorizado en este sistema. Su perfil no tiene permisos para acceder. Contacte al administrador.']],
                    ], 422);
                }
            }
        });

        $exceptions->render(function (UnhandledMatchError $e, Request $request) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'statusCode' => 500,
                    'message' => 'Error técnico: no existe una vista asociada a su rol. El administrador ha sido notificado.',
                    'data' => null,
                    'errors' => [],
                ], 500);
            }
        });

        $exceptions->render(function (TooManyRequestsHttpException $e, Request $request) {
            Log::channel('security')->error('Rate limit excedido', [
                'ip' => $request->ip(),
                'endpoint' => $request->fullUrl(),
                'timestamp' => now()->toIso8601String(),
            ]);

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'statusCode' => 429,
                    'message' => 'Demasiadas solicitudes. Por favor, inténtelo de nuevo más tarde.',
                    'data' => null,
                    'errors' => [],
                ], 429);
            }
        });

        $exceptions->render(function (ModelNotFoundException $e, Request $request) {
            if ($request->expectsJson()) {
                $modelClass = $e->getModel();
                $message = 'El recurso solicitado no existe o fue eliminado.';
                if (str_contains($modelClass, 'Building')) {
                    $message = 'El edificio solicitado no existe o no esta registrado en el sistema.';
                } elseif (str_contains($modelClass, 'Classroom')) {
                    $message = 'El aula solicitada no existe o no esta registrada en el sistema.';
                } elseif (str_contains($modelClass, 'Semester')) {
                    $message = 'El semestre solicitado no existe o fue eliminado.';
                } elseif (str_contains($modelClass, 'ClassSchedule') || str_contains($modelClass, 'Schedule')) {
                    $message = 'El horario solicitado no existe o fue eliminado.';
                }

                return response()->json([
                    'success' => false,
                    'statusCode' => 404,
                    'message' => $message,
                    'data' => null,
                    'errors' => [],
                ], 404);
            }
        });

        $exceptions->render(function (QueryException $e, Request $request) {
            Log::error('Error de base de datos en solicitud API/Ajax', [
                'ip' => $request->ip(),
                'endpoint' => $request->fullUrl(),
                'message' => $e->getMessage(),
                'sql' => $e->getSql(),
                'bindings' => $e->getBindings(),
                'timestamp' => now()->toIso8601String(),
            ]);

            if ($request->expectsJson() || $request->is('api/*') || $request->is('horarios/*')) {
                $msg = $e->getMessage();
                if (str_contains($msg, 'UNIQUE constraint failed') || str_contains($msg, 'Duplicate entry') || $e->getCode() === '23000') {
                    return response()->json([
                        'success' => false,
                        'statusCode' => 422,
                        'message' => 'El registro que intenta crear ya existe en el sistema.',
                        'error' => 'El registro que intenta crear ya existe en el sistema.',
                        'data' => null,
                        'errors' => ['database' => ['El registro que intenta crear ya existe en el sistema.']],
                    ], 422);
                }

                // Si es una consulta de lectura (SELECT)
                if (stripos($e->getSql() ?? '', 'select') === 0) {
                    return response()->json([
                        'success' => false,
                        'statusCode' => 500,
                        'message' => 'Error al consultar la base de datos. Intente nuevamente.',
                        'error' => 'Error al consultar la base de datos. Intente nuevamente.',
                        'data' => null,
                        'errors' => ['database' => ['Error al consultar la base de datos. Intente nuevamente.']],
                    ], 500);
                }

                return response()->json([
                    'success' => false,
                    'statusCode' => 500,
                    'message' => 'Error al guardar en la base de datos. Intente nuevamente o contacte al administrador.',
                    'error' => 'Error al guardar en la base de datos. Intente nuevamente o contacte al administrador.',
                    'data' => null,
                    'errors' => ['database' => ['Error al guardar en la base de datos. Intente nuevamente o contacte al administrador.']],
                ], 500);
            }
        });

        $exceptions->render(function (PDOException $e, Request $request) {
            Log::error('Error de conexión PDO en solicitud API/Ajax', [
                'ip' => $request->ip(),
                'endpoint' => $request->fullUrl(),
                'message' => $e->getMessage(),
                'timestamp' => now()->toIso8601String(),
            ]);

            if ($request->expectsJson() || $request->is('api/*') || $request->is('horarios/*')) {
                return response()->json([
                    'success' => false,
                    'statusCode' => 500,
                    'message' => 'Error de conexión con la base de datos. Contacte al administrador.',
                    'error' => 'Error de conexión con la base de datos. Contacte al administrador.',
                    'data' => null,
                    'errors' => ['database' => ['Error de conexión con la base de datos. Contacte al administrador.']],
                ], 500);
            }
        });

        $exceptions->render(function (Throwable $e, Request $request) {
            if ($request->expectsJson() && app()->isProduction()) {
                return response()->json([
                    'success' => false,
                    'statusCode' => 500,
                    'message' => 'Error interno del servidor. Contacte al administrador.',
                    'data' => null,
                    'errors' => [],
                ], 500);
            }
        });
    })->create();
