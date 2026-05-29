<?php

/**
 * @descripcion  Pruebas unitarias para GamaSemesterService.
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

use App\Models\Institution;
use App\Models\Semester;
use App\Services\Schedules\GamaSemesterService;
use Carbon\Carbon;

beforeEach(function () {
    $this->service = app(GamaSemesterService::class);
    $this->institution = Institution::factory()->create();
});

it('can get all semesters', function () {
    Semester::factory()->create(['institution_id' => $this->institution->institution_id]);
    Semester::factory()->create(['institution_id' => $this->institution->institution_id]);

    $semesters = $this->service->getAll();

    expect($semesters)->toHaveCount(2);
});

it('can get semester by id', function () {
    $semester = Semester::factory()->create(['institution_id' => $this->institution->institution_id]);

    $found = $this->service->getById($semester->semester_id);

    expect($found->semester_id)->toBe($semester->semester_id);
});

it('can get current semester', function () {
    $current = Semester::factory()->create([
        'institution_id' => $this->institution->institution_id,
        'start_date' => Carbon::now()->subDays(10),
        'end_date' => Carbon::now()->addDays(10),
    ]);

    $found = $this->service->getCurrent();

    expect($found->semester_id)->toBe($current->semester_id);
});

it('can create semester', function () {
    $data = [
        'institution_id' => $this->institution->institution_id,
        'name' => '2026-A',
        'start_date' => '2026-01-01',
        'end_date' => '2026-06-30',
    ];

    $semester = $this->service->create($data);

    expect($semester->name)->toBe('2026-A');
    $this->assertDatabaseHas('semesters', ['name' => '2026-A']);
});

it('throws exception on create with overlap', function () {
    Semester::factory()->create([
        'institution_id' => $this->institution->institution_id,
        'start_date' => '2026-01-01',
        'end_date' => '2026-06-30',
    ]);

    $data = [
        'institution_id' => $this->institution->institution_id,
        'name' => '2026-B',
        'start_date' => '2026-05-01', // Overlaps
        'end_date' => '2026-10-31',
    ];

    expect(fn () => $this->service->create($data))->toThrow(RuntimeException::class);
});

it('can update semester', function () {
    $semester = Semester::factory()->create([
        'institution_id' => $this->institution->institution_id,
        'start_date' => '2026-01-01',
        'end_date' => '2026-06-30',
    ]);

    $updated = $this->service->update($semester->semester_id, ['name' => '2026-A Updated']);

    expect($updated->name)->toBe('2026-A Updated');
});

it('throws exception on update with overlap', function () {
    $semester1 = Semester::factory()->create([
        'institution_id' => $this->institution->institution_id,
        'start_date' => '2026-01-01',
        'end_date' => '2026-06-30',
    ]);

    $semester2 = Semester::factory()->create([
        'institution_id' => $this->institution->institution_id,
        'start_date' => '2026-08-01',
        'end_date' => '2026-12-31',
    ]);

    expect(fn () => $this->service->update($semester2->semester_id, [
        'start_date' => '2026-05-01', // Overlaps with semester1
    ]))->toThrow(RuntimeException::class);
});

it('can delete semester', function () {
    $semester = Semester::factory()->create(['institution_id' => $this->institution->institution_id]);

    $result = $this->service->delete($semester->semester_id);

    expect($result)->toBeTrue();
    $this->assertSoftDeleted('semesters', ['semester_id' => $semester->semester_id]);
});
