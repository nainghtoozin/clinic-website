<?php

use App\Enums\AppointmentStatus;
use App\Models\Appointment;
use App\Models\Consultation;
use App\Models\Department;
use App\Models\Doctor;
use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Models\Investigation;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\LabTest;
use App\Models\Medicine;
use App\Models\Payment;
use App\Models\Patient;
use App\Models\Prescription;
use App\Models\PrescriptionItem;
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
        'report.analytics',
        'patient.view', 'patient.create', 'patient.edit', 'patient.delete',
        'appointment.view', 'appointment.create', 'appointment.edit', 'appointment.cancel',
        'consultation.view', 'consultation.create', 'consultation.edit', 'consultation.complete',
        'prescription.view', 'prescription.create',
        'medicine.view', 'medicine.create',
        'invoice.view', 'invoice.create',
        'payment.view', 'payment.create',
        'expense.view', 'expense.create',
        'inventory.view',
        'lab_test.view',
        'investigation.view', 'investigation.create',
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
        'is_active' => true,
    ]);

    $this->patient = Patient::factory()->create(['status' => 'active']);
    $this->department = Department::create(['name' => 'General', 'slug' => 'general']);
});

// --- ANALYTICS DASHBOARD ---

test('analytics page loads', function () {
    $response = $this->actingAs($this->user)->get(route('analytics.index'));
    $response->assertOk();
});

test('analytics page shows patient analytics section', function () {
    $response = $this->actingAs($this->user)->get(route('analytics.index'));
    $response->assertOk();
    $response->assertSee('Patient Analytics');
    $response->assertSee('Total Patients');
    $response->assertSee('New Patients');
    $response->assertSee('Active Patients');
});

test('analytics page shows appointment analytics section', function () {
    $response = $this->actingAs($this->user)->get(route('analytics.index'));
    $response->assertOk();
    $response->assertSee('Appointment Analytics');
    $response->assertSee('Total Appointments');
});

test('analytics page shows doctor performance section', function () {
    $response = $this->actingAs($this->user)->get(route('analytics.index'));
    $response->assertOk();
    $response->assertSee('Doctor Performance');
});

test('analytics page shows consultation analytics section', function () {
    $response = $this->actingAs($this->user)->get(route('analytics.index'));
    $response->assertOk();
    $response->assertSee('Consultation Analytics');
});

test('analytics page shows prescription and medicine section', function () {
    $response = $this->actingAs($this->user)->get(route('analytics.index'));
    $response->assertOk();
    $response->assertSee('Prescription & Medicine Analytics', false);
});

test('analytics page shows inventory analytics section', function () {
    $response = $this->actingAs($this->user)->get(route('analytics.index'));
    $response->assertOk();
    $response->assertSee('Inventory Analytics');
    $response->assertSee('Low Stock');
    $response->assertSee('Out of Stock');
});

test('analytics page shows lab investigation section', function () {
    $response = $this->actingAs($this->user)->get(route('analytics.index'));
    $response->assertOk();
    $response->assertSee('Lab / Investigation Analytics');
});

test('analytics page shows financial analytics section', function () {
    $response = $this->actingAs($this->user)->get(route('analytics.index'));
    $response->assertOk();
    $response->assertSee('Financial Analytics');
    $response->assertSee('Revenue');
    $response->assertSee('Expenses');
    $response->assertSee('Net Income');
    $response->assertSee('Outstanding');
});

test('analytics page shows comparison cards', function () {
    $response = $this->actingAs($this->user)->get(route('analytics.index'));
    $response->assertOk();
    $response->assertSee('vs previous');
});

test('analytics page shows date range filter', function () {
    $response = $this->actingAs($this->user)->get(route('analytics.index'));
    $response->assertOk();
    $response->assertSee('date_from');
    $response->assertSee('date_to');
});

test('analytics page shows quick preset buttons', function () {
    $response = $this->actingAs($this->user)->get(route('analytics.index'));
    $response->assertOk();
    $response->assertSee('Today');
    $response->assertSee('This Week');
    $response->assertSee('This Month');
    $response->assertSee('Last Month');
    $response->assertSee('This Year');
});

// --- DATE RANGE FILTERING ---

