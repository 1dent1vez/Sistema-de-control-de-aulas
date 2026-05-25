<?php

/**
 * @descripcion  Prueba de integración: Flujo académico y registro de ausencias.
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
 * @modificado   2026-05-14
 *
 * @cambios      2026-05-14 - Creación de la prueba de integración
 */

declare(strict_types=1);

use App\Models\AbsenceType;
use App\Models\Building;
use App\Models\Classroom;
use App\Models\Institution;
use App\Models\Level;
use Carbon\Carbon;

it('executes the academic and teacher absence flow correctly', function () {
    $this->loginAsAdmin('TEACHER-INT');
    // 1. Preparar infraestructura base
    $institution = Institution::factory()->create();
    $building = Building::factory()->create(['institution_id' => $institution->id]);
    $level = Level::factory()->create(['building_id' => $building->id]);
    $classroom = Classroom::factory()->create([
        'building_id' => $building->id,
        'level_id' => $level->id,
    ]);
    $absenceType = AbsenceType::factory()->create();

    // 2. Crear un Semestre vigente
    $response = $this->postJson('/api/v1/semesters', [
        'institution_id' => $institution->id,
        'name' => 'Semestre de Pruebas',
        'start_date' => Carbon::now()->subDays(10)->format('Y-m-d'),
        'end_date' => Carbon::now()->addDays(90)->format('Y-m-d'),
        'status' => true,
    ]);

    $response->assertStatus(201);
    $semesterId = $response->json('data.id');

    // 3. Asignar un horario (3 días de hoy, de 08:00 a 10:00)
    $dynamicDay = strtolower(Carbon::now()->addDays(3)->format('l'));
    $response = $this->postJson('/api/v1/class-schedules', [
        'semester_id' => $semesterId,
        'classroom_id' => $classroom->id,
        'teacher_external_id' => 'TEACHER-INT',
        'subject_name' => 'Física Cuántica',
        'group_name' => 'Q1',
        'weekday' => $dynamicDay,
        'start_time' => '08:00',
        'end_time' => '10:00',
    ]);

    $response->assertStatus(201);

    // 4. Intentar asignar horario que se empalma en la misma aula
    $response = $this->postJson('/api/v1/class-schedules', [
        'semester_id' => $semesterId,
        'classroom_id' => $classroom->id,
        'teacher_external_id' => 'TEACHER-OTHER',
        'subject_name' => 'Química',
        'group_name' => 'Q2',
        'weekday' => $dynamicDay,
        'start_time' => '09:00',
        'end_time' => '11:00',
    ]);

    // Validar que el sistema lo rechaza por empalme
    $response->assertStatus(422);
    $this->assertStringContainsString('empalma', $response->json('message'));

    // 5. Registrar ausencia (que se traslapa con otra ausencia - Simulando lógica de validación)
    // Primero creamos una ausencia
    $this->postJson('/api/v1/teacher-absences', [
        'teacher_external_id' => 'TEACHER-INT',
        'absence_type_id' => $absenceType->id,
        'start_date' => Carbon::now()->addDays(1)->format('Y-m-d'),
        'end_date' => Carbon::now()->addDays(5)->format('Y-m-d'),
    ])->assertStatus(201);

    // Intentamos empalmar otra ausencia
    $response = $this->postJson('/api/v1/teacher-absences', [
        'teacher_external_id' => 'TEACHER-INT',
        'absence_type_id' => $absenceType->id,
        'start_date' => Carbon::now()->addDays(3)->format('Y-m-d'),
        'end_date' => Carbon::now()->addDays(7)->format('Y-m-d'),
    ]);

    $response->assertStatus(422);
    $this->assertStringContainsString('traslapa', $response->json('message'));

    // 6. Confirmar la ausencia (bypass del traslape)
    $response = $this->postJson('/api/v1/teacher-absences', [
        'teacher_external_id' => 'TEACHER-INT',
        'absence_type_id' => $absenceType->id,
        'start_date' => Carbon::now()->addDays(3)->format('Y-m-d'),
        'end_date' => Carbon::now()->addDays(7)->format('Y-m-d'),
        'is_confirmed' => true,
    ]);

    $response->assertStatus(201);
    $this->assertTrue($response->json('data.isConfirmed'));
});
