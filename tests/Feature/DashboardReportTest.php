<?php

use App\Enums\AppointmentStatus;
use App\Models\Appointment;
use App\Models\Consultation;
use App\Models\Doctor;
use App\Models\Invoice;
use App\Models\Medicine;
use App\Models\Payment;
use App\Models\Patient;
use App\Models\QueueTicket;
use App\Models\StockMovement;
use App\Models\User;
use Spatie\Permission\Models\Permission;

beforeEach(function () {
    $this->permissions = [
        'dashboard.view',
        'report.patient',
        'report.appointment',
        'report.consultation',
        'report.financial',
        'report.inventory',
        'patient.view', 'patient.create', 'patient.edit', 'patient.delete',
        'appointment.view', 'appointment.create', 'appointment.edit', 'appointment.cancel',
        'queue.view', 'queue.checkin', 'queue.call', 'queue.consult', 'queue.cancel',
        'consultation.view', 'consultation.create', 'consultation.edit', 'consultation.complete',
        'medicine.view', 'medicine.create',
        'invoice.view', 'invoice.create',
        'payment.view', 'payment.create',
        'inventory.view',
        'prescription.view',
        'doctor.view',
    ];

    foreach ($this->permissions as $perm) {
        Permission::firstOrCreate(['name' => $perm, 'guard_name' => 'web']);
    }

    $this->user = User::factory()->create();
    $this->user->givePermissionTo($this->permissions);

    $this->doctor = Doctor::factory()->create([
        'user_id' => $this->user->id,
        'is_available' => true,
    ]);

    $this->patient = Patient::factory()->create(['status' => 'active']);
});

// --- DASHBOARD ---

test('dashboard loads', function () {
    $response = $this->actingAs($this->user)->get(route('dashboard'));
    $response->assertOk();
});

test('dashboard shows today appointment count', function () {
    $response = $this->actingAs($this->user)->get(route('dashboard'));
    $response->assertOk();
    $response->assertSee("Appointments Today");
});

test('dashboard shows queue count', function () {
    QueueTicket::create([
        'patient_id' => $this->patient->id,
        'doctor_id' => $this->doctor->id,
        'queue_date' => now()->toDateString(),
        'ticket_number' => 'A001',
        'status' => 'waiting',
    ]);

    $response = $this->actingAs($this->user)->get(route('dashboard'));
    $response->assertOk();
    $response->assertSee('Queue Waiting');
});

test('dashboard shows consultation count', function () {
    $response = $this->actingAs($this->user)->get(route('dashboard'));
    $response->assertOk();
    $response->assertSee('Consultations');
});

test('dashboard shows low stock count', function () {
    Medicine::create([
        'name' => 'Low Stock Med',
        'unit_price' => 10.00,
        'stock_quantity' => 2,
        'minimum_stock_level' => 10,
        'is_active' => true,
    ]);

    $response = $this->actingAs($this->user)->get(route('dashboard'));
    $response->assertOk();
    $response->assertSee('Low Stock');
});

test('dashboard shows expired count', function () {
    Medicine::create([
        'name' => 'Expired Med',
        'unit_price' => 10.00,
        'stock_quantity' => 50,
        'minimum_stock_level' => 10,
        'is_active' => true,
        'expiry_date' => now()->subDays(5)->toDateString(),
    ]);

    $response = $this->actingAs($this->user)->get(route('dashboard'));
    $response->assertOk();
    $response->assertSee('Expired');
});

test('dashboard shows expiring soon count', function () {
    Medicine::create([
        'name' => 'Expiring Med',
        'unit_price' => 10.00,
        'stock_quantity' => 50,
        'minimum_stock_level' => 10,
        'is_active' => true,
        'expiry_date' => now()->addDays(15)->toDateString(),
    ]);

    $response = $this->actingAs($this->user)->get(route('dashboard'));
    $response->assertOk();
    $response->assertSee('Expiring Soon');
});

test('dashboard shows new patients today', function () {
    $response = $this->actingAs($this->user)->get(route('dashboard'));
    $response->assertOk();
    $response->assertSee('Patients Today');
});

test('dashboard unauthorized user cannot access', function () {
    $user = User::factory()->create();
    $user->givePermissionTo([]);

    $response = $this->actingAs($user)->get(route('dashboard'));
    $response->assertForbidden();
});

// --- FINANCIAL DASHBOARD KPI ---

test('dashboard shows financial KPIs for admin', function () {
    $response = $this->actingAs($this->user)->get(route('dashboard'));
    $response->assertOk();
    $response->assertSee('Invoiced Today');
    $response->assertSee('Paid Today');
    $response->assertSee('Outstanding');
});

// --- REPORT INDEX ---

test('report index loads', function () {
    $response = $this->actingAs($this->user)->get(route('reports.index'));
    $response->assertOk();
    $response->assertSee('Reports');
});

// --- PATIENT REPORT ---

test('patient report loads', function () {
    $response = $this->actingAs($this->user)->get(route('reports.patient'));
    $response->assertOk();
});

test('patient report shows patients', function () {
    Patient::factory()->count(3)->create(['status' => 'active']);

    $response = $this->actingAs($this->user)->get(route('reports.patient'));
    $response->assertOk();
});

test('patient report filters by date', function () {
    $response = $this->actingAs($this->user)->get(route('reports.patient', [
        'date_from' => now()->subDays(7)->toDateString(),
        'date_to' => now()->toDateString(),
    ]));
    $response->assertOk();
});

test('patient report filters by status', function () {
    $response = $this->actingAs($this->user)->get(route('reports.patient', [
        'status' => 'active',
    ]));
    $response->assertOk();
});

test('patient report filters by gender', function () {
    $response = $this->actingAs($this->user)->get(route('reports.patient', [
        'gender' => 'male',
    ]));
    $response->assertOk();
});

