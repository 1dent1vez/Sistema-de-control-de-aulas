<?php

/**
 * @descripcion  Controlador API para ausencias de docentes.
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
 * @creado       2026-05-14
 *
 * @modificado   2026-05-18
 *
 * @cambios      2026-05-18 - Refactorización: compactación
 */

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\TeacherStatus;

use App\Http\Controllers\Controller;
use App\Http\Requests\TeacherStatus\StoreTeacherAbsenceRequest;
use App\Http\Requests\TeacherStatus\UpdateTeacherAbsenceRequest;
use App\Http\Resources\TeacherStatus\TeacherAbsenceResource;
use App\Models\TeacherAbsence;
use App\Services\TeacherStatus\GamaTeacherAbsenceService;
use App\Services\TeacherStatus\OverlapRequiredException;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class GamaTeacherAbsenceController extends Controller
{
    use ApiResponse;

    public function __construct(
        private readonly GamaTeacherAbsenceService $service
    ) {}

    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', TeacherAbsence::class);

        return $this->success(TeacherAbsenceResource::collection($this->service->getAll($request->only(['teacher_external_id', 'start_date', 'end_date']))));
    }

    public function show(int $id): JsonResponse
    {
        $absence = $this->service->getById($id);
        if (! $absence) {
            return $this->error('Teacher absence not found.', 404);
        }
        $this->authorize('view', $absence);

        return $this->success(new TeacherAbsenceResource($absence));
    }

    public function store(StoreTeacherAbsenceRequest $request): JsonResponse
    {
        $this->authorize('create', TeacherAbsence::class);

        try {
            return $this->created(new TeacherAbsenceResource($this->service->store(array_merge($request->validated(), ['teacher_external_id' => $request->user()->external_id]))));
        } catch (OverlapRequiredException $e) {
            return $this->error($e->getMessage(), 422, ['overlap' => $e->getOverlapDetails()]);
        } catch (\RuntimeException $e) {
            return $this->error($e->getMessage(), 422);
        }
    }

    public function update(UpdateTeacherAbsenceRequest $request, int $id): JsonResponse
    {
        $absence = $this->service->getById($id);
        if (! $absence) {
            return $this->error('Teacher absence not found.', 404);
        }
        $this->authorize('update', $absence);

        try {
            return $this->success(new TeacherAbsenceResource($this->service->update($id, array_merge($request->validated(), ['teacher_external_id' => $absence->teacher_external_id]))), 'Teacher absence updated successfully.');
        } catch (OverlapRequiredException $e) {
            return $this->error($e->getMessage(), 422, ['overlap' => $e->getOverlapDetails()]);
        } catch (\RuntimeException $e) {
            return $this->error($e->getMessage(), 422);
        }
    }

    public function destroy(int $id): JsonResponse
    {
        $absence = $this->service->getById($id);
        if (! $absence) {
            return $this->error('Teacher absence not found.', 404);
        }
        $this->authorize('delete', $absence);
        $this->service->delete($id);

        return $this->success(null, 'Teacher absence deleted successfully.');
    }
}
