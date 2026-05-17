<?php

/**
 * @descripcion  JsonResource que transforma SamIdentity al perfil camelCase para la API.
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
 * @cambios      2026-05-17 - Creación inicial del Resource
 */

declare(strict_types=1);

namespace App\Http\Resources\Auth;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SamProfileResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'externalId' => $this->external_id,
            'fullName' => $this->full_name,
            'email' => $this->email,
            'position' => $this->position ?? null,
            'department' => $this->department ?? null,
            'building' => $this->building ?? null,
            'role' => $this->role?->value,
        ];
    }
}
