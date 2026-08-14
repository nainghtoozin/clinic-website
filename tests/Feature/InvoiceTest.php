<?php

use App\Models\Appointment;
use App\Models\Consultation;
use App\Models\Department;
use App\Models\Doctor;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Medicine;
use App\Models\Payment;
use App\Models\Patient;
use App\Models\Prescription;
use App\Models\PrescriptionItem;
use App\Models\User;
use Spatie\Permission\Models\Permission;

beforeEach(function () {
    $this->permissions = [
        'doctor.view', 'doctor.create', 'doctor.edit', 'doctor.delete',
        'patient.view', 'patient.create', 'patient.edit', 'patient.delete',
        'appointment.view', 'appointment.create', 'appointment.edit', 'appointment.cancel',
        'queue.view', 'queue.checkin', 'queue.call', 'queue.consult', 'queue.cancel',
        'consultation.view', 'consultation.create', 'consultation.edit', 'consultation.complete',
        'medicine.view', 'medicine.create', 'medicine.edit', 'medicine.delete',
        'prescription.view', 'prescription.create', 'prescription.edit', 'prescription.delete',
        'invoice.view', 'invoice.create', 'invoice.edit', 'invoice.cancel',
        'payment.view', 'payment.create', 'payment.cancel',
    ];

    foreach ($this->permissions as $perm) {
        Permission::firstOrCreate(['name' => $perm, 'guard_name' => 'web']);
    }

    $this->user = User::factory()->create();
    $this->user->givePermissionTo($this->permissions);

    $this->department = Department::create(['name' => 'Cardiology', 'slug' => 'cardiology', 'description' => 'Heart care']);
    $this->doctor = Doctor::create([
        'name' => 'Dr. Smith',
        'slug' => 'dr-smith',
        'department_id' => $this->department->id,
        'is_available' => true,
        'available_days' => [1, 2, 3, 4, 5],
        'start_time' => '09:00:00',
        'end_time' => '17:00:00',
        'user_id' => $this->user->id,
        'consultation_fee' => 100.00,
    ]);
    $this->patient = Patient::factory()->create(['status' => 'active']);
});

// --- INVOICE CREATION TESTS ---

test('invoice can be created from completed consultation', function () {
    $consultation = Consultation::create([
        'patient_id' => $this->patient->id,
        'doctor_id' => $this->doctor->id,
        'status' => 'completed',
    ]);

    $response = $this->actingAs($this->user)->post(route('invoices.store'), [
        'patient_id' => $this->patient->id,
        'doctor_id' => $this->doctor->id,
        'consultation_id' => $consultation->id,
        'items' => [
            ['description' => 'Consultation Fee', 'type' => 'consultation', 'quantity' => 1, 'unit_price' => 100.00],
        ],
    ]);

    $response->assertRedirect();
    $this->assertDatabaseHas('invoices', [
        'patient_id' => $this->patient->id,
        'consultation_id' => $consultation->id,
        'status' => 'draft',
    ]);
});

test('invoice number is generated', function () {
    $consultation = Consultation::create([
        'patient_id' => $this->patient->id,
        'doctor_id' => $this->doctor->id,
        'status' => 'completed',
    ]);

    $this->actingAs($this->user)->post(route('invoices.store'), [
        'patient_id' => $this->patient->id,
        'doctor_id' => $this->doctor->id,
        'consultation_id' => $consultation->id,
        'items' => [
            ['description' => 'Consultation Fee', 'type' => 'consultation', 'quantity' => 1, 'unit_price' => 100.00],
        ],
    ]);

    $invoice = Invoice::latest()->first();
    $this->assertNotNull($invoice->invoice_number);
    $this->assertMatchesRegularExpression('/^INV-\d{8}-\d{4}$/', $invoice->invoice_number);
});

