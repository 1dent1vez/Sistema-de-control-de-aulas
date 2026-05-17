<?php

/**
 * @descripcion  Controlador API para semestres.
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
use App\Http\Requests\Schedules\StoreSemesterRequest;
use App\Http\Resources\Schedules\SemesterResource;
use App\Models\Semester;
use App\Services\Schedules\GamaSemesterService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;

class GamaSemesterController extends Controller
{
    use ApiResponse;

    public function __construct(
        private readonly GamaSemesterService $service
    ) {}

    public function index(): JsonResponse
    {
        return $this->success(
            SemesterResource::collection($this->service->getAll()),
            'Semesters retrieved successfully.'
        );
    }

    public function current(): JsonResponse
    {
        $semester = $this->service->getCurrent();

        if (! $semester) {
            return $this->error('No active semester found.', 404);
        }

        return $this->success(
            new SemesterResource($semester),
            'Current semester retrieved successfully.'
        );
    }

    public function show(int $id): JsonResponse
    {
        $semester = $this->service->getById($id);

        if (! $semester) {
            return $this->error('Semester not found.', 404);
        }

        return $this->success(
            new SemesterResource($semester),
            'Semester retrieved successfully.'
        );
    }

    public function store(StoreSemesterRequest $request): JsonResponse
    {
        $this->authorize('create', Semester::class);
        try {
            $semester = $this->service->create($request->validated());

            return $this->success(
                new SemesterResource($semester),
                'Semester created successfully.',
                201
            );
        } catch (\RuntimeException $e) {
            return $this->error($e->getMessage(), 422);
        }
    }

    public function update(StoreSemesterRequest $request, int $id): JsonResponse
    {
        $this->authorize('update', Semester::class);
        try {
            $semester = $this->service->update($id, $request->validated());

            if (! $semester) {
                return $this->error('Semester not found.', 404);
            }

            return $this->success(
                new SemesterResource($semester),
                'Semester updated successfully.'
            );
        } catch (\RuntimeException $e) {
            return $this->error($e->getMessage(), 422);
        }
    }

    public function destroy(int $id): JsonResponse
    {
        $this->authorize('delete', Semester::class);
        $deleted = $this->service->delete($id);

        if (! $deleted) {
            return $this->error('Semester not found.', 404);
        }

        return $this->success(null, 'Semester deleted successfully.');
    }
}
