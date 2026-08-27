<?php

use App\Models\Appointment;
use App\Models\Consultation;
use App\Models\Department;
use App\Models\Doctor;
use App\Models\Expense;
use App\Models\Investigation;
use App\Models\Invoice;
use App\Models\LabTest;
use App\Models\Medicine;
use App\Models\Patient;
use App\Models\Prescription;
use App\Models\StockMovement;
use App\Models\User;
use Database\Seeders\DemoDataSeeder;
use Illuminate\Support\Facades\Artisan;

it('seeds demo data successfully', function () {
    Artisan::call('db:seed', ['--class' => DemoDataSeeder::class, '--force' => true]);

    expect(User::where('email', 'like', '%@clinic-demo.test')->count())->toBeGreaterThanOrEqual(5);
    expect(Patient::where('patient_number', 'like', 'DEMO-P-%')->count())->toBeGreaterThanOrEqual(15);
    expect(Doctor::count())->toBeGreaterThanOrEqual(5);
    expect(Department::where('slug', 'like', '%-demo')->count())->toBe(6);
});

it('creates demo users with correct roles', function () {
    Artisan::call('db:seed', ['--class' => DemoDataSeeder::class, '--force' => true]);

    $superAdmin = User::where('email', 'superadmin@clinic-demo.test')->first();
    expect($superAdmin)->not->toBeNull();
    expect($superAdmin->hasRole('super-admin'))->toBeTrue();

    $admin = User::where('email', 'admin@clinic-demo.test')->first();
    expect($admin)->not->toBeNull();
    expect($admin->hasRole('admin'))->toBeTrue();

    $doctor = User::where('email', 'doctor@clinic-demo.test')->first();
    expect($doctor)->not->toBeNull();
    expect($doctor->hasRole('doctor'))->toBeTrue();

    $nurse = User::where('email', 'nurse@clinic-demo.test')->first();
    expect($nurse)->not->toBeNull();
    expect($nurse->hasRole('nurse'))->toBeTrue();

    $reception = User::where('email', 'reception@clinic-demo.test')->first();
    expect($reception)->not->toBeNull();
    expect($reception->hasRole('receptionist'))->toBeTrue();
});

it('creates doctors with valid schedules', function () {
    Artisan::call('db:seed', ['--class' => DemoDataSeeder::class, '--force' => true]);

    $doctors = Doctor::whereHas('user', fn($q) => $q->where('email', 'like', '%@clinic-demo.test'))
        ->orWhereIn('name', [
            'Dr. Aung Myo', 'Dr. Thin Thin Aye', 'Dr. Kyaw Zin Lin',
            'Dr. Mar Mar Aye', 'Dr. Zaw Min Htut',
        ])
        ->get();

    expect($doctors->count())->toBeGreaterThanOrEqual(5);

    foreach ($doctors as $doctor) {
        expect($doctor->available_days)->not->toBeEmpty();
        expect($doctor->start_time)->not->toBeNull();
        expect($doctor->end_time)->not->toBeNull();
        expect($doctor->break_start)->not->toBeNull();
        expect($doctor->break_end)->not->toBeNull();
        expect($doctor->department_id)->not->toBeNull();
        expect($doctor->is_active)->toBeTrue();
    }
});

it('creates patients with all required fields', function () {
    Artisan::call('db:seed', ['--class' => DemoDataSeeder::class, '--force' => true]);

    $patients = Patient::where('patient_number', 'like', 'DEMO-P-%')->get();
    expect($patients->count())->toBeGreaterThanOrEqual(15);

    foreach ($patients as $patient) {
        expect($patient->name)->not->toBeEmpty();
        expect($patient->phone)->not->toBeNull();
        expect($patient->date_of_birth)->not->toBeNull();
        expect($patient->gender)->toBeIn(['male', 'female', 'other']);
        expect($patient->blood_group)->not->toBeNull();
        expect($patient->status)->toBe('active');
    }
});

it('creates appointments with valid dates against doctor schedules', function () {
    Artisan::call('db:seed', ['--class' => DemoDataSeeder::class, '--force' => true]);

    $appointments = Appointment::where('appointment_number', 'like', 'APT-%')
        ->where('source', 'reception')
        ->get();

    expect($appointments->count())->toBeGreaterThanOrEqual(5);

    foreach ($appointments as $apt) {
        $doctor = Doctor::find($apt->doctor_id);
        expect($doctor)->not->toBeNull();

        $dayOfWeek = (int) $apt->date->format('N');
        expect($dayOfWeek)->toBeIn($doctor->available_days);
    }
});

