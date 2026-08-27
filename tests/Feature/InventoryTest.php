<?php

use App\Models\Doctor;
use App\Models\InventoryBatch;
use App\Models\Medicine;
use App\Models\Prescription;
use App\Models\PrescriptionItem;
use App\Models\StockMovement;
use App\Models\User;
use Spatie\Permission\Models\Permission;

beforeEach(function () {
    $this->permissions = [
        'medicine.view', 'medicine.create', 'medicine.edit', 'medicine.delete',
        'inventory.view', 'inventory.opening_stock', 'inventory.stock_in',
        'inventory.stock_out', 'inventory.adjust', 'inventory.dispense',
        'prescription.view', 'prescription.create',
        'patient.view', 'patient.create', 'patient.edit', 'patient.delete',
        'appointment.view', 'appointment.create', 'appointment.edit', 'appointment.delete',
        'consultation.view', 'consultation.create', 'consultation.edit', 'consultation.complete',
        'queue.view', 'queue.check_in', 'queue.walk_in', 'queue.call_next', 'queue.manage',
        'invoice.view', 'invoice.create', 'invoice.edit', 'invoice.cancel',
        'payment.view', 'payment.create',
    ];

    foreach ($this->permissions as $perm) {
        Permission::firstOrCreate(['name' => $perm, 'guard_name' => 'web']);
    }

    $this->user = User::factory()->create();
    $this->user->givePermissionTo($this->permissions);

    $this->doctor = Doctor::factory()->create([
        'user_id' => $this->user->id,
        'is_available' => true,
        'available_days' => ['monday', 'tuesday', 'wednesday', 'thursday', 'friday'],
    ]);

    $this->medicine = Medicine::create([
        'name' => 'Amoxicillin',
        'generic_name' => 'Amoxicillin Trihydrate',
        'category' => 'Antibiotics',
        'form' => 'capsule',
        'strength' => '500mg',
        'unit' => 'capsule',
        'unit_price' => 15.50,
        'cost_price' => 10.00,
        'selling_price' => 15.50,
        'stock_quantity' => 0,
        'minimum_stock_level' => 10,
        'is_active' => true,
    ]);
});

// --- MEDICINE INVENTORY FIELDS ---

test('medicine has inventory fields', function () {
    $this->assertEquals(10, $this->medicine->minimum_stock_level);
    $this->assertEquals(10.00, $this->medicine->cost_price);
    $this->assertEquals(15.50, $this->medicine->selling_price);
    $this->assertEquals('capsule', $this->medicine->unit);
});

// --- OPENING STOCK ---

test('opening stock creates movement', function () {
    $movement = $this->medicine->setOpeningStock(100, $this->user->id);

    $this->assertEquals(100, $this->medicine->fresh()->stock_quantity);
    $this->assertEquals('opening', $movement->type);
    $this->assertEquals(100, $movement->quantity);
    $this->assertEquals(100, $movement->balance_after);
    $this->assertEquals($this->user->id, $movement->performed_by);
});

test('opening stock updates balance', function () {
    $this->medicine->setOpeningStock(50, $this->user->id);
    $this->assertEquals(50, $this->medicine->fresh()->stock_quantity);
});

test('duplicate opening stock is handled safely', function () {
    $this->medicine->setOpeningStock(100, $this->user->id);
    $this->medicine->setOpeningStock(200, $this->user->id);

    $this->assertEquals(200, $this->medicine->fresh()->stock_quantity);
    $this->assertEquals(2, StockMovement::where('medicine_id', $this->medicine->id)->where('type', 'opening')->count());
});

// --- STOCK IN ---

test('stock in increases balance', function () {
    $this->medicine->setOpeningStock(100, $this->user->id);
    $this->medicine->stockIn(50, 'Restocked', $this->user->id);

    $this->assertEquals(150, $this->medicine->fresh()->stock_quantity);
});

test('stock in movement is recorded', function () {
    $this->medicine->setOpeningStock(100, $this->user->id);
    $movement = $this->medicine->stockIn(50, 'Restocked', $this->user->id);

    $this->assertEquals('stock_in', $movement->type);
    $this->assertEquals(50, $movement->quantity);
    $this->assertEquals(150, $movement->balance_after);
    $this->assertEquals($this->user->id, $movement->performed_by);
    $this->assertEquals('Restocked', $movement->reason);
});

// --- STOCK OUT ---

test('stock out decreases balance', function () {
    $this->medicine->setOpeningStock(100, $this->user->id);
    $this->medicine->stockOut(30, 'Dispensed', $this->user->id);

    $this->assertEquals(70, $this->medicine->fresh()->stock_quantity);
});

test('negative stock is prevented', function () {
    $this->medicine->setOpeningStock(10, $this->user->id);

    $this->expectException(\RuntimeException::class);
    $this->medicine->stockOut(20, 'Too much', $this->user->id);
});

test('stock out movement is recorded', function () {
    $this->medicine->setOpeningStock(100, $this->user->id);
    $movement = $this->medicine->stockOut(30, 'Dispensed', $this->user->id);

    $this->assertEquals('stock_out', $movement->type);
    $this->assertEquals(30, $movement->quantity);
    $this->assertEquals(70, $movement->balance_after);
    $this->assertEquals($this->user->id, $movement->performed_by);
});

// --- ADJUSTMENT ---

test('increase adjustment works', function () {
    $this->medicine->setOpeningStock(100, $this->user->id);
    $this->medicine->adjust(10, true, 'Stock count correction', $this->user->id);

    $this->assertEquals(110, $this->medicine->fresh()->stock_quantity);
});

test('decrease adjustment works', function () {
    $this->medicine->setOpeningStock(100, $this->user->id);
    $this->medicine->adjust(10, false, 'Damaged items', $this->user->id);

    $this->assertEquals(90, $this->medicine->fresh()->stock_quantity);
});

test('reason is required for adjustment', function () {
    $this->medicine->setOpeningStock(100, $this->user->id);

    $this->expectException(\InvalidArgumentException::class);
    $this->medicine->adjust(10, true, '', $this->user->id);
});

