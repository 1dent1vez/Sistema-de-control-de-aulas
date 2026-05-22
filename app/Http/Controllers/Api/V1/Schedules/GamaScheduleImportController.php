<?php

/**
 * @descripcion  Controlador invocable para importar horarios vía CSV.
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

namespace App\Http\Controllers\Api\V1\Schedules;

use App\Http\Controllers\Controller;
use App\Http\Requests\Schedules\ImportScheduleRequest;
use App\Jobs\ProcessScheduleImportJob;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;

class GamaScheduleImportController extends Controller
{
    use ApiResponse;

    public function __invoke(ImportScheduleRequest $request): JsonResponse
    {
        $file = $request->file('file');
        $semesterId = (int) $request->input('semester_id');
        $path = $file->store('imports');
        ProcessScheduleImportJob::dispatch($path, $file->getClientOriginalName(), $semesterId);

        return $this->success(['file' => $path], 'Import scheduled successfully. Check logs for results.');
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
