<?php

use App\Models\Communication;
use App\Models\Patient;
use App\Models\User;
use Spatie\Permission\Models\Permission;

beforeEach(function () {
    $this->permissions = [
        'communication.view', 'communication.create', 'communication.edit', 'communication.delete',
    ];

    foreach ($this->permissions as $perm) {
        Permission::firstOrCreate(['name' => $perm, 'guard_name' => 'web']);
    }

    $this->user = User::factory()->create();
    $this->user->givePermissionTo($this->permissions);

    $this->patient = Patient::create([
        'name' => 'John Doe',
        'patient_number' => 'P-000001',
        'date_of_birth' => '1990-01-01',
        'gender' => 'male',
        'phone' => '1234567890',
    ]);

    $this->communication = Communication::create([
        'patient_id' => $this->patient->id,
        'user_id' => $this->user->id,
        'contact_method' => 'phone',
        'purpose' => 'appointment_confirmation',
        'outcome' => 'contacted',
        'contacted_at' => now(),
        'note' => 'Confirmed appointment with patient.',
    ]);
});

// --- INDEX ---

test('communication index page loads successfully', function () {
    $response = $this->actingAs($this->user)->get(route('communications.index'));
    $response->assertOk();
});

test('communication index shows communications', function () {
    $response = $this->actingAs($this->user)->get(route('communications.index'));
    $response->assertOk();
    $response->assertSee('John Doe');
    $response->assertSee('Phone');
    $response->assertSee('Appointment Confirmation');
});

test('communication index filters by patient', function () {
    $patient2 = Patient::create([
        'name' => 'Jane Smith',
        'patient_number' => 'P-000002',
        'date_of_birth' => '1985-05-15',
        'gender' => 'female',
    ]);

    Communication::create([
        'patient_id' => $patient2->id,
        'user_id' => $this->user->id,
        'contact_method' => 'email',
        'purpose' => 'general',
        'outcome' => 'contacted',
        'contacted_at' => now(),
    ]);

    $response = $this->actingAs($this->user)->get(route('communications.index', ['patient_id' => $this->patient->id]));
    $response->assertOk();
    $response->assertSee('1 communication(s)');
    $response->assertSee('John Doe');
});

test('communication index filters by contact method', function () {
    Communication::create([
        'patient_id' => $this->patient->id,
        'user_id' => $this->user->id,
        'contact_method' => 'email',
        'purpose' => 'general',
        'outcome' => 'contacted',
        'contacted_at' => now(),
    ]);

    $response = $this->actingAs($this->user)->get(route('communications.index', ['contact_method' => 'phone']));
    $response->assertOk();
    $response->assertSee('1 communication(s)');
    $response->assertSee('Appointment Confirmation');
});

test('communication index requires view permission', function () {
    $user = User::factory()->create();
    $response = $this->actingAs($user)->get(route('communications.index'));
    $response->assertForbidden();
});

// --- STORE ---

test('communication can be created', function () {
    $response = $this->actingAs($this->user)->post(route('communications.store'), [
        'patient_id' => $this->patient->id,
        'contact_method' => 'sms',
        'purpose' => 'reminder',
        'outcome' => 'confirmed',
        'contacted_at' => now()->toDateTimeString(),
        'note' => 'Sent appointment reminder via SMS.',
    ]);

    $response->assertRedirect();
    $this->assertDatabaseHas('communications', [
        'patient_id' => $this->patient->id,
        'contact_method' => 'sms',
        'purpose' => 'reminder',
        'outcome' => 'confirmed',
        'user_id' => $this->user->id,
    ]);
});

test('communication store validates required fields', function () {
    $response = $this->actingAs($this->user)->post(route('communications.store'), []);
    $response->assertSessionHasErrors(['patient_id', 'contact_method', 'purpose', 'outcome', 'contacted_at']);
});

test('communication store with follow-up', function () {
    $response = $this->actingAs($this->user)->post(route('communications.store'), [
        'patient_id' => $this->patient->id,
        'contact_method' => 'phone',
        'purpose' => 'follow_up',
        'outcome' => 'callback_requested',
        'contacted_at' => now()->toDateTimeString(),
        'follow_up_date' => now()->addDays(3)->toDateString(),
        'follow_up_note' => 'Call patient back about test results.',
    ]);

    $response->assertRedirect();
    $this->assertDatabaseHas('communications', [
        'patient_id' => $this->patient->id,
        'follow_up_note' => 'Call patient back about test results.',
        'follow_up_completed' => false,
    ]);
});