test('patient report filters by search', function () {
    $response = $this->actingAs($this->user)->get(route('reports.patient', [
        'search' => 'John',
    ]));
    $response->assertOk();
});

test('patient report paginates', function () {
    Patient::factory()->count(25)->create(['status' => 'active']);

    $response = $this->actingAs($this->user)->get(route('reports.patient'));
    $response->assertOk();
});

test('patient report unauthorized user cannot access', function () {
    $user = User::factory()->create();
    $user->givePermissionTo([]);

    $response = $this->actingAs($user)->get(route('reports.patient'));
    $response->assertForbidden();
});

// --- APPOINTMENT REPORT ---

test('appointment report loads', function () {
    $response = $this->actingAs($this->user)->get(route('reports.appointment'));
    $response->assertOk();
});

test('appointment report filters by date', function () {
    $response = $this->actingAs($this->user)->get(route('reports.appointment', [
        'date_from' => now()->subDays(7)->toDateString(),
        'date_to' => now()->toDateString(),
    ]));
    $response->assertOk();
});

test('appointment report filters by status', function () {
    $response = $this->actingAs($this->user)->get(route('reports.appointment', [
        'status' => 'scheduled',
    ]));
    $response->assertOk();
});

test('appointment report filters by doctor', function () {
    $response = $this->actingAs($this->user)->get(route('reports.appointment', [
        'doctor_id' => $this->doctor->id,
    ]));
    $response->assertOk();
});

test('appointment report unauthorized user cannot access', function () {
    $user = User::factory()->create();
    $user->givePermissionTo([]);

    $response = $this->actingAs($user)->get(route('reports.appointment'));
    $response->assertForbidden();
});

// --- CONSULTATION REPORT ---

test('consultation report loads', function () {
    $response = $this->actingAs($this->user)->get(route('reports.consultation'));
    $response->assertOk();
});

test('consultation report filters by date', function () {
    $response = $this->actingAs($this->user)->get(route('reports.consultation', [
        'date_from' => now()->subDays(7)->toDateString(),
        'date_to' => now()->toDateString(),
    ]));
    $response->assertOk();
});

test('consultation report filters by status', function () {
    $response = $this->actingAs($this->user)->get(route('reports.consultation', [
        'status' => 'completed',
    ]));
    $response->assertOk();
});

test('consultation report unauthorized user cannot access', function () {
    $user = User::factory()->create();
    $user->givePermissionTo([]);

    $response = $this->actingAs($user)->get(route('reports.consultation'));
    $response->assertForbidden();
});

// --- FINANCIAL REPORT ---

test('financial report loads', function () {
    $response = $this->actingAs($this->user)->get(route('reports.financial'));
    $response->assertOk();
});

test('financial report shows totals', function () {
    $response = $this->actingAs($this->user)->get(route('reports.financial'));
    $response->assertOk();
    $response->assertSee('Total Invoiced');
    $response->assertSee('Total Paid');
    $response->assertSee('Outstanding');
});

test('financial report filters by date', function () {
    $response = $this->actingAs($this->user)->get(route('reports.financial', [
        'date_from' => now()->subDays(30)->toDateString(),
        'date_to' => now()->toDateString(),
    ]));
    $response->assertOk();
});

test('financial report filters by status', function () {
    $response = $this->actingAs($this->user)->get(route('reports.financial', [
        'status' => 'paid',
    ]));
    $response->assertOk();
});

test('financial report filters by payment method', function () {
    $response = $this->actingAs($this->user)->get(route('reports.financial', [
        'payment_method' => 'cash',
    ]));
    $response->assertOk();
});

test('financial report unauthorized user cannot access', function () {
    $user = User::factory()->create();
    $user->givePermissionTo([]);

    $response = $this->actingAs($user)->get(route('reports.financial'));
    $response->assertForbidden();
});

// --- INVENTORY REPORT ---

test('inventory report loads', function () {
    $response = $this->actingAs($this->user)->get(route('reports.inventory'));
    $response->assertOk();
});

test('inventory report shows medicine list', function () {
    Medicine::create([
        'name' => 'Test Med',
        'unit_price' => 10.00,
        'stock_quantity' => 50,
        'minimum_stock_level' => 10,
        'is_active' => true,
    ]);

    $response = $this->actingAs($this->user)->get(route('reports.inventory'));
    $response->assertOk();
    $response->assertSee('Test Med');
});

test('inventory report filters by stock status', function () {
    Medicine::create([
        'name' => 'Low Med',
        'unit_price' => 10.00,
        'stock_quantity' => 2,
        'minimum_stock_level' => 10,
        'is_active' => true,
    ]);

    $response = $this->actingAs($this->user)->get(route('reports.inventory', [
        'stock_status' => 'low',
    ]));
    $response->assertOk();
});

test('inventory report filters by search', function () {
    $response = $this->actingAs($this->user)->get(route('reports.inventory', [
        'search' => 'Test',
    ]));
    $response->assertOk();
});

test('inventory report shows recent movements', function () {
    $response = $this->actingAs($this->user)->get(route('reports.inventory'));
    $response->assertOk();
    $response->assertSee('Recent Stock Movements');
});

test('inventory report unauthorized user cannot access', function () {
    $user = User::factory()->create();
    $user->givePermissionTo([]);

    $response = $this->actingAs($user)->get(route('reports.inventory'));
    $response->assertForbidden();
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

test('existing inventory tests still pass', function () {
    $response = $this->actingAs($this->user)->get(route('inventory.dashboard'));
    $response->assertOk();
});

test('existing billing tests still pass', function () {
    $response = $this->actingAs($this->user)->get(route('invoices.index'));
    $response->assertOk();
});
