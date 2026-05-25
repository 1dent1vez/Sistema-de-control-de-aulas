<?php

/**
 * @descripcion  Controlador invocable para obtener estadísticas de ausencias docentes.
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
 * @creado       2026-05-25
 *
 * @modificado   2026-05-25
 *
 * @cambios      2026-05-25 - Creación inicial del controlador de estadísticas.
 */

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\TeacherStatus;

use App\Http\Controllers\Controller;
use App\Models\TeacherAbsence;
use App\Services\TeacherStatus\GamaTeacherAbsenceService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class GamaTeacherAbsenceStatsController extends Controller
{
    use ApiResponse;

    public function __construct(
        private readonly GamaTeacherAbsenceService $service
    ) {}

    public function __invoke(Request $request): JsonResponse
    {
        $this->authorize('viewAny', TeacherAbsence::class);

        $filters = $request->only(['teacher_external_id']);
        $stats = $this->service->getStats($filters, $request->user());

        return $this->success($stats, 'Estadísticas de ausencias obtenidas exitosamente.');
    }
}
