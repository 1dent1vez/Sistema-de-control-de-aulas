<?php

/**
 * @descripcion  Migración que crea la tabla gama_levels para los niveles auto-generados de edificios.
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
        Schema::create('gama_levels', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('building_id')->constrained('gama_buildings')->cascadeOnDelete();
            $table->string('name', 10);
            $table->tinyInteger('display_order')->unsigned();
            $table->timestamps();

            $table->unique(['building_id', 'name']);
            $table->index('building_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('gama_levels');
    }
};
