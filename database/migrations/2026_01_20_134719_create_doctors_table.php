<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('doctors', function (Blueprint $table) {
            $table->id();

            // Basic Info
            $table->string('name');
            $table->string('slug')->unique();
            $table->enum('gender', ['male', 'female', 'other'])->nullable();
            $table->string('profile_image')->nullable();

            // Professional Info
            $table->string('title')->nullable();               // Cardiologist • MD, FACC
            $table->string('role')->nullable();                // Senior Consultant
            $table->string('qualifications')->nullable();      // MD, MBBS
            $table->integer('experience_years')->default(0);
            $table->boolean('board_certified')->default(false);

            // Department (fallback if not normalized)
            $table->string('primary_department')->nullable();

            // Description
            $table->text('short_description')->nullable();
            $table->longText('biography')->nullable();
            $table->string('location')->nullable();
            $table->foreignId('department_id')->nullable()->constrained()->cascadeOnDelete();

            // Status
            $table->boolean('is_available')->default(true);
            $table->string('availability_note')->nullable();
            $table->boolean('is_featured')->default(false);

            // ✅ Schedule
            $table->json('available_days')->nullable();   // [1,3,5]
            $table->time('start_time')->default('00:00:00');
            $table->time('end_time')->default('00:00:00');

            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('location_id')->nullable()->constrained()->cascadeOnDelete();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('doctors');
    }
};
