<?php

use App\Enums\AppointmentStatus;
use App\Models\Appointment;
use App\Models\Department;
use App\Models\Doctor;
use App\Models\Patient;
use App\Models\User;
use Spatie\Permission\Models\Permission;

function nextWeekday(\Carbon\Carbon $from = null): string
{
    $date = $from ?? now();
    while ($date->isWeekend()) {
        $date->addDay();
    }
    return $date->toDateString();
}

function nthWeekday(int $n): string
{
    return nextWeekday(now()->addDays($n));
}

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

// --- DOCTOR TESTS ---

test('doctor index requires authentication', function () {
    $response = $this->get(route('doctors.index'));
    $response->assertRedirect(route('login'));
});

test('doctor index displays doctors', function () {
    $response = $this->actingAs($this->user)->get(route('doctors.index'));
    $response->assertOk();
    $response->assertSee('Dr. Smith');
});

test('doctor index supports search', function () {
    Doctor::create([
        'name' => 'Dr. Jones', 'slug' => 'dr-jones',
        'department_id' => $this->department->id,
        'is_available' => true, 'available_days' => [1],
        'start_time' => '09:00:00', 'end_time' => '17:00:00',
        'user_id' => $this->user->id,
    ]);

    $response = $this->actingAs($this->user)->get(route('doctors.index', ['search' => 'Jones']));
    $response->assertOk();
    $response->assertSee('Dr. Jones');
    $response->assertDontSee('Dr. Smith');
});

test('doctor create page accessible', function () {
    $response = $this->actingAs($this->user)->get(route('doctors.create'));
    $response->assertOk();
});

test('doctor can be stored', function () {
    $response = $this->actingAs($this->user)->post(route('doctors.store'), [
        'name' => 'Dr. New',
        'department_id' => $this->department->id,
        'days' => [1, 2, 3],
        'start_time' => '08:00',
        'end_time' => '16:00',
        'experience_years' => 5,
    ]);

    $response->assertRedirect(route('doctors.index'));
    $this->assertDatabaseHas('doctors', ['name' => 'Dr. New']);
});

test('doctor edit page accessible', function () {
    $response = $this->actingAs($this->user)->get(route('doctors.edit', $this->doctor));
    $response->assertOk();
});

test('doctor can be updated', function () {
    $response = $this->actingAs($this->user)->put(route('doctors.update', $this->doctor), [
        'name' => 'Dr. Updated',
        'days' => [1, 2, 3],
        'start_time' => '08:00',
        'end_time' => '16:00',
        'experience_years' => 10,
    ]);

    $response->assertRedirect(route('doctors.index'));
    $this->assertDatabaseHas('doctors', ['id' => $this->doctor->id, 'name' => 'Dr. Updated']);
});

test('doctor schedule with end before start is rejected', function () {
    $response = $this->actingAs($this->user)->post(route('doctors.store'), [
        'name' => 'Dr. Bad Schedule',
        'department_id' => $this->department->id,
        'days' => [1, 2, 3],
        'start_time' => '17:39',
        'end_time' => '11:14',
        'experience_years' => 1,
    ]);

    $response->assertSessionHasErrors('end_time');
    $this->assertDatabaseMissing('doctors', ['name' => 'Dr. Bad Schedule']);
});

test('doctor schedule with invalid time format is rejected', function () {
    $response = $this->actingAs($this->user)->post(route('doctors.store'), [
        'name' => 'Dr. Bad Format',
        'department_id' => $this->department->id,
        'days' => [1, 2, 3],
        'start_time' => '9am',
        'end_time' => '5pm',
        'experience_years' => 1,
    ]);

    $response->assertSessionHasErrors(['start_time', 'end_time']);
    $this->assertDatabaseMissing('doctors', ['name' => 'Dr. Bad Format']);
});

test('doctor belongs to department', function () {
    $this->assertNotNull($this->doctor->department);
    $this->assertEquals($this->department->id, $this->doctor->department_id);
});

test('doctor without permission cannot access index', function () {
    $user = User::factory()->create();
    $response = $this->actingAs($user)->get(route('doctors.index'));
    $response->assertForbidden();
});

