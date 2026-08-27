<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('expenses', function (Blueprint $table) {
            $table->id();
            $table->string('expense_number', 30)->unique();
            $table->foreignId('expense_category_id')->constrained()->cascadeOnDelete();
            $table->decimal('amount', 12, 2);
            $table->string('payment_method', 30)->default('cash');
            $table->date('expense_date');
            $table->string('vendor')->nullable();
            $table->string('description');
            $table->text('note')->nullable();
            $table->string('reference_number')->nullable();
            $table->string('status', 20)->default('active');
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index('expense_category_id');
            $table->index('expense_date');
            $table->index('status');
            $table->index('payment_method');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('expenses');
    }
};
