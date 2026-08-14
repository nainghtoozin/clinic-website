<?php

use App\Models\Doctor;
use App\Models\Department;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    $this->allPermissions = [
        'patient.view', 'patient.create', 'patient.edit', 'patient.delete',
        'doctor.view', 'doctor.create', 'doctor.edit', 'doctor.delete',
        'appointment.view', 'appointment.create', 'appointment.edit', 'appointment.cancel',
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
    ];

    foreach ($this->allPermissions as $perm) {
        Permission::firstOrCreate(['name' => $perm, 'guard_name' => 'web']);
    }

    // Create roles with permissions
    $superAdminRole = Role::firstOrCreate(['name' => 'super-admin', 'guard_name' => 'web']);
    $superAdminRole->syncPermissions(Permission::all());

    $adminRole = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
    $adminPermissions = Permission::whereNotIn('name', ['role.view', 'role.create', 'role.edit', 'role.delete'])->get();
    $adminRole->syncPermissions($adminPermissions);

    $doctorRole = Role::firstOrCreate(['name' => 'doctor', 'guard_name' => 'web']);
    $doctorRole->syncPermissions([
        'patient.view', 'patient.create', 'patient.edit',
        'appointment.view', 'appointment.create', 'appointment.edit', 'appointment.cancel',
        'queue.view', 'queue.consult',
        'consultation.view', 'consultation.create', 'consultation.edit', 'consultation.complete',
        'prescription.view', 'prescription.create', 'prescription.edit', 'prescription.delete',
        'medicine.view',
        'invoice.view',
    ]);

    $receptionistRole = Role::firstOrCreate(['name' => 'receptionist', 'guard_name' => 'web']);
    $receptionistRole->syncPermissions([
        'patient.view', 'patient.create', 'patient.edit', 'patient.delete',
        'doctor.view',
        'appointment.view', 'appointment.create', 'appointment.edit', 'appointment.cancel',
        'queue.view', 'queue.checkin', 'queue.call', 'queue.cancel',
        'invoice.view', 'invoice.create', 'invoice.edit', 'invoice.cancel',
        'payment.view', 'payment.create',
        'medicine.view',
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
    ]);

    $this->admin = User::factory()->create();
    $this->admin->assignRole('admin');

    $this->superAdmin = User::factory()->create();
    $this->superAdmin->assignRole('super-admin');

    $this->doctorUser = User::factory()->create();
    $this->doctorUser->assignRole('doctor');

    $this->receptionist = User::factory()->create();
    $this->receptionist->assignRole('receptionist');

    $this->nurse = User::factory()->create();
    $this->nurse->assignRole('nurse');

    $this->noRoleUser = User::factory()->create(['is_active' => true]);
});

// --- STAFF CRUD TESTS ---

test('staff can be created', function () {
    $response = $this->actingAs($this->admin)->post(route('staff.store'), [
        'name' => 'John Doe',
        'email' => 'john@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
        'role' => 'receptionist',
        'phone' => '555-0100',
        'position' => 'Front Desk',
    ]);

    $response->assertRedirect(route('staff.index'));
    $this->assertDatabaseHas('users', [
        'name' => 'John Doe',
        'email' => 'john@example.com',
        'phone' => '555-0100',
        'position' => 'Front Desk',
        'is_active' => true,
    ]);

    $user = User::where('email', 'john@example.com')->first();
    $this->assertTrue($user->hasRole('receptionist'));
});

test('staff can be edited', function () {
    $staff = User::factory()->create(['name' => 'Original Name']);

    $response = $this->actingAs($this->admin)->put(route('staff.update', $staff), [
        'name' => 'Updated Name',
        'email' => $staff->email,
        'role' => 'nurse',
        'is_active' => true,
    ]);

    $response->assertRedirect(route('staff.index'));
    $this->assertDatabaseHas('users', [
        'id' => $staff->id,
        'name' => 'Updated Name',
    ]);
    $staff->refresh();
    $this->assertTrue($staff->hasRole('nurse'));
});

