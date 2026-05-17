<?php

/**
 * @descripcion  Servicio para búsqueda en SAM y asignación de roles locales.
 *              La búsqueda contra SAM se implementa cuando haya acceso real al servicio.
 *              Por ahora opera sobre sam_identities local.
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
 * @creado       2026-05-17
 *
 * @modificado   2026-05-17
 *
 * @cambios      2026-05-17 - Creación inicial del servicio de roles SAM
 */

declare(strict_types=1);

namespace App\Services\Auth;

use App\Enums\Auth\SamRole;
use App\Models\SamIdentity;
use Illuminate\Support\Facades\DB;

class SamRoleService
{
    public function __construct(
        private readonly SamService $samService
    ) {}

    public function searchInSam(string $query): array
    {
        // TODO: Buscar contra SAM real vía endpoint de SAM cuando esté disponible
        $identities = SamIdentity::where('external_id', $query)
            ->orWhere('email', $query)
            ->orWhere('full_name', 'like', "%{$query}%")
            ->get();

        if ($identities->isNotEmpty()) {
            return $identities->toArray();
        }

        return [
            [
                'external_id' => $query,
                'email' => $query.'@toluca.tecnm.mx',
                'full_name' => null,
                'role' => 'teacher',
            ],
        ];
    }

    public function assignRole(string $externalId, SamRole $role): SamIdentity
    {
        return DB::transaction(function () use ($externalId, $role) {
            $identity = SamIdentity::where('external_id', $externalId)->first();

            if ($identity === null) {
                $identity = SamIdentity::create([
                    'external_id' => $externalId,
                    'email' => $externalId.'@toluca.tecnm.mx',
                    'full_name' => null,
                    'role' => $role,
                ]);
            } else {
                $identity->update(['role' => $role]);
            }

            return $identity;
        });
    }
}
