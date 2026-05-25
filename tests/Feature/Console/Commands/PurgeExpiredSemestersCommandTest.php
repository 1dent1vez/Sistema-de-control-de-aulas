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
        'institution_id' => $institution->id,
        'name' => 'Expired Semester',
    ]);

    // Active semester (not expired)
    $activeSemester = Semester::factory()->create([
        'institution_id' => $institution->id,
        'name' => 'Active Semester',
        'start_date' => now()->subDays(5)->format('Y-m-d'),
        'end_date' => now()->addDays(5)->format('Y-m-d'),
    ]);

    // Create schedules for both
    $schedule1 = ClassSchedule::factory()->create([
        'semester_id' => $expiredSemester->id,
    ]);

    $schedule2 = ClassSchedule::factory()->create([
        'semester_id' => $activeSemester->id,
    ]);

    // Run the command
    $this->artisan('purge:expired-semesters')
        ->expectsOutput("Semestre 'Expired Semester' (ID: {$expiredSemester->id}) purgado.")
        ->expectsOutput('1 semestre(s) purgado(s).')
        ->assertExitCode(0);

    // Verify database status
    $this->assertSoftDeleted('gama_semesters', ['id' => $expiredSemester->id]);
    $this->assertSoftDeleted('gama_class_schedules', ['id' => $schedule1->id]);

    $this->assertDatabaseHas('gama_semesters', ['id' => $activeSemester->id, 'deleted_at' => null]);
    $this->assertDatabaseHas('gama_class_schedules', ['id' => $schedule2->id, 'deleted_at' => null]);
});

it('does nothing when there are no expired semesters', function (): void {
    $institution = Institution::factory()->create();

    Semester::factory()->create([
        'institution_id' => $institution->id,
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
        'institution_id' => $institution->id,
        'name' => 'Expired 1',
    ]);

    $expired2 = Semester::factory()->expired()->create([
        'institution_id' => $institution->id,
        'name' => 'Expired 2',
    ]);

    Semester::deleting(function ($semester) use ($expired1) {
        if ($semester->id === $expired1->id) {
            throw new \Exception('Simulated database error');
        }
    });

    $this->artisan('purge:expired-semesters')
        ->expectsOutput("Error al purgar semestre caducado ID: {$expired1->id}")
        ->expectsOutput("Semestre 'Expired 2' (ID: {$expired2->id}) purgado.")
        ->assertExitCode(0);

    Notification::assertSentTo(
        $admin,
        PurgeFailedNotification::class,
        function ($notification) use ($expired1) {
            return $notification->semester->id === $expired1->id && str_contains($notification->error, 'Simulated database error');
        }
    );

    $this->assertDatabaseHas('gama_semesters', ['id' => $expired1->id, 'deleted_at' => null]);
    $this->assertSoftDeleted('gama_semesters', ['id' => $expired2->id]);
});
