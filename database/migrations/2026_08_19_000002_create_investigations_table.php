<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('investigations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('patient_id')->constrained()->cascadeOnDelete();
            $table->foreignId('doctor_id')->constrained()->cascadeOnDelete();
            $table->foreignId('consultation_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('lab_test_id')->constrained()->cascadeOnDelete();
            $table->date('requested_date');
            $table->string('priority')->default('routine');
            $table->text('clinical_notes')->nullable();
            $table->string('status')->default('requested');

            // Result fields
            $table->text('result_value')->nullable();
            $table->string('result_unit')->nullable();
            $table->string('result_reference_range')->nullable();
            $table->text('interpretation')->nullable();
            $table->timestamp('resulted_at')->nullable();
            $table->string('result_status')->default('pending');

            // Billing
            $table->foreignId('invoice_id')->nullable()->constrained()->nullOnDelete();

            $table->timestamps();

            $table->index(['patient_id', 'created_at']);
            $table->index(['doctor_id', 'created_at']);
            $table->index('status');
            $table->index('result_status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('investigations');
    }
};
