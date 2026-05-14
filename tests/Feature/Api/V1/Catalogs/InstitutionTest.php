<?php

/**
 * @descripcion  Tests de feature para los endpoints del catálogo de instituciones.
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

use App\Models\Institution;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    $this->endpoint = '/api/v1/institutions';
});

it('can list all institutions', function (): void {
    Institution::factory()->count(3)->create();

    $response = $this->getJson($this->endpoint);

    $response->assertStatus(200)
        ->assertJsonStructure([
            'success',
            'statusCode',
            'message',
            'data' => [
                '*' => ['id', 'name', 'code', 'isActive', 'createdAt', 'updatedAt'],
            ],
            'errors',
        ])
        ->assertJsonFragment(['success' => true]);
});

it('can show a single institution', function (): void {
    $institution = Institution::factory()->create();

    $response = $this->getJson("$this->endpoint/{$institution->id}");

    $response->assertStatus(200)
        ->assertJsonStructure([
            'success',
            'statusCode',
            'message',
            'data' => ['id', 'name', 'code', 'isActive'],
            'errors',
        ]);
});

it('returns 404 when institution not found', function (): void {
    $response = $this->getJson("$this->endpoint/999");

    $response->assertStatus(404)
        ->assertJsonFragment(['success' => false]);
});

it('can create an institution', function (): void {
    $data = [
        'name' => 'Tecnológico de Toluca',
        'code' => 'TEC-TOL',
        'is_active' => true,
    ];

    $response = $this->postJson($this->endpoint, $data);

    $response->assertStatus(201)
        ->assertJsonStructure([
            'success',
            'statusCode',
            'message',
            'data' => ['id', 'name', 'code', 'isActive'],
            'errors',
        ]);

    $this->assertDatabaseHas('gama_institutions', ['name' => 'Tecnológico de Toluca']);
});

it('validates required fields when creating', function (): void {
    $response = $this->postJson($this->endpoint, []);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['name', 'code']);
});

it('can update an institution', function (): void {
    $institution = Institution::factory()->create();

    $response = $this->putJson("$this->endpoint/{$institution->id}", [
        'name' => 'Updated Name',
    ]);

    $response->assertStatus(200)
        ->assertJsonPath('data.name', 'Updated Name');
});

it('can soft delete an institution', function (): void {
    $institution = Institution::factory()->create();

    $response = $this->deleteJson("$this->endpoint/{$institution->id}");

    $response->assertStatus(200);
    $this->assertSoftDeleted($institution);
});
