<?php

declare(strict_types=1);

use App\Enums\Auth\SamRole;
use App\Models\SamIdentity;
use App\Services\Auth\SamService;

it('revokes token on logout', function () {
    $samServiceMock = Mockery::mock(SamService::class);
    $samServiceMock->shouldReceive('logout')->once();
    $this->instance(SamService::class, $samServiceMock);

    $identity = SamIdentity::factory()->create(['role' => SamRole::TEACHER]);
    $token = $identity->createToken('test')->plainTextToken;

    $response = $this->withHeader('Authorization', 'Bearer '.$token)
        ->postJson('/api/v1/auth/logout');

    $response->assertStatus(200);

    // Verify token is revoked
    $this->assertDatabaseMissing('personal_access_tokens', [
        'tokenable_id' => $identity->id,
    ]);
});
