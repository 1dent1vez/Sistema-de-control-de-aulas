<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('gama_qr_codes', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('classroom_id')->constrained('gama_classrooms');
            $table->string('token', 255)->unique();
            $table->json('payload');
            $table->string('file_path')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamp('generated_at')->nullable();
            $table->timestamp('invalidated_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('classroom_id');
            $table->index('is_active');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('gama_qr_codes');
    }
};
