<?php

/**
 * @descripcion  Servicio de ausencias de docentes con validación de traslapes.
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
 * @cambios      2026-05-14 - Creación inicial del servicio
 */

declare(strict_types=1);

namespace App\Services\TeacherStatus;

use App\Models\TeacherAbsence;
use App\Repositories\Contracts\TeacherAbsenceRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class GamaTeacherAbsenceService
{
    public function __construct(
        private readonly TeacherAbsenceRepositoryInterface $repository
    ) {}

    public function getAll(array $filters = []): Collection
    {
        return $this->repository->all($filters);
    }

    public function getById(int $id): ?TeacherAbsence
    {
        return $this->repository->findById($id);
    }

    public function checkOverlap(string $teacherExternalId, string $startDate, string $endDate, ?int $excludeId = null): Collection
    {
        return $this->repository->findOverlappingAbsences($teacherExternalId, $startDate, $endDate, $excludeId);
    }

    public function store(array $data): TeacherAbsence
    {
        $startDate = $data['start_date'];
        $endDate = $data['end_date'];
        $teacherExternalId = $data['teacher_external_id'];

        if ($endDate < now()->format('Y-m-d')) {
            throw new \RuntimeException('La ausencia no puede estar completamente en el pasado.');
        }

        $overlaps = $this->repository->findOverlappingAbsences($teacherExternalId, $startDate, $endDate);

        if ($overlaps->isNotEmpty() && empty($data['is_confirmed'])) {
            throw new OverlapRequiredException(
                'El rango de fechas se traslapa con ausencias existentes.',
                $overlaps
            );
        }

        if (! isset($data['is_confirmed'])) {
            $data['is_confirmed'] = false;
        }

        return $this->repository->create($data);
    }

    public function update(int $id, array $data): ?TeacherAbsence
    {
        $absence = $this->repository->findById($id);

        if (! $absence) {
            return null;
        }

        $startDate = $data['start_date'] ?? $absence->start_date->format('Y-m-d');

        if ($startDate < now()->format('Y-m-d')) {
            throw new \RuntimeException('No se puede modificar una ausencia que ya inició.');
        }

        $teacherExternalId = $data['teacher_external_id'] ?? $absence->teacher_external_id;
        $endDate = $data['end_date'] ?? $absence->end_date->format('Y-m-d');

        $overlaps = $this->repository->findOverlappingAbsences($teacherExternalId, $startDate, $endDate, $id);

        if ($overlaps->isNotEmpty() && empty($data['is_confirmed'])) {
            throw new OverlapRequiredException(
                'El rango de fechas se traslapa con ausencias existentes.',
                $overlaps
            );
        }

        return $this->repository->update($absence, $data);
    }

    public function delete(int $id): bool
    {
        $absence = $this->repository->findById($id);

        if (! $absence) {
            return false;
        }

        return $this->repository->delete($absence);
    }
}