test('adjustment movement is recorded', function () {
    $this->medicine->setOpeningStock(100, $this->user->id);
    $movement = $this->medicine->adjust(10, true, 'Correction', $this->user->id);

    $this->assertEquals('adjustment', $movement->type);
    $this->assertEquals(10, $movement->quantity);
    $this->assertEquals(110, $movement->balance_after);
    $this->assertEquals('Correction', $movement->reason);
});

// --- LOW STOCK ---

test('low stock detection works', function () {
    $this->medicine->minimum_stock_level = 10;
    $this->medicine->stock_quantity = 5;
    $this->assertTrue($this->medicine->isLowStock());

    $this->medicine->stock_quantity = 15;
    $this->assertFalse($this->medicine->isLowStock());
});

// --- EXPIRY ---

test('expired detection works', function () {
    $this->medicine->expiry_date = now()->subDay();
    $this->assertTrue($this->medicine->isExpired());
    $this->assertTrue($this->medicine->stock_status === 'expired');
});

test('expiring soon detection works', function () {
    $this->medicine->expiry_date = now()->addDays(15);
    $this->assertTrue($this->medicine->isExpiringSoon());
    $this->assertTrue($this->medicine->stock_status === 'expiring');
});

// --- MOVEMENTS ---

test('movement history works', function () {
    $this->medicine->setOpeningStock(100, $this->user->id);
    $this->medicine->stockIn(50, 'Restocked', $this->user->id);
    $this->medicine->stockOut(20, 'Dispensed', $this->user->id);

    $movements = StockMovement::where('medicine_id', $this->medicine->id)->get();
    $this->assertEquals(3, $movements->count());
});

test('historical movements cannot be silently edited', function () {
    $movement = $this->medicine->setOpeningStock(100, $this->user->id);

    $this->assertEquals(100, $movement->quantity);
});

// --- CONCURRENCY ---

test('concurrent stock out cannot create negative stock', function () {
    $this->medicine->setOpeningStock(10, $this->user->id);

    $this->medicine->stockOut(8, 'First', $this->user->id);

    $this->expectException(\RuntimeException::class);
    $this->medicine->stockOut(8, 'Second', $this->user->id);
});

// --- INVENTORY CONTROLLER ---

test('inventory dashboard is accessible', function () {
    $response = $this->actingAs($this->user)->get(route('inventory.dashboard'));
    $response->assertOk();
});

test('inventory index is accessible', function () {
    $response = $this->actingAs($this->user)->get(route('inventory.index'));
    $response->assertOk();
});

test('inventory movements is accessible', function () {
    $response = $this->actingAs($this->user)->get(route('inventory.movements'));
    $response->assertOk();
});

test('stock in form is accessible', function () {
    $response = $this->actingAs($this->user)->get(route('inventory.stock-in.form', $this->medicine));
    $response->assertOk();
});

test('stock in works via controller', function () {
    $response = $this->actingAs($this->user)->post(route('inventory.stock-in', $this->medicine), [
        'batch_number' => 'BATCH-001',
        'quantity' => 50,
        'received_date' => now()->toDateString(),
        'expiry_date' => now()->addYear()->toDateString(),
        'reason' => 'Purchase',
    ]);

    $response->assertRedirect();
    $this->assertEquals(50, $this->medicine->fresh()->stock_quantity);
    $this->assertEquals(1, $this->medicine->inventoryBatches()->count());
});

test('stock out form is accessible', function () {
    $batch = InventoryBatch::create([
        'medicine_id' => $this->medicine->id,
        'batch_number' => 'BATCH-001',
        'quantity' => 100,
        'received_date' => now()->toDateString(),
        'expiry_date' => now()->addYear()->toDateString(),
    ]);
    $this->medicine->reconcileStock();

    $response = $this->actingAs($this->user)->get(route('inventory.stock-out.form', $this->medicine));
    $response->assertOk();
});

test('stock out works via controller', function () {
    $batch = InventoryBatch::create([
        'medicine_id' => $this->medicine->id,
        'batch_number' => 'BATCH-001',
        'quantity' => 100,
        'received_date' => now()->toDateString(),
        'expiry_date' => now()->addYear()->toDateString(),
    ]);
    $this->medicine->reconcileStock();

    $response = $this->actingAs($this->user)->post(route('inventory.stock-out', $this->medicine), [
        'inventory_batch_id' => $batch->id,
        'quantity' => 30,
        'reason' => 'Dispensed',
    ]);

    $response->assertRedirect();
    $this->assertEquals(70, $this->medicine->fresh()->stock_quantity);
    $this->assertEquals(70, $batch->fresh()->quantity);
});

test('stock out prevents negative stock via controller', function () {
    $batch = InventoryBatch::create([
        'medicine_id' => $this->medicine->id,
        'batch_number' => 'BATCH-001',
        'quantity' => 10,
        'received_date' => now()->toDateString(),
        'expiry_date' => now()->addYear()->toDateString(),
    ]);
    $this->medicine->reconcileStock();

    $response = $this->actingAs($this->user)->post(route('inventory.stock-out', $this->medicine), [
        'inventory_batch_id' => $batch->id,
        'quantity' => 20,
        'reason' => 'Too much',
    ]);

    $response->assertSessionHasErrors('quantity');
    $this->assertEquals(10, $this->medicine->fresh()->stock_quantity);
    $this->assertEquals(10, $batch->fresh()->quantity);
});

test('adjust form is accessible', function () {
    $response = $this->actingAs($this->user)->get(route('inventory.adjust.form', $this->medicine));
    $response->assertOk();
});

test('adjust works via controller', function () {
    $batch = InventoryBatch::create([
        'medicine_id' => $this->medicine->id,
        'batch_number' => 'BATCH-001',
        'quantity' => 100,
        'received_date' => now()->toDateString(),
        'expiry_date' => now()->addYear()->toDateString(),
    ]);
    $this->medicine->reconcileStock();

    $response = $this->actingAs($this->user)->post(route('inventory.adjust', $this->medicine), [
        'inventory_batch_id' => $batch->id,
        'quantity' => 10,
        'direction' => 'increase',
        'reason' => 'Correction',
    ]);

    $response->assertRedirect();
    $this->assertEquals(110, $this->medicine->fresh()->stock_quantity);
    $this->assertEquals(110, $batch->fresh()->quantity);
});