test('communication store with appointment', function () {
    $department = \App\Models\Department::create(['name' => 'General', 'slug' => 'general']);
    $doctor = \App\Models\Doctor::create([
        'name' => 'Dr. Smith',
        'slug' => 'dr-smith',
        'department_id' => $department->id,
        'is_available' => true,
        'available_days' => [1, 2, 3, 4, 5],
        'start_time' => '09:00:00',
        'end_time' => '17:00:00',
        'user_id' => $this->user->id,
        'consultation_fee' => 100.00,
    ]);

    $appointment = \App\Models\Appointment::create([
        'patient_id' => $this->patient->id,
        'appointment_number' => 'APT-20260825-0001',
        'name' => 'John Doe',
        'phone' => '1234567890',
        'date' => now()->addDay()->toDateString(),
        'time' => '10:00:00',
        'duration' => 30,
        'department_id' => $department->id,
        'doctor_id' => $doctor->id,
        'status' => 'scheduled',
    ]);

    $response = $this->actingAs($this->user)->post(route('communications.store'), [
        'patient_id' => $this->patient->id,
        'appointment_id' => $appointment->id,
        'contact_method' => 'phone',
        'purpose' => 'appointment_confirmation',
        'outcome' => 'confirmed',
        'contacted_at' => now()->toDateTimeString(),
    ]);

    $response->assertRedirect();
    $this->assertDatabaseHas('communications', [
        'patient_id' => $this->patient->id,
        'appointment_id' => $appointment->id,
    ]);
});

test('communication store requires create permission', function () {
    $user = User::factory()->create();
    $response = $this->actingAs($user)->post(route('communications.store'), [
        'patient_id' => $this->patient->id,
        'contact_method' => 'phone',
        'purpose' => 'general',
        'outcome' => 'contacted',
        'contacted_at' => now()->toDateTimeString(),
    ]);
    $response->assertForbidden();
});

test('communication store validates contact method', function () {
    $response = $this->actingAs($this->user)->post(route('communications.store'), [
        'patient_id' => $this->patient->id,
        'contact_method' => 'invalid_method',
        'purpose' => 'general',
        'outcome' => 'contacted',
        'contacted_at' => now()->toDateTimeString(),
    ]);
    $response->assertSessionHasErrors(['contact_method']);
});

test('communication store validates purpose', function () {
    $response = $this->actingAs($this->user)->post(route('communications.store'), [
        'patient_id' => $this->patient->id,
        'contact_method' => 'phone',
        'purpose' => 'invalid_purpose',
        'outcome' => 'contacted',
        'contacted_at' => now()->toDateTimeString(),
    ]);
    $response->assertSessionHasErrors(['purpose']);
});

// --- SHOW ---

test('communication show page loads', function () {
    $response = $this->actingAs($this->user)->get(route('communications.show', $this->communication));
    $response->assertOk();
    $response->assertSee('Confirmed appointment with patient');
    $response->assertSee('John Doe');
});

test('communication show requires view permission', function () {
    $user = User::factory()->create();
    $response = $this->actingAs($user)->get(route('communications.show', $this->communication));
    $response->assertForbidden();
});

// --- UPDATE ---

test('communication can be updated', function () {
    $response = $this->actingAs($this->user)->put(route('communications.update', $this->communication), [
        'contact_method' => 'email',
        'purpose' => 'general',
        'outcome' => 'informed',
        'contacted_at' => $this->communication->contacted_at->toDateTimeString(),
        'note' => 'Updated note.',
    ]);

    $response->assertRedirect();
    $this->assertDatabaseHas('communications', [
        'id' => $this->communication->id,
        'contact_method' => 'email',
        'note' => 'Updated note.',
    ]);
});

test('communication update requires edit permission', function () {
    $user = User::factory()->create();
    $response = $this->actingAs($user)->put(route('communications.update', $this->communication), [
        'contact_method' => 'email',
        'purpose' => 'general',
        'outcome' => 'informed',
        'contacted_at' => $this->communication->contacted_at->toDateTimeString(),
    ]);
    $response->assertForbidden();
});

// --- DELETE ---

