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
 * @modificado   2026-05-14
 *
 * @cambios      2026-05-14 - Creación inicial del controlador
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
        $filters = $request->only(['teacher_external_id', 'start_date', 'end_date']);

        return $this->success(
            TeacherAbsenceResource::collection($this->service->getAll($filters)),
            'Teacher absences retrieved successfully.'
        );
    }

    public function show(int $id): JsonResponse
    {
        $absence = $this->service->getById($id);

        if (! $absence) {
            return $this->error('Teacher absence not found.', 404);
        }

        $this->authorize('view', $absence);

        return $this->success(
            new TeacherAbsenceResource($absence),
            'Teacher absence retrieved successfully.'
        );
    }

    public function store(StoreTeacherAbsenceRequest $request): JsonResponse
    {
        $this->authorize('create', TeacherAbsence::class);
        try {
            $data = array_merge($request->validated(), [
                'teacher_external_id' => $request->user()->external_id,
            ]);
            $absence = $this->service->store($data);

            return $this->success(
                new TeacherAbsenceResource($absence),
                'Teacher absence created successfully.',
                201
            );
        } catch (OverlapRequiredException $e) {
            return response()->json([
                'success' => false,
                'statusCode' => 422,
                'message' => $e->getMessage(),
                'data' => null,
                'errors' => ['overlap' => $e->getOverlapDetails()],
            ], 422);
        } catch (\RuntimeException $e) {
            return $this->error($e->getMessage(), 422);
        }
    }

    public function update(UpdateTeacherAbsenceRequest $request, int $id): JsonResponse
    {
        $absenceModel = $this->service->getById($id);
        if (! $absenceModel) {
            return $this->error('Teacher absence not found.', 404);
        }
        $this->authorize('update', $absenceModel);
        try {
            $data = array_merge($request->validated(), [
                'teacher_external_id' => $absenceModel->teacher_external_id,
            ]);
            $absence = $this->service->update($id, $data);

            if (! $absence) {
                return $this->error('Teacher absence not found.', 404);
            }

            return $this->success(
                new TeacherAbsenceResource($absence),
                'Teacher absence updated successfully.'
            );
        } catch (OverlapRequiredException $e) {
            return response()->json([
                'success' => false,
                'statusCode' => 422,
                'message' => $e->getMessage(),
                'data' => null,
                'errors' => ['overlap' => $e->getOverlapDetails()],
            ], 422);
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
        $deleted = $this->service->delete($id);

        if (! $deleted) {
            return $this->error('Teacher absence not found.', 404);
        }

        return $this->success(null, 'Teacher absence deleted successfully.');
    }

    public function checkOverlap(Request $request): JsonResponse
    {
        $request->validate([
            'teacher_external_id' => 'required|string|max:50',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
        ]);

        $overlaps = $this->service->checkOverlap(
            $request->input('teacher_external_id'),
            $request->input('start_date'),
            $request->input('end_date'),
        );

        return $this->success([
            'hasOverlap' => $overlaps->isNotEmpty(),
            'overlaps' => TeacherAbsenceResource::collection($overlaps),
        ], 'Overlap check completed.');
    }
}
