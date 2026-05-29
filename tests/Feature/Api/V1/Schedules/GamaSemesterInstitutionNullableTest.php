<?php

declare(strict_types=1);

use App\Models\Institution;
use App\Models\Semester;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    $this->endpoint = '/api/v1/semesters';
    $this->loginAsAdmin();
    $this->institution = Institution::create([
        'name' => 'Universidad Tecnológica GAMA',
        'code' => 'UTGAMA',
        'is_active' => true,
    ]);
});

it('can create a semester without an institution', function (): void {
    $data = [
        'institution_id' => null,
        'name' => 'Semestre Sin Institucion',
        'start_date' => now()->addDay()->format('Y-m-d'),
        'end_date' => now()->addMonths(5)->format('Y-m-d'),
    ];

    $response = $this->postJson($this->endpoint, $data);

    $response->assertStatus(201)
        ->assertJsonPath('data.name', 'Semestre Sin Institucion')
        ->assertJsonPath('data.institutionId', null);

    $this->assertDatabaseHas('semesters', [
        'name' => 'Semestre Sin Institucion',
        'institution_id' => null,
    ]);
});

it('can create a semester with an institution', function (): void {
    $data = [
        'institution_id' => $this->institution->institution_id,
        'name' => 'Semestre Con Institucion',
        'start_date' => now()->addDay()->format('Y-m-d'),
        'end_date' => now()->addMonths(5)->format('Y-m-d'),
    ];

    $response = $this->postJson($this->endpoint, $data);

    $response->assertStatus(201)
        ->assertJsonPath('data.name', 'Semestre Con Institucion')
        ->assertJsonPath('data.institutionId', $this->institution->institution_id);

    $this->assertDatabaseHas('semesters', [
        'name' => 'Semestre Con Institucion',
        'institution_id' => $this->institution->institution_id,
    ]);
});

it('can update a semester and remove its institution', function (): void {
    $semester = Semester::create([
        'institution_id' => $this->institution->institution_id,
        'name' => 'Semestre Test',
        'start_date' => now()->addDay()->format('Y-m-d'),
        'end_date' => now()->addMonths(5)->format('Y-m-d'),
    ]);

    $data = [
        'institution_id' => null,
        'name' => 'Semestre Test Updated',
        'start_date' => now()->addDay()->format('Y-m-d'),
        'end_date' => now()->addMonths(5)->format('Y-m-d'),
    ];

    $response = $this->putJson("{$this->endpoint}/{$semester->semester_id}", $data);

    $response->assertStatus(200)
        ->assertJsonPath('data.name', 'Semestre Test Updated')
        ->assertJsonPath('data.institutionId', null);

    $this->assertDatabaseHas('semesters', [
        'semester_id' => $semester->semester_id,
        'name' => 'Semestre Test Updated',
        'institution_id' => null,
    ]);
});

it('enforces uniqueness on name when institution is null', function (): void {
    Semester::create([
        'institution_id' => null,
        'name' => 'Unique Sem Name',
        'start_date' => now()->subMonths(6)->format('Y-m-d'),
        'end_date' => now()->subMonth()->format('Y-m-d'),
    ]);

    $data = [
        'institution_id' => null,
        'name' => 'Unique Sem Name',
        'start_date' => now()->addDay()->format('Y-m-d'),
        'end_date' => now()->addMonths(5)->format('Y-m-d'),
    ];

    $response = $this->postJson($this->endpoint, $data);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['name']);
});

it('allows same name across different institutions or null institution', function (): void {
    // Create one with null institution
    Semester::create([
        'institution_id' => null,
        'name' => 'Duplicate Name',
        'start_date' => now()->subMonths(6)->format('Y-m-d'),
        'end_date' => now()->subMonth()->format('Y-m-d'),
    ]);

    // Create another institution
    $anotherInst = Institution::create([
        'name' => 'Universidad GAMA B',
        'code' => 'UTGAMAB',
        'is_active' => true,
    ]);

    // We can create one with Duplicate Name for institution
    $data = [
        'institution_id' => $this->institution->institution_id,
        'name' => 'Duplicate Name',
        'start_date' => now()->addDay()->format('Y-m-d'),
        'end_date' => now()->addMonths(5)->format('Y-m-d'),
    ];

    $this->postJson($this->endpoint, $data)->assertStatus(201);

    // We can also create one with Duplicate Name for anotherInst
    $data2 = [
        'institution_id' => $anotherInst->institution_id,
        'name' => 'Duplicate Name',
        'start_date' => now()->addMonths(6)->format('Y-m-d'),
        'end_date' => now()->addMonths(11)->format('Y-m-d'),
    ];

    $this->postJson($this->endpoint, $data2)->assertStatus(201);

    $this->assertEquals(3, Semester::where('name', 'Duplicate Name')->count());
});
