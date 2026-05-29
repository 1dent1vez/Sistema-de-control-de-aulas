<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        // No-op: class_schedule_id was never added to teacher_absences in the main migration
    }

    public function down(): void
    {
        // No-op
    }
};