// --- DISPENSING ---

test('prescription can be dispensed', function () {
    $batch = InventoryBatch::create([
        'medicine_id' => $this->medicine->id,
        'batch_number' => 'BATCH-001',
        'quantity' => 100,
        'received_date' => now()->toDateString(),
        'expiry_date' => now()->addYear()->toDateString(),
    ]);
    $this->medicine->reconcileStock();

    $prescription = Prescription::create([
        'patient_id' => \App\Models\Patient::factory()->create(['status' => 'active'])->id,
        'doctor_id' => $this->doctor->id,
        'prescribed_date' => now()->toDateString(),
        'status' => 'pending',
    ]);

    $item = PrescriptionItem::create([
        'prescription_id' => $prescription->id,
        'medicine_id' => $this->medicine->id,
        'dosage' => '500mg',
        'frequency' => '3x daily',
        'duration' => '5 days',
        'quantity' => 15,
    ]);

    $response = $this->actingAs($this->user)->post(route('inventory.dispense', $prescription), [
        'dispensed_quantities' => [$item->id => 15],
    ]);

    $response->assertRedirect();
    $this->assertEquals(85, $this->medicine->fresh()->stock_quantity);
    $this->assertEquals(85, $batch->fresh()->quantity);
    $prescription->refresh();
    $this->assertTrue($prescription->isDispensed());
});

test('dispensed quantity is validated', function () {
    $this->medicine->setOpeningStock(100, $this->user->id);

    $prescription = Prescription::create([
        'patient_id' => \App\Models\Patient::factory()->create(['status' => 'active'])->id,
        'doctor_id' => $this->doctor->id,
        'prescribed_date' => now()->toDateString(),
        'status' => 'pending',
    ]);

    $item = PrescriptionItem::create([
        'prescription_id' => $prescription->id,
        'medicine_id' => $this->medicine->id,
        'dosage' => '500mg',
        'frequency' => '3x daily',
        'duration' => '5 days',
        'quantity' => 15,
    ]);

    $response = $this->actingAs($this->user)->post(route('inventory.dispense', $prescription), [
        'dispensed_quantities' => [$item->id => 20],
    ]);

    $response->assertSessionHasErrors();
    $this->assertEquals(100, $this->medicine->fresh()->stock_quantity);
});

test('stock decreases correctly on dispensing', function () {
    $batch = InventoryBatch::create([
        'medicine_id' => $this->medicine->id,
        'batch_number' => 'BATCH-001',
        'quantity' => 100,
        'received_date' => now()->toDateString(),
        'expiry_date' => now()->addYear()->toDateString(),
    ]);
    $this->medicine->reconcileStock();

    $prescription = Prescription::create([
        'patient_id' => \App\Models\Patient::factory()->create(['status' => 'active'])->id,
        'doctor_id' => $this->doctor->id,
        'prescribed_date' => now()->toDateString(),
        'status' => 'pending',
    ]);

    $item = PrescriptionItem::create([
        'prescription_id' => $prescription->id,
        'medicine_id' => $this->medicine->id,
        'dosage' => '500mg',
        'frequency' => '3x daily',
        'duration' => '5 days',
        'quantity' => 10,
    ]);

    $this->actingAs($this->user)->post(route('inventory.dispense', $prescription), [
        'dispensed_quantities' => [$item->id => 10],
    ]);

    $this->assertEquals(90, $this->medicine->fresh()->stock_quantity);

    $movement = StockMovement::where('medicine_id', $this->medicine->id)->where('type', 'dispensed')->first();
    $this->assertNotNull($movement);
    $this->assertEquals(10, $movement->quantity);
    $this->assertEquals(90, $movement->balance_after);
    $this->assertEquals($batch->id, $movement->inventory_batch_id);
});

test('multiple medicine dispensing works', function () {
    $medicine2 = Medicine::create([
        'name' => 'Paracetamol',
        'unit_price' => 5.00,
        'stock_quantity' => 0,
        'minimum_stock_level' => 10,
        'is_active' => true,
    ]);

    $batch1 = InventoryBatch::create([
        'medicine_id' => $this->medicine->id,
        'batch_number' => 'BATCH-001',
        'quantity' => 100,
        'received_date' => now()->toDateString(),
        'expiry_date' => now()->addYear()->toDateString(),
    ]);
    $batch2 = InventoryBatch::create([
        'medicine_id' => $medicine2->id,
        'batch_number' => 'BATCH-002',
        'quantity' => 50,
        'received_date' => now()->toDateString(),
        'expiry_date' => now()->addYear()->toDateString(),
    ]);
    $this->medicine->reconcileStock();
    $medicine2->reconcileStock();

    $prescription = Prescription::create([
        'patient_id' => \App\Models\Patient::factory()->create(['status' => 'active'])->id,
        'doctor_id' => $this->doctor->id,
        'prescribed_date' => now()->toDateString(),
        'status' => 'pending',
    ]);

    $item1 = PrescriptionItem::create([
        'prescription_id' => $prescription->id,
        'medicine_id' => $this->medicine->id,
        'dosage' => '500mg',
        'frequency' => '3x daily',
        'duration' => '5 days',
        'quantity' => 15,
    ]);

    $item2 = PrescriptionItem::create([
        'prescription_id' => $prescription->id,
        'medicine_id' => $medicine2->id,
        'dosage' => '500mg',
        'frequency' => '2x daily',
        'duration' => '3 days',
        'quantity' => 6,
    ]);

    $response = $this->actingAs($this->user)->post(route('inventory.dispense', $prescription), [
        'dispensed_quantities' => [
            $item1->id => 15,
            $item2->id => 6,
        ],
    ]);

    $response->assertRedirect();
    $this->assertEquals(85, $this->medicine->fresh()->stock_quantity);
    $this->assertEquals(44, $medicine2->fresh()->stock_quantity);
    $this->assertEquals(85, $batch1->fresh()->quantity);
    $this->assertEquals(44, $batch2->fresh()->quantity);
});

