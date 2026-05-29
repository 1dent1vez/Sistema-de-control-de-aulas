<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        // No-op: all CASCADE/RESTRICT rules defined directly in initial migrations
    }

    public function down(): void
    {
        // No-op
    }
};
