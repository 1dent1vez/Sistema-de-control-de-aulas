<?php

/**
 * @descripcion  Agrega la columna class_schedule_id a gama_teacher_absences con FK onDelete cascade.
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
 * @creado       2026-05-18
 *
 * @modificado   2026-05-18
 *
 * @cambios      2026-05-18 - Creación inicial de la migración
 */

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('gama_teacher_absences', function (Blueprint $table): void {
            $table->foreignId('class_schedule_id')->nullable()->constrained('gama_class_schedules')->onDelete('cascade');
            $table->index('class_schedule_id');
        });
    }

    public function down(): void
    {
        Schema::table('gama_teacher_absences', function (Blueprint $table): void {
            $table->dropForeign(['class_schedule_id']);
            $table->dropColumn('class_schedule_id');
        });
    }
};