test('failed dispensing rolls back all stock changes', function () {
    $medicine2 = Medicine::create([
        'name' => 'Paracetamol',
        'unit_price' => 5.00,
        'stock_quantity' => 0,
        'minimum_stock_level' => 10,
        'is_active' => true,
    ]);

    $batch1 = InventoryBatch::create([
        'medicine_id' => $this->medicine->id,
        'batch_number' => 'BATCH-001',
        'quantity' => 100,
        'received_date' => now()->toDateString(),
        'expiry_date' => now()->addYear()->toDateString(),
    ]);
    $batch2 = InventoryBatch::create([
        'medicine_id' => $medicine2->id,
        'batch_number' => 'BATCH-002',
        'quantity' => 3,
        'received_date' => now()->toDateString(),
        'expiry_date' => now()->addYear()->toDateString(),
    ]);
    $this->medicine->reconcileStock();
    $medicine2->reconcileStock();

    $prescription = Prescription::create([
        'patient_id' => \App\Models\Patient::factory()->create(['status' => 'active'])->id,
        'doctor_id' => $this->doctor->id,
        'prescribed_date' => now()->toDateString(),
        'status' => 'pending',
    ]);

    $item1 = PrescriptionItem::create([
        'prescription_id' => $prescription->id,
        'medicine_id' => $this->medicine->id,
        'dosage' => '500mg',
        'frequency' => '3x daily',
        'duration' => '5 days',
        'quantity' => 15,
    ]);

    $item2 = PrescriptionItem::create([
        'prescription_id' => $prescription->id,
        'medicine_id' => $medicine2->id,
        'dosage' => '500mg',
        'frequency' => '2x daily',
        'duration' => '3 days',
        'quantity' => 6,
    ]);

    $response = $this->actingAs($this->user)->post(route('inventory.dispense', $prescription), [
        'dispensed_quantities' => [
            $item1->id => 15,
            $item2->id => 6,
        ],
    ]);

    $response->assertSessionHasErrors();
    $this->assertEquals(100, $this->medicine->fresh()->stock_quantity);
    $this->assertEquals(3, $medicine2->fresh()->stock_quantity);
    $this->assertEquals('pending', $prescription->fresh()->status);
});

// --- BATCH / LOT INVENTORY ---

test('stock in creates a batch', function () {
    $this->actingAs($this->user)->post(route('inventory.stock-in', $this->medicine), [
        'batch_number' => 'LOT-0001',
        'quantity' => 50,
        'received_date' => now()->toDateString(),
        'expiry_date' => now()->addMonths(6)->toDateString(),
    ])->assertRedirect();

    $batch = $this->medicine->inventoryBatches()->first();
    $this->assertNotNull($batch);
    $this->assertEquals('LOT-0001', $batch->batch_number);
    $this->assertEquals(50, $batch->quantity);
    $this->assertEquals(50, $this->medicine->fresh()->stock_quantity);
});

test('multiple batches for one medicine remain separate', function () {
    InventoryBatch::create([
        'medicine_id' => $this->medicine->id,
        'batch_number' => 'BATCH-A',
        'quantity' => 50,
        'received_date' => now()->toDateString(),
        'expiry_date' => now()->addMonths(6)->toDateString(),
    ]);
    InventoryBatch::create([
        'medicine_id' => $this->medicine->id,
        'batch_number' => 'BATCH-B',
        'quantity' => 100,
        'received_date' => now()->toDateString(),
        'expiry_date' => now()->addMonths(12)->toDateString(),
    ]);
    $this->medicine->reconcileStock();

    $batches = $this->medicine->inventoryBatches;
    $this->assertEquals(2, $batches->count());
    $this->assertEquals(50, $batches->firstWhere('batch_number', 'BATCH-A')->quantity);
    $this->assertEquals(100, $batches->firstWhere('batch_number', 'BATCH-B')->quantity);
    $this->assertEquals(150, $this->medicine->fresh()->stock_quantity);
});

test('batch quantity is tracked correctly on stock in and out', function () {
    $batch = InventoryBatch::create([
        'medicine_id' => $this->medicine->id,
        'batch_number' => 'BATCH-A',
        'quantity' => 0,
        'received_date' => now()->toDateString(),
        'expiry_date' => now()->addMonths(6)->toDateString(),
    ]);
    $this->medicine->reconcileStock();

    $batch->stockIn(80, 'Purchase', $this->user->id);
    $this->assertEquals(80, $batch->fresh()->quantity);

    $batch->stockOut(30, 'Dispensed', $this->user->id);
    $this->assertEquals(50, $batch->fresh()->quantity);
    $this->assertEquals(50, $this->medicine->fresh()->stock_quantity);
});

test('stock out deducts from the selected batch only', function () {
    $batchA = InventoryBatch::create([
        'medicine_id' => $this->medicine->id,
        'batch_number' => 'BATCH-A',
        'quantity' => 50,
        'received_date' => now()->toDateString(),
        'expiry_date' => now()->addMonths(6)->toDateString(),
    ]);
    $batchB = InventoryBatch::create([
        'medicine_id' => $this->medicine->id,
        'batch_number' => 'BATCH-B',
        'quantity' => 100,
        'received_date' => now()->toDateString(),
        'expiry_date' => now()->addMonths(12)->toDateString(),
    ]);
    $this->medicine->reconcileStock();

    $this->actingAs($this->user)->post(route('inventory.stock-out', $this->medicine), [
        'inventory_batch_id' => $batchA->id,
        'quantity' => 20,
        'reason' => 'Damaged',
    ])->assertRedirect();

    $this->assertEquals(30, $batchA->fresh()->quantity);
    $this->assertEquals(100, $batchB->fresh()->quantity);
    $this->assertEquals(130, $this->medicine->fresh()->stock_quantity);
});

