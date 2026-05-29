<?php

/**
 * @descripcion  Prueba de feature para validar las 4 reglas de negocio de RF-05 en semestres y horarios.
 *
 * @autor        Antigravity <support@google.com>
 *
 * @autorizador  Ruben Alejandro Nolasco Ruiz <correo@dominio.com>
 *
 * @prueba       Antigravity <support@google.com>
 *
 * @mantenimiento Antigravity <support@google.com>
 *
 * @version      1.1.0
 *
 * @creado       2026-05-25
 *
 * @modificado   2026-05-26
 *
 * @cambios      2026-05-25 - Creación inicial de la clase de pruebas para RF-05.
 *               2026-05-26 - Actualización de aserciones de mensajes de error en español para reflejar el comportamiento actual.
 */

declare(strict_types=1);

use App\Models\Building;
use App\Models\Classroom;
use App\Models\Institution;
use App\Models\Level;
use App\Models\Semester;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    $this->loginAsAdmin();
    $this->institution = Institution::create([
        'name' => 'Universidad Tecnológica GAMA',
        'code' => 'UTGAMA',
        'is_active' => true,
    ]);

    $this->building = Building::create([
        'institution_id' => $this->institution->institution_id,
        'name' => 'Edificio A',
        'level_count' => 1,
        'description' => 'Ciencias',
        'status' => true,
    ]);

    $this->level = Level::create([
        'name' => 'Planta Baja',
        'display_order' => 0,
    ]);

    $this->classroom = Classroom::create([
        'building_id' => $this->building->building_id,
        'level_id' => $this->level->level_id,
        'classroom_name' => 'Aula A-101',
        'classroom_type' => 'classroom',
        'status' => true,
    ]);
});

it('RF-05.1 rejects overlapping semester dates with exact error message', function (): void {
    Semester::create([
        'institution_id' => $this->institution->institution_id,
        'name' => 'Semester Active',
        'start_date' => now()->subDays(5)->format('Y-m-d'),
        'end_date' => now()->addDays(5)->format('Y-m-d'),
        'is_active' => true,
    ]);

    $response = $this->postJson('/api/v1/semesters', [
        'institution_id' => $this->institution->institution_id,
        'name' => 'Overlapping Semester',
        'start_date' => now()->subDays(2)->format('Y-m-d'),
        'end_date' => now()->addDays(10)->format('Y-m-d'),
    ]);

    $response->assertStatus(422)
        ->assertJsonPath('success', false)
        ->assertJsonPath('message', 'El periodo del semestre se solapa con un semestre vigente existente.');
});

it('RF-05.3 blocks schedules registry when no current semester is active', function (): void {
    // Create an expired semester (exists, but not active/vigente)
    $expiredSemester = Semester::create([
        'institution_id' => $this->institution->institution_id,
        'name' => 'Expired 2025',
        'start_date' => '2025-01-01',
        'end_date' => '2025-06-30',
        'is_active' => true,
    ]);

    $response = $this->postJson('/api/v1/class-schedules', [
        'semester_id' => $expiredSemester->semester_id,
        'classroom_id' => $this->classroom->classroom_id,
        'teacher_external_id' => 'SAM-12345',
        'subject_name' => 'Química',
        'group_name' => '1B',
        'weekday' => 'monday',
        'start_time' => '08:00',
        'end_time' => '09:00',
    ]);

    $response->assertStatus(422)
        ->assertJsonPath('success', false)
        ->assertJsonPath('errors.semester_id.0', 'No existe un semestre vigente. Cree un semestre antes de registrar horarios.');
});
