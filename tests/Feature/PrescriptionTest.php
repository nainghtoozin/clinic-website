<?php

use App\Models\Doctor;
use App\Models\Department;
use App\Models\Medicine;
use App\Models\Patient;
use App\Models\Prescription;
use App\Models\PrescriptionItem;
use App\Models\Consultation;
use App\Models\User;
use Spatie\Permission\Models\Permission;

beforeEach(function () {
    $this->permissions = [
        'doctor.view', 'doctor.create', 'doctor.edit', 'doctor.delete',
        'patient.view', 'patient.create', 'patient.edit', 'patient.delete',
        'medicine.view', 'medicine.create', 'medicine.edit', 'medicine.delete',
        'prescription.view', 'prescription.create', 'prescription.edit', 'prescription.delete',
        'consultation.view', 'consultation.create', 'consultation.edit', 'consultation.complete',
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

    $this->medicine = Medicine::create([
        'name' => 'Amoxicillin',
        'generic_name' => 'Amoxicillin Trihydrate',
        'category' => 'Antibiotics',
        'form' => 'capsule',
        'strength' => '500mg',
        'unit_price' => 15.50,
        'stock_quantity' => 100,
        'is_active' => true,
    ]);
});

// --- PRESCRIPTION CREATION TESTS ---

test('prescription can be created with items', function () {
    $response = $this->actingAs($this->user)->post(route('prescriptions.store'), [
        'patient_id' => $this->patient->id,
        'doctor_id' => $this->doctor->id,
        'prescribed_date' => now()->toDateString(),
        'notes' => 'Take with food',
        'items' => [
            [
                'medicine_id' => $this->medicine->id,
                'dosage' => '1 capsule',
                'frequency' => '3 times daily',
                'duration' => '7 days',
                'instructions' => 'After meals',
                'quantity' => 21,
            ],
        ],
    ]);

    $response->assertRedirect();
    $this->assertDatabaseHas('prescriptions', [
        'patient_id' => $this->patient->id,
        'doctor_id' => $this->doctor->id,
    ]);
    $this->assertDatabaseHas('prescription_items', [
        'medicine_id' => $this->medicine->id,
        'dosage' => '1 capsule',
    ]);
});

test('prescription number is auto-generated', function () {
    $prescription = Prescription::create([
        'patient_id' => $this->patient->id,
        'doctor_id' => $this->doctor->id,
        'prescribed_date' => now()->toDateString(),
    ]);

    $this->assertNotNull($prescription->prescription_number);
    $this->assertStringStartsWith('RX-', $prescription->prescription_number);
});

test('prescription number is unique', function () {
    $p1 = Prescription::create([
        'patient_id' => $this->patient->id,
        'doctor_id' => $this->doctor->id,
        'prescribed_date' => now()->toDateString(),
    ]);

    $p2 = Prescription::create([
        'patient_id' => $this->patient->id,
        'doctor_id' => $this->doctor->id,
        'prescribed_date' => now()->toDateString(),
    ]);

    $this->assertNotEquals($p1->prescription_number, $p2->prescription_number);
});

test('patient_id is required', function () {
    $response = $this->actingAs($this->user)->post(route('prescriptions.store'), [
        'patient_id' => '',
        'doctor_id' => $this->doctor->id,
        'prescribed_date' => now()->toDateString(),
        'items' => [
            [
                'medicine_id' => $this->medicine->id,
                'dosage' => '1 tablet',
                'frequency' => 'Once daily',
                'quantity' => 7,
            ],
        ],
    ]);

    $response->assertSessionHasErrors('patient_id');
});

test('doctor_id is required', function () {
    $response = $this->actingAs($this->user)->post(route('prescriptions.store'), [
        'patient_id' => $this->patient->id,
        'doctor_id' => '',
        'prescribed_date' => now()->toDateString(),
        'items' => [
            [
                'medicine_id' => $this->medicine->id,
                'dosage' => '1 tablet',
                'frequency' => 'Once daily',
                'quantity' => 7,
            ],
        ],
    ]);

    $response->assertSessionHasErrors('doctor_id');
});

test('at least one item is required', function () {
    $response = $this->actingAs($this->user)->post(route('prescriptions.store'), [
        'patient_id' => $this->patient->id,
        'doctor_id' => $this->doctor->id,
        'prescribed_date' => now()->toDateString(),
        'items' => [],
    ]);

    $response->assertSessionHasErrors('items');
});

test('prescription item medicine_id is required', function () {
    $response = $this->actingAs($this->user)->post(route('prescriptions.store'), [
        'patient_id' => $this->patient->id,
        'doctor_id' => $this->doctor->id,
        'prescribed_date' => now()->toDateString(),
        'items' => [
            [
                'medicine_id' => '',
                'dosage' => '1 tablet',
                'frequency' => 'Once daily',
                'quantity' => 7,
            ],
        ],
    ]);

    $response->assertSessionHasErrors('items.0.medicine_id');
});

test('prescription item dosage is required', function () {
    $response = $this->actingAs($this->user)->post(route('prescriptions.store'), [
        'patient_id' => $this->patient->id,
        'doctor_id' => $this->doctor->id,
        'prescribed_date' => now()->toDateString(),
        'items' => [
            [
                'medicine_id' => $this->medicine->id,
                'dosage' => '',
                'frequency' => 'Once daily',
                'quantity' => 7,
            ],
        ],
    ]);

    $response->assertSessionHasErrors('items.0.dosage');
});

test('prescription item frequency is required', function () {
    $response = $this->actingAs($this->user)->post(route('prescriptions.store'), [
        'patient_id' => $this->patient->id,
        'doctor_id' => $this->doctor->id,
        'prescribed_date' => now()->toDateString(),
        'items' => [
            [
                'medicine_id' => $this->medicine->id,
                'dosage' => '1 tablet',
                'frequency' => '',
                'quantity' => 7,
            ],
        ],
    ]);

    $response->assertSessionHasErrors('items.0.frequency');
});

test('prescription item quantity is required', function () {
    $response = $this->actingAs($this->user)->post(route('prescriptions.store'), [
        'patient_id' => $this->patient->id,
        'doctor_id' => $this->doctor->id,
        'prescribed_date' => now()->toDateString(),
        'items' => [
            [
                'medicine_id' => $this->medicine->id,
                'dosage' => '1 tablet',
                'frequency' => 'Once daily',
                'quantity' => '',
            ],
        ],
    ]);

    $response->assertSessionHasErrors('items.0.quantity');
});

test('multiple items can be added', function () {
    $medicine2 = Medicine::create([
        'name' => 'Paracetamol',
        'unit_price' => 5.00,
        'stock_quantity' => 200,
        'is_active' => true,
    ]);

    $response = $this->actingAs($this->user)->post(route('prescriptions.store'), [
        'patient_id' => $this->patient->id,
        'doctor_id' => $this->doctor->id,
        'prescribed_date' => now()->toDateString(),
        'items' => [
            [
                'medicine_id' => $this->medicine->id,
                'dosage' => '1 capsule',
                'frequency' => '3 times daily',
                'quantity' => 21,
            ],
            [
                'medicine_id' => $medicine2->id,
                'dosage' => '1 tablet',
                'frequency' => 'As needed',
                'quantity' => 10,
            ],
        ],
    ]);

    $response->assertRedirect();
    $this->assertEquals(2, Prescription::latest()->first()->items->count());
});

// --- PRESCRIPTION RELATIONSHIP TESTS ---

test('prescription belongs to patient', function () {
    $prescription = Prescription::create([
        'patient_id' => $this->patient->id,
        'doctor_id' => $this->doctor->id,
        'prescribed_date' => now()->toDateString(),
    ]);

    $this->assertNotNull($prescription->patient);
    $this->assertEquals($this->patient->id, $prescription->patient_id);
});

test('prescription belongs to doctor', function () {
    $prescription = Prescription::create([
        'patient_id' => $this->patient->id,
        'doctor_id' => $this->doctor->id,
        'prescribed_date' => now()->toDateString(),
    ]);

    $this->assertNotNull($prescription->doctor);
    $this->assertEquals($this->doctor->id, $prescription->doctor_id);
});

test('prescription has many items', function () {
    $prescription = Prescription::create([
        'patient_id' => $this->patient->id,
        'doctor_id' => $this->doctor->id,
        'prescribed_date' => now()->toDateString(),
    ]);

    PrescriptionItem::create([
        'prescription_id' => $prescription->id,
        'medicine_id' => $this->medicine->id,
        'dosage' => '1 tablet',
        'frequency' => 'Once daily',
        'quantity' => 7,
    ]);

    $this->assertEquals(1, $prescription->items->count());
});

test('prescription item belongs to medicine', function () {
    $prescription = Prescription::create([
        'patient_id' => $this->patient->id,
        'doctor_id' => $this->doctor->id,
        'prescribed_date' => now()->toDateString(),
    ]);

    $item = PrescriptionItem::create([
        'prescription_id' => $prescription->id,
        'medicine_id' => $this->medicine->id,
        'dosage' => '1 tablet',
        'frequency' => 'Once daily',
        'quantity' => 7,
    ]);

    $this->assertNotNull($item->medicine);
    $this->assertEquals($this->medicine->id, $item->medicine_id);
});

test('prescription total is calculated correctly', function () {
    $prescription = Prescription::create([
        'patient_id' => $this->patient->id,
        'doctor_id' => $this->doctor->id,
        'prescribed_date' => now()->toDateString(),
    ]);

    PrescriptionItem::create([
        'prescription_id' => $prescription->id,
        'medicine_id' => $this->medicine->id,
        'dosage' => '1 tablet',
        'frequency' => 'Once daily',
        'quantity' => 10,
    ]);

    $this->assertEquals(155.00, $prescription->total);
});

test('prescription item subtotal is calculated correctly', function () {
    $prescription = Prescription::create([
        'patient_id' => $this->patient->id,
        'doctor_id' => $this->doctor->id,
        'prescribed_date' => now()->toDateString(),
    ]);

    $item = PrescriptionItem::create([
        'prescription_id' => $prescription->id,
        'medicine_id' => $this->medicine->id,
        'dosage' => '1 tablet',
        'frequency' => 'Once daily',
        'quantity' => 10,
    ]);

    $this->assertEquals(155.00, $item->subtotal);
});

// --- PRESCRIPTION CRUD TESTS ---

test('prescription can be updated', function () {
    $prescription = Prescription::create([
        'patient_id' => $this->patient->id,
        'doctor_id' => $this->doctor->id,
        'prescribed_date' => now()->toDateString(),
    ]);

    PrescriptionItem::create([
        'prescription_id' => $prescription->id,
        'medicine_id' => $this->medicine->id,
        'dosage' => '1 tablet',
        'frequency' => 'Once daily',
        'quantity' => 7,
    ]);

    $response = $this->actingAs($this->user)->put(route('prescriptions.update', $prescription), [
        'patient_id' => $this->patient->id,
        'doctor_id' => $this->doctor->id,
        'prescribed_date' => now()->toDateString(),
        'notes' => 'Updated notes',
        'items' => [
            [
                'medicine_id' => $this->medicine->id,
                'dosage' => '2 tablets',
                'frequency' => 'Twice daily',
                'quantity' => 14,
            ],
        ],
    ]);

    $response->assertRedirect();
    $prescription->refresh();
    $this->assertEquals('Updated notes', $prescription->notes);
    $this->assertEquals(1, $prescription->items->count());
    $this->assertEquals('2 tablets', $prescription->items->first()->dosage);
});

test('prescription can be deleted', function () {
    $prescription = Prescription::create([
        'patient_id' => $this->patient->id,
        'doctor_id' => $this->doctor->id,
        'prescribed_date' => now()->toDateString(),
    ]);

    $response = $this->actingAs($this->user)->delete(route('prescriptions.destroy', $prescription));

    $response->assertRedirect();
    $this->assertDatabaseMissing('prescriptions', ['id' => $prescription->id]);
});

test('prescription can be viewed', function () {
    $prescription = Prescription::create([
        'patient_id' => $this->patient->id,
        'doctor_id' => $this->doctor->id,
        'prescribed_date' => now()->toDateString(),
    ]);

    $response = $this->actingAs($this->user)->get(route('prescriptions.show', $prescription));

    $response->assertOk();
    $response->assertSee($prescription->prescription_number);
});

test('prescription list can be viewed', function () {
    $response = $this->actingAs($this->user)->get(route('prescriptions.index'));

    $response->assertOk();
});

test('prescription search works', function () {
    Prescription::create([
        'patient_id' => $this->patient->id,
        'doctor_id' => $this->doctor->id,
        'prescription_number' => 'RX-20260811-0001',
        'prescribed_date' => now()->toDateString(),
    ]);

    $response = $this->actingAs($this->user)->get(route('prescriptions.index', ['search' => 'RX-20260811-0001']));

    $response->assertOk();
    $response->assertSee('RX-20260811-0001');
});

test('prescription create form without a consultation redirects with an info message', function () {
    $response = $this->actingAs($this->user)->get(route('prescriptions.create'));

    $response->assertRedirect(route('prescriptions.index'));
    $response->assertSessionHas('info');
});

// --- PATIENT PRESCRIPTION HISTORY TESTS ---

test('patient prescription history appears', function () {
    $prescription = Prescription::create([
        'patient_id' => $this->patient->id,
        'doctor_id' => $this->doctor->id,
        'prescribed_date' => now()->toDateString(),
    ]);

    $response = $this->actingAs($this->user)->get(route('patients.show', $this->patient));
    $response->assertOk();
    $response->assertSee('Prescription History');
    $response->assertSee($prescription->prescription_number);
});

// --- AUTHORIZATION TESTS ---

test('unauthorized user cannot access prescriptions', function () {
    $user = User::factory()->create();
    $response = $this->actingAs($user)->get(route('prescriptions.index'));
    $response->assertForbidden();
});

test('unauthorized user cannot create prescription', function () {
    $user = User::factory()->create();
    $response = $this->actingAs($user)->get(route('prescriptions.create'));
    $response->assertForbidden();
});

test('unauthorized user cannot edit prescription', function () {
    $user = User::factory()->create();
    $prescription = Prescription::create([
        'patient_id' => $this->patient->id,
        'doctor_id' => $this->doctor->id,
        'prescribed_date' => now()->toDateString(),
    ]);
    $response = $this->actingAs($user)->get(route('prescriptions.edit', $prescription));
    $response->assertForbidden();
});

test('unauthorized user cannot delete prescription', function () {
    $user = User::factory()->create();
    $prescription = Prescription::create([
        'patient_id' => $this->patient->id,
        'doctor_id' => $this->doctor->id,
        'prescribed_date' => now()->toDateString(),
    ]);
    $response = $this->actingAs($user)->delete(route('prescriptions.destroy', $prescription));
    $response->assertForbidden();
});

// --- PRESCRIPTION CONSULTATION FLOW TESTS ---

test('prescription create form for a consultation is accessible and locks the doctor', function () {
    $consultation = Consultation::create([
        'patient_id' => $this->patient->id,
        'doctor_id' => $this->doctor->id,
        'status' => 'draft',
    ]);

    $response = $this->actingAs($this->user)->get(route('prescriptions.create', ['consultation_id' => $consultation->id]));

    $response->assertOk();
    $response->assertSee('For consultation');
    $response->assertSee($this->doctor->name);
});

test('prescription created from a consultation auto-links patient and doctor', function () {
    $consultation = Consultation::create([
        'patient_id' => $this->patient->id,
        'doctor_id' => $this->doctor->id,
        'status' => 'draft',
    ]);

    $response = $this->actingAs($this->user)->post(route('prescriptions.store'), [
        'patient_id' => $this->patient->id,
        'doctor_id' => $this->doctor->id,
        'consultation_id' => $consultation->id,
        'prescribed_date' => now()->toDateString(),
        'items' => [
            [
                'medicine_id' => $this->medicine->id,
                'dosage' => '1 capsule',
                'frequency' => '3 times daily',
                'quantity' => 21,
            ],
        ],
    ]);

    $response->assertRedirect();
    $this->assertDatabaseHas('prescriptions', [
        'patient_id' => $this->patient->id,
        'doctor_id' => $this->doctor->id,
        'consultation_id' => $consultation->id,
    ]);
});

test('prescription cannot be stored with a mismatched patient for the consultation', function () {
    $otherPatient = Patient::factory()->create(['status' => 'active']);
    $consultation = Consultation::create([
        'patient_id' => $this->patient->id,
        'doctor_id' => $this->doctor->id,
        'status' => 'draft',
    ]);

    $response = $this->actingAs($this->user)->post(route('prescriptions.store'), [
        'patient_id' => $otherPatient->id,
        'doctor_id' => $this->doctor->id,
        'consultation_id' => $consultation->id,
        'prescribed_date' => now()->toDateString(),
        'items' => [
            [
                'medicine_id' => $this->medicine->id,
                'dosage' => '1 capsule',
                'frequency' => '3 times daily',
                'quantity' => 21,
            ],
        ],
    ]);

    $response->assertSessionHasErrors('patient_id');
    $this->assertDatabaseMissing('prescriptions', ['consultation_id' => $consultation->id]);
});

test('prescription cannot be stored with a mismatched doctor for the consultation', function () {
    $otherDoctor = Doctor::create([
        'name' => 'Dr. Other',
        'slug' => 'dr-other',
        'department_id' => $this->department->id,
        'is_available' => true,
        'available_days' => [1, 2, 3, 4, 5],
        'start_time' => '09:00:00',
        'end_time' => '17:00:00',
        'user_id' => $this->user->id,
    ]);
    $consultation = Consultation::create([
        'patient_id' => $this->patient->id,
        'doctor_id' => $this->doctor->id,
        'status' => 'draft',
    ]);

    $response = $this->actingAs($this->user)->post(route('prescriptions.store'), [
        'patient_id' => $this->patient->id,
        'doctor_id' => $otherDoctor->id,
        'consultation_id' => $consultation->id,
        'prescribed_date' => now()->toDateString(),
        'items' => [
            [
                'medicine_id' => $this->medicine->id,
                'dosage' => '1 capsule',
                'frequency' => '3 times daily',
                'quantity' => 21,
            ],
        ],
    ]);

    $response->assertSessionHasErrors('doctor_id');
    $this->assertDatabaseMissing('prescriptions', ['consultation_id' => $consultation->id]);
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