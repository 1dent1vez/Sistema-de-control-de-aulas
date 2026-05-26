<?php

/**
 * @descripcion  Pruebas de Feature para la validación de rol y extensión en la carga masiva.
 *
 * @autor        Agente OpenCode
 *
 * @autorizador  Ruben Alejandro Nolasco Ruiz <correo@dominio.com>
 *
 * @prueba       Agente OpenCode
 *
 * @mantenimiento Agente OpenCode
 *
 * @version      1.0.0
 *
 * @creado       2026-05-25
 *
 * @modificado   2026-05-25
 *
 * @cambios      2026-05-25 - Creación de pruebas de seguridad y extensión para la carga masiva
 */

declare(strict_types=1);

namespace Tests\Feature\Api\V1\Schedules;

use App\Models\Institution;
use App\Models\Semester;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    $this->endpoint = '/api/v1/class-schedules/import';
    $this->institution = Institution::factory()->create();
    $this->semester = Semester::factory()->create(['institution_id' => $this->institution->id]);
    Storage::fake('local');
});

it('denies access to non-admin users', function (): void {
    $this->loginAsTeacher();

    $file = UploadedFile::fake()->create('schedules.csv', 100);

    $response = $this->postJson($this->endpoint, [
        'file' => $file,
        'semester_id' => $this->semester->id,
    ]);

    $response->assertStatus(403);
});

it('denies access to guest users', function (): void {
    $file = UploadedFile::fake()->create('schedules.csv', 100);

    $response = $this->postJson($this->endpoint, [
        'file' => $file,
        'semester_id' => $this->semester->id,
    ]);

    $response->assertStatus(401);
});

it('allows admin to import valid extension file', function (): void {
    $this->loginAsAdmin();

    // Create a mock CSV with headers to pass mimes and structure
    $csvContent = "aula,docente,materia,grupo,dias,hora_inicio,hora_fin\n".
                  "A-101,TCH-001,Mathematics,Group A,Lunes,08:00,10:00\n";
    $file = UploadedFile::fake()->createWithContent('schedules.csv', $csvContent);

    $response = $this->postJson($this->endpoint, [
        'file' => $file,
        'semester_id' => $this->semester->id,
    ]);

    $response->assertStatus(202)
        ->assertJsonPath('success', true);
});

it('rejects files with invalid extension', function (): void {
    $this->loginAsAdmin();

    $file = UploadedFile::fake()->create('schedules.pdf', 100);

    $response = $this->postJson($this->endpoint, [
        'file' => $file,
        'semester_id' => $this->semester->id,
    ]);

    $response->assertStatus(422)
        ->assertJsonFragment([
            'success' => false,
        ]);
});
