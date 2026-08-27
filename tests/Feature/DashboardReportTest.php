<?php

use App\Enums\AppointmentStatus;
use App\Models\Appointment;
use App\Models\Consultation;
use App\Models\Department;
use App\Models\Doctor;
use App\Models\Invoice;
use App\Models\Medicine;
use App\Models\Payment;
use App\Models\Patient;
use App\Models\Prescription;
use App\Models\QueueTicket;
use App\Models\StockMovement;
use App\Models\User;
use Spatie\Permission\Models\Permission;

beforeEach(function () {
    $this->permissions = [
        'dashboard.view',
        'report.patient',
        'report.appointment',
        'report.consultation',
        'report.financial',
        'report.inventory',
        'patient.view', 'patient.create', 'patient.edit', 'patient.delete',
        'appointment.view', 'appointment.create', 'appointment.edit', 'appointment.cancel',
        'queue.view', 'queue.checkin', 'queue.call', 'queue.consult', 'queue.cancel',
        'consultation.view', 'consultation.create', 'consultation.edit', 'consultation.complete',
        'medicine.view', 'medicine.create',
        'invoice.view', 'invoice.create',
        'payment.view', 'payment.create',
        'inventory.view',
        'prescription.view',
        'doctor.view',
    ];

    foreach ($this->permissions as $perm) {
        Permission::firstOrCreate(['name' => $perm, 'guard_name' => 'web']);
    }

    $this->user = User::factory()->create();
    $this->user->givePermissionTo($this->permissions);

    $this->doctor = Doctor::factory()->create([
        'user_id' => $this->user->id,
        'is_available' => true,
    ]);

    $this->patient = Patient::factory()->create(['status' => 'active']);
});

// --- DASHBOARD ---

test('dashboard loads', function () {
    $response = $this->actingAs($this->user)->get(route('dashboard'));
    $response->assertOk();
});

test('dashboard shows today appointment count', function () {
    $response = $this->actingAs($this->user)->get(route('dashboard'));
    $response->assertOk();
    $response->assertSee("Appointments Today");
});

test('dashboard shows queue count', function () {
    QueueTicket::create([
        'patient_id' => $this->patient->id,
        'doctor_id' => $this->doctor->id,
        'queue_date' => now()->toDateString(),
        'ticket_number' => 'A001',
        'status' => 'waiting',
    ]);

    $response = $this->actingAs($this->user)->get(route('dashboard'));
    $response->assertOk();
    $response->assertSee('Queue Waiting');
});

test('dashboard shows consultation count', function () {
    $response = $this->actingAs($this->user)->get(route('dashboard'));
    $response->assertOk();
    $response->assertSee('Consultations');
});

test('dashboard shows low stock count', function () {
    Medicine::create([
        'name' => 'Low Stock Med',
        'unit_price' => 10.00,
        'stock_quantity' => 2,
        'minimum_stock_level' => 10,
        'is_active' => true,
    ]);

    $response = $this->actingAs($this->user)->get(route('dashboard'));
    $response->assertOk();
    $response->assertSee('Low Stock');
});

test('dashboard shows expired count', function () {
    Medicine::create([
        'name' => 'Expired Med',
        'unit_price' => 10.00,
        'stock_quantity' => 50,
        'minimum_stock_level' => 10,
        'is_active' => true,
        'expiry_date' => now()->subDays(5)->toDateString(),
    ]);

    $response = $this->actingAs($this->user)->get(route('dashboard'));
    $response->assertOk();
    $response->assertSee('Expired');
});

test('dashboard shows expiring soon count', function () {
    Medicine::create([
        'name' => 'Expiring Med',
        'unit_price' => 10.00,
        'stock_quantity' => 50,
        'minimum_stock_level' => 10,
        'is_active' => true,
        'expiry_date' => now()->addDays(15)->toDateString(),
    ]);

    $response = $this->actingAs($this->user)->get(route('dashboard'));
    $response->assertOk();
    $response->assertSee('Expiring Soon');
});

