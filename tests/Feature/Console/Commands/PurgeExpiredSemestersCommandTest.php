<?php

/**
 * @descripcion  Pruebas para el comando de Artisan PurgeExpiredSemestersCommand.
 *
 * @autor        Agente OpenCode
 *
 * @autorizador  Ruben Alejandro Nolasco Ruiz <correo@dominio.com>
 *
 * @prueba       Agente OpenCode
 *
 * @mantenimiento Agente OpenCode
 *
 * @version      1.0.0
 *
 * @creado       2026-05-24
 *
 * @modificado   2026-05-24
 *
 * @cambios      2026-05-24 - Creación de pruebas de integración para el comando
 */

declare(strict_types=1);

namespace Tests\Feature\Console\Commands;

use App\Enums\Auth\SamRole;
use App\Models\ClassSchedule;
use App\Models\Institution;
use App\Models\SamIdentity;
use App\Models\Semester;
use App\Notifications\PurgeFailedNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;

uses(RefreshDatabase::class);

it('purges expired semesters and their schedules', function (): void {
    $institution = Institution::factory()->create();

    // Semester expired
    $expiredSemester = Semester::factory()->expired()->create([
        'institution_id' => $institution->institution_id,
        'name' => 'Expired Semester',
    ]);

    // Active semester (not expired)
    $activeSemester = Semester::factory()->create([
        'institution_id' => $institution->institution_id,
        'name' => 'Active Semester',
        'start_date' => now()->subDays(5)->format('Y-m-d'),
        'end_date' => now()->addDays(5)->format('Y-m-d'),
    ]);

    // Create schedules for both
    $schedule1 = ClassSchedule::factory()->create([
        'semester_id' => $expiredSemester->semester_id,
    ]);

    $schedule2 = ClassSchedule::factory()->create([
        'semester_id' => $activeSemester->semester_id,
    ]);

    // Run the command
    $this->artisan('purge:expired-semesters')
        ->expectsOutput("Semestre 'Expired Semester' (ID: {$expiredSemester->semester_id}) purgado.")
        ->expectsOutput('1 semestre(s) purgado(s).')
        ->assertExitCode(0);

    // Verify database status
    $this->assertSoftDeleted('semesters', ['semester_id' => $expiredSemester->semester_id]);
    $this->assertSoftDeleted('class_schedules', ['class_schedule_id' => $schedule1->class_schedule_id]);

    $this->assertDatabaseHas('semesters', ['semester_id' => $activeSemester->semester_id, 'deleted_at' => null]);
    $this->assertDatabaseHas('class_schedules', ['class_schedule_id' => $schedule2->class_schedule_id, 'deleted_at' => null]);
});

it('does nothing when there are no expired semesters', function (): void {
    $institution = Institution::factory()->create();

    Semester::factory()->create([
        'institution_id' => $institution->institution_id,
        'start_date' => now()->subDays(5)->format('Y-m-d'),
        'end_date' => now()->addDays(5)->format('Y-m-d'),
    ]);

    $this->artisan('purge:expired-semesters')
        ->expectsOutput('No hay semestres caducados.')
        ->assertExitCode(0);
});

it('notifies administrators on failure and continues with other semesters', function (): void {
    Notification::fake();

    $institution = Institution::factory()->create();

    $admin = SamIdentity::factory()->create([
        'role' => SamRole::ADMIN,
        'email' => 'admin@gama.com',
    ]);

    $expired1 = Semester::factory()->expired()->create([
        'institution_id' => $institution->institution_id,
        'name' => 'Expired 1',
    ]);

    $expired2 = Semester::factory()->expired()->create([
        'institution_id' => $institution->institution_id,
        'name' => 'Expired 2',
    ]);

    Semester::deleting(function ($semester) use ($expired1) {
        if ($semester->semester_id === $expired1->semester_id) {
            throw new \Exception('Simulated database error');
        }
    });

    $this->artisan('purge:expired-semesters')
        ->expectsOutput("Error al purgar semestre caducado ID: {$expired1->semester_id}")
        ->expectsOutput("Semestre 'Expired 2' (ID: {$expired2->semester_id}) purgado.")
        ->assertExitCode(0);

    Notification::assertSentTo(
        $admin,
        PurgeFailedNotification::class,
        function ($notification) use ($expired1) {
            return $notification->semester->semester_id === $expired1->semester_id && str_contains($notification->error, 'Simulated database error');
        }
    );

    $this->assertDatabaseHas('semesters', ['semester_id' => $expired1->semester_id, 'deleted_at' => null]);
    $this->assertSoftDeleted('semesters', ['semester_id' => $expired2->semester_id]);
});
