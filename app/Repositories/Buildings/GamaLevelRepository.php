<?php

/**
 * @descripcion  Repositorio de niveles que encapsula el acceso a datos de gama_levels.
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

use App\Models\Level;
use App\Repositories\Contracts\LevelRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class GamaLevelRepository implements LevelRepositoryInterface
{
    public function findByBuildingId(int $buildingId): Collection
    {
        return Level::where('building_id', $buildingId)->orderBy('display_order')->get();
    }

    public function create(array $data): Level
    {
        return Level::create($data);
    }

    public function insertMultiple(array $data): bool
    {
        return Level::insert($data);
    }
}