// --- APPOINTMENT CREATION TESTS ---

test('appointment index requires authentication', function () {
    $response = $this->get(route('appointments.index'));
    $response->assertRedirect(route('login'));
});

test('appointment index displays appointments', function () {
    $apt = Appointment::create([
        'patient_id' => $this->patient->id,
        'doctor_id' => $this->doctor->id,
        'department_id' => $this->department->id,
        'name' => $this->patient->name,
        'email' => $this->patient->email,
        'phone' => $this->patient->phone,
        'date' => now()->addDay()->toDateString(),
        'time' => '10:00',
        'duration' => 30,
        'appointment_number' => 'APT-TEST-001',
        'status' => AppointmentStatus::Scheduled,
    ]);

    $response = $this->actingAs($this->user)->get(route('appointments.index'));
    $response->assertOk();
    $response->assertSee('APT-TEST-001');
});

test('appointment can be created', function () {
    $response = $this->actingAs($this->user)->post(route('appointments.store'), [
        'patient_id' => $this->patient->id,
        'doctor_id' => $this->doctor->id,
        'department_id' => $this->department->id,
        'date' => nthWeekday(2),
        'time' => '10:00',
        'duration' => 30,
    ]);

    $response->assertRedirect(route('appointments.index'));
    $this->assertDatabaseHas('appointments', [
        'patient_id' => $this->patient->id,
        'doctor_id' => $this->doctor->id,
        'status' => AppointmentStatus::Scheduled->value,
    ]);
});

test('appointment uses existing patient', function () {
    $this->actingAs($this->user)->post(route('appointments.store'), [
        'patient_id' => $this->patient->id,
        'doctor_id' => $this->doctor->id,
        'department_id' => $this->department->id,
        'date' => nthWeekday(3),
        'time' => '11:00',
        'duration' => 30,
    ]);

    $apt = Appointment::where('patient_id', $this->patient->id)->first();
    $this->assertNotNull($apt);
    $this->assertEquals($this->patient->name, $apt->name);
    $this->assertEquals($this->patient->email, $apt->email);
});

test('appointment number is generated', function () {
    $this->actingAs($this->user)->post(route('appointments.store'), [
        'patient_id' => $this->patient->id,
        'doctor_id' => $this->doctor->id,
        'department_id' => $this->department->id,
        'date' => nthWeekday(4),
        'time' => '14:00',
        'duration' => 30,
    ]);

    $apt = Appointment::latest()->first();
    $this->assertNotNull($apt->appointment_number);
    $this->assertMatchesRegularExpression('/^APT-\d{8}-\d{4}$/', $apt->appointment_number);
});

test('appointment date time validation works', function () {
    $response = $this->actingAs($this->user)->post(route('appointments.store'), [
        'patient_id' => $this->patient->id,
        'doctor_id' => $this->doctor->id,
        'department_id' => $this->department->id,
        'date' => now()->subDay()->toDateString(),
        'time' => '10:00',
        'duration' => 30,
    ]);

    $response->assertSessionHasErrors('date');
});

// --- DOUBLE BOOKING PREVENTION ---

test('double booking is rejected', function () {
    Appointment::create([
        'patient_id' => $this->patient->id,
        'doctor_id' => $this->doctor->id,
        'department_id' => $this->department->id,
        'name' => $this->patient->name,
        'email' => $this->patient->email,
        'phone' => $this->patient->phone,
        'date' => nextWeekday(now()->addDays(5)),
        'time' => '10:00',
        'duration' => 60,
        'appointment_number' => 'APT-EXISTING-001',
        'status' => AppointmentStatus::Scheduled,
    ]);

    $response = $this->actingAs($this->user)->post(route('appointments.store'), [
        'patient_id' => $this->patient->id,
        'doctor_id' => $this->doctor->id,
        'department_id' => $this->department->id,
        'date' => nextWeekday(now()->addDays(5)),
        'time' => '10:30',
        'duration' => 30,
    ]);

    $response->assertSessionHasErrors('time');
});

