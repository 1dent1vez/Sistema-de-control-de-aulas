<?php

/**
 * @descripcion  Tests de feature para los endpoints de edificios.
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
 * @creado       2026-05-13
 *
 * @modificado   2026-05-13
 *
 * @cambios      2026-05-13 - Creación inicial de los tests
 */

declare(strict_types=1);

use App\Models\Building;
use App\Models\Institution;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    $this->endpoint = '/api/v1/buildings';
    $this->institution = Institution::factory()->create();
});

it('can list all buildings', function (): void {
    Building::factory()->count(3)->create();

    $response = $this->getJson($this->endpoint);

    $response->assertStatus(200)
        ->assertJsonStructure([
            'success', 'statusCode', 'message',
            'data' => [['id', 'name', 'levelCount', 'status', 'levels']],
            'errors',
        ]);
});

it('can show a single building with levels', function (): void {
    $building = Building::factory()->create();

    $response = $this->getJson("$this->endpoint/{$building->id}");

    $response->assertStatus(200)
        ->assertJsonPath('data.id', $building->id);
});

it('returns 404 when building not found', function (): void {
    $this->getJson("$this->endpoint/999")
        ->assertStatus(404)
        ->assertJsonFragment(['success' => false]);
});

it('can create a building with auto-generated levels', function (): void {
    $data = [
        'institution_id' => $this->institution->id,
        'name' => 'Edificio Principal',
        'level_count' => 3,
    ];

    $response = $this->postJson($this->endpoint, $data);

    $response->assertStatus(201)
        ->assertJsonPath('data.levelCount', 3);

    $this->assertDatabaseHas('gama_buildings', ['name' => 'Edificio Principal']);

    $buildingId = $response->json('data.id');
    $this->assertDatabaseHas('gama_levels', ['building_id' => $buildingId, 'name' => 'PB', 'display_order' => 0]);
    $this->assertDatabaseHas('gama_levels', ['building_id' => $buildingId, 'name' => 'P1', 'display_order' => 1]);
    $this->assertDatabaseHas('gama_levels', ['building_id' => $buildingId, 'name' => 'P2', 'display_order' => 2]);
});

it('validates level_count between 1 and 20', function (): void {
    $this->postJson($this->endpoint, [
        'institution_id' => $this->institution->id,
        'name' => 'Test',
        'level_count' => 0,
    ])->assertStatus(422);

    $this->postJson($this->endpoint, [
        'institution_id' => $this->institution->id,
        'name' => 'Test 2',
        'level_count' => 21,
    ])->assertStatus(422);
});

it('validates unique building name per institution', function (): void {
    $building = Building::factory()->create(['institution_id' => $this->institution->id]);

    $this->postJson($this->endpoint, [
        'institution_id' => $this->institution->id,
        'name' => $building->name,
        'level_count' => 2,
    ])->assertStatus(422);
});

it('can soft delete a building', function (): void {
    $building = Building::factory()->create();

    $this->deleteJson("$this->endpoint/{$building->id}")
        ->assertStatus(200);

    $this->assertSoftDeleted($building);
});

it('can get levels of a building', function (): void {
    $data = [
        'institution_id' => $this->institution->id,
        'name' => 'Building With Levels',
        'level_count' => 3,
    ];

    $createResponse = $this->postJson($this->endpoint, $data);
    $buildingId = $createResponse->json('data.id');

    $response = $this->getJson("$this->endpoint/{$buildingId}/levels");

    $response->assertStatus(200)
        ->assertJsonStructure([
            'success', 'statusCode', 'message',
            'data' => [['id', 'buildingId', 'name', 'displayOrder']],
            'errors',
        ])
        ->assertJsonCount(3, 'data');
});