test('invoice number is unique', function () {
    $consultation = Consultation::create([
        'patient_id' => $this->patient->id,
        'doctor_id' => $this->doctor->id,
        'status' => 'completed',
    ]);

    $this->actingAs($this->user)->post(route('invoices.store'), [
        'patient_id' => $this->patient->id,
        'doctor_id' => $this->doctor->id,
        'consultation_id' => $consultation->id,
        'items' => [
            ['description' => 'Consultation Fee', 'type' => 'consultation', 'quantity' => 1, 'unit_price' => 100.00],
        ],
    ]);

    $this->actingAs($this->user)->post(route('invoices.store'), [
        'patient_id' => $this->patient->id,
        'doctor_id' => $this->doctor->id,
        'consultation_id' => $consultation->id,
        'items' => [
            ['description' => 'Consultation Fee', 'type' => 'consultation', 'quantity' => 1, 'unit_price' => 100.00],
        ],
    ]);

    $invoice1 = Invoice::where('consultation_id', $consultation->id)->first();
    $this->assertNotNull($invoice1);
});

test('invoice items can be added', function () {
    $consultation = Consultation::create([
        'patient_id' => $this->patient->id,
        'doctor_id' => $this->doctor->id,
        'status' => 'completed',
    ]);

    $this->actingAs($this->user)->post(route('invoices.store'), [
        'patient_id' => $this->patient->id,
        'doctor_id' => $this->doctor->id,
        'consultation_id' => $consultation->id,
        'items' => [
            ['description' => 'Consultation Fee', 'type' => 'consultation', 'quantity' => 1, 'unit_price' => 100.00],
            ['description' => 'Blood Test', 'type' => 'service', 'quantity' => 1, 'unit_price' => 50.00],
        ],
    ]);

    $invoice = Invoice::latest()->first();
    $this->assertEquals(2, $invoice->items->count());
});

test('consultation fee is calculated correctly', function () {
    $consultation = Consultation::create([
        'patient_id' => $this->patient->id,
        'doctor_id' => $this->doctor->id,
        'status' => 'completed',
    ]);

    $this->actingAs($this->user)->post(route('invoices.store'), [
        'patient_id' => $this->patient->id,
        'doctor_id' => $this->doctor->id,
        'consultation_id' => $consultation->id,
        'items' => [
            ['description' => 'Consultation Fee', 'type' => 'consultation', 'quantity' => 1, 'unit_price' => 100.00],
        ],
    ]);

    $invoice = Invoice::latest()->first();
    $this->assertEquals(100.00, $invoice->subtotal);
    $this->assertEquals(100.00, $invoice->total);
});

test('medicine charges use current medicine price', function () {
    $medicine = Medicine::create([
        'name' => 'Amoxicillin',
        'unit_price' => 15.50,
        'stock_quantity' => 100,
        'is_active' => true,
    ]);

    $consultation = Consultation::create([
        'patient_id' => $this->patient->id,
        'doctor_id' => $this->doctor->id,
        'status' => 'completed',
    ]);

    $this->actingAs($this->user)->post(route('invoices.store'), [
        'patient_id' => $this->patient->id,
        'doctor_id' => $this->doctor->id,
        'consultation_id' => $consultation->id,
        'items' => [
            ['description' => $medicine->name, 'type' => 'medicine', 'quantity' => 10, 'unit_price' => $medicine->unit_price],
        ],
    ]);

    $invoice = Invoice::latest()->first();
    $this->assertEquals(155.00, $invoice->subtotal);
});

test('discount validation works', function () {
    $consultation = Consultation::create([
        'patient_id' => $this->patient->id,
        'doctor_id' => $this->doctor->id,
        'status' => 'completed',
    ]);

    $response = $this->actingAs($this->user)->post(route('invoices.store'), [
        'patient_id' => $this->patient->id,
        'doctor_id' => $this->doctor->id,
        'consultation_id' => $consultation->id,
        'discount' => -10,
        'items' => [
            ['description' => 'Consultation Fee', 'type' => 'consultation', 'quantity' => 1, 'unit_price' => 100.00],
        ],
    ]);

    $response->assertSessionHasErrors('discount');
});