test('non-overlapping appointment is allowed', function () {
    Appointment::create([
        'patient_id' => $this->patient->id,
        'doctor_id' => $this->doctor->id,
        'department_id' => $this->department->id,
        'name' => $this->patient->name,
        'email' => $this->patient->email,
        'phone' => $this->patient->phone,
        'date' => nextWeekday(now()->addDays(6)),
        'time' => '10:00',
        'duration' => 30,
        'appointment_number' => 'APT-EXISTING-002',
        'status' => AppointmentStatus::Scheduled,
    ]);

    $response = $this->actingAs($this->user)->post(route('appointments.store'), [
        'patient_id' => $this->patient->id,
        'doctor_id' => $this->doctor->id,
        'department_id' => $this->department->id,
        'date' => nextWeekday(now()->addDays(6)),
        'time' => '10:30',
        'duration' => 30,
    ]);

    $response->assertRedirect(route('appointments.index'));
    $this->assertDatabaseHas('appointments', [
        'doctor_id' => $this->doctor->id,
        'time' => '10:30',
    ]);
});

test('cancelled appointment does not conflict', function () {
    Appointment::create([
        'patient_id' => $this->patient->id,
        'doctor_id' => $this->doctor->id,
        'department_id' => $this->department->id,
        'name' => $this->patient->name,
        'email' => $this->patient->email,
        'phone' => $this->patient->phone,
        'date' => nextWeekday(now()->addDays(7)),
        'time' => '10:00',
        'duration' => 60,
        'appointment_number' => 'APT-CANCELLED-001',
        'status' => AppointmentStatus::Cancelled,
    ]);

    $response = $this->actingAs($this->user)->post(route('appointments.store'), [
        'patient_id' => $this->patient->id,
        'doctor_id' => $this->doctor->id,
        'department_id' => $this->department->id,
        'date' => nextWeekday(now()->addDays(7)),
        'time' => '10:30',
        'duration' => 30,
    ]);

    $response->assertRedirect(route('appointments.index'));
});

// --- DOCTOR AVAILABILITY ---

test('unavailable doctor is rejected', function () {
    $unavailDoctor = Doctor::create([
        'name' => 'Dr. Unavail', 'slug' => 'dr-unavail',
        'department_id' => $this->department->id,
        'is_available' => false,
        'available_days' => [1, 2, 3, 4, 5],
        'start_time' => '09:00:00', 'end_time' => '17:00:00',
        'user_id' => $this->user->id,
    ]);

    $response = $this->actingAs($this->user)->post(route('appointments.store'), [
        'patient_id' => $this->patient->id,
        'doctor_id' => $unavailDoctor->id,
        'department_id' => $this->department->id,
        'date' => nthWeekday(2),
        'time' => '10:00',
        'duration' => 30,
    ]);

    $response->assertSessionHasErrors('doctor_id');
});

test('doctor not working that day is rejected', function () {
    $response = $this->actingAs($this->user)->post(route('appointments.store'), [
        'patient_id' => $this->patient->id,
        'doctor_id' => $this->doctor->id,
        'department_id' => $this->department->id,
        'date' => now()->nextWeekendDay()->toDateString(),
        'time' => '10:00',
        'duration' => 30,
    ]);

    $response->assertSessionHasErrors('date');
});

test('time outside working hours is rejected', function () {
    $response = $this->actingAs($this->user)->post(route('appointments.store'), [
        'patient_id' => $this->patient->id,
        'doctor_id' => $this->doctor->id,
        'department_id' => $this->department->id,
        'date' => nthWeekday(2),
        'time' => '20:00',
        'duration' => 30,
    ]);

    $response->assertSessionHasErrors('time');
});

