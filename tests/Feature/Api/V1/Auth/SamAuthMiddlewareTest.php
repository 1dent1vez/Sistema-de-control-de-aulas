<?php

/**
 * @descripcion  Prueba de feature para validar el funcionamiento del middleware SamAuthMiddleware
 *               con cookies de sesión sam_token y logs de diagnóstico.
 *
 * @autor        Equipo GAMA
 *
 * @autorizador  Ruben Alejandro Nolasco Ruiz <correo@dominio.com>
 *
 * @prueba       Diego Miguel Hernandez Fabela
 *
 * @mantenimiento Ghael Garcia Manjarrez <ghael.engineer@gmail.com>
 *
 * @version      1.0.0
 *
 * @creado       2026-05-22
 *
 * @modificado   2026-05-22
 *
 * @cambios      2026-05-22 - Creación inicial del test para middleware SamAuthMiddleware
 */

declare(strict_types=1);

use App\Enums\Auth\SamRole;
use App\Models\SamIdentity;

it('redirects to login when no sam_token is provided for web routes', function () {
    $response = $this->get('/dashboard');

    $response->assertRedirect('/');
});

it('allows access to dashboard when valid sam_token cookie is provided', function () {
    $identity = SamIdentity::factory()->create([
        'role' => SamRole::ADMIN,
        'email' => 'admin@controlaulas.edu.mx',
    ]);

    // Crear token de Sanctum
    $tokenResult = $identity->createToken('test');
    $token = $tokenResult->plainTextToken;

    $response = $this->withUnencryptedCookie('sam_token', $token)
        ->get('/dashboard');

    $response->assertStatus(200);
});

it('redirects to login when sam_token cookie is expired', function () {
    $identity = SamIdentity::factory()->create([
        'role' => SamRole::TEACHER,
    ]);

    $tokenResult = $identity->createToken('test');
    $token = $tokenResult->plainTextToken;

    // Forzar expiración en base de datos
    $accessToken = $tokenResult->accessToken;
    $accessToken->expires_at = now()->subMinutes(1);
    $accessToken->save();

    $response = $this->withUnencryptedCookie('sam_token', $token)
        ->get('/dashboard');

    $response->assertRedirect('/');
});

it('redirects to login when sam_token cookie is invalid', function () {
    $response = $this->withUnencryptedCookie('sam_token', 'invalid-token-value')
        ->get('/dashboard');

    $response->assertRedirect('/');
});
