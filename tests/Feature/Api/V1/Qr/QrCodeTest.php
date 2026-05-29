<?php

declare(strict_types=1);

use App\Models\Building;
use App\Models\Classroom;
use App\Models\Institution;
use App\Models\Level;
use App\Models\QrCode;
use App\Models\Semester;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    $building = Building::factory()->create();
    $level = Level::factory()->create();
    $this->classroom = Classroom::factory()->create([
        'building_id' => $building->building_id,
        'level_id' => $level->level_id,
    ]);
});

it('can generate a new QR code', function (): void {
    $this->loginAsAdmin();
    $response = $this->postJson("/api/v1/classrooms/{$this->classroom->classroom_id}/qr", []);

    $response->assertStatus(201)
        ->assertJsonStructure([
            'success', 'statusCode', 'message',
            'data' => ['id', 'token', 'isActive', 'payload'],
            'errors',
        ]);

    $this->assertDatabaseHas('qr_codes', [
        'classroom_id' => $this->classroom->classroom_id,
        'is_active' => true,
    ]);
});

it('allows regeneration without force flag', function (): void {
    $this->loginAsAdmin();
    $existing = QrCode::factory()->create([
        'classroom_id' => $this->classroom->classroom_id,
        'is_active' => true,
    ]);

    $response = $this->postJson("/api/v1/classrooms/{$this->classroom->classroom_id}/qr", []);

    $response->assertStatus(201);
    $this->assertDatabaseMissing('qr_codes', [
        'qr_id' => $existing->qr_id,
        'is_active' => true,
    ]);
});

it('allows regeneration with force flag', function (): void {
    $this->loginAsAdmin();
    $existing = QrCode::factory()->create([
        'classroom_id' => $this->classroom->classroom_id,
        'is_active' => true,
    ]);

    $response = $this->postJson("/api/v1/classrooms/{$this->classroom->classroom_id}/qr", [
        'force_regenerate' => true,
    ]);

    $response->assertStatus(201);
    $this->assertDatabaseMissing('qr_codes', [
        'qr_id' => $existing->qr_id,
        'is_active' => true,
    ]);
});

it('can show active QR code for a classroom', function (): void {
    $this->loginAsAdmin();
    QrCode::factory()->create([
        'classroom_id' => $this->classroom->classroom_id,
        'is_active' => true,
    ]);

    $response = $this->getJson("/api/v1/classrooms/{$this->classroom->classroom_id}/qr");

    $response->assertStatus(200)
        ->assertJsonPath('data.isActive', true);
});

it('returns 404 when no active QR for classroom', function (): void {
    $this->loginAsAdmin();
    $this->getJson("/api/v1/classrooms/{$this->classroom->classroom_id}/qr")
        ->assertStatus(404);
});

it('can download QR batch', function (): void {
    $this->loginAsAdmin();
    $this->postJson("/api/v1/classrooms/{$this->classroom->classroom_id}/qr", []);

    $response = $this->postJson('/api/v1/qr-codes/download', [
        'classroom_ids' => [$this->classroom->classroom_id],
        'format' => 'png',
    ]);

    $response->assertStatus(200)
        ->assertJsonStructure(['data' => ['batchId']]);
});

it('allows guest to view public classroom schedule via QR link', function (): void {
    $semester = Semester::factory()->create([
        'start_date' => now()->subDay()->format('Y-m-d'),
        'end_date' => now()->addDay()->format('Y-m-d'),
    ]);

    $response = $this->get(route('qr.aula.horario', ['aula_id' => $this->classroom->classroom_id]));

    $response->assertStatus(200)
        ->assertSee($this->classroom->classroom_name);
});

it('returns 404 when classroom not found on public schedule page', function (): void {
    $response = $this->get('/qr/aula/99999');
    $response->assertStatus(404);
});
