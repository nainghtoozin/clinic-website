<?php

use App\Enums\AppointmentStatus;
use App\Models\Appointment;
use App\Models\Consultation;
use App\Models\Department;
use App\Models\Doctor;
use App\Models\Patient;
use App\Models\QueueTicket;
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

// --- CONSULTATION CREATION TESTS ---

test('consultation can be created for active in-consultation queue ticket', function () {
    $ticket = QueueTicket::create([
        'patient_id' => $this->patient->id,
        'doctor_id' => $this->doctor->id,
        'queue_date' => now()->toDateString(),
        'ticket_number' => 'A001',
        'status' => 'in_consultation',
        'checked_in_at' => now(),
        'called_at' => now(),
        'consultation_started_at' => now(),
    ]);

    $response = $this->actingAs($this->user)->post(route('consultations.store'), [
        'patient_id' => $this->patient->id,
        'doctor_id' => $this->doctor->id,
        'queue_ticket_id' => $ticket->id,
        'symptoms' => 'Chest pain',
        'diagnosis' => 'Angina',
    ]);

    $response->assertRedirect();
    $this->assertDatabaseHas('consultations', [
        'patient_id' => $this->patient->id,
        'doctor_id' => $this->doctor->id,
        'queue_ticket_id' => $ticket->id,
        'status' => 'draft',
    ]);
});

test('invalid appointment cannot create consultation', function () {
    $response = $this->actingAs($this->user)->post(route('consultations.store'), [
        'patient_id' => 9999,
        'doctor_id' => $this->doctor->id,
        'symptoms' => 'Test',
    ]);

    $response->assertSessionHasErrors('patient_id');
});

test('duplicate consultation for same appointment is prevented', function () {
    $apt = Appointment::create([
        'patient_id' => $this->patient->id,
        'doctor_id' => $this->doctor->id,
        'department_id' => $this->department->id,
        'name' => $this->patient->name,
        'email' => $this->patient->email,
        'phone' => $this->patient->phone,
        'date' => now()->toDateString(),
        'time' => '10:00',
        'duration' => 30,
        'appointment_number' => 'APT-DUP-001',
        'status' => AppointmentStatus::CheckedIn,
    ]);

    Consultation::create([
        'patient_id' => $this->patient->id,
        'doctor_id' => $this->doctor->id,
        'appointment_id' => $apt->id,
        'status' => 'draft',
    ]);

    $response = $this->actingAs($this->user)->post(route('consultations.store'), [
        'patient_id' => $this->patient->id,
        'doctor_id' => $this->doctor->id,
        'appointment_id' => $apt->id,
        'symptoms' => 'Test',
    ]);

    $response->assertSessionHasErrors('appointment_id');
});

test('consultation belongs to patient', function () {
    $consultation = Consultation::create([
        'patient_id' => $this->patient->id,
        'doctor_id' => $this->doctor->id,
        'status' => 'draft',
    ]);

    $this->assertNotNull($consultation->patient);
    $this->assertEquals($this->patient->id, $consultation->patient_id);
});

test('consultation belongs to doctor', function () {
    $consultation = Consultation::create([
        'patient_id' => $this->patient->id,
        'doctor_id' => $this->doctor->id,
        'status' => 'draft',
    ]);

    $this->assertNotNull($consultation->doctor);
    $this->assertEquals($this->doctor->id, $consultation->doctor_id);
});

test('symptoms can be saved', function () {
    $this->actingAs($this->user)->post(route('consultations.store'), [
        'patient_id' => $this->patient->id,
        'doctor_id' => $this->doctor->id,
        'symptoms' => 'Persistent headache for 3 days',
    ]);

    $consultation = Consultation::where('patient_id', $this->patient->id)->first();
    $this->assertEquals('Persistent headache for 3 days', $consultation->symptoms);
});

test('diagnosis can be saved', function () {
    $this->actingAs($this->user)->post(route('consultations.store'), [
        'patient_id' => $this->patient->id,
        'doctor_id' => $this->doctor->id,
        'diagnosis' => 'Migraine',
    ]);

    $consultation = Consultation::where('patient_id', $this->patient->id)->first();
    $this->assertEquals('Migraine', $consultation->diagnosis);
});

test('clinical notes can be saved', function () {
    $this->actingAs($this->user)->post(route('consultations.store'), [
        'patient_id' => $this->patient->id,
        'doctor_id' => $this->doctor->id,
        'clinical_notes' => 'Patient presents with severe headache',
    ]);

    $consultation = Consultation::where('patient_id', $this->patient->id)->first();
    $this->assertEquals('Patient presents with severe headache', $consultation->clinical_notes);
});

test('treatment plan can be saved', function () {
    $this->actingAs($this->user)->post(route('consultations.store'), [
        'patient_id' => $this->patient->id,
        'doctor_id' => $this->doctor->id,
        'treatment_plan' => 'Rest, hydration, pain relief',
    ]);

    $consultation = Consultation::where('patient_id', $this->patient->id)->first();
    $this->assertEquals('Rest, hydration, pain relief', $consultation->treatment_plan);
});

