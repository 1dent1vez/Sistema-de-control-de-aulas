<?php

/**
 * @descripcion  Pruebas unitarias para el Job ProcessScheduleImportJob.
 *
 * @autor        Agente OpenCode
 *
 * @autorizador  Ruben Alejandro Nolasco Ruiz <correo@dominio.com>
 *
 * @prueba       Agente OpenCode
 *
 * @mantenimiento Agente OpenCode
 *
 * @version      1.1.0
 *
 * @creado       2026-05-24
 *
 * @modificado   2026-05-25
 *
 * @cambios      2026-05-24 - Creación de pruebas unitarias para el Job de importación
 *               2026-05-25 - Actualización de las expectativas de Mockery para soportar la ruta del archivo como string
 */

declare(strict_types=1);

namespace Tests\Unit\Jobs;

use App\Jobs\ProcessScheduleImportJob;
use App\Services\Schedules\GamaScheduleImportService;
use Mockery;

it('executes import service when job is handled', function (): void {
    $tempFile = tempnam(sys_get_temp_dir(), 'test_job_import');
    file_put_contents($tempFile, 'dummy data');

    $originalName = 'schedules.csv';
    $semesterId = 123;
    $batchId = 'test-uuid-batch';

    // Mock the Import Service
    $mockService = Mockery::mock(GamaScheduleImportService::class);
    $mockService->shouldReceive('import')
        ->once()
        ->with(
            Mockery::type('string'),
            $semesterId,
            $batchId,
            Mockery::any()
        )
        ->andReturn([
            'imported' => 5,
            'errors' => [],
            'report_path' => 'reports/test.json',
        ]);

    $job = new ProcessScheduleImportJob($tempFile, $originalName, $semesterId, $batchId);
    $job->handle($mockService);

    @unlink($tempFile);

    // Verify Mockery assertions
    Mockery::close();
    expect(true)->toBeTrue();
});
