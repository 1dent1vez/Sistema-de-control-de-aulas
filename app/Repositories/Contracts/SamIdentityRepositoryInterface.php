<?php

/**
 * @descripcion  Interfaz del repositorio de identidades SAM.
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
 * @creado       2026-05-18
 *
 * @modificado   2026-05-18
 *
 * @cambios      2026-05-18 - Creación inicial de la interfaz
 */

declare(strict_types=1);

namespace App\Repositories\Contracts;

use App\Models\SamIdentity;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

interface SamIdentityRepositoryInterface
{
    public function all(): LengthAwarePaginator;

    public function findByEmail(string $email): ?SamIdentity;

    public function findByExternalId(string $externalId): ?SamIdentity;

    public function search(string $query): Collection;

    public function create(array $data): SamIdentity;

    public function update(SamIdentity $identity, array $data): SamIdentity;
}
