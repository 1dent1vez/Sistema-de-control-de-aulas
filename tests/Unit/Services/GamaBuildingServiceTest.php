<?php

/**
 * @descripcion  Pruebas unitarias para GamaBuildingService.
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
use App\Models\Institution;
use App\Services\Buildings\GamaBuildingService;

beforeEach(function () {
    $this->service = app(GamaBuildingService::class);
});

it('can get all buildings', function () {
    Building::factory()->create();
    Building::factory()->create();

    $buildings = $this->service->getAll();

    expect($buildings)->toHaveCount(2);
});

it('can get building by id', function () {
    $building = Building::factory()->create();

    $found = $this->service->getById($building->building_id);

    expect($found->building_id)->toBe($building->building_id);
});

it('can create building', function () {
    $data = [
        'name' => 'Service-Building',
        'level_count' => 3,
        'description' => 'Test Desc',
        'status' => true,
    ];

    $building = $this->service->store($data);

    expect($building->name)->toBe('Service-Building')
        ->and($building->level_count)->toBe(3);

    $this->assertDatabaseHas('buildings', ['name' => 'Service-Building']);
});

it('can update building', function () {
    $building = Building::factory()->create(['name' => 'Old-BName']);

    $updated = $this->service->update($building->building_id, ['name' => 'New-BName']);

    expect($updated->name)->toBe('New-BName');
    $this->assertDatabaseHas('buildings', ['building_id' => $building->building_id, 'name' => 'New-BName']);
});

it('can delete building', function () {
    $building = Building::factory()->create();

    $result = $this->service->delete($building->building_id);

    expect($result)->toBeTrue();
    $this->assertSoftDeleted('buildings', ['building_id' => $building->building_id]);
});
