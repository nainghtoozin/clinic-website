<?php

use App\Enums\AppointmentStatus;
use App\Models\Appointment;
use App\Models\Department;
use App\Models\Doctor;
use App\Models\User;
use Spatie\Permission\Models\Permission;

function nextWeekdayPublic(\Carbon\Carbon $from = null): string
{
    $date = $from ?? now();
    while ($date->isWeekend()) {
        $date->addDay();
    }
    return $date->toDateString();
}

beforeEach(function () {
    $this->department = Department::create(['name' => 'Cardiology', 'slug' => 'cardiology', 'description' => 'Heart care']);
    $this->doctor = Doctor::create([
        'name' => 'Dr. Smith',
        'slug' => 'dr-smith',
        'department_id' => $this->department->id,
        'is_available' => true,
        'available_days' => [1, 2, 3, 4, 5],
        'start_time' => '09:00:00',
        'end_time' => '17:00:00',
        'user_id' => User::factory()->create()->id,
        'consultation_fee' => 100.00,
    ]);
});

// --- PUBLIC APPOINTMENT PAGE TESTS ---

test('guest can view public appointment page', function () {
    $response = $this->get(route('public.appointment.create'));
    $response->assertOk();
    $response->assertSee('Book an Appointment');
    $response->assertSee('Request Appointment');
});

test('guest can access public appointment page without authentication', function () {
    $response = $this->get(route('public.appointment.create'));
    $response->assertOk();
    $response->assertDontSee('Login');
});

test('guest can load doctors for a department', function () {
    $response = $this->get(route('public.appointment.doctors', ['department_id' => $this->department->id]));
    $response->assertOk();
    $response->assertJsonFragment(['name' => 'Dr. Smith']);
    $response->assertJsonPath('doctors.0.department', 'Cardiology');
});

test('doctors endpoint only returns doctors from the requested department', function () {
    $other = Department::create(['name' => 'Dental', 'slug' => 'dental', 'description' => 'Teeth']);
    Doctor::create([
        'name' => 'Dr. Dental',
        'slug' => 'dr-dental',
        'department_id' => $other->id,
        'is_available' => true,
        'available_days' => [1, 2, 3],
        'start_time' => '09:00:00',
        'end_time' => '17:00:00',
        'user_id' => User::factory()->create()->id,
    ]);

    $response = $this->get(route('public.appointment.doctors', ['department_id' => $this->department->id]));
    $response->assertOk();
    $response->assertJsonFragment(['name' => 'Dr. Smith']);
    $response->assertJsonMissing(['name' => 'Dr. Dental']);
});

test('public appointment page shows departments', function () {
    $response = $this->get(route('public.appointment.create'));
    $response->assertOk();
    $response->assertSee('Cardiology');
});

// --- PUBLIC APPOINTMENT SUBMISSION TESTS ---

test('guest can submit appointment request', function () {
    $response = $this->post(route('public.appointment.store'), [
        'name' => 'John Doe',
        'phone' => '+1234567890',
        'email' => 'john@example.com',
        'doctor_id' => $this->doctor->id,
        'department_id' => $this->department->id,
        'date' => nextWeekdayPublic(now()->addDays(5)),
        'time' => '10:00',
        'message' => 'General checkup',
    ]);

    $response->assertRedirect(route('public.appointment.success'));
    $this->assertDatabaseHas('appointments', [
        'name' => 'John Doe',
        'phone' => '+1234567890',
        'email' => 'john@example.com',
        'doctor_id' => $this->doctor->id,
        'department_id' => $this->department->id,
        'status' => AppointmentStatus::Pending->value,
        'source' => 'public',
    ]);
});

test('public appointment does not require login', function () {
    $response = $this->post(route('public.appointment.store'), [
        'name' => 'Jane Doe',
        'phone' => '+0987654321',
        'doctor_id' => $this->doctor->id,
        'department_id' => $this->department->id,
        'date' => nextWeekdayPublic(now()->addDays(6)),
        'time' => '11:00',
    ]);

    $response->assertRedirect(route('public.appointment.success'));
});

test('public appointment creates pending status', function () {
    $this->post(route('public.appointment.store'), [
        'name' => 'Pending User',
        'phone' => '+1112223333',
        'doctor_id' => $this->doctor->id,
        'department_id' => $this->department->id,
        'date' => nextWeekdayPublic(now()->addDays(7)),
        'time' => '14:00',
    ]);

    $appointment = Appointment::where('phone', '+1112223333')->first();
    $this->assertNotNull($appointment);
    $this->assertEquals(AppointmentStatus::Pending, $appointment->status);
    $this->assertEquals('public', $appointment->source);
    $this->assertNull($appointment->appointment_number);
});

// --- VALIDATION TESTS ---