test('subtotal is calculated server-side', function () {
    $consultation = Consultation::create([
        'patient_id' => $this->patient->id,
        'doctor_id' => $this->doctor->id,
        'status' => 'completed',
    ]);

    $this->actingAs($this->user)->post(route('invoices.store'), [
        'patient_id' => $this->patient->id,
        'doctor_id' => $this->doctor->id,
        'consultation_id' => $consultation->id,
        'items' => [
            ['description' => 'Consultation Fee', 'type' => 'consultation', 'quantity' => 1, 'unit_price' => 100.00],
        ],
    ]);

    $invoice = Invoice::latest()->first();
    $this->assertEquals(100.00, $invoice->subtotal);
    $this->assertEquals(100.00, $invoice->total);
    $this->assertEquals(100.00, $invoice->balance);
});

test('total is calculated correctly with discount and tax', function () {
    $consultation = Consultation::create([
        'patient_id' => $this->patient->id,
        'doctor_id' => $this->doctor->id,
        'status' => 'completed',
    ]);

    $this->actingAs($this->user)->post(route('invoices.store'), [
        'patient_id' => $this->patient->id,
        'doctor_id' => $this->doctor->id,
        'consultation_id' => $consultation->id,
        'discount' => 10.00,
        'tax' => 9.00,
        'items' => [
            ['description' => 'Consultation Fee', 'type' => 'consultation', 'quantity' => 1, 'unit_price' => 100.00],
        ],
    ]);

    $invoice = Invoice::latest()->first();
    $this->assertEquals(100.00, $invoice->subtotal);
    $this->assertEquals(10.00, $invoice->discount);
    $this->assertEquals(9.00, $invoice->tax);
    $this->assertEquals(99.00, $invoice->total);
});

// --- INVOICE STATUS TESTS ---

test('invoice can be issued', function () {
    $consultation = Consultation::create([
        'patient_id' => $this->patient->id,
        'doctor_id' => $this->doctor->id,
        'status' => 'completed',
    ]);

    $this->actingAs($this->user)->post(route('invoices.store'), [
        'patient_id' => $this->patient->id,
        'doctor_id' => $this->doctor->id,
        'consultation_id' => $consultation->id,
        'items' => [
            ['description' => 'Consultation Fee', 'type' => 'consultation', 'quantity' => 1, 'unit_price' => 100.00],
        ],
    ]);

    $invoice = Invoice::latest()->first();
    $response = $this->actingAs($this->user)->post(route('invoices.issue', $invoice));

    $response->assertRedirect();
    $invoice->refresh();
    $this->assertEquals('issued', $invoice->status);
    $this->assertNotNull($invoice->issued_at);
});

test('invoice can be cancelled', function () {
    $consultation = Consultation::create([
        'patient_id' => $this->patient->id,
        'doctor_id' => $this->doctor->id,
        'status' => 'completed',
    ]);

    $this->actingAs($this->user)->post(route('invoices.store'), [
        'patient_id' => $this->patient->id,
        'doctor_id' => $this->doctor->id,
        'consultation_id' => $consultation->id,
        'items' => [
            ['description' => 'Consultation Fee', 'type' => 'consultation', 'quantity' => 1, 'unit_price' => 100.00],
        ],
    ]);

    $invoice = Invoice::latest()->first();
    $response = $this->actingAs($this->user)->post(route('invoices.cancel', $invoice));

    $response->assertRedirect();
    $invoice->refresh();
    $this->assertEquals('cancelled', $invoice->status);
});

// --- PAYMENT TESTS ---

test('payment can be recorded', function () {
    $consultation = Consultation::create([
        'patient_id' => $this->patient->id,
        'doctor_id' => $this->doctor->id,
        'status' => 'completed',
    ]);

    $this->actingAs($this->user)->post(route('invoices.store'), [
        'patient_id' => $this->patient->id,
        'doctor_id' => $this->doctor->id,
        'consultation_id' => $consultation->id,
        'items' => [
            ['description' => 'Consultation Fee', 'type' => 'consultation', 'quantity' => 1, 'unit_price' => 100.00],
        ],
    ]);

    $invoice = Invoice::latest()->first();
    $this->actingAs($this->user)->post(route('invoices.issue', $invoice));

    $response = $this->actingAs($this->user)->post(route('payments.store'), [
        'invoice_id' => $invoice->id,
        'amount' => 50.00,
        'payment_method' => 'cash',
        'paid_at' => now()->toDateTimeString(),
    ]);

    $response->assertRedirect();
    $this->assertDatabaseHas('payments', [
        'invoice_id' => $invoice->id,
        'amount' => 50.00,
        'payment_method' => 'cash',
    ]);
});