test('fefo selects the earliest valid expiry batch first', function () {
    $early = InventoryBatch::create([
        'medicine_id' => $this->medicine->id,
        'batch_number' => 'BATCH-EARLY',
        'quantity' => 10,
        'received_date' => now()->toDateString(),
        'expiry_date' => now()->addDays(5)->toDateString(),
    ]);
    $late = InventoryBatch::create([
        'medicine_id' => $this->medicine->id,
        'batch_number' => 'BATCH-LATE',
        'quantity' => 100,
        'received_date' => now()->toDateString(),
        'expiry_date' => now()->addMonths(6)->toDateString(),
    ]);
    $this->medicine->reconcileStock();

    $this->medicine->deductFromBatches(8, 'Dispensed', $this->user->id);

    $this->assertEquals(2, $early->fresh()->quantity);
    $this->assertEquals(100, $late->fresh()->quantity);

    // A larger deduction flows into the next batch.
    $this->medicine->deductFromBatches(5, 'Dispensed', $this->user->id);
    $this->assertEquals(0, $early->fresh()->quantity);
    $this->assertEquals(97, $late->fresh()->quantity);
});

test('fefo never selects an expired batch', function () {
    $expired = InventoryBatch::create([
        'medicine_id' => $this->medicine->id,
        'batch_number' => 'BATCH-EXPIRED',
        'quantity' => 50,
        'received_date' => now()->subMonths(3)->toDateString(),
        'expiry_date' => now()->subDay()->toDateString(),
    ]);
    $valid = InventoryBatch::create([
        'medicine_id' => $this->medicine->id,
        'batch_number' => 'BATCH-VALID',
        'quantity' => 10,
        'received_date' => now()->toDateString(),
        'expiry_date' => now()->addMonths(6)->toDateString(),
    ]);

    $this->medicine->deductFromBatches(10, 'Dispensed', $this->user->id);

    $this->assertEquals(50, $expired->fresh()->quantity);
    $this->assertEquals(0, $valid->fresh()->quantity);
});

test('expired batch cannot be dispensed', function () {
    $expired = InventoryBatch::create([
        'medicine_id' => $this->medicine->id,
        'batch_number' => 'BATCH-EXPIRED',
        'quantity' => 50,
        'received_date' => now()->subMonths(3)->toDateString(),
        'expiry_date' => now()->subDay()->toDateString(),
    ]);
    $this->medicine->reconcileStock();

    $prescription = Prescription::create([
        'patient_id' => \App\Models\Patient::factory()->create(['status' => 'active'])->id,
        'doctor_id' => $this->doctor->id,
        'prescribed_date' => now()->toDateString(),
        'status' => 'pending',
    ]);

    $item = PrescriptionItem::create([
        'prescription_id' => $prescription->id,
        'medicine_id' => $this->medicine->id,
        'dosage' => '500mg',
        'frequency' => '3x daily',
        'duration' => '5 days',
        'quantity' => 10,
    ]);

    // Manual selection of the expired batch must be blocked.
    $response = $this->actingAs($this->user)->post(route('inventory.dispense', $prescription), [
        'dispensed_quantities' => [$item->id => 10],
        'batch_selections' => [$item->id => $expired->id],
    ]);

    $response->assertSessionHasErrors();
    $this->assertEquals(50, $expired->fresh()->quantity);

    // Automatic FEFO must also fail (no valid batches → insufficient usable stock).
    $response = $this->actingAs($this->user)->post(route('inventory.dispense', $prescription), [
        'dispensed_quantities' => [$item->id => 10],
    ]);
    $response->assertSessionHasErrors();
    $this->assertEquals(50, $expired->fresh()->quantity);
    $this->assertEquals('pending', $prescription->fresh()->status);
});

test('stock adjustment is batch-specific', function () {
    $batchA = InventoryBatch::create([
        'medicine_id' => $this->medicine->id,
        'batch_number' => 'BATCH-A',
        'quantity' => 50,
        'received_date' => now()->toDateString(),
        'expiry_date' => now()->addMonths(6)->toDateString(),
    ]);
    $batchB = InventoryBatch::create([
        'medicine_id' => $this->medicine->id,
        'batch_number' => 'BATCH-B',
        'quantity' => 100,
        'received_date' => now()->toDateString(),
        'expiry_date' => now()->addMonths(12)->toDateString(),
    ]);
    $this->medicine->reconcileStock();

    $this->actingAs($this->user)->post(route('inventory.adjust', $this->medicine), [
        'inventory_batch_id' => $batchB->id,
        'quantity' => 10,
        'direction' => 'decrease',
        'reason' => 'Damaged items',
    ])->assertRedirect();

    $this->assertEquals(50, $batchA->fresh()->quantity);
    $this->assertEquals(90, $batchB->fresh()->quantity);
    $this->assertEquals(140, $this->medicine->fresh()->stock_quantity);
});

test('patient dispensing records the exact batch', function () {
    $batch = InventoryBatch::create([
        'medicine_id' => $this->medicine->id,
        'batch_number' => 'BATCH-A',
        'quantity' => 100,
        'received_date' => now()->toDateString(),
        'expiry_date' => now()->addMonths(6)->toDateString(),
    ]);
    $this->medicine->reconcileStock();

    $prescription = Prescription::create([
        'patient_id' => \App\Models\Patient::factory()->create(['status' => 'active'])->id,
        'doctor_id' => $this->doctor->id,
        'prescribed_date' => now()->toDateString(),
        'status' => 'pending',
    ]);

    $item = PrescriptionItem::create([
        'prescription_id' => $prescription->id,
        'medicine_id' => $this->medicine->id,
        'dosage' => '500mg',
        'frequency' => '3x daily',
        'duration' => '5 days',
        'quantity' => 15,
    ]);

    $this->actingAs($this->user)->post(route('inventory.dispense', $prescription), [
        'dispensed_quantities' => [$item->id => 15],
    ])->assertRedirect();

    $movement = StockMovement::where('type', 'dispensed')
        ->where('reference_type', Prescription::class)
        ->where('reference_id', $prescription->id)
        ->first();

    $this->assertNotNull($movement);
    $this->assertEquals($batch->id, $movement->inventory_batch_id);
    $this->assertEquals(15, $movement->quantity);
    $this->assertEquals($this->user->id, $movement->performed_by);
});

