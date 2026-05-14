<?php

/**
 * @descripcion  Controlador API para el módulo de edificios.
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

namespace App\Http\Controllers\Api\V1\Buildings;

use App\Http\Controllers\Controller;
use App\Http\Requests\Buildings\StoreBuildingRequest;
use App\Http\Requests\Buildings\UpdateBuildingRequest;
use App\Http\Resources\Buildings\BuildingResource;
use App\Http\Resources\Buildings\LevelResource;
use App\Services\Buildings\GamaBuildingService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;

class GamaBuildingController extends Controller
{
    use ApiResponse;

    public function __construct(
        private readonly GamaBuildingService $service
    ) {}

    public function index(): JsonResponse
    {
        return $this->success(
            BuildingResource::collection($this->service->getAll()),
            'Buildings retrieved successfully.'
        );
    }

    public function show(int $id): JsonResponse
    {
        $building = $this->service->getById($id);

        if (! $building) {
            return $this->error('Building not found.', 404);
        }

        return $this->success(
            new BuildingResource($building),
            'Building retrieved successfully.'
        );
    }

    public function store(StoreBuildingRequest $request): JsonResponse
    {
        $building = $this->service->store($request->validated());

        return $this->success(
            new BuildingResource($building),
            'Building created successfully.',
            201
        );
    }

    public function update(UpdateBuildingRequest $request, int $id): JsonResponse
    {
        $building = $this->service->update($id, $request->validated());

        if (! $building) {
            return $this->error('Building not found.', 404);
        }

        return $this->success(
            new BuildingResource($building),
            'Building updated successfully.'
        );
    }

    public function destroy(int $id): JsonResponse
    {
        $deleted = $this->service->delete($id);

        if (! $deleted) {
            return $this->error('Building not found.', 404);
        }

        return $this->success(null, 'Building deleted successfully.');
    }

    public function levels(int $buildingId): JsonResponse
    {
        $levels = $this->service->getLevels($buildingId);

        return $this->success(
            LevelResource::collection($levels),
            'Levels retrieved successfully.'
        );
    }
}
