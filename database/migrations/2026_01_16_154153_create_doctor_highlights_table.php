<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('doctor_highlights', function (Blueprint $table) {
            $table->id();
            $table->foreignId('doctor_id')->constrained()->cascadeOnDelete();
            $table->string('icon')->nullable();      // bi-mortarboard
            $table->string('label');                 // Residency
            $table->string('value');                 // St. Mary’s Medical Center
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('doctor_highlights');
    }
};
