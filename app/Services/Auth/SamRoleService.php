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
 * @modificado   2026-05-25
 *
 * @cambios      2026-05-17 - Creación inicial del servicio de roles SAM
 *               2026-05-25 - Corrección: prevenir duplicación de email si external_id o query ya contiene arroba.
 */

declare(strict_types=1);

namespace App\Services\Auth;

use App\Enums\Auth\SamRole;
use App\Exceptions\SamOfflineException;
use App\Models\SamIdentity;
use App\Repositories\Contracts\SamIdentityRepositoryInterface;
use Illuminate\Support\Facades\DB;

class SamRoleService
{
    public function __construct(
        private readonly SamService $samService,
        private readonly SamIdentityRepositoryInterface $identityRepository
    ) {}

    /**
     * Lista todas las identidades SAM registradas.
     */
    public function listAll(): mixed
    {
        return $this->identityRepository->all();
    }

    /**
     * Busca identidades en SAM (actualmente en BD local).
     *
     * @return array<int, array{external_id: string, email: string, full_name: string|null, role: string}>
     */
    public function searchInSam(string $query): array
    {
        if (! config('sam.mock_enabled') && ! $this->samService->checkConnection()) {
            throw new SamOfflineException;
        }

        // TODO: Buscar contra SAM real vía endpoint de SAM cuando esté disponible
        $identities = $this->identityRepository->search($query);

        if ($identities->isNotEmpty()) {
            return $identities->toArray();
        }

        return [
            [
                'external_id' => $query,
                'email' => str_contains($query, '@') ? $query : $query.'@toluca.tecnm.mx',
                'full_name' => null,
                'role' => 'teacher',
            ],
        ];
    }

    /**
     * Asigna (o crea si no existe) un rol a una identidad en una transacción.
     */
    public function assignRole(string $externalId, SamRole $role): SamIdentity
    {
        if (! config('sam.mock_enabled') && ! $this->samService->checkConnection()) {
            throw new SamOfflineException;
        }

        return DB::transaction(function () use ($externalId, $role) {
            $identity = $this->identityRepository->findByExternalId($externalId);

            if ($identity === null) {
                $identity = new SamIdentity;
                $identity->external_id = $externalId;
                $identity->email = str_contains($externalId, '@') ? $externalId : $externalId.'@toluca.tecnm.mx';
                $identity->full_name = null;
                $identity->role = $role;
                $identity->save();
            } else {
                $identity->role = $role;
                $identity->save();
            }

            // Revocar de inmediato todos los tokens Sanctum para forzar re-login
            $identity->tokens()->delete();

            return $identity;
        });
    }
}