test('admin appointment with an inverted schedule is rejected with a friendly message, not raw hours', function () {
    // Regression guard for the "Requested time is outside doctor's working hours (17:39 - 11:14)"
    // leak. An inverted schedule (end before start) is invalid and must be handled by the shared
    // availability service without echoing the raw stored times.
    $badDoctor = Doctor::create([
        'name' => 'Dr. Inverted', 'slug' => 'dr-inverted',
        'department_id' => $this->department->id,
        'is_available' => true,
        'available_days' => [1, 2, 3, 4, 5],
        'start_time' => '17:39:00', 'end_time' => '11:14:00',
        'user_id' => $this->user->id,
    ]);

    $response = $this->actingAs($this->user)->post(route('appointments.store'), [
        'patient_id' => $this->patient->id,
        'doctor_id' => $badDoctor->id,
        'department_id' => $this->department->id,
        'date' => nthWeekday(2),
        'time' => '10:00',
        'duration' => 30,
    ]);

    $response->assertSessionHasErrors('time');
    $this->assertDatabaseMissing('appointments', ['doctor_id' => $badDoctor->id]);

    $message = session('errors')->getBag('default')->first('time');
    expect($message)->toContain('schedule');
    expect($message)->not->toContain('17:39');
    expect($message)->not->toContain('11:14');
});

// --- STATUS TRANSITIONS ---

test('appointment can be confirmed', function () {
    $apt = Appointment::create([
        'patient_id' => $this->patient->id,
        'doctor_id' => $this->doctor->id,
        'department_id' => $this->department->id,
        'name' => $this->patient->name, 'email' => $this->patient->email, 'phone' => $this->patient->phone,
        'date' => now()->addDays(8)->toDateString(), 'time' => '10:00', 'duration' => 30,
        'appointment_number' => 'APT-CONFIRM-001', 'status' => AppointmentStatus::Scheduled,
    ]);

    $response = $this->actingAs($this->user)->post(route('appointments.confirm', $apt));
    $response->assertRedirect();
    $apt->refresh();
    $this->assertEquals(AppointmentStatus::Confirmed, $apt->status);
});

test('appointment can be cancelled', function () {
    $apt = Appointment::create([
        'patient_id' => $this->patient->id,
        'doctor_id' => $this->doctor->id,
        'department_id' => $this->department->id,
        'name' => $this->patient->name, 'email' => $this->patient->email, 'phone' => $this->patient->phone,
        'date' => now()->addDays(9)->toDateString(), 'time' => '11:00', 'duration' => 30,
        'appointment_number' => 'APT-CANCEL-001', 'status' => AppointmentStatus::Scheduled,
    ]);

    $response = $this->actingAs($this->user)->post(route('appointments.cancel', $apt), [
        'cancel_reason' => 'Patient request',
    ]);
    $response->assertRedirect();
    $apt->refresh();
    $this->assertEquals(AppointmentStatus::Cancelled, $apt->status);
    $this->assertEquals('Patient request', $apt->cancel_reason);
});

test('cancelled appointment is not deleted', function () {
    $apt = Appointment::create([
        'patient_id' => $this->patient->id,
        'doctor_id' => $this->doctor->id,
        'department_id' => $this->department->id,
        'name' => $this->patient->name, 'email' => $this->patient->email, 'phone' => $this->patient->phone,
        'date' => now()->addDays(10)->toDateString(), 'time' => '12:00', 'duration' => 30,
        'appointment_number' => 'APT-CANCEL-002', 'status' => AppointmentStatus::Scheduled,
    ]);

    $this->actingAs($this->user)->post(route('appointments.cancel', $apt), [
        'cancel_reason' => 'No longer needed',
    ]);
    $this->assertDatabaseHas('appointments', ['id' => $apt->id, 'status' => AppointmentStatus::Cancelled->value]);
});

test('appointment can be completed', function () {
    $apt = Appointment::create([
        'patient_id' => $this->patient->id,
        'doctor_id' => $this->doctor->id,
        'department_id' => $this->department->id,
        'name' => $this->patient->name, 'email' => $this->patient->email, 'phone' => $this->patient->phone,
        'date' => now()->addDays(11)->toDateString(), 'time' => '14:00', 'duration' => 30,
        'appointment_number' => 'APT-COMPLETE-001', 'status' => AppointmentStatus::Confirmed,
    ]);

    $response = $this->actingAs($this->user)->post(route('appointments.complete', $apt));
    $response->assertRedirect();
    $apt->refresh();
    $this->assertEquals(AppointmentStatus::Completed, $apt->status);
});

// --- RESCHEDULING ---

