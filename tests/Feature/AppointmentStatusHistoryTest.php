<?php

use App\Enums\AppointmentStatus;
use App\Models\Appointment;
use App\Models\AppointmentStatusHistory;
use App\Models\Department;
use App\Models\Doctor;
use App\Models\Patient;
use App\Models\User;
use Spatie\Permission\Models\Permission;

beforeEach(function () {
    $this->permissions = [
        'doctor.view', 'doctor.create', 'doctor.edit', 'doctor.delete',
        'patient.view', 'patient.create', 'patient.edit', 'patient.delete',
        'appointment.view', 'appointment.create', 'appointment.edit', 'appointment.cancel',
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

function makeAppointment($status = AppointmentStatus::Scheduled, array $overrides = [])
{
    return Appointment::create(array_merge([
        'patient_id' => test()->patient->id,
        'doctor_id' => test()->doctor->id,
        'department_id' => test()->department->id,
        'name' => test()->patient->name,
        'email' => test()->patient->email,
        'phone' => test()->patient->phone,
        'date' => now()->addDays(5)->toDateString(),
        'time' => '10:00',
        'duration' => 30,
        'appointment_number' => 'APT-HIST-' . str_pad((string) random_int(1, 999999), 6, '0', STR_PAD_LEFT),
        'status' => $status,
    ], $overrides));
}

// --- INITIAL HISTORY ---

test('appointment creation records an initial status history', function () {
    $apt = makeAppointment(AppointmentStatus::Scheduled);

    $this->assertDatabaseHas('appointment_status_histories', [
        'appointment_id' => $apt->id,
        'from_status' => null,
        'to_status' => AppointmentStatus::Scheduled->value,
        'changed_by' => null,
    ]);
});

test('public appointment request records a pending initial history', function () {
    $apt = makeAppointment(AppointmentStatus::Pending, ['source' => 'public', 'appointment_number' => null]);

    $this->assertDatabaseHas('appointment_status_histories', [
        'appointment_id' => $apt->id,
        'to_status' => AppointmentStatus::Pending->value,
        'note' => 'Appointment request created.',
    ]);
});

// --- STATUS CHANGE VIA MODAL (appointments.status) ---

test('pending appointment can be confirmed through the status endpoint', function () {
    $apt = makeAppointment(AppointmentStatus::Pending);

    $response = $this->actingAs($this->user)->post(route('appointments.status', $apt), [
        'status' => AppointmentStatus::Confirmed->value,
        'note' => 'Confirmed by front desk.',
    ]);

    $response->assertRedirect();
    $apt->refresh();
    $this->assertEquals(AppointmentStatus::Confirmed, $apt->status);
    $this->assertDatabaseHas('appointment_status_histories', [
        'appointment_id' => $apt->id,
        'from_status' => AppointmentStatus::Pending->value,
        'to_status' => AppointmentStatus::Confirmed->value,
        'note' => 'Confirmed by front desk.',
        'changed_by' => $this->user->id,
    ]);
});

test('scheduled appointment can be confirmed and gets an appointment number', function () {
    $apt = makeAppointment(AppointmentStatus::Scheduled, ['appointment_number' => null]);

    $this->actingAs($this->user)->post(route('appointments.status', $apt), [
        'status' => AppointmentStatus::Confirmed->value,
    ]);

    $apt->refresh();
    $this->assertEquals(AppointmentStatus::Confirmed, $apt->status);
    $this->assertNotNull($apt->appointment_number);
});

test('rejecting a pending appointment requires a note', function () {
    $apt = makeAppointment(AppointmentStatus::Pending);

    $response = $this->actingAs($this->user)->post(route('appointments.status', $apt), [
        'status' => AppointmentStatus::Cancelled->value,
    ]);

    $response->assertSessionHasErrors('note');
    $apt->refresh();
    $this->assertEquals(AppointmentStatus::Pending, $apt->status);
    $this->assertDatabaseMissing('appointment_status_histories', [
        'appointment_id' => $apt->id,
        'to_status' => AppointmentStatus::Cancelled->value,
    ]);
});

test('rejecting a pending appointment with a note records history and cancel reason', function () {
    $apt = makeAppointment(AppointmentStatus::Pending);

    $response = $this->actingAs($this->user)->post(route('appointments.status', $apt), [
        'status' => AppointmentStatus::Cancelled->value,
        'note' => 'No available slot.',
    ]);

    $response->assertRedirect();
    $apt->refresh();
    $this->assertEquals(AppointmentStatus::Cancelled, $apt->status);
    $this->assertEquals('No available slot.', $apt->cancel_reason);
    $this->assertDatabaseHas('appointment_status_histories', [
        'appointment_id' => $apt->id,
        'from_status' => AppointmentStatus::Pending->value,
        'to_status' => AppointmentStatus::Cancelled->value,
        'note' => 'No available slot.',
        'changed_by' => $this->user->id,
    ]);
});

test('confirmed appointment can be cancelled through the status endpoint', function () {
    $apt = makeAppointment(AppointmentStatus::Confirmed);

    $this->actingAs($this->user)->post(route('appointments.status', $apt), [
        'status' => AppointmentStatus::Cancelled->value,
        'note' => 'Patient canceled.',
    ]);

    $apt->refresh();
    $this->assertEquals(AppointmentStatus::Cancelled, $apt->status);
    $this->assertDatabaseHas('appointment_status_histories', [
        'appointment_id' => $apt->id,
        'from_status' => AppointmentStatus::Confirmed->value,
        'to_status' => AppointmentStatus::Cancelled->value,
    ]);
});

test('confirmed appointment can be completed through the status endpoint', function () {
    $apt = makeAppointment(AppointmentStatus::Confirmed);

    $this->actingAs($this->user)->post(route('appointments.status', $apt), [
        'status' => AppointmentStatus::Completed->value,
        'note' => 'Visit completed.',
    ]);

    $apt->refresh();
    $this->assertEquals(AppointmentStatus::Completed, $apt->status);
    $this->assertDatabaseHas('appointment_status_histories', [
        'appointment_id' => $apt->id,
        'from_status' => AppointmentStatus::Confirmed->value,
        'to_status' => AppointmentStatus::Completed->value,
    ]);
});

test('invalid status transition is rejected without changing anything', function () {
    $apt = makeAppointment(AppointmentStatus::Pending);

    $response = $this->actingAs($this->user)->post(route('appointments.status', $apt), [
        'status' => AppointmentStatus::Completed->value,
        'note' => 'Skip ahead',
    ]);

    $response->assertSessionHasErrors('status');
    $apt->refresh();
    $this->assertEquals(AppointmentStatus::Pending, $apt->status);
    $this->assertDatabaseMissing('appointment_status_histories', [
        'appointment_id' => $apt->id,
        'to_status' => AppointmentStatus::Completed->value,
    ]);
});

test('terminal appointments have no further transitions', function () {
    $cancelled = makeAppointment(AppointmentStatus::Cancelled);
    $completed = makeAppointment(AppointmentStatus::Completed);

    $response = $this->actingAs($this->user)->post(route('appointments.status', $cancelled), [
        'status' => AppointmentStatus::Scheduled->value,
        'note' => 'Reopen',
    ]);
    $response->assertSessionHasErrors('status');

    $response = $this->actingAs($this->user)->post(route('appointments.status', $completed), [
        'status' => AppointmentStatus::Pending->value,
        'note' => 'Reopen',
    ]);
    $response->assertSessionHasErrors('status');
});

test('invalid status value is rejected', function () {
    $apt = makeAppointment(AppointmentStatus::Pending);

    $response = $this->actingAs($this->user)->post(route('appointments.status', $apt), [
        'status' => 'not-a-real-status',
    ]);

    $response->assertSessionHasErrors('status');
});

// --- AJAX / JSON RESPONSES (status modal fetch) ---

test('status endpoint returns JSON success for ajax request', function () {
    $apt = makeAppointment(AppointmentStatus::Pending);

    $response = $this->actingAs($this->user)->postJson(route('appointments.status', $apt), [
        'status' => AppointmentStatus::Confirmed->value,
        'note' => 'Confirmed by front desk.',
    ]);

    $response->assertOk()
        ->assertJson([
            'message' => 'Appointment marked as Confirmed.',
            'status' => AppointmentStatus::Confirmed->value,
            'label' => 'Confirmed',
        ]);

    $apt->refresh();
    $this->assertEquals(AppointmentStatus::Confirmed, $apt->status);
    $this->assertDatabaseHas('appointment_status_histories', [
        'appointment_id' => $apt->id,
        'from_status' => AppointmentStatus::Pending->value,
        'to_status' => AppointmentStatus::Confirmed->value,
        'changed_by' => $this->user->id,
    ]);
});

test('status endpoint returns JSON error for invalid transition on ajax', function () {
    $apt = makeAppointment(AppointmentStatus::Pending);

    $response = $this->actingAs($this->user)->postJson(route('appointments.status', $apt), [
        'status' => AppointmentStatus::Completed->value,
        'note' => 'Skip ahead',
    ]);

    $response->assertStatus(422)
        ->assertJson(['message' => 'Cannot change appointment from Pending to Completed.'])
        ->assertJsonStructure(['message', 'errors']);

    $apt->refresh();
    $this->assertEquals(AppointmentStatus::Pending, $apt->status);
    $this->assertDatabaseMissing('appointment_status_histories', [
        'appointment_id' => $apt->id,
        'to_status' => AppointmentStatus::Completed->value,
    ]);
});

test('status endpoint returns JSON error when cancellation note missing on ajax', function () {
    $apt = makeAppointment(AppointmentStatus::Pending);

    $response = $this->actingAs($this->user)->postJson(route('appointments.status', $apt), [
        'status' => AppointmentStatus::Cancelled->value,
    ]);

    $response->assertStatus(422)
        ->assertJson(['message' => 'A cancellation/rejection reason is required.'])
        ->assertJsonStructure(['message', 'errors']);

    $this->assertEquals(AppointmentStatus::Pending, $apt->refresh()->status);
});

test('status endpoint returns JSON validation error for invalid status value on ajax', function () {
    $apt = makeAppointment(AppointmentStatus::Pending);

    $response = $this->actingAs($this->user)->postJson(route('appointments.status', $apt), [
        'status' => 'not-a-real-status',
    ]);

    $response->assertStatus(422)->assertJsonStructure(['message', 'errors']);
});

test('status endpoint returns 403 JSON for unauthorized ajax request', function () {
    $user = User::factory()->create();
    $apt = makeAppointment(AppointmentStatus::Pending);

    $response = $this->actingAs($user)->postJson(route('appointments.status', $apt), [
        'status' => AppointmentStatus::Confirmed->value,
    ]);

    $response->assertForbidden();
    $this->assertEquals(AppointmentStatus::Pending, $apt->refresh()->status);
});

test('terminal appointments reject transitions with JSON error on ajax', function () {
    $completed = makeAppointment(AppointmentStatus::Completed);

    $response = $this->actingAs($this->user)->postJson(route('appointments.status', $completed), [
        'status' => AppointmentStatus::Pending->value,
        'note' => 'Reopen',
    ]);

    $response->assertStatus(422)->assertJsonStructure(['message', 'errors']);
    $this->assertEquals(AppointmentStatus::Completed, $completed->refresh()->status);
});

// --- STATUS CHANGE VIA EXISTING ROUTES ---

test('confirm route records history', function () {
    $apt = makeAppointment(AppointmentStatus::Scheduled);

    $this->actingAs($this->user)->post(route('appointments.confirm', $apt));

    $this->assertDatabaseHas('appointment_status_histories', [
        'appointment_id' => $apt->id,
        'from_status' => AppointmentStatus::Scheduled->value,
        'to_status' => AppointmentStatus::Confirmed->value,
    ]);
});

test('cancel route requires a reason and records the cancel reason in history', function () {
    $apt = makeAppointment(AppointmentStatus::Scheduled);

    $response = $this->actingAs($this->user)->post(route('appointments.cancel', $apt));
    $response->assertSessionHasErrors('cancel_reason');
    $this->assertEquals(AppointmentStatus::Scheduled, $apt->refresh()->status);

    $this->actingAs($this->user)->post(route('appointments.cancel', $apt), [
        'cancel_reason' => 'Patient request',
    ]);

    $apt->refresh();
    $this->assertEquals(AppointmentStatus::Cancelled, $apt->status);
    $this->assertDatabaseHas('appointment_status_histories', [
        'appointment_id' => $apt->id,
        'to_status' => AppointmentStatus::Cancelled->value,
        'note' => 'Patient request',
        'changed_by' => $this->user->id,
    ]);
});

test('complete route records history', function () {
    $apt = makeAppointment(AppointmentStatus::Confirmed);

    $this->actingAs($this->user)->post(route('appointments.complete', $apt));

    $this->assertDatabaseHas('appointment_status_histories', [
        'appointment_id' => $apt->id,
        'from_status' => AppointmentStatus::Confirmed->value,
        'to_status' => AppointmentStatus::Completed->value,
    ]);
});

test('terminal appointment hides change status button and renders the no-changes message', function () {
    $completed = makeAppointment(AppointmentStatus::Completed);
    $cancelled = makeAppointment(AppointmentStatus::Cancelled);

    foreach ([$completed, $cancelled] as $apt) {
        $response = $this->actingAs($this->user)->get(route('appointments.show', $apt));
        $response->assertOk();
        $response->assertDontSee('btn btn-primary w-100');
        $response->assertSee('No further status changes are available for this appointment.');
    }
});

test('modal is hidden by default, uses x-cloak, and does not rely on bootstrap d-block', function () {
    $apt = makeAppointment(AppointmentStatus::Confirmed);

    $html = $this->actingAs($this->user)
        ->get(route('appointments.show', $apt))
        ->assertOk()
        ->getContent();

    expect($html)->toContain('x-data="');
    expect($html)->toContain('open: false');
    expect($html)->toContain('x-show="open"');
    expect($html)->toContain('x-cloak');
    expect($html)->toContain('[x-cloak]');

    preg_match('/<div x-show="open" x-cloak class="([^"]+)"/', $html, $m);
    expect($m[1] ?? '')->toContain('appointment-status-modal');
    expect($m[1] ?? '')->not->toContain('d-block');

    preg_match('/x-data="(.*?)"\s*x-init/s', $html, $m);
    expect((count(explode('"', $m[1] ?? '')) - 1))->toBe(0, 'x-data must not contain raw double quotes');
});

// --- HISTORY DISPLAY ---

test('status history appears on the appointment details page', function () {
    $apt = makeAppointment(AppointmentStatus::Pending);

    $this->actingAs($this->user)->post(route('appointments.status', $apt), [
        'status' => AppointmentStatus::Confirmed->value,
        'note' => 'Confirmed by front desk.',
    ]);

    $response = $this->actingAs($this->user)->get(route('appointments.show', $apt));
    $response->assertOk();
    $response->assertSee('Status History');
    $response->assertSee('Confirmed by front desk.');
});

// --- RELATIONSHIPS ---

test('appointment status history belongs to appointment and user', function () {
    $apt = makeAppointment(AppointmentStatus::Pending);

    $this->actingAs($this->user)->post(route('appointments.status', $apt), [
        'status' => AppointmentStatus::Confirmed->value,
        'note' => 'Done',
    ]);

    $history = AppointmentStatusHistory::where('appointment_id', $apt->id)
        ->whereNotNull('from_status')
        ->firstOrFail();

    $this->assertEquals($apt->id, $history->appointment->id);
    $this->assertEquals($this->user->id, $history->changedBy->id);
    $this->assertEquals('Pending', $history->from_label);
});

// --- AUTHORIZATION ---

test('unauthorized user cannot change appointment status', function () {
    $user = User::factory()->create();
    $apt = makeAppointment(AppointmentStatus::Pending);

    $response = $this->actingAs($user)->post(route('appointments.status', $apt), [
        'status' => AppointmentStatus::Confirmed->value,
    ]);

    $response->assertForbidden();
    $this->assertEquals(AppointmentStatus::Pending, $apt->refresh()->status);
});

test('guest cannot change appointment status', function () {
    $apt = makeAppointment(AppointmentStatus::Pending);

    $response = $this->post(route('appointments.status', $apt), [
        'status' => AppointmentStatus::Confirmed->value,
    ]);

    $response->assertRedirect(route('login'));
});