<?php

/**
 * @descripcion  Servicio de horarios con validación de empalme.
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

use App\Models\ClassSchedule;
use App\Repositories\Contracts\ClassScheduleRepositoryInterface;
use App\Repositories\Contracts\SemesterRepositoryInterface;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;

class GamaClassScheduleService
{
    public function __construct(
        private readonly ClassScheduleRepositoryInterface $repository,
        private readonly SemesterRepositoryInterface $semesterRepository,
    ) {}

    /**
     * Obtiene todos los horarios, opcionalmente filtrados.
     *
     * @param  array<string, mixed>  $filters
     * @return Collection<int, ClassSchedule>
     */
    public function getAll(array $filters = []): Collection
    {
        return $this->repository->all($filters);
    }

    /**
     * Busca un horario por su ID.
     */
    public function getById(int $id): ?ClassSchedule
    {
        return $this->repository->findById($id);
    }

    /**
     * Crea un horario con validación de empalme.
     *
     * @param  array<string, mixed>  $data
     *
     * @throws \RuntimeException Si el semestre no existe o hay empalme de horario
     */
    public function create(array $data): ClassSchedule
    {
        $semester = $this->semesterRepository->findById((int) $data['semester_id']);

        if (! $semester) {
            throw new \RuntimeException('El semestre seleccionado no existe.');
        }

        if ($this->repository->hasOverlap(
            (int) $data['classroom_id'],
            $data['weekday'],
            $data['start_time'],
            $data['end_time']
        )) {
            throw new \RuntimeException('El horario se empalma con otro existente en el mismo salón y día.');
        }

        return $this->repository->create($data);
    }

    /**
     * Actualiza un horario existente con validación de empalme.
     *
     * @param  array<string, mixed>  $data
     *
     * @throws \RuntimeException Si hay empalme de horario
     */
    public function update(int $id, array $data): ?ClassSchedule
    {
        $schedule = $this->repository->findById($id);

        if (! $schedule) {
            return null;
        }

        $classroomId = (int) ($data['classroom_id'] ?? $schedule->classroom_id);
        $weekday = $data['weekday'] ?? $schedule->weekday;
        $startTime = $data['start_time'] ?? Carbon::parse($schedule->start_time)->format('H:i');
        $endTime = $data['end_time'] ?? Carbon::parse($schedule->end_time)->format('H:i');

        if ($this->repository->hasOverlap($classroomId, $weekday, $startTime, $endTime, $id)) {
            throw new \RuntimeException('El horario se empalma con otro existente en el mismo salón y día.');
        }

        return $this->repository->update($schedule, $data);
    }

    /**
     * Elimina (soft delete) un horario.
     */
    public function delete(int $id): bool
    {
        $schedule = $this->repository->findById($id);

        if (! $schedule) {
            return false;
        }

        return $this->repository->delete($schedule);
    }
}
