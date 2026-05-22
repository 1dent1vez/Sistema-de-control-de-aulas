<?php

/**
 * @descripcion  Servicio de instituciones que contiene la lógica de negocio del catálogo.
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

namespace App\Services\Catalogs;

use App\Models\Institution;
use App\Repositories\Contracts\InstitutionRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class GamaInstitutionService
{
    public function __construct(
        private readonly InstitutionRepositoryInterface $repository
    ) {}

    /**
     * Obtiene todas las instituciones activas.
     *
     * @return Collection<int, Institution>
     */
    public function getAll(): Collection
    {
        return $this->repository->all();
    }

    /**
     * Busca una institución por su ID.
     */
    public function getById(int $id): ?Institution
    {
        return $this->repository->findById($id);
    }

    /**
     * Crea una nueva institución.
     *
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): Institution
    {
        return $this->repository->create($data);
    }

    /**
     * Actualiza una institución existente.
     *
     * @param  array<string, mixed>  $data
     */
    public function update(int $id, array $data): ?Institution
    {
        $institution = $this->repository->findById($id);

        if (! $institution) {
            return null;
        }

        return $this->repository->update($institution, $data);
    }

    /**
     * Elimina (soft delete) una institución.
     */
    public function delete(int $id): bool
    {
        $institution = $this->repository->findById($id);

        if (! $institution) {
            return false;
        }

        return $this->repository->delete($institution);
    }
}
