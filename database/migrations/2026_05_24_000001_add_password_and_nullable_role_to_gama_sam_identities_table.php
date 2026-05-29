<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        // No-op: password and nullable role defined directly in sam_identities main migration
    }

    public function down(): void
    {
        // No-op
    }
};
