<?php

declare(strict_types=1);

use App\Models\Building;
use App\Models\Classroom;
use App\Models\ClassSchedule;
use App\Models\Institution;
use App\Models\Level;
use App\Models\Semester;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    $this->endpoint = '/api/v1/class-schedules';
    $institution = Institution::factory()->create();
    $building = Building::factory()->create(['institution_id' => $institution->id]);
    $level = Level::factory()->create(['building_id' => $building->id]);
    $this->classroom = Classroom::factory()->create([
        'building_id' => $building->id,
        'level_id' => $level->id,
    ]);
    $this->semester = Semester::factory()->create([
        'institution_id' => $institution->id,
    ]);
});

it('can list class schedules', function (): void {
    ClassSchedule::factory()->count(3)->create();

    $response = $this->getJson($this->endpoint);

    $response->assertStatus(200)
        ->assertJsonStructure([
            'success', 'statusCode', 'message',
            'data' => [['id', 'semesterId', 'classroomId', 'teacherExternalId', 'subjectName', 'weekday']],
            'errors',
        ]);
});

it('can show a single schedule', function (): void {
    $schedule = ClassSchedule::factory()->create();

    $response = $this->getJson("$this->endpoint/{$schedule->id}");

    $response->assertStatus(200)
        ->assertJsonPath('data.id', $schedule->id);
});

it('returns 404 when schedule not found', function (): void {
    $this->getJson("$this->endpoint/999")
        ->assertStatus(404)
        ->assertJsonFragment(['success' => false]);
});

it('can create a class schedule', function (): void {
    $data = [
        'semester_id' => $this->semester->id,
        'classroom_id' => $this->classroom->id,
        'teacher_external_id' => 'TCH001',
        'subject_name' => 'Mathematics',
        'group_name' => 'A1',
        'weekday' => 'monday',
        'start_time' => '08:00',
        'end_time' => '10:00',
    ];

    $response = $this->postJson($this->endpoint, $data);

    $response->assertStatus(201)
        ->assertJsonPath('data.teacherExternalId', 'TCH001');

    $this->assertDatabaseHas('gama_class_schedules', ['teacher_external_id' => 'TCH001']);
});

it('rejects overlapping schedules', function (): void {
    ClassSchedule::factory()->create([
        'classroom_id' => $this->classroom->id,
        'weekday' => 'monday',
        'start_time' => '08:00',
        'end_time' => '10:00',
    ]);

    $response = $this->postJson($this->endpoint, [
        'semester_id' => $this->semester->id,
        'classroom_id' => $this->classroom->id,
        'teacher_external_id' => 'TCH002',
        'subject_name' => 'Physics',
        'group_name' => 'B1',
        'weekday' => 'monday',
        'start_time' => '09:00',
        'end_time' => '11:00',
    ]);

    $response->assertStatus(422);
});

it('can soft delete a schedule', function (): void {
    $schedule = ClassSchedule::factory()->create();

    $this->deleteJson("$this->endpoint/{$schedule->id}")
        ->assertStatus(200);

    $this->assertSoftDeleted($schedule);
});

it('can filter schedules by filters', function (): void {
    ClassSchedule::factory()->count(3)->create();
    $schedule = ClassSchedule::factory()->create(['teacher_external_id' => 'FILTER01']);

    $response = $this->getJson("$this->endpoint?teacher_external_id=FILTER01");

    $response->assertStatus(200)
        ->assertJsonCount(1, 'data');
});