it('creates consultations with vital signs', function () {
    Artisan::call('db:seed', ['--class' => DemoDataSeeder::class, '--force' => true]);

    $consultations = Consultation::where('status', 'completed')
        ->whereHas('patient', fn($q) => $q->where('patient_number', 'like', 'DEMO-P-%'))
        ->get();

    expect($consultations->count())->toBeGreaterThanOrEqual(3);

    foreach ($consultations as $consultation) {
        expect($consultation->patient_id)->not->toBeNull();
        expect($consultation->doctor_id)->not->toBeNull();
        expect($consultation->symptoms)->not->toBeNull();
        expect($consultation->diagnosis)->not->toBeNull();
        expect($consultation->treatment_plan)->not->toBeNull();

        $vitalSign = $consultation->vitalSign;
        expect($vitalSign)->not->toBeNull();
        expect($vitalSign->blood_pressure)->not->toBeNull();
        expect($vitalSign->temperature)->toBeGreaterThan(35);
        expect($vitalSign->temperature)->toBeLessThan(40);
        expect($vitalSign->pulse)->toBeGreaterThan(50);
        expect($vitalSign->pulse)->toBeLessThan(150);
    }
});

it('creates prescriptions with valid items', function () {
    Artisan::call('db:seed', ['--class' => DemoDataSeeder::class, '--force' => true]);

    $prescriptions = Prescription::whereHas('patient', fn($q) => $q->where('patient_number', 'like', 'DEMO-P-%'))->get();
    expect($prescriptions->count())->toBeGreaterThanOrEqual(3);

    foreach ($prescriptions as $prescription) {
        expect($prescription->prescription_number)->not->toBeNull();
        expect($prescription->patient_id)->not->toBeNull();
        expect($prescription->doctor_id)->not->toBeNull();
        expect($prescription->consultation_id)->not->toBeNull();
        expect($prescription->status)->toBe('active');

        $items = $prescription->items;
        expect($items->count())->toBeGreaterThan(0);

        foreach ($items as $item) {
            expect($item->medicine_id)->not->toBeNull();
            expect($item->dosage)->not->toBeNull();
            expect($item->frequency)->not->toBeNull();
            expect($item->quantity)->toBeGreaterThan(0);
        }
    }
});

it('creates medicines with valid inventory', function () {
    Artisan::call('db:seed', ['--class' => DemoDataSeeder::class, '--force' => true]);

    $medicines = Medicine::whereIn('name', [
        'Paracetamol 500mg', 'Amoxicillin 500mg', 'Cetirizine 10mg',
        'Omeprazole 20mg', 'Ibuprofen 400mg', 'Amlodipine 5mg',
    ])->get();

    expect($medicines->count())->toBe(6);

    foreach ($medicines as $medicine) {
        expect($medicine->is_active)->toBeTrue();
        expect($medicine->unit_price)->toBeGreaterThan(0);
        expect($medicine->stock_quantity)->toBeGreaterThan(0);
        expect($medicine->inventoryBatches()->count())->toBeGreaterThan(0);
    }
});

it('creates inventory batches with valid stock quantities', function () {
    Artisan::call('db:seed', ['--class' => DemoDataSeeder::class, '--force' => true]);

    $batches = Medicine::whereIn('name', ['Paracetamol 500mg', 'Amoxicillin 500mg'])->first()
        ->inventoryBatches()->get();

    expect($batches->count())->toBeGreaterThanOrEqual(1);

    foreach ($batches as $batch) {
        expect($batch->quantity)->toBeGreaterThanOrEqual(0);
        expect($batch->batch_number)->not->toBeEmpty();
        expect($batch->received_date)->not->toBeNull();
        expect($batch->expiry_date)->not->toBeNull();
        expect($batch->status)->toBe('active');
    }

    $expiredBatch = Medicine::where('name', 'Amoxicillin 500mg')->first()
        ->inventoryBatches()
        ->where('batch_number', 'BATCH-002-EXP')
        ->first();

    if ($expiredBatch) {
        expect($expiredBatch->expiry_date->isPast())->toBeTrue();
    }

    $expiringSoonBatch = Medicine::where('name', 'Paracetamol 500mg')->first()
        ->inventoryBatches()
        ->where('batch_number', 'BATCH-001-EXP-SOON')
        ->first();

    if ($expiringSoonBatch) {
        expect($expiringSoonBatch->expiry_date->diffInDays(now()))->toBeLessThanOrEqual(30);
    }
});

it('creates invoices with correct totals', function () {
    Artisan::call('db:seed', ['--class' => DemoDataSeeder::class, '--force' => true]);

    $invoices = Invoice::whereHas('patient', fn($q) => $q->where('patient_number', 'like', 'DEMO-P-%'))->get();
    expect($invoices->count())->toBeGreaterThanOrEqual(3);

    foreach ($invoices as $invoice) {
        expect($invoice->invoice_number)->not->toBeNull();
        expect($invoice->subtotal)->toBeGreaterThan(0);
        expect($invoice->total)->toBeGreaterThan(0);

        $expectedTotal = $invoice->subtotal - $invoice->discount + $invoice->tax;
        expect(round((float) $invoice->total, 2))->toEqual(round($expectedTotal, 2));

        $expectedBalance = max(0, $invoice->total - $invoice->amount_paid);
        expect(round((float) $invoice->balance, 2))->toEqual(round($expectedBalance, 2));

        expect($invoice->status)->toBeIn(['issued', 'partially_paid', 'paid', 'cancelled']);
    }
});

