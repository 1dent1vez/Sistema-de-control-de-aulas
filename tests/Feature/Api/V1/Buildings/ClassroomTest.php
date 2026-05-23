<?php

/**
 * @descripcion  Tests de feature para los endpoints de aulas.
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
 * @creado       2026-05-13
 *
 * @modificado   2026-05-13
 *
 * @cambios      2026-05-13 - Creación inicial de los tests
 */

declare(strict_types=1);

use App\Models\Building;
use App\Models\Classroom;
use App\Models\Institution;
use App\Models\Level;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    $this->endpoint = '/api/v1/classrooms';
    $institution = Institution::factory()->create();
    $this->building = Building::factory()->create(['institution_id' => $institution->id]);
    $this->level = Level::factory()->create(['building_id' => $this->building->id]);
});

it('can list all classrooms', function (): void {
    Classroom::factory()->count(3)->create();

    $response = $this->getJson($this->endpoint);

    $response->assertStatus(200)
        ->assertJsonStructure([
            'success', 'statusCode', 'message',
            'data' => [['id', 'buildingId', 'levelId', 'classroomName', 'classroomType', 'classroomTypeLabel', 'status']],
            'errors',
        ]);
});

it('can show a single classroom', function (): void {
    $classroom = Classroom::factory()->create();

    $response = $this->getJson("$this->endpoint/{$classroom->id}");

    $response->assertStatus(200)
        ->assertJsonPath('data.id', $classroom->id);
});

it('returns 404 when classroom not found', function (): void {
    $this->getJson("$this->endpoint/999")
        ->assertStatus(404)
        ->assertJsonFragment(['success' => false]);
});

it('can create a classroom', function (): void {
    $this->loginAsAdmin();
    $data = [
        'building_id' => $this->building->id,
        'level_id' => $this->level->id,
        'classroom_name' => 'A101',
        'classroom_type' => 'classroom',
    ];

    $response = $this->postJson($this->endpoint, $data);

    $response->assertStatus(201)
        ->assertJsonPath('data.classroomName', 'A101')
        ->assertJsonPath('data.classroomTypeLabel', 'Salón');

    $this->assertDatabaseHas('gama_classrooms', ['classroom_name' => 'A101']);
});

it('validates unique classroom name per building', function (): void {
    $this->loginAsAdmin();
    Classroom::factory()->create([
        'building_id' => $this->building->id,
        'classroom_name' => 'A101',
    ]);

    $this->postJson($this->endpoint, [
        'building_id' => $this->building->id,
        'level_id' => $this->level->id,
        'classroom_name' => 'A101',
        'classroom_type' => 'classroom',
    ])->assertStatus(422);
});

it('validates classroom type must be classroom or computer_lab', function (): void {
    $this->loginAsAdmin();
    $this->postJson($this->endpoint, [
        'building_id' => $this->building->id,
        'level_id' => $this->level->id,
        'classroom_name' => 'B202',
        'classroom_type' => 'invalid_type',
    ])->assertStatus(422);
});

it('can soft delete a classroom', function (): void {
    $this->loginAsAdmin();
    $classroom = Classroom::factory()->create();

    $this->deleteJson("$this->endpoint/{$classroom->id}")
        ->assertStatus(200);

    $this->assertSoftDeleted($classroom);
});

it('can get classrooms by building', function (): void {
    Classroom::factory()->count(2)->create(['building_id' => $this->building->id]);

    $response = $this->getJson("/api/v1/buildings/{$this->building->id}/classrooms");

    $response->assertStatus(200)
        ->assertJsonCount(2, 'data');
});
