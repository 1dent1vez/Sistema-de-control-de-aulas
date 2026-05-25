<?php

/**
 * @descripcion  Middleware que verifica que el usuario autenticado tenga un rol específico.
 *
 * @autor        Ghael Garcia Manjarrez <ghael.engineer@gmail.com>
 *
 * @autorizador  Ruben Alejandro Nolasco Ruiz <correo@dominio.com>
 *
 * @prueba       Ghael Garcia Manjarrez <ghael.engineer@gmail.com>
 *
 * @mantenimiento Ghael Garcia Manjarrez <ghael.engineer@gmail.com>
 *
 * @version      1.0.1
 *
 * @creado       2026-05-21
 *
 * @modificado   2026-05-25
 *
 * @cambios      2026-05-21 - Creación inicial del middleware
 *               2026-05-25 - Corrección del tipo de comparación del rol contra string y formateo de prólogo
 */

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Enums\Auth\SamRole;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    public function handle(Request $request, Closure $next, string $role): Response
    {
        $user = $request->user();

        if (! $user || ! $user->role || $user->role->value !== $role) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json([
                    'success' => false,
                    'statusCode' => 403,
                    'message' => 'No tienes permisos para acceder a esta sección.',
                ], 403);
            }

            if ($user && $user->role) {
                return match ($user->role) {
                    SamRole::ADMIN => redirect()->route('admin.dashboard')->with('error', 'No tienes permisos para acceder a esta sección.'),
                    SamRole::TEACHER => redirect()->route('docente.dashboard')->with('error', 'No tienes permisos para acceder a esta sección.'),
                    default => redirect()->route('espera.rol')->with('error', 'No tienes permisos para acceder a esta sección.'),
                };
            }

            return redirect()->route('espera.rol')->with('error', 'No tienes permisos para acceder a esta sección.');
        }

        return $next($request);
    }
}
