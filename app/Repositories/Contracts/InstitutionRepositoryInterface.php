<?php

/**
 * @descripcion  Interfaz del repositorio de instituciones.
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

use App\Models\Institution;
use Illuminate\Database\Eloquent\Collection;

interface InstitutionRepositoryInterface
{
    public function all(): Collection;

    public function findById(int $id): ?Institution;

    public function create(array $data): Institution;

    public function update(Institution $institution, array $data): Institution;

    public function delete(Institution $institution): bool;
}