test('communication can be deleted', function () {
    $response = $this->actingAs($this->user)->delete(route('communications.destroy', $this->communication));
    $response->assertRedirect();
    $this->assertDatabaseMissing('communications', ['id' => $this->communication->id]);
});

test('communication delete requires delete permission', function () {
    $user = User::factory()->create();
    $response = $this->actingAs($user)->delete(route('communications.destroy', $this->communication));
    $response->assertForbidden();
});

// --- FOLLOW-UP ---

test('follow-up can be marked as completed', function () {
    $this->communication->update([
        'follow_up_date' => now()->addDays(3)->toDateString(),
        'follow_up_note' => 'Call patient back.',
        'follow_up_completed' => false,
    ]);

    $response = $this->actingAs($this->user)->post(route('communications.complete-follow-up', $this->communication));
    $response->assertRedirect();

    $this->assertDatabaseHas('communications', [
        'id' => $this->communication->id,
        'follow_up_completed' => true,
    ]);
});

test('follow-ups page loads', function () {
    $this->communication->update([
        'follow_up_date' => now()->addDays(3)->toDateString(),
        'follow_up_note' => 'Call patient back.',
    ]);

    $response = $this->actingAs($this->user)->get(route('communications.follow-ups'));
    $response->assertOk();
    $response->assertSee('Follow-ups');
});

test('follow-ups page filters overdue', function () {
    $this->communication->update([
        'follow_up_date' => now()->subDay()->toDateString(),
        'follow_up_note' => 'Overdue follow-up.',
    ]);

    $response = $this->actingAs($this->user)->get(route('communications.follow-ups', ['filter' => 'overdue']));
    $response->assertOk();
    $response->assertSee('Overdue');
});

test('follow-ups page filters completed', function () {
    $this->communication->update([
        'follow_up_date' => now()->subDay()->toDateString(),
        'follow_up_note' => 'Completed follow-up.',
        'follow_up_completed' => true,
    ]);

    $response = $this->actingAs($this->user)->get(route('communications.follow-ups', ['filter' => 'completed']));
    $response->assertOk();
    $response->assertSee('Completed follow-up.');
    $response->assertSee('Appointment Confirmation');
});

test('patient communications page loads', function () {
    $response = $this->actingAs($this->user)->get(route('communications.patient', $this->patient));
    $response->assertOk();
    $response->assertSee('John Doe');
    $response->assertSee('Communication History');
});

// --- MODEL TESTS ---

test('communication model has correct contact methods', function () {
    $this->assertEquals('Phone', $this->communication->contact_method_label);
    $this->assertEquals('Appointment Confirmation', $this->communication->purpose_label);
    $this->assertEquals('Contacted', $this->communication->outcome_label);
});

test('communication model badge classes', function () {
    $this->assertEquals('bg-primary', $this->communication->getContactMethodBadgeClass());
    $this->assertEquals('bg-success', $this->communication->getPurposeBadgeClass());
    $this->assertEquals('bg-success', $this->communication->getOutcomeBadgeClass());
});

test('communication follow-up status methods', function () {
    // No follow-up
    $this->assertFalse($this->communication->isFollowUpPending());
    $this->assertFalse($this->communication->isFollowUpOverdue());

    // Pending follow-up (future)
    $this->communication->update(['follow_up_date' => now()->addDays(3)->toDateString()]);
    $this->assertTrue($this->communication->isFollowUpPending());
    $this->assertFalse($this->communication->isFollowUpOverdue());

    // Overdue follow-up
    $this->communication->update(['follow_up_date' => now()->subDay()->toDateString()]);
    $this->assertTrue($this->communication->isFollowUpPending());
    $this->assertTrue($this->communication->isFollowUpOverdue());

    // Completed follow-up
    $this->communication->update(['follow_up_completed' => true]);
    $this->assertFalse($this->communication->isFollowUpPending());
    $this->assertFalse($this->communication->isFollowUpOverdue());
});

test('communication relationships', function () {
    $this->assertNotNull($this->communication->patient);
    $this->assertEquals($this->patient->id, $this->communication->patient->id);

    $this->assertNotNull($this->communication->user);
    $this->assertEquals($this->user->id, $this->communication->user->id);
});

test('patient model has communications relationship', function () {
    $this->assertNotNull($this->patient->communications);
    $this->assertEquals(1, $this->patient->communications->count());
});
