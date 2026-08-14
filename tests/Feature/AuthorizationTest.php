<?php

use App\Models\Doctor;
use App\Models\Patient;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    $allPermissions = [
        'patient.view', 'patient.create', 'patient.edit', 'patient.delete',
        'doctor.view', 'doctor.create', 'doctor.edit', 'doctor.delete',
        'appointment.view', 'appointment.create', 'appointment.edit', 'appointment.cancel', 'appointment.delete',
        'queue.view', 'queue.checkin', 'queue.call', 'queue.consult', 'queue.cancel',
        'consultation.view', 'consultation.create', 'consultation.edit', 'consultation.complete',
        'prescription.view', 'prescription.create', 'prescription.edit', 'prescription.delete',
        'medicine.view', 'medicine.create', 'medicine.edit', 'medicine.delete',
        'invoice.view', 'invoice.create', 'invoice.edit', 'invoice.cancel',
        'payment.view', 'payment.create', 'payment.cancel',
        'staff.view', 'staff.create', 'staff.edit', 'staff.delete',
        'role.view', 'role.create', 'role.edit', 'role.delete',
        'department.view', 'department.create', 'department.edit', 'department.delete',
        'service.view', 'service.create', 'service.edit', 'service.delete',
        'location.view', 'location.create', 'location.edit', 'location.delete',
        'settings.view', 'settings.edit',
        'inventory.view', 'inventory.opening_stock', 'inventory.stock_in', 'inventory.stock_out', 'inventory.adjust', 'inventory.dispense',
        'dashboard.view',
        'report.patient', 'report.appointment', 'report.consultation', 'report.financial', 'report.inventory',
    ];

    foreach ($allPermissions as $perm) {
        Permission::firstOrCreate(['name' => $perm, 'guard_name' => 'web']);
    }

    $superAdminRole = Role::firstOrCreate(['name' => 'super-admin', 'guard_name' => 'web']);
    $superAdminRole->syncPermissions(Permission::all());

    $adminRole = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
    $adminPermissions = Permission::whereNotIn('name', ['role.view', 'role.create', 'role.edit', 'role.delete'])->get();
    $adminRole->syncPermissions($adminPermissions);

    $doctorRole = Role::firstOrCreate(['name' => 'doctor', 'guard_name' => 'web']);
    $doctorRole->syncPermissions([
        'patient.view', 'patient.create', 'patient.edit',
        'appointment.view', 'appointment.create', 'appointment.edit', 'appointment.cancel', 'appointment.delete',
        'queue.view', 'queue.consult',
        'consultation.view', 'consultation.create', 'consultation.edit', 'consultation.complete',
        'prescription.view', 'prescription.create', 'prescription.edit', 'prescription.delete',
        'medicine.view', 'medicine.create', 'medicine.edit',
        'invoice.view',
        'dashboard.view',
        'report.patient', 'report.appointment', 'report.consultation', 'report.inventory',
    ]);

    $receptionistRole = Role::firstOrCreate(['name' => 'receptionist', 'guard_name' => 'web']);
    $receptionistRole->syncPermissions([
        'patient.view', 'patient.create', 'patient.edit', 'patient.delete',
        'doctor.view',
        'appointment.view', 'appointment.create', 'appointment.edit', 'appointment.cancel', 'appointment.delete',
        'queue.view', 'queue.checkin', 'queue.call', 'queue.cancel',
        'invoice.view', 'invoice.create', 'invoice.edit', 'invoice.cancel',
        'payment.view', 'payment.create',
        'medicine.view',
        'dashboard.view',
        'report.patient', 'report.appointment',
    ]);

    $nurseRole = Role::firstOrCreate(['name' => 'nurse', 'guard_name' => 'web']);
    $nurseRole->syncPermissions([
        'patient.view', 'patient.create', 'patient.edit',
        'doctor.view',
        'appointment.view',
        'queue.view', 'queue.checkin', 'queue.call', 'queue.cancel',
        'consultation.view', 'consultation.create', 'consultation.edit', 'consultation.complete',
        'medicine.view',
        'prescription.view',
        'dashboard.view',
        'report.patient', 'report.appointment', 'report.consultation',
    ]);

    $this->superAdmin = User::factory()->create();
    $this->superAdmin->assignRole('super-admin');

    $this->admin = User::factory()->create();
    $this->admin->assignRole('admin');

    $this->doctorUser = User::factory()->create();
    $this->doctorUser->assignRole('doctor');

    $this->receptionistUser = User::factory()->create();
    $this->receptionistUser->assignRole('receptionist');

    $this->nurseUser = User::factory()->create();
    $this->nurseUser->assignRole('nurse');

    $this->unauthorizedUser = User::factory()->create();

    $this->department = \App\Models\Department::create(['name' => 'General', 'slug' => 'general', 'is_active' => true]);
    $this->doctor = Doctor::factory()->create([
        'user_id' => $this->doctorUser->id,
        'department_id' => $this->department->id,
        'is_available' => true,
    ]);
    $this->patient = Patient::factory()->create(['status' => 'active']);
});

