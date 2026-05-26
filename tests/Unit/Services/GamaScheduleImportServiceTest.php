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
 * @version      1.3.0
 *
 * @creado       2026-05-24
 *
 * @modificado   2026-05-26
 *
 * @cambios      2026-05-24 - Creación de pruebas unitarias para el servicio de importación
 *               2026-05-25 - Actualización de columnas a español, validaciones de solapamiento y semestre vigente
 *               2026-05-26 - Adición de prueba para verificar el manejo de excepciones de BD y formateo de errores UNIQUE.
 *               2026-05-26 - Actualización de aserciones de mensajes de error en español para reflejar el comportamiento actual de la importación.
 */

declare(strict_types=1);

namespace Tests\Unit\Services;

use App\Enums\Auth\SamRole;
use App\Models\Building;
use App\Models\Classroom;
use App\Models\Institution;
use App\Models\Level;
use App\Models\SamIdentity;
use App\Models\Semester;
use App\Repositories\Contracts\ClassroomRepositoryInterface;
use App\Repositories\Contracts\ClassScheduleRepositoryInterface;
use App\Services\Schedules\GamaScheduleImportService;
use Illuminate\Database\QueryException;
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
        'classroom_name' => 'A-101',
    ]);

    // Crear docentes válidos locales
    $teacher1 = new SamIdentity;
    $teacher1->external_id = 'TCH-001';
    $teacher1->email = 'tch001@toluca.tecnm.mx';
    $teacher1->full_name = 'Teacher One';
    $teacher1->role = SamRole::TEACHER;
    $teacher1->save();

    $teacher2 = new SamIdentity;
    $teacher2->external_id = 'TCH-002';
    $teacher2->email = 'tch002@toluca.tecnm.mx';
    $teacher2->full_name = 'Teacher Two';
    $teacher2->role = SamRole::TEACHER;
    $teacher2->save();

    $teacher3 = new SamIdentity;
    $teacher3->external_id = 'TCH-003';
    $teacher3->email = 'tch003@toluca.tecnm.mx';
    $teacher3->full_name = 'Teacher Three';
    $teacher3->role = SamRole::TEACHER;
    $teacher3->save();

    Storage::fake('local');
});

it('can import valid schedules from csv', function (): void {
    $batchId = Str::uuid()->toString();

    // Crear CSV con columnas en español
    $csvContent = "aula,docente,materia,grupo,dias,hora_inicio,hora_fin\n".
                  "A-101,TCH-001,Mathematics,Group A,\"Lunes, Miércoles\",08:00,10:00\n".
                  "A-101,TCH-002,Physics,Group B,Martes,10:00,12:00\n";

    $tempFile = tempnam(sys_get_temp_dir(), 'test_import_');
    file_put_contents($tempFile, $csvContent);

    $file = new UploadedFile($tempFile, 'schedules.csv', 'text/csv', null, true);

    $result = $this->service->import($file, $this->semester->id, $batchId);

    // Se importaron 3 registros (2 días para la fila 1 y 1 día para la fila 2)
    expect($result['imported'])->toBe(3)
        ->and($result['errors'])->toHaveCount(2);

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

    // Fila 2 válida, Fila 3 aula inválida, Fila 4 día inválido
    $csvContent = "aula,docente,materia,grupo,dias,hora_inicio,hora_fin\n".
                  "A-101,TCH-001,Mathematics,Group A,Lunes,08:00,10:00\n".
                  "AULA_INEXISTENTE,TCH-002,Physics,Group B,Martes,10:00,12:00\n".
                  "A-101,TCH-003,Chemistry,Group C,invalid_day,09:00,11:00\n";

    $tempFile = tempnam(sys_get_temp_dir(), 'test_import_');
    file_put_contents($tempFile, $csvContent);

    $file = new UploadedFile($tempFile, 'schedules.csv', 'text/csv', null, true);

    $result = $this->service->import($file, $this->semester->id, $batchId);

    expect($result['imported'])->toBe(1)
        ->and($result['errors'])->toHaveCount(3); // 1 exitoso + 2 descartados

    // Fila 3 descartada por aula inexistente
    $discardedRow3 = collect($result['errors'])->firstWhere('row', 3);
    expect($discardedRow3['ok'])->toBeFalse()
        ->and($discardedRow3['error'])->toContain('El aula seleccionada no existe o no esta disponible.');

    // Fila 4 descartada por día inválido
    $discardedRow4 = collect($result['errors'])->firstWhere('row', 4);
    expect($discardedRow4['ok'])->toBeFalse()
        ->and($discardedRow4['error'])->toContain('El dia de la semana no es valido. Use: Lunes, Martes, Miercoles, Jueves, Viernes, Sabado o Domingo.');

    Storage::disk('local')->assertExists("imports/{$batchId}.json");
});

it('rejects csv with missing columns', function (): void {
    $batchId = Str::uuid()->toString();

    // Falta columna 'dias'
    $csvContent = "aula,docente,materia,grupo,hora_inicio,hora_fin\n".
                  "A-101,TCH-001,Mathematics,Group A,08:00,10:00\n";

    $tempFile = tempnam(sys_get_temp_dir(), 'test_import_');
    file_put_contents($tempFile, $csvContent);

    $file = new UploadedFile($tempFile, 'schedules_missing.csv', 'text/csv', null, true);

    $result = $this->service->import($file, $this->semester->id, $batchId);

    expect($result['imported'])->toBe(0)
        ->and($result['errors'])->toHaveCount(1)
        ->and($result['errors'][0]['error'])->toContain('El archivo no tiene las columnas requeridas. Descargue la plantilla de ejemplo.');
});

it('handles corrupt file gracefully', function (): void {
    $batchId = Str::uuid()->toString();

    $csvContent = "\xFF\xFE\x00\x00corrupt_binary_data";

    $tempFile = tempnam(sys_get_temp_dir(), 'test_import_');
    file_put_contents($tempFile, $csvContent);

    $file = new UploadedFile($tempFile, 'corrupt.xlsx', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', null, true);

    $result = $this->service->import($file, $this->semester->id, $batchId);

    expect($result['imported'])->toBe(0)
        ->and($result['errors'])->toHaveCount(1)
        ->and($result['errors'][0]['error'])->toContain('El archivo esta danado o no se puede leer. Vuelva a cargar el archivo.');
});

it('handles database query exceptions gracefully by returning a clean error report without exposing SQL details', function (): void {
    $batchId = Str::uuid()->toString();

    $mockRepo = mock(ClassScheduleRepositoryInterface::class);
    $mockRepo->shouldReceive('insertMultiple')->andThrow(new QueryException(
        'sqlite',
        'insert into gama_class_schedules ...',
        [],
        new \Exception('UNIQUE constraint failed: test')
    ));

    $service = new GamaScheduleImportService(app(ClassroomRepositoryInterface::class), $mockRepo);

    $csvContent = "aula,docente,materia,grupo,dias,hora_inicio,hora_fin\n".
                  "A-101,TCH-001,Mathematics,Group A,Lunes,08:00,10:00\n";

    $tempFile = tempnam(sys_get_temp_dir(), 'test_import_');
    file_put_contents($tempFile, $csvContent);

    $file = new UploadedFile($tempFile, 'schedules.csv', 'text/csv', null, true);

    $result = $service->import($file, $this->semester->id, $batchId);

    expect($result['imported'])->toBe(0)
        ->and($result['errors'])->toHaveCount(1)
        ->and($result['errors'][0]['error'])->toBe('El registro que intenta crear ya existe en el sistema.');
});
