<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('medicines', function (Blueprint $table) {
            $table->decimal('cost_price', 10, 2)->default(0)->after('unit_price');
            $table->decimal('selling_price', 10, 2)->default(0)->after('cost_price');
            $table->string('unit')->nullable()->after('selling_price');
            $table->integer('minimum_stock_level')->default(10)->after('stock_quantity');
            $table->date('expiry_date')->nullable()->after('minimum_stock_level');
        });
    }

    public function down(): void
    {
        Schema::table('medicines', function (Blueprint $table) {
            $table->dropColumn(['cost_price', 'selling_price', 'unit', 'minimum_stock_level', 'expiry_date']);
        });
    }
};
