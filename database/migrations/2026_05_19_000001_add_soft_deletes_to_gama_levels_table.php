<?php

/**
 * @descripcion  Migración que agrega soft deletes a la tabla gama_levels.
 *
 * @autor        Equipo GAMA
 *
 * @version      1.0.0
 *
 * @creado       2026-05-19
 */

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('gama_levels', function (Blueprint $table): void {
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::table('gama_levels', function (Blueprint $table): void {
            $table->dropSoftDeletes();
        });
    }
};