it('creates payments that match invoice totals', function () {
    Artisan::call('db:seed', ['--class' => DemoDataSeeder::class, '--force' => true]);

    $paidInvoices = Invoice::whereHas('patient', fn($q) => $q->where('patient_number', 'like', 'DEMO-P-%'))
        ->whereIn('status', ['paid', 'partially_paid'])
        ->get();

    expect($paidInvoices->count())->toBeGreaterThanOrEqual(3);

    foreach ($paidInvoices as $invoice) {
        $payments = $invoice->payments;
        expect($payments->count())->toBeGreaterThan(0);

        $totalPaid = $payments->sum('amount');
        expect(round((float) $totalPaid, 2))->toEqual(round((float) $invoice->amount_paid, 2));

        foreach ($payments as $payment) {
            expect($payment->amount)->toBeGreaterThan(0);
            expect($payment->payment_method)->toBeIn(['cash', 'card', 'bank_transfer', 'mobile_payment']);
            expect($payment->recorded_by)->not->toBeNull();
        }
    }
});

it('creates investigations with valid relationships', function () {
    Artisan::call('db:seed', ['--class' => DemoDataSeeder::class, '--force' => true]);

    $investigations = Investigation::whereHas('patient', fn($q) => $q->where('patient_number', 'like', 'DEMO-P-%'))->get();
    expect($investigations->count())->toBeGreaterThanOrEqual(3);

    foreach ($investigations as $investigation) {
        expect($investigation->patient_id)->not->toBeNull();
        expect($investigation->doctor_id)->not->toBeNull();
        expect($investigation->lab_test_id)->not->toBeNull();
        expect($investigation->requested_date)->not->toBeNull();
        expect($investigation->priority)->toBeIn(['urgent', 'stat', 'routine']);
        expect($investigation->status)->toBeIn(['requested', 'in_progress', 'completed', 'cancelled']);

        $labTest = LabTest::find($investigation->lab_test_id);
        expect($labTest)->not->toBeNull();

        if ($investigation->status === 'completed') {
            expect($investigation->result_value)->not->toBeNull();
            expect($investigation->result_unit)->not->toBeNull();
        }
    }
});

it('creates expenses with valid categories', function () {
    Artisan::call('db:seed', ['--class' => DemoDataSeeder::class, '--force' => true]);

    $expenses = Expense::where('expense_number', 'like', 'EXP-DEMO-%')->get();
    expect($expenses->count())->toBeGreaterThanOrEqual(5);

    foreach ($expenses as $expense) {
        expect($expense->expense_category_id)->not->toBeNull();
        expect($expense->amount)->toBeGreaterThan(0);
        expect($expense->payment_method)->toBeIn(['cash', 'card', 'bank_transfer', 'mobile_payment']);
        expect($expense->expense_date)->not->toBeNull();
        expect($expense->status)->toBe('active');
        expect($expense->created_by)->not->toBeNull();
    }
});

it('creates communications with valid data', function () {
    Artisan::call('db:seed', ['--class' => DemoDataSeeder::class, '--force' => true]);

    $comms = \App\Models\Communication::whereHas('patient', fn($q) => $q->where('patient_number', 'like', 'DEMO-P-%'))->get();
    expect($comms->count())->toBeGreaterThanOrEqual(3);

    foreach ($comms as $comm) {
        expect($comm->patient_id)->not->toBeNull();
        expect($comm->contact_method)->toBeIn(['phone', 'in_person', 'sms', 'email', 'telegram', 'other']);
        expect($comm->purpose)->not->toBeNull();
        expect($comm->outcome)->not->toBeNull();
        expect($comm->contacted_at)->not->toBeNull();
        expect($comm->user_id)->not->toBeNull();
    }
});

it('does not create uncontrolled duplicates on repeated runs', function () {
    Artisan::call('db:seed', ['--class' => DemoDataSeeder::class, '--force' => true]);
    $count1 = Patient::where('patient_number', 'like', 'DEMO-P-%')->count();
    $doctorCount1 = Doctor::whereHas('user', fn($q) => $q->where('email', 'like', '%@clinic-demo.test'))->count();
    $medCount1 = Medicine::whereIn('name', ['Paracetamol 500mg', 'Amoxicillin 500mg'])->count();

    Artisan::call('db:seed', ['--class' => DemoDataSeeder::class, '--force' => true]);
    $count2 = Patient::where('patient_number', 'like', 'DEMO-P-%')->count();
    $doctorCount2 = Doctor::whereHas('user', fn($q) => $q->where('email', 'like', '%@clinic-demo.test'))->count();
    $medCount2 = Medicine::whereIn('name', ['Paracetamol 500mg', 'Amoxicillin 500mg'])->count();

    expect($count2)->toBe($count1);
    expect($doctorCount2)->toBe($doctorCount1);
    expect($medCount2)->toBe($medCount1);
});
