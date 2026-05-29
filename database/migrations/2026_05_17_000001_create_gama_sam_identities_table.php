<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sam_identities', function (Blueprint $table) {
            $table->id('sam_id'); // customized PK name from ER
            $table->string('external_id', 50)->unique();
            $table->string('email', 100)->unique();
            $table->string('full_name', 100)->nullable();
            $table->string('role', 20)->nullable(); // made nullable
            $table->string('password')->nullable(); // password added directly
            $table->timestamp('last_login_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('role');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sam_identities');
    }
};