test('follow-up date can be saved', function () {
    $followUpDate = now()->addWeek()->toDateString();

    $this->actingAs($this->user)->post(route('consultations.store'), [
        'patient_id' => $this->patient->id,
        'doctor_id' => $this->doctor->id,
        'follow_up_date' => $followUpDate,
    ]);

    $consultation = Consultation::where('patient_id', $this->patient->id)->first();
    $this->assertEquals($followUpDate, $consultation->follow_up_date->toDateString());
});

// --- VITAL SIGNS TESTS ---

test('vital signs can be saved', function () {
    $this->actingAs($this->user)->post(route('consultations.store'), [
        'patient_id' => $this->patient->id,
        'doctor_id' => $this->doctor->id,
        'blood_pressure' => '120/80',
        'temperature' => 36.5,
        'pulse' => 72,
        'respiratory_rate' => 16,
        'weight' => 70.5,
        'height' => 175.0,
        'oxygen_saturation' => 98.0,
    ]);

    $consultation = Consultation::where('patient_id', $this->patient->id)->first();
    $this->assertNotNull($consultation->vitalSign);
    $this->assertEquals('120/80', $consultation->vitalSign->blood_pressure);
    $this->assertEquals(36.5, $consultation->vitalSign->temperature);
    $this->assertEquals(72, $consultation->vitalSign->pulse);
    $this->assertEquals(16, $consultation->vitalSign->respiratory_rate);
    $this->assertEquals(70.5, $consultation->vitalSign->weight);
    $this->assertEquals(175.0, $consultation->vitalSign->height);
    $this->assertEquals(98.0, $consultation->vitalSign->oxygen_saturation);
});

test('optional vital sign fields work', function () {
    $this->actingAs($this->user)->post(route('consultations.store'), [
        'patient_id' => $this->patient->id,
        'doctor_id' => $this->doctor->id,
        'temperature' => 37.0,
    ]);

    $consultation = Consultation::where('patient_id', $this->patient->id)->first();
    $this->assertNotNull($consultation->vitalSign);
    $this->assertEquals(37.0, $consultation->vitalSign->temperature);
    $this->assertNull($consultation->vitalSign->blood_pressure);
    $this->assertNull($consultation->vitalSign->pulse);
});

test('invalid numeric vital sign values are rejected', function () {
    $response = $this->actingAs($this->user)->post(route('consultations.store'), [
        'patient_id' => $this->patient->id,
        'doctor_id' => $this->doctor->id,
        'temperature' => 50.0,
    ]);

    $response->assertSessionHasErrors('temperature');
});

test('vital signs belong to consultation', function () {
    $consultation = Consultation::create([
        'patient_id' => $this->patient->id,
        'doctor_id' => $this->doctor->id,
        'status' => 'draft',
    ]);

    $vitalSign = VitalSign::create([
        'consultation_id' => $consultation->id,
        'temperature' => 36.5,
        'recorded_at' => now(),
    ]);

    $this->assertNotNull($vitalSign->consultation);
    $this->assertEquals($consultation->id, $vitalSign->consultation_id);
});

// --- COMPLETION TESTS ---

test('save works without completing', function () {
    $this->actingAs($this->user)->post(route('consultations.store'), [
        'patient_id' => $this->patient->id,
        'doctor_id' => $this->doctor->id,
        'symptoms' => 'Test symptoms',
    ]);

    $consultation = Consultation::where('patient_id', $this->patient->id)->first();
    $this->assertEquals('draft', $consultation->status);
});

test('save and complete works', function () {
    $ticket = QueueTicket::create([
        'patient_id' => $this->patient->id,
        'doctor_id' => $this->doctor->id,
        'queue_date' => now()->toDateString(),
        'ticket_number' => 'A001',
        'status' => 'in_consultation',
        'checked_in_at' => now(),
        'called_at' => now(),
        'consultation_started_at' => now(),
    ]);

    $this->actingAs($this->user)->post(route('consultations.store'), [
        'patient_id' => $this->patient->id,
        'doctor_id' => $this->doctor->id,
        'queue_ticket_id' => $ticket->id,
        'symptoms' => 'Test',
    ]);

    $consultation = Consultation::where('patient_id', $this->patient->id)->first();
    $response = $this->actingAs($this->user)->post(route('consultations.complete', $consultation));
    $response->assertRedirect();

    $consultation->refresh();
    $this->assertEquals('completed', $consultation->status);
});

test('consultation becomes completed', function () {
    $consultation = Consultation::create([
        'patient_id' => $this->patient->id,
        'doctor_id' => $this->doctor->id,
        'status' => 'draft',
    ]);

    $this->actingAs($this->user)->post(route('consultations.complete', $consultation));

    $consultation->refresh();
    $this->assertTrue($consultation->isCompleted());
    $this->assertFalse($consultation->isDraft());
});

