<?php

declare(strict_types=1);

use App\Enums\Auth\SamRole;
use App\Models\SamIdentity;
use Laravel\Sanctum\Sanctum;

it('returns profile with camelCase keys', function () {
    $identity = SamIdentity::factory()->create([
        'external_id' => 'EXT123',
        'full_name' => 'John Doe',
        'role' => SamRole::TEACHER,
    ]);

    Sanctum::actingAs($identity, ['teacher']);

    $response = $this->getJson('/api/v1/auth/me');

    $response->assertStatus(200)
        ->assertJsonPath('data.externalId', 'EXT123')
        ->assertJsonPath('data.fullName', 'John Doe')
        ->assertJsonPath('data.role', 'teacher');
});

it('fails without token', function () {
    $response = $this->getJson('/api/v1/auth/me');
    $response->assertStatus(401);
});
