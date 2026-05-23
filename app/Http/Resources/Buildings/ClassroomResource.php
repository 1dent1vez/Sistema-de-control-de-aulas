<?php

/**
 * @descripcion  JsonResource que transforma Classroom a la estructura JSON canónica.
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
 * @creado       2026-05-13
 *
 * @modificado   2026-05-13
 *
 * @cambios      2026-05-13 - Creación inicial del Resource
 */

declare(strict_types=1);

namespace App\Http\Resources\Buildings;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ClassroomResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'buildingId' => $this->building_id,
            'buildingName' => $this->building?->name ?? null,
            'levelId' => $this->level_id,
            'levelName' => $this->level?->name ?? null,
            'classroomName' => $this->classroom_name,
            'classroomType' => $this->classroom_type,
            'classroomTypeLabel' => $this->classroom_type?->label(),
            'isActive' => (bool) $this->status,
            'status' => $this->status,
            'hasActiveQr' => $this->relationLoaded('activeQr') ? ! is_null($this->activeQr) : $this->activeQr()->exists(),
            'level' => new LevelResource($this->whenLoaded('level')),
            'createdAt' => $this->created_at?->toISOString(),
            'updatedAt' => $this->updated_at?->toISOString(),
        ];
    }
}
