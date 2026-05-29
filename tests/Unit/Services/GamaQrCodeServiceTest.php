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
    $this->level = Level::factory()->create();
    $this->classroom = Classroom::factory()->create([
        'building_id' => $this->building->building_id,
        'level_id' => $this->level->level_id,
    ]);
    Storage::fake('local');
});

it('can generate a qr code for a classroom', function () {
    $qr = $this->service->generateForClassroom($this->classroom->classroom_id);

    expect($qr->classroom_id)->toBe($this->classroom->classroom_id)
        ->and($qr->is_active)->toBeTrue();

    Storage::disk('local')->assertExists($qr->file_path);
});

it('allows normal regeneration of qr, deactivating the previous one', function () {
    $firstQr = $this->service->generateForClassroom($this->classroom->classroom_id);

    $secondQr = $this->service->generateForClassroom($this->classroom->classroom_id);

    $firstQr->refresh();

    expect($firstQr->is_active)->toBeFalse()
        ->and($firstQr->invalidated_at)->not->toBeNull()
        ->and($secondQr->is_active)->toBeTrue();
});

it('can force regenerate qr code, invalidating previous', function () {
    $firstQr = $this->service->generateForClassroom($this->classroom->classroom_id);

    // Force generate
    $secondQr = $this->service->generateForClassroom($this->classroom->classroom_id, true);

    $firstQr->refresh();

    expect($firstQr->is_active)->toBeFalse()
        ->and($firstQr->invalidated_at)->not->toBeNull()
        ->and($secondQr->is_active)->toBeTrue();
});

it('can get active qr', function () {
    $qr = $this->service->generateForClassroom($this->classroom->classroom_id);

    $found = $this->service->getActiveQr($this->classroom->classroom_id);

    expect($found->qr_id)->toBe($qr->qr_id);
});

it('throws exception if batch downloading without active qrs', function () {
    expect(fn () => $this->service->downloadBatch([$this->classroom->classroom_id], 'png'))
        ->toThrow(RuntimeException::class, 'No se encontraron códigos QR activos para las aulas especificadas.');
});

it('can download single png', function () {
    $qr = $this->service->generateForClassroom($this->classroom->classroom_id);

    $path = $this->service->downloadBatch([$this->classroom->classroom_id], 'png');

    Storage::disk('local')->assertExists($path);
});

it('can download zip of multiple pngs', function () {
    $classroom2 = Classroom::factory()->create([
        'building_id' => $this->building->building_id,
        'level_id' => $this->level->level_id,
    ]);

    $this->service->generateForClassroom($this->classroom->classroom_id);
    $this->service->generateForClassroom($classroom2->classroom_id);

    $path = $this->service->downloadBatch([$this->classroom->classroom_id, $classroom2->classroom_id], 'png');

    Storage::disk('local')->assertExists($path);
    expect(str_ends_with($path, '.zip'))->toBeTrue();
});
