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
 * @version      1.1.0
 *
 * @creado       2026-05-13
 *
 * @modificado   2026-05-26
 *
 * @cambios      2026-05-18 - Refactorización: compactación y uso de created()
 *               2026-05-26 - Actualización de mensajes de error de la API de edificios según requerimientos.
 */

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Buildings;

use App\Http\Controllers\Controller;
use App\Http\Requests\Buildings\StoreBuildingRequest;
use App\Http\Requests\Buildings\UpdateBuildingRequest;
use App\Http\Resources\Buildings\BuildingResource;
use App\Http\Resources\Buildings\LevelResource;
use App\Models\Building;
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
        return $this->success(BuildingResource::collection($this->service->getAll()));
    }

    public function show(int $id): JsonResponse
    {
        $building = $this->service->getById($id);
        if (! $building) {
            return $this->error('El edificio solicitado no existe o no esta registrado en el sistema.', 404);
        }

        return $this->success(new BuildingResource($building));
    }

    public function store(StoreBuildingRequest $request): JsonResponse
    {
        $this->authorize('create', Building::class);

        return $this->created(new BuildingResource($this->service->store($request->validated())));
    }

    public function update(UpdateBuildingRequest $request, int $id): JsonResponse
    {
        $this->authorize('update', Building::class);
        $building = $this->service->update($id, $request->validated());
        if (! $building) {
            return $this->error('El edificio solicitado no existe o no esta registrado en el sistema.', 404);
        }

        return $this->success(new BuildingResource($building));
    }

    public function destroy(int $id): JsonResponse
    {
        $this->authorize('delete', Building::class);
        $deleted = $this->service->delete($id);
        if (! $deleted) {
            return $this->error('El edificio solicitado no existe o no esta registrado en el sistema.', 404);
        }

        return $this->success(null, 'Edificio eliminado exitosamente.');
    }

    public function levels(int $buildingId): JsonResponse
    {
        return $this->success(LevelResource::collection($this->service->getLevels($buildingId)));
    }
}
