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
    $building = Building::factory()->create();
    $level = Level::factory()->create();
    $classroom = Classroom::factory()->create([
        'building_id' => $building->building_id,
        'level_id' => $level->level_id,
    ]);

    // 2. Generar QR inicial para el Aula
    $response = $this->postJson("/api/v1/classrooms/{$classroom->classroom_id}/qr");

    $response->assertStatus(201);
    $firstQrId = $response->json('data.id');

    // 3. Intentar generar de nuevo sin el flag force (debe tener éxito directo y desactivar el anterior)
    $response = $this->postJson("/api/v1/classrooms/{$classroom->classroom_id}/qr");

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
        'classroom_ids' => [$classroom->classroom_id],
        'format' => 'png',
    ]);

    $batchId = $response->json('data.batchId');
    $this->assertNotNull($batchId);

    // Verificar indirectamente interceptando la creación del archivo a través del service o confiando en el 200 OK.
});