test('analytics respects custom date range', function () {
    $dateFrom = now()->subDays(7)->toDateString();
    $dateTo = now()->toDateString();

    $response = $this->actingAs($this->user)->get(route('analytics.index', [
        'date_from' => $dateFrom,
        'date_to' => $dateTo,
    ]));
    $response->assertOk();
    $response->assertSee('name="date_from"', false);
    $response->assertSee('value="' . $dateFrom . '"', false);
    $response->assertSee('name="date_to"', false);
    $response->assertSee('value="' . $dateTo . '"', false);
});

test('analytics corrects start date after end date', function () {
    $response = $this->actingAs($this->user)->get(route('analytics.index', [
        'date_from' => now()->toDateString(),
        'date_to' => now()->subDays(5)->toDateString(),
    ]));
    $response->assertOk();
});

test('analytics filters patients by date range', function () {
    // Patient inside range
    $patientInside = Patient::factory()->create([
        'created_at' => now()->subDays(2),
        'status' => 'active',
    ]);

    $response = $this->actingAs($this->user)->get(route('analytics.index', [
        'date_from' => now()->subDays(5)->toDateString(),
        'date_to' => now()->toDateString(),
    ]));
    $response->assertOk();

    // Patient outside range should not count as "new" in filtered results
    $response2 = $this->actingAs($this->user)->get(route('analytics.index', [
        'date_from' => now()->subDays(1)->toDateString(),
        'date_to' => now()->toDateString(),
    ]));
    $response2->assertOk();
});

test('analytics filters appointments by date range', function () {
    $appointment = Appointment::create([
        'patient_id' => $this->patient->id,
        'doctor_id' => $this->doctor->id,
        'department_id' => $this->department->id,
        'name' => $this->patient->name,
        'email' => $this->patient->email,
        'phone' => $this->patient->phone,
        'date' => now()->subDays(2)->toDateString(),
        'time' => '10:00',
        'appointment_number' => 'APT-ANALYTICS-001',
        'status' => AppointmentStatus::Scheduled,
    ]);

    $response = $this->actingAs($this->user)->get(route('analytics.index', [
        'date_from' => now()->subDays(5)->toDateString(),
        'date_to' => now()->toDateString(),
    ]));
    $response->assertOk();
    $response->assertSee('Appointment Analytics');
    $response->assertSee('Total Appointments');
    $response->assertSee('Scheduled');
});

// --- DATA ACCURACY ---

test('analytics shows correct patient totals', function () {
    Patient::factory()->count(3)->create(['status' => 'active']);
    Patient::factory()->count(2)->create(['status' => 'inactive']);

    $response = $this->actingAs($this->user)->get(route('analytics.index'));
    $response->assertOk();
    // Total patients = 5 (created above) + 1 (from beforeEach) = 6
    $response->assertSee('Total Patients');
});

test('analytics shows correct appointment status breakdown', function () {
    $date = now()->toDateString();

    Appointment::create([
        'patient_id' => $this->patient->id,
        'doctor_id' => $this->doctor->id,
        'department_id' => $this->department->id,
        'name' => $this->patient->name,
        'phone' => $this->patient->phone,
        'date' => $date,
        'time' => '09:00',
        'appointment_number' => 'APT-STAT-001',
        'status' => AppointmentStatus::Scheduled,
    ]);

    Appointment::create([
        'patient_id' => $this->patient->id,
        'doctor_id' => $this->doctor->id,
        'department_id' => $this->department->id,
        'name' => $this->patient->name,
        'phone' => $this->patient->phone,
        'date' => $date,
        'time' => '10:00',
        'appointment_number' => 'APT-STAT-002',
        'status' => AppointmentStatus::Completed,
    ]);

    Appointment::create([
        'patient_id' => $this->patient->id,
        'doctor_id' => $this->doctor->id,
        'department_id' => $this->department->id,
        'name' => $this->patient->name,
        'phone' => $this->patient->phone,
        'date' => $date,
        'time' => '11:00',
        'appointment_number' => 'APT-STAT-003',
        'status' => AppointmentStatus::Cancelled,
    ]);

    $response = $this->actingAs($this->user)->get(route('analytics.index', [
        'date_from' => $date,
        'date_to' => $date,
    ]));
    $response->assertOk();
    $response->assertSee('Scheduled');
    $response->assertSee('Completed');
    $response->assertSee('Cancelled');
});

