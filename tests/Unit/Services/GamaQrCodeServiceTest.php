<?php

/**
 * @descripcion  Pruebas unitarias para GamaQrCodeService.
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
use App\Models\Classroom;
use App\Models\Level;
use App\Services\Qr\GamaQrCodeService;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    $this->service = app(GamaQrCodeService::class);
    $this->building = Building::factory()->create();
    $this->level = Level::factory()->create(['building_id' => $this->building->id]);
    $this->classroom = Classroom::factory()->create([
        'building_id' => $this->building->id,
        'level_id' => $this->level->id,
    ]);
    Storage::fake('local');
});

it('can generate a qr code for a classroom', function () {
    $qr = $this->service->generateForClassroom($this->classroom->id);

    expect($qr->classroom_id)->toBe($this->classroom->id)
        ->and($qr->is_active)->toBeTrue();

    Storage::disk('local')->assertExists($qr->file_path);
});

it('throws exception when generating qr if one already active', function () {
    $this->service->generateForClassroom($this->classroom->id);

    expect(fn () => $this->service->generateForClassroom($this->classroom->id))
        ->toThrow(RuntimeException::class, 'Ya existe un código QR activo para esta aula. Utiliza forceRegenerate para reemplazarlo.');
});

it('can force regenerate qr code, invalidating previous', function () {
    $firstQr = $this->service->generateForClassroom($this->classroom->id);

    // Force generate
    $secondQr = $this->service->generateForClassroom($this->classroom->id, true);

    $firstQr->refresh();

    expect($firstQr->is_active)->toBeFalse()
        ->and($firstQr->invalidated_at)->not->toBeNull()
        ->and($secondQr->is_active)->toBeTrue();
});

it('can get active qr', function () {
    $qr = $this->service->generateForClassroom($this->classroom->id);

    $found = $this->service->getActiveQr($this->classroom->id);

    expect($found->id)->toBe($qr->id);
});

it('throws exception if batch downloading without active qrs', function () {
    expect(fn () => $this->service->downloadBatch([$this->classroom->id], 'png'))
        ->toThrow(RuntimeException::class, 'No se encontraron códigos QR activos para las aulas especificadas.');
});

it('can download single png', function () {
    $qr = $this->service->generateForClassroom($this->classroom->id);

    $path = $this->service->downloadBatch([$this->classroom->id], 'png');

    Storage::disk('local')->assertExists($path);
});

it('can download zip of multiple pngs', function () {
    $classroom2 = Classroom::factory()->create([
        'building_id' => $this->building->id,
        'level_id' => $this->level->id,
    ]);

    $this->service->generateForClassroom($this->classroom->id);
    $this->service->generateForClassroom($classroom2->id);

    $path = $this->service->downloadBatch([$this->classroom->id, $classroom2->id], 'png');

    Storage::disk('local')->assertExists($path);
    expect(str_ends_with($path, '.zip'))->toBeTrue();
});
