<?php

/**
 * @descripcion  Tests de feature para los endpoints del catálogo de tipos de ausencia.
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

use App\Models\AbsenceType;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    $this->endpoint = '/api/v1/absence-types';
});

it('can list all absence types', function (): void {
    AbsenceType::factory()->count(3)->create();

    $response = $this->getJson($this->endpoint);

    $response->assertStatus(200)
        ->assertJsonStructure([
            'success',
            'statusCode',
            'message',
            'data' => [
                '*' => ['id', 'name', 'code', 'createdAt', 'updatedAt'],
            ],
            'errors',
        ])
        ->assertJsonFragment(['success' => true]);
});

it('can show a single absence type', function (): void {
    $absenceType = AbsenceType::factory()->create();

    $response = $this->getJson("$this->endpoint/{$absenceType->id}");

    $response->assertStatus(200)
        ->assertJsonStructure([
            'success',
            'statusCode',
            'message',
            'data' => ['id', 'name', 'code'],
            'errors',
        ]);
});

it('returns 404 when absence type not found', function (): void {
    $response = $this->getJson("$this->endpoint/999");

    $response->assertStatus(404)
        ->assertJsonFragment(['success' => false]);
});
