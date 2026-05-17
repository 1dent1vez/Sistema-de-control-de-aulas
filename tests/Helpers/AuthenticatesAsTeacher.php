<?php

declare(strict_types=1);

namespace Tests\Helpers;

use App\Enums\Auth\SamRole;
use App\Models\SamIdentity;
use Laravel\Sanctum\Sanctum;

trait AuthenticatesAsTeacher
{
    protected function loginAsTeacher(string $externalId = 'TCH001'): SamIdentity
    {
        $identity = SamIdentity::factory()->create([
            'external_id' => $externalId,
            'email' => $externalId.'@toluca.tecnm.mx',
            'full_name' => 'Teacher User',
            'role' => SamRole::TEACHER,
        ]);

        Sanctum::actingAs($identity, ['teacher']);

        return $identity;
    }
}
