<?php

/**
 * @descripcion  Servicio de aulas que contiene la lógica de negocio del catálogo.
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

namespace App\Services\Buildings;

use App\Models\Classroom;
use App\Repositories\Contracts\ClassroomRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class GamaClassroomService
{
    public function __construct(
        private readonly ClassroomRepositoryInterface $repository
    ) {}

    /**
     * Obtiene todas las aulas activas.
     *
     * @return Collection<int, Classroom>
     */
    public function getAll(): Collection
    {
        return $this->repository->all();
    }

    /**
     * Busca un aula por su ID.
     */
    public function getById(int $id): ?Classroom
    {
        return $this->repository->findById($id);
    }

    /**
     * Busca aulas por ID de edificio.
     *
     * @return Collection<int, Classroom>
     */
    public function getByBuildingId(int $buildingId): Collection
    {
        return $this->repository->findByBuildingId($buildingId);
    }

    /**
     * Crea un aula nueva.
     *
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): Classroom
    {
        return DB::transaction(function () use ($data): Classroom {
            return $this->repository->create($data);
        });
    }

    /**
     * Actualiza un aula existente.
     *
     * @param  array<string, mixed>  $data
     */
    public function update(int $id, array $data): ?Classroom
    {
        $classroom = $this->repository->findById($id);

        if (! $classroom) {
            return null;
        }

        return $this->repository->update($classroom, $data);
    }

    /**
     * Elimina (soft delete) un aula.
     */
    public function delete(int $id): bool
    {
        $classroom = $this->repository->findById($id);

        if (! $classroom) {
            return false;
        }

        return $this->repository->delete($classroom);
    }
}