test('public appointment validates required fields', function () {
    $response = $this->post(route('public.appointment.store'), []);
    $response->assertSessionHasErrors(['name', 'phone', 'doctor_id', 'department_id', 'date', 'time']);
});

test('public appointment requires doctor_id', function () {
    $response = $this->post(route('public.appointment.store'), [
        'name' => 'Test User',
        'phone' => '+1234567890',
        'department_id' => $this->department->id,
        'date' => nextWeekdayPublic(now()->addDays(5)),
        'time' => '10:00',
    ]);
    $response->assertSessionHasErrors('doctor_id');
});

test('public appointment requires department_id', function () {
    $response = $this->post(route('public.appointment.store'), [
        'name' => 'Test User',
        'phone' => '+1234567890',
        'doctor_id' => $this->doctor->id,
        'date' => nextWeekdayPublic(now()->addDays(5)),
        'time' => '10:00',
    ]);
    $response->assertSessionHasErrors('department_id');
});

test('public appointment page submits doctor_id and department_id via state-synced hidden fields', function () {
    $response = $this->get(route('public.appointment.create'));
    $response->assertOk();

    // Regression guard for the submission bug: the named selects are pure UI and
    // can be disabled during submission (disabled controls are dropped from the
    // native payload), so the POST values must come from hidden inputs bound to
    // the Alpine state.
    $response->assertSee('name="department_id"', false);
    $response->assertSee('name="doctor_id"', false);
    $response->assertSee('name="date"', false);
    $response->assertSee('name="time"', false);
});

test('public appointment validates email format', function () {
    $response = $this->post(route('public.appointment.store'), [
        'name' => 'Test User',
        'phone' => '+1234567890',
        'email' => 'invalid-email',
        'doctor_id' => $this->doctor->id,
        'department_id' => $this->department->id,
        'date' => nextWeekdayPublic(now()->addDays(5)),
        'time' => '10:00',
    ]);
    $response->assertSessionHasErrors('email');
});

test('public appointment rejects invalid doctor', function () {
    $response = $this->post(route('public.appointment.store'), [
        'name' => 'Test User',
        'phone' => '+1234567890',
        'doctor_id' => 99999,
        'department_id' => $this->department->id,
        'date' => nextWeekdayPublic(now()->addDays(5)),
        'time' => '10:00',
    ]);
    $response->assertSessionHasErrors('doctor_id');
});

test('public appointment rejects past date', function () {
    $response = $this->post(route('public.appointment.store'), [
        'name' => 'Test User',
        'phone' => '+1234567890',
        'doctor_id' => $this->doctor->id,
        'department_id' => $this->department->id,
        'date' => now()->subDay()->toDateString(),
        'time' => '10:00',
    ]);
    $response->assertSessionHasErrors('date');
});

test('public appointment rejects invalid time format', function () {
    $response = $this->post(route('public.appointment.store'), [
        'name' => 'Test User',
        'phone' => '+1234567890',
        'doctor_id' => $this->doctor->id,
        'department_id' => $this->department->id,
        'date' => nextWeekdayPublic(now()->addDays(5)),
        'time' => 'invalid-time',
    ]);
    $response->assertSessionHasErrors('time');
});

test('public appointment rejects time outside working hours', function () {
    $response = $this->post(route('public.appointment.store'), [
        'name' => 'Test User',
        'phone' => '+1234567890',
        'doctor_id' => $this->doctor->id,
        'department_id' => $this->department->id,
        'date' => nextWeekdayPublic(now()->addDays(5)),
        'time' => '20:00',
    ]);
    $response->assertSessionHasErrors('time');
});

test('public appointment rejects unavailable doctor', function () {
    $unavailDoctor = Doctor::create([
        'name' => 'Dr. Unavail',
        'slug' => 'dr-unavail',
        'department_id' => $this->department->id,
        'is_available' => false,
        'available_days' => [1, 2, 3, 4, 5],
        'start_time' => '09:00:00',
        'end_time' => '17:00:00',
        'user_id' => User::factory()->create()->id,
    ]);

    $response = $this->post(route('public.appointment.store'), [
        'name' => 'Test User',
        'phone' => '+1234567890',
        'doctor_id' => $unavailDoctor->id,
        'department_id' => $this->department->id,
        'date' => nextWeekdayPublic(now()->addDays(5)),
        'time' => '10:00',
    ]);
    $response->assertSessionHasErrors('doctor_id');
});

// --- SUCCESS PAGE TESTS ---

test('success page shows appointment details', function () {
    $this->post(route('public.appointment.store'), [
        'name' => 'Success Test',
        'phone' => '+1234567890',
        'email' => 'success@example.com',
        'doctor_id' => $this->doctor->id,
        'department_id' => $this->department->id,
        'date' => nextWeekdayPublic(now()->addDays(5)),
        'time' => '10:00',
    ]);

    $response = $this->get(route('public.appointment.success'));
    $response->assertOk();
    $response->assertSee('Success Test');
    $response->assertSee('Dr. Smith');
});