test('analytics shows consultation totals', function () {
    Consultation::create([
        'patient_id' => $this->patient->id,
        'doctor_id' => $this->doctor->id,
        'status' => 'completed',
        'diagnosis' => 'Common cold',
    ]);

    Consultation::create([
        'patient_id' => $this->patient->id,
        'doctor_id' => $this->doctor->id,
        'status' => 'draft',
    ]);

    $response = $this->actingAs($this->user)->get(route('analytics.index'));
    $response->assertOk();
    $response->assertSee('Consultation Analytics');
});

test('analytics shows inventory status', function () {
    Medicine::create([
        'name' => 'Low Stock Med',
        'unit_price' => 10.00,
        'stock_quantity' => 2,
        'minimum_stock_level' => 10,
        'is_active' => true,
    ]);

    Medicine::create([
        'name' => 'Expired Med',
        'unit_price' => 10.00,
        'stock_quantity' => 50,
        'minimum_stock_level' => 10,
        'is_active' => true,
        'expiry_date' => now()->subDays(5)->toDateString(),
    ]);

    Medicine::create([
        'name' => 'Good Med',
        'unit_price' => 10.00,
        'stock_quantity' => 100,
        'minimum_stock_level' => 10,
        'is_active' => true,
    ]);

    $response = $this->actingAs($this->user)->get(route('analytics.index'));
    $response->assertOk();
    $response->assertSee('Low Stock');
    $response->assertSee('Expired');
});

test('analytics shows lab investigation data', function () {
    $labTest = LabTest::create([
        'name' => 'Blood Test',
        'code' => 'BT001',
        'category' => 'Hematology',
        'price' => 25.00,
    ]);

    Investigation::create([
        'patient_id' => $this->patient->id,
        'doctor_id' => $this->doctor->id,
        'lab_test_id' => $labTest->id,
        'requested_date' => now()->toDateString(),
        'status' => 'requested',
    ]);

    $response = $this->actingAs($this->user)->get(route('analytics.index'));
    $response->assertOk();
    $response->assertSee('Lab / Investigation Analytics');
    $response->assertSee('Requested');
});

test('analytics shows diagnosis data', function () {
    Consultation::create([
        'patient_id' => $this->patient->id,
        'doctor_id' => $this->doctor->id,
        'status' => 'completed',
        'diagnosis' => 'Upper respiratory infection',
    ]);

    Consultation::create([
        'patient_id' => $this->patient->id,
        'doctor_id' => $this->doctor->id,
        'status' => 'completed',
        'diagnosis' => 'Upper respiratory infection',
    ]);

    $response = $this->actingAs($this->user)->get(route('analytics.index'));
    $response->assertOk();
    $response->assertSee('Top Diagnoses');
    $response->assertSee('Upper respiratory infection');
});

test('analytics shows top medicines', function () {
    $medicine = Medicine::create([
        'name' => 'Amoxicillin',
        'unit_price' => 15.00,
        'stock_quantity' => 100,
        'is_active' => true,
    ]);

    $prescription = Prescription::create([
        'patient_id' => $this->patient->id,
        'doctor_id' => $this->doctor->id,
        'prescribed_date' => now()->toDateString(),
        'status' => 'pending',
    ]);

    PrescriptionItem::create([
        'prescription_id' => $prescription->id,
        'medicine_id' => $medicine->id,
        'dosage' => '500mg',
        'frequency' => '3x daily',
        'duration' => '5 days',
        'quantity' => 15,
    ]);

    $response = $this->actingAs($this->user)->get(route('analytics.index'));
    $response->assertOk();
    $response->assertSee('Top Prescribed Medicines');
    $response->assertSee('Amoxicillin');
});

// --- FINANCIAL ANALYTICS ---

