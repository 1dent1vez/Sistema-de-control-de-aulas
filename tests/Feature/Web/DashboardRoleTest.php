<?php

/**
 * @descripcion  Prueba de feature para validar la separación de dashboards y
 *               la protección de rutas por rol en la web (admin vs teacher).
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
 * @creado       2026-05-24
 *
 * @modificado   2026-05-24
 */

declare(strict_types=1);

use App\Enums\Auth\SamRole;
use App\Models\SamIdentity;

it('redirects guest to login from any dashboard route', function () {
    $this->get('/dashboard')->assertRedirect('/');
    $this->get('/admin/dashboard')->assertRedirect('/');
    $this->get('/docente/dashboard')->assertRedirect('/');
});

it('allows admin to access admin dashboard and dynamic gateway', function () {
    $admin = SamIdentity::factory()->create([
        'role' => SamRole::ADMIN,
        'email' => 'admin.test@toluca.tecnm.mx',
    ]);
    $token = $admin->createToken('test-token')->plainTextToken;

    // Gateway /dashboard para admin
    $this->withUnencryptedCookie('sam_token', $token)
        ->get('/dashboard')
        ->assertStatus(200)
        ->assertViewIs('dashboard.admin');

    // Endpoint directo /admin/dashboard
    $this->withUnencryptedCookie('sam_token', $token)
        ->get('/admin/dashboard')
        ->assertStatus(200);

    // Intento de entrar a /docente/dashboard -> redirecciona a su dashboard de admin
    $this->withUnencryptedCookie('sam_token', $token)
        ->get('/docente/dashboard')
        ->assertRedirect('/admin/dashboard');
});

it('allows teacher to access teacher dashboard and dynamic gateway', function () {
    $teacher = SamIdentity::factory()->create([
        'role' => SamRole::TEACHER,
        'email' => 'teacher.test@toluca.tecnm.mx',
    ]);
    $token = $teacher->createToken('test-token')->plainTextToken;

    // Gateway /dashboard para teacher
    $this->withUnencryptedCookie('sam_token', $token)
        ->get('/dashboard')
        ->assertStatus(200)
        ->assertViewIs('docente.dashboard');

    // Endpoint directo /docente/dashboard
    $this->withUnencryptedCookie('sam_token', $token)
        ->get('/docente/dashboard')
        ->assertStatus(200);

    // Intento de entrar a /admin/dashboard -> redirecciona a su dashboard de docente con flash error
    $this->withUnencryptedCookie('sam_token', $token)
        ->get('/admin/dashboard')
        ->assertRedirect('/docente/dashboard')
        ->assertSessionHas('error', 'No tienes permisos para acceder a esta sección.');
});
