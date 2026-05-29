<?php

/**
 * @descripcion  Tests de feature para los endpoints de edificios adaptados para niveles globales.
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
 * @creado       2026-05-13
 *
 * @modificado   2026-05-28
 *
 * @cambios      2026-05-28 - Adaptado para la desvinculación de niveles de edificios (niveles globales).
 */

declare(strict_types=1);

use App\Models\Building;
use App\Models\Institution;
use App\Models\Level;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    $this->endpoint = '/api/v1/buildings';
    Level::create(['name' => 'PB', 'display_order' => 0]);
    Level::create(['name' => 'P1', 'display_order' => 1]);
});

it('can list all buildings', function (): void {
    Building::factory()->create();
    Building::factory()->create();
    Building::factory()->create();

    $response = $this->getJson($this->endpoint);

    $response->assertStatus(200)
        ->assertJsonStructure([
            'success', 'statusCode', 'message',
            'data' => [['id', 'name', 'levelCount', 'status']],
            'errors',
        ]);
});

it('can show a single building', function (): void {
    $building = Building::factory()->create();

    $response = $this->getJson("$this->endpoint/{$building->building_id}");

    $response->assertStatus(200)
        ->assertJsonPath('data.id', $building->building_id);
});

it('returns 404 when building not found', function (): void {
    $this->getJson("$this->endpoint/999")
        ->assertStatus(404)
        ->assertJsonFragment(['success' => false]);
});

it('can create a building', function (): void {
    $this->loginAsAdmin();
    $data = [
        'name' => 'Edificio-Principal',
        'level_count' => 3,
    ];

    $response = $this->postJson($this->endpoint, $data);

    $response->assertStatus(201)
        ->assertJsonPath('data.levelCount', 3);

    $this->assertDatabaseHas('buildings', ['name' => 'Edificio-Principal']);
});

it('validates level_count between 1 and 5', function (): void {
    $this->loginAsAdmin();
    $this->postJson($this->endpoint, [
        'name' => 'Test',
        'level_count' => 0,
    ])->assertStatus(422);

    $this->postJson($this->endpoint, [
        'name' => 'Test-2',
        'level_count' => 6,
    ])->assertStatus(422);
});

it('validates unique building name', function (): void {
    $this->loginAsAdmin();
    $building = Building::factory()->create();

    $this->postJson($this->endpoint, [
        'name' => $building->name,
        'level_count' => 2,
    ])->assertStatus(422);
});

it('can soft delete a building', function (): void {
    $this->loginAsAdmin();
    $building = Building::factory()->create();

    $this->deleteJson("$this->endpoint/{$building->building_id}")
        ->assertStatus(200);

    $this->assertSoftDeleted($building);
});

it('can get levels dynamically', function (): void {
    $this->loginAsAdmin();
    Building::factory()->create();

    $response = $this->getJson("/api/v1/levels");

    $response->assertStatus(200)
        ->assertJsonStructure([
            'success', 'statusCode', 'message',
            'data' => [['id', 'name', 'displayOrder']],
            'errors',
        ])
        ->assertJsonCount(2, 'data');
});
