<?php

/**
 * @descripcion  Middleware de autenticación SAM — valida token Sanctum desde
 *               cabecera Authorization (Bearer) o cookie sam_token.
 *
 * @autor        Equipo GAMA
 *
 * @autorizador  Ruben Alejandro Nolasco Ruiz <correo@dominio.com>
 *
 * @prueba       Diego Miguel Hernandez Fabela
 *
 * @mantenimiento Ghael Garcia Manjarrez <ghael.engineer@gmail.com>
 *
 * @version      1.1.0
 *
 * @creado       2026-05-17
 *
 * @modificado   2026-05-19
 *
 * @cambios      2026-05-19 - Soporte para cookie sam_token (protección rutas web)
 */

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Laravel\Sanctum\PersonalAccessToken;

class SamAuthMiddleware
{
    public function handle(Request $request, Closure $next): mixed
    {
        $token = $request->bearerToken() ?? $request->cookie('sam_token');

        if ($token) {
            $accessToken = PersonalAccessToken::findToken($token);
            if ($accessToken) {
                Auth::login($accessToken->tokenable);

                return $next($request);
            }
        }

        if (! $request->expectsJson()) {
            return redirect('/');
        }

        throw new AuthenticationException('Unauthenticated.', ['sanctum']);
    }
}