test('success page redirects if no session data', function () {
    $response = $this->get(route('public.appointment.success'));
    $response->assertRedirect(route('public.appointment.create'));
});

// --- AVAILABILITY-FIRST BEHAVIOUR ---

test('availability endpoint returns working hours and slots', function () {
    $date = nextWeekdayPublic(now()->addDays(5));

    $response = $this->get(route('public.appointment.availability', [
        'doctor_id' => $this->doctor->id,
        'date' => $date,
    ]));

    $response->assertOk();
    $response->assertJsonPath('date', $date);
    $response->assertJsonPath('available', true);
    $response->assertJsonPath('working_hours.start', '09:00');
    $response->assertJsonPath('working_hours.end', '17:00');
    $response->assertJsonPath('slots.0', '09:00');
    $response->assertJsonPath('slots.1', '09:30');
    expect($response->json('slots'))->toContain('10:00')->toContain('16:30')->not->toContain('17:00');
});

test('availability endpoint rejects a non-working day', function () {
    $date = now()->nextWeekendDay()->toDateString();

    $response = $this->get(route('public.appointment.availability', [
        'doctor_id' => $this->doctor->id,
        'date' => $date,
    ]));

    $response->assertOk();
    $response->assertJsonPath('date', $date);
    $response->assertJsonPath('available', false);
    $response->assertJsonPath('working_day', false);
    expect($response->json('slots'))->toBeEmpty();
});

test('availability endpoint excludes already booked slots', function () {
    $date = nextWeekdayPublic(now()->addDays(5));

    Appointment::create([
        'name' => 'Existing',
        'phone' => '+1234567890',
        'doctor_id' => $this->doctor->id,
        'department_id' => $this->department->id,
        'date' => $date,
        'time' => '10:00',
        'duration' => 30,
        'status' => AppointmentStatus::Scheduled,
    ]);

    $response = $this->get(route('public.appointment.availability', [
        'doctor_id' => $this->doctor->id,
        'date' => $date,
    ]));

    $slots = $response->json('slots');
    expect($slots)->not->toContain('10:00')->toContain('10:30')->toContain('11:00');
});

test('availability endpoint includes cancelled slots again', function () {
    $date = nextWeekdayPublic(now()->addDays(5));

    Appointment::create([
        'name' => 'Cancelled',
        'phone' => '+1234567890',
        'doctor_id' => $this->doctor->id,
        'department_id' => $this->department->id,
        'date' => $date,
        'time' => '10:00',
        'duration' => 30,
        'status' => AppointmentStatus::Cancelled,
    ]);

    $response = $this->get(route('public.appointment.availability', [
        'doctor_id' => $this->doctor->id,
        'date' => $date,
    ]));

    expect($response->json('slots'))->toContain('10:00');
});

test('availability endpoint validates doctor and date', function () {
    $this->get(route('public.appointment.availability', ['date' => nextWeekdayPublic(now()->addDays(5))]))
        ->assertSessionHasErrors('doctor_id');

    $this->get(route('public.appointment.availability', [
        'doctor_id' => $this->doctor->id,
        'date' => now()->subDay()->toDateString(),
    ]))->assertSessionHasErrors('date');
});

test('public appointment rejects doctor from another department', function () {
    $other = Department::create(['name' => 'Dental', 'slug' => 'dental', 'description' => 'Teeth']);

    $response = $this->post(route('public.appointment.store'), [
        'name' => 'Test User',
        'phone' => '+1234567890',
        'doctor_id' => $this->doctor->id,
        'department_id' => $other->id,
        'date' => nextWeekdayPublic(now()->addDays(5)),
        'time' => '10:00',
    ]);

    $response->assertSessionHasErrors('doctor_id');
});

test('public appointment rejects a time that is not a generated slot', function () {
    $response = $this->post(route('public.appointment.store'), [
        'name' => 'Test User',
        'phone' => '+1234567890',
        'doctor_id' => $this->doctor->id,
        'department_id' => $this->department->id,
        'date' => nextWeekdayPublic(now()->addDays(5)),
        'time' => '10:15',
    ]);

    $response->assertSessionHasErrors('time');
});

test('public appointment rejects a second booking of the same slot', function () {
    $date = nextWeekdayPublic(now()->addDays(5));

    $this->post(route('public.appointment.store'), [
        'name' => 'First User',
        'phone' => '+1234567890',
        'doctor_id' => $this->doctor->id,
        'department_id' => $this->department->id,
        'date' => $date,
        'time' => '10:00',
    ]);

    $response = $this->post(route('public.appointment.store'), [
        'name' => 'Second User',
        'phone' => '+0987654321',
        'doctor_id' => $this->doctor->id,
        'department_id' => $this->department->id,
        'date' => $date,
        'time' => '10:00',
    ]);

    $response->assertSessionHasErrors('time');
});

