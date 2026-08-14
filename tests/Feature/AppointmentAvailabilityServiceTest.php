<?php

use App\Enums\AppointmentStatus;
use App\Models\Appointment;
use App\Models\Department;
use App\Models\Doctor;
use App\Models\User;
use App\Services\AppointmentAvailabilityService;

function availDate(\Carbon\Carbon $from = null): string
{
    $date = $from ?? now();
    while ($date->isWeekend()) {
        $date->addDay();
    }
    return $date->toDateString();
}

function makeAvailabilityDoctor(array $overrides = []): Doctor
{
    $department = Department::firstOrCreate(['name' => 'Cardiology', 'slug' => 'cardiology'], ['description' => 'Heart']);

    return Doctor::create(array_merge([
        'name' => 'Dr. Avail',
        'slug' => 'dr-avail-' . uniqid(),
        'department_id' => $department->id,
        'is_available' => true,
        'available_days' => [1, 2, 3, 4, 5],
        'start_time' => '09:00:00',
        'end_time' => '17:00:00',
        'user_id' => User::factory()->create()->id,
    ], $overrides));
}

beforeEach(function () {
    $this->service = app(AppointmentAvailabilityService::class);
});

// --- WORKING DAYS ---

test('working days are sorted and deduplicated', function () {
    $doctor = makeAvailabilityDoctor(['available_days' => [5, 1, 3, 1]]);

    expect($this->service->workingDays($doctor))->toBe([1, 3, 5]);
    expect($this->service->workingDayLabels($doctor))->toBe(['Mon', 'Wed', 'Fri']);
});

test('working day detection works for a given date', function () {
    $doctor = makeAvailabilityDoctor(['available_days' => [1, 2, 3, 4, 5]]);
    $monday = now()->next(Carbon\Carbon::MONDAY);

    expect($this->service->isWorkingDay($doctor, $monday))->toBeTrue();
    expect($this->service->isWorkingDay($doctor, $monday->next(Carbon\Carbon::SUNDAY)))->toBeFalse();
});

// --- WORKING HOURS ---

test('working hours normalize to H:i', function () {
    $doctor = makeAvailabilityDoctor(['start_time' => '09:00:00', 'end_time' => '17:00:00']);

    expect($this->service->workingHours($doctor))->toBe(['start' => '09:00', 'end' => '17:00']);
});

test('working hours returns null for an invalid schedule (end before start)', function () {
    $doctor = makeAvailabilityDoctor(['start_time' => '17:39:00', 'end_time' => '11:14:00']);

    expect($this->service->workingHours($doctor))->toBeNull();
    expect($this->service->availableSlots($doctor, availDate()))->toBeEmpty();
    expect($this->service->isBookable($doctor))->toBeFalse();
});

test('within working hours requires the booking to finish before end', function () {
    $doctor = makeAvailabilityDoctor(['start_time' => '09:00:00', 'end_time' => '17:00:00']);

    expect($this->service->isWithinWorkingHours($doctor, '08:59'))->toBeFalse();
    expect($this->service->isWithinWorkingHours($doctor, '09:00'))->toBeTrue();
    expect($this->service->isWithinWorkingHours($doctor, '16:30'))->toBeTrue();
    expect($this->service->isWithinWorkingHours($doctor, '16:45'))->toBeFalse();
    expect($this->service->isWithinWorkingHours($doctor, '17:00'))->toBeFalse();
});

// --- SLOT GENERATION ---

test('slots are generated from working hours at the default interval', function () {
    $doctor = makeAvailabilityDoctor();
    $date = availDate();

    $slots = $this->service->availableSlots($doctor, $date);

    expect($slots[0])->toBe('09:00');
    expect($slots[1])->toBe('09:30');
    expect($slots)->toContain('10:00')->toContain('12:00')->toContain('16:30');
    expect($slots)->not->toContain('17:00');
    // 09:00 -> 17:00 in 30-minute steps = 16 slots
    expect(count($slots))->toBe(16);
});

