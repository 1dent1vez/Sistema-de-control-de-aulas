<?php

declare(strict_types=1);

use App\Services\Auth\SamService;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Support\Facades\RateLimiter;

beforeEach(function () {
    RateLimiter::for('auth', function () {
        return Limit::perMinute(60);
    });
});

it('returns a png captcha', function () {
    $samServiceMock = Mockery::mock(SamService::class);
    $samServiceMock->shouldReceive('obtenerCaptcha')
        ->once()
        ->andReturn(['png' => 'fake-png-bytes', 'sessionId' => 'test-session']);
    $samServiceMock->shouldReceive('getSessionCookieName')
        ->andReturn('sam_session');

    $this->instance(SamService::class, $samServiceMock);

    $response = $this->postJson('/api/v1/auth/captcha');

    $response->assertStatus(200);
    $response->assertHeader('Content-Type', 'image/png');
    $this->assertEquals('fake-png-bytes', $response->getContent());
});

it('returns 503 when sam is down', function () {
    $samServiceMock = Mockery::mock(SamService::class);
    $samServiceMock->shouldReceive('obtenerCaptcha')
        ->once()
        ->andReturn(['png' => null, 'sessionId' => null]);

    $this->instance(SamService::class, $samServiceMock);

    $response = $this->postJson('/api/v1/auth/captcha');

    $response->assertStatus(503)
        ->assertJsonFragment(['success' => false]);
});
