<?php

/**
 * @descripcion  Interfaz del repositorio de niveles.
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
 * @cambios      2026-05-13 - Creación inicial de la interfaz
 */

declare(strict_types=1);

namespace App\Repositories\Contracts;

use App\Models\Level;
use Illuminate\Database\Eloquent\Collection;

interface LevelRepositoryInterface
{
    public function findByBuildingId(int $buildingId): Collection;

    public function create(array $data): Level;

    public function insertMultiple(array $data): bool;
}
