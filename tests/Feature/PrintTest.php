<?php

use App\Models\Appointment;
use App\Models\Consultation;
use App\Models\Department;
use App\Models\Doctor;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Investigation;
use App\Models\LabTest;
use App\Models\Medicine;
use App\Models\Patient;
use App\Models\Payment;
use App\Models\Prescription;
use App\Models\PrescriptionItem;
use App\Models\QueueTicket;
use App\Models\User;
use App\Models\VitalSign;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(\Database\Seeders\PermissionSeeder::class);
    $this->user = User::factory()->create();
    $this->user->assignRole('admin');
    $this->actingAs($this->user);

    $this->department = Department::create(['name' => 'General', 'slug' => 'general', 'is_active' => true]);
    $this->doctor = Doctor::create([
        'name' => 'Dr. Smith', 'slug' => 'dr-smith', 'department_id' => $this->department->id,
        'is_available' => true, 'available_days' => [1,2,3,4,5], 'start_time' => '09:00', 'end_time' => '17:00',
    ]);
    $this->patient = Patient::create([
        'patient_number' => 'P-000001', 'name' => 'John Doe', 'email' => 'john@test.com',
        'phone' => '1234567890', 'date_of_birth' => '1990-01-01', 'gender' => 'male',
        'blood_group' => 'O+', 'allergies' => 'Penicillin', 'medical_history' => 'Diabetes',
    ]);
});

describe('Print Routes - Appointment', function () {
    it('renders appointment print view', function () {
        $appointment = Appointment::create([
            'patient_id' => $this->patient->id, 'doctor_id' => $this->doctor->id,
            'department_id' => $this->department->id, 'appointment_number' => 'APT-20260826-0001',
            'name' => 'John Doe', 'phone' => '1234567890', 'email' => 'john@test.com',
            'date' => now()->toDateString(), 'time' => '10:00', 'duration' => 30,
            'status' => 'scheduled',
        ]);

        $this->get(route('print.appointment', $appointment))
            ->assertOk()
            ->assertSee('Appointment Confirmation')
            ->assertSee('APT-20260826-0001')
            ->assertSee('John Doe')
            ->assertSee('Dr. Smith');
    });

    it('authorizes appointment print access', function () {
        $this->user->syncRoles([]);
        $appointment = Appointment::create([
            'patient_id' => $this->patient->id, 'doctor_id' => $this->doctor->id,
            'department_id' => $this->department->id, 'appointment_number' => 'APT-20260826-0001',
            'name' => 'John Doe', 'phone' => '1234567890', 'email' => 'john@test.com',
            'date' => now()->toDateString(), 'time' => '10:00', 'duration' => 30, 'status' => 'scheduled',
        ]);

        $this->get(route('print.appointment', $appointment))->assertForbidden();
    });
});

describe('Print Routes - Queue Ticket', function () {
    it('renders queue ticket print view', function () {
        $ticket = QueueTicket::create([
            'patient_id' => $this->patient->id, 'doctor_id' => $this->doctor->id,
            'queue_date' => now()->toDateString(), 'ticket_number' => 'A001',
            'status' => 'waiting', 'checked_in_at' => now(),
        ]);

        $this->get(route('print.queue-ticket', $ticket))
            ->assertOk()
            ->assertSee('Queue Ticket')
            ->assertSee('A001')
            ->assertSee('John Doe');
    });
});

describe('Print Routes - Prescription', function () {
    it('renders prescription print view', function () {
        $medicine = Medicine::create(['name' => 'Amoxicillin', 'unit_price' => 10, 'stock_quantity' => 100]);
        $prescription = Prescription::create([
            'patient_id' => $this->patient->id, 'doctor_id' => $this->doctor->id,
            'prescription_number' => 'RX-20260826-0001', 'prescribed_date' => now()->toDateString(),
        ]);
        PrescriptionItem::create([
            'prescription_id' => $prescription->id, 'medicine_id' => $medicine->id,
            'dosage' => '500mg', 'frequency' => 'Twice daily', 'duration' => '7 days',
            'quantity' => 14, 'instructions' => 'Take with food',
        ]);

        $this->get(route('print.prescription', $prescription))
            ->assertOk()
            ->assertSee('Prescription')
            ->assertSee('RX-20260826-0001')
            ->assertSee('Amoxicillin')
            ->assertSee('500mg')
            ->assertSee('Take with food');
    });
});

describe('Print Routes - Investigation', function () {
    it('renders investigation print view', function () {
        $labTest = LabTest::create(['name' => 'CBC', 'code' => 'CBC001', 'price' => 50, 'is_active' => true]);
        $investigation = Investigation::create([
            'patient_id' => $this->patient->id, 'doctor_id' => $this->doctor->id,
            'lab_test_id' => $labTest->id, 'status' => 'completed', 'priority' => 'routine',
            'requested_date' => now()->toDateString(), 'completed_date' => now()->toDateString(),
            'result_value' => '14.5', 'result_unit' => 'g/dL', 'result_reference_range' => '12-16 g/dL',
            'interpretation' => 'Normal',
        ]);

        $this->get(route('print.investigation', $investigation))
            ->assertOk()
            ->assertSee('Laboratory Investigation Report')
            ->assertSee('CBC')
            ->assertSee('14.5')
            ->assertSee('Normal');
    });
});

