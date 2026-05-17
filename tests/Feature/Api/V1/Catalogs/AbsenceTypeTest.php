<?php

/**
 * @descripcion  Pruebas funcionales para GamaAbsenceTypeController.
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

use App\Models\AbsenceType;

it('can list absence types via API', function () {
    AbsenceType::factory()->count(3)->create();

    $response = $this->getJson('/api/v1/absence-types');

    $response->assertStatus(200)
        ->assertJsonCount(3, 'data')
        ->assertJsonStructure([
            'success',
            'statusCode',
            'message',
            'data' => [
                '*' => ['id', 'name', 'code'],
            ],
        ]);
});

it('can get single absence type via API', function () {
    $absenceType = AbsenceType::factory()->create();

    $response = $this->getJson("/api/v1/absence-types/{$absenceType->id}");

    $response->assertStatus(200)
        ->assertJsonPath('data.id', $absenceType->id)
        ->assertJsonPath('data.name', $absenceType->name);
});