test('no slots on a non-working day', function () {
    $doctor = makeAvailabilityDoctor(['available_days' => [1]]);
    $sunday = now()->next(Carbon\Carbon::SUNDAY);

    expect($this->service->availableSlots($doctor, $sunday))->toBeEmpty();
});

test('booked slots are excluded from generation', function () {
    $doctor = makeAvailabilityDoctor();
    $this->doctor = $doctor;
    $date = availDate(now()->addDays(3));

    $bookedStart = $this->service->availableSlots($doctor, $date)[0];

    Appointment::create([
        'name' => 'Booked',
        'phone' => '+1234567890',
        'doctor_id' => $doctor->id,
        'department_id' => $doctor->department_id,
        'date' => $date,
        'time' => $bookedStart,
        'duration' => 30,
        'status' => AppointmentStatus::Scheduled,
    ]);

    $slots = $this->service->availableSlots($doctor, $date);

    expect($slots)->not->toContain($bookedStart);
});

test('cancelled appointments free the slot again', function () {
    $doctor = makeAvailabilityDoctor();
    $this->doctor = $doctor;
    $date = availDate(now()->addDays(4));

    $slot = $this->service->availableSlots($doctor, $date)[0];

    Appointment::create([
        'name' => 'Cancelled',
        'phone' => '+1234567890',
        'doctor_id' => $doctor->id,
        'department_id' => $doctor->department_id,
        'date' => $date,
        'time' => $slot,
        'duration' => 30,
        'status' => AppointmentStatus::Cancelled,
    ]);

    expect($this->service->availableSlots($doctor, $date))->toContain($slot);
});

// --- CONFLICT DETECTION ---

test('hasConflict detects overlapping bookings only', function () {
    $doctor = makeAvailabilityDoctor();
    $this->doctor = $doctor;
    $date = availDate(now()->addDays(5));

    Appointment::create([
        'name' => 'Existing',
        'phone' => '+1234567890',
        'doctor_id' => $doctor->id,
        'department_id' => $doctor->department_id,
        'date' => $date,
        'time' => '10:00',
        'duration' => 60,
        'status' => AppointmentStatus::Scheduled,
    ]);

    // 10:30 overlaps 10:00-11:00.
    expect($this->service->hasConflict($doctor->id, $date, '10:30', 30))->toBeTrue();
    // 09:30 finishes before 10:00 -> free.
    expect($this->service->hasConflict($doctor->id, $date, '09:30', 30))->toBeFalse();
    // 11:00 starts exactly when the existing ends -> free.
    expect($this->service->hasConflict($doctor->id, $date, '11:00', 30))->toBeFalse();
});

test('hasConflict ignores cancelled appointments and self', function () {
    $doctor = makeAvailabilityDoctor();
    $this->doctor = $doctor;
    $date = availDate(now()->addDays(6));

    $apt = Appointment::create([
        'name' => 'Cancelled',
        'phone' => '+1234567890',
        'doctor_id' => $doctor->id,
        'department_id' => $doctor->department_id,
        'date' => $date,
        'time' => '10:00',
        'duration' => 30,
        'status' => AppointmentStatus::Cancelled,
    ]);

    expect($this->service->hasConflict($doctor->id, $date, '10:00', 30))->toBeFalse();

    $apt2 = Appointment::create([
        'name' => 'Scheduled',
        'phone' => '+1234567890',
        'doctor_id' => $doctor->id,
        'department_id' => $doctor->department_id,
        'date' => availDate(now()->addDays(7)),
        'time' => '10:00',
        'duration' => 30,
        'status' => AppointmentStatus::Scheduled,
    ]);

    expect($this->service->hasConflict($doctor->id, $apt2->date, '10:00', 30, $apt2->id))->toBeFalse();
});

// --- BOOKABILITY ---

test('inactive doctor is not bookable', function () {
    $doctor = makeAvailabilityDoctor(['is_available' => false]);

    expect($this->service->isBookable($doctor))->toBeFalse();
});