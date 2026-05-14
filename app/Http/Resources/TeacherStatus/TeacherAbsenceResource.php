<?php

/**
 * @descripcion  JsonResource que transforma TeacherAbsence a camelCase.
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
 * @modificado   2026-05-14
 *
 * @cambios      2026-05-14 - Creación inicial del Resource
 */

declare(strict_types=1);

namespace App\Http\Resources\TeacherStatus;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TeacherAbsenceResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'teacherExternalId' => $this->teacher_external_id,
            'absenceTypeId' => $this->absence_type_id,
            'startDate' => $this->start_date,
            'endDate' => $this->end_date,
            'observations' => $this->observations,
            'isConfirmed' => $this->is_confirmed,
            'absenceType' => $this->whenLoaded('absenceType', fn () => [
                'id' => $this->absenceType->id,
                'name' => $this->absenceType->name,
                'code' => $this->absenceType->code,
            ]),
            'createdAt' => $this->created_at?->toISOString(),
            'updatedAt' => $this->updated_at?->toISOString(),
        ];
    }
}
