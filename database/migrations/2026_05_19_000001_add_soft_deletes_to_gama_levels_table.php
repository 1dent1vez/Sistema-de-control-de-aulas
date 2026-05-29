<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        // No-op: softDeletes defined directly in levels main migration
    }

    public function down(): void
    {
        // No-op
    }
};