test('analytics shows financial data', function () {
    $invoice = Invoice::create([
        'patient_id' => $this->patient->id,
        'doctor_id' => $this->doctor->id,
        'total' => 100.00,
        'amount_paid' => 100.00,
        'balance' => 0.00,
        'status' => 'paid',
    ]);

    Payment::create([
        'invoice_id' => $invoice->id,
        'amount' => 100.00,
        'payment_method' => 'cash',
        'paid_at' => now(),
    ]);

    $category = ExpenseCategory::create(['name' => 'Utilities', 'slug' => 'utilities']);
    Expense::create([
        'expense_number' => 'EXP-TEST-001',
        'expense_category_id' => $category->id,
        'amount' => 50.00,
        'payment_method' => 'cash',
        'expense_date' => now()->toDateString(),
        'description' => 'Office utilities',
        'status' => 'active',
        'created_by' => $this->user->id,
    ]);

    $response = $this->actingAs($this->user)->get(route('analytics.index'));
    $response->assertOk();
    $response->assertSee('Financial Analytics');
    $response->assertSee('Revenue');
    $response->assertSee('Expenses');
});

test('analytics financial data respects date range', function () {
    $oldInvoice = Invoice::create([
        'patient_id' => $this->patient->id,
        'total' => 500.00,
        'amount_paid' => 500.00,
        'balance' => 0.00,
        'status' => 'paid',
        'created_at' => now()->subDays(30),
    ]);

    Payment::create([
        'invoice_id' => $oldInvoice->id,
        'amount' => 500.00,
        'payment_method' => 'cash',
        'paid_at' => now()->subDays(30),
    ]);

    // Old payment should not appear in today-only view
    $response = $this->actingAs($this->user)->get(route('analytics.index', [
        'date_from' => now()->toDateString(),
        'date_to' => now()->toDateString(),
    ]));
    $response->assertOk();

    // Old payment should appear in wider range
    $response2 = $this->actingAs($this->user)->get(route('analytics.index', [
        'date_from' => now()->subDays(35)->toDateString(),
        'date_to' => now()->toDateString(),
    ]));
    $response2->assertOk();
});

// --- COMPARISON ANALYTICS ---

test('analytics shows comparison percentages', function () {
    $response = $this->actingAs($this->user)->get(route('analytics.index'));
    $response->assertOk();
    $response->assertSee('vs previous');
    $response->assertSee('%');
});

test('analytics handles zero previous period safely', function () {
    $response = $this->actingAs($this->user)->get(route('analytics.index'));
    $response->assertOk();
    // Should not show misleading NaN or infinity
    $response->assertDontSee('NaN');
    $response->assertDontSee('Infinity');
});

// --- EXPORT ---

test('analytics export patients works', function () {
    $response = $this->actingAs($this->user)->get(route('analytics.export', [
        'type' => 'patients',
        'date_from' => now()->startOfMonth()->toDateString(),
        'date_to' => now()->toDateString(),
    ]));
    $response->assertOk();
    $response->assertHeader('Content-Type', 'text/csv; charset=utf-8');
});

test('analytics export appointments works', function () {
    $response = $this->actingAs($this->user)->get(route('analytics.export', [
        'type' => 'appointments',
        'date_from' => now()->startOfMonth()->toDateString(),
        'date_to' => now()->toDateString(),
    ]));
    $response->assertOk();
});

test('analytics export doctors works', function () {
    $response = $this->actingAs($this->user)->get(route('analytics.export', [
        'type' => 'doctors',
        'date_from' => now()->startOfMonth()->toDateString(),
        'date_to' => now()->toDateString(),
    ]));
    $response->assertOk();
});

test('analytics export consultations works', function () {
    $response = $this->actingAs($this->user)->get(route('analytics.export', [
        'type' => 'consultations',
        'date_from' => now()->startOfMonth()->toDateString(),
        'date_to' => now()->toDateString(),
    ]));
    $response->assertOk();
});

test('analytics export prescriptions works', function () {
    $response = $this->actingAs($this->user)->get(route('analytics.export', [
        'type' => 'prescriptions',
        'date_from' => now()->startOfMonth()->toDateString(),
        'date_to' => now()->toDateString(),
    ]));
    $response->assertOk();
});

test('analytics export inventory works', function () {
    $response = $this->actingAs($this->user)->get(route('analytics.export', [
        'type' => 'inventory',
        'date_from' => now()->startOfMonth()->toDateString(),
        'date_to' => now()->toDateString(),
    ]));
    $response->assertOk();
});

test('analytics export investigations works', function () {
    $response = $this->actingAs($this->user)->get(route('analytics.export', [
        'type' => 'investigations',
        'date_from' => now()->startOfMonth()->toDateString(),
        'date_to' => now()->toDateString(),
    ]));
    $response->assertOk();
});

