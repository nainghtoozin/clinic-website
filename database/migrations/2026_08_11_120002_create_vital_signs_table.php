<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vital_signs', function (Blueprint $table) {
            $table->id();

            $table->foreignId('consultation_id')->constrained()->cascadeOnDelete();

            $table->string('blood_pressure')->nullable();
            $table->decimal('temperature', 5, 1)->nullable();
            $table->integer('pulse')->nullable();
            $table->integer('respiratory_rate')->nullable();
            $table->decimal('weight', 5, 2)->nullable();
            $table->decimal('height', 5, 2)->nullable();
            $table->decimal('oxygen_saturation', 5, 2)->nullable();

            $table->timestamp('recorded_at')->nullable();
            $table->timestamps();

            $table->index('consultation_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vital_signs');
    }
};
