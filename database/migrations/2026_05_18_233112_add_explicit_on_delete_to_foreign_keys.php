<?php

/**
 * @descripcion  Agrega onDelete explícito (cascade/restrict) a todas las FKs de tablas gama_.
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
        Schema::table('gama_buildings', function (Blueprint $table): void {
            $table->dropForeign(['institution_id']);
            $table->foreign('institution_id')->references('id')->on('gama_institutions')->onDelete('cascade');
        });

        Schema::table('gama_classrooms', function (Blueprint $table): void {
            $table->dropForeign(['building_id']);
            $table->foreign('building_id')->references('id')->on('gama_buildings')->onDelete('cascade');
            $table->dropForeign(['level_id']);
            $table->foreign('level_id')->references('id')->on('gama_levels')->onDelete('restrict');
        });

        Schema::table('gama_semesters', function (Blueprint $table): void {
            $table->dropForeign(['institution_id']);
            $table->foreign('institution_id')->references('id')->on('gama_institutions')->onDelete('cascade');
        });

        Schema::table('gama_class_schedules', function (Blueprint $table): void {
            $table->dropForeign(['semester_id']);
            $table->foreign('semester_id')->references('id')->on('gama_semesters')->onDelete('cascade');
            $table->dropForeign(['classroom_id']);
            $table->foreign('classroom_id')->references('id')->on('gama_classrooms')->onDelete('restrict');
        });

        Schema::table('gama_qr_codes', function (Blueprint $table): void {
            $table->dropForeign(['classroom_id']);
            $table->foreign('classroom_id')->references('id')->on('gama_classrooms')->onDelete('cascade');
        });

        Schema::table('gama_teacher_absences', function (Blueprint $table): void {
            $table->dropForeign(['absence_type_id']);
            $table->foreign('absence_type_id')->references('id')->on('gama_absence_types')->onDelete('restrict');
        });
    }

    public function down(): void
    {
        Schema::table('gama_buildings', function (Blueprint $table): void {
            $table->dropForeign(['institution_id']);
            $table->foreign('institution_id')->references('id')->on('gama_institutions');
        });

        Schema::table('gama_classrooms', function (Blueprint $table): void {
            $table->dropForeign(['building_id']);
            $table->foreign('building_id')->references('id')->on('gama_buildings');
            $table->dropForeign(['level_id']);
            $table->foreign('level_id')->references('id')->on('gama_levels');
        });

        Schema::table('gama_semesters', function (Blueprint $table): void {
            $table->dropForeign(['institution_id']);
            $table->foreign('institution_id')->references('id')->on('gama_institutions');
        });

        Schema::table('gama_class_schedules', function (Blueprint $table): void {
            $table->dropForeign(['semester_id']);
            $table->foreign('semester_id')->references('id')->on('gama_semesters');
            $table->dropForeign(['classroom_id']);
            $table->foreign('classroom_id')->references('id')->on('gama_classrooms');
        });

        Schema::table('gama_qr_codes', function (Blueprint $table): void {
            $table->dropForeign(['classroom_id']);
            $table->foreign('classroom_id')->references('id')->on('gama_classrooms');
        });
    }
};
