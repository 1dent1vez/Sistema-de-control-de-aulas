<?php

/**
 * @descripcion  Middleware alias para autenticación vía Sanctum (sam.auth).
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
 * @cambios      2026-05-17 - Creación inicial del middleware
 */

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\Request;

class SamAuthMiddleware
{
    public function handle(Request $request, Closure $next): mixed
    {
        if (! $request->user('sanctum')) {
            throw new AuthenticationException('Unauthenticated.', ['sanctum']);
        }

        return $next($request);
    }
}
