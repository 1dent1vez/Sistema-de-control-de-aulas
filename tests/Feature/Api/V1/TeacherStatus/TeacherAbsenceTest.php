<?php

/**
 * @descripcion  Pruebas de API para ausencias de docentes (RF-08).
 *
 * @autor        Ghael Garcia Manjarrez <ghael.engineer@gmail.com>
 *
 * @autorizador  Ruben Alejandro Nolasco Ruiz <correo@dominio.com>
 *
 * @prueba       Ghael Garcia Manjarrez <ghael.engineer@gmail.com>
 *
 * @mantenimiento Ghael Garcia Manjarrez <ghael.engineer@gmail.com>
 *
 * @version      1.1.0
 *
 * @creado       2026-05-14
 *
 * @modificado   2026-05-25
 *
 * @cambios      2026-05-14 - Creación inicial del archivo de pruebas
 *               2026-05-25 - Adición de pruebas de actualización, asociación, stats, validación de clases y notificaciones.
 */

declare(strict_types=1);

use App\Enums\Auth\SamRole;
use App\Models\AbsenceType;
use App\Models\ClassSchedule;
use App\Models\SamIdentity;
use App\Models\Semester;
use App\Models\TeacherAbsence;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    $this->endpoint = '/api/v1/teacher-absences';
    $this->absenceType = AbsenceType::factory()->create();

    // Crear semestre y horarios por defecto para TCH001 para pasar validación de clases
    $semester = Semester::factory()->create([
        'start_date' => now()->subMonths(3)->format('Y-m-d'),
        'end_date' => now()->addMonths(3)->format('Y-m-d'),
    ]);

    $days = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'];
    foreach ($days as $day) {
        ClassSchedule::factory()->create([
            'semester_id' => $semester->id,
            'teacher_external_id' => 'TCH001',
            'weekday' => $day,
            'status' => true,
        ]);
    }
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

it('can update an absence and resync class schedules', function (): void {
    $this->loginAsAdmin('TCH001');

    $mondaySchedule = ClassSchedule::where('teacher_external_id', 'TCH001')->where('weekday', 'monday')->first();
    $tuesdaySchedule = ClassSchedule::where('teacher_external_id', 'TCH001')->where('weekday', 'tuesday')->first();

    $mondayDate = '2026-05-25'; // Lunes
    $tuesdayDate = '2026-05-26'; // Martes

    $absence = TeacherAbsence::factory()->create([
        'teacher_external_id' => 'TCH001',
        'start_date' => $mondayDate,
        'end_date' => $mondayDate,
        'absence_type_id' => $this->absenceType->id,
    ]);

    $absence->classSchedules()->sync([$mondaySchedule->id]);

    $this->assertDatabaseHas('gama_class_schedule_teacher_absence', [
        'teacher_absence_id' => $absence->id,
        'class_schedule_id' => $mondaySchedule->id,
    ]);

    $response = $this->putJson("$this->endpoint/{$absence->id}", [
        'start_date' => $tuesdayDate,
        'end_date' => $tuesdayDate,
        'absence_type_id' => $this->absenceType->id,
    ]);

    $response->assertStatus(200);

    $this->assertDatabaseMissing('gama_class_schedule_teacher_absence', [
        'teacher_absence_id' => $absence->id,
        'class_schedule_id' => $mondaySchedule->id,
    ]);

    $this->assertDatabaseHas('gama_class_schedule_teacher_absence', [
        'teacher_absence_id' => $absence->id,
        'class_schedule_id' => $tuesdaySchedule->id,
    ]);
});

it('prevents a teacher from viewing or deleting another teacher absence', function (): void {
    $absence = TeacherAbsence::factory()->create([
        'teacher_external_id' => 'TCH001',
        'start_date' => now()->addDay()->format('Y-m-d'),
        'end_date' => now()->addDays(2)->format('Y-m-d'),
    ]);

    $identity = SamIdentity::factory()->create([
        'external_id' => 'TCH002',
        'email' => 'tch002@toluca.tecnm.mx',
        'role' => SamRole::TEACHER,
    ]);
    Sanctum::actingAs($identity, ['teacher']);

    $this->getJson("$this->endpoint/{$absence->id}")
        ->assertStatus(403);

    $this->deleteJson("$this->endpoint/{$absence->id}")
        ->assertStatus(403);
});

it('associates class schedules to registered absences', function (): void {
    $this->loginAsAdmin('TCH001');

    $semester = Semester::first();
    $schedule = ClassSchedule::factory()->create([
        'semester_id' => $semester->id,
        'teacher_external_id' => 'TCH001',
        'weekday' => 'monday',
        'status' => true,
    ]);

    $response = $this->postJson($this->endpoint, [
        'teacher_external_id' => 'TCH001',
        'absence_type_id' => $this->absenceType->id,
        'start_date' => '2026-05-25',
        'end_date' => '2026-05-25',
    ]);

    $response->assertStatus(201);

    $absenceId = $response->json('data.id');

    $this->assertDatabaseHas('gama_class_schedule_teacher_absence', [
        'teacher_absence_id' => $absenceId,
        'class_schedule_id' => $schedule->id,
    ]);
});

it('fails to register absence if teacher has no classes in period', function (): void {
    $this->loginAsAdmin('TCH002'); // TCH002 no tiene horarios registrados

    $response = $this->postJson($this->endpoint, [
        'teacher_external_id' => 'TCH002',
        'absence_type_id' => $this->absenceType->id,
        'start_date' => now()->addDay()->format('Y-m-d'),
        'end_date' => now()->addDays(2)->format('Y-m-d'),
    ]);

    $response->assertStatus(422)
        ->assertJsonFragment([
            'success' => false,
            'message' => 'No tienes clases asignadas en el período seleccionado.',
        ]);
});

it('can retrieve stats for teacher absences', function (): void {
    $this->loginAsAdmin('TCH001');

    $response = $this->getJson('/api/v1/teacher-absences/stats');

    $response->assertStatus(200)
        ->assertJsonStructure([
            'success',
            'statusCode',
            'data' => [
                'totalAbsences',
                'totalDaysAbsent',
                'byType',
            ],
        ]);
});

it('notifies admins when a teacher registers an absence', function (): void {
    $admin = SamIdentity::factory()->create([
        'external_id' => 'ADMIN_NOTIF_01',
        'email' => 'admin_notif@toluca.tecnm.mx',
        'role' => SamRole::ADMIN,
    ]);

    $teacherIdentity = SamIdentity::factory()->create([
        'external_id' => 'TCH_NOTIF_01',
        'email' => 'tch_notif@toluca.tecnm.mx',
        'role' => SamRole::TEACHER,
    ]);
    Sanctum::actingAs($teacherIdentity, ['teacher']);

    $semester = Semester::first();
    ClassSchedule::factory()->create([
        'semester_id' => $semester->id,
        'teacher_external_id' => 'TCH_NOTIF_01',
        'weekday' => strtolower(now()->addDay()->englishDayOfWeek),
        'status' => true,
    ]);

    $response = $this->postJson($this->endpoint, [
        'teacher_external_id' => 'TCH_NOTIF_01',
        'absence_type_id' => $this->absenceType->id,
        'start_date' => now()->addDay()->format('Y-m-d'),
        'end_date' => now()->addDay()->format('Y-m-d'),
    ]);

    $response->assertStatus(201);

    $this->assertDatabaseHas('gama_notifications', [
        'notifiable_type' => SamIdentity::class,
        'notifiable_id' => $admin->id,
    ]);
});