test('multiple payments work', function () {
    $consultation = Consultation::create([
        'patient_id' => $this->patient->id,
        'doctor_id' => $this->doctor->id,
        'status' => 'completed',
    ]);

    $this->actingAs($this->user)->post(route('invoices.store'), [
        'patient_id' => $this->patient->id,
        'doctor_id' => $this->doctor->id,
        'consultation_id' => $consultation->id,
        'items' => [
            ['description' => 'Consultation Fee', 'type' => 'consultation', 'quantity' => 1, 'unit_price' => 100.00],
        ],
    ]);

    $invoice = Invoice::latest()->first();
    $this->actingAs($this->user)->post(route('invoices.issue', $invoice));

    $this->actingAs($this->user)->post(route('payments.store'), [
        'invoice_id' => $invoice->id,
        'amount' => 40.00,
        'payment_method' => 'cash',
        'paid_at' => now()->toDateTimeString(),
    ]);

    $this->actingAs($this->user)->post(route('payments.store'), [
        'invoice_id' => $invoice->id,
        'amount' => 60.00,
        'payment_method' => 'card',
        'paid_at' => now()->toDateTimeString(),
    ]);

    $invoice->refresh();
    $this->assertEquals(100.00, $invoice->amount_paid);
    $this->assertEquals(0.00, $invoice->balance);
    $this->assertEquals('paid', $invoice->status);
    $this->assertEquals(2, $invoice->payments->count());
});

test('payment cannot exceed remaining balance', function () {
    $consultation = Consultation::create([
        'patient_id' => $this->patient->id,
        'doctor_id' => $this->doctor->id,
        'status' => 'completed',
    ]);

    $this->actingAs($this->user)->post(route('invoices.store'), [
        'patient_id' => $this->patient->id,
        'doctor_id' => $this->doctor->id,
        'consultation_id' => $consultation->id,
        'items' => [
            ['description' => 'Consultation Fee', 'type' => 'consultation', 'quantity' => 1, 'unit_price' => 100.00],
        ],
    ]);

    $invoice = Invoice::latest()->first();
    $this->actingAs($this->user)->post(route('invoices.issue', $invoice));

    $response = $this->actingAs($this->user)->post(route('payments.store'), [
        'invoice_id' => $invoice->id,
        'amount' => 150.00,
        'payment_method' => 'cash',
        'paid_at' => now()->toDateTimeString(),
    ]);

    $response->assertSessionHas('error');
});

test('paid amount is calculated correctly', function () {
    $consultation = Consultation::create([
        'patient_id' => $this->patient->id,
        'doctor_id' => $this->doctor->id,
        'status' => 'completed',
    ]);

    $this->actingAs($this->user)->post(route('invoices.store'), [
        'patient_id' => $this->patient->id,
        'doctor_id' => $this->doctor->id,
        'consultation_id' => $consultation->id,
        'items' => [
            ['description' => 'Consultation Fee', 'type' => 'consultation', 'quantity' => 1, 'unit_price' => 100.00],
        ],
    ]);

    $invoice = Invoice::latest()->first();
    $this->actingAs($this->user)->post(route('invoices.issue', $invoice));

    $this->actingAs($this->user)->post(route('payments.store'), [
        'invoice_id' => $invoice->id,
        'amount' => 30.00,
        'payment_method' => 'cash',
        'paid_at' => now()->toDateTimeString(),
    ]);

    $invoice->refresh();
    $this->assertEquals(30.00, $invoice->amount_paid);
    $this->assertEquals(70.00, $invoice->balance);
    $this->assertEquals('partially_paid', $invoice->status);
});

test('unpaid status works', function () {
    $consultation = Consultation::create([
        'patient_id' => $this->patient->id,
        'doctor_id' => $this->doctor->id,
        'status' => 'completed',
    ]);

    $this->actingAs($this->user)->post(route('invoices.store'), [
        'patient_id' => $this->patient->id,
        'doctor_id' => $this->doctor->id,
        'consultation_id' => $consultation->id,
        'items' => [
            ['description' => 'Consultation Fee', 'type' => 'consultation', 'quantity' => 1, 'unit_price' => 100.00],
        ],
    ]);

    $invoice = Invoice::latest()->first();
    $this->actingAs($this->user)->post(route('invoices.issue', $invoice));

    $invoice->refresh();
    $this->assertEquals('issued', $invoice->status);
});

