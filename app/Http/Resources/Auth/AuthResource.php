<?php

/**
 * @descripcion  JsonResource para la respuesta de login exitoso.
 *              Retorna accessToken, tokenType, expiresAt, role, redirectUrl, user.
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
 * @modificado   2026-05-24
 *
 * @cambios      2026-05-17 - Creación inicial del Resource
 *               2026-05-24 - Adición de usingMockFallback para control en contingencia.
 */

declare(strict_types=1);

namespace App\Http\Resources\Auth;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AuthResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'accessToken' => $this['accessToken'],
            'tokenType' => $this['tokenType'],
            'expiresAt' => $this['expiresAt'],
            'role' => $this['role'],
            'needsRole' => $this['needsRole'] ?? false,
            'redirectUrl' => $this['redirectUrl'],
            'usingMockFallback' => $this['using_mock_fallback'] ?? false,
            'using_mock_fallback' => $this['using_mock_fallback'] ?? false,
            'user' => $this['user'],
        ];
    }
}
