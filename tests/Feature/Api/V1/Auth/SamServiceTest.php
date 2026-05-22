<?php

/**
 * @descripcion  Pruebas de regresión para validar el comportamiento robusto del método privado extraerToken()
 *               de SamService usando diferentes variaciones de HTML (comillas dobles, simples, inputs hidden,
 *               meta tags, JSON inline, UUIDs genéricos y tokens planos de 32 caracteres).
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
 * @creado       2026-05-22
 *
 * @modificado   2026-05-22
 *
 * @cambios      2026-05-22 - Creación de pruebas de regresión para la extracción de tokens en SamService.
 *               2026-05-22 - Adición de pruebas unitarias para helpers de obtención de perfil en SamService.
 */

declare(strict_types=1);

use App\Services\Auth\SamService;
use Illuminate\Support\Facades\Log;

/**
 * Helper para invocar el método privado extraerToken() en una instancia de SamService.
 */
function callExtraerToken(string $html): ?array
{
    $request = request();
    $service = new SamService($request);

    $reflection = new ReflectionClass(SamService::class);
    $method = $reflection->getMethod('extraerToken');
    $method->setAccessible(true);

    return $method->invoke($service, $html);
}

it('extracts token with original href double quotes pattern', function () {
    $html = '<html><body><a href="http://192.168.1.74:8090/SAM/app/empleado.do?token=12345678-abcd-1234-abcd-1234567890ab">Docente</a></body></html>';

    $result = callExtraerToken($html);

    expect($result)->toBeArray()
        ->toHaveKey('token', '12345678-abcd-1234-abcd-1234567890ab')
        ->toHaveKey('sistemaUrl', 'http://192.168.1.74:8090/SAM/app/empleado.do');
});

it('extracts token with href single quotes pattern', function () {
    $html = "<html><body><a href='http://192.168.1.74:8090/SAM/app/empleado.do?token=87654321-dbca-4321-dbca-ba0987654321'>Docente</a></body></html>";

    $result = callExtraerToken($html);

    expect($result)->toBeArray()
        ->toHaveKey('token', '87654321-dbca-4321-dbca-ba0987654321')
        ->toHaveKey('sistemaUrl', 'http://192.168.1.74:8090/SAM/app/empleado.do');
});

it('extracts token from input hidden field with name then value', function () {
    $html = '<html><body><input type="hidden" name="token" value="abcde123-abcd-1234-abcd-1234567890ef"></body></html>';

    $result = callExtraerToken($html);

    expect($result)->toBeArray()
        ->toHaveKey('token', 'abcde123-abcd-1234-abcd-1234567890ef')
        ->toHaveKey('sistemaUrl', env('SAM_SYSTEM_URL', 'http://localhost:8000'));
});

it('extracts token from input hidden field with value then name', function () {
    $html = '<html><body><input type="hidden" value="f1234567-abcd-1234-abcd-7654321abcde" name="token"></body></html>';

    $result = callExtraerToken($html);

    expect($result)->toBeArray()
        ->toHaveKey('token', 'f1234567-abcd-1234-abcd-7654321abcde')
        ->toHaveKey('sistemaUrl', env('SAM_SYSTEM_URL', 'http://localhost:8000'));
});

it('extracts token from meta tag', function () {
    $html = '<html><head><meta name="token" content="99999999-aaaa-bbbb-cccc-dddddddddddd"></head></html>';

    $result = callExtraerToken($html);

    expect($result)->toBeArray()
        ->toHaveKey('token', '99999999-aaaa-bbbb-cccc-dddddddddddd')
        ->toHaveKey('sistemaUrl', env('SAM_SYSTEM_URL', 'http://localhost:8000'));
});

it('extracts token from json inline script', function () {
    $html = '<html><script>var config = {"token": "88888888-8888-8888-8888-888888888888"};</script></html>';

    $result = callExtraerToken($html);

    expect($result)->toBeArray()
        ->toHaveKey('token', '88888888-8888-8888-8888-888888888888')
        ->toHaveKey('sistemaUrl', env('SAM_SYSTEM_URL', 'http://localhost:8000'));
});

it('extracts token from generic uuid in plain text', function () {
    $html = '<html><body>El token de sesión asignado es 11112222-3333-4444-5555-666677778888 en esta respuesta.</body></html>';

    $result = callExtraerToken($html);

    expect($result)->toBeArray()
        ->toHaveKey('token', '11112222-3333-4444-5555-666677778888')
        ->toHaveKey('sistemaUrl', env('SAM_SYSTEM_URL', 'http://localhost:8000'));
});

it('extracts token from 32 character flat hex token', function () {
    $html = '<html><body>token_plano: a1b2c3d4e5f67890a1b2c3d4e5f67890</body></html>';

    $result = callExtraerToken($html);

    expect($result)->toBeArray()
        ->toHaveKey('token', 'a1b2c3d4e5f67890a1b2c3d4e5f67890')
        ->toHaveKey('sistemaUrl', env('SAM_SYSTEM_URL', 'http://localhost:8000'));
});

it('throws exception and logs when extraction fails completely', function () {
    Log::shouldReceive('channel')
        ->with('sam_debug')
        ->andReturnSelf();

    Log::shouldReceive('debug')->once();
    Log::shouldReceive('error')->once();

    $html = '<html><body>Acceso inválido, intente de nuevo.</body></html>';

    expect(fn () => callExtraerToken($html))
        ->toThrow(RuntimeException::class, 'No se encontró token SAM en la respuesta HTML.');
});

it('resolves system identifier from url', function () {
    $request = request();
    $service = new SamService($request);

    $reflection = new ReflectionClass(SamService::class);
    $method = $reflection->getMethod('resolverIdentificadorSistema');
    $method->setAccessible(true);

    expect($method->invoke($service, 'http://192.168.1.74:8090/SAM/app/empleado.do'))->toBe('EMPLEADO');
    expect($method->invoke($service, 'http://192.168.1.74:8090/SAM/app/docente.do'))->toBe('DOCENTE');
    expect($method->invoke($service, 'http://192.168.1.74:8090/SAM/app'))->toBe('EMPLEADO');
});

it('gets nested value from array with dot notation', function () {
    $request = request();
    $service = new SamService($request);

    $reflection = new ReflectionClass(SamService::class);
    $method = $reflection->getMethod('getNestedValue');
    $method->setAccessible(true);

    $data = [
        'responseObject' => [
            'usuario' => [
                'id' => 123,
                'nombre' => 'Test',
            ],
        ],
        'simple' => 'value',
    ];

    expect($method->invoke($service, $data, 'responseObject.usuario.id'))->toBe(123);
    expect($method->invoke($service, $data, 'responseObject.usuario.nombre'))->toBe('Test');
    expect($method->invoke($service, $data, 'simple'))->toBe('value');
    expect($method->invoke($service, $data, 'responseObject.non_existent'))->toBeNull();
});
