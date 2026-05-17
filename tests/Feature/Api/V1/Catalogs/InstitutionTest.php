<?php

/**
 * @descripcion  Pruebas funcionales para GamaInstitutionController.
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
 * @cambios      2026-05-14 - Creación de pruebas funcionales API
 */

declare(strict_types=1);

use App\Models\Institution;

it('can list institutions via API', function () {
    Institution::factory()->count(3)->create();

    $response = $this->getJson('/api/v1/institutions');

    $response->assertStatus(200)
        ->assertJsonCount(3, 'data')
        ->assertJsonStructure([
            'success',
            'statusCode',
            'message',
            'data' => [
                '*' => ['id', 'name', 'code', 'isActive'],
            ],
        ]);
});

it('can get single institution via API', function () {
    $institution = Institution::factory()->create();

    $response = $this->getJson("/api/v1/institutions/{$institution->id}");

    $response->assertStatus(200)
        ->assertJsonPath('data.id', $institution->id)
        ->assertJsonPath('data.name', $institution->name);
});

it('can create institution via API', function () {
    $data = [
        'name' => 'API Institution',
        'code' => 'API-01',
        'is_active' => true,
    ];

    $response = $this->postJson('/api/v1/institutions', $data);

    $response->assertStatus(201)
        ->assertJsonPath('data.name', 'API Institution');

    $this->assertDatabaseHas('gama_institutions', ['name' => 'API Institution']);
});

it('validates creation of institution', function () {
    $response = $this->postJson('/api/v1/institutions', []);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['name', 'code']);
});

it('can update institution via API', function () {
    $institution = Institution::factory()->create(['name' => 'Old Name']);

    $response = $this->putJson("/api/v1/institutions/{$institution->id}", [
        'name' => 'Updated Name',
        'code' => $institution->code,
        'is_active' => $institution->is_active,
    ]);

    $response->assertStatus(200)
        ->assertJsonPath('data.name', 'Updated Name');

    $this->assertDatabaseHas('gama_institutions', ['id' => $institution->id, 'name' => 'Updated Name']);
});

it('can delete institution via API', function () {
    $institution = Institution::factory()->create();

    $response = $this->deleteJson("/api/v1/institutions/{$institution->id}");

    $response->assertStatus(200);

    $this->assertSoftDeleted('gama_institutions', ['id' => $institution->id]);
});
