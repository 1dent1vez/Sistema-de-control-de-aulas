<?php

/**
 * @descripcion  Middleware de autenticación SAM — valida token Sanctum desde
 *               cabecera Authorization (Bearer) o cookie sam_token con diagnóstico y logs detallados.
 *
 * @autor        Equipo GAMA
 *
 * @autorizador  Ruben Alejandro Nolasco Ruiz <correo@dominio.com>
 *
 * @prueba       Diego Miguel Hernandez Fabela
 *
 * @mantenimiento Ghael Garcia Manjarrez <ghael.engineer@gmail.com>
 *
 * @version      1.2.0
 *
 * @creado       2026-05-17
 *
 * @modificado   2026-05-22
 *
 * @cambios      2026-05-19 - Soporte para cookie sam_token (protección rutas web)
 *               2026-05-22 - Diagnóstico completo, logging estructurado, eager loading de tokenable y corrección de encriptación de cookie
 */

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Models\SamIdentity;
use Closure;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Laravel\Sanctum\PersonalAccessToken;

class SamAuthMiddleware
{
    public function handle(Request $request, Closure $next): mixed
    {
        $bearer = $request->bearerToken();
        $cookie = $request->cookie('sam_token');
        $token = $bearer ?? $cookie;

        Log::channel('sam_auth')->debug('[SAM-AUTH] Entrada | Context: '.json_encode([
            'method' => $request->method(),
            'uri' => $request->fullUrl(),
            'has_bearer' => $bearer !== null,
            'has_cookie' => $cookie !== null,
        ]));

        if ($token) {
            $tokenPrefix = substr($token, 0, 10).'...';
            Log::channel('sam_auth')->debug('[SAM-AUTH] Extracción de token | Context: '.json_encode([
                'token_prefix' => $tokenPrefix,
                'source' => $bearer !== null ? 'bearer' : 'cookie',
            ]));

            // Eager loading para evitar N+1
            $accessToken = null;
            if (str_contains($token, '|')) {
                [$id, $tokenVal] = explode('|', $token, 2);
                $accessToken = PersonalAccessToken::with('tokenable')->find($id);
                if ($accessToken && ! hash_equals($accessToken->token, hash('sha256', $tokenVal))) {
                    $accessToken = null;
                }
            } else {
                $accessToken = PersonalAccessToken::with('tokenable')
                    ->where('token', hash('sha256', $token))
                    ->first();
            }

            if ($accessToken) {
                $isExpired = $accessToken->expires_at && $accessToken->expires_at->isPast();

                Log::channel('sam_auth')->debug('[SAM-AUTH] Consulta DB | Context: '.json_encode([
                    'found' => true,
                    'token_id' => $accessToken->id,
                    'tokenable_type' => $accessToken->tokenable_type,
                    'tokenable_id' => $accessToken->tokenable_id,
                    'last_used_at' => $accessToken->last_used_at?->toIso8601String(),
                    'expires_at' => $accessToken->expires_at?->toIso8601String(),
                    'is_expired' => $isExpired,
                ]));

                if ($isExpired) {
                    Log::channel('sam_auth')->debug('[SAM-AUTH] Decisión | Context: '.json_encode([
                        'decision' => 'auth fail',
                        'reason' => 'Token expirado',
                    ]));

                    if (! $request->expectsJson()) {
                        return redirect('/')->with('error', 'Sesión expirada');
                    }
                    throw new AuthenticationException('Session expired.', ['sanctum']);
                }

                $identity = $accessToken->tokenable;

                if ($identity && $identity instanceof SamIdentity) {
                    Log::channel('sam_auth')->debug('[SAM-AUTH] Resolución de modelo | Context: '.json_encode([
                        'resolved' => true,
                        'identity_id' => $identity->id,
                        'email' => $identity->email,
                    ]));

                    Auth::login($identity);

                    Log::channel('sam_auth')->debug('[SAM-AUTH] Decisión | Context: '.json_encode([
                        'decision' => 'auth pass',
                        'reason' => 'Usuario autenticado exitosamente',
                        'identity_id' => $identity->id,
                    ]));

                    return $next($request);
                } else {
                    Log::channel('sam_auth')->debug('[SAM-AUTH] Resolución de modelo | Context: '.json_encode([
                        'resolved' => false,
                        'reason' => 'Tokenable inválido o no es instancia de SamIdentity',
                    ]));

                    Log::channel('sam_auth')->debug('[SAM-AUTH] Decisión | Context: '.json_encode([
                        'decision' => 'auth fail',
                        'reason' => 'Identidad no válida',
                    ]));

                    if (! $request->expectsJson()) {
                        return redirect('/')->with('error', 'Identidad no válida');
                    }
                    throw new AuthenticationException('Invalid identity.', ['sanctum']);
                }
            } else {
                Log::channel('sam_auth')->debug('[SAM-AUTH] Consulta DB | Context: '.json_encode([
                    'found' => false,
                    'token_prefix' => $tokenPrefix,
                ]));

                Log::channel('sam_auth')->debug('[SAM-AUTH] Decisión | Context: '.json_encode([
                    'decision' => 'auth fail',
                    'reason' => 'Token no existe en DB',
                ]));

                if (! $request->expectsJson()) {
                    return redirect('/')->with('error', 'Token inválido');
                }
                throw new AuthenticationException('Unauthenticated.', ['sanctum']);
            }
        } else {
            Log::channel('sam_auth')->debug('[SAM-AUTH] Decisión | Context: '.json_encode([
                'decision' => 'auth fail',
                'reason' => 'No se encontró token en cookie ni header Bearer',
            ]));

            if (! $request->expectsJson()) {
                return redirect('/')->with('error', 'Sesión no iniciada');
            }
            throw new AuthenticationException('Unauthenticated.', ['sanctum']);
        }
    }
}
