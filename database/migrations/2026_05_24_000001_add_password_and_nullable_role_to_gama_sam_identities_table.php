<?php

/**
 * @descripcion  Migración para añadir la columna password (local admin credentials)
 *              y modificar la columna role para permitir valores null en gama_sam_identities.
 *
 * @autor        Antigravity <support@google.com>
 *
 * @autorizador  Ruben Alejandro Nolasco Ruiz <correo@dominio.com>
 *
 * @prueba       Antigravity <support@google.com>
 *
 * @mantenimiento Antigravity <support@google.com>
 *
 * @version      1.0.0
 *
 * @creado       2026-05-24
 *
 * @modificado   2026-05-24
 */

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('gama_sam_identities', function (Blueprint $table) {
            $table->string('password')->nullable()->after('role');
            $table->string('role')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('gama_sam_identities', function (Blueprint $table) {
            $table->dropColumn('password');
            $table->string('role')->nullable(false)->change();
        });
    }
};