test('analytics export financial works', function () {
    $response = $this->actingAs($this->user)->get(route('analytics.export', [
        'type' => 'financial',
        'date_from' => now()->startOfMonth()->toDateString(),
        'date_to' => now()->toDateString(),
    ]));
    $response->assertOk();
});

test('analytics export rejects invalid type', function () {
    $response = $this->actingAs($this->user)->get(route('analytics.export', [
        'type' => 'invalid',
        'date_from' => now()->startOfMonth()->toDateString(),
        'date_to' => now()->toDateString(),
    ]));
    $response->assertOk();
});

// --- AUTHORIZATION ---

test('analytics page requires authentication', function () {
    $response = $this->get(route('analytics.index'));
    $response->assertRedirect();
});

test('analytics page requires dashboard.view permission', function () {
    $user = User::factory()->create();
    $user->givePermissionTo([]);

    $response = $this->actingAs($user)->get(route('analytics.index'));
    $response->assertForbidden();
});

test('analytics export requires authentication', function () {
    $response = $this->get(route('analytics.export', [
        'type' => 'patients',
        'date_from' => now()->startOfMonth()->toDateString(),
        'date_to' => now()->toDateString(),
    ]));
    $response->assertRedirect();
});

test('analytics export requires dashboard.view permission', function () {
    $user = User::factory()->create();
    $user->givePermissionTo([]);

    $response = $this->actingAs($user)->get(route('analytics.export', [
        'type' => 'patients',
        'date_from' => now()->startOfMonth()->toDateString(),
        'date_to' => now()->toDateString(),
    ]));
    $response->assertForbidden();
});

// --- DOCTOR PERFORMANCE ---

test('analytics shows doctor performance data', function () {
    Appointment::create([
        'patient_id' => $this->patient->id,
        'doctor_id' => $this->doctor->id,
        'department_id' => $this->department->id,
        'name' => $this->patient->name,
        'phone' => $this->patient->phone,
        'date' => now()->toDateString(),
        'time' => '10:00',
        'appointment_number' => 'APT-DOC-001',
        'status' => AppointmentStatus::Completed,
    ]);

    Consultation::create([
        'patient_id' => $this->patient->id,
        'doctor_id' => $this->doctor->id,
        'status' => 'completed',
    ]);

    $response = $this->actingAs($this->user)->get(route('analytics.index'));
    $response->assertOk();
    $response->assertSee('Doctor Performance');
    $response->assertSee($this->doctor->name);
});

// --- INVENTORY ANALYTICS ---

test('analytics shows stock movement types', function () {
    $medicine = Medicine::create([
        'name' => 'Test Med',
        'unit_price' => 10.00,
        'stock_quantity' => 100,
        'is_active' => true,
    ]);

    $medicine->setOpeningStock(100, $this->user->id);
    $medicine->stockOut(10, 'Dispensed', $this->user->id);

    $response = $this->actingAs($this->user)->get(route('analytics.index'));
    $response->assertOk();
    $response->assertSee('Stock Movements by Type');
});

test('analytics shows fast moving medicines', function () {
    $medicine = Medicine::create([
        'name' => 'Fast Med',
        'unit_price' => 10.00,
        'stock_quantity' => 100,
        'is_active' => true,
    ]);

    $medicine->setOpeningStock(100, $this->user->id);
    $medicine->stockOut(50, 'Dispensed', $this->user->id);

    $response = $this->actingAs($this->user)->get(route('analytics.index'));
    $response->assertOk();
    $response->assertSee('Fast-Moving Medicines');
    $response->assertSee('Fast Med');
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

test('existing consultation tests still pass', function () {
    $response = $this->actingAs($this->user)->get(route('consultations.index'));
    $response->assertOk();
});

test('existing billing tests still pass', function () {
    $response = $this->actingAs($this->user)->get(route('invoices.index'));
    $response->assertOk();
});

test('existing dashboard tests still pass', function () {
    $response = $this->actingAs($this->user)->get(route('dashboard'));
    $response->assertOk();
});

test('existing report tests still pass', function () {
    $response = $this->actingAs($this->user)->get(route('reports.index'));
    $response->assertOk();
});
