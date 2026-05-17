<?php

declare(strict_types=1);

use App\Enums\Auth\SamRole;
use App\Models\AbsenceType;
use App\Models\Institution;
use App\Models\SamIdentity;
use App\Models\TeacherAbsence;
use Laravel\Sanctum\Sanctum;

it('forbids guest to create building', function () {
    $response = $this->postJson('/api/v1/buildings', [
        'name' => 'B1', 'code' => 'B1',
    ]);
    $response->assertStatus(401);
});

it('forbids teacher to create building', function () {
    $teacher = SamIdentity::factory()->create(['role' => SamRole::TEACHER]);
    Sanctum::actingAs($teacher, ['teacher']);

    $response = $this->postJson('/api/v1/buildings', [
        'name' => 'B1', 'code' => 'B1', 'institution_id' => Institution::factory()->create()->id, 'level_count' => 1,
    ]);
    $response->assertStatus(403);
});

it('allows admin to create building', function () {
    $admin = SamIdentity::factory()->create(['role' => SamRole::ADMIN]);
    Sanctum::actingAs($admin, ['*']);

    $response = $this->postJson('/api/v1/buildings', [
        'name' => 'B1', 'code' => 'B1', 'is_active' => true, 'institution_id' => Institution::factory()->create()->id, 'level_count' => 1,
    ]);
    $response->assertStatus(201);
});

it('allows teacher to create own absence', function () {
    $teacher = SamIdentity::factory()->create(['role' => SamRole::TEACHER]);
    Sanctum::actingAs($teacher, ['teacher']);

    $response = $this->postJson('/api/v1/teacher-absences', [
        'teacher_external_id' => $teacher->external_id,
        'start_date' => now()->addDays(5)->format('Y-m-d H:i:s'),
        'end_date' => now()->addDays(5)->addHours(2)->format('Y-m-d H:i:s'),
        'absence_type_id' => AbsenceType::factory()->create()->id,
        'is_confirmed' => true,
        'comments' => 'test',
    ]);
    $response->assertStatus(201);
});

it('forbids teacher to view other absence', function () {
    $teacher = SamIdentity::factory()->create(['role' => SamRole::TEACHER]);
    Sanctum::actingAs($teacher, ['teacher']);

    $otherAbsence = TeacherAbsence::factory()->create([
        'teacher_external_id' => 'OTHER123',
        'absence_type_id' => AbsenceType::factory()->create()->id,
    ]);

    $response = $this->getJson("/api/v1/teacher-absences/{$otherAbsence->id}");
    $response->assertStatus(403);
});

it('allows admin to view all absences', function () {
    $admin = SamIdentity::factory()->create(['role' => SamRole::ADMIN]);
    Sanctum::actingAs($admin, ['*']);

    $otherAbsence = TeacherAbsence::factory()->create([
        'teacher_external_id' => 'OTHER123',
        'absence_type_id' => AbsenceType::factory()->create()->id,
    ]);

    $response = $this->getJson("/api/v1/teacher-absences/{$otherAbsence->id}");
    $response->assertStatus(200);
});
