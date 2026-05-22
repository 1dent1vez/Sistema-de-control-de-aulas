<?php

/**
 * @descripcion  Controlador API para administración de identidades SAM.
 *              Métodos: search, assignRole, index. Todos requieren rol admin.
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
 * @cambios      2026-05-17 - Creación inicial del controlador
 */

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Auth;

use App\Enums\Auth\SamRole;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\AssignRoleRequest;
use App\Http\Resources\Auth\SamProfileResource;
use App\Models\SamIdentity;
use App\Services\Auth\SamRoleService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SamIdentityController extends Controller
{
    use ApiResponse;

    public function __construct(
        private readonly SamRoleService $samRoleService
    ) {}

    public function index(): JsonResponse
    {
        $this->authorize('viewAny', SamIdentity::class);
        $identities = $this->samRoleService->listAll();

        return $this->success(
            SamProfileResource::collection($identities),
            'Identidades recuperadas exitosamente.'
        );
    }

    public function search(Request $request): JsonResponse
    {
        $this->authorize('viewAny', SamIdentity::class);
        $query = $request->query('q', '');
        if (empty($query)) {
            return $this->error('El parámetro de búsqueda es obligatorio.', 422);
        }

        $results = $this->samRoleService->searchInSam($query);

        return $this->success($results, 'Resultados de búsqueda.');
    }

    public function assignRole(string $externalId, AssignRoleRequest $request): JsonResponse
    {
        $this->authorize('create', SamIdentity::class);
        $role = SamRole::from($request->input('role'));
        $identity = $this->samRoleService->assignRole($externalId, $role);

        return $this->success(
            new SamProfileResource($identity),
            'Rol asignado exitosamente.',
            200
        );
    }
}
