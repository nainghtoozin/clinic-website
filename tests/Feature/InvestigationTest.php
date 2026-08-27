<?php

use App\Models\Consultation;
use App\Models\Doctor;
use App\Models\Department;
use App\Models\Investigation;
use App\Models\LabTest;
use App\Models\Patient;
use App\Models\User;
use Spatie\Permission\Models\Permission;

beforeEach(function () {
    $this->permissions = [
        'patient.view', 'patient.create', 'patient.edit', 'patient.delete',
        'consultation.view', 'consultation.create', 'consultation.edit', 'consultation.complete',
        'investigation.view', 'investigation.create', 'investigation.edit', 'investigation.delete',
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

    $this->patient = Patient::create([
        'name' => 'John Doe',
        'patient_number' => 'P-000001',
        'date_of_birth' => '1990-01-01',
        'gender' => 'male',
        'phone' => '1234567890',
    ]);

    $this->labTest = LabTest::create([
        'name' => 'Complete Blood Count',
        'code' => 'CBC',
        'category' => 'Hematology',
        'price' => 25.00,
        'is_active' => true,
    ]);

    $this->consultation = Consultation::create([
        'patient_id' => $this->patient->id,
        'doctor_id' => $this->doctor->id,
        'symptoms' => 'Fever and headache',
        'status' => 'draft',
    ]);
});

// --- INDEX ---

test('investigation index page loads successfully', function () {
    $response = $this->actingAs($this->user)->get(route('investigations.index'));
    $response->assertOk();
});

test('investigation index shows investigations', function () {
    Investigation::create([
        'patient_id' => $this->patient->id,
        'doctor_id' => $this->doctor->id,
        'lab_test_id' => $this->labTest->id,
        'requested_date' => now()->toDateString(),
        'priority' => 'routine',
        'status' => 'requested',
    ]);

    $response = $this->actingAs($this->user)->get(route('investigations.index'));
    $response->assertSee('John Doe');
    $response->assertSee('Complete Blood Count');
});

test('investigation index shows empty state', function () {
    $response = $this->actingAs($this->user)->get(route('investigations.index'));
    $response->assertSee('No Investigations Found');
});

test('investigation index search works', function () {
    Investigation::create([
        'patient_id' => $this->patient->id,
        'doctor_id' => $this->doctor->id,
        'lab_test_id' => $this->labTest->id,
        'requested_date' => now()->toDateString(),
        'priority' => 'routine',
        'status' => 'requested',
    ]);

    $response = $this->actingAs($this->user)->get(route('investigations.index', ['search' => 'Blood']));
    $response->assertSee('Complete Blood Count');
});

test('investigation index status filter works', function () {
    Investigation::create([
        'patient_id' => $this->patient->id,
        'doctor_id' => $this->doctor->id,
        'lab_test_id' => $this->labTest->id,
        'requested_date' => now()->toDateString(),
        'priority' => 'routine',
        'status' => 'requested',
    ]);

    Investigation::create([
        'patient_id' => $this->patient->id,
        'doctor_id' => $this->doctor->id,
        'lab_test_id' => $this->labTest->id,
        'requested_date' => now()->toDateString(),
        'priority' => 'routine',
        'status' => 'completed',
        'result_value' => '12.5',
        'resulted_at' => now(),
    ]);

    $response = $this->actingAs($this->user)->get(route('investigations.index', ['status' => 'requested']));
    $response->assertSee('John Doe');
});

// --- CREATE ---

test('investigation create form loads', function () {
    $response = $this->actingAs($this->user)->get(route('investigations.create'));
    $response->assertOk();
    $response->assertSee('New Investigation');
});

test('investigation create form pre-fills patient from consultation', function () {
    $response = $this->actingAs($this->user)->get(route('investigations.create', [
        'consultation_id' => $this->consultation->id,
        'patient_id' => $this->patient->id,
    ]));

    $response->assertOk();
    $response->assertSee('John Doe');
    $response->assertSee('Dr. Smith');
});

test('investigation can be created', function () {
    $response = $this->actingAs($this->user)->post(route('investigations.store'), [
        'patient_id' => $this->patient->id,
        'doctor_id' => $this->doctor->id,
        'lab_test_id' => $this->labTest->id,
        'requested_date' => now()->toDateString(),
        'priority' => 'routine',
        'clinical_notes' => 'Test for anemia',
    ]);

    $response->assertRedirect();
    $this->assertDatabaseHas('investigations', [
        'patient_id' => $this->patient->id,
        'doctor_id' => $this->doctor->id,
        'lab_test_id' => $this->labTest->id,
        'status' => 'requested',
    ]);
});

test('investigation validation requires patient_id', function () {
    $response = $this->actingAs($this->user)->post(route('investigations.store'), [
        'doctor_id' => $this->doctor->id,
        'lab_test_id' => $this->labTest->id,
        'requested_date' => now()->toDateString(),
        'priority' => 'routine',
    ]);

    $response->assertSessionHasErrors('patient_id');
});

test('investigation validation requires doctor_id', function () {
    $response = $this->actingAs($this->user)->post(route('investigations.store'), [
        'patient_id' => $this->patient->id,
        'lab_test_id' => $this->labTest->id,
        'requested_date' => now()->toDateString(),
        'priority' => 'routine',
    ]);

    $response->assertSessionHasErrors('doctor_id');
});

test('investigation validation requires lab_test_id', function () {
    $response = $this->actingAs($this->user)->post(route('investigations.store'), [
        'patient_id' => $this->patient->id,
        'doctor_id' => $this->doctor->id,
        'requested_date' => now()->toDateString(),
        'priority' => 'routine',
    ]);

    $response->assertSessionHasErrors('lab_test_id');
});

test('investigation validation requires valid priority', function () {
    $response = $this->actingAs($this->user)->post(route('investigations.store'), [
        'patient_id' => $this->patient->id,
        'doctor_id' => $this->doctor->id,
        'lab_test_id' => $this->labTest->id,
        'requested_date' => now()->toDateString(),
        'priority' => 'invalid',
    ]);

    $response->assertSessionHasErrors('priority');
});

// --- SHOW ---

test('investigation show page loads', function () {
    $inv = Investigation::create([
        'patient_id' => $this->patient->id,
        'doctor_id' => $this->doctor->id,
        'lab_test_id' => $this->labTest->id,
        'requested_date' => now()->toDateString(),
        'priority' => 'routine',
        'status' => 'requested',
    ]);

    $response = $this->actingAs($this->user)->get(route('investigations.show', $inv));
    $response->assertOk();
    $response->assertSee('Complete Blood Count');
    $response->assertSee('John Doe');
});

// --- STATUS TRANSITIONS ---

test('requested investigation can transition to in_progress', function () {
    $inv = Investigation::create([
        'patient_id' => $this->patient->id,
        'doctor_id' => $this->doctor->id,
        'lab_test_id' => $this->labTest->id,
        'requested_date' => now()->toDateString(),
        'priority' => 'routine',
        'status' => 'requested',
    ]);

    $response = $this->actingAs($this->user)->post(route('investigations.status', $inv), ['status' => 'in_progress']);
    $response->assertRedirect();
    $this->assertDatabaseHas('investigations', ['id' => $inv->id, 'status' => 'in_progress']);
});

test('in_progress investigation can transition to completed', function () {
    $inv = Investigation::create([
        'patient_id' => $this->patient->id,
        'doctor_id' => $this->doctor->id,
        'lab_test_id' => $this->labTest->id,
        'requested_date' => now()->toDateString(),
        'priority' => 'routine',
        'status' => 'in_progress',
    ]);

    $response = $this->actingAs($this->user)->post(route('investigations.status', $inv), ['status' => 'completed']);
    $response->assertRedirect();
    $this->assertDatabaseHas('investigations', ['id' => $inv->id, 'status' => 'completed']);
});

test('requested investigation can be cancelled', function () {
    $inv = Investigation::create([
        'patient_id' => $this->patient->id,
        'doctor_id' => $this->doctor->id,
        'lab_test_id' => $this->labTest->id,
        'requested_date' => now()->toDateString(),
        'priority' => 'routine',
        'status' => 'requested',
    ]);

    $response = $this->actingAs($this->user)->post(route('investigations.status', $inv), ['status' => 'cancelled']);
    $response->assertRedirect();
    $this->assertDatabaseHas('investigations', ['id' => $inv->id, 'status' => 'cancelled']);
});

test('completed investigation cannot transition to in_progress', function () {
    $inv = Investigation::create([
        'patient_id' => $this->patient->id,
        'doctor_id' => $this->doctor->id,
        'lab_test_id' => $this->labTest->id,
        'requested_date' => now()->toDateString(),
        'priority' => 'routine',
        'status' => 'completed',
    ]);

    $response = $this->actingAs($this->user)->post(route('investigations.status', $inv), ['status' => 'in_progress']);
    $response->assertSessionHas('error');
});

test('cancelled investigation cannot transition', function () {
    $inv = Investigation::create([
        'patient_id' => $this->patient->id,
        'doctor_id' => $this->doctor->id,
        'lab_test_id' => $this->labTest->id,
        'requested_date' => now()->toDateString(),
        'priority' => 'routine',
        'status' => 'cancelled',
    ]);

    $response = $this->actingAs($this->user)->post(route('investigations.status', $inv), ['status' => 'requested']);
    $response->assertSessionHas('error');
});

// --- RESULT ENTRY ---

test('result can be entered for requested investigation', function () {
    $inv = Investigation::create([
        'patient_id' => $this->patient->id,
        'doctor_id' => $this->doctor->id,
        'lab_test_id' => $this->labTest->id,
        'requested_date' => now()->toDateString(),
        'priority' => 'routine',
        'status' => 'requested',
    ]);

    $response = $this->actingAs($this->user)->post(route('investigations.result', $inv), [
        'result_value' => '12.5',
        'result_unit' => 'g/dL',
        'result_reference_range' => '12-16',
        'interpretation' => 'Normal',
    ]);

    $response->assertRedirect();
    $this->assertDatabaseHas('investigations', [
        'id' => $inv->id,
        'result_value' => '12.5',
        'result_unit' => 'g/dL',
        'result_status' => 'entered',
    ]);
});

test('result validation requires result_value', function () {
    $inv = Investigation::create([
        'patient_id' => $this->patient->id,
        'doctor_id' => $this->doctor->id,
        'lab_test_id' => $this->labTest->id,
        'requested_date' => now()->toDateString(),
        'priority' => 'routine',
        'status' => 'requested',
    ]);

    $response = $this->actingAs($this->user)->post(route('investigations.result', $inv), []);
    $response->assertSessionHasErrors('result_value');
});

// --- EDIT ---

test('investigation edit form loads', function () {
    $inv = Investigation::create([
        'patient_id' => $this->patient->id,
        'doctor_id' => $this->doctor->id,
        'lab_test_id' => $this->labTest->id,
        'requested_date' => now()->toDateString(),
        'priority' => 'routine',
        'status' => 'requested',
    ]);

    $response = $this->actingAs($this->user)->get(route('investigations.edit', $inv));
    $response->assertOk();
    $response->assertSee('Edit Investigation');
});

test('investigation can be updated', function () {
    $inv = Investigation::create([
        'patient_id' => $this->patient->id,
        'doctor_id' => $this->doctor->id,
        'lab_test_id' => $this->labTest->id,
        'requested_date' => now()->toDateString(),
        'priority' => 'routine',
        'status' => 'requested',
        'clinical_notes' => 'Old notes',
    ]);

    $response = $this->actingAs($this->user)->put(route('investigations.update', $inv), [
        'clinical_notes' => 'Updated notes',
    ]);

    $response->assertRedirect();
    $this->assertDatabaseHas('investigations', ['id' => $inv->id, 'clinical_notes' => 'Updated notes']);
});

// --- DELETE ---

test('requested investigation can be deleted', function () {
    $inv = Investigation::create([
        'patient_id' => $this->patient->id,
        'doctor_id' => $this->doctor->id,
        'lab_test_id' => $this->labTest->id,
        'requested_date' => now()->toDateString(),
        'priority' => 'routine',
        'status' => 'requested',
    ]);

    $response = $this->actingAs($this->user)->delete(route('investigations.destroy', $inv));
    $response->assertRedirect();
    $this->assertDatabaseMissing('investigations', ['id' => $inv->id]);
});

test('completed investigation cannot be deleted', function () {
    $inv = Investigation::create([
        'patient_id' => $this->patient->id,
        'doctor_id' => $this->doctor->id,
        'lab_test_id' => $this->labTest->id,
        'requested_date' => now()->toDateString(),
        'priority' => 'routine',
        'status' => 'completed',
        'result_value' => '12.5',
        'resulted_at' => now(),
    ]);

    $response = $this->actingAs($this->user)->delete(route('investigations.destroy', $inv));
    $response->assertSessionHas('error');
    $this->assertDatabaseHas('investigations', ['id' => $inv->id]);
});

// --- AUTHORIZATION ---

test('unauthorized user cannot access investigation index', function () {
    $user = User::factory()->create();
    $response = $this->actingAs($user)->get(route('investigations.index'));
    $response->assertForbidden();
});

test('unauthorized user cannot create investigation', function () {
    $user = User::factory()->create();
    $response = $this->actingAs($user)->get(route('investigations.create'));
    $response->assertForbidden();
});

// --- PRIORITY ---

test('investigation supports urgent priority', function () {
    $response = $this->actingAs($this->user)->post(route('investigations.store'), [
        'patient_id' => $this->patient->id,
        'doctor_id' => $this->doctor->id,
        'lab_test_id' => $this->labTest->id,
        'requested_date' => now()->toDateString(),
        'priority' => 'urgent',
    ]);

    $response->assertRedirect();
    $this->assertDatabaseHas('investigations', ['priority' => 'urgent']);
});

test('investigation supports stat priority', function () {
    $response = $this->actingAs($this->user)->post(route('investigations.store'), [
        'patient_id' => $this->patient->id,
        'doctor_id' => $this->doctor->id,
        'lab_test_id' => $this->labTest->id,
        'requested_date' => now()->toDateString(),
        'priority' => 'stat',
    ]);

    $response->assertRedirect();
    $this->assertDatabaseHas('investigations', ['priority' => 'stat']);
});
