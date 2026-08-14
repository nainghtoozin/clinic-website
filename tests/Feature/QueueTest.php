<?php

use App\Enums\AppointmentStatus;
use App\Models\Appointment;
use App\Models\Department;
use App\Models\Doctor;
use App\Models\Patient;
use App\Models\QueueTicket;
use App\Models\User;
use Spatie\Permission\Models\Permission;

beforeEach(function () {
    $this->permissions = [
        'doctor.view', 'doctor.create', 'doctor.edit', 'doctor.delete',
        'patient.view', 'patient.create', 'patient.edit', 'patient.delete',
        'appointment.view', 'appointment.create', 'appointment.edit', 'appointment.cancel',
        'queue.view', 'queue.checkin', 'queue.call', 'queue.consult', 'queue.cancel',
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

// --- CHECK-IN TESTS ---

test('scheduled appointment can be checked in', function () {
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
        'appointment_number' => 'APT-CHECK-001',
        'status' => AppointmentStatus::Scheduled,
    ]);

    $response = $this->actingAs($this->user)->post(route('queue.checkin'), [
        'appointment_id' => $apt->id,
    ]);

    $response->assertRedirect(route('queue.index'));
    $this->assertDatabaseHas('queue_tickets', [
        'appointment_id' => $apt->id,
        'patient_id' => $this->patient->id,
        'doctor_id' => $this->doctor->id,
        'status' => 'waiting',
    ]);
    $apt->refresh();
    $this->assertEquals(AppointmentStatus::CheckedIn, $apt->status);
});

test('confirmed appointment can be checked in', function () {
    $apt = Appointment::create([
        'patient_id' => $this->patient->id,
        'doctor_id' => $this->doctor->id,
        'department_id' => $this->department->id,
        'name' => $this->patient->name,
        'email' => $this->patient->email,
        'phone' => $this->patient->phone,
        'date' => now()->toDateString(),
        'time' => '11:00',
        'duration' => 30,
        'appointment_number' => 'APT-CHECK-002',
        'status' => AppointmentStatus::Confirmed,
    ]);

    $response = $this->actingAs($this->user)->post(route('queue.checkin'), [
        'appointment_id' => $apt->id,
    ]);

    $response->assertRedirect(route('queue.index'));
    $apt->refresh();
    $this->assertEquals(AppointmentStatus::CheckedIn, $apt->status);
});

test('cancelled appointment cannot be checked in', function () {
    $apt = Appointment::create([
        'patient_id' => $this->patient->id,
        'doctor_id' => $this->doctor->id,
        'department_id' => $this->department->id,
        'name' => $this->patient->name,
        'email' => $this->patient->email,
        'phone' => $this->patient->phone,
        'date' => now()->toDateString(),
        'time' => '12:00',
        'duration' => 30,
        'appointment_number' => 'APT-CHECK-003',
        'status' => AppointmentStatus::Cancelled,
    ]);

    $response = $this->actingAs($this->user)->post(route('queue.checkin'), [
        'appointment_id' => $apt->id,
    ]);

    $response->assertSessionHas('error');
    $this->assertDatabaseMissing('queue_tickets', [
        'appointment_id' => $apt->id,
    ]);
});

test('completed appointment cannot be checked in', function () {
    $apt = Appointment::create([
        'patient_id' => $this->patient->id,
        'doctor_id' => $this->doctor->id,
        'department_id' => $this->department->id,
        'name' => $this->patient->name,
        'email' => $this->patient->email,
        'phone' => $this->patient->phone,
        'date' => now()->toDateString(),
        'time' => '13:00',
        'duration' => 30,
        'appointment_number' => 'APT-CHECK-004',
        'status' => AppointmentStatus::Completed,
    ]);

    $response = $this->actingAs($this->user)->post(route('queue.checkin'), [
        'appointment_id' => $apt->id,
    ]);

    $response->assertSessionHas('error');
    $this->assertDatabaseMissing('queue_tickets', [
        'appointment_id' => $apt->id,
    ]);
});

test('duplicate check-in is prevented', function () {
    $apt = Appointment::create([
        'patient_id' => $this->patient->id,
        'doctor_id' => $this->doctor->id,
        'department_id' => $this->department->id,
        'name' => $this->patient->name,
        'email' => $this->patient->email,
        'phone' => $this->patient->phone,
        'date' => now()->toDateString(),
        'time' => '14:00',
        'duration' => 30,
        'appointment_number' => 'APT-CHECK-005',
        'status' => AppointmentStatus::Scheduled,
    ]);

    $this->actingAs($this->user)->post(route('queue.checkin'), [
        'appointment_id' => $apt->id,
    ]);

    $response = $this->actingAs($this->user)->post(route('queue.checkin'), [
        'appointment_id' => $apt->id,
    ]);

    $response->assertSessionHas('error');
});

test('queue ticket is created with correct number', function () {
    $apt = Appointment::create([
        'patient_id' => $this->patient->id,
        'doctor_id' => $this->doctor->id,
        'department_id' => $this->department->id,
        'name' => $this->patient->name,
        'email' => $this->patient->email,
        'phone' => $this->patient->phone,
        'date' => now()->toDateString(),
        'time' => '15:00',
        'duration' => 30,
        'appointment_number' => 'APT-CHECK-006',
        'status' => AppointmentStatus::Scheduled,
    ]);

    $this->actingAs($this->user)->post(route('queue.checkin'), [
        'appointment_id' => $apt->id,
    ]);

    $ticket = QueueTicket::where('appointment_id', $apt->id)->first();
    $this->assertNotNull($ticket);
    $this->assertMatchesRegularExpression('/^A\d{3}$/', $ticket->ticket_number);
    $this->assertEquals(now()->toDateString(), $ticket->queue_date->toDateString());
});

// --- WALK-IN TESTS ---

test('existing patient can join as walk-in', function () {
    $response = $this->actingAs($this->user)->post(route('queue.walkin'), [
        'patient_id' => $this->patient->id,
        'doctor_id' => $this->doctor->id,
    ]);

    $response->assertRedirect(route('queue.index'));
    $this->assertDatabaseHas('queue_tickets', [
        'patient_id' => $this->patient->id,
        'doctor_id' => $this->doctor->id,
        'appointment_id' => null,
        'status' => 'waiting',
    ]);
});

test('walk-in queue has null appointment_id', function () {
    $this->actingAs($this->user)->post(route('queue.walkin'), [
        'patient_id' => $this->patient->id,
        'doctor_id' => $this->doctor->id,
    ]);

    $ticket = QueueTicket::where('patient_id', $this->patient->id)->first();
    $this->assertNull($ticket->appointment_id);
});

test('walk-in receives queue ticket', function () {
    $this->actingAs($this->user)->post(route('queue.walkin'), [
        'patient_id' => $this->patient->id,
        'doctor_id' => $this->doctor->id,
    ]);

    $ticket = QueueTicket::where('patient_id', $this->patient->id)->first();
    $this->assertNotNull($ticket);
    $this->assertNotNull($ticket->ticket_number);
    $this->assertNotNull($ticket->checked_in_at);
});

test('walk-in with notes works', function () {
    $this->actingAs($this->user)->post(route('queue.walkin'), [
        'patient_id' => $this->patient->id,
        'doctor_id' => $this->doctor->id,
        'notes' => 'Follow-up visit',
    ]);

    $ticket = QueueTicket::where('patient_id', $this->patient->id)->first();
    $this->assertEquals('Follow-up visit', $ticket->notes);
});

// --- QUEUE TESTS ---

test('waiting ticket appears in today queue', function () {
    $ticket = QueueTicket::create([
        'patient_id' => $this->patient->id,
        'doctor_id' => $this->doctor->id,
        'queue_date' => now()->toDateString(),
        'ticket_number' => 'A001',
        'status' => 'waiting',
        'checked_in_at' => now(),
    ]);

    $response = $this->actingAs($this->user)->get(route('queue.index'));
    $response->assertOk();
    $response->assertSee('A001');
    $response->assertSee($this->patient->name);
});

test('queue number is unique per day', function () {
    $ticket1 = QueueTicket::create([
        'patient_id' => $this->patient->id,
        'doctor_id' => $this->doctor->id,
        'queue_date' => now()->toDateString(),
        'ticket_number' => 'A005',
        'status' => 'waiting',
        'checked_in_at' => now(),
    ]);

    $this->expectException(\Illuminate\Database\UniqueConstraintViolationException::class);

    QueueTicket::create([
        'patient_id' => $this->patient->id,
        'doctor_id' => $this->doctor->id,
        'queue_date' => now()->toDateString(),
        'ticket_number' => 'A005',
        'status' => 'waiting',
        'checked_in_at' => now(),
    ]);
});

test('queue is ordered correctly', function () {
    $t1 = QueueTicket::create([
        'patient_id' => $this->patient->id,
        'doctor_id' => $this->doctor->id,
        'queue_date' => now()->toDateString(),
        'ticket_number' => 'A003',
        'status' => 'waiting',
        'checked_in_at' => now()->subMinutes(5),
    ]);

    $t2 = QueueTicket::create([
        'patient_id' => $this->patient->id,
        'doctor_id' => $this->doctor->id,
        'queue_date' => now()->toDateString(),
        'ticket_number' => 'A001',
        'status' => 'waiting',
        'checked_in_at' => now()->subMinutes(10),
    ]);

    $t3 = QueueTicket::create([
        'patient_id' => $this->patient->id,
        'doctor_id' => $this->doctor->id,
        'queue_date' => now()->toDateString(),
        'ticket_number' => 'A002',
        'status' => 'waiting',
        'checked_in_at' => now()->subMinutes(3),
    ]);

    $tickets = QueueTicket::whereDate('queue_date', now()->toDateString())
        ->where('status', 'waiting')
        ->ordered()
        ->get();

    $this->assertEquals('A001', $tickets[0]->ticket_number);
    $this->assertEquals('A003', $tickets[1]->ticket_number);
    $this->assertEquals('A002', $tickets[2]->ticket_number);
});

test('doctor filtering works', function () {
    $doctor2 = Doctor::create([
        'name' => 'Dr. Jones',
        'slug' => 'dr-jones',
        'department_id' => $this->department->id,
        'is_available' => true,
        'available_days' => [1, 2, 3, 4, 5],
        'start_time' => '09:00:00',
        'end_time' => '17:00:00',
        'user_id' => $this->user->id,
    ]);

    $patient2 = Patient::factory()->create(['status' => 'active']);

    $t1 = QueueTicket::create([
        'patient_id' => $this->patient->id,
        'doctor_id' => $this->doctor->id,
        'queue_date' => now()->toDateString(),
        'ticket_number' => 'A001',
        'status' => 'waiting',
        'checked_in_at' => now(),
    ]);

    $t2 = QueueTicket::create([
        'patient_id' => $patient2->id,
        'doctor_id' => $doctor2->id,
        'queue_date' => now()->toDateString(),
        'ticket_number' => 'A002',
        'status' => 'waiting',
        'checked_in_at' => now(),
    ]);

    $response = $this->actingAs($this->user)->get(route('queue.index', ['doctor_id' => $this->doctor->id]));
    $response->assertOk();
    $response->assertSee('A001');
    $response->assertDontSee('A002');
});

// --- CALL NEXT TESTS ---

test('call next changes waiting to called', function () {
    $ticket = QueueTicket::create([
        'patient_id' => $this->patient->id,
        'doctor_id' => $this->doctor->id,
        'queue_date' => now()->toDateString(),
        'ticket_number' => 'A001',
        'status' => 'waiting',
        'checked_in_at' => now(),
    ]);

    $response = $this->actingAs($this->user)->post(route('queue.call-next'));
    $response->assertRedirect();

    $ticket->refresh();
    $this->assertEquals('called', $ticket->status);
    $this->assertNotNull($ticket->called_at);
});

test('called_at is recorded', function () {
    $ticket = QueueTicket::create([
        'patient_id' => $this->patient->id,
        'doctor_id' => $this->doctor->id,
        'queue_date' => now()->toDateString(),
        'ticket_number' => 'A001',
        'status' => 'waiting',
        'checked_in_at' => now(),
    ]);

    $this->actingAs($this->user)->post(route('queue.call-next'));

    $ticket->refresh();
    $this->assertNotNull($ticket->called_at);
    $this->assertEquals(now()->toDateString(), $ticket->called_at->toDateString());
});

test('call specific ticket works', function () {
    $ticket = QueueTicket::create([
        'patient_id' => $this->patient->id,
        'doctor_id' => $this->doctor->id,
        'queue_date' => now()->toDateString(),
        'ticket_number' => 'A001',
        'status' => 'waiting',
        'checked_in_at' => now(),
    ]);

    $response = $this->actingAs($this->user)->post(route('queue.call-ticket', $ticket));
    $response->assertRedirect();

    $ticket->refresh();
    $this->assertEquals('called', $ticket->status);
});

// --- START CONSULTATION TESTS ---

test('start consultation changes called to in_consultation', function () {
    $ticket = QueueTicket::create([
        'patient_id' => $this->patient->id,
        'doctor_id' => $this->doctor->id,
        'queue_date' => now()->toDateString(),
        'ticket_number' => 'A001',
        'status' => 'called',
        'checked_in_at' => now(),
        'called_at' => now(),
    ]);

    $response = $this->actingAs($this->user)->post(route('queue.start-consultation', $ticket));
    $response->assertRedirect();

    $ticket->refresh();
    $this->assertEquals('in_consultation', $ticket->status);
    $this->assertNotNull($ticket->consultation_started_at);
});

test('consultation_started_at is recorded', function () {
    $ticket = QueueTicket::create([
        'patient_id' => $this->patient->id,
        'doctor_id' => $this->doctor->id,
        'queue_date' => now()->toDateString(),
        'ticket_number' => 'A001',
        'status' => 'called',
        'checked_in_at' => now(),
        'called_at' => now(),
    ]);

    $this->actingAs($this->user)->post(route('queue.start-consultation', $ticket));

    $ticket->refresh();
    $this->assertNotNull($ticket->consultation_started_at);
    $this->assertEquals(now()->toDateString(), $ticket->consultation_started_at->toDateString());
});

// --- INVALID STATUS TRANSITIONS ---

test('invalid status transitions are blocked', function () {
    $ticket = QueueTicket::create([
        'patient_id' => $this->patient->id,
        'doctor_id' => $this->doctor->id,
        'queue_date' => now()->toDateString(),
        'ticket_number' => 'A001',
        'status' => 'waiting',
        'checked_in_at' => now(),
    ]);

    $response = $this->actingAs($this->user)->post(route('queue.start-consultation', $ticket));
    $response->assertSessionHas('error');

    $ticket->refresh();
    $this->assertEquals('waiting', $ticket->status);
});

test('cancelled ticket cannot be called', function () {
    $ticket = QueueTicket::create([
        'patient_id' => $this->patient->id,
        'doctor_id' => $this->doctor->id,
        'queue_date' => now()->toDateString(),
        'ticket_number' => 'A001',
        'status' => 'cancelled',
        'checked_in_at' => now(),
    ]);

    $response = $this->actingAs($this->user)->post(route('queue.call-ticket', $ticket));
    $response->assertSessionHas('error');
});

// --- CANCEL TESTS ---

test('cancelled ticket is preserved', function () {
    $ticket = QueueTicket::create([
        'patient_id' => $this->patient->id,
        'doctor_id' => $this->doctor->id,
        'queue_date' => now()->toDateString(),
        'ticket_number' => 'A001',
        'status' => 'waiting',
        'checked_in_at' => now(),
    ]);

    $this->actingAs($this->user)->post(route('queue.cancel-ticket', $ticket));

    $this->assertDatabaseHas('queue_tickets', [
        'id' => $ticket->id,
        'status' => 'cancelled',
    ]);
});

test('waiting ticket can be cancelled', function () {
    $ticket = QueueTicket::create([
        'patient_id' => $this->patient->id,
        'doctor_id' => $this->doctor->id,
        'queue_date' => now()->toDateString(),
        'ticket_number' => 'A001',
        'status' => 'waiting',
        'checked_in_at' => now(),
    ]);

    $response = $this->actingAs($this->user)->post(route('queue.cancel-ticket', $ticket));
    $response->assertRedirect();

    $ticket->refresh();
    $this->assertEquals('cancelled', $ticket->status);
});

test('called ticket can be cancelled', function () {
    $ticket = QueueTicket::create([
        'patient_id' => $this->patient->id,
        'doctor_id' => $this->doctor->id,
        'queue_date' => now()->toDateString(),
        'ticket_number' => 'A001',
        'status' => 'called',
        'checked_in_at' => now(),
        'called_at' => now(),
    ]);

    $response = $this->actingAs($this->user)->post(route('queue.cancel-ticket', $ticket));
    $response->assertRedirect();

    $ticket->refresh();
    $this->assertEquals('cancelled', $ticket->status);
});

test('in_consultation ticket cannot be cancelled', function () {
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

    $response = $this->actingAs($this->user)->post(route('queue.cancel-ticket', $ticket));
    $response->assertSessionHas('error');
});

test('appointment status updated on cancellation', function () {
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
        'appointment_number' => 'APT-CANCEL-Q-001',
        'status' => AppointmentStatus::CheckedIn,
    ]);

    $ticket = QueueTicket::create([
        'patient_id' => $this->patient->id,
        'doctor_id' => $this->doctor->id,
        'appointment_id' => $apt->id,
        'queue_date' => now()->toDateString(),
        'ticket_number' => 'A001',
        'status' => 'waiting',
        'checked_in_at' => now(),
    ]);

    $this->actingAs($this->user)->post(route('queue.cancel-ticket', $ticket));

    $apt->refresh();
    $this->assertEquals(AppointmentStatus::Cancelled, $apt->status);
});