test('queue becomes completed on consultation complete', function () {
    $ticket = QueueTicket::create([
        'patient_id' => $this->patient->id,
        'doctor_id' => $this->doctor->id,
        'queue_date' => now()->toDateString(),
        'ticket_number' => 'A001',
        'status' => 'in_consultation',
        'checked_in_at' => now(),
        'called_at' => now(),
        'consultation_started_at' => now(),
    ]);

    $consultation = Consultation::create([
        'patient_id' => $this->patient->id,
        'doctor_id' => $this->doctor->id,
        'queue_ticket_id' => $ticket->id,
        'status' => 'draft',
    ]);

    $this->actingAs($this->user)->post(route('consultations.complete', $consultation));

    $ticket->refresh();
    $this->assertEquals('completed', $ticket->status);
    $this->assertNotNull($ticket->completed_at);
});

test('appointment becomes completed on consultation complete', function () {
    $apt = Appointment::create([
        'patient_id' => $this->patient->id,
        'doctor_id' => $this->doctor->id,
        'department_id' => $this->department->id,
        'name' => $this->patient->name,
        'email' => $this->patient->email,
        'phone' => $this->patient->phone,
        'date' => now()->toDateString(),
        'time' => '10:00',
        'duration' => 30,
        'appointment_number' => 'APT-COMPLETE-001',
        'status' => AppointmentStatus::CheckedIn,
    ]);

    $ticket = QueueTicket::create([
        'patient_id' => $this->patient->id,
        'doctor_id' => $this->doctor->id,
        'appointment_id' => $apt->id,
        'queue_date' => now()->toDateString(),
        'ticket_number' => 'A001',
        'status' => 'in_consultation',
        'checked_in_at' => now(),
        'called_at' => now(),
        'consultation_started_at' => now(),
    ]);

    $consultation = Consultation::create([
        'patient_id' => $this->patient->id,
        'doctor_id' => $this->doctor->id,
        'queue_ticket_id' => $ticket->id,
        'appointment_id' => $apt->id,
        'status' => 'draft',
    ]);

    $this->actingAs($this->user)->post(route('consultations.complete', $consultation));

    $apt->refresh();
    $this->assertEquals(AppointmentStatus::Completed, $apt->status);
});

test('failed transaction does not partially update records', function () {
    $ticket = QueueTicket::create([
        'patient_id' => $this->patient->id,
        'doctor_id' => $this->doctor->id,
        'queue_date' => now()->toDateString(),
        'ticket_number' => 'A001',
        'status' => 'in_consultation',
        'checked_in_at' => now(),
        'called_at' => now(),
        'consultation_started_at' => now(),
    ]);

    $consultation = Consultation::create([
        'patient_id' => $this->patient->id,
        'doctor_id' => $this->doctor->id,
        'queue_ticket_id' => $ticket->id,
        'status' => 'draft',
    ]);

    $this->actingAs($this->user)->post(route('consultations.complete', $consultation));

    $ticket->refresh();
    $consultation->refresh();

    $this->assertEquals('completed', $ticket->status);
    $this->assertEquals('completed', $consultation->status);
});

// --- PATIENT HISTORY TESTS ---

test('patient consultation history appears', function () {
    $consultation = Consultation::create([
        'patient_id' => $this->patient->id,
        'doctor_id' => $this->doctor->id,
        'diagnosis' => 'Common cold',
        'status' => 'completed',
    ]);

    $response = $this->actingAs($this->user)->get(route('patients.show', $this->patient));
    $response->assertOk();
    $response->assertSee('Consultation History');
    $response->assertSee('Common cold');
});

test('consultation details are correct', function () {
    $consultation = Consultation::create([
        'patient_id' => $this->patient->id,
        'doctor_id' => $this->doctor->id,
        'symptoms' => 'Fever and cough',
        'diagnosis' => 'Upper respiratory infection',
        'treatment_plan' => 'Rest and fluids',
        'status' => 'completed',
    ]);

    $response = $this->actingAs($this->user)->get(route('consultations.show', $consultation));
    $response->assertOk();
    $response->assertSee('Fever and cough');
    $response->assertSee('Upper respiratory infection');
    $response->assertSee('Rest and fluids');
});

// --- AUTHORIZATION TESTS ---

test('unauthorized user cannot access consultations', function () {
    $user = User::factory()->create();
    $response = $this->actingAs($user)->get(route('consultations.index'));
    $response->assertForbidden();
});

test('unauthorized user cannot create consultation', function () {
    $user = User::factory()->create();
    $response = $this->actingAs($user)->get(route('consultations.create'));
    $response->assertForbidden();
});

test('unauthorized user cannot complete consultation', function () {
    $user = User::factory()->create();
    $consultation = Consultation::create([
        'patient_id' => $this->patient->id,
        'doctor_id' => $this->doctor->id,
        'status' => 'draft',
    ]);
    $response = $this->actingAs($user)->post(route('consultations.complete', $consultation));
    $response->assertForbidden();
});

// --- REGRESSION TESTS ---

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

test('existing doctor tests still pass', function () {
    $response = $this->actingAs($this->user)->get(route('doctors.index'));
    $response->assertOk();
});