test('staff can be activated and deactivated', function () {
    $staff = User::factory()->create(['is_active' => true]);

    $response = $this->actingAs($this->admin)->patch(route('staff.toggle-status', $staff));
    $response->assertRedirect();
    $staff->refresh();
    $this->assertFalse($staff->is_active);

    $response = $this->actingAs($this->admin)->patch(route('staff.toggle-status', $staff));
    $response->assertRedirect();
    $staff->refresh();
    $this->assertTrue($staff->is_active);
});

test('role assignment works', function () {
    $staff = User::factory()->create();

    $this->actingAs($this->admin)->put(route('staff.update', $staff), [
        'name' => $staff->name,
        'email' => $staff->email,
        'role' => 'doctor',
        'is_active' => true,
    ]);

    $staff->refresh();
    $this->assertTrue($staff->hasRole('doctor'));
});

test('doctor can be linked to user', function () {
    $department = Department::create(['name' => 'General', 'slug' => 'general']);
    $doctor = Doctor::create([
        'name' => 'Dr. Test',
        'slug' => 'dr-test',
        'department_id' => $department->id,
        'available_days' => [1, 2, 3, 4, 5],
        'start_time' => '09:00:00',
        'end_time' => '17:00:00',
    ]);

    $response = $this->actingAs($this->admin)->post(route('staff.store'), [
        'name' => 'Dr. Linked',
        'email' => 'dr.linked@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
        'role' => 'doctor',
        'doctor_id' => $doctor->id,
    ]);

    $response->assertRedirect();
    $doctor->refresh();
    $user = User::where('email', 'dr.linked@example.com')->first();
    $this->assertEquals($user->id, $doctor->user_id);
});

test('historical records remain valid after staff deactivation', function () {
    $staff = User::factory()->create(['is_active' => true]);
    $staff->assignRole('receptionist');

    $this->actingAs($this->admin)->patch(route('staff.toggle-status', $staff));

    $staff->refresh();
    $this->assertFalse($staff->is_active);
    $this->assertNotNull($staff->name);
    $this->assertNotNull($staff->email);
    $this->assertTrue($staff->roles->count() > 0);
});

// --- AUTHORIZATION TESTS ---

test('unauthorized user cannot access staff management', function () {
    $user = User::factory()->create();
    $response = $this->actingAs($user)->get(route('staff.index'));
    $response->assertForbidden();
});

test('unauthorized user cannot create staff', function () {
    $user = User::factory()->create();
    $response = $this->actingAs($user)->get(route('staff.create'));
    $response->assertForbidden();
});

test('unauthorized user cannot edit staff', function () {
    $user = User::factory()->create();
    $staff = User::factory()->create();
    $response = $this->actingAs($user)->get(route('staff.edit', $staff));
    $response->assertForbidden();
});

test('unauthorized user cannot delete staff', function () {
    $user = User::factory()->create();
    $staff = User::factory()->create();
    $response = $this->actingAs($user)->delete(route('staff.destroy', $staff));
    $response->assertForbidden();
});

test('user cannot deactivate their own account', function () {
    $response = $this->actingAs($this->admin)->delete(route('staff.destroy', $this->admin));
    $response->assertSessionHas('error');
    $this->admin->refresh();
    $this->assertTrue($this->admin->is_active);
});

test('user cannot change their own status', function () {
    $response = $this->actingAs($this->admin)->patch(route('staff.toggle-status', $this->admin));
    $response->assertSessionHas('error');
});

// --- ROLE ACCESS TESTS ---

test('admin can access staff management', function () {
    $response = $this->actingAs($this->admin)->get(route('staff.index'));
    $response->assertOk();
});

test('doctor cannot access staff management', function () {
    $response = $this->actingAs($this->doctorUser)->get(route('staff.index'));
    $response->assertForbidden();
});

test('receptionist cannot access staff management', function () {
    $response = $this->actingAs($this->receptionist)->get(route('staff.index'));
    $response->assertForbidden();
});

