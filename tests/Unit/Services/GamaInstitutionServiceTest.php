<?php

/**
 * @descripcion  Pruebas unitarias para GamaInstitutionService.
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
use App\Services\Catalogs\GamaInstitutionService;

beforeEach(function () {
    $this->service = app(GamaInstitutionService::class);
});

it('can get all institutions', function () {
    Institution::factory()->count(3)->create();

    $institutions = $this->service->getAll();

    expect($institutions)->toHaveCount(3);
});

it('can get institution by id', function () {
    $institution = Institution::factory()->create();

    $found = $this->service->getById($institution->institution_id);

    expect($found->institution_id)->toBe($institution->institution_id);
});

it('can create institution', function () {
    $data = [
        'name' => 'Test Institution',
        'code' => 'TEST-01',
        'is_active' => true,
    ];

    $institution = $this->service->create($data);

    expect($institution->name)->toBe('Test Institution')
        ->and($institution->code)->toBe('TEST-01')
        ->and($institution->is_active)->toBeTrue();

    $this->assertDatabaseHas('institutions', ['name' => 'Test Institution']);
});

it('can update institution', function () {
    $institution = Institution::factory()->create(['name' => 'Old Name']);

    $updated = $this->service->update($institution->institution_id, ['name' => 'New Name']);

    expect($updated->name)->toBe('New Name');
    $this->assertDatabaseHas('institutions', ['institution_id' => $institution->institution_id, 'name' => 'New Name']);
});

it('can delete institution', function () {
    $institution = Institution::factory()->create();

    $result = $this->service->delete($institution->institution_id);

    expect($result)->toBeTrue();
    $this->assertSoftDeleted('institutions', ['institution_id' => $institution->institution_id]);
});
