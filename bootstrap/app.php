<?php

use App\Http\Middleware\CheckRole;
use App\Http\Middleware\SamAuthMiddleware;
use App\Http\Middleware\SecurityHeadersMiddleware;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
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
                    'message' => 'Resource not found.',
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
                    'message' => 'Forbidden.',
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
                return response()->json([
                    'success' => false,
                    'statusCode' => 401,
                    'message' => 'Unauthenticated.',
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
                    'message' => 'Validation failed.',
                    'data' => null,
                    'errors' => $e->errors(),
                ], 422);
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
                    'message' => 'Too many requests.',
                    'data' => null,
                    'errors' => [],
                ], 429);
            }
        });

        $exceptions->render(function (Throwable $e, Request $request) {
            if ($request->expectsJson() && app()->isProduction()) {
                return response()->json([
                    'success' => false,
                    'statusCode' => 500,
                    'message' => 'Internal server error.',
                    'data' => null,
                    'errors' => [],
                ], 500);
            }
        });
    })->create();