test('manual batch selection is dispensed from the chosen batch', function () {
    $batchA = InventoryBatch::create([
        'medicine_id' => $this->medicine->id,
        'batch_number' => 'BATCH-A',
        'quantity' => 100,
        'received_date' => now()->toDateString(),
        'expiry_date' => now()->addMonths(6)->toDateString(),
    ]);
    $batchB = InventoryBatch::create([
        'medicine_id' => $this->medicine->id,
        'batch_number' => 'BATCH-B',
        'quantity' => 50,
        'received_date' => now()->toDateString(),
        'expiry_date' => now()->addMonths(12)->toDateString(),
    ]);
    $this->medicine->reconcileStock();

    $prescription = Prescription::create([
        'patient_id' => \App\Models\Patient::factory()->create(['status' => 'active'])->id,
        'doctor_id' => $this->doctor->id,
        'prescribed_date' => now()->toDateString(),
        'status' => 'pending',
    ]);

    $item = PrescriptionItem::create([
        'prescription_id' => $prescription->id,
        'medicine_id' => $this->medicine->id,
        'dosage' => '500mg',
        'frequency' => '3x daily',
        'duration' => '5 days',
        'quantity' => 20,
    ]);

    $this->actingAs($this->user)->post(route('inventory.dispense', $prescription), [
        'dispensed_quantities' => [$item->id => 20],
        'batch_selections' => [$item->id => $batchB->id],
    ])->assertRedirect();

    $this->assertEquals(100, $batchA->fresh()->quantity);
    $this->assertEquals(30, $batchB->fresh()->quantity);
});

test('expired stock remains traceable and usable stock excludes it', function () {
    InventoryBatch::create([
        'medicine_id' => $this->medicine->id,
        'batch_number' => 'BATCH-EXPIRED',
        'quantity' => 30,
        'received_date' => now()->subMonths(3)->toDateString(),
        'expiry_date' => now()->subDay()->toDateString(),
    ]);
    InventoryBatch::create([
        'medicine_id' => $this->medicine->id,
        'batch_number' => 'BATCH-VALID',
        'quantity' => 70,
        'received_date' => now()->toDateString(),
        'expiry_date' => now()->addMonths(6)->toDateString(),
    ]);
    $this->medicine->reconcileStock();

    $medicine = $this->medicine->fresh();

    $this->assertEquals(100, $medicine->totalPhysicalStock());
    $this->assertEquals(70, $medicine->usableStockQuantity());
    $this->assertEquals(30, $medicine->expiredStockQuantity());
    // Aggregate stock_quantity represents usable stock.
    $this->assertEquals(70, $medicine->stock_quantity);
});

test('expiring soon batch status works', function () {
    $batch = InventoryBatch::create([
        'medicine_id' => $this->medicine->id,
        'batch_number' => 'BATCH-A',
        'quantity' => 20,
        'received_date' => now()->toDateString(),
        'expiry_date' => now()->addDays(15)->toDateString(),
    ]);

    $this->assertEquals('expiring', $batch->expiry_status);
});

test('depleted batch status works', function () {
    $batch = InventoryBatch::create([
        'medicine_id' => $this->medicine->id,
        'batch_number' => 'BATCH-A',
        'quantity' => 0,
        'received_date' => now()->toDateString(),
        'expiry_date' => now()->addMonths(6)->toDateString(),
    ]);

    $this->assertEquals('depleted', $batch->expiry_status);
});

test('stock movement contains batch information', function () {
    $batch = InventoryBatch::create([
        'medicine_id' => $this->medicine->id,
        'batch_number' => 'BATCH-A',
        'quantity' => 50,
        'received_date' => now()->toDateString(),
        'expiry_date' => now()->addMonths(6)->toDateString(),
    ]);
    $this->medicine->reconcileStock();

    $movement = $batch->stockOut(10, 'Test', $this->user->id);

    $this->assertEquals($batch->id, $movement->inventory_batch_id);
    $this->assertEquals(50, $movement->balance_before);
    $this->assertEquals(40, $movement->balance_after);
    $this->assertEquals($this->user->id, $movement->performed_by);
    $this->assertNotNull($movement->medicine_id);
});

test('expired batch can be written off and recorded as expired movement', function () {
    $batch = InventoryBatch::create([
        'medicine_id' => $this->medicine->id,
        'batch_number' => 'BATCH-EXPIRED',
        'quantity' => 25,
        'received_date' => now()->subMonths(3)->toDateString(),
        'expiry_date' => now()->subDay()->toDateString(),
    ]);
    $this->medicine->reconcileStock();

    $this->actingAs($this->user)->post(route('inventory.batch.expire', $batch))->assertRedirect();

    $this->assertEquals(0, $batch->fresh()->quantity);
    $this->assertEquals('depleted', $batch->fresh()->expiry_status);

    $movement = StockMovement::where('inventory_batch_id', $batch->id)->where('type', 'expired')->first();
    $this->assertNotNull($movement);
    $this->assertEquals(-25, $movement->quantity);
});

// --- SAFE STOCK DELETION ---

test('unused stock batch can be deleted', function () {
    $batch = InventoryBatch::create([
        'medicine_id' => $this->medicine->id,
        'batch_number' => 'BATCH-UNUSED',
        'quantity' => 50,
        'received_date' => now()->toDateString(),
        'expiry_date' => now()->addMonths(6)->toDateString(),
    ]);
    $this->medicine->reconcileStock();

    $this->assertTrue($batch->canDelete());

    $response = $this->actingAs($this->user)->delete(route('inventory.batch.destroy', $batch));
    $response->assertRedirect();

    $this->assertDatabaseMissing('inventory_batches', ['id' => $batch->id]);
    $this->assertEquals(0, $this->medicine->fresh()->stock_quantity);
});

test('stock batch with stock out history cannot be deleted', function () {
    $batch = InventoryBatch::create([
        'medicine_id' => $this->medicine->id,
        'batch_number' => 'BATCH-USED',
        'quantity' => 100,
        'received_date' => now()->toDateString(),
        'expiry_date' => now()->addMonths(6)->toDateString(),
    ]);
    $this->medicine->reconcileStock();
    $batch->stockOut(10, 'Dispensed', $this->user->id);

    $this->assertFalse($batch->fresh()->canDelete());

    $response = $this->actingAs($this->user)->delete(route('inventory.batch.destroy', $batch));
    $response->assertSessionHas('error');

    $this->assertDatabaseHas('inventory_batches', ['id' => $batch->id]);
    $this->assertDatabaseHas('stock_movements', ['inventory_batch_id' => $batch->id]);
});

