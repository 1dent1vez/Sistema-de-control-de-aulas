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
 * @modificado   2026-05-18
 *
 * @cambios      2026-05-18 - Refactorización: compactación y uso de created()
 */

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Catalogs;

use App\Http\Controllers\Controller;
use App\Http\Requests\Catalogs\StoreInstitutionRequest;
use App\Http\Requests\Catalogs\UpdateInstitutionRequest;
use App\Http\Resources\Catalogs\InstitutionResource;
use App\Models\Institution;
use App\Services\Catalogs\GamaInstitutionService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;

class GamaInstitutionController extends Controller
{
    use ApiResponse;

    public function __construct(
        private readonly GamaInstitutionService $service
    ) {}

    public function index(): JsonResponse
    {
        return $this->success(InstitutionResource::collection($this->service->getAll()));
    }

    public function show(int $id): JsonResponse
    {
        $institution = $this->service->getById($id);
        if (! $institution) {
            return $this->error('Institution not found.', 404);
        }

        return $this->success(new InstitutionResource($institution));
    }

    public function store(StoreInstitutionRequest $request): JsonResponse
    {
        $this->authorize('create', Institution::class);

        return $this->created(new InstitutionResource($this->service->create($request->validated())));
    }

    public function update(UpdateInstitutionRequest $request, int $id): JsonResponse
    {
        $this->authorize('update', Institution::class);
        $institution = $this->service->update($id, $request->validated());
        if (! $institution) {
            return $this->error('Institution not found.', 404);
        }

        return $this->success(new InstitutionResource($institution));
    }

    public function destroy(int $id): JsonResponse
    {
        $this->authorize('delete', Institution::class);
        $deleted = $this->service->delete($id);
        if (! $deleted) {
            return $this->error('Institution not found.', 404);
        }

        return $this->success(null, 'Institution deleted successfully.');
    }
}
