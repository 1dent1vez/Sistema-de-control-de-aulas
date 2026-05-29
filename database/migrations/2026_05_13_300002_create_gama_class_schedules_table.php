<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('class_schedules', function (Blueprint $table): void {
            $table->id('class_schedule_id');
            $table->foreignId('semester_id')->constrained('semesters', 'semester_id')->onDelete('cascade');
            $table->foreignId('classroom_id')->constrained('classrooms', 'classroom_id')->onDelete('restrict');
            $table->string('teacher_external_id', 50);
            $table->string('subject_name', 100);
            $table->string('group_name', 10);
            $table->string('weekday', 15);
            $table->time('start_time');
            $table->time('end_time');
            $table->boolean('status')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->index('semester_id');
            $table->index('classroom_id');
            $table->index('teacher_external_id');
            $table->index('weekday');
            $table->index(['start_time', 'end_time']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('class_schedules');
    }
};