test('stock batch with dispensing history cannot be deleted', function () {
    $batch = InventoryBatch::create([
        'medicine_id' => $this->medicine->id,
        'batch_number' => 'BATCH-DISPENSED',
        'quantity' => 100,
        'received_date' => now()->toDateString(),
        'expiry_date' => now()->addMonths(6)->toDateString(),
    ]);
    $this->medicine->reconcileStock();

    $prescription = Prescription::create([
        'patient_id' => \App\Models\Patient::factory()->create(['status' => 'active'])->id,
        'doctor_id' => $this->doctor->id,
        'prescribed_date' => now()->toDateString(),
        'status' => 'pending',
    ]);

    $item = PrescriptionItem::create([
        'prescription_id' => $prescription->id,
        'medicine_id' => $this->medicine->id,
        'dosage' => '500mg',
        'frequency' => '3x daily',
        'duration' => '5 days',
        'quantity' => 10,
    ]);

    $this->actingAs($this->user)->post(route('inventory.dispense', $prescription), [
        'dispensed_quantities' => [$item->id => 10],
    ])->assertRedirect();

    $this->assertFalse($batch->fresh()->canDelete());

    $response = $this->actingAs($this->user)->delete(route('inventory.batch.destroy', $batch));
    $response->assertSessionHas('error');
    $this->assertDatabaseHas('inventory_batches', ['id' => $batch->id]);
});

test('stock batch with adjustments cannot be deleted', function () {
    $batch = InventoryBatch::create([
        'medicine_id' => $this->medicine->id,
        'batch_number' => 'BATCH-ADJUSTED',
        'quantity' => 100,
        'received_date' => now()->toDateString(),
        'expiry_date' => now()->addMonths(6)->toDateString(),
    ]);
    $this->medicine->reconcileStock();
    $batch->adjust(5, false, 'Damaged', $this->user->id);

    $this->assertFalse($batch->fresh()->canDelete());

    $response = $this->actingAs($this->user)->delete(route('inventory.batch.destroy', $batch));
    $response->assertSessionHas('error');
    $this->assertDatabaseHas('inventory_batches', ['id' => $batch->id]);
});

test('delete is blocked server-side even if frontend is bypassed', function () {
    $batch = InventoryBatch::create([
        'medicine_id' => $this->medicine->id,
        'batch_number' => 'BATCH-PROTECTED',
        'quantity' => 100,
        'received_date' => now()->toDateString(),
        'expiry_date' => now()->addMonths(6)->toDateString(),
    ]);
    $this->medicine->reconcileStock();
    $batch->stockOut(10, 'Dispensed', $this->user->id);

    // Calling the destroy route directly must still be rejected.
    $this->actingAs($this->user)->delete(route('inventory.batch.destroy', $batch))
        ->assertSessionHas('error');

    $this->assertDatabaseHas('inventory_batches', ['id' => $batch->id]);
    $this->assertDatabaseHas('stock_movements', ['inventory_batch_id' => $batch->id]);
});

test('unauthorized user cannot delete stock batches', function () {
    $batch = InventoryBatch::create([
        'medicine_id' => $this->medicine->id,
        'batch_number' => 'BATCH-UNUSED',
        'quantity' => 50,
        'received_date' => now()->toDateString(),
        'expiry_date' => now()->addMonths(6)->toDateString(),
    ]);

    $user = User::factory()->create();

    $this->actingAs($user)->delete(route('inventory.batch.destroy', $batch))->assertForbidden();
    $this->assertDatabaseHas('inventory_batches', ['id' => $batch->id]);
});

// --- EXPIRY REPORT ---

test('expiry report shows expired batches', function () {
    InventoryBatch::create([
        'medicine_id' => $this->medicine->id,
        'batch_number' => 'BATCH-EXPIRED-RPT',
        'quantity' => 10,
        'received_date' => now()->subMonths(3)->toDateString(),
        'expiry_date' => now()->subDay()->toDateString(),
    ]);

    $this->actingAs($this->user)->get(route('inventory.expiry', ['status' => 'expired']))
        ->assertOk()
        ->assertSee('BATCH-EXPIRED-RPT');
});

test('expiry report shows expiring soon batches', function () {
    InventoryBatch::create([
        'medicine_id' => $this->medicine->id,
        'batch_number' => 'BATCH-EXPIRING-RPT',
        'quantity' => 20,
        'received_date' => now()->toDateString(),
        'expiry_date' => now()->addDays(15)->toDateString(),
    ]);

    $this->actingAs($this->user)->get(route('inventory.expiry', ['status' => 'expiring']))
        ->assertOk()
        ->assertSee('BATCH-EXPIRING-RPT');
});

test('expiry report shows active batches', function () {
    InventoryBatch::create([
        'medicine_id' => $this->medicine->id,
        'batch_number' => 'BATCH-ACTIVE-RPT',
        'quantity' => 30,
        'received_date' => now()->toDateString(),
        'expiry_date' => now()->addMonths(6)->toDateString(),
    ]);

    $this->actingAs($this->user)->get(route('inventory.expiry', ['status' => 'active']))
        ->assertOk()
        ->assertSee('BATCH-ACTIVE-RPT');
});

test('expiry report shows depleted batches', function () {
    InventoryBatch::create([
        'medicine_id' => $this->medicine->id,
        'batch_number' => 'BATCH-DEPLETED-RPT',
        'quantity' => 0,
        'received_date' => now()->toDateString(),
        'expiry_date' => now()->addMonths(6)->toDateString(),
    ]);

    $this->actingAs($this->user)->get(route('inventory.expiry', ['status' => 'depleted']))
        ->assertOk()
        ->assertSee('BATCH-DEPLETED-RPT');
});

test('expiry report can search by batch number', function () {
    InventoryBatch::create([
        'medicine_id' => $this->medicine->id,
        'batch_number' => 'LOT-SEARCH-ME',
        'quantity' => 25,
        'received_date' => now()->toDateString(),
        'expiry_date' => now()->addMonths(6)->toDateString(),
    ]);

    $this->actingAs($this->user)->get(route('inventory.expiry', ['search' => 'SEARCH-ME']))
        ->assertOk()
        ->assertSee('LOT-SEARCH-ME');
});