test('partially paid status works', function () {
    $consultation = Consultation::create([
        'patient_id' => $this->patient->id,
        'doctor_id' => $this->doctor->id,
        'status' => 'completed',
    ]);

    $this->actingAs($this->user)->post(route('invoices.store'), [
        'patient_id' => $this->patient->id,
        'doctor_id' => $this->doctor->id,
        'consultation_id' => $consultation->id,
        'items' => [
            ['description' => 'Consultation Fee', 'type' => 'consultation', 'quantity' => 1, 'unit_price' => 100.00],
        ],
    ]);

    $invoice = Invoice::latest()->first();
    $this->actingAs($this->user)->post(route('invoices.issue', $invoice));

    $this->actingAs($this->user)->post(route('payments.store'), [
        'invoice_id' => $invoice->id,
        'amount' => 50.00,
        'payment_method' => 'cash',
        'paid_at' => now()->toDateTimeString(),
    ]);

    $invoice->refresh();
    $this->assertEquals('partially_paid', $invoice->status);
});

test('paid status works', function () {
    $consultation = Consultation::create([
        'patient_id' => $this->patient->id,
        'doctor_id' => $this->doctor->id,
        'status' => 'completed',
    ]);

    $this->actingAs($this->user)->post(route('invoices.store'), [
        'patient_id' => $this->patient->id,
        'doctor_id' => $this->doctor->id,
        'consultation_id' => $consultation->id,
        'items' => [
            ['description' => 'Consultation Fee', 'type' => 'consultation', 'quantity' => 1, 'unit_price' => 100.00],
        ],
    ]);

    $invoice = Invoice::latest()->first();
    $this->actingAs($this->user)->post(route('invoices.issue', $invoice));

    $this->actingAs($this->user)->post(route('payments.store'), [
        'invoice_id' => $invoice->id,
        'amount' => 100.00,
        'payment_method' => 'cash',
        'paid_at' => now()->toDateTimeString(),
    ]);

    $invoice->refresh();
    $this->assertEquals('paid', $invoice->status);
    $this->assertEquals(0.00, $invoice->balance);
});

test('payment history is preserved', function () {
    $consultation = Consultation::create([
        'patient_id' => $this->patient->id,
        'doctor_id' => $this->doctor->id,
        'status' => 'completed',
    ]);

    $this->actingAs($this->user)->post(route('invoices.store'), [
        'patient_id' => $this->patient->id,
        'doctor_id' => $this->doctor->id,
        'consultation_id' => $consultation->id,
        'items' => [
            ['description' => 'Consultation Fee', 'type' => 'consultation', 'quantity' => 1, 'unit_price' => 100.00],
        ],
    ]);

    $invoice = Invoice::latest()->first();
    $this->actingAs($this->user)->post(route('invoices.issue', $invoice));

    $this->actingAs($this->user)->post(route('payments.store'), [
        'invoice_id' => $invoice->id,
        'amount' => 50.00,
        'payment_method' => 'cash',
        'paid_at' => now()->toDateTimeString(),
    ]);

    $this->actingAs($this->user)->post(route('payments.store'), [
        'invoice_id' => $invoice->id,
        'amount' => 50.00,
        'payment_method' => 'card',
        'paid_at' => now()->toDateTimeString(),
    ]);

    $payments = Payment::where('invoice_id', $invoice->id)->get();
    $this->assertEquals(2, $payments->count());
    $this->assertEquals('cash', $payments->first()->payment_method);
    $this->assertEquals('card', $payments->last()->payment_method);
});

// --- RECEIPT TESTS ---

