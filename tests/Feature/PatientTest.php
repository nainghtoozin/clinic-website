<?php

use App\Models\Patient;

test('patient number generation follows correct format', function () {
    $number = Patient::generatePatientNumber();
    expect($number)->toMatch('/^P-\d{6}$/');
});

test('patient number increments correctly', function () {
    $patient1 = Patient::factory()->create([
        'patient_number' => 'P-000005',
    ]);

    $nextNumber = Patient::generatePatientNumber();
    expect($nextNumber)->toBe('P-000006');
});

test('patient can be created with factory', function () {
    $patient = Patient::factory()->create();

    expect($patient->id)->not->toBeNull();
    expect($patient->patient_number)->toMatch('/^P-\d{6}$/');
    expect($patient->name)->not->toBeEmpty();
    expect($patient->status)->toBe('active');
});

test('patient has many appointments relationship', function () {
    $patient = Patient::factory()->create();

    expect($patient->appointments)->toHaveCount(0);
});