test('dashboard shows new patients today', function () {
    $response = $this->actingAs($this->user)->get(route('dashboard'));
    $response->assertOk();
    $response->assertSee('Patients Today');
});

test('dashboard unauthorized user cannot access', function () {
    $user = User::factory()->create();
    $user->givePermissionTo([]);

    $response = $this->actingAs($user)->get(route('dashboard'));
    $response->assertForbidden();
});

// --- DASHBOARD DATE RANGE FILTER ---

test('dashboard defaults the date filter to today', function () {
    $today = now()->toDateString();

    $response = $this->actingAs($this->user)->get(route('dashboard'));
    $response->assertOk();
    $response->assertSee('name="date_from" value="' . $today . '"', false);
    $response->assertSee('name="date_to" value="' . $today . '"', false);
});

test('dashboard date range changes date-sensitive statistics', function () {
    $past = now()->subDays(3)->toDateString();
    $department = Department::create(['name' => 'General', 'slug' => 'general']);

    Appointment::create([
        'patient_id' => $this->patient->id,
        'doctor_id' => $this->doctor->id,
        'department_id' => $department->id,
        'name' => $this->patient->name,
        'email' => $this->patient->email,
        'phone' => $this->patient->phone,
        'date' => $past,
        'time' => '10:00',
        'appointment_number' => 'APT-PAST-001',
        'status' => AppointmentStatus::Scheduled,
    ]);

    // The past appointment must appear when that day is the selected range...
    $this->actingAs($this->user)->get(route('dashboard', [
        'date_from' => $past,
        'date_to' => $past,
    ]))->assertOk()->assertSee('APT-PAST-001');

    // ...but not on the default (today) dashboard.
    $this->actingAs($this->user)->get(route('dashboard'))
        ->assertOk()
        ->assertDontSee('APT-PAST-001');
});

test('dashboard appointment list honours a multi-day date range', function () {
    $department = Department::create(['name' => 'General', 'slug' => 'general']);
    $start = now()->subDays(2)->toDateString();
    $end = now()->toDateString();

    Appointment::create([
        'patient_id' => $this->patient->id,
        'doctor_id' => $this->doctor->id,
        'department_id' => $department->id,
        'name' => $this->patient->name,
        'email' => $this->patient->email,
        'phone' => $this->patient->phone,
        'date' => $start,
        'time' => '09:00',
        'appointment_number' => 'APT-RANGE-001',
        'status' => AppointmentStatus::Scheduled,
    ]);

    $response = $this->actingAs($this->user)->get(route('dashboard', [
        'date_from' => $start,
        'date_to' => $end,
    ]));
    $response->assertOk();
    $response->assertSee('APT-RANGE-001');
});

test('dashboard billing totals use the selected date range', function () {
    $start = now()->subDays(3)->toDateString();
    $end = now()->toDateString();

    Invoice::create([
        'patient_id' => $this->patient->id,
        'total' => 250.00,
        'amount_paid' => 250.00,
        'balance' => 0.00,
        'status' => 'paid',
        'created_at' => $start,
    ]);

    $this->actingAs($this->user)->get(route('dashboard', [
        'date_from' => $start,
        'date_to' => $end,
    ]))->assertOk()->assertSee('250.00');

    // A different range that excludes the invoice must not show its total.
    $this->actingAs($this->user)->get(route('dashboard', [
        'date_from' => now()->subDays(10)->toDateString(),
        'date_to' => now()->subDays(5)->toDateString(),
    ]))->assertOk()->assertDontSee('250.00');
});

test('dashboard supports the this week preset', function () {
    $response = $this->actingAs($this->user)->get(route('dashboard', ['period' => 'this_week']));
    $response->assertOk();

    $startOfWeek = now()->startOfWeek(\Carbon\Carbon::SUNDAY);
    $endOfWeek = $startOfWeek->copy()->addDays(6)->toDateString();

    $response->assertSee('name="date_from" value="' . $startOfWeek->toDateString() . '"', false);
    $response->assertSee('name="date_to" value="' . $endOfWeek . '"', false);
});

