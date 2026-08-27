<?php

use App\Models\Doctor;
use App\Models\Department;
use App\Models\DoctorUnavailableDate;
use App\Services\AppointmentAvailabilityService;
use Carbon\Carbon;

beforeEach(function () {
    $this->service = new AppointmentAvailabilityService();
    $this->department = Department::create(['name' => 'Cardiology', 'slug' => 'cardiology']);
});

// --- BREAK HOURS ---

test('break hours returns null when no break configured', function () {
    $doctor = Doctor::create([
        'name' => 'Dr. Smith',
        'slug' => 'dr-smith',
        'department_id' => $this->department->id,
        'available_days' => [1, 2, 3, 4, 5],
        'start_time' => '09:00',
        'end_time' => '17:00',
    ]);

    $break = $this->service->breakHours($doctor);
    $this->assertNull($break);
});

test('break hours returns normalized times', function () {
    $doctor = Doctor::create([
        'name' => 'Dr. Smith',
        'slug' => 'dr-smith',
        'department_id' => $this->department->id,
        'available_days' => [1, 2, 3, 4, 5],
        'start_time' => '09:00',
        'end_time' => '17:00',
        'break_start' => '12:00',
        'break_end' => '13:00',
    ]);

    $break = $this->service->breakHours($doctor);
    $this->assertNotNull($break);
    $this->assertEquals('12:00', $break['start']);
    $this->assertEquals('13:00', $break['end']);
});

test('break hours returns null for invalid break (end before start)', function () {
    $doctor = Doctor::create([
        'name' => 'Dr. Smith',
        'slug' => 'dr-smith',
        'department_id' => $this->department->id,
        'available_days' => [1, 2, 3, 4, 5],
        'start_time' => '09:00',
        'end_time' => '17:00',
        'break_start' => '13:00',
        'break_end' => '12:00',
    ]);

    $break = $this->service->breakHours($doctor);
    $this->assertNull($break);
});

// --- DURING BREAK ---

test('is during break detects slot overlapping break', function () {
    $doctor = Doctor::create([
        'name' => 'Dr. Smith',
        'slug' => 'dr-smith',
        'department_id' => $this->department->id,
        'available_days' => [1, 2, 3, 4, 5],
        'start_time' => '09:00',
        'end_time' => '17:00',
        'break_start' => '12:00',
        'break_end' => '13:00',
    ]);

    $this->assertTrue($this->service->isDuringBreak($doctor, '11:30', 60));
    $this->assertTrue($this->service->isDuringBreak($doctor, '12:00', 30));
    $this->assertFalse($this->service->isDuringBreak($doctor, '13:00', 30));
    $this->assertFalse($this->service->isDuringBreak($doctor, '11:00', 60));
});

test('is during break returns false when no break configured', function () {
    $doctor = Doctor::create([
        'name' => 'Dr. Smith',
        'slug' => 'dr-smith',
        'department_id' => $this->department->id,
        'available_days' => [1, 2, 3, 4, 5],
        'start_time' => '09:00',
        'end_time' => '17:00',
    ]);

    $this->assertFalse($this->service->isDuringBreak($doctor, '12:00', 30));
});

// --- SLOTS EXCLUDE BREAK ---

test('available slots exclude break time', function () {
    $doctor = Doctor::create([
        'name' => 'Dr. Smith',
        'slug' => 'dr-smith',
        'department_id' => $this->department->id,
        'available_days' => [1, 2, 3, 4, 5],
        'start_time' => '09:00',
        'end_time' => '17:00',
        'break_start' => '12:00',
        'break_end' => '13:00',
    ]);

    $date = Carbon::now()->startOfWeek()->addDay(1);
    $slots = $this->service->availableSlots($doctor, $date, 60);

    $this->assertContains('09:00', $slots);
    $this->assertContains('13:00', $slots);
    $this->assertContains('14:00', $slots);
    $this->assertContains('15:00', $slots);
    $this->assertContains('16:00', $slots);
    $this->assertNotContains('12:00', $slots);
});

test('available slots work normally without break', function () {
    $doctor = Doctor::create([
        'name' => 'Dr. Smith',
        'slug' => 'dr-smith',
        'department_id' => $this->department->id,
        'available_days' => [1, 2, 3, 4, 5],
        'start_time' => '09:00',
        'end_time' => '17:00',
    ]);

    $date = Carbon::now()->startOfWeek()->addDay(1);
    $slots = $this->service->availableSlots($doctor, $date, 60);

    $this->assertContains('09:00', $slots);
    $this->assertContains('10:00', $slots);
    $this->assertContains('11:00', $slots);
    $this->assertContains('12:00', $slots);
    $this->assertContains('13:00', $slots);
    $this->assertContains('14:00', $slots);
    $this->assertContains('15:00', $slots);
    $this->assertContains('16:00', $slots);
});

// --- UNAVAILABLE DATES ---

