<?php

/**
 * @descripcion  Migración que crea la tabla gama_class_schedules.
 *
 * @autor        Ghael Garcia Manjarrez <ghael.engineer@gmail.com>
 *
 * @autorizador  Ruben Alejandro Nolasco Ruiz <correo@dominio.com>
 *
 * @prueba       Ghael Garcia Manjarrez <ghael.engineer@gmail.com>
 *
 * @mantenimiento Ghael Garcia Manjarrez <ghael.engineer@gmail.com>
 *
 * @version      1.0.0
 *
 * @creado       2026-05-13
 *
 * @modificado   2026-05-13
 *
 * @cambios      2026-05-13 - Creación inicial de la migración
 */

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('gama_class_schedules', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('semester_id')->constrained('gama_semesters');
            $table->foreignId('classroom_id')->constrained('gama_classrooms');
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
        Schema::dropIfExists('gama_class_schedules');
    }
};