test('appointment can be rescheduled', function () {
    $apt = Appointment::create([
        'patient_id' => $this->patient->id,
        'doctor_id' => $this->doctor->id,
        'department_id' => $this->department->id,
        'name' => $this->patient->name, 'email' => $this->patient->email, 'phone' => $this->patient->phone,
        'date' => now()->addDays(12)->toDateString(), 'time' => '10:00', 'duration' => 30,
        'appointment_number' => 'APT-RESCHED-001', 'status' => AppointmentStatus::Scheduled,
    ]);

    $newDate = nthWeekday(15);
    $response = $this->actingAs($this->user)->put(route('appointments.update', $apt), [
        'patient_id' => $this->patient->id,
        'doctor_id' => $this->doctor->id,
        'department_id' => $this->department->id,
        'date' => $newDate,
        'time' => '14:00',
        'duration' => 45,
    ]);

    $response->assertRedirect(route('appointments.show', $apt));
    $apt->refresh();
    $this->assertEquals($newDate, $apt->date->toDateString());
    $this->assertEquals('14:00', $apt->time);
    $this->assertEquals(45, $apt->duration);
    $this->assertEquals('APT-RESCHED-001', $apt->appointment_number);
});

test('rescheduling checks conflicts', function () {
    $apt1 = Appointment::create([
        'patient_id' => $this->patient->id,
        'doctor_id' => $this->doctor->id,
        'department_id' => $this->department->id,
        'name' => $this->patient->name, 'email' => $this->patient->email, 'phone' => $this->patient->phone,
        'date' => nextWeekday(now()->addDays(13)), 'time' => '10:00', 'duration' => 60,
        'appointment_number' => 'APT-RESCHED-002', 'status' => AppointmentStatus::Scheduled,
    ]);

    $apt2 = Appointment::create([
        'patient_id' => $this->patient->id,
        'doctor_id' => $this->doctor->id,
        'department_id' => $this->department->id,
        'name' => $this->patient->name, 'email' => $this->patient->email, 'phone' => $this->patient->phone,
        'date' => nextWeekday(now()->addDays(14)), 'time' => '14:00', 'duration' => 30,
        'appointment_number' => 'APT-RESCHED-003', 'status' => AppointmentStatus::Scheduled,
    ]);

    $response = $this->actingAs($this->user)->put(route('appointments.update', $apt2), [
        'patient_id' => $this->patient->id,
        'doctor_id' => $this->doctor->id,
        'department_id' => $this->department->id,
        'date' => nextWeekday(now()->addDays(13)),
        'time' => '10:30',
        'duration' => 30,
    ]);

    $response->assertSessionHasErrors('time');
});

// --- SEARCH AND FILTER ---

test('appointment search works', function () {
    $apt = Appointment::create([
        'patient_id' => $this->patient->id,
        'doctor_id' => $this->doctor->id,
        'department_id' => $this->department->id,
        'name' => $this->patient->name, 'email' => $this->patient->email, 'phone' => $this->patient->phone,
        'date' => now()->addDays(16)->toDateString(), 'time' => '10:00', 'duration' => 30,
        'appointment_number' => 'APT-SEARCH-001', 'status' => AppointmentStatus::Scheduled,
    ]);

    $response = $this->actingAs($this->user)->get(route('appointments.index', ['search' => 'SEARCH-001']));
    $response->assertOk();
    $response->assertSee('APT-SEARCH-001');
});

test('appointment filter by status works', function () {
    Appointment::create([
        'patient_id' => $this->patient->id, 'doctor_id' => $this->doctor->id,
        'department_id' => $this->department->id, 'name' => $this->patient->name,
        'email' => $this->patient->email, 'phone' => $this->patient->phone,
        'date' => now()->addDays(17)->toDateString(), 'time' => '10:00', 'duration' => 30,
        'appointment_number' => 'APT-FILTER-001', 'status' => AppointmentStatus::Scheduled,
    ]);

    $response = $this->actingAs($this->user)->get(route('appointments.index', ['status' => 'scheduled']));
    $response->assertOk();
    $response->assertSee('APT-FILTER-001');
});

