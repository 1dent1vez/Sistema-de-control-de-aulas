<?php

declare(strict_types=1);

use App\Enums\Auth\SamRole;
use App\Models\SamIdentity;
use Laravel\Sanctum\Sanctum;

it('allows admin to assign role', function () {
    $admin = SamIdentity::factory()->create(['role' => SamRole::ADMIN]);
    Sanctum::actingAs($admin, ['*']);

    $target = SamIdentity::factory()->create(['role' => SamRole::TEACHER]);

    $response = $this->postJson("/api/v1/sam-identities/{$target->external_id}/assign-role", [
        'role' => 'admin',
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