test('dashboard supports the this month preset', function () {
    $response = $this->actingAs($this->user)->get(route('dashboard', ['period' => 'this_month']));
    $response->assertOk();

    $response->assertSee('name="date_from" value="' . now()->startOfMonth()->toDateString() . '"', false);
    $response->assertSee('name="date_to" value="' . now()->endOfMonth()->toDateString() . '"', false);
});

test('dashboard total appointments respects the selected date range', function () {
    $department = Department::create(['name' => 'General', 'slug' => 'general']);

    // Appointment A on 2026-08-19, Appointment B on 2026-08-21.
    foreach ([
        '2026-08-19' => 'APT-A-001',
        '2026-08-21' => 'APT-B-001',
    ] as $date => $number) {
        Appointment::create([
            'patient_id' => $this->patient->id,
            'doctor_id' => $this->doctor->id,
            'department_id' => $department->id,
            'name' => $this->patient->name,
            'email' => $this->patient->email,
            'phone' => $this->patient->phone,
            'date' => $date,
            'time' => '10:00',
            'appointment_number' => $number,
            'status' => AppointmentStatus::Scheduled,
        ]);
    }

    // 2026-08-19 → 2026-08-19  = 1
    $this->actingAs($this->user)->get(route('dashboard', [
        'date_from' => '2026-08-19', 'date_to' => '2026-08-19',
    ]))->assertOk()
        ->assertSeeInOrder(['Total Appointments', 'stat-value mb-0">1</h5>'], false);

    // 2026-08-20 → 2026-08-20  = 0 (no appointments that day)
    $this->actingAs($this->user)->get(route('dashboard', [
        'date_from' => '2026-08-20', 'date_to' => '2026-08-20',
    ]))->assertOk()
        ->assertSeeInOrder(['Total Appointments', 'stat-value mb-0">0</h5>'], false);

    // 2026-08-19 → 2026-08-21  = 2
    $this->actingAs($this->user)->get(route('dashboard', [
        'date_from' => '2026-08-19', 'date_to' => '2026-08-21',
    ]))->assertOk()
        ->assertSeeInOrder(['Total Appointments', 'stat-value mb-0">2</h5>'], false);

    // The default (today) dashboard must not count off-range appointments.
    $this->actingAs($this->user)->get(route('dashboard'))
        ->assertOk()
        ->assertSeeInOrder(['Total Appointments', 'stat-value mb-0">0</h5>'], false);
});

