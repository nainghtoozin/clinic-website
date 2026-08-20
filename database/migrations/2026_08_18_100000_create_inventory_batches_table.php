<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inventory_batches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('medicine_id')->constrained()->cascadeOnDelete();
            $table->string('batch_number');
            $table->integer('quantity')->default(0);
            $table->date('received_date');
            $table->date('expiry_date')->nullable();
            $table->decimal('unit_cost', 10, 2)->nullable();
            $table->string('supplier')->nullable();
            $table->text('notes')->nullable();
            // active | depleted — "expired" is always derived from expiry_date.
            $table->string('status')->default('active');
            $table->timestamps();

            $table->index(['medicine_id', 'expiry_date']);
            $table->index(['medicine_id', 'status']);
            $table->index('expiry_date');
        });

        Schema::table('stock_movements', function (Blueprint $table) {
            $table->foreignId('inventory_batch_id')->nullable()->after('medicine_id')
                ->constrained('inventory_batches')->nullOnDelete();
            $table->integer('balance_before')->nullable()->after('quantity');
            $table->enum('type', ['opening', 'stock_in', 'stock_out', 'adjustment', 'dispensed', 'expired'])
                ->change();
            $table->index('inventory_batch_id');
        });

        // Safe backfill: convert the existing single-total medicine stock into a
        // default batch per medicine so no existing stock is lost or hidden.
        $medicines = DB::table('medicines')
            ->select('id', 'stock_quantity', 'cost_price', 'expiry_date', 'created_at')
            ->get();

        foreach ($medicines as $medicine) {
            if ((int) $medicine->stock_quantity <= 0) {
                continue;
            }

            DB::table('inventory_batches')->insert([
                'medicine_id' => $medicine->id,
                'batch_number' => 'LEGACY-' . str_pad((string) $medicine->id, 4, '0', STR_PAD_LEFT),
                'quantity' => $medicine->stock_quantity,
                'received_date' => $medicine->created_at
                    ? substr((string) $medicine->created_at, 0, 10)
                    : now()->toDateString(),
                'expiry_date' => $medicine->expiry_date,
                'unit_cost' => $medicine->cost_price,
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        Schema::table('stock_movements', function (Blueprint $table) {
            $table->dropIndex(['inventory_batch_id']);
            $table->dropForeign(['inventory_batch_id']);
            $table->dropColumn(['inventory_batch_id', 'balance_before']);
            $table->enum('type', ['opening', 'stock_in', 'stock_out', 'adjustment'])->change();
        });

        Schema::dropIfExists('inventory_batches');
    }
};
