<?php

/**
 * @descripcion  Repositorio de identidades SAM.
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
 * @cambios      2026-05-18 - Creación inicial del repositorio
 */

declare(strict_types=1);

namespace App\Repositories\Auth;

use App\Models\SamIdentity;
use App\Repositories\Contracts\SamIdentityRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class GamaSamIdentityRepository implements SamIdentityRepositoryInterface
{
    public function all(): LengthAwarePaginator
    {
        return SamIdentity::whereIn('role', \App\Enums\Auth\SamRole::values())
            ->orWhereNull('role')
            ->paginate(20);
    }

    public function findByEmail(string $email): ?SamIdentity
    {
        return SamIdentity::where('email', $email)->first();
    }

    public function findByExternalId(string $externalId): ?SamIdentity
    {
        return SamIdentity::where('external_id', $externalId)->first();
    }

    public function search(string $query): Collection
    {
        return SamIdentity::where(fn($q) => $q->whereIn('role', \App\Enums\Auth\SamRole::values())->orWhereNull('role'))
            ->where(fn($q) => $q->where('external_id', $query)
                ->orWhere('email', $query)
                ->orWhere('full_name', 'like', "%{$query}%")
            )->get();
    }

    public function create(array $data): SamIdentity
    {
        return SamIdentity::create($data);
    }

    public function update(SamIdentity $identity, array $data): SamIdentity
    {
        $identity->update($data);

        return $identity;
    }
}
