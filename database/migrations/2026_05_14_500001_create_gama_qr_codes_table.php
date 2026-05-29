<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('qr_codes', function (Blueprint $table): void {
            $table->id('qr_id'); // customized PK name from ER
            $table->foreignId('classroom_id')->constrained('classrooms', 'classroom_id')->onDelete('cascade');
            $table->string('token', 255)->unique();
            $table->json('payload');
            $table->string('file_path')->nullable();
            $table->tinyInteger('is_active')->default(1); // corrected type TINYINT(1)
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
        Schema::dropIfExists('qr_codes');
    }
};
