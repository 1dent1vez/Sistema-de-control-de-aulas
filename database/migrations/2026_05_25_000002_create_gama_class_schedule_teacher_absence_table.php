<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('class_schedule_teacher_absence', function (Blueprint $table): void {
            $table->id(); // PK standard is fine for pivot
            $table->foreignId('teacher_absence_id')->constrained('teacher_absences', 'teacher_absence_id')->onDelete('cascade');
            $table->foreignId('class_schedule_id')->constrained('class_schedules', 'class_schedule_id')->onDelete('cascade');
            $table->timestamps();

            $table->unique(['teacher_absence_id', 'class_schedule_id'], 'sched_abs_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('class_schedule_teacher_absence');
    }
};
