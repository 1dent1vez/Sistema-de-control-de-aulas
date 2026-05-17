<?php

/**
 * @descripcion  Pruebas unitarias para GamaClassroomService.
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
use App\Services\Buildings\GamaClassroomService;

beforeEach(function () {
    $this->service = app(GamaClassroomService::class);
    $this->building = Building::factory()->create();
    $this->level = Level::factory()->create(['building_id' => $this->building->id]);
});

it('can get all classrooms', function () {
    Classroom::factory()->count(2)->create([
        'building_id' => $this->building->id,
        'level_id' => $this->level->id,
    ]);

    $classrooms = $this->service->getAll();

    expect($classrooms)->toHaveCount(2);
});

it('can get classroom by id', function () {
    $classroom = Classroom::factory()->create([
        'building_id' => $this->building->id,
        'level_id' => $this->level->id,
    ]);

    $found = $this->service->getById($classroom->id);

    expect($found->id)->toBe($classroom->id);
});

it('can get classrooms by building id', function () {
    Classroom::factory()->count(3)->create([
        'building_id' => $this->building->id,
        'level_id' => $this->level->id,
    ]);

    $otherBuilding = Building::factory()->create();
    $otherLevel = Level::factory()->create(['building_id' => $otherBuilding->id]);
    Classroom::factory()->create([
        'building_id' => $otherBuilding->id,
        'level_id' => $otherLevel->id,
    ]);

    $classrooms = $this->service->getByBuildingId($this->building->id);

    expect($classrooms)->toHaveCount(3);
});

it('can create classroom', function () {
    $data = [
        'building_id' => $this->building->id,
        'level_id' => $this->level->id,
        'classroom_name' => 'Aula 101',
        'classroom_type' => 'classroom',
        'status' => true,
    ];

    $classroom = $this->service->create($data);

    expect($classroom->classroom_name)->toBe('Aula 101')
        ->and($classroom->classroom_type)->toBe('classroom');

    $this->assertDatabaseHas('gama_classrooms', ['classroom_name' => 'Aula 101']);
});

it('can update classroom', function () {
    $classroom = Classroom::factory()->create([
        'building_id' => $this->building->id,
        'level_id' => $this->level->id,
        'classroom_name' => 'Aula Old',
    ]);

    $updated = $this->service->update($classroom->id, ['classroom_name' => 'Aula New']);

    expect($updated->classroom_name)->toBe('Aula New');
    $this->assertDatabaseHas('gama_classrooms', ['id' => $classroom->id, 'classroom_name' => 'Aula New']);
});

it('can delete classroom', function () {
    $classroom = Classroom::factory()->create([
        'building_id' => $this->building->id,
        'level_id' => $this->level->id,
    ]);

    $result = $this->service->delete($classroom->id);

    expect($result)->toBeTrue();
    $this->assertSoftDeleted('gama_classrooms', ['id' => $classroom->id]);
});
