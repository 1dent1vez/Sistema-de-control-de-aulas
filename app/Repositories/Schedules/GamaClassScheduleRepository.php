<?php

/**
 * @descripcion  Repositorio de horarios.
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
 * @cambios      2026-05-13 - Creación inicial del repositorio
 */

declare(strict_types=1);

namespace App\Repositories\Schedules;

use App\Models\ClassSchedule;
use App\Repositories\Contracts\ClassScheduleRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class GamaClassScheduleRepository implements ClassScheduleRepositoryInterface
{
    public function all(array $filters = []): Collection
    {
        $query = ClassSchedule::with(['semester', 'classroom.level']);

        if (! empty($filters['semester_id'])) {
            $query->where('semester_id', $filters['semester_id']);
        }

        if (! empty($filters['classroom_id'])) {
            $query->where('classroom_id', $filters['classroom_id']);
        }

        if (! empty($filters['teacher_external_id'])) {
            $query->where('teacher_external_id', $filters['teacher_external_id']);
        }

        if (! empty($filters['building_id'])) {
            $query->whereHas('classroom', function ($q) use ($filters): void {
                $q->where('building_id', $filters['building_id']);
            });
        }

        return $query->get();
    }

    public function findById(int $id): ?ClassSchedule
    {
        return ClassSchedule::with(['semester', 'classroom.level'])->find($id);
    }

    public function create(array $data): ClassSchedule
    {
        return ClassSchedule::create($data);
    }

    public function update(ClassSchedule $schedule, array $data): ClassSchedule
    {
        $schedule->update($data);

        return $schedule->fresh()->load(['semester', 'classroom.level']);
    }

    public function delete(ClassSchedule $schedule): bool
    {
        return $schedule->delete();
    }

    public function hasOverlap(int $classroomId, string $weekday, string $startTime, string $endTime, ?int $excludeId = null): bool
    {
        $query = ClassSchedule::where('classroom_id', $classroomId)
            ->where('weekday', $weekday)
            ->where('start_time', '<', $endTime)
            ->where('end_time', '>', $startTime);

        if ($excludeId) {
            $query->where('class_schedule_id', '!=', $excludeId);
        }

        return $query->exists();
    }

    public function insertMultiple(array $data): bool
    {
        return ClassSchedule::insert($data);
    }
}
