<?php

declare(strict_types=1);

use App\Models\Institution;
use App\Models\Semester;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    $this->endpoint = '/api/v1/semesters';
    $this->institution = Institution::factory()->create();
});

it('can list semesters', function (): void {
    Semester::factory()->count(3)->create();

    $response = $this->getJson($this->endpoint);

    $response->assertStatus(200)
        ->assertJsonStructure([
            'success', 'statusCode', 'message',
            'data' => [['id', 'name', 'startDate', 'endDate', 'isActive']],
            'errors',
        ]);
});

it('can show current semester', function (): void {
    Semester::factory()->create([
        'institution_id' => $this->institution->institution_id,
        'start_date' => now()->subDays(5)->format('Y-m-d'),
        'end_date' => now()->addDays(5)->format('Y-m-d'),
    ]);

    $response = $this->getJson("$this->endpoint/current");

    $response->assertStatus(200)
        ->assertJsonPath('success', true);
});

it('returns 404 when no current semester', function (): void {
    Semester::factory()->expired()->create();

    $this->getJson("$this->endpoint/current")
        ->assertStatus(404);
});

it('can create a semester', function (): void {
    $this->loginAsAdmin();
    $data = [
        'institution_id' => $this->institution->institution_id,
        'name' => 'Enero-Junio 2026',
        'start_date' => now()->addDay()->format('Y-m-d'),
        'end_date' => now()->addMonths(5)->format('Y-m-d'),
    ];

    $response = $this->postJson($this->endpoint, $data);

    $response->assertStatus(201)
        ->assertJsonPath('data.name', 'Enero-Junio 2026');

    $this->assertDatabaseHas('semesters', ['name' => 'Enero-Junio 2026']);
});

it('rejects overlapping semester dates', function (): void {
    $this->loginAsAdmin();
    Semester::factory()->create([
        'institution_id' => $this->institution->institution_id,
        'start_date' => now()->addDay()->format('Y-m-d'),
        'end_date' => now()->addMonths(3)->format('Y-m-d'),
    ]);

    $response = $this->postJson($this->endpoint, [
        'institution_id' => $this->institution->institution_id,
        'name' => 'Overlapping Semester',
        'start_date' => now()->addMonth()->format('Y-m-d'),
        'end_date' => now()->addMonths(2)->format('Y-m-d'),
    ]);

    $response->assertStatus(422);
});

it('can soft delete a semester', function (): void {
    $this->loginAsAdmin();
    $semester = Semester::factory()->create();

    $this->deleteJson("$this->endpoint/{$semester->semester_id}")
        ->assertStatus(200);

    $this->assertSoftDeleted($semester);
});

it('rejects invalid date range', function (): void {
    $this->loginAsAdmin();
    $response = $this->postJson($this->endpoint, [
        'institution_id' => $this->institution->institution_id,
        'name' => 'Invalid',
        'start_date' => now()->addMonths(2)->format('Y-m-d'),
        'end_date' => now()->addMonth()->format('Y-m-d'),
    ]);

    $response->assertStatus(422);
});
