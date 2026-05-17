<?php

declare(strict_types=1);

use App\Models\AbsenceType;
use App\Models\TeacherAbsence;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    $this->endpoint = '/api/v1/teacher-absences';
    $this->absenceType = AbsenceType::factory()->create();
});

it('can list absences', function (): void {
    $this->loginAsAdmin('TCH001');
    TeacherAbsence::factory()->count(3)->create();

    $response = $this->getJson($this->endpoint);

    $response->assertStatus(200)
        ->assertJsonStructure([
            'success', 'statusCode', 'message',
            'data' => [['id', 'teacherExternalId', 'absenceTypeId', 'startDate', 'endDate', 'isConfirmed']],
            'errors',
        ]);
});

it('can show a single absence', function (): void {
    $this->loginAsAdmin('TCH001');
    $absence = TeacherAbsence::factory()->create();

    $response = $this->getJson("$this->endpoint/{$absence->id}");

    $response->assertStatus(200)
        ->assertJsonPath('data.id', $absence->id);
});

it('returns 404 when absence not found', function (): void {
    $this->loginAsAdmin('TCH001');
    $this->getJson("$this->endpoint/999")
        ->assertStatus(404);
});

it('can create an absence', function (): void {
    $this->loginAsAdmin('TCH001');
    $data = [
        'teacher_external_id' => 'TCH001',
        'absence_type_id' => $this->absenceType->id,
        'start_date' => now()->addDay()->format('Y-m-d'),
        'end_date' => now()->addDays(3)->format('Y-m-d'),
        'observations' => 'Medical appointment',
    ];

    $response = $this->postJson($this->endpoint, $data);

    $response->assertStatus(201)
        ->assertJsonPath('data.teacherExternalId', 'TCH001');

    $this->assertDatabaseHas('gama_teacher_absences', ['teacher_external_id' => 'TCH001']);
});

it('rejects absence completely in the past', function (): void {
    $this->loginAsAdmin('TCH001');
    $response = $this->postJson($this->endpoint, [
        'teacher_external_id' => 'TCH001',
        'absence_type_id' => $this->absenceType->id,
        'start_date' => now()->subDays(10)->format('Y-m-d'),
        'end_date' => now()->subDays(5)->format('Y-m-d'),
    ]);

    $response->assertStatus(422);
});

it('detects overlap and requires confirmation', function (): void {
    $this->loginAsAdmin('TCH001');
    TeacherAbsence::factory()->confirmed()->create([
        'teacher_external_id' => 'TCH001',
        'start_date' => now()->addDay()->format('Y-m-d'),
        'end_date' => now()->addDays(5)->format('Y-m-d'),
    ]);

    $response = $this->postJson($this->endpoint, [
        'teacher_external_id' => 'TCH001',
        'absence_type_id' => $this->absenceType->id,
        'start_date' => now()->addDays(2)->format('Y-m-d'),
        'end_date' => now()->addDays(4)->format('Y-m-d'),
    ]);

    $response->assertStatus(422)
        ->assertJsonStructure(['errors' => ['overlap']]);
});

it('creates absence with is_confirmed bypasses overlap', function (): void {
    $this->loginAsAdmin('TCH001');
    TeacherAbsence::factory()->confirmed()->create([
        'teacher_external_id' => 'TCH001',
        'start_date' => now()->addDay()->format('Y-m-d'),
        'end_date' => now()->addDays(5)->format('Y-m-d'),
    ]);

    $response = $this->postJson($this->endpoint, [
        'teacher_external_id' => 'TCH001',
        'absence_type_id' => $this->absenceType->id,
        'start_date' => now()->addDays(2)->format('Y-m-d'),
        'end_date' => now()->addDays(4)->format('Y-m-d'),
        'is_confirmed' => true,
    ]);

    $response->assertStatus(201);
});

it('can filter by teacher_external_id', function (): void {
    $this->loginAsAdmin('TCH001');
    TeacherAbsence::factory()->count(3)->create();
    TeacherAbsence::factory()->create(['teacher_external_id' => 'FILTER01']);

    $response = $this->getJson("$this->endpoint?teacher_external_id=FILTER01");

    $response->assertStatus(200)
        ->assertJsonCount(1, 'data');
});

it('can soft delete an absence', function (): void {
    $this->loginAsAdmin('TCH001');
    $absence = TeacherAbsence::factory()->create();

    $this->deleteJson("$this->endpoint/{$absence->id}")
        ->assertStatus(200);

    $this->assertSoftDeleted($absence);
});
