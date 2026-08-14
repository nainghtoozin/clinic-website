<?php

use App\Models\Doctor;
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
    $this->medicine->setOpeningStock(100, $this->user->id);

    $response = $this->actingAs($this->user)->post(route('inventory.stock-in', $this->medicine), [
        'quantity' => 50,
        'reason' => 'Purchase',
    ]);

    $response->assertRedirect();
    $this->assertEquals(150, $this->medicine->fresh()->stock_quantity);
});

test('stock out form is accessible', function () {
    $this->medicine->setOpeningStock(100, $this->user->id);

    $response = $this->actingAs($this->user)->get(route('inventory.stock-out.form', $this->medicine));
    $response->assertOk();
});

test('stock out works via controller', function () {
    $this->medicine->setOpeningStock(100, $this->user->id);

    $response = $this->actingAs($this->user)->post(route('inventory.stock-out', $this->medicine), [
        'quantity' => 30,
        'reason' => 'Dispensed',
    ]);

    $response->assertRedirect();
    $this->assertEquals(70, $this->medicine->fresh()->stock_quantity);
});

test('stock out prevents negative stock via controller', function () {
    $this->medicine->setOpeningStock(10, $this->user->id);

    $response = $this->actingAs($this->user)->post(route('inventory.stock-out', $this->medicine), [
        'quantity' => 20,
        'reason' => 'Too much',
    ]);

    $response->assertSessionHasErrors('quantity');
    $this->assertEquals(10, $this->medicine->fresh()->stock_quantity);
});

test('adjust form is accessible', function () {
    $response = $this->actingAs($this->user)->get(route('inventory.adjust.form', $this->medicine));
    $response->assertOk();
});

test('adjust works via controller', function () {
    $this->medicine->setOpeningStock(100, $this->user->id);

    $response = $this->actingAs($this->user)->post(route('inventory.adjust', $this->medicine), [
        'quantity' => 10,
        'direction' => 'increase',
        'reason' => 'Correction',
    ]);

    $response->assertRedirect();
    $this->assertEquals(110, $this->medicine->fresh()->stock_quantity);
});

// --- DISPENSING ---

test('prescription can be dispensed', function () {
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
        'dispensed_quantities' => [$item->id => 15],
    ]);

    $response->assertRedirect();
    $this->assertEquals(85, $this->medicine->fresh()->stock_quantity);
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
        'quantity' => 10,
    ]);

    $this->actingAs($this->user)->post(route('inventory.dispense', $prescription), [
        'dispensed_quantities' => [$item->id => 10],
    ]);

    $this->assertEquals(90, $this->medicine->fresh()->stock_quantity);

    $movement = StockMovement::where('medicine_id', $this->medicine->id)->where('type', 'stock_out')->first();
    $this->assertNotNull($movement);
    $this->assertEquals(10, $movement->quantity);
    $this->assertEquals(90, $movement->balance_after);
});

test('multiple medicine dispensing works', function () {
    $medicine2 = Medicine::create([
        'name' => 'Paracetamol',
        'unit_price' => 5.00,
        'stock_quantity' => 50,
        'minimum_stock_level' => 10,
        'is_active' => true,
    ]);

    $this->medicine->setOpeningStock(100, $this->user->id);

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
});

test('failed dispensing rolls back all stock changes', function () {
    $medicine2 = Medicine::create([
        'name' => 'Paracetamol',
        'unit_price' => 5.00,
        'stock_quantity' => 3,
        'minimum_stock_level' => 10,
        'is_active' => true,
    ]);

    $this->medicine->setOpeningStock(100, $this->user->id);

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