// --- DUPLICATE PREVENTION ---

test('duplicate call is prevented', function () {
    $ticket = QueueTicket::create([
        'patient_id' => $this->patient->id,
        'doctor_id' => $this->doctor->id,
        'queue_date' => now()->toDateString(),
        'ticket_number' => 'A001',
        'status' => 'called',
        'checked_in_at' => now(),
        'called_at' => now(),
    ]);

    $response = $this->actingAs($this->user)->post(route('queue.call-ticket', $ticket));
    $response->assertSessionHas('error');
});

// --- AUTHORIZATION ---

test('unauthorized user cannot view queue', function () {
    $user = User::factory()->create();
    $response = $this->actingAs($user)->get(route('queue.index'));
    $response->assertForbidden();
});

test('unauthorized user cannot check in', function () {
    $user = User::factory()->create();
    $response = $this->actingAs($user)->get(route('queue.checkin.form'));
    $response->assertForbidden();
});

test('unauthorized user cannot walk in', function () {
    $user = User::factory()->create();
    $response = $this->actingAs($user)->get(route('queue.walkin.form'));
    $response->assertForbidden();
});

test('unauthorized user cannot call next', function () {
    $user = User::factory()->create();
    $response = $this->actingAs($user)->post(route('queue.call-next'));
    $response->assertForbidden();
});

