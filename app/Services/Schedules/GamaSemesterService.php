<?php

/**
 * @descripcion  Servicio de semestres con validación de solapamiento.
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
 * @cambios      2026-05-13 - Creación inicial del servicio
 */

declare(strict_types=1);

namespace App\Services\Schedules;

use App\Models\Semester;
use App\Repositories\Contracts\SemesterRepositoryInterface;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;

class GamaSemesterService
{
    public function __construct(
        private readonly SemesterRepositoryInterface $repository
    ) {}

    public function getAll(): Collection
    {
        return $this->repository->all();
    }

    public function getById(int $id): ?Semester
    {
        return $this->repository->findById($id);
    }

    public function getCurrent(): ?Semester
    {
        return $this->repository->getCurrent();
    }

    public function create(array $data): Semester
    {
        if ($this->repository->hasOverlap($data['institution_id'], $data['start_date'], $data['end_date'])) {
            throw new \RuntimeException('El rango de fechas del semestre se solapa con otro semestre existente.');
        }

        return $this->repository->create($data);
    }

    public function update(int $id, array $data): ?Semester
    {
        $semester = $this->repository->findById($id);

        if (! $semester) {
            return null;
        }

        if (isset($data['start_date']) || isset($data['end_date'])) {
            $startDate = $data['start_date'] ?? Carbon::parse($semester->start_date)->format('Y-m-d');
            $endDate = $data['end_date'] ?? Carbon::parse($semester->end_date)->format('Y-m-d');

            if ($this->repository->hasOverlap($semester->institution_id, $startDate, $endDate, $id)) {
                throw new \RuntimeException('El rango de fechas se solapa con otro semestre existente.');
            }
        }

        return $this->repository->update($semester, $data);
    }

    public function delete(int $id): bool
    {
        $semester = $this->repository->findById($id);

        if (! $semester) {
            return false;
        }

        return $this->repository->delete($semester);
    }
}
