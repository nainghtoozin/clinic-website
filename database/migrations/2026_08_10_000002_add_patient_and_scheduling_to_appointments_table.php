<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('appointments', function (Blueprint $table) {
            // New columns for patient relationship
            $table->foreignId('patient_id')->nullable()->after('id')->constrained()->nullOnDelete();

            // Appointment identification and scheduling
            $table->string('appointment_number')->unique()->nullable()->after('patient_id');
            $table->time('time')->nullable()->after('date');
            $table->integer('duration')->nullable()->after('time')->comment('Duration in minutes');
        });
    }

    public function down(): void
    {
        Schema::table('appointments', function (Blueprint $table) {
            $table->dropForeign(['patient_id']);
            $table->dropColumn([
                'patient_id',
                'appointment_number',
                'time',
                'duration',
            ]);
        });
    }
};
