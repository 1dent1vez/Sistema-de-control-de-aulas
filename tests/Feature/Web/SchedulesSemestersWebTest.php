<?php

/**
 * @descripcion  Prueba de feature para validar el acceso web a la vista de Semestres.
 *
 * @autor        Antigravity <support@google.com>
 *
 * @autorizador  Ruben Alejandro Nolasco Ruiz <correo@dominio.com>
 *
 * @prueba       Antigravity <support@google.com>
 *
 * @mantenimiento Antigravity <support@google.com>
 *
 * @version      1.0.0
 *
 * @creado       2026-05-25
 *
 * @modificado   2026-05-25
 *
 * @cambios      2026-05-25 - Creación inicial de las pruebas de acceso web a semestres.
 */

declare(strict_types=1);

use App\Enums\Auth\SamRole;
use App\Models\SamIdentity;

it('redirects guest to login from schedules semesters route', function (): void {
    $this->get('/horarios/semestres')->assertRedirect('/');
});

it('allows admin to access schedules semesters view', function (): void {
    $admin = SamIdentity::factory()->create([
        'role' => SamRole::ADMIN,
        'email' => 'admin.semestres@toluca.tecnm.mx',
    ]);
    $token = $admin->createToken('test-token')->plainTextToken;

    $this->withUnencryptedCookie('sam_token', $token)
        ->get('/horarios/semestres')
        ->assertStatus(200)
        ->assertViewIs('horarios.semestres.index');
});

it('redirects teacher from schedules semesters view with permission error', function (): void {
    $teacher = SamIdentity::factory()->create([
        'role' => SamRole::TEACHER,
        'email' => 'teacher.semestres@toluca.tecnm.mx',
    ]);
    $token = $teacher->createToken('test-token')->plainTextToken;

    $this->withUnencryptedCookie('sam_token', $token)
        ->get('/horarios/semestres')
        ->assertRedirect('/docente/dashboard')
        ->assertSessionHas('error', 'No tienes permisos para acceder a esta sección.');
});
