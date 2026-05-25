<?php

/**
 * @descripcion  Controlador API para horarios.
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
 * @cambios      2026-05-18 - Refactorización: extraído import/report a GamaScheduleImportController
 */

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Schedules;

use App\Http\Controllers\Controller;
use App\Http\Requests\Schedules\StoreClassScheduleRequest;
use App\Http\Resources\Schedules\ClassScheduleResource;
use App\Models\ClassSchedule;
use App\Services\Schedules\GamaClassScheduleService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class GamaClassScheduleController extends Controller
{
    use ApiResponse;

    public function __construct(
        private readonly GamaClassScheduleService $service
    ) {}

    public function index(Request $request): JsonResponse
    {
        $filters = $request->only(['semester_id', 'classroom_id', 'teacher_external_id', 'building_id']);

        return $this->success(ClassScheduleResource::collection($this->service->getAll($filters)));
    }

    public function show(int $id): JsonResponse
    {
        $schedule = $this->service->getById($id);
        if (! $schedule) {
            return $this->error('Horario no encontrado.', 404);
        }

        return $this->success(new ClassScheduleResource($schedule));
    }

    public function store(StoreClassScheduleRequest $request): JsonResponse
    {
        $this->authorize('create', ClassSchedule::class);
        try {
            return $this->success(new ClassScheduleResource($this->service->create($request->validated())), 'Horario creado exitosamente.', 201);
        } catch (\RuntimeException $e) {
            return $this->error($e->getMessage(), 422);
        }
    }

    public function update(StoreClassScheduleRequest $request, int $id): JsonResponse
    {
        $this->authorize('update', ClassSchedule::class);
        try {
            $schedule = $this->service->update($id, $request->validated());
            if (! $schedule) {
                return $this->error('Horario no encontrado.', 404);
            }

            return $this->success(new ClassScheduleResource($schedule), 'Horario actualizado exitosamente.');
        } catch (\RuntimeException $e) {
            return $this->error($e->getMessage(), 422);
        }
    }

    public function destroy(int $id): JsonResponse
    {
        $this->authorize('delete', ClassSchedule::class);
        $deleted = $this->service->delete($id);
        if (! $deleted) {
            return $this->error('Horario no encontrado.', 404);
        }

        return $this->success(null, 'Horario eliminado exitosamente.');
    }
}