// --- PATIENT PROFILE INTEGRATION ---

test('patient profile shows active queue status', function () {
    $ticket = QueueTicket::create([
        'patient_id' => $this->patient->id,
        'doctor_id' => $this->doctor->id,
        'queue_date' => now()->toDateString(),
        'ticket_number' => 'A014',
        'status' => 'waiting',
        'checked_in_at' => now(),
    ]);

    $response = $this->actingAs($this->user)->get(route('patients.show', $this->patient));
    $response->assertOk();
    $response->assertSee('A014');
    $response->assertSee('Waiting');
});

// --- MODEL TESTS ---

test('ticket number format is correct', function () {
    $number = QueueTicket::generateTicketNumber(now()->toDateString());
    $this->assertMatchesRegularExpression('/^A\d{3}$/', $number);
    $this->assertEquals('A001', $number);
});

test('ticket number increments correctly', function () {
    QueueTicket::create([
        'patient_id' => $this->patient->id,
        'doctor_id' => $this->doctor->id,
        'queue_date' => now()->toDateString(),
        'ticket_number' => 'A001',
        'status' => 'waiting',
        'checked_in_at' => now(),
    ]);

    $number = QueueTicket::generateTicketNumber(now()->toDateString());
    $this->assertEquals('A002', $number);
});

