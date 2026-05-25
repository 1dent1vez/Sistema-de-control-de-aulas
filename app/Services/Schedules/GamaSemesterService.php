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
use Illuminate\Support\Facades\Log;

class GamaSemesterService
{
    public function __construct(
        private readonly SemesterRepositoryInterface $repository
    ) {}

    /**
     * Obtiene todos los semestres activos.
     *
     * @return Collection<int, Semester>
     */
    public function getAll(): Collection
    {
        return $this->repository->all();
    }

    /**
     * Busca un semestre por su ID.
     */
    public function getById(int $id): ?Semester
    {
        return $this->repository->findById($id);
    }

    /**
     * Obtiene el semestre activo más reciente.
     */
    public function getCurrent(): ?Semester
    {
        return $this->repository->getCurrent();
    }

    /**
     * Crea un semestre con validación de solapamiento.
     *
     * @param  array<string, mixed>  $data
     *
     * @throws \RuntimeException Si el rango de fechas se solapa con otro semestre
     */
    public function create(array $data): Semester
    {
        try {
            $hasOverlap = $this->repository->hasOverlap($data['institution_id'], $data['start_date'], $data['end_date']);
        } catch (\Exception $e) {
            Log::error('Fallo de BD al verificar solapamiento: '.$e->getMessage());
            throw new \RuntimeException('No se pudo determinar el semestre activo');
        }

        if ($hasOverlap) {
            throw new \RuntimeException('El período se solapa con un semestre vigente');
        }

        return $this->repository->create($data);
    }

    /**
     * Actualiza un semestre existente con validación de solapamiento.
     *
     * @param  array<string, mixed>  $data
     *
     * @throws \RuntimeException Si el nuevo rango se solapa con otro semestre
     */
    public function update(int $id, array $data): ?Semester
    {
        $semester = $this->repository->findById($id);

        if (! $semester) {
            return null;
        }

        if (isset($data['start_date']) || isset($data['end_date'])) {
            $startDate = $data['start_date'] ?? Carbon::parse($semester->start_date)->format('Y-m-d');
            $endDate = $data['end_date'] ?? Carbon::parse($semester->end_date)->format('Y-m-d');

            try {
                $hasOverlap = $this->repository->hasOverlap($semester->institution_id, $startDate, $endDate, $id);
            } catch (\Exception $e) {
                Log::error('Fallo de BD al verificar solapamiento: '.$e->getMessage());
                throw new \RuntimeException('No se pudo determinar el semestre activo');
            }

            if ($hasOverlap) {
                throw new \RuntimeException('El período se solapa con un semestre vigente');
            }
        }

        return $this->repository->update($semester, $data);
    }

    /**
     * Elimina (soft delete) un semestre.
     */
    public function delete(int $id): bool
    {
        $semester = $this->repository->findById($id);

        if (! $semester) {
            return false;
        }

        return $this->repository->delete($semester);
    }

    /**
     * Obtiene el semestre vigente comparando la fecha actual del servidor.
     *
     * @throws \RuntimeException Si hay un fallo de BD o anomalía lógica.
     */
    public function obtenerSemestreVigente(): ?Semester
    {
        try {
            $today = now()->format('Y-m-d');
            $semesters = Semester::vigente($today)->get();

            if ($semesters->count() > 1) {
                Log::critical('Error crítico: Existe más de un semestre vigente simultáneamente.');

                return $semesters->first();
            }

            return $semesters->first();
        } catch (\Exception $e) {
            Log::error('Error de BD al determinar el semestre vigente: '.$e->getMessage());
            throw new \RuntimeException('DB_ERROR');
        }
    }
}
