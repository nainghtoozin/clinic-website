<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('departments', function (Blueprint $table) {
            $table->id();

            // Main info
            $table->string('name');                 // Pediatrics
            $table->string('slug')->unique();        // pediatrics
            $table->string('category')->nullable();  // Children's Health
            $table->text('description')->nullable(); // Paragraph text

            // UI related
            $table->string('icon')->nullable();      // fas fa-baby
            $table->string('image')->nullable();     // pediatrics-2.webp

            // Status & ordering
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('departments');
    }
};
