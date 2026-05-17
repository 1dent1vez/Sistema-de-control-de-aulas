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
 * @version      1.0.0
 *
 * @creado       2026-05-14
 *
 * @modificado   2026-05-14
 *
 * @cambios      2026-05-14 - Creación de pruebas unitarias
 */

declare(strict_types=1);

use App\Models\AbsenceType;
use App\Models\TeacherAbsence;
use App\Services\TeacherStatus\GamaTeacherAbsenceService;
use App\Services\TeacherStatus\OverlapRequiredException;
use Carbon\Carbon;

beforeEach(function () {
    $this->service = app(GamaTeacherAbsenceService::class);
    $this->absenceType = AbsenceType::factory()->create();
});

it('can get all absences', function () {
    TeacherAbsence::factory()->count(2)->create(['absence_type_id' => $this->absenceType->id]);

    $absences = $this->service->getAll();

    expect($absences)->toHaveCount(2);
});

it('can get absence by id', function () {
    $absence = TeacherAbsence::factory()->create(['absence_type_id' => $this->absenceType->id]);

    $found = $this->service->getById($absence->id);

    expect($found->id)->toBe($absence->id);
});

it('can create absence', function () {
    $data = [
        'teacher_external_id' => 'TEACHER-001',
        'absence_type_id' => $this->absenceType->id,
        'start_date' => Carbon::now()->addDays(1)->format('Y-m-d'),
        'end_date' => Carbon::now()->addDays(3)->format('Y-m-d'),
    ];

    $absence = $this->service->store($data);

    expect($absence->teacher_external_id)->toBe('TEACHER-001');
    $this->assertDatabaseHas('gama_teacher_absences', ['teacher_external_id' => 'TEACHER-001']);
});

it('throws exception if absence is entirely in the past', function () {
    $data = [
        'teacher_external_id' => 'TEACHER-001',
        'absence_type_id' => $this->absenceType->id,
        'start_date' => Carbon::now()->subDays(5)->format('Y-m-d'),
        'end_date' => Carbon::now()->subDays(1)->format('Y-m-d'),
    ];

    expect(fn () => $this->service->store($data))->toThrow(RuntimeException::class, 'La ausencia no puede estar completamente en el pasado.');
});

it('throws OverlapRequiredException on overlap without confirmation', function () {
    TeacherAbsence::factory()->create([
        'teacher_external_id' => 'TEACHER-001',
        'absence_type_id' => $this->absenceType->id,
        'start_date' => Carbon::now()->addDays(1)->format('Y-m-d'),
        'end_date' => Carbon::now()->addDays(5)->format('Y-m-d'),
    ]);

    $data = [
        'teacher_external_id' => 'TEACHER-001',
        'absence_type_id' => $this->absenceType->id,
        'start_date' => Carbon::now()->addDays(3)->format('Y-m-d'),
        'end_date' => Carbon::now()->addDays(7)->format('Y-m-d'),
    ];

    expect(fn () => $this->service->store($data))->toThrow(OverlapRequiredException::class);
});

it('creates absence despite overlap if is_confirmed is true', function () {
    TeacherAbsence::factory()->create([
        'teacher_external_id' => 'TEACHER-001',
        'absence_type_id' => $this->absenceType->id,
        'start_date' => Carbon::now()->addDays(1)->format('Y-m-d'),
        'end_date' => Carbon::now()->addDays(5)->format('Y-m-d'),
    ]);

    $data = [
        'teacher_external_id' => 'TEACHER-001',
        'absence_type_id' => $this->absenceType->id,
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
        'absence_type_id' => $this->absenceType->id,
        'start_date' => Carbon::now()->addDays(10)->format('Y-m-d'),
        'end_date' => Carbon::now()->addDays(15)->format('Y-m-d'),
    ]);

    $newEndDate = Carbon::now()->addDays(16)->format('Y-m-d');
    $updated = $this->service->update($absence->id, ['end_date' => $newEndDate]);

    expect($updated->end_date->format('Y-m-d'))->toBe($newEndDate);
});

it('throws exception if updating an absence that already started', function () {
    $absence = TeacherAbsence::factory()->create([
        'teacher_external_id' => 'TEACHER-001',
        'absence_type_id' => $this->absenceType->id,
        'start_date' => Carbon::now()->subDays(1)->format('Y-m-d'), // Started yesterday
        'end_date' => Carbon::now()->addDays(5)->format('Y-m-d'),
    ]);

    expect(fn () => $this->service->update($absence->id, ['end_date' => Carbon::now()->addDays(6)->format('Y-m-d')]))
        ->toThrow(RuntimeException::class, 'No se puede modificar una ausencia que ya inició.');
});

it('can delete absence', function () {
    $absence = TeacherAbsence::factory()->create([
        'absence_type_id' => $this->absenceType->id,
    ]);

    $result = $this->service->delete($absence->id);

    expect($result)->toBeTrue();
    $this->assertSoftDeleted('gama_teacher_absences', ['id' => $absence->id]);
});
