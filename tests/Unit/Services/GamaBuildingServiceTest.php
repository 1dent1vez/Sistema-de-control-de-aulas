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
    $this->institution = Institution::factory()->create();
});

it('can get all buildings', function () {
    Building::factory()->count(2)->create(['institution_id' => $this->institution->id]);

    $buildings = $this->service->getAll();

    expect($buildings)->toHaveCount(2);
});

it('can get building by id', function () {
    $building = Building::factory()->create(['institution_id' => $this->institution->id]);

    $found = $this->service->getById($building->id);

    expect($found->id)->toBe($building->id);
});

it('can create building and auto-generates levels', function () {
    $data = [
        'institution_id' => $this->institution->id,
        'name' => 'Service Building',
        'level_count' => 3,
        'description' => 'Test Desc',
        'status' => true,
    ];

    $building = $this->service->store($data);

    expect($building->name)->toBe('Service Building')
        ->and($building->level_count)->toBe(3);

    $this->assertDatabaseHas('gama_buildings', ['name' => 'Service Building']);
    $this->assertDatabaseCount('gama_levels', 3);

    $this->assertDatabaseHas('gama_levels', ['building_id' => $building->id, 'name' => 'PB', 'display_order' => 0]);
    $this->assertDatabaseHas('gama_levels', ['building_id' => $building->id, 'name' => 'P1', 'display_order' => 1]);
    $this->assertDatabaseHas('gama_levels', ['building_id' => $building->id, 'name' => 'P2', 'display_order' => 2]);
});

it('can update building', function () {
    $building = Building::factory()->create(['name' => 'Old BName', 'institution_id' => $this->institution->id]);

    $updated = $this->service->update($building->id, ['name' => 'New BName']);

    expect($updated->name)->toBe('New BName');
    $this->assertDatabaseHas('gama_buildings', ['id' => $building->id, 'name' => 'New BName']);
});

it('can delete building', function () {
    $building = Building::factory()->create(['institution_id' => $this->institution->id]);

    $result = $this->service->delete($building->id);

    expect($result)->toBeTrue();
    $this->assertSoftDeleted('gama_buildings', ['id' => $building->id]);
});

it('can get levels of a building', function () {
    $data = [
        'institution_id' => $this->institution->id,
        'name' => 'Level Building',
        'level_count' => 2,
    ];

    $building = $this->service->store($data);

    $levels = $this->service->getLevels($building->id);

    expect($levels)->toHaveCount(2)
        ->and($levels->first()->name)->toBe('PB');
});
