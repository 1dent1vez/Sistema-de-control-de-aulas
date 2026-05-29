<?php

/**
 * @descripcion  Repositorio de ausencias de docentes.
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
 * @cambios      2026-05-14 - Creación inicial del repositorio
 */

declare(strict_types=1);

namespace App\Repositories\TeacherStatus;

use App\Models\TeacherAbsence;
use App\Repositories\Contracts\TeacherAbsenceRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class GamaTeacherAbsenceRepository implements TeacherAbsenceRepositoryInterface
{
    public function all(array $filters = []): Collection
    {
        $query = TeacherAbsence::with('absenceType');

        if (! empty($filters['teacher_external_id'])) {
            $query->where('teacher_external_id', $filters['teacher_external_id']);
        }

        if (! empty($filters['start_date'])) {
            $query->whereDate('start_date', '>=', $filters['start_date']);
        }

        if (! empty($filters['end_date'])) {
            $query->whereDate('end_date', '<=', $filters['end_date']);
        }

        return $query->orderBy('start_date', 'desc')->get();
    }

    public function findById(int $id): ?TeacherAbsence
    {
        return TeacherAbsence::with('absenceType')->find($id);
    }

    public function create(array $data): TeacherAbsence
    {
        return TeacherAbsence::create($data);
    }

    public function update(TeacherAbsence $absence, array $data): TeacherAbsence
    {
        $absence->update($data);

        return $absence->fresh()->load('absenceType');
    }

    public function delete(TeacherAbsence $absence): bool
    {
        return $absence->delete();
    }

    public function findOverlappingAbsences(string $teacherExternalId, string $startDate, string $endDate, ?int $excludeId = null): Collection
    {
        $query = TeacherAbsence::where('teacher_external_id', $teacherExternalId)
            ->where('start_date', '<=', $endDate)
            ->where('end_date', '>=', $startDate);

        if ($excludeId) {
            $query->where('teacher_absence_id', '!=', $excludeId);
        }

        return $query->get();
    }
}
