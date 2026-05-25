<?php

/**
 * @descripcion  Pruebas unitarias para GamaScheduleImportService.
 *
 * @autor        Agente OpenCode
 *
 * @autorizador  Ruben Alejandro Nolasco Ruiz <correo@dominio.com>
 *
 * @prueba       Agente OpenCode
 *
 * @mantenimiento Agente OpenCode
 *
 * @version      1.0.0
 *
 * @creado       2026-05-24
 *
 * @modificado   2026-05-24
 *
 * @cambios      2026-05-24 - Creación de pruebas unitarias para el servicio de importación
 */

declare(strict_types=1);

namespace Tests\Unit\Services;

use App\Models\Building;
use App\Models\Classroom;
use App\Models\Institution;
use App\Models\Level;
use App\Models\Semester;
use App\Services\Schedules\GamaScheduleImportService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    $this->service = app(GamaScheduleImportService::class);
    $this->institution = Institution::factory()->create();
    $this->semester = Semester::factory()->create(['institution_id' => $this->institution->id]);
    $this->building = Building::factory()->create(['institution_id' => $this->institution->id]);
    $this->level = Level::factory()->create(['building_id' => $this->building->id]);
    $this->classroom = Classroom::factory()->create([
        'building_id' => $this->building->id,
        'level_id' => $this->level->id,
    ]);
    Storage::fake('local');
});

it('can import valid schedules from csv', function (): void {
    $batchId = Str::uuid()->toString();

    // Create a temporary CSV
    $csvContent = "classroom_id,teacher_external_id,subject_name,group_name,weekday,start_time,end_time\n".
                  "{$this->classroom->id},TCH-001,Mathematics,Group A,monday,08:00,10:00\n".
                  "{$this->classroom->id},TCH-002,Physics,Group B,tuesday,10:00,12:00\n";

    $tempFile = tempnam(sys_get_temp_dir(), 'test_import_');
    file_put_contents($tempFile, $csvContent);

    $file = new UploadedFile($tempFile, 'schedules.csv', 'text/csv', null, true);

    $result = $this->service->import($file, $this->semester->id, $batchId);

    expect($result['imported'])->toBe(2)
        ->and($result['errors'])->toHaveCount(2); // Since we log all rows, there will be 2 rows (ok: true) in the report

    $this->assertDatabaseHas('gama_class_schedules', [
        'semester_id' => $this->semester->id,
        'classroom_id' => $this->classroom->id,
        'teacher_external_id' => 'TCH-001',
        'subject_name' => 'Mathematics',
        'weekday' => 'monday',
        'start_time' => '08:00',
        'end_time' => '10:00',
    ]);

    Storage::disk('local')->assertExists("imports/{$batchId}.json");
});

it('handles validation errors and logs them in the report', function (): void {
    $batchId = Str::uuid()->toString();

    // Row 2 is valid, Row 3 has invalid classroom, Row 4 has invalid weekday
    $csvContent = "classroom_id,teacher_external_id,subject_name,group_name,weekday,start_time,end_time\n".
                  "{$this->classroom->id},TCH-001,Mathematics,Group A,monday,08:00,10:00\n".
                  "99999,TCH-002,Physics,Group B,tuesday,10:00,12:00\n".
                  "{$this->classroom->id},TCH-003,Chemistry,Group C,invalid_day,09:00,11:00\n";

    $tempFile = tempnam(sys_get_temp_dir(), 'test_import_');
    file_put_contents($tempFile, $csvContent);

    $file = new UploadedFile($tempFile, 'schedules.csv', 'text/csv', null, true);

    $result = $this->service->import($file, $this->semester->id, $batchId);

    expect($result['imported'])->toBe(1)
        ->and($result['errors'])->toHaveCount(3); // 1 successful + 2 discarded

    // Row 3 should be discarded
    $discardedRow3 = collect($result['errors'])->firstWhere('row', 3);
    expect($discardedRow3['ok'])->toBeFalse()
        ->and($discardedRow3['error'])->toContain("classroom_id '99999' no existe");

    // Row 4 should be discarded
    $discardedRow4 = collect($result['errors'])->firstWhere('row', 4);
    expect($discardedRow4['ok'])->toBeFalse()
        ->and($discardedRow4['error'])->toContain("weekday 'invalid_day' no válido");

    Storage::disk('local')->assertExists("imports/{$batchId}.json");
});

it('rejects csv with missing columns', function (): void {
    $batchId = Str::uuid()->toString();

    // Missing 'weekday' column
    $csvContent = "classroom_id,teacher_external_id,subject_name,group_name,start_time,end_time\n".
                  "{$this->classroom->id},TCH-001,Mathematics,Group A,08:00,10:00\n";

    $tempFile = tempnam(sys_get_temp_dir(), 'test_import_');
    file_put_contents($tempFile, $csvContent);

    $file = new UploadedFile($tempFile, 'schedules_missing.csv', 'text/csv', null, true);

    $result = $this->service->import($file, $this->semester->id, $batchId);

    expect($result['imported'])->toBe(0)
        ->and($result['errors'])->toHaveCount(1)
        ->and($result['errors'][0]['error'])->toContain('Columnas faltantes');
});

it('handles corrupt file gracefully', function (): void {
    $batchId = Str::uuid()->toString();

    // Binary / Corrupt file simulator
    $csvContent = "\xFF\xFE\x00\x00corrupt_binary_data";

    $tempFile = tempnam(sys_get_temp_dir(), 'test_import_');
    file_put_contents($tempFile, $csvContent);

    $file = new UploadedFile($tempFile, 'corrupt.xlsx', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', null, true);

    $result = $this->service->import($file, $this->semester->id, $batchId);

    expect($result['imported'])->toBe(0)
        ->and($result['errors'])->toHaveCount(1)
        ->and($result['errors'][0]['error'])->toContain('Archivo dañado');
});
