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
 * @version      1.3.0
 *
 * @creado       2026-05-14
 *
 * @modificado   2026-05-26
 *
 * @cambios      2026-05-14 - Creación inicial del controlador
 *               2026-05-25 - Refactorización para cumplir con el límite de 100 líneas y cargar relaciones.
 *               2026-05-26 - Estandarización y traducción de mensajes de error de la API en español.
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

    public function __construct(private readonly GamaTeacherAbsenceService $service) {}

    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', TeacherAbsence::class);
        $filters = $request->only(['teacher_external_id', 'start_date', 'end_date']);
        $absences = $this->service->getAll($filters, $request->user())->load('absenceType', 'classSchedules');

        return $this->success(TeacherAbsenceResource::collection($absences));
    }

    public function show(int $id): JsonResponse
    {
        $absence = $this->service->getById($id);
        if (! $absence) {
            return $this->error('El estado de docente solicitado no existe o fue eliminado.', 404);
        }
        $this->authorize('view', $absence);

        return $this->success(new TeacherAbsenceResource($absence->load('absenceType', 'classSchedules')));
    }

    public function store(StoreTeacherAbsenceRequest $request): JsonResponse
    {
        $this->authorize('create', TeacherAbsence::class);

        return $this->executeAction(function () use ($request) {
            $data = array_merge($request->validated(), ['teacher_external_id' => $request->user()->external_id]);

            return $this->created(new TeacherAbsenceResource($this->service->store($data)));
        });
    }

    public function update(UpdateTeacherAbsenceRequest $request, int $id): JsonResponse
    {
        $absence = $this->service->getById($id);
        if (! $absence) {
            return $this->error('El estado de docente solicitado no existe o fue eliminado.', 404);
        }
        $this->authorize('update', $absence);

        return $this->executeAction(function () use ($request, $id, $absence) {
            $data = array_merge($request->validated(), ['teacher_external_id' => $absence->teacher_external_id]);

            return $this->success(new TeacherAbsenceResource($this->service->update($id, $data)), 'Ausencia actualizada exitosamente.');
        });
    }

    public function destroy(int $id): JsonResponse
    {
        $absence = $this->service->getById($id);
        if (! $absence) {
            return $this->error('El estado de docente solicitado no existe o fue eliminado.', 404);
        }
        $this->authorize('delete', $absence);
        $this->service->delete($id);

        return $this->success(null, 'Ausencia eliminada exitosamente.');
    }

    private function executeAction(callable $action): JsonResponse
    {
        try {
            return $action();
        } catch (OverlapRequiredException $e) {
            return $this->error($e->getMessage(), 422, ['overlap' => $e->getOverlapDetails()]);
        } catch (\Throwable $e) {
            return $this->error($e->getMessage(), 422);
        }
    }
}
