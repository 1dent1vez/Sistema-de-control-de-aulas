<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('levels', function (Blueprint $table): void {
            $table->id('level_id');
            $table->string('name', 10)->unique();
            $table->tinyInteger('display_order')->unsigned();
            $table->timestamps();
            $table->softDeletes(); // Soft deletes added directly
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('levels');
    }
};
