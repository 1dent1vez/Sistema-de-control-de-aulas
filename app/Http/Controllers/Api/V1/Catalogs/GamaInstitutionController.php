<?php

/**
 * @descripcion  Controlador API para el catálogo de instituciones.
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
 * @cambios      2026-05-13 - Creación inicial del controlador
 */

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Catalogs;

use App\Http\Controllers\Controller;
use App\Http\Requests\Catalogs\StoreInstitutionRequest;
use App\Http\Requests\Catalogs\UpdateInstitutionRequest;
use App\Http\Resources\Catalogs\InstitutionResource;
use App\Traits\ApiResponse;
use App\Services\Catalogs\GamaInstitutionService;
use Illuminate\Http\JsonResponse;

class GamaInstitutionController extends Controller
{
    use ApiResponse;

    public function __construct(
        private readonly GamaInstitutionService $service
    ) {}

    public function index(): JsonResponse
    {
        $institutions = $this->service->getAll();

        return $this->success(
            InstitutionResource::collection($institutions),
            'Institutions retrieved successfully.'
        );
    }

    public function show(int $id): JsonResponse
    {
        $institution = $this->service->getById($id);

        if (! $institution) {
            return $this->error('Institution not found.', 404);
        }

        return $this->success(
            new InstitutionResource($institution),
            'Institution retrieved successfully.'
        );
    }

    public function store(StoreInstitutionRequest $request): JsonResponse
    {
        $institution = $this->service->create($request->validated());

        return $this->success(
            new InstitutionResource($institution),
            'Institution created successfully.',
            201
        );
    }

    public function update(UpdateInstitutionRequest $request, int $id): JsonResponse
    {
        $institution = $this->service->update($id, $request->validated());

        if (! $institution) {
            return $this->error('Institution not found.', 404);
        }

        return $this->success(
            new InstitutionResource($institution),
            'Institution updated successfully.'
        );
    }

    public function destroy(int $id): JsonResponse
    {
        $deleted = $this->service->delete($id);

        if (! $deleted) {
            return $this->error('Institution not found.', 404);
        }

        return $this->success(null, 'Institution deleted successfully.');
    }
}
