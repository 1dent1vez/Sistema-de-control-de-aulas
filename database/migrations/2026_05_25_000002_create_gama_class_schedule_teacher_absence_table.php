<?php

/**
 * @descripcion  Migración para crear la tabla pivote gama_class_schedule_teacher_absence.
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
 * @creado       2026-05-25
 *
 * @modificado   2026-05-25
 *
 * @cambios      2026-05-25 - Creación inicial de la tabla pivote para la relación N:M.
 */

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('gama_class_schedule_teacher_absence', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('teacher_absence_id')->constrained('gama_teacher_absences')->onDelete('cascade');
            $table->foreignId('class_schedule_id')->constrained('gama_class_schedules')->onDelete('cascade');
            $table->timestamps();

            $table->unique(['teacher_absence_id', 'class_schedule_id'], 'gama_sched_abs_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('gama_class_schedule_teacher_absence');
    }
};
