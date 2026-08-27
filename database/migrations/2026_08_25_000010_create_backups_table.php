<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('backups', function (Blueprint $table) {
            $table->id();
            $table->string('backup_number', 30)->unique();
            $table->string('type', 20)->default('full');
            $table->string('status', 20)->default('pending');
            $table->string('filename');
            $table->bigInteger('size')->default(0);
            $table->string('disk')->default('local');
            $table->text('notes')->nullable();
            $table->json('metadata')->nullable();
            $table->boolean('is_safety')->default(false);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index('status');
            $table->index('type');
            $table->index('is_safety');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('backups');
    }
};
