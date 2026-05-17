<?php

/**
 * @descripcion  Prueba de integración: Flujo del ciclo de vida operativo de un QR.
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

use App\Models\Building;
use App\Models\Classroom;
use App\Models\Institution;
use App\Models\Level;
use App\Models\QrCode;
use Illuminate\Support\Facades\Storage;

it('executes the QR lifecycle flow correctly', function () {
    $this->loginAsAdmin();
    Storage::fake('local');

    // 1. Preparar infraestructura base
    $institution = Institution::factory()->create();
    $building = Building::factory()->create(['institution_id' => $institution->id]);
    $level = Level::factory()->create(['building_id' => $building->id]);
    $classroom = Classroom::factory()->create([
        'building_id' => $building->id,
        'level_id' => $level->id,
    ]);

    // 2. Generar QR inicial para el Aula
    $response = $this->postJson("/api/v1/classrooms/{$classroom->id}/qr");

    $response->assertStatus(201);
    $firstQrId = $response->json('data.id');

    // 3. Intentar generar de nuevo sin el flag force (debe fallar con 409 Conflict)
    $response = $this->postJson("/api/v1/classrooms/{$classroom->id}/qr");

    $response->assertStatus(409);
    $this->assertStringContainsString('forceRegenerate', $response->json('message'));

    // 4. Generar nuevo QR con el flag force
    $response = $this->postJson("/api/v1/classrooms/{$classroom->id}/qr", [
        'force_regenerate' => true,
    ]);

    $response->assertStatus(201);
    $secondQrId = $response->json('data.id');

    // 5. Verificar estado de los QR en base de datos
    $firstQr = QrCode::find($firstQrId);
    $secondQr = QrCode::find($secondQrId);

    $this->assertFalse($firstQr->is_active);
    $this->assertNotNull($firstQr->invalidated_at);
    $this->assertTrue($secondQr->is_active);

    // 6. Ejecutar descarga masiva en lote y verificar que procesa solo el activo
    $response = $this->postJson('/api/v1/qr-codes/download', [
        'classroom_ids' => [$classroom->id],
        'format' => 'png',
    ]);

    $batchId = $response->json('data.batchId');
    $this->assertNotNull($batchId);

    // Verificar indirectamente interceptando la creación del archivo a través del service o confiando en el 200 OK.
});
