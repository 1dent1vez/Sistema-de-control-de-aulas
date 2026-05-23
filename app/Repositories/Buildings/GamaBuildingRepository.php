<?php

/**
 * @descripcion  Repositorio de edificios que encapsula el acceso a datos de gama_buildings.
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

use App\Models\Building;
use App\Repositories\Contracts\BuildingRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class GamaBuildingRepository implements BuildingRepositoryInterface
{
    public function all(?int $institutionId = null): Collection
    {
        $query = Building::with('levels');

        if ($institutionId !== null) {
            $query->where('institution_id', $institutionId);
        }

        return $query->get();
    }

    public function findById(int $id): ?Building
    {
        return Building::with('levels')->find($id);
    }

    public function create(array $data): Building
    {
        return Building::create($data);
    }

    public function update(Building $building, array $data): Building
    {
        $building->update($data);

        return $building->fresh()->load('levels');
    }

    public function delete(Building $building): bool
    {
        return $building->delete();
    }
}