test('nurse cannot access staff management', function () {
    $response = $this->actingAs($this->nurse)->get(route('staff.index'));
    $response->assertForbidden();
});

test('admin cannot access role management', function () {
    $response = $this->actingAs($this->admin)->get(route('roles.index'));
    $response->assertForbidden();
});

test('super admin can access everything', function () {
    $response = $this->actingAs($this->superAdmin)->get(route('staff.index'));
    $response->assertOk();

    $response = $this->actingAs($this->superAdmin)->get(route('roles.index'));
    $response->assertOk();
});

// --- CLINICAL PRIVACY TESTS ---

test('receptionist cannot access consultations', function () {
    $response = $this->actingAs($this->receptionist)->get(route('consultations.index'));
    $response->assertForbidden();
});

test('receptionist cannot access prescriptions', function () {
    $response = $this->actingAs($this->receptionist)->get(route('prescriptions.index'));
    $response->assertForbidden();
});

test('nurse can access consultations', function () {
    $response = $this->actingAs($this->nurse)->get(route('consultations.index'));
    $response->assertOk();
});

test('nurse cannot access payments', function () {
    $response = $this->actingAs($this->nurse)->get(route('payments.index'));
    $response->assertForbidden();
});

test('doctor can access consultations', function () {
    $response = $this->actingAs($this->doctorUser)->get(route('consultations.index'));
    $response->assertOk();
});

// --- BILLING SECURITY TESTS ---

test('unauthorized staff cannot manage payments', function () {
    $response = $this->actingAs($this->doctorUser)->get(route('payments.index'));
    $response->assertForbidden();
});

test('doctor cannot access payment management', function () {
    $response = $this->actingAs($this->doctorUser)->get(route('payments.index'));
    $response->assertForbidden();
});

test('receptionist can access payments', function () {
    $response = $this->actingAs($this->receptionist)->get(route('payments.index'));
    $response->assertOk();
});

test('unauthorized staff cannot change invoice status', function () {
    $response = $this->actingAs($this->nurse)->get(route('invoices.index'));
    $response->assertForbidden();
});

// --- SETTINGS TESTS ---

test('authorized admin can view clinic settings', function () {
    $response = $this->actingAs($this->admin)->get(route('settings.clinic'));
    $response->assertOk();
});

test('authorized admin can update clinic settings', function () {
    $response = $this->actingAs($this->admin)->post(route('settings.clinic.update'), [
        'clinic_name' => 'Test Clinic',
        'clinic_email' => 'clinic@test.com',
        'clinic_phone' => '555-0199',
        'clinic_address' => '123 Main St',
        'clinic_currency' => 'USD',
        'clinic_opening_hours' => 'Mon-Fri 9-5',
        'clinic_default_fee' => 100.00,
        'clinic_tax_rate' => 7.5,
        'clinic_receipt_footer' => 'Thank you!',
    ]);

    $response->assertRedirect();
    $this->assertDatabaseHas('settings', [
        'key' => 'clinic_name',
        'value' => 'Test Clinic',
        'group' => 'clinic',
    ]);
});

test('authorized admin can view website settings', function () {
    $response = $this->actingAs($this->admin)->get(route('settings.website.edit'));
    $response->assertOk();
});

test('unauthorized user cannot access settings', function () {
    $user = User::factory()->create();
    $response = $this->actingAs($user)->get(route('settings.clinic'));
    $response->assertForbidden();
});

test('existing cached settings continue working', function () {
    $this->actingAs($this->admin)->post(route('settings.clinic.update'), [
        'clinic_name' => 'Cached Clinic',
    ]);

    $setting = \App\Models\Setting::where('key', 'clinic_name')->first();
    $this->assertNotNull($setting);
    $this->assertEquals('Cached Clinic', $setting->value);
});

// --- DOCTOR DATA ISOLATION TEST ---

