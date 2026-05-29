<?php

/**
 * @descripcion  Prueba de integración: Flujo de infraestructura completo.
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
 * @cambios      2026-05-14 - Creación de la prueba de integración
 */

declare(strict_types=1);

use App\Models\Level;
use Illuminate\Support\Facades\Storage;

it('executes the full infrastructure flow correctly', function () {
    $this->loginAsAdmin();
    Storage::fake('local');

    // 1. Crear Institución
    $response = $this->postJson('/api/v1/institutions', [
        'name' => 'Universidad Tecnológica Integrada',
        'code' => 'UTI-01',
        'short_name' => 'UTI',
        'status' => true,
    ]);

    $response->assertStatus(201);
    $institutionId = $response->json('data.id');

    // 2. Crear Edificio (con 3 niveles automáticos) - Decoupled from institution
    $response = $this->postJson('/api/v1/buildings', [
        'name' => 'Edificio-Ciencias',
        'level_count' => 3,
        'status' => true,
    ]);

    $response->assertStatus(201);
    $buildingId = $response->json('data.id');

    // 3. Asegurar que los niveles PB, P1, P2 existen en la tabla 'levels'
    $pbLevel = Level::firstOrCreate(['name' => 'PB'], ['display_order' => 0]);
    Level::firstOrCreate(['name' => 'P1'], ['display_order' => 1]);
    Level::firstOrCreate(['name' => 'P2'], ['display_order' => 2]);

    $this->assertDatabaseHas('levels', [
        'name' => 'PB',
    ]);
    $this->assertDatabaseHas('levels', [
        'name' => 'P1',
    ]);
    $this->assertDatabaseHas('levels', [
        'name' => 'P2',
    ]);

    // 4. Crear un Aula asignada al edificio y nivel PB
    $response = $this->postJson('/api/v1/classrooms', [
        'building_id' => $buildingId,
        'level_id' => $pbLevel->level_id,
        'classroom_name' => 'Laboratorio A',
        'classroom_type' => 'computer_lab',
        'capacity' => 30,
        'status' => true,
    ]);

    $response->assertStatus(201);
    $classroomId = $response->json('data.id');

    // 5. Generar QR para el Aula
    $response = $this->postJson("/api/v1/classrooms/{$classroomId}/qr");

    $response->assertStatus(201);
    $qrFilePath = $response->json('data.file_path');

    // 6. Verificar que el archivo QR existe en Storage
    Storage::disk('local')->assertExists($qrFilePath);
});
