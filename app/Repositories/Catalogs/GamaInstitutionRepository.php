<?php

/**
 * @descripcion  Repositorio de instituciones que encapsula el acceso a datos de gama_institutions.
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

namespace App\Repositories\Catalogs;

use App\Models\Institution;
use App\Repositories\Contracts\InstitutionRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class GamaInstitutionRepository implements InstitutionRepositoryInterface
{
    public function all(): Collection
    {
        return Institution::all();
    }

    public function findById(int $id): ?Institution
    {
        return Institution::find($id);
    }

    public function create(array $data): Institution
    {
        return Institution::create($data);
    }

    public function update(Institution $institution, array $data): Institution
    {
        $institution->update($data);

        return $institution->fresh();
    }

    public function delete(Institution $institution): bool
    {
        return $institution->delete();
    }
}
