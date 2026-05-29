<?php

/**
 * @descripcion  Pruebas unitarias para GamaClassScheduleService.
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
 * @cambios      2026-05-14 - Creación de pruebas unitarias
 */

declare(strict_types=1);

use App\Models\Building;
use App\Models\Classroom;
use App\Models\ClassSchedule;
use App\Models\Institution;
use App\Models\Level;
use App\Models\Semester;
use App\Services\Schedules\GamaClassScheduleService;

beforeEach(function () {
    $this->service = app(GamaClassScheduleService::class);
    $this->institution = Institution::factory()->create();
    $this->semester = Semester::factory()->create(['institution_id' => $this->institution->institution_id]);
    $this->building = Building::factory()->create();
    $this->level = Level::factory()->create();
    $this->classroom = Classroom::factory()->create([
        'building_id' => $this->building->building_id,
        'level_id' => $this->level->level_id,
    ]);
});

it('can get all schedules', function () {
    ClassSchedule::factory()->create([
        'semester_id' => $this->semester->semester_id,
        'classroom_id' => $this->classroom->classroom_id,
    ]);
    ClassSchedule::factory()->create([
        'semester_id' => $this->semester->semester_id,
        'classroom_id' => $this->classroom->classroom_id,
    ]);

    $schedules = $this->service->getAll();

    expect($schedules)->toHaveCount(2);
});

it('can get schedule by id', function () {
    $schedule = ClassSchedule::factory()->create([
        'semester_id' => $this->semester->semester_id,
        'classroom_id' => $this->classroom->classroom_id,
    ]);

    $found = $this->service->getById($schedule->class_schedule_id);

    expect($found->class_schedule_id)->toBe($schedule->class_schedule_id);
});

it('can create schedule', function () {
    $data = [
        'semester_id' => $this->semester->semester_id,
        'classroom_id' => $this->classroom->classroom_id,
        'teacher_external_id' => 'TEACHER-001',
        'subject_name' => 'Math',
        'group_name' => 'A1',
        'weekday' => 'monday',
        'start_time' => '08:00',
        'end_time' => '10:00',
    ];

    $schedule = $this->service->create($data);

    expect($schedule->teacher_external_id)->toBe('TEACHER-001')
        ->and($schedule->subject_name)->toBe('Math');

    $this->assertDatabaseHas('class_schedules', ['subject_name' => 'Math']);
});

it('throws exception if semester does not exist', function () {
    $data = [
        'semester_id' => 9999,
        'classroom_id' => $this->classroom->classroom_id,
        'teacher_external_id' => 'TEACHER-001',
        'subject_name' => 'Math',
        'group_name' => 'A1',
        'weekday' => 'monday',
        'start_time' => '08:00',
        'end_time' => '10:00',
    ];

    expect(fn () => $this->service->create($data))->toThrow(RuntimeException::class, 'El semestre seleccionado no existe.');
});

it('throws exception on create with time overlap', function () {
    ClassSchedule::factory()->create([
        'semester_id' => $this->semester->semester_id,
        'classroom_id' => $this->classroom->classroom_id,
        'weekday' => 'monday',
        'start_time' => '08:00',
        'end_time' => '10:00',
    ]);

    $data = [
        'semester_id' => $this->semester->semester_id,
        'classroom_id' => $this->classroom->classroom_id,
        'teacher_external_id' => 'TEACHER-002',
        'subject_name' => 'Physics',
        'group_name' => 'A2',
        'weekday' => 'monday',
        'start_time' => '09:00', // Overlaps
        'end_time' => '11:00',
    ];

    expect(fn () => $this->service->create($data))->toThrow(RuntimeException::class);
});

it('can update schedule', function () {
    $schedule = ClassSchedule::factory()->create([
        'semester_id' => $this->semester->semester_id,
        'classroom_id' => $this->classroom->classroom_id,
        'subject_name' => 'Math',
    ]);

    $updated = $this->service->update($schedule->class_schedule_id, ['subject_name' => 'Advanced Math']);

    expect($updated->subject_name)->toBe('Advanced Math');
});

it('throws exception on update with time overlap', function () {
    $schedule1 = ClassSchedule::factory()->create([
        'semester_id' => $this->semester->semester_id,
        'classroom_id' => $this->classroom->classroom_id,
        'weekday' => 'monday',
        'start_time' => '08:00',
        'end_time' => '10:00',
    ]);

    $schedule2 = ClassSchedule::factory()->create([
        'semester_id' => $this->semester->semester_id,
        'classroom_id' => $this->classroom->classroom_id,
        'weekday' => 'monday',
        'start_time' => '10:00',
        'end_time' => '12:00',
    ]);

    expect(fn () => $this->service->update($schedule2->class_schedule_id, [
        'start_time' => '09:00', // Overlaps with schedule1
    ]))->toThrow(RuntimeException::class);
});

it('can delete schedule', function () {
    $schedule = ClassSchedule::factory()->create([
        'semester_id' => $this->semester->semester_id,
        'classroom_id' => $this->classroom->classroom_id,
    ]);

    $result = $this->service->delete($schedule->class_schedule_id);

    expect($result)->toBeTrue();
    $this->assertSoftDeleted('class_schedules', ['class_schedule_id' => $schedule->class_schedule_id]);
});