test('appointment filter by doctor works', function () {
    Appointment::create([
        'patient_id' => $this->patient->id, 'doctor_id' => $this->doctor->id,
        'department_id' => $this->department->id, 'name' => $this->patient->name,
        'email' => $this->patient->email, 'phone' => $this->patient->phone,
        'date' => now()->addDays(18)->toDateString(), 'time' => '10:00', 'duration' => 30,
        'appointment_number' => 'APT-FILTER-002', 'status' => AppointmentStatus::Scheduled,
    ]);

    $response = $this->actingAs($this->user)->get(route('appointments.index', ['doctor_id' => $this->doctor->id]));
    $response->assertOk();
    $response->assertSee('APT-FILTER-002');
});

// --- PATIENT INTEGRATION ---

test('patient appointment history works', function () {
    Appointment::create([
        'patient_id' => $this->patient->id, 'doctor_id' => $this->doctor->id,
        'department_id' => $this->department->id, 'name' => $this->patient->name,
        'email' => $this->patient->email, 'phone' => $this->patient->phone,
        'date' => now()->addDays(19)->toDateString(), 'time' => '10:00', 'duration' => 30,
        'appointment_number' => 'APT-PATIENT-001', 'status' => AppointmentStatus::Scheduled,
    ]);

    $response = $this->actingAs($this->user)->get(route('patients.show', $this->patient));
    $response->assertOk();
    $response->assertSee('APT-PATIENT-001');
});

test('appointment create page accessible for admin', function () {
    $response = $this->actingAs($this->user)->get(route('appointments.create'));
    $response->assertOk();
});

test('appointment show page displays details', function () {
    $apt = Appointment::create([
        'patient_id' => $this->patient->id, 'doctor_id' => $this->doctor->id,
        'department_id' => $this->department->id, 'name' => $this->patient->name,
        'email' => $this->patient->email, 'phone' => $this->patient->phone,
        'date' => now()->addDays(20)->toDateString(), 'time' => '10:00', 'duration' => 30,
        'appointment_number' => 'APT-SHOW-001', 'status' => AppointmentStatus::Scheduled,
    ]);

    $response = $this->actingAs($this->user)->get(route('appointments.show', $apt));
    $response->assertOk();
    $response->assertSee('APT-SHOW-001');
    $response->assertSee('Dr. Smith');
});

// --- AUTHORIZATION ---

test('unauthorized user cannot access appointments', function () {
    $user = User::factory()->create();
    $response = $this->actingAs($user)->get(route('appointments.index'));
    $response->assertForbidden();
});

test('unauthorized user cannot create appointment', function () {
    $user = User::factory()->create();
    $response = $this->actingAs($user)->get(route('appointments.create'));
    $response->assertForbidden();
});

// --- MODEL TESTS ---

test('appointment number follows correct format', function () {
    $number = Appointment::generateAppointmentNumber();
    $today = now()->format('Ymd');
    expect($number)->toBe("APT-{$today}-0001");
});

test('appointment status enum works correctly', function () {
    $apt = Appointment::create([
        'patient_id' => $this->patient->id, 'doctor_id' => $this->doctor->id,
        'department_id' => $this->department->id, 'name' => $this->patient->name,
        'email' => $this->patient->email, 'phone' => $this->patient->phone,
        'date' => now()->addDays(21)->toDateString(), 'time' => '10:00', 'duration' => 30,
        'appointment_number' => 'APT-ENUM-001', 'status' => AppointmentStatus::Scheduled,
    ]);

    $this->assertTrue($apt->isScheduled());
    $this->assertFalse($apt->isConfirmed());
    $this->assertFalse($apt->isCancelled());
    $this->assertFalse($apt->isCompleted());
});

test('appointment end time is calculated correctly', function () {
    $apt = Appointment::create([
        'patient_id' => $this->patient->id, 'doctor_id' => $this->doctor->id,
        'department_id' => $this->department->id, 'name' => $this->patient->name,
        'email' => $this->patient->email, 'phone' => $this->patient->phone,
        'date' => now()->addDays(22)->toDateString(), 'time' => '10:00', 'duration' => 45,
        'appointment_number' => 'APT-END-001', 'status' => AppointmentStatus::Scheduled,
    ]);

    $endTime = $apt->endTime();
    $this->assertEquals('10:45', $endTime->format('H:i'));
});
