<?php

declare(strict_types=1);

use App\Models\Building;
use App\Models\Classroom;
use App\Models\Institution;
use App\Models\Level;
use App\Models\QrCode;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    $institution = Institution::factory()->create();
    $building = Building::factory()->create(['institution_id' => $institution->id]);
    $level = Level::factory()->create(['building_id' => $building->id]);
    $this->classroom = Classroom::factory()->create([
        'building_id' => $building->id,
        'level_id' => $level->id,
    ]);
});

it('can generate a new QR code', function (): void {
    $response = $this->postJson("/api/v1/classrooms/{$this->classroom->id}/qr", []);

    $response->assertStatus(201)
        ->assertJsonStructure([
            'success', 'statusCode', 'message',
            'data' => ['id', 'token', 'isActive', 'payload'],
            'errors',
        ]);

    $this->assertDatabaseHas('gama_qr_codes', [
        'classroom_id' => $this->classroom->id,
        'is_active' => true,
    ]);
});

it('rejects regeneration without force flag', function (): void {
    QrCode::factory()->create([
        'classroom_id' => $this->classroom->id,
        'is_active' => true,
    ]);

    $response = $this->postJson("/api/v1/classrooms/{$this->classroom->id}/qr", []);

    $response->assertStatus(409);
});

it('allows regeneration with force flag', function (): void {
    $existing = QrCode::factory()->create([
        'classroom_id' => $this->classroom->id,
        'is_active' => true,
    ]);

    $response = $this->postJson("/api/v1/classrooms/{$this->classroom->id}/qr", [
        'force_regenerate' => true,
    ]);

    $response->assertStatus(201);
    $this->assertDatabaseMissing('gama_qr_codes', [
        'id' => $existing->id,
        'is_active' => true,
    ]);
});

it('can show active QR code for a classroom', function (): void {
    QrCode::factory()->create([
        'classroom_id' => $this->classroom->id,
        'is_active' => true,
    ]);

    $response = $this->getJson("/api/v1/classrooms/{$this->classroom->id}/qr");

    $response->assertStatus(200)
        ->assertJsonPath('data.isActive', true);
});

it('returns 404 when no active QR for classroom', function (): void {
    $this->getJson("/api/v1/classrooms/{$this->classroom->id}/qr")
        ->assertStatus(404);
});

it('can download QR batch', function (): void {
    $this->postJson("/api/v1/classrooms/{$this->classroom->id}/qr", []);

    $response = $this->postJson('/api/v1/qr-codes/download', [
        'classroom_ids' => [$this->classroom->id],
        'format' => 'png',
    ]);

    $response->assertStatus(200)
        ->assertJsonStructure(['data' => ['batchId']]);
});