test('model status helpers work', function () {
    $waiting = QueueTicket::create([
        'patient_id' => $this->patient->id,
        'doctor_id' => $this->doctor->id,
        'queue_date' => now()->toDateString(),
        'ticket_number' => 'A001',
        'status' => 'waiting',
        'checked_in_at' => now(),
    ]);

    $this->assertTrue($waiting->isWaiting());
    $this->assertFalse($waiting->isCalled());
    $this->assertFalse($waiting->isInConsultation());
    $this->assertFalse($waiting->isCancelled());
    $this->assertTrue($waiting->canBeCalled());
    $this->assertFalse($waiting->canStartConsultation());
    $this->assertTrue($waiting->canBeCancelled());
});

// --- REGRESSION: EXISTING TESTS STILL PASS ---

test('existing patient tests still pass', function () {
    $response = $this->actingAs($this->user)->get(route('patients.index'));
    $response->assertOk();
});

test('existing appointment tests still pass', function () {
    $response = $this->actingAs($this->user)->get(route('appointments.index'));
    $response->assertOk();
});

test('existing doctor tests still pass', function () {
    $response = $this->actingAs($this->user)->get(route('doctors.index'));
    $response->assertOk();
});

test('appointment status checked_in works', function () {
    $apt = Appointment::create([
        'patient_id' => $this->patient->id,
        'doctor_id' => $this->doctor->id,
        'department_id' => $this->department->id,
        'name' => $this->patient->name,
        'email' => $this->patient->email,
        'phone' => $this->patient->phone,
        'date' => now()->addDays(5)->toDateString(),
        'time' => '10:00',
        'duration' => 30,
        'appointment_number' => 'APT-CI-TEST',
        'status' => AppointmentStatus::CheckedIn,
    ]);

    $this->assertTrue($apt->isCheckedIn());
    $this->assertEquals('Checked In', $apt->status->label());
});
