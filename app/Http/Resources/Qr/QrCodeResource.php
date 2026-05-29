<?php

/**
 * @descripcion  Resource JSON que transforma un modelo QrCode a snake_case → camelCase.
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
 * @creado       2026-05-14
 *
 * @modificado   2026-05-19
 *
 * @cambios      2026-05-19 - Estandarización de prólogo según formato GAMA
 */

declare(strict_types=1);

namespace App\Http\Resources\Qr;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class QrCodeResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->qr_id,
            'classroomId' => $this->classroom_id,
            'token' => $this->token,
            'payload' => $this->payload,
            'fileUrl' => $this->file_path ? url("api/v1/qr-codes/{$this->qr_id}/file") : null,
            'isActive' => $this->is_active,
            'generatedAt' => $this->generated_at?->toISOString(),
            'invalidatedAt' => $this->invalidated_at?->toISOString(),
            'createdAt' => $this->created_at?->toISOString(),
        ];
    }
}