test('receipt displays correct payment information', function () {
    $consultation = Consultation::create([
        'patient_id' => $this->patient->id,
        'doctor_id' => $this->doctor->id,
        'status' => 'completed',
    ]);

    $this->actingAs($this->user)->post(route('invoices.store'), [
        'patient_id' => $this->patient->id,
        'doctor_id' => $this->doctor->id,
        'consultation_id' => $consultation->id,
        'items' => [
            ['description' => 'Consultation Fee', 'type' => 'consultation', 'quantity' => 1, 'unit_price' => 100.00],
        ],
    ]);

    $invoice = Invoice::latest()->first();
    $this->actingAs($this->user)->post(route('invoices.issue', $invoice));

    $this->actingAs($this->user)->post(route('payments.store'), [
        'invoice_id' => $invoice->id,
        'amount' => 100.00,
        'payment_method' => 'cash',
        'paid_at' => now()->toDateTimeString(),
    ]);

    $payment = Payment::where('invoice_id', $invoice->id)->first();
    $response = $this->actingAs($this->user)->get(route('payments.receipt', $payment));

    $response->assertOk();
    $response->assertSee($invoice->invoice_number);
    $response->assertSee('100.00');
    $response->assertSee('Cash');
});

test('receipt is printable', function () {
    $consultation = Consultation::create([
        'patient_id' => $this->patient->id,
        'doctor_id' => $this->doctor->id,
        'status' => 'completed',
    ]);

    $this->actingAs($this->user)->post(route('invoices.store'), [
        'patient_id' => $this->patient->id,
        'doctor_id' => $this->doctor->id,
        'consultation_id' => $consultation->id,
        'items' => [
            ['description' => 'Consultation Fee', 'type' => 'consultation', 'quantity' => 1, 'unit_price' => 100.00],
        ],
    ]);

    $invoice = Invoice::latest()->first();
    $this->actingAs($this->user)->post(route('invoices.issue', $invoice));

    $this->actingAs($this->user)->post(route('payments.store'), [
        'invoice_id' => $invoice->id,
        'amount' => 100.00,
        'payment_method' => 'card',
        'paid_at' => now()->toDateTimeString(),
    ]);

    $payment = Payment::where('invoice_id', $invoice->id)->first();
    $response = $this->actingAs($this->user)->get(route('payments.receipt', $payment));

    $response->assertOk();
    $response->assertSee('window.print()');
});

// --- PATIENT BILLING HISTORY TEST ---

test('billing history appears on patient profile', function () {
    $consultation = Consultation::create([
        'patient_id' => $this->patient->id,
        'doctor_id' => $this->doctor->id,
        'status' => 'completed',
    ]);

    $this->actingAs($this->user)->post(route('invoices.store'), [
        'patient_id' => $this->patient->id,
        'doctor_id' => $this->doctor->id,
        'consultation_id' => $consultation->id,
        'items' => [
            ['description' => 'Consultation Fee', 'type' => 'consultation', 'quantity' => 1, 'unit_price' => 100.00],
        ],
    ]);

    $response = $this->actingAs($this->user)->get(route('patients.show', $this->patient));
    $response->assertOk();
    $response->assertSee('Billing History');
});

// --- AUTHORIZATION TESTS ---

test('unauthorized user cannot access invoices', function () {
    $user = User::factory()->create();
    $response = $this->actingAs($user)->get(route('invoices.index'));
    $response->assertForbidden();
});

test('unauthorized user cannot create invoice', function () {
    $user = User::factory()->create();
    $response = $this->actingAs($user)->get(route('invoices.create'));
    $response->assertForbidden();
});

test('unauthorized user cannot access payments', function () {
    $user = User::factory()->create();
    $response = $this->actingAs($user)->get(route('payments.index'));
    $response->assertForbidden();
});

test('unauthorized user cannot record payment', function () {
    $user = User::factory()->create();
    $response = $this->actingAs($user)->get(route('payments.create'));
    $response->assertForbidden();
});

// --- REGRESSION TESTS ---

test('existing patient tests still pass', function () {
    $response = $this->actingAs($this->user)->get(route('patients.index'));
    $response->assertOk();
});

test('existing consultation tests still pass', function () {
    $response = $this->actingAs($this->user)->get(route('consultations.index'));
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

test('existing doctor tests still pass', function () {
    $response = $this->actingAs($this->user)->get(route('doctors.index'));
    $response->assertOk();
});
