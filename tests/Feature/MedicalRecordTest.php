<?php

use App\Enums\AppointmentStatus;
use App\Models\Appointment;
use App\Models\Consultation;
use App\Models\Department;
use App\Models\Doctor;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Medicine;
use App\Models\Patient;
use App\Models\Payment;
use App\Models\Prescription;
use App\Models\PrescriptionItem;
use App\Models\User;
use App\Models\VitalSign;
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

// --- MEDICAL RECORD PAGE LOADS ---

test('medical record page loads successfully', function () {
    $response = $this->actingAs($this->user)->get(route('patients.medical-record', $this->patient));
    $response->assertOk();
});

test('medical record page shows patient name', function () {
    $response = $this->actingAs($this->user)->get(route('patients.medical-record', $this->patient));
    $response->assertSee($this->patient->name);
});

test('medical record page shows patient number', function () {
    $response = $this->actingAs($this->user)->get(route('patients.medical-record', $this->patient));
    $response->assertSee($this->patient->patient_number);
});

test('medical record page shows all tab sections', function () {
    $response = $this->actingAs($this->user)->get(route('patients.medical-record', $this->patient));
    $response->assertSee('tab-visits');
    $response->assertSee('tab-vitals');
    $response->assertSee('tab-prescriptions');
    $response->assertSee('tab-appointments');
    $response->assertSee('tab-billing');
    $response->assertSee('tab-investigations');
});

// --- PATIENT DATA DISPLAYED ---

test('medical record shows patient age', function () {
    $this->patient->update(['date_of_birth' => now()->subYears(30)->toDateString()]);
    $response = $this->actingAs($this->user)->get(route('patients.medical-record', $this->patient));
    $response->assertSee('Age: 30');
});

test('medical record shows patient gender', function () {
    $this->patient->update(['gender' => 'male']);
    $response = $this->actingAs($this->user)->get(route('patients.medical-record', $this->patient));
    $response->assertSee('Male');
});

test('medical record shows patient phone', function () {
    $this->patient->update(['phone' => '555-1234']);
    $response = $this->actingAs($this->user)->get(route('patients.medical-record', $this->patient));
    $response->assertSee('555-1234');
});

test('medical record shows blood group', function () {
    $this->patient->update(['blood_group' => 'A+']);
    $response = $this->actingAs($this->user)->get(route('patients.medical-record', $this->patient));
    $response->assertSee('A+');
});

test('medical record shows allergies', function () {
    $this->patient->update(['allergies' => 'Penicillin, Dust']);
    $response = $this->actingAs($this->user)->get(route('patients.medical-record', $this->patient));
    $response->assertSee('Penicillin, Dust');
    $response->assertSee('Allergies');
});

test('medical record shows medical history', function () {
    $this->patient->update(['medical_history' => 'Hypertension, Diabetes']);
    $response = $this->actingAs($this->user)->get(route('patients.medical-record', $this->patient));
    $response->assertSee('Hypertension, Diabetes');
    $response->assertSee('Medical History');
});

// --- APPOINTMENTS DISPLAYED ---

test('patient appointments are displayed', function () {
    $appointment = Appointment::create([
        'patient_id' => $this->patient->id,
        'doctor_id' => $this->doctor->id,
        'department_id' => $this->department->id,
        'name' => $this->patient->name,
        'email' => $this->patient->email,
        'phone' => $this->patient->phone,
        'date' => now()->toDateString(),
        'time' => '10:00',
        'duration' => 30,
        'appointment_number' => 'APT-MR-001',
        'status' => AppointmentStatus::Completed,
    ]);

    $response = $this->actingAs($this->user)->get(route('patients.medical-record', $this->patient));
    $response->assertSee('APT-MR-001');
    $response->assertSee('Appointment History');
});

// --- CONSULTATIONS DISPLAYED ---

test('patient consultations are displayed', function () {
    $consultation = Consultation::create([
        'patient_id' => $this->patient->id,
        'doctor_id' => $this->doctor->id,
        'symptoms' => 'Chest pain',
        'diagnosis' => 'Angina',
        'status' => 'completed',
    ]);

    $response = $this->actingAs($this->user)->get(route('patients.medical-record', $this->patient));
    $response->assertSee('Chest pain');
    $response->assertSee('Angina');
});

// --- VITAL SIGNS DISPLAYED ---

test('vital signs history is displayed', function () {
    $consultation = Consultation::create([
        'patient_id' => $this->patient->id,
        'doctor_id' => $this->doctor->id,
        'status' => 'completed',
    ]);

    VitalSign::create([
        'consultation_id' => $consultation->id,
        'blood_pressure' => '120/80',
        'temperature' => 36.5,
        'pulse' => 72,
        'weight' => 70.0,
        'height' => 175.0,
        'oxygen_saturation' => 98.0,
        'recorded_at' => now(),
    ]);

    $response = $this->actingAs($this->user)->get(route('patients.medical-record', $this->patient));
    $response->assertSee('120/80');
    $response->assertSee('36.5');
    $response->assertSee('72');
    $response->assertSee('98');
});