describe('Print Routes - Invoice', function () {
    it('renders invoice print view', function () {
        $invoice = Invoice::create([
            'invoice_number' => 'INV-20260826-0001', 'patient_id' => $this->patient->id,
            'doctor_id' => $this->doctor->id, 'subtotal' => 100, 'discount' => 10,
            'tax' => 9, 'total' => 99, 'amount_paid' => 50, 'balance' => 49,
            'status' => 'partially_paid',
        ]);
        InvoiceItem::create([
            'invoice_id' => $invoice->id, 'description' => 'Consultation',
            'type' => 'consultation', 'quantity' => 1, 'unit_price' => 100, 'total' => 100,
        ]);

        $this->get(route('print.invoice', $invoice))
            ->assertOk()
            ->assertSee('Invoice')
            ->assertSee('INV-20260826-0001')
            ->assertSee('John Doe')
            ->assertSee('Consultation')
            ->assertSee('99');
    });
});

describe('Print Routes - Payment Receipt', function () {
    it('renders payment receipt print view', function () {
        $invoice = Invoice::create([
            'invoice_number' => 'INV-20260826-0001', 'patient_id' => $this->patient->id,
            'doctor_id' => $this->doctor->id, 'subtotal' => 100, 'total' => 100,
            'amount_paid' => 100, 'balance' => 0, 'status' => 'paid',
        ]);
        $payment = Payment::create([
            'invoice_id' => $invoice->id, 'amount' => 100, 'payment_method' => 'cash',
            'paid_at' => now(), 'recorded_by' => $this->user->id,
        ]);

        $this->get(route('print.receipt', $payment))
            ->assertOk()
            ->assertSee('Payment Receipt')
            ->assertSee('100')
            ->assertSee('John Doe');
    });
});

describe('Print Routes - Medical Record', function () {
    it('renders medical record print view', function () {
        $this->get(route('print.medical-record', $this->patient))
            ->assertOk()
            ->assertSee('Patient Medical Record')
            ->assertSee('P-000001')
            ->assertSee('John Doe')
            ->assertSee('O+')
            ->assertSee('Penicillin')
            ->assertSee('Diabetes');
    });
});

describe('Print Routes - Reports', function () {
    it('renders financial report print view', function () {
        $this->get(route('print.report', ['type' => 'financial', 'start_date' => '2026-08-01', 'end_date' => '2026-08-31']))
            ->assertOk()
            ->assertSee('Financial Report')
            ->assertSee('Aug 01, 2026');
    });

    it('renders appointment report print view', function () {
        $this->get(route('print.report', ['type' => 'appointment', 'start_date' => '2026-08-01', 'end_date' => '2026-08-31']))
            ->assertOk()
            ->assertSee('Appointment Report');
    });

    it('renders patient report print view', function () {
        $this->get(route('print.report', ['type' => 'patient', 'start_date' => '2026-08-01', 'end_date' => '2026-08-31']))
            ->assertOk()
            ->assertSee('Patient Report');
    });

    it('renders inventory report print view', function () {
        $this->get(route('print.report', ['type' => 'inventory', 'start_date' => '2026-08-01', 'end_date' => '2026-08-31']))
            ->assertOk()
            ->assertSee('Inventory Report');
    });

    it('returns 404 for unknown report type', function () {
        $this->get(route('print.report', ['type' => 'unknown', 'start_date' => '2026-08-01', 'end_date' => '2026-08-31']))
            ->assertNotFound();
    });
});

describe('Currency Helper', function () {
    it('formats currency with default USD', function () {
        expect(fmt_money(1234.56))->toBe('$1,234.56');
    });

    it('formats zero amount', function () {
        expect(fmt_money(0))->toBe('$0.00');
    });

    it('formats null amount', function () {
        expect(fmt_money(null))->toBe('$0.00');
    });
});

describe('Clinic Header Data', function () {
    it('returns clinic header data array', function () {
        $data = clinic_header_data();
        expect($data)->toHaveKeys(['name', 'logo', 'address', 'phone', 'email', 'footer', 'currency']);
        expect($data['name'])->toBeString();
    });
});

describe('Print Views - Clinic Branding', function () {
    it('appointment print shows clinic name', function () {
        $appointment = Appointment::create([
            'patient_id' => $this->patient->id, 'doctor_id' => $this->doctor->id,
            'department_id' => $this->department->id, 'appointment_number' => 'APT-20260826-0001',
            'name' => 'John Doe', 'phone' => '1234567890', 'email' => 'john@test.com',
            'date' => now()->toDateString(), 'time' => '10:00', 'duration' => 30, 'status' => 'scheduled',
        ]);

        $response = $this->get(route('print.appointment', $appointment));
        $response->assertOk();
        $content = $response->getContent();
        expect($content)->toContain('clinic-header');
    });

    it('invoice print uses fmt_money not hardcoded dollar', function () {
        $invoice = Invoice::create([
            'invoice_number' => 'INV-20260826-0001', 'patient_id' => $this->patient->id,
            'doctor_id' => $this->doctor->id, 'subtotal' => 100, 'total' => 100,
            'amount_paid' => 0, 'balance' => 100, 'status' => 'issued',
        ]);

        $response = $this->get(route('print.invoice', $invoice));
        $content = $response->getContent();
        expect($content)->toContain('100.00')
            ->and($response)->assertOk();
    });
});

describe('Print Views - Financial Accuracy', function () {
    it('invoice print shows correct totals from model', function () {
        $invoice = Invoice::create([
            'invoice_number' => 'INV-20260826-0001', 'patient_id' => $this->patient->id,
            'doctor_id' => $this->doctor->id, 'subtotal' => 500, 'discount' => 50,
            'tax' => 45, 'total' => 495, 'amount_paid' => 200, 'balance' => 295,
            'status' => 'partially_paid',
        ]);

        $response = $this->get(route('print.invoice', $invoice));
        $content = $response->getContent();
        expect($content)->toContain('500')
            ->toContain('495')
            ->toContain('200')
            ->toContain('295');
    });
});