test('is unavailable date returns true for marked date', function () {
    $doctor = Doctor::create([
        'name' => 'Dr. Smith',
        'slug' => 'dr-smith',
        'department_id' => $this->department->id,
        'available_days' => [1, 2, 3, 4, 5],
        'start_time' => '09:00',
        'end_time' => '17:00',
    ]);

    DoctorUnavailableDate::create([
        'doctor_id' => $doctor->id,
        'date' => '2026-09-01',
        'type' => 'leave',
        'reason' => 'Annual leave',
    ]);

    $this->assertTrue($this->service->isUnavailableDate($doctor, '2026-09-01'));
    $this->assertFalse($this->service->isUnavailableDate($doctor, '2026-09-02'));
});

test('get unavailable date returns record', function () {
    $doctor = Doctor::create([
        'name' => 'Dr. Smith',
        'slug' => 'dr-smith',
        'department_id' => $this->department->id,
        'available_days' => [1, 2, 3, 4, 5],
        'start_time' => '09:00',
        'end_time' => '17:00',
    ]);

    $record = DoctorUnavailableDate::create([
        'doctor_id' => $doctor->id,
        'date' => '2026-09-01',
        'type' => 'training',
        'reason' => 'Conference',
    ]);

    $result = $this->service->getUnavailableDate($doctor, '2026-09-01');
    $this->assertNotNull($result);
    $this->assertEquals($record->id, $result->id);
    $this->assertEquals('training', $result->type);
});

test('available slots return empty for unavailable date', function () {
    $doctor = Doctor::create([
        'name' => 'Dr. Smith',
        'slug' => 'dr-smith',
        'department_id' => $this->department->id,
        'available_days' => [1, 2, 3, 4, 5],
        'start_time' => '09:00',
        'end_time' => '17:00',
    ]);

    DoctorUnavailableDate::create([
        'doctor_id' => $doctor->id,
        'date' => '2026-09-01',
        'type' => 'leave',
    ]);

    $slots = $this->service->availableSlots($doctor, '2026-09-01');
    $this->assertEmpty($slots);
});

// --- DOCTOR MODEL ---

test('doctor has break method works', function () {
    $doctor = Doctor::create([
        'name' => 'Dr. Smith',
        'slug' => 'dr-smith',
        'department_id' => $this->department->id,
        'available_days' => [1, 2, 3, 4, 5],
        'start_time' => '09:00',
        'end_time' => '17:00',
    ]);

    $this->assertFalse($doctor->hasBreak());

    $doctor->update(['break_start' => '12:00', 'break_end' => '13:00']);
    $doctor->refresh();

    $this->assertTrue($doctor->hasBreak());
});

test('doctor has unavailable date method works', function () {
    $doctor = Doctor::create([
        'name' => 'Dr. Smith',
        'slug' => 'dr-smith',
        'department_id' => $this->department->id,
        'available_days' => [1, 2, 3, 4, 5],
        'start_time' => '09:00',
        'end_time' => '17:00',
    ]);

    $this->assertFalse($doctor->hasUnavailableDate('2026-09-01'));

    DoctorUnavailableDate::create([
        'doctor_id' => $doctor->id,
        'date' => '2026-09-01',
        'type' => 'leave',
    ]);

    $this->assertTrue($doctor->hasUnavailableDate('2026-09-01'));
});

// --- UNAVAILABLE DATE MODEL ---

test('unavailable date type labels work', function () {
    $doctor = Doctor::create([
        'name' => 'Dr. Smith',
        'slug' => 'dr-smith',
        'department_id' => $this->department->id,
        'available_days' => [1, 2, 3, 4, 5],
        'start_time' => '09:00',
        'end_time' => '17:00',
    ]);

    $leave = DoctorUnavailableDate::create(['doctor_id' => $doctor->id, 'date' => '2026-09-01', 'type' => 'leave']);
    $holiday = DoctorUnavailableDate::create(['doctor_id' => $doctor->id, 'date' => '2026-09-02', 'type' => 'holiday']);
    $training = DoctorUnavailableDate::create(['doctor_id' => $doctor->id, 'date' => '2026-09-03', 'type' => 'training']);
    $emergency = DoctorUnavailableDate::create(['doctor_id' => $doctor->id, 'date' => '2026-09-04', 'type' => 'emergency']);

    $this->assertEquals('Leave', $leave->getTypeLabel());
    $this->assertEquals('Holiday', $holiday->getTypeLabel());
    $this->assertEquals('Training', $training->getTypeLabel());
    $this->assertEquals('Emergency', $emergency->getTypeLabel());
});

test('unavailable date badge classes work', function () {
    $doctor = Doctor::create([
        'name' => 'Dr. Smith',
        'slug' => 'dr-smith',
        'department_id' => $this->department->id,
        'available_days' => [1, 2, 3, 4, 5],
        'start_time' => '09:00',
        'end_time' => '17:00',
    ]);

    $leave = DoctorUnavailableDate::create(['doctor_id' => $doctor->id, 'date' => '2026-09-01', 'type' => 'leave']);
    $this->assertEquals('bg-warning text-dark', $leave->getTypeBadgeClass());
});
