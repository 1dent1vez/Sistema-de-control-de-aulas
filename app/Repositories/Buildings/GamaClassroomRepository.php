<?php

/**
 * @descripcion  Repositorio de aulas que encapsula el acceso a datos de gama_classrooms.
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

namespace App\Repositories\Buildings;

use App\Models\Classroom;
use App\Repositories\Contracts\ClassroomRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class GamaClassroomRepository implements ClassroomRepositoryInterface
{
    public function all(): Collection
    {
        return Classroom::whereHas('building')->with(['building', 'level', 'activeQr'])->get();
    }

    public function findById(int $id): ?Classroom
    {
        return Classroom::with(['building', 'level', 'activeQr'])->find($id);
    }

    public function findByBuildingId(int $buildingId): Collection
    {
        return Classroom::where('building_id', $buildingId)->whereHas('building')->with(['level', 'activeQr'])->get();
    }

    public function create(array $data): Classroom
    {
        return Classroom::create($data);
    }

    public function update(Classroom $classroom, array $data): Classroom
    {
        $classroom->update($data);

        return $classroom->fresh()->load(['building', 'level']);
    }

    public function delete(Classroom $classroom): bool
    {
        return $classroom->delete();
    }
}
