<?php

/**
 * @descripcion  Repositorio de semestres.
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

use App\Models\Semester;
use App\Repositories\Contracts\SemesterRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class GamaSemesterRepository implements SemesterRepositoryInterface
{
    public function all(): Collection
    {
        return Semester::withCount('classSchedules')
            ->orderBy('start_date', 'desc')
            ->get();
    }

    public function findById(int $id): ?Semester
    {
        return Semester::find($id);
    }

    public function getCurrent(): ?Semester
    {
        return Semester::current()->first();
    }

    public function hasOverlap(int $institutionId, string $startDate, string $endDate, ?int $excludeId = null): bool
    {
        $query = Semester::where('institution_id', $institutionId)
            ->where(function ($q) use ($startDate, $endDate): void {
                $q->whereBetween('start_date', [$startDate, $endDate])
                    ->orWhereBetween('end_date', [$startDate, $endDate])
                    ->orWhere(function ($q2) use ($startDate, $endDate): void {
                        $q2->where('start_date', '<=', $startDate)
                            ->where('end_date', '>=', $endDate);
                    });
            });

        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }

        return $query->exists();
    }

    public function create(array $data): Semester
    {
        return Semester::create($data);
    }

    public function update(Semester $semester, array $data): Semester
    {
        $semester->update($data);

        return $semester->fresh();
    }

    public function delete(Semester $semester): bool
    {
        return $semester->delete();
    }

    public function getExpired(): Collection
    {
        return Semester::whereDate('end_date', '<', now())->get();
    }
}