test('dashboard date-sensitive KPIs respect the selected range', function () {
    $department = Department::create(['name' => 'General', 'slug' => 'general']);
    $from = now()->subDays(2)->toDateString();
    $mid = now()->subDays(1)->toDateString();
    $to = now()->toDateString();

    // Records created/dated inside the range.
    $appointment = Appointment::create([
        'patient_id' => $this->patient->id,
        'doctor_id' => $this->doctor->id,
        'department_id' => $department->id,
        'name' => $this->patient->name,
        'email' => $this->patient->email,
        'phone' => $this->patient->phone,
        'date' => $mid,
        'time' => '09:00',
        'appointment_number' => 'APT-KPI-001',
        'status' => AppointmentStatus::Scheduled,
    ]);

    $consultation = Consultation::create([
        'patient_id' => $this->patient->id,
        'doctor_id' => $this->doctor->id,
        'appointment_id' => $appointment->id,
        'clinical_notes' => 'kpi range test',
        'status' => 'completed',
    ]);
    $consultation->update(['created_at' => $mid]);

    $prescription = Prescription::create([
        'patient_id' => $this->patient->id,
        'doctor_id' => $this->doctor->id,
        'prescribed_date' => $mid,
        'notes' => 'kpi range test',
    ]);
    $prescription->update(['created_at' => $mid]);

    QueueTicket::create([
        'patient_id' => $this->patient->id,
        'doctor_id' => $this->doctor->id,
        'queue_date' => $mid,
        'ticket_number' => 'KPI001',
        'status' => 'waiting',
    ]);

    // When the range covers them, each KPI shows the record.
    $this->actingAs($this->user)->get(route('dashboard', [
        'date_from' => $from, 'date_to' => $to,
    ]))->assertOk()
        ->assertSeeInOrder(['Total Appointments', 'stat-value mb-0">1</h5>'], false)
        ->assertSeeInOrder(['Queue Waiting', 'stat-value mb-0">1</h4>'], false)
        ->assertSeeInOrder(['Consultations', 'stat-value mb-0">1<'], false)
        ->assertSeeInOrder(['Prescriptions', 'stat-value mb-0">1</h5>'], false);

    // When the range excludes them, the same KPIs all drop to zero.
    $this->actingAs($this->user)->get(route('dashboard', [
        'date_from' => now()->subDays(10)->toDateString(),
        'date_to' => now()->subDays(6)->toDateString(),
    ]))->assertOk()
        ->assertSeeInOrder(['Total Appointments', 'stat-value mb-0">0</h5>'], false)
        ->assertSeeInOrder(['Queue Waiting', 'stat-value mb-0">0</h4>'], false)
        ->assertSeeInOrder(['Consultations', 'stat-value mb-0">0<'], false)
        ->assertSeeInOrder(['Prescriptions', 'stat-value mb-0">0</h5>'], false);
});

test('dashboard rejects a start date after the end date', function () {
    $from = now()->addDays(2)->toDateString();
    $to = now()->toDateString();

    $this->actingAs($this->user)
        ->from(route('dashboard'))
        ->get(route('dashboard', ['date_from' => $from, 'date_to' => $to]))
        ->assertSessionHasErrors('date_range')
        ->assertRedirect(route('dashboard'));
});

test('dashboard rejects malformed date input safely', function () {
    $this->actingAs($this->user)->get(route('dashboard', ['date_from' => 'not-a-date', 'date_to' => '2026-08-18']))
        ->assertRedirect(route('dashboard'));

    $this->actingAs($this->user)->get(route('dashboard', ['date_from' => '2026-13-99', 'date_to' => '2026-13-99']))
        ->assertRedirect(route('dashboard'));

    $this->actingAs($this->user)->get(route('dashboard', ['period' => 'bogus']))
        ->assertRedirect(route('dashboard'));
});

test('dashboard reset returns to the today range', function () {
    $this->actingAs($this->user)->get(route('dashboard', [
        'date_from' => now()->subDays(4)->toDateString(),
        'date_to' => now()->subDays(2)->toDateString(),
    ]))->assertOk();

    $today = now()->toDateString();

    $response = $this->actingAs($this->user)->get(route('dashboard'));
    $response->assertOk();
    $response->assertSee('name="date_from" value="' . $today . '"', false);
    $response->assertSee('name="date_to" value="' . $today . '"', false);
});

test('dashboard doctor summary is limited to 5 doctors', function () {
    foreach (range(2, 7) as $i) {
        Doctor::factory()->create([
            'name' => "Doc {$i}",
            'slug' => 'doctor-' . $i,
            'is_available' => true,
        ]);
    }

    $response = $this->actingAs($this->user)->get(route('dashboard'));
    $response->assertOk();

    // The five alphabetically-first available doctors are shown...
    $response->assertSee('Doc 2')
        ->assertSee('Doc 3')
        ->assertSee('Doc 6')
        // ...but the sixth available doctor is not loaded onto the dashboard.
        ->assertDontSee('Doc 7');
});

// --- FINANCIAL DASHBOARD KPI ---