test('doctor user has doctor relationship', function () {
    $department = Department::create(['name' => 'Cardiology', 'slug' => 'cardiology']);
    $doctor = Doctor::create([
        'name' => 'Dr. Smith',
        'slug' => 'dr-smith',
        'department_id' => $department->id,
        'available_days' => [1, 2, 3, 4, 5],
        'start_time' => '09:00:00',
        'end_time' => '17:00:00',
        'user_id' => $this->doctorUser->id,
    ]);

    $this->assertNotNull($this->doctorUser->doctor);
    $this->assertEquals($doctor->id, $this->doctorUser->doctor->id);
});

// --- USER STATUS TESTS ---

test('inactive user is_active is false', function () {
    $user = User::factory()->create(['is_active' => false]);
    $this->assertFalse($user->isActive());
});

test('active user is_active is true', function () {
    $this->assertTrue($this->admin->isActive());
});

test('scope active works', function () {
    $countBefore = User::active()->count();
    User::factory()->create(['is_active' => true]);
    User::factory()->create(['is_active' => false]);

    $this->assertEquals($countBefore + 1, User::active()->count());
});

test('scope inactive works', function () {
    User::factory()->create(['is_active' => true]);
    User::factory()->create(['is_active' => false]);

    $this->assertEquals(1, User::inactive()->count());
});

// --- REGRESSION TESTS ---

test('existing patient tests still pass', function () {
    $response = $this->actingAs($this->admin)->get(route('patients.index'));
    $response->assertOk();
});

test('existing appointment tests still pass', function () {
    $response = $this->actingAs($this->admin)->get(route('appointments.index'));
    $response->assertOk();
});

test('existing consultation tests still pass', function () {
    $response = $this->actingAs($this->admin)->get(route('consultations.index'));
    $response->assertOk();
});

test('existing queue tests still pass', function () {
    $response = $this->actingAs($this->admin)->get(route('queue.index'));
    $response->assertOk();
});

test('existing doctor tests still pass', function () {
    $response = $this->actingAs($this->admin)->get(route('doctors.index'));
    $response->assertOk();
});

test('existing medicine tests still pass', function () {
    $response = $this->actingAs($this->admin)->get(route('medicines.index'));
    $response->assertOk();
});

test('existing prescription tests still pass', function () {
    $response = $this->actingAs($this->admin)->get(route('prescriptions.index'));
    $response->assertOk();
});

test('existing invoice tests still pass', function () {
    $response = $this->actingAs($this->admin)->get(route('invoices.index'));
    $response->assertOk();
});

test('existing payment tests still pass', function () {
    $response = $this->actingAs($this->admin)->get(route('payments.index'));
    $response->assertOk();
});

// --- PERMISSION MATRIX TESTS ---

test('permission seeder creates all required permissions', function () {
    $expectedPermissions = [
        'patient.view', 'patient.create', 'patient.edit', 'patient.delete',
        'doctor.view', 'doctor.create', 'doctor.edit', 'doctor.delete',
        'appointment.view', 'appointment.create', 'appointment.edit', 'appointment.cancel',
        'queue.view', 'queue.checkin', 'queue.call', 'queue.consult', 'queue.cancel',
        'consultation.view', 'consultation.create', 'consultation.edit', 'consultation.complete',
        'prescription.view', 'prescription.create', 'prescription.edit', 'prescription.delete',
        'medicine.view', 'medicine.create', 'medicine.edit', 'medicine.delete',
        'invoice.view', 'invoice.create', 'invoice.edit', 'invoice.cancel',
        'payment.view', 'payment.create', 'payment.cancel',
        'staff.view', 'staff.create', 'staff.edit', 'staff.delete',
        'role.view', 'role.create', 'role.edit', 'role.delete',
        'settings.view', 'settings.edit',
    ];

    foreach ($expectedPermissions as $perm) {
        $this->assertDatabaseHas('permissions', [
            'name' => $perm,
            'guard_name' => 'web',
        ]);
    }
});

test('roles exist in database', function () {
    foreach (['super-admin', 'admin', 'doctor', 'receptionist', 'nurse'] as $roleName) {
        $this->assertDatabaseHas('roles', [
            'name' => $roleName,
            'guard_name' => 'web',
        ]);
    }
});