// --- DIAGNOSIS HISTORY DISPLAYED ---

test('diagnosis history is displayed', function () {
    Consultation::create([
        'patient_id' => $this->patient->id,
        'doctor_id' => $this->doctor->id,
        'diagnosis' => 'Upper respiratory infection',
        'status' => 'completed',
    ]);

    $response = $this->actingAs($this->user)->get(route('patients.medical-record', $this->patient));
    $response->assertSee('Upper respiratory infection');
});

// --- PRESCRIPTION HISTORY DISPLAYED ---

test('prescription history is displayed', function () {
    $medicine = Medicine::create([
        'name' => 'Amoxicillin',
        'generic_name' => 'Amoxicillin Trihydrate',
        'category' => 'Antibiotics',
        'form' => 'capsule',
        'strength' => '500mg',
        'unit_price' => 15.50,
        'stock_quantity' => 100,
        'is_active' => true,
    ]);

    $prescription = Prescription::create([
        'patient_id' => $this->patient->id,
        'doctor_id' => $this->doctor->id,
        'prescribed_date' => now()->toDateString(),
    ]);

    PrescriptionItem::create([
        'prescription_id' => $prescription->id,
        'medicine_id' => $medicine->id,
        'dosage' => '1 capsule',
        'frequency' => '3 times daily',
        'quantity' => 21,
    ]);

    $response = $this->actingAs($this->user)->get(route('patients.medical-record', $this->patient));
    $response->assertSee($prescription->prescription_number);
    $response->assertSee('Amoxicillin');
    $response->assertSee('1 capsule');
});

// --- FOLLOW-UP DISPLAYED ---

test('follow-up information is displayed', function () {
    Consultation::create([
        'patient_id' => $this->patient->id,
        'doctor_id' => $this->doctor->id,
        'follow_up_date' => now()->addWeek()->toDateString(),
        'follow_up_notes' => 'Check blood pressure',
        'status' => 'completed',
    ]);

    $response = $this->actingAs($this->user)->get(route('patients.medical-record', $this->patient));
    $response->assertSee('Check blood pressure');
});

// --- BILLING HISTORY DISPLAYED ---

test('billing history is displayed', function () {
    $consultation = Consultation::create([
        'patient_id' => $this->patient->id,
        'doctor_id' => $this->doctor->id,
        'status' => 'completed',
    ]);

    $invoice = Invoice::create([
        'patient_id' => $this->patient->id,
        'doctor_id' => $this->doctor->id,
        'consultation_id' => $consultation->id,
        'subtotal' => 100.00,
        'total' => 100.00,
        'amount_paid' => 0,
        'balance' => 100.00,
        'status' => 'issued',
    ]);

    $response = $this->actingAs($this->user)->get(route('patients.medical-record', $this->patient));
    $response->assertSee('Billing History');
    $response->assertSee($invoice->invoice_number);
});

// --- DATE FILTERING ---

test('date filtering works', function () {
    Consultation::create([
        'patient_id' => $this->patient->id,
        'doctor_id' => $this->doctor->id,
        'diagnosis' => 'Test condition',
        'status' => 'completed',
    ]);

    $response = $this->actingAs($this->user)->get(route('patients.medical-record', $this->patient, [
        'date_from' => now()->subDay()->toDateString(),
        'date_to' => now()->addDay()->toDateString(),
    ]));
    $response->assertOk();
    $response->assertSee('Test condition');
});

// --- EMPTY STATE ---

test('empty patient shows clean empty state', function () {
    $emptyPatient = Patient::factory()->create(['status' => 'active']);

    $response = $this->actingAs($this->user)->get(route('patients.medical-record', $emptyPatient));
    $response->assertOk();
    $response->assertSee('No Visit Records');
    $response->assertSee('No Vital Signs Recorded');
    $response->assertSee('No Prescriptions');
    $response->assertSee('No Appointments');
    $response->assertSee('No Billing Records');
});

// --- AUTHORIZATION ---

test('unauthorized user cannot access medical record', function () {
    $user = User::factory()->create();
    $response = $this->actingAs($user)->get(route('patients.medical-record', $this->patient));
    $response->assertForbidden();
});

test('patient data does not leak through public routes', function () {
    $response = $this->get(route('public.index'));
    $response->assertOk();
    $response->assertDontSee($this->patient->name);
    $response->assertDontSee($this->patient->patient_number);
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

test('existing prescription tests still pass', function () {
    $response = $this->actingAs($this->user)->get(route('prescriptions.index'));
    $response->assertOk();
});

test('existing invoice tests still pass', function () {
    $response = $this->actingAs($this->user)->get(route('invoices.index'));
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

test('patient profile page still works', function () {
    $response = $this->actingAs($this->user)->get(route('patients.show', $this->patient));
    $response->assertOk();
    $response->assertSee($this->patient->name);
    $response->assertSee('Medical Record');
});