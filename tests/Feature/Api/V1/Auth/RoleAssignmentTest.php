<?php

declare(strict_types=1);

use App\Enums\Auth\SamRole;
use App\Models\SamIdentity;
use App\Services\Auth\SamAuthService;
use App\Services\Auth\SamService;
use Illuminate\Contracts\Debug\ExceptionHandler;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Laravel\Sanctum\Sanctum;

beforeEach(function () {
    // Clona la configuración sqlite en memoria para la conexión 'sam' en pruebas
    config(['database.connections.sam' => [
        'driver' => 'sqlite',
        'database' => ':memory:',
        'prefix' => '',
    ]]);

    // Crear la tabla empleados en la base de datos 'sam' en memoria
    Schema::connection('sam')->create('empleados', function ($table) {
        $table->id('id_empleado');
        $table->string('nombre');
        $table->string('apellidoPa');
        $table->string('apellidoMa');
        $table->string('usuario');
        $table->string('correo');
    });
});

it('allows admin to search SAM employees in real-time', function () {
    $admin = SamIdentity::factory()->create(['role' => SamRole::ADMIN]);
    Sanctum::actingAs($admin, ['*']);

    // Insertar un empleado de prueba en la base de datos 'sam'
    DB::connection('sam')->table('empleados')->insert([
        'id_empleado' => 9999,
        'nombre' => 'Juan Carlos',
        'apellidoPa' => 'Pérez',
        'apellidoMa' => 'López',
        'usuario' => 'jcperez',
        'correo' => 'jcperez@toluca.tecnm.mx',
    ]);

    $response = $this->getJson('/api/v1/sam/empleados?q=Juan');

    $response->assertStatus(200);
    $response->assertJsonFragment([
        'externalId' => '9999',
        'fullName' => 'Juan Carlos Pérez López',
        'usuario' => 'jcperez',
        'correo' => 'jcperez@toluca.tecnm.mx',
    ]);
});

it('forbids teacher from searching SAM employees', function () {
    $teacher = SamIdentity::factory()->create(['role' => SamRole::TEACHER]);
    Sanctum::actingAs($teacher, ['teacher']);

    $response = $this->getJson('/api/v1/sam/empleados?q=Juan');

    $response->assertStatus(403);
});

it('allows admin to assign role with password confirmation', function () {
    $admin = SamIdentity::factory()->create([
        'role' => SamRole::ADMIN,
        'password' => bcrypt('admin-password123'),
    ]);
    Sanctum::actingAs($admin, ['*']);

    $target = SamIdentity::factory()->create(['role' => SamRole::TEACHER]);

    $response = $this->postJson("/api/v1/sam-identities/{$target->external_id}/assign-role", [
        'role' => 'admin',
        'current_password' => 'admin-password123',
    ]);

    $response->assertStatus(200);
    $this->assertDatabaseHas('gama_sam_identities', [
        'id' => $target->id,
        'role' => 'admin',
    ]);
});

it('forbids teacher to assign role', function () {
    $teacher = SamIdentity::factory()->create(['role' => SamRole::TEACHER]);
    Sanctum::actingAs($teacher, ['teacher']);

    $target = SamIdentity::factory()->create(['role' => SamRole::TEACHER]);

    $response = $this->postJson("/api/v1/sam-identities/{$target->external_id}/assign-role", [
        'role' => 'admin',
    ]);

    $response->assertStatus(403);
});

it('fails to assign role when SAM is offline', function () {
    $admin = SamIdentity::factory()->create([
        'role' => SamRole::ADMIN,
        'password' => bcrypt('admin-password123'),
    ]);
    Sanctum::actingAs($admin, ['*']);

    $target = SamIdentity::factory()->create(['role' => SamRole::TEACHER]);

    // Configurar sam mock como false
    config(['sam.mock_enabled' => false]);

    // Mockear SamService checkConnection para retornar false
    $this->mock(SamService::class, function ($mock) {
        $mock->shouldReceive('checkConnection')->andReturn(false);
    });

    $response = $this->postJson("/api/v1/sam-identities/{$target->external_id}/assign-role", [
        'role' => 'admin',
        'current_password' => 'admin-password123',
    ]);

    $response->assertStatus(503);
    $response->assertJson([
        'success' => false,
        'statusCode' => 503,
        'message' => 'Servicio no disponible. El sistema no puede contactar a SAM. Intente mas tarde o contacte al administrador.',
    ]);
});

it('revokes sanctum tokens immediately upon role change', function () {
    $admin = SamIdentity::factory()->create([
        'role' => SamRole::ADMIN,
        'password' => bcrypt('admin-password123'),
    ]);
    Sanctum::actingAs($admin, ['*']);

    $target = SamIdentity::factory()->create(['role' => SamRole::TEACHER]);

    // Crear token para el target
    $target->createToken('test-token');
    $this->assertDatabaseHas('personal_access_tokens', [
        'tokenable_id' => $target->id,
    ]);

    $response = $this->postJson("/api/v1/sam-identities/{$target->external_id}/assign-role", [
        'role' => 'admin',
        'current_password' => 'admin-password123',
    ]);

    $response->assertStatus(200);
    // Verificar que el token fue revocado (eliminado de DB)
    $this->assertDatabaseMissing('personal_access_tokens', [
        'tokenable_id' => $target->id,
    ]);
});

