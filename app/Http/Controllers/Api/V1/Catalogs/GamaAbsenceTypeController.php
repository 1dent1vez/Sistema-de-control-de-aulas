<?php

/**
 * @descripcion  Controlador API para el catálogo de tipos de ausencia.
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

namespace App\Http\Controllers\Api\V1\Catalogs;

use App\Http\Controllers\Controller;
use App\Http\Resources\Catalogs\AbsenceTypeResource;
use App\Http\Traits\ApiResponse;
use App\Services\Catalogs\GamaAbsenceTypeService;
use Illuminate\Http\JsonResponse;

class GamaAbsenceTypeController extends Controller
{
    use ApiResponse;

    public function __construct(
        private readonly GamaAbsenceTypeService $service
    ) {}

    public function index(): JsonResponse
    {
        $absenceTypes = $this->service->getAll();

        return $this->successResponse(
            AbsenceTypeResource::collection($absenceTypes),
            'Absence types retrieved successfully.'
        );
    }

    public function show(int $id): JsonResponse
    {
        $absenceType = $this->service->getById($id);

        if (! $absenceType) {
            return $this->notFoundResponse('Absence type not found.');
        }

        return $this->successResponse(
            new AbsenceTypeResource($absenceType),
            'Absence type retrieved successfully.'
        );
    }
}
