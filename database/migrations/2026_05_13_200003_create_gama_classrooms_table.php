<?php

/**
 * @descripcion  Migración que crea la tabla gama_classrooms para el registro de aulas.
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
        Schema::create('gama_classrooms', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('building_id')->constrained('gama_buildings');
            $table->foreignId('level_id')->constrained('gama_levels');
            $table->string('classroom_name', 30);
            $table->string('classroom_type', 20);
            $table->boolean('status')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['building_id', 'classroom_name']);
            $table->index('building_id');
            $table->index('level_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('gama_classrooms');
    }
};
