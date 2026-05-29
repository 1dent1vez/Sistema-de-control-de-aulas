<?php

/**
 * @descripcion  Pruebas unitarias para GamaTeacherAbsenceService.
 *
 * @autor        Ghael Garcia Manjarrez <ghael.engineer@gmail.com>
 *
 * @autorizador  Ruben Alejandro Nolasco Ruiz <correo@dominio.com>
 *
 * @prueba       Ghael Garcia Manjarrez <ghael.engineer@gmail.com>
 *
 * @mantenimiento Ghael Garcia Manjarrez <ghael.engineer@gmail.com>
 *
 * @version      1.1.0
 *
 * @creado       2026-05-14
 *
 * @modificado   2026-05-26
 *
 * @cambios      2026-05-14 - Creación de pruebas unitarias
 *               2026-05-26 - Actualización de aserciones de mensajes de error en español para reflejar el comportamiento actual.
 */

declare(strict_types=1);

use App\Models\AbsenceType;
use App\Models\ClassSchedule;
use App\Models\Semester;
use App\Models\TeacherAbsence;
use App\Services\TeacherStatus\GamaTeacherAbsenceService;
use App\Services\TeacherStatus\OverlapRequiredException;
use Carbon\Carbon;

beforeEach(function () {
    $this->service = app(GamaTeacherAbsenceService::class);
    $this->absenceType = AbsenceType::factory()->create();

    // Crear semestre y horarios por defecto para TEACHER-001 para pasar validación de clases
    $semester = Semester::factory()->create([
        'start_date' => now()->subMonths(3)->format('Y-m-d'),
        'end_date' => now()->addMonths(3)->format('Y-m-d'),
    ]);

    $days = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'];
    foreach ($days as $day) {
        ClassSchedule::factory()->create([
            'semester_id' => $semester->semester_id,
            'teacher_external_id' => 'TEACHER-001',
            'weekday' => $day,
            'status' => true,
        ]);
    }
});

it('can get all absences', function () {
    TeacherAbsence::factory()->count(2)->create(['absence_type_id' => $this->absenceType->absence_type_id]);

    $absences = $this->service->getAll();

    expect($absences)->toHaveCount(2);
});

it('can get absence by id', function () {
    $absence = TeacherAbsence::factory()->create(['absence_type_id' => $this->absenceType->absence_type_id]);

    $found = $this->service->getById($absence->teacher_absence_id);

    expect($found->teacher_absence_id)->toBe($absence->teacher_absence_id);
});

it('can create absence', function () {
    $data = [
        'teacher_external_id' => 'TEACHER-001',
        'absence_type_id' => $this->absenceType->absence_type_id,
        'start_date' => Carbon::now()->addDays(1)->format('Y-m-d'),
        'end_date' => Carbon::now()->addDays(3)->format('Y-m-d'),
    ];

    $absence = $this->service->store($data);

    expect($absence->teacher_external_id)->toBe('TEACHER-001');
    $this->assertDatabaseHas('teacher_absences', ['teacher_external_id' => 'TEACHER-001']);
});

it('throws exception if absence is entirely in the past', function () {
    $data = [
        'teacher_external_id' => 'TEACHER-001',
        'absence_type_id' => $this->absenceType->absence_type_id,
        'start_date' => Carbon::now()->subDays(5)->format('Y-m-d'),
        'end_date' => Carbon::now()->subDays(1)->format('Y-m-d'),
    ];

    expect(fn () => $this->service->store($data))->toThrow(RuntimeException::class, 'La fecha de inicio no puede ser anterior a la fecha actual.');
});

it('throws OverlapRequiredException on overlap without confirmation', function () {
    TeacherAbsence::factory()->create([
        'teacher_external_id' => 'TEACHER-001',
        'absence_type_id' => $this->absenceType->absence_type_id,
        'start_date' => Carbon::now()->addDays(1)->format('Y-m-d'),
        'end_date' => Carbon::now()->addDays(5)->format('Y-m-d'),
    ]);

    $data = [
        'teacher_external_id' => 'TEACHER-001',
        'absence_type_id' => $this->absenceType->absence_type_id,
        'start_date' => Carbon::now()->addDays(3)->format('Y-m-d'),
        'end_date' => Carbon::now()->addDays(7)->format('Y-m-d'),
    ];

    expect(fn () => $this->service->store($data))->toThrow(OverlapRequiredException::class);
});

it('creates absence despite overlap if is_confirmed is true', function () {
    TeacherAbsence::factory()->create([
        'teacher_external_id' => 'TEACHER-001',
        'absence_type_id' => $this->absenceType->absence_type_id,
        'start_date' => Carbon::now()->addDays(1)->format('Y-m-d'),
        'end_date' => Carbon::now()->addDays(5)->format('Y-m-d'),
    ]);

    $data = [
        'teacher_external_id' => 'TEACHER-001',
        'absence_type_id' => $this->absenceType->absence_type_id,
        'start_date' => Carbon::now()->addDays(3)->format('Y-m-d'),
        'end_date' => Carbon::now()->addDays(7)->format('Y-m-d'),
        'is_confirmed' => true,
    ];

    $absence = $this->service->store($data);

    expect($absence->is_confirmed)->toBeTrue();
});

it('can update absence', function () {
    $absence = TeacherAbsence::factory()->create([
        'teacher_external_id' => 'TEACHER-001',
        'absence_type_id' => $this->absenceType->absence_type_id,
        'start_date' => Carbon::now()->addDays(10)->format('Y-m-d'),
        'end_date' => Carbon::now()->addDays(15)->format('Y-m-d'),
    ]);

    $newEndDate = Carbon::now()->addDays(16)->format('Y-m-d');
    $updated = $this->service->update($absence->teacher_absence_id, ['end_date' => $newEndDate]);

    expect($updated->end_date->format('Y-m-d'))->toBe($newEndDate);
});

it('throws exception if updating an absence that already started', function () {
    $absence = TeacherAbsence::factory()->create([
        'teacher_external_id' => 'TEACHER-001',
        'absence_type_id' => $this->absenceType->absence_type_id,
        'start_date' => Carbon::now()->subDays(1)->format('Y-m-d'), // Started yesterday
        'end_date' => Carbon::now()->addDays(5)->format('Y-m-d'),
    ]);

    expect(fn () => $this->service->update($absence->teacher_absence_id, ['end_date' => Carbon::now()->addDays(6)->format('Y-m-d')]))
        ->toThrow(RuntimeException::class, 'Este estado ya fue procesado y no puede modificarse.');
});

it('can delete absence', function () {
    $absence = TeacherAbsence::factory()->create([
        'absence_type_id' => $this->absenceType->absence_type_id,
    ]);

    $result = $this->service->delete($absence->teacher_absence_id);

    expect($result)->toBeTrue();
    $this->assertSoftDeleted('teacher_absences', ['teacher_absence_id' => $absence->teacher_absence_id]);
});
