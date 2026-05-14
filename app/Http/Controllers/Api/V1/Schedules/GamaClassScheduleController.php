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
 * @modificado   2026-05-13
 *
 * @cambios      2026-05-13 - Creación inicial del controlador
 */

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Schedules;

use App\Http\Controllers\Controller;
use App\Http\Requests\Schedules\ImportScheduleRequest;
use App\Http\Requests\Schedules\StoreClassScheduleRequest;
use App\Http\Resources\Schedules\ClassScheduleResource;
use App\Jobs\ProcessScheduleImportJob;
use App\Services\Schedules\GamaClassScheduleService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class GamaClassScheduleController extends Controller
{
    use ApiResponse;

    public function __construct(
        private readonly GamaClassScheduleService $service
    ) {}

    public function index(Request $request): JsonResponse
    {
        $filters = $request->only(['semester_id', 'classroom_id', 'teacher_external_id']);

        return $this->success(
            ClassScheduleResource::collection($this->service->getAll($filters)),
            'Class schedules retrieved successfully.'
        );
    }

    public function show(int $id): JsonResponse
    {
        $schedule = $this->service->getById($id);

        if (! $schedule) {
            return $this->error('Class schedule not found.', 404);
        }

        return $this->success(
            new ClassScheduleResource($schedule),
            'Class schedule retrieved successfully.'
        );
    }

    public function store(StoreClassScheduleRequest $request): JsonResponse
    {
        try {
            $schedule = $this->service->create($request->validated());

            return $this->success(
                new ClassScheduleResource($schedule),
                'Class schedule created successfully.',
                201
            );
        } catch (\RuntimeException $e) {
            return $this->error($e->getMessage(), 422);
        }
    }

    public function update(StoreClassScheduleRequest $request, int $id): JsonResponse
    {
        try {
            $schedule = $this->service->update($id, $request->validated());

            if (! $schedule) {
                return $this->error('Class schedule not found.', 404);
            }

            return $this->success(
                new ClassScheduleResource($schedule),
                'Class schedule updated successfully.'
            );
        } catch (\RuntimeException $e) {
            return $this->error($e->getMessage(), 422);
        }
    }

    public function destroy(int $id): JsonResponse
    {
        $deleted = $this->service->delete($id);

        if (! $deleted) {
            return $this->error('Class schedule not found.', 404);
        }

        return $this->success(null, 'Class schedule deleted successfully.');
    }

    public function import(ImportScheduleRequest $request): JsonResponse
    {
        $file = $request->file('file');
        $semesterId = (int) $request->input('semester_id');

        $path = $file->store('imports');

        ProcessScheduleImportJob::dispatch($path, $file->getClientOriginalName(), $semesterId);

        return $this->success(
            ['file' => $path],
            'Import scheduled successfully. Check logs for results.'
        );
    }

    public function report(string $batchId): JsonResponse
    {
        $path = "imports/{$batchId}.json";

        if (! Storage::disk('local')->exists($path)) {
            return $this->error('Report not found.', 404);
        }

        $content = json_decode(Storage::disk('local')->get($path), true);

        return $this->success($content, 'Import report retrieved successfully.');
    }
}
