<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('classrooms', function (Blueprint $table): void {
            $table->id('classroom_id');
            $table->foreignId('building_id')->constrained('buildings', 'building_id')->onDelete('cascade');
            $table->foreignId('level_id')->constrained('levels', 'level_id')->onDelete('restrict');
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
        Schema::dropIfExists('classrooms');
    }
};
