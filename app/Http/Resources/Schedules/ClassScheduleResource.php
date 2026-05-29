<?php

/**
 * @descripcion  JsonResource que transforma ClassSchedule a camelCase.
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

namespace App\Http\Resources\Schedules;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ClassScheduleResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->class_schedule_id,
            'semesterId' => $this->semester_id,
            'classroomId' => $this->classroom_id,
            'teacherExternalId' => $this->teacher_external_id,
            'subjectName' => $this->subject_name,
            'groupName' => $this->group_name,
            'groupLabel' => $this->group_name,
            'weekday' => $this->weekday,
            'startTime' => $this->start_time,
            'endTime' => $this->end_time,
            'isActive' => (bool) $this->status,
            'status' => $this->status,
            'classroom' => $this->whenLoaded('classroom', fn () => [
                'id' => $this->classroom->classroom_id,
                'classroomName' => $this->classroom->classroom_name,
                'classroomType' => $this->classroom->classroom_type,
            ]),
            'createdAt' => $this->created_at?->toISOString(),
            'updatedAt' => $this->updated_at?->toISOString(),
        ];
    }
}
