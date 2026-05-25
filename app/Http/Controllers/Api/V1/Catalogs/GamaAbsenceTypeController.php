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
use App\Services\Catalogs\GamaAbsenceTypeService;
use App\Traits\ApiResponse;
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

        return $this->success(
            AbsenceTypeResource::collection($absenceTypes),
            'Tipos de ausencia recuperados.'
        );
    }

    public function show(int $id): JsonResponse
    {
        $absenceType = $this->service->getById($id);

        if (! $absenceType) {
            return $this->error('Tipo de ausencia no encontrado.', 404);
        }

        return $this->success(
            new AbsenceTypeResource($absenceType),
            'Tipo de ausencia recuperado.'
        );
    }
}