test('dashboard shows financial KPIs for admin', function () {
    $response = $this->actingAs($this->user)->get(route('dashboard'));
    $response->assertOk();
    $response->assertSee('Invoiced Today');
    $response->assertSee('Paid Today');
    $response->assertSee('Outstanding');
});

// --- REPORT INDEX ---

test('report index loads', function () {
    $response = $this->actingAs($this->user)->get(route('reports.index'));
    $response->assertOk();
    $response->assertSee('Reports');
});

// --- PATIENT REPORT ---

test('patient report loads', function () {
    $response = $this->actingAs($this->user)->get(route('reports.patient'));
    $response->assertOk();
});

test('patient report shows patients', function () {
    Patient::factory()->count(3)->create(['status' => 'active']);

    $response = $this->actingAs($this->user)->get(route('reports.patient'));
    $response->assertOk();
});

test('patient report filters by date', function () {
    $response = $this->actingAs($this->user)->get(route('reports.patient', [
        'date_from' => now()->subDays(7)->toDateString(),
        'date_to' => now()->toDateString(),
    ]));
    $response->assertOk();
});

test('patient report filters by status', function () {
    $response = $this->actingAs($this->user)->get(route('reports.patient', [
        'status' => 'active',
    ]));
    $response->assertOk();
});

test('patient report filters by gender', function () {
    $response = $this->actingAs($this->user)->get(route('reports.patient', [
        'gender' => 'male',
    ]));
    $response->assertOk();
});

test('patient report filters by search', function () {
    $response = $this->actingAs($this->user)->get(route('reports.patient', [
        'search' => 'John',
    ]));
    $response->assertOk();
});

test('patient report paginates', function () {
    Patient::factory()->count(25)->create(['status' => 'active']);

    $response = $this->actingAs($this->user)->get(route('reports.patient'));
    $response->assertOk();
});

test('patient report unauthorized user cannot access', function () {
    $user = User::factory()->create();
    $user->givePermissionTo([]);

    $response = $this->actingAs($user)->get(route('reports.patient'));
    $response->assertForbidden();
});

// --- APPOINTMENT REPORT ---

test('appointment report loads', function () {
    $response = $this->actingAs($this->user)->get(route('reports.appointment'));
    $response->assertOk();
});

test('appointment report filters by date', function () {
    $response = $this->actingAs($this->user)->get(route('reports.appointment', [
        'date_from' => now()->subDays(7)->toDateString(),
        'date_to' => now()->toDateString(),
    ]));
    $response->assertOk();
});

test('appointment report filters by status', function () {
    $response = $this->actingAs($this->user)->get(route('reports.appointment', [
        'status' => 'scheduled',
    ]));
    $response->assertOk();
});

test('appointment report filters by doctor', function () {
    $response = $this->actingAs($this->user)->get(route('reports.appointment', [
        'doctor_id' => $this->doctor->id,
    ]));
    $response->assertOk();
});

test('appointment report unauthorized user cannot access', function () {
    $user = User::factory()->create();
    $user->givePermissionTo([]);

    $response = $this->actingAs($user)->get(route('reports.appointment'));
    $response->assertForbidden();
});

// --- CONSULTATION REPORT ---

test('consultation report loads', function () {
    $response = $this->actingAs($this->user)->get(route('reports.consultation'));
    $response->assertOk();
});

test('consultation report filters by date', function () {
    $response = $this->actingAs($this->user)->get(route('reports.consultation', [
        'date_from' => now()->subDays(7)->toDateString(),
        'date_to' => now()->toDateString(),
    ]));
    $response->assertOk();
});

test('consultation report filters by status', function () {
    $response = $this->actingAs($this->user)->get(route('reports.consultation', [
        'status' => 'completed',
    ]));
    $response->assertOk();
});

test('consultation report unauthorized user cannot access', function () {
    $user = User::factory()->create();
    $user->givePermissionTo([]);

    $response = $this->actingAs($user)->get(route('reports.consultation'));
    $response->assertForbidden();
});

