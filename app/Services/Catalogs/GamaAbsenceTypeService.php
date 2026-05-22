<?php

/**
 * @descripcion  Servicio de tipos de ausencia que contiene la lógica de negocio del catálogo.
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

use App\Models\AbsenceType;
use App\Repositories\Contracts\AbsenceTypeRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class GamaAbsenceTypeService
{
    public function __construct(
        private readonly AbsenceTypeRepositoryInterface $repository
    ) {}

    /**
     * Obtiene todos los tipos de ausencia activos.
     *
     * @return Collection<int, AbsenceType>
     */
    public function getAll(): Collection
    {
        return $this->repository->all();
    }

    /**
     * Busca un tipo de ausencia por su ID.
     */
    public function getById(int $id): ?AbsenceType
    {
        return $this->repository->findById($id);
    }
}
