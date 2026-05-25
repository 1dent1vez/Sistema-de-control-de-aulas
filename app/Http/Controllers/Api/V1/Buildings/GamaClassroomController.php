<?php

/**
 * @descripcion  Controlador API para el módulo de aulas.
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

namespace App\Http\Controllers\Api\V1\Buildings;

use App\Http\Controllers\Controller;
use App\Http\Requests\Buildings\StoreClassroomRequest;
use App\Http\Requests\Buildings\UpdateClassroomRequest;
use App\Http\Resources\Buildings\ClassroomResource;
use App\Models\Classroom;
use App\Services\Buildings\GamaClassroomService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;

class GamaClassroomController extends Controller
{
    use ApiResponse;

    public function __construct(
        private readonly GamaClassroomService $service
    ) {}

    public function index(): JsonResponse
    {
        return $this->success(ClassroomResource::collection($this->service->getAll()));
    }

    public function show(int $id): JsonResponse
    {
        $classroom = $this->service->getById($id);
        if (! $classroom) {
            return $this->error('Aula no encontrada.', 404);
        }

        return $this->success(new ClassroomResource($classroom));
    }

    public function store(StoreClassroomRequest $request): JsonResponse
    {
        $this->authorize('create', Classroom::class);

        return $this->created(new ClassroomResource($this->service->create($request->validated())));
    }

    public function update(UpdateClassroomRequest $request, int $id): JsonResponse
    {
        $this->authorize('update', Classroom::class);
        $classroom = $this->service->update($id, $request->validated());
        if (! $classroom) {
            return $this->error('Aula no encontrada.', 404);
        }

        return $this->success(new ClassroomResource($classroom));
    }

    public function destroy(int $id): JsonResponse
    {
        $this->authorize('delete', Classroom::class);
        $deleted = $this->service->delete($id);
        if (! $deleted) {
            return $this->error('Aula no encontrada.', 404);
        }

        return $this->success(null, 'Aula eliminada exitosamente.');
    }

    public function byBuilding(int $buildingId): JsonResponse
    {
        return $this->success(ClassroomResource::collection($this->service->getByBuildingId($buildingId)));
    }
}
