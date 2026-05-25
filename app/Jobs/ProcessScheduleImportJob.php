<?php

/**
 * @descripcion  Job para procesar importación de horarios en segundo plano.
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
 * @cambios      2026-05-13 - Creación inicial del Job
 */

declare(strict_types=1);

namespace App\Jobs;

use App\Services\Schedules\GamaScheduleImportService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Http\UploadedFile;
use Illuminate\Queue\InteractsWithQueue;

class ProcessScheduleImportJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable;

    public int $timeout = 600;

    public int $tries = 3;

    public function __construct(
        private readonly string $filePath,
        private readonly string $originalName,
        private readonly int $semesterId,
        private readonly string $batchId,
    ) {}

    public function handle(GamaScheduleImportService $importService): void
    {
        $file = new UploadedFile($this->filePath, $this->originalName);

        $result = $importService->import($file, $this->semesterId, $this->batchId);

        logger('Schedule import completed', [
            'batchId' => $this->batchId,
            'imported' => $result['imported'],
            'errors' => count($result['errors']),
            'report_path' => $result['report_path'],
        ]);
    }

    public function failed(\Throwable $exception): void
    {
        logger()->error("ProcessScheduleImportJob failed for semester ID: {$this->semesterId}, batch ID: {$this->batchId}", [
            'filePath' => $this->filePath,
            'originalName' => $this->originalName,
            'error' => $exception->getMessage(),
        ]);
    }
}
