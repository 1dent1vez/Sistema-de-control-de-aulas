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
 * @version      1.3.0
 *
 * @creado       2026-05-18
 *
 * @modificado   2026-05-26
 *
 * @cambios      2026-05-18 - Creación inicial del controlador
 *               2026-05-25 - Adición de validación de rol de administrador y doble validación defensiva de extensión de archivo (.csv o .xlsx).
 *               2026-05-26 - Separación del flujo en Preview y Confirmación, y actualización de mensajes de error de horarios.
 */

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Schedules;

use App\Enums\Auth\SamRole;
use App\Http\Controllers\Controller;
use App\Http\Requests\Schedules\ImportScheduleRequest;
use App\Jobs\ProcessScheduleImportJob;
use App\Models\ClassSchedule;
use App\Services\Schedules\GamaScheduleImportService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class GamaScheduleImportController extends Controller
{
    use ApiResponse;

    public function __construct(
        private readonly GamaScheduleImportService $importService
    ) {}

    public function __invoke(ImportScheduleRequest $request): JsonResponse
    {
        $this->authorize('create', ClassSchedule::class);

        if ($request->user()->role !== SamRole::ADMIN) {
            return $this->error('No tiene permisos para gestionar horarios. Contacte al administrador.', 403);
        }

        $file = $request->file('file');
        $extension = $file->getClientOriginalExtension();
        if (! in_array(strtolower($extension), ['csv', 'xlsx'], true)) {
            return $this->error('Solo se aceptan archivos con extension .csv o .xlsx.', 422);
        }

        $semesterId = (int) $request->input('semester_id');
        $batchId = Str::uuid()->toString();

        $path = $file->storeAs('imports', $batchId.'.'.$file->getClientOriginalExtension());
        ProcessScheduleImportJob::dispatchSync($path, $file->getClientOriginalName(), $semesterId, $batchId, false);

        return $this->success([
            'batchId' => $batchId,
            'file' => $path,
        ], 'La importación ha sido programada exitosamente.', 202);
    }

    public function report(string $batchId): JsonResponse
    {
        $this->authorize('create', ClassSchedule::class);

        if (request()->user()->role !== SamRole::ADMIN) {
            return $this->error('No tiene permisos para gestionar horarios. Contacte al administrador.', 403);
        }

        $path = "imports/{$batchId}.json";
        if (! Storage::disk('local')->exists($path)) {
            return $this->success(null, 'El archivo se está procesando. Por favor, espere...', 202);
        }
        $content = json_decode(Storage::disk('local')->get($path), true);

        return $this->success($content, 'Import report retrieved successfully.');
    }

    public function confirm(Request $request): JsonResponse
    {
        $this->authorize('create', ClassSchedule::class);

        if ($request->user()->role !== SamRole::ADMIN) {
            return $this->error('No tiene permisos para gestionar horarios. Contacte al administrador.', 403);
        }

        $batchId = $request->input('batch_id') ?? $request->input('batchId');
        if (! $batchId) {
            return $this->error('El identificador proporcionado no es valido.', 422);
        }

        try {
            $result = $this->importService->confirm((string) $batchId);

            return $this->success($result, 'La importación ha sido confirmada y guardada exitosamente.');
        } catch (\RuntimeException $e) {
            return $this->error($e->getMessage(), 404);
        } catch (\Exception $e) {
            Log::error('Error al confirmar importacion de horarios: '.$e->getMessage());

            return $this->error('Error al guardar en la base de datos. Intente nuevamente o contacte al administrador.', 500);
        }
    }
}
