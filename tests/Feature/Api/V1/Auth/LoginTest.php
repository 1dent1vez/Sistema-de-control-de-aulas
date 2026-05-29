<?php

/**
 * @descripcion  Pruebas de autenticación y login contra el servicio SAM.
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
 * @creado       2026-05-17
 *
 * @modificado   2026-05-25
 *
 * @cambios      2026-05-17 - Creación inicial del archivo de pruebas de login
 *               2026-05-25 - Adición de prueba para validar el fallback local si obtenerPerfil falla
 */

declare(strict_types=1);

use App\Services\Auth\SamService;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Support\Facades\RateLimiter;

beforeEach(function () {
    RateLimiter::for('auth', function () {
        return Limit::perMinute(60);
    });
});

it('succeeds with valid credentials', function () {
    $samServiceMock = Mockery::mock(SamService::class);
    $samServiceMock->shouldReceive('login')->with('admin', 'password', '1234')->andReturn([
        'success' => true,
        'rol' => 'master',
        'token' => null,
        'sistemaUrl' => null,
        'error' => null,
    ]);

    $this->instance(SamService::class, $samServiceMock);

    $response = $this->postJson('/api/v1/auth/login', [
        'username' => 'admin',
        'password' => 'password',
        'captchaCode' => '1234',
    ]);

    $response->assertStatus(200)
        ->assertJsonStructure([
            'success',
            'statusCode',
            'data' => [
                'accessToken',
                'role',
                'redirectUrl',
                'user' => ['externalId', 'fullName', 'email'],
            ],
        ]);
});

it('fails with invalid captcha', function () {
    $samServiceMock = Mockery::mock(SamService::class);
    $samServiceMock->shouldReceive('login')->with('user', 'pass', 'wrong')->andReturn([
        'success' => false,
        'error' => 'Credenciales inválidas',
    ]);

    $this->instance(SamService::class, $samServiceMock);

    $response = $this->postJson('/api/v1/auth/login', [
        'username' => 'user',
        'password' => 'pass',
        'captchaCode' => 'wrong',
    ]);

    $response->assertStatus(401);
});

it('fails with invalid credentials', function () {
    $samServiceMock = Mockery::mock(SamService::class);
    $samServiceMock->shouldReceive('validarCaptcha')->andReturn(true);
    $samServiceMock->shouldReceive('login')->andReturn([
        'success' => false,
        'error' => 'Credenciales inválidas',
    ]);

    $this->instance(SamService::class, $samServiceMock);

    $response = $this->postJson('/api/v1/auth/login', [
        'username' => 'user',
        'password' => 'wrong',
        'captchaCode' => '1234',
    ]);

    $response->assertStatus(401);
});

it('assigns teacher role as fallback when sam returns unknown role', function () {
    $samServiceMock = Mockery::mock(SamService::class);
    $samServiceMock->shouldReceive('validarCaptcha')->andReturn(true);
    $samServiceMock->shouldReceive('login')->andReturn([
        'success' => true,
        'rol' => 'empleado',
        'token' => 'some-token',
        'sistemaUrl' => 'http://test',
    ]);
    $samServiceMock->shouldReceive('obtenerPerfil')->andReturn([
        'rol' => 'unknown_role',
        'numero_empleado' => '123',
        'correo' => 'test@test.com',
    ]);

    $this->instance(SamService::class, $samServiceMock);

    $response = $this->postJson('/api/v1/auth/login', [
        'username' => 'user',
        'password' => 'pass',
        'captchaCode' => '1234',
    ]);

    $response->assertStatus(200)
        ->assertJsonPath('data.role', null)
        ->assertJsonPath('data.redirectUrl', '/espera-rol');
});

it('fails with service error when sam is down', function () {
    $samServiceMock = Mockery::mock(SamService::class);
    $samServiceMock->shouldReceive('validarCaptcha')->andReturn(true);
    $samServiceMock->shouldReceive('login')->andReturn([
        'success' => false,
        'error' => 'Servicio SAM no disponible',
    ]);

    $this->instance(SamService::class, $samServiceMock);

    $response = $this->postJson('/api/v1/auth/login', [
        'username' => 'user',
        'password' => 'pass',
        'captchaCode' => '1234',
    ]);

    $response->assertStatus(500)
        ->assertJsonFragment([
            'success' => false,
            'message' => 'Ocurrió un error inesperado. Contacta al administrador.',
        ]);
});

it('falls back to local database when obtenerPerfil fails', function () {
    $samServiceMock = Mockery::mock(SamService::class);
    $samServiceMock->shouldReceive('validarCaptcha')->andReturn(true);
    $samServiceMock->shouldReceive('login')->andReturn([
        'success' => true,
        'rol' => 'empleado',
        'token' => 'some-token',
        'sistemaUrl' => 'http://test',
    ]);
    $samServiceMock->shouldReceive('obtenerPerfil')->andThrow(new RuntimeException('SAM real profile endpoint failed'));

    $this->instance(SamService::class, $samServiceMock);

    $response = $this->postJson('/api/v1/auth/login', [
        'username' => 'NEW_TCH_001',
        'password' => 'pass',
        'captchaCode' => '1234',
    ]);

    $response->assertStatus(200)
        ->assertJsonPath('data.role', 'teacher')
        ->assertJsonPath('data.redirectUrl', '/docente/dashboard');

    $this->assertDatabaseHas('sam_identities', [
        'external_id' => 'NEW_TCH_001',
        'role' => 'teacher',
    ]);
});