// =====================
// SUPER ADMIN
// =====================
test('super admin can access all modules', function () {
    $routes = [
        ['GET', '/dashboard'],
        ['GET', '/patients'],
        ['GET', '/patients/create'],
        ['GET', '/doctors'],
        ['GET', '/doctors/create'],
        ['GET', '/appointments'],
        ['GET', '/queue'],
        ['GET', '/consultations'],
        ['GET', '/prescriptions'],
        ['GET', '/medicines'],
        ['GET', '/inventory'],
        ['GET', '/inventory/medicines'],
        ['GET', '/invoices'],
        ['GET', '/payments'],
        ['GET', '/staff'],
        ['GET', '/users'],
        ['GET', '/roles'],
        ['GET', '/departments'],
        ['GET', '/locations'],
        ['GET', '/services'],
        ['GET', '/settings/website'],
        ['GET', '/settings/clinic'],
        ['GET', '/reports'],
    ];

    foreach ($routes as [$method, $uri]) {
        $response = $this->actingAs($this->superAdmin)->get($uri);
        $response->assertStatus(200);
    }
});

// =====================
// ADMIN
// =====================
test('admin can access admin modules', function () {
    $routes = [
        ['GET', '/dashboard'],
        ['GET', '/patients'],
        ['GET', '/patients/create'],
        ['GET', '/doctors'],
        ['GET', '/doctors/create'],
        ['GET', '/appointments'],
        ['GET', '/queue'],
        ['GET', '/consultations'],
        ['GET', '/prescriptions'],
        ['GET', '/medicines'],
        ['GET', '/inventory'],
        ['GET', '/invoices'],
        ['GET', '/payments'],
        ['GET', '/staff'],
        ['GET', '/users'],
        ['GET', '/departments'],
        ['GET', '/locations'],
        ['GET', '/services'],
        ['GET', '/settings/website'],
        ['GET', '/settings/clinic'],
        ['GET', '/reports'],
    ];

    foreach ($routes as [$method, $uri]) {
        $response = $this->actingAs($this->admin)->get($uri);
        $response->assertStatus(200);
    }
});

test('admin gets 403 for role management', function () {
    $this->actingAs($this->admin)->get('/roles')->assertStatus(403);
    $this->actingAs($this->admin)->get('/roles/create')->assertStatus(403);
});

// =====================
// DOCTOR
// =====================
test('doctor can access permitted modules', function () {
    $routes = [
        '/dashboard',
        '/patients',
        '/appointments',
        '/queue',
        '/consultations',
        '/prescriptions',
    ];

    foreach ($routes as $uri) {
        $response = $this->actingAs($this->doctorUser)->get($uri);
        $response->assertStatus(200);
    }
});

test('doctor gets 403 for unauthorized modules', function () {
    $forbiddenRoutes = [
        '/staff',
        '/users',
        '/roles',
        '/departments',
        '/locations',
        '/services',
        '/settings/website',
        '/payments',
    ];

    foreach ($forbiddenRoutes as $uri) {
        $response = $this->actingAs($this->doctorUser)->get($uri);
        $response->assertStatus(403);
    }
});

// =====================
// RECEPTIONIST
// =====================
test('receptionist can access permitted modules', function () {
    $routes = [
        '/dashboard',
        '/patients',
        '/appointments',
        '/queue',
        '/invoices',
        '/payments',
        '/medicines',
    ];

    foreach ($routes as $uri) {
        $response = $this->actingAs($this->receptionistUser)->get($uri);
        $response->assertStatus(200);
    }
});

test('receptionist gets 403 for unauthorized modules', function () {
    $forbiddenRoutes = [
        '/consultations',
        '/prescriptions',
        '/staff',
        '/users',
        '/roles',
        '/departments',
        '/locations',
        '/services',
        '/settings/website',
    ];

    foreach ($forbiddenRoutes as $uri) {
        $response = $this->actingAs($this->receptionistUser)->get($uri);
        $response->assertStatus(403);
    }
});

// =====================
// NURSE
// =====================
test('nurse can access permitted modules', function () {
    $routes = [
        '/dashboard',
        '/patients',
        '/appointments',
        '/queue',
        '/consultations',
        '/prescriptions',
        '/medicines',
    ];

    foreach ($routes as $uri) {
        $response = $this->actingAs($this->nurseUser)->get($uri);
        $response->assertStatus(200);
    }
});

test('nurse gets 403 for unauthorized modules', function () {
    $forbiddenRoutes = [
        '/invoices',
        '/payments',
        '/staff',
        '/users',
        '/roles',
        '/departments',
        '/locations',
        '/services',
        '/settings/website',
    ];

    foreach ($forbiddenRoutes as $uri) {
        $response = $this->actingAs($this->nurseUser)->get($uri);
        $response->assertStatus(403);
    }
});

