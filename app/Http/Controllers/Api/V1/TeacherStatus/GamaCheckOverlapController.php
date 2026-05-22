<?php

/**
 * @descripcion  Controlador invocable para consultar solapamiento de ausencias.
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
 * @creado       2026-05-18
 *
 * @modificado   2026-05-18
 *
 * @cambios      2026-05-18 - Creación inicial del controlador
 */

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\TeacherStatus;

use App\Http\Controllers\Controller;
use App\Http\Requests\TeacherStatus\CheckOverlapRequest;
use App\Http\Resources\TeacherStatus\TeacherAbsenceResource;
use App\Services\TeacherStatus\GamaTeacherAbsenceService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;

class GamaCheckOverlapController extends Controller
{
    use ApiResponse;

    public function __construct(
        private readonly GamaTeacherAbsenceService $service
    ) {}

    public function __invoke(CheckOverlapRequest $request): JsonResponse
    {
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