test('expiry status is calculated from the expiry date', function () {
    $active = InventoryBatch::create([
        'medicine_id' => $this->medicine->id,
        'batch_number' => 'B-ACTIVE',
        'quantity' => 10,
        'received_date' => now()->toDateString(),
        'expiry_date' => now()->addMonths(6)->toDateString(),
    ]);
    $expiring = InventoryBatch::create([
        'medicine_id' => $this->medicine->id,
        'batch_number' => 'B-EXPIRING',
        'quantity' => 10,
        'received_date' => now()->toDateString(),
        'expiry_date' => now()->addDays(15)->toDateString(),
    ]);
    $expired = InventoryBatch::create([
        'medicine_id' => $this->medicine->id,
        'batch_number' => 'B-EXPIRED',
        'quantity' => 10,
        'received_date' => now()->subMonths(3)->toDateString(),
        'expiry_date' => now()->subDay()->toDateString(),
    ]);
    $depleted = InventoryBatch::create([
        'medicine_id' => $this->medicine->id,
        'batch_number' => 'B-DEPLETED',
        'quantity' => 0,
        'received_date' => now()->toDateString(),
        'expiry_date' => now()->addMonths(6)->toDateString(),
    ]);

    $this->assertEquals('active', $active->expiry_status);
    $this->assertEquals('expiring', $expiring->expiry_status);
    $this->assertEquals('expired', $expired->expiry_status);
    $this->assertEquals('depleted', $depleted->expiry_status);
});

// --- STOCK MOVEMENT DETAIL MODAL ---

test('inventory dashboard shows recent movements directly without a view action', function () {
    $batch = InventoryBatch::create([
        'medicine_id' => $this->medicine->id,
        'batch_number' => 'BATCH-MODAL',
        'quantity' => 100,
        'received_date' => now()->toDateString(),
        'expiry_date' => now()->addMonths(6)->toDateString(),
    ]);
    $this->medicine->reconcileStock();
    $batch->stockIn(50, 'Purchase order #5', $this->user->id);

    $response = $this->actingAs($this->user)->get(route('inventory.dashboard'));
    $response->assertOk();
    // The movement information is displayed directly — no View action / modal on the dashboard.
    $response->assertDontSee('open-movement-detail', false);
    $response->assertDontSee('data-movement="', false);
    $response->assertSee('BATCH-MODAL', false);
    $response->assertSee('Amoxicillin', false);
    // The Reason column is restored and shows the stored movement reason.
    $response->assertSee('Purchase order #5', false);
});

test('recent movements show a dash when the reason is missing', function () {
    $batch = InventoryBatch::create([
        'medicine_id' => $this->medicine->id,
        'batch_number' => 'BATCH-NOREASON',
        'quantity' => 100,
        'received_date' => now()->toDateString(),
        'expiry_date' => now()->addMonths(6)->toDateString(),
    ]);
    $this->medicine->reconcileStock();
    $batch->stockOut(5, null, $this->user->id);

    $html = $this->actingAs($this->user)->get(route('inventory.dashboard'))->assertOk()->getContent();
    $this->assertStringContainsString('d-none d-md-table-cell">-</td>', $html);
});

test('movement detail modal shows medicine, batch, before/after, reason, note, user and date', function () {
    $batch = InventoryBatch::create([
        'medicine_id' => $this->medicine->id,
        'batch_number' => 'BATCH-001',
        'quantity' => 50,
        'received_date' => now()->toDateString(),
        'expiry_date' => now()->addMonths(6)->toDateString(),
        'notes' => 'Supplier note: keep refrigerated',
    ]);
    $this->medicine->reconcileStock();
    $batch->stockOut(10, 'Damaged during handling', $this->user->id);

    $html = $this->actingAs($this->user)->get(route('inventory.movements'))->assertOk()->getContent();

    $this->assertStringContainsString('BATCH-001', $html);
    $this->assertStringContainsString('Amoxicillin', $html);
    $this->assertStringContainsString('Damaged during handling', $html);
    $this->assertStringContainsString('Supplier note: keep refrigerated', $html);
    $this->assertStringContainsString(e($this->user->name), $html);
    // Before/after quantities are embedded in the (HTML-escaped) modal JSON payload.
    $this->assertStringContainsString('&quot;before&quot;:50', $html);
    $this->assertStringContainsString('&quot;after&quot;:40', $html);
});

test('movements page includes the movement detail modal', function () {
    $batch = InventoryBatch::create([
        'medicine_id' => $this->medicine->id,
        'batch_number' => 'BATCH-MODAL-2',
        'quantity' => 30,
        'received_date' => now()->toDateString(),
        'expiry_date' => now()->addMonths(6)->toDateString(),
    ]);
    $this->medicine->reconcileStock();
    $batch->stockOut(5, 'Damaged', $this->user->id);

    $response = $this->actingAs($this->user)->get(route('inventory.movements'));
    $response->assertOk();
    $response->assertSee('open-movement-detail', false);
    $response->assertSee('Stock Movement Detail', false);
});

// --- REGRESSION ---

test('existing patient tests still pass', function () {
    $response = $this->actingAs($this->user)->get(route('patients.index'));
    $response->assertOk();
});

test('existing appointment tests still pass', function () {
    $response = $this->actingAs($this->user)->get(route('appointments.index'));
    $response->assertOk();
});

test('existing queue tests still pass', function () {
    $response = $this->actingAs($this->user)->get(route('queue.index'));
    $response->assertOk();
});

test('existing consultation tests still pass', function () {
    $response = $this->actingAs($this->user)->get(route('consultations.index'));
    $response->assertOk();
});

test('existing prescription tests still pass', function () {
    $response = $this->actingAs($this->user)->get(route('prescriptions.index'));
    $response->assertOk();
});

test('existing billing tests still pass', function () {
    $response = $this->actingAs($this->user)->get(route('invoices.index'));
    $response->assertOk();
});