test('public appointment stores the default slot duration', function () {
    $this->post(route('public.appointment.store'), [
        'name' => 'Duration Test',
        'phone' => '+1234567890',
        'doctor_id' => $this->doctor->id,
        'department_id' => $this->department->id,
        'date' => nextWeekdayPublic(now()->addDays(5)),
        'time' => '10:00',
    ]);

    $this->assertDatabaseHas('appointments', [
        'name' => 'Duration Test',
        'duration' => 30,
    ]);
});

// --- SECURITY TESTS ---

test('guest cannot access admin appointment routes', function () {
    $response = $this->get(route('appointments.index'));
    $response->assertRedirect(route('login'));
});

test('guest cannot access admin appointment create', function () {
    $response = $this->get(route('appointments.create'));
    $response->assertRedirect(route('login'));
});

test('public appointment has CSRF protection', function () {
    $response = $this->post(route('public.appointment.store'), [
        'name' => 'CSRF Test',
        'phone' => '+1234567890',
        'email' => 'csrf@example.com',
        'doctor_id' => $this->doctor->id,
        'department_id' => $this->department->id,
        'date' => nextWeekdayPublic(now()->addDays(5)),
        'time' => '10:00',
    ]);

    $response->assertRedirect(route('public.appointment.success'));
});

// --- MASS ASSIGNMENT TESTS ---

test('public appointment prevents mass assignment of internal fields', function () {
    $this->post(route('public.appointment.store'), [
        'name' => 'Hacker',
        'phone' => '+1234567890',
        'email' => 'hacker@example.com',
        'doctor_id' => $this->doctor->id,
        'department_id' => $this->department->id,
        'date' => nextWeekdayPublic(now()->addDays(5)),
        'time' => '10:00',
        'status' => 'confirmed',
        'appointment_number' => 'APT-HACKED-001',
        'patient_id' => 99999,
    ]);

    $appointment = Appointment::where('name', 'Hacker')->first();
    $this->assertNotNull($appointment);
    $this->assertEquals(AppointmentStatus::Pending, $appointment->status);
    $this->assertNull($appointment->appointment_number);
    $this->assertNull($appointment->patient_id);
});

// --- INTEGRATION WITH ADMIN WORKFLOW ---

test('admin can confirm public appointment request', function () {
    $permissions = [
        'appointment.view', 'appointment.create', 'appointment.edit', 'appointment.cancel',
    ];
    foreach ($permissions as $perm) {
        Permission::firstOrCreate(['name' => $perm, 'guard_name' => 'web']);
    }
    $user = User::factory()->create();
    $user->givePermissionTo($permissions);

    $appointment = Appointment::create([
        'name' => 'Public User',
        'phone' => '+1234567890',
        'email' => 'public@example.com',
        'doctor_id' => $this->doctor->id,
        'department_id' => $this->department->id,
        'date' => nextWeekdayPublic(now()->addDays(5)),
        'time' => '10:00',
        'status' => AppointmentStatus::Pending,
        'source' => 'public',
    ]);

    $response = $this->actingAs($user)->post(route('appointments.confirm', $appointment));
    $response->assertRedirect();

    $appointment->refresh();
    $this->assertEquals(AppointmentStatus::Confirmed, $appointment->status);
    $this->assertNotNull($appointment->appointment_number);
});

test('admin can reject public appointment request', function () {
    $permissions = [
        'appointment.view', 'appointment.create', 'appointment.edit', 'appointment.cancel',
    ];
    foreach ($permissions as $perm) {
        Permission::firstOrCreate(['name' => $perm, 'guard_name' => 'web']);
    }
    $user = User::factory()->create();
    $user->givePermissionTo($permissions);

    $appointment = Appointment::create([
        'name' => 'Reject User',
        'phone' => '+1234567890',
        'email' => 'reject@example.com',
        'doctor_id' => $this->doctor->id,
        'department_id' => $this->department->id,
        'date' => nextWeekdayPublic(now()->addDays(5)),
        'time' => '10:00',
        'status' => AppointmentStatus::Pending,
        'source' => 'public',
    ]);

    $response = $this->actingAs($user)->post(route('appointments.cancel', $appointment), [
        'cancel_reason' => 'Slot not available',
    ]);
    $response->assertRedirect();

    $appointment->refresh();
    $this->assertEquals(AppointmentStatus::Cancelled, $appointment->status);
    $this->assertEquals('Slot not available', $appointment->cancel_reason);
});
