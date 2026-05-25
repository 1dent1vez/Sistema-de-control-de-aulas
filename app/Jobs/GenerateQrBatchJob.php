<?php

/**
 * @descripcion  Job encolado para la generación y descarga por lotes de códigos QR.
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
 * @creado       2026-05-14
 *
 * @modificado   2026-05-19
 *
 * @cambios      2026-05-19 - Estandarización de prólogo según formato GAMA
 */

declare(strict_types=1);

namespace App\Jobs;

use App\Services\Qr\GamaQrCodeService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Cache;

class GenerateQrBatchJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable;

    public function __construct(
        private readonly array $classroomIds,
        private readonly string $format,
        private readonly string $batchId,
    ) {}

    public function handle(GamaQrCodeService $service): void
    {
        Cache::put("qr_batch_{$this->batchId}", [
            'status' => 'processing',
            'progress' => 50,
        ], 600);

        try {
            $service->downloadBatch($this->classroomIds, $this->format, $this->batchId);

            Cache::put("qr_batch_{$this->batchId}", [
                'status' => 'completed',
                'progress' => 100,
                'downloadUrl' => url("api/v1/qr-codes/download/file/{$this->batchId}"),
            ], 600);
        } catch (\Throwable $e) {
            Cache::put("qr_batch_{$this->batchId}", [
                'status' => 'failed',
                'progress' => 0,
                'error' => $e->getMessage(),
            ], 600);
            throw $e;
        }
    }
}
