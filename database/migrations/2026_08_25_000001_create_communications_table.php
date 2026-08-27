<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('communications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('patient_id')->constrained()->cascadeOnDelete();
            $table->foreignId('appointment_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('contact_method', 30); // phone, in_person, sms, email, telegram, other
            $table->string('purpose', 50); // appointment_confirmation, rejection, reschedule, cancellation, reminder, follow_up, test_result, general, other
            $table->string('outcome', 50); // contacted, no_answer, callback_requested, confirmed, rescheduled, cancelled, informed, other
            $table->dateTime('contacted_at');
            $table->text('note')->nullable();
            $table->date('follow_up_date')->nullable();
            $table->text('follow_up_note')->nullable();
            $table->boolean('follow_up_completed')->default(false);
            $table->timestamps();

            $table->index('patient_id');
            $table->index('appointment_id');
            $table->index('user_id');
            $table->index('contacted_at');
            $table->index('follow_up_date');
            $table->index('follow_up_completed');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('communications');
    }
};