// --- FINANCIAL REPORT ---

test('financial report loads', function () {
    $response = $this->actingAs($this->user)->get(route('reports.financial'));
    $response->assertOk();
});

test('financial report shows totals', function () {
    $response = $this->actingAs($this->user)->get(route('reports.financial'));
    $response->assertOk();
    $response->assertSee('Total Revenue');
    $response->assertSee('Total Expenses');
    $response->assertSee('Net Income');
    $response->assertSee('Outstanding');
});

test('financial report filters by date', function () {
    $response = $this->actingAs($this->user)->get(route('reports.financial', [
        'date_from' => now()->subDays(30)->toDateString(),
        'date_to' => now()->toDateString(),
    ]));
    $response->assertOk();
});

test('financial report filters by status', function () {
    $response = $this->actingAs($this->user)->get(route('reports.financial', [
        'status' => 'paid',
    ]));
    $response->assertOk();
});

test('financial report filters by payment method', function () {
    $response = $this->actingAs($this->user)->get(route('reports.financial', [
        'payment_method' => 'cash',
    ]));
    $response->assertOk();
});

test('financial report unauthorized user cannot access', function () {
    $user = User::factory()->create();
    $user->givePermissionTo([]);

    $response = $this->actingAs($user)->get(route('reports.financial'));
    $response->assertForbidden();
});

// --- INVENTORY REPORT ---

test('inventory report loads', function () {
    $response = $this->actingAs($this->user)->get(route('reports.inventory'));
    $response->assertOk();
});

test('inventory report shows medicine list', function () {
    Medicine::create([
        'name' => 'Test Med',
        'unit_price' => 10.00,
        'stock_quantity' => 50,
        'minimum_stock_level' => 10,
        'is_active' => true,
    ]);

    $response = $this->actingAs($this->user)->get(route('reports.inventory'));
    $response->assertOk();
    $response->assertSee('Test Med');
});

test('inventory report filters by stock status', function () {
    Medicine::create([
        'name' => 'Low Med',
        'unit_price' => 10.00,
        'stock_quantity' => 2,
        'minimum_stock_level' => 10,
        'is_active' => true,
    ]);

    $response = $this->actingAs($this->user)->get(route('reports.inventory', [
        'stock_status' => 'low',
    ]));
    $response->assertOk();
});

test('inventory report filters by search', function () {
    $response = $this->actingAs($this->user)->get(route('reports.inventory', [
        'search' => 'Test',
    ]));
    $response->assertOk();
});

test('inventory report shows recent movements', function () {
    $response = $this->actingAs($this->user)->get(route('reports.inventory'));
    $response->assertOk();
    $response->assertSee('Recent Stock Movements');
});

test('inventory report unauthorized user cannot access', function () {
    $user = User::factory()->create();
    $user->givePermissionTo([]);

    $response = $this->actingAs($user)->get(route('reports.inventory'));
    $response->assertForbidden();
});

// --- REGRESSION ---

test('existing patient tests still pass', function () {
    $response = $this->actingAs($this->user)->get(route('patients.index'));
    $response->assertOk();
});

test('existing appointment tests still pass', function () {
    $response = $this->actingAs($this->user)->get(route('appointments.index'));
    $response->assertOk();
});

test('existing queue tests still pass', function () {
    $response = $this->actingAs($this->user)->get(route('queue.index'));
    $response->assertOk();
});

test('existing consultation tests still pass', function () {
    $response = $this->actingAs($this->user)->get(route('consultations.index'));
    $response->assertOk();
});

test('existing inventory tests still pass', function () {
    $response = $this->actingAs($this->user)->get(route('inventory.dashboard'));
    $response->assertOk();
});

test('existing billing tests still pass', function () {
    $response = $this->actingAs($this->user)->get(route('invoices.index'));
    $response->assertOk();
});