it('renders ValueError as 422 JSON response', function () {
    $exception = new ValueError("Value 'invalid-role' is not a valid backing value for enum 'App\Enums\Auth\SamRole'");

    $handler = app(ExceptionHandler::class);
    $request = Request::create('/api/test', 'POST');
    $request->headers->set('Accept', 'application/json');

    $response = $handler->render($request, $exception);

    expect($response->getStatusCode())->toBe(422);
    $data = json_decode($response->getContent(), true);
    expect($data['message'])->toBe('Rol no autorizado en este sistema. Su perfil no tiene permisos para acceder. Contacte al administrador.');
});

it('maps employee with CRUD permissions from SAM to ADMIN role', function () {
    $samAuthService = app(SamAuthService::class);

    // Obtener reflejo del método privado mapearRolLocal
    $reflection = new ReflectionClass(SamAuthService::class);
    $method = $reflection->getMethod('mapearRolLocal');
    $method->setAccessible(true);

    // Caso 1: Empleado sin permisos CRUD completos
    $perfilComun = [
        'rol' => 'empleado',
        'crear' => false,
        'leer' => true,
        'editar' => false,
        'eliminar' => false,
    ];
    $rolLocal1 = $method->invokeArgs($samAuthService, [$perfilComun]);
    expect($rolLocal1)->toBe(SamRole::TEACHER);

    // Caso 2: Empleado con permisos CRUD completos
    $perfilPrivilegiado = [
        'rol' => 'empleado',
        'crear' => true,
        'leer' => true,
        'editar' => true,
        'eliminar' => false,
    ];
    $rolLocal2 = $method->invokeArgs($samAuthService, [$perfilPrivilegiado]);
    expect($rolLocal2)->toBe(SamRole::ADMIN);

    // Caso 3: Master siempre es ADMIN
    $perfilMaster = [
        'rol' => 'master',
        'crear' => false,
        'leer' => false,
        'editar' => false,
        'eliminar' => false,
    ];
    $rolLocal3 = $method->invokeArgs($samAuthService, [$perfilMaster]);
    expect($rolLocal3)->toBe(SamRole::ADMIN);
});

it('allows user to set their local password', function () {
    $user = SamIdentity::factory()->create();
    Sanctum::actingAs($user, ['*']);

    $response = $this->postJson('/api/v1/sam-identities/set-password', [
        'password' => 'new-password123',
        'password_confirmation' => 'new-password123',
    ]);

    $response->assertStatus(200);
    expect(Hash::check('new-password123', $user->fresh()->password))->toBeTrue();
});

it('allows admin to search local teachers', function () {
    $admin = SamIdentity::factory()->create(['role' => SamRole::ADMIN]);
    Sanctum::actingAs($admin, ['*']);

    $teacher1 = SamIdentity::factory()->create([
        'role' => SamRole::TEACHER,
        'full_name' => 'Profesor Jirafales',
    ]);
    $teacher2 = SamIdentity::factory()->create([
        'role' => SamRole::TEACHER,
        'full_name' => 'Profesor Super O',
    ]);
    $anotherAdmin = SamIdentity::factory()->create([
        'role' => SamRole::ADMIN,
        'full_name' => 'Otro Administrador',
    ]);

    $response = $this->getJson('/api/v1/sam-identities/teachers?q=Profesor');

    $response->assertStatus(200);
    $response->assertJsonCount(2, 'data');
});

it('allows admin to physically delete a user confirming password', function () {
    $admin = SamIdentity::factory()->create([
        'role' => SamRole::ADMIN,
        'password' => bcrypt('admin-password123'),
    ]);
    Sanctum::actingAs($admin, ['*']);

    $target = SamIdentity::factory()->create(['role' => SamRole::TEACHER]);

    $response = $this->deleteJson("/api/v1/sam-identities/{$target->external_id}", [
        'current_password' => 'admin-password123',
    ]);

    $response->assertStatus(200);
    $this->assertDatabaseMissing('gama_sam_identities', [
        'id' => $target->id,
    ]);
});

it('fails to physically delete a user with incorrect password', function () {
    $admin = SamIdentity::factory()->create([
        'role' => SamRole::ADMIN,
        'password' => bcrypt('admin-password123'),
    ]);
    Sanctum::actingAs($admin, ['*']);

    $target = SamIdentity::factory()->create(['role' => SamRole::TEACHER]);

    $response = $this->deleteJson("/api/v1/sam-identities/{$target->external_id}", [
        'current_password' => 'wrong-password',
    ]);

    $response->assertStatus(422);
    $this->assertDatabaseHas('gama_sam_identities', [
        'id' => $target->id,
    ]);
});

it('fails to assign role from admin to teacher (degradation)', function () {
    $admin = SamIdentity::factory()->create([
        'role' => SamRole::ADMIN,
        'password' => bcrypt('admin-password123'),
    ]);
    Sanctum::actingAs($admin, ['*']);

    $target = SamIdentity::factory()->create(['role' => SamRole::ADMIN]);

    $response = $this->postJson("/api/v1/sam-identities/{$target->external_id}/assign-role", [
        'role' => 'teacher',
        'current_password' => 'admin-password123',
    ]);

    $response->assertStatus(422);
    $response->assertJsonValidationErrors(['role']);
});
