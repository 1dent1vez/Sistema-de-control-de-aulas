<?php

declare(strict_types=1);

namespace Tests\Helpers;

use App\Enums\Auth\SamRole;
use App\Models\SamIdentity;
use Laravel\Sanctum\Sanctum;

trait AuthenticatesAsAdmin
{
    protected function loginAsAdmin(string $externalId = 'ADMIN001'): SamIdentity
    {
        $identity = SamIdentity::factory()->create([
            'external_id' => $externalId,
            'email' => $externalId.'@toluca.tecnm.mx',
            'full_name' => 'Admin User',
            'role' => SamRole::ADMIN,
        ]);

        Sanctum::actingAs($identity, ['*']);

        return $identity;
    }
}
