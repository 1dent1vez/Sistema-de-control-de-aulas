<?php

/**
 * @descripcion  JsonResource que transforma SamEmployee al perfil camelCase para la API.
 *
 * @autor        Antigravity <support@google.com>
 *
 * @autorizador  Ruben Alejandro Nolasco Ruiz <correo@dominio.com>
 *
 * @prueba       Antigravity <support@google.com>
 *
 * @mantenimiento Antigravity <support@google.com>
 *
 * @version      1.0.0
 *
 * @creado       2026-05-24
 *
 * @modificado   2026-05-24
 */

declare(strict_types=1);

namespace App\Http\Resources\Auth;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SamEmployeeResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'externalId' => (string) $this->id_empleado,
            'external_id' => (string) $this->id_empleado,
            'nombre' => $this->nombre,
            'apellidoPa' => $this->apellidoPa,
            'apellidoMa' => $this->apellidoMa,
            'fullName' => trim(($this->nombre ?? '').' '.($this->apellidoPa ?? '').' '.($this->apellidoMa ?? '')),
            'usuario' => $this->usuario,
            'correo' => $this->correo,
            'email' => $this->correo,
        ];
    }
}