// =====================
// UNAUTHORIZED USER (no role)
// =====================
test('user with no role gets 403 for all protected actions', function () {
    $routes = [
        '/dashboard',
        '/patients',
        '/doctors',
        '/appointments',
        '/queue',
        '/consultations',
        '/prescriptions',
        '/medicines',
        '/inventory',
        '/invoices',
        '/payments',
        '/staff',
        '/users',
        '/roles',
        '/departments',
    ];

    foreach ($routes as $uri) {
        $response = $this->actingAs($this->unauthorizedUser)->get($uri);
        $response->assertStatus(403);
    }
});

// =====================
// GUEST (not authenticated)
// =====================
test('unauthenticated user gets redirect for protected routes', function () {
    $routes = [
        '/dashboard',
        '/patients',
        '/doctors',
        '/appointments',
        '/queue',
        '/consultations',
        '/prescriptions',
        '/medicines',
        '/inventory',
        '/invoices',
        '/payments',
        '/staff',
    ];

    foreach ($routes as $uri) {
        $response = $this->get($uri);
        $response->assertRedirect('/login');
    }
});

// =====================
// SPECIFIC CONTROLLER AUTHORIZATION
// =====================
test('patient controller requires correct permissions', function () {
    $patient = Patient::factory()->create();

    $this->actingAs($this->doctorUser)->get('/patients')->assertStatus(200);
    $this->actingAs($this->doctorUser)->get("/patients/{$patient->id}")->assertStatus(200);
    $this->actingAs($this->doctorUser)->get('/patients/create')->assertStatus(200);

    $this->actingAs($this->unauthorizedUser)->get('/patients')->assertStatus(403);
    $this->actingAs($this->unauthorizedUser)->get("/patients/{$patient->id}")->assertStatus(403);
    $this->actingAs($this->unauthorizedUser)->get('/patients/create')->assertStatus(403);
});

test('doctor controller requires correct permissions', function () {
    $this->actingAs($this->admin)->get('/doctors')->assertStatus(200);
    $this->actingAs($this->admin)->get('/doctors/create')->assertStatus(200);

    $this->actingAs($this->unauthorizedUser)->get('/doctors')->assertStatus(403);
    $this->actingAs($this->unauthorizedUser)->get('/doctors/create')->assertStatus(403);
});

test('appointment controller requires correct permissions', function () {
    $this->actingAs($this->admin)->get('/appointments')->assertStatus(200);
    $this->actingAs($this->admin)->get('/appointments/create')->assertStatus(200);

    $this->actingAs($this->unauthorizedUser)->get('/appointments')->assertStatus(403);
    $this->actingAs($this->unauthorizedUser)->get('/appointments/create')->assertStatus(403);
});

test('invoice controller requires correct permissions', function () {
    $this->actingAs($this->receptionistUser)->get('/invoices')->assertStatus(200);

    $this->actingAs($this->unauthorizedUser)->get('/invoices')->assertStatus(403);
});

test('payment controller requires correct permissions', function () {
    $this->actingAs($this->receptionistUser)->get('/payments')->assertStatus(200);

    $this->actingAs($this->unauthorizedUser)->get('/payments')->assertStatus(403);
});

test('staff controller requires correct permissions', function () {
    $this->actingAs($this->admin)->get('/staff')->assertStatus(200);

    $this->actingAs($this->doctorUser)->get('/staff')->assertStatus(403);
    $this->actingAs($this->receptionistUser)->get('/staff')->assertStatus(403);
    $this->actingAs($this->unauthorizedUser)->get('/staff')->assertStatus(403);
});

test('settings controller requires correct permissions', function () {
    $this->actingAs($this->admin)->get('/settings/website')->assertStatus(200);
    $this->actingAs($this->admin)->get('/settings/clinic')->assertStatus(200);

    $this->actingAs($this->doctorUser)->get('/settings/website')->assertStatus(403);
    $this->actingAs($this->unauthorizedUser)->get('/settings/website')->assertStatus(403);
});

test('inventory controller requires correct permissions', function () {
    $this->actingAs($this->admin)->get('/inventory')->assertStatus(200);
    $this->actingAs($this->admin)->get('/inventory/medicines')->assertStatus(200);

    $this->actingAs($this->unauthorizedUser)->get('/inventory')->assertStatus(403);
    $this->actingAs($this->unauthorizedUser)->get('/inventory/medicines')->assertStatus(403);
});

test('reports controller requires correct permissions', function () {
    $this->actingAs($this->admin)->get('/reports')->assertStatus(200);
    $this->actingAs($this->admin)->get('/reports/patients')->assertStatus(200);

    $this->actingAs($this->unauthorizedUser)->get('/reports')->assertStatus(403);
    $this->actingAs($this->unauthorizedUser)->get('/reports/patients')->assertStatus(403);
});
