<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('teacher_absences', function (Blueprint $table): void {
            $table->id('teacher_absence_id');
            $table->string('teacher_external_id', 100); // corrected type VARCHAR(100)
            $table->foreignId('absence_type_id')->constrained('absence_types', 'absence_type_id')->onDelete('restrict');
            $table->date('start_date');
            $table->date('end_date');
            $table->text('observations')->nullable();
            $table->boolean('is_confirmed')->default(false);
            $table->timestamps();
            $table->softDeletes();

            $table->index('teacher_external_id');
            $table->index('absence_type_id');
            $table->index(['start_date', 'end_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('teacher_absences');
    }
};
