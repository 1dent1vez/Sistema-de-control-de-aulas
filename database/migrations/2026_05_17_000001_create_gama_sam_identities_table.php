<?php

/**
 * @descripcion  Migración para crear la tabla gama_sam_identities (cache mínimo de identidad SAM).
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
 * @creado       2026-05-17
 *
 * @modificado   2026-05-17
 *
 * @cambios      2026-05-17 - Creación inicial de la tabla
 */

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('gama_sam_identities', function (Blueprint $table) {
            $table->id();
            $table->string('external_id', 50)->unique();
            $table->string('email', 100)->unique();
            $table->string('full_name', 100)->nullable();
            $table->string('role');
            $table->timestamp('last_login_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('role');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('gama_sam_identities');
    }
};
