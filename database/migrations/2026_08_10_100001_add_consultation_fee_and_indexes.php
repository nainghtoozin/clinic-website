<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('doctors', function (Blueprint $table) {
            $table->decimal('consultation_fee', 8, 2)->nullable()->after('is_featured');
        });

        Schema::table('appointments', function (Blueprint $table) {
            $table->text('cancel_reason')->nullable()->after('message');
            $table->index('doctor_id');
            $table->index('patient_id');
            $table->index('status');
            $table->index(['doctor_id', 'date']);
        });
    }

    public function down(): void
    {
        Schema::table('appointments', function (Blueprint $table) {
            $table->dropIndex(['doctor_id', 'date']);
            $table->dropIndex('appointments_doctor_id_index');
            $table->dropIndex('appointments_patient_id_index');
            $table->dropIndex('appointments_status_index');
            $table->dropColumn('cancel_reason');
        });

        Schema::table('doctors', function (Blueprint $table) {
            $table->dropColumn('consultation_fee');
        });
    }
};
