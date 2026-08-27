<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('doctors', function (Blueprint $table) {
            $table->time('break_start')->nullable()->after('end_time');
            $table->time('break_end')->nullable()->after('break_start');
            $table->boolean('is_active')->default(true)->after('is_available');
            $table->index('is_available');
            $table->index('is_active');
        });
    }

    public function down(): void
    {
        Schema::table('doctors', function (Blueprint $table) {
            $table->dropColumn(['break_start', 'break_end', 'is_active']);
            $table->dropIndex(['is_available']);
        });
    }
};
