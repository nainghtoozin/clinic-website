<?php

use App\Models\AuditLog;
use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Models\Patient;
use App\Models\User;
use App\Services\AuditService;
use Spatie\Permission\Models\Permission;

beforeEach(function () {
    $this->permissions = [
        'audit.view', 'patient.view', 'patient.create', 'patient.edit', 'patient.delete',
        'dashboard.view', 'expense.view', 'expense.create', 'expense.edit', 'expense.delete',
    ];

    foreach ($this->permissions as $perm) {
        Permission::firstOrCreate(['name' => $perm, 'guard_name' => 'web']);
    }

    $this->user = User::factory()->create();
    $this->user->givePermissionTo($this->permissions);
});

test('audit log model has correct fillable fields', function () {
    $log = new AuditLog();
    $expected = [
        'user_id', 'action', 'module', 'auditable_type', 'auditable_id',
        'description', 'old_values', 'new_values', 'metadata', 'ip_address', 'user_agent',
    ];
    $this->assertEquals($expected, $log->getFillable());
});

test('audit log model casts correctly', function () {
    $log = new AuditLog();
    $casts = $log->getCasts();
    $this->assertEquals('array', $casts['old_values']);
    $this->assertEquals('array', $casts['new_values']);
    $this->assertEquals('array', $casts['metadata']);
});

test('audit log action label accessor works', function () {
    $log = AuditLog::create(['action' => 'created', 'module' => 'Patient']);
    $this->assertEquals('Created', $log->action_label);

    $log2 = AuditLog::create(['action' => 'status_changed', 'module' => 'Patient']);
    $this->assertEquals('Status Changed', $log2->action_label);
});

test('audit log module label accessor works', function () {
    $log = AuditLog::create(['action' => 'created', 'module' => 'Patient']);
    $this->assertEquals('Patient', $log->module_label);

    $log2 = AuditLog::create(['action' => 'created', 'module' => 'VitalSigns']);
    $this->assertEquals('Vital Signs', $log2->module_label);
});

test('audit log badge class accessor works', function () {
    $this->assertEquals('bg-success', (new AuditLog(['action' => 'created']))->action_badge_class);
    $this->assertEquals('bg-primary', (new AuditLog(['action' => 'updated']))->action_badge_class);
    $this->assertEquals('bg-danger', (new AuditLog(['action' => 'deleted']))->action_badge_class);
    $this->assertEquals('bg-info', (new AuditLog(['action' => 'status_changed']))->action_badge_class);
    $this->assertEquals('bg-secondary', (new AuditLog(['action' => 'login']))->action_badge_class);
});

test('audit log formatted_changes accessor filters sensitive fields', function () {
    $log = AuditLog::create([
        'action' => 'updated',
        'module' => 'User',
        'old_values' => ['name' => 'Old', 'password' => 'secret123'],
        'new_values' => ['name' => 'New', 'password' => 'newsecret'],
    ]);

    $changes = $log->formatted_changes;
    $this->assertArrayHasKey('name', $changes);
    $this->assertArrayNotHasKey('password', $changes);
    $this->assertEquals('Old', $changes['name']['old']);
    $this->assertEquals('New', $changes['name']['new']);
});

test('audit log scopes work correctly', function () {
    $user = User::factory()->create();
    AuditLog::create(['action' => 'created', 'module' => 'Patient', 'user_id' => $user->id]);
    AuditLog::create(['action' => 'updated', 'module' => 'Invoice', 'user_id' => $user->id]);
    AuditLog::create(['action' => 'created', 'module' => 'Invoice', 'user_id' => null]);

    $this->assertCount(1, AuditLog::forModule('Patient')->get());
    $this->assertCount(2, AuditLog::forAction('created')->get());
    $this->assertCount(2, AuditLog::forUser($user->id)->get());
});

test('audit log sanitizeValues removes sensitive fields', function () {
    $data = ['name' => 'John', 'password' => 'secret', 'email' => 'john@test.com', 'api_token' => 'token123'];
    $sanitized = AuditLog::sanitizeValues($data);

    $this->assertArrayHasKey('name', $sanitized);
    $this->assertArrayHasKey('email', $sanitized);
    $this->assertArrayNotHasKey('password', $sanitized);
    $this->assertArrayNotHasKey('api_token', $sanitized);
});

test('audit service log creates entry', function () {
    $this->actingAs($this->user);
    $log = AuditService::log('created', 'Patient', null, 'Test log');

    $this->assertNotNull($log);
    $this->assertEquals('created', $log->action);
    $this->assertEquals('Patient', $log->module);
    $this->assertEquals('Test log', $log->description);
    $this->assertEquals($this->user->id, $log->user_id);
});

test('audit service logCreated works', function () {
    $patient = Patient::create([
        'patient_number' => 'PAT-TEST-001',
        'name' => 'Test Patient',
        'status' => 'active',
    ]);

    $log = AuditService::logCreated($patient, 'Patient');

    $this->assertNotNull($log);
    $this->assertEquals('created', $log->action);
    $this->assertEquals('Patient', $log->module);
    $this->assertEquals('App\Models\Patient', $log->auditable_type);
    $this->assertEquals($patient->id, $log->auditable_id);
    $this->assertNotNull($log->new_values);
    $this->assertNull($log->old_values);
});

test('audit service logUpdated captures changes only', function () {
    $patient = Patient::create([
        'patient_number' => 'PAT-TEST-002',
        'name' => 'Original Name',
        'status' => 'active',
    ]);

    $old = $patient->toArray();
    $patient->update(['name' => 'Updated Name']);
    $log = AuditService::logUpdated($patient, 'Patient', $old, ['name' => 'Updated Name']);

    $this->assertNotNull($log);
    $this->assertEquals('updated', $log->action);
    $this->assertArrayHasKey('name', $log->new_values);
    $this->assertEquals('Original Name', $log->old_values['name']);
    $this->assertEquals('Updated Name', $log->new_values['name']);
});

test('audit service logUpdated returns null when no changes', function () {
    $patient = Patient::create([
        'patient_number' => 'PAT-TEST-003',
        'name' => 'Same Name',
        'status' => 'active',
    ]);

    $old = $patient->toArray();
    $log = AuditService::logUpdated($patient, 'Patient', $old, ['name' => 'Same Name']);

    $this->assertNull($log);
});

test('audit service logDeleted works', function () {
    $patient = Patient::create([
        'patient_number' => 'PAT-TEST-004',
        'name' => 'Delete Me',
        'status' => 'active',
    ]);

    $log = AuditService::logDeleted($patient, 'Patient');

    $this->assertNotNull($log);
    $this->assertEquals('deleted', $log->action);
    $this->assertNotNull($log->old_values);
    $this->assertNull($log->new_values);
});

test('audit service logStatusChange works', function () {
    $patient = Patient::create([
        'patient_number' => 'PAT-TEST-005',
        'name' => 'Status Test',
        'status' => 'active',
    ]);

    $log = AuditService::logStatusChange($patient, 'Patient', 'active', 'inactive');

    $this->assertNotNull($log);
    $this->assertEquals('status_changed', $log->action);
    $this->assertEquals('active', $log->old_values['status']);
    $this->assertEquals('inactive', $log->new_values['status']);
});

test('audit service logLogin works', function () {
    $this->actingAs($this->user);
    $log = AuditService::logLogin();

    $this->assertNotNull($log);
    $this->assertEquals('login', $log->action);
    $this->assertEquals('Auth', $log->module);
    $this->assertEquals($this->user->id, $log->user_id);
});

test('audit service logLogout works', function () {
    $log = AuditService::logLogout();

    $this->assertNotNull($log);
    $this->assertEquals('logout', $log->action);
    $this->assertEquals('Auth', $log->module);
});

test('audit service logRoleChanged works', function () {
    $log = AuditService::logRoleChanged(1, ['admin'], ['doctor']);

    $this->assertNotNull($log);
    $this->assertEquals('role_changed', $log->action);
    $this->assertEquals(['admin'], $log->old_values['roles']);
    $this->assertEquals(['doctor'], $log->new_values['roles']);
});

test('audit service sanitizes sensitive data', function () {
    $patient = Patient::create([
        'patient_number' => 'PAT-TEST-006',
        'name' => 'Sensitive Test',
        'status' => 'active',
    ]);

    $log = AuditService::log('created', 'Patient', $patient, 'test', null, [
        'name' => 'Test',
        'password' => 'secret123',
    ]);

    $this->assertArrayNotHasKey('password', $log->new_values);
    $this->assertArrayHasKey('name', $log->new_values);
});

test('audit log page requires authentication', function () {
    $response = $this->get(route('audit-logs.index'));
    $response->assertRedirect();
});

test('audit log page requires audit.view permission', function () {
    $user = User::factory()->create();
    $response = $this->actingAs($user)->get(route('audit-logs.index'));
    $response->assertForbidden();
});

test('audit log page loads for authorized user', function () {
    $response = $this->actingAs($this->user)->get(route('audit-logs.index'));
    $response->assertOk();
    $response->assertSee('Audit Logs');
});

test('audit log detail page loads', function () {
    $log = AuditLog::create([
        'action' => 'created',
        'module' => 'Patient',
        'description' => 'Test detail view',
        'user_id' => $this->user->id,
    ]);

    $response = $this->actingAs($this->user)->get(route('audit-logs.show', $log));
    $response->assertOk();
    $response->assertSee('Test detail view');
});

test('audit log detail returns JSON for AJAX', function () {
    $log = AuditLog::create([
        'action' => 'updated',
        'module' => 'Patient',
        'description' => 'Test AJAX',
        'old_values' => ['name' => 'Old'],
        'new_values' => ['name' => 'New'],
        'user_id' => $this->user->id,
    ]);

    $response = $this->actingAs($this->user)
        ->withHeaders(['X-Requested-With' => 'XMLHttpRequest', 'Accept' => 'application/json'])
        ->get(route('audit-logs.show', $log));
    $response->assertOk();
    $response->assertJsonFragment(['action' => 'Updated']);
});

test('audit log index filters by module', function () {
    AuditLog::create(['action' => 'created', 'module' => 'Patient']);
    AuditLog::create(['action' => 'created', 'module' => 'Invoice']);

    $response = $this->actingAs($this->user)->get(route('audit-logs.index', ['module' => 'Patient']));
    $response->assertOk();
    $response->assertSee('Patient');
});

test('audit log index filters by action', function () {
    AuditLog::create(['action' => 'created', 'module' => 'Patient']);
    AuditLog::create(['action' => 'deleted', 'module' => 'Patient']);

    $response = $this->actingAs($this->user)->get(route('audit-logs.index', ['action' => 'created']));
    $response->assertOk();
});

test('audit log index filters by user', function () {
    AuditLog::create(['action' => 'created', 'module' => 'Patient', 'user_id' => $this->user->id]);
    $other = User::factory()->create();
    AuditLog::create(['action' => 'created', 'module' => 'Patient', 'user_id' => $other->id]);

    $response = $this->actingAs($this->user)->get(route('audit-logs.index', ['user_id' => $this->user->id]));
    $response->assertOk();
});

test('audit log index filters by date range', function () {
    AuditLog::create(['action' => 'created', 'module' => 'Patient', 'created_at' => '2026-01-15']);
    AuditLog::create(['action' => 'created', 'module' => 'Patient', 'created_at' => '2026-06-15']);

    $response = $this->actingAs($this->user)->get(route('audit-logs.index', [
        'date_from' => '2026-06-01',
        'date_to' => '2026-06-30',
    ]));
    $response->assertOk();
});

test('audit log index searches by description', function () {
    AuditLog::create(['action' => 'created', 'module' => 'Patient', 'description' => 'John Doe patient created']);
    AuditLog::create(['action' => 'created', 'module' => 'Patient', 'description' => 'Jane Smith patient created']);

    $response = $this->actingAs($this->user)->get(route('audit-logs.index', ['search' => 'John']));
    $response->assertOk();
    $response->assertSee('John Doe');
});

test('audit log index paginates', function () {
    for ($i = 0; $i < 30; $i++) {
        AuditLog::create(['action' => 'created', 'module' => 'Patient']);
    }

    $response = $this->actingAs($this->user)->get(route('audit-logs.index'));
    $response->assertOk();
    $response->assertSee('25');
});

test('audit log entries are immutable - no edit route exists', function () {
    $response = $this->actingAs($this->user)->get(route('audit-logs.index'));
    $response->assertOk();

    $log = AuditLog::create(['action' => 'created', 'module' => 'Patient']);

    $response = $this->actingAs($this->user)->put(route('audit-logs.show', $log), ['description' => 'hacked']);
    $response->assertStatus(405);
});

test('patient create triggers audit log', function () {
    $this->actingAs($this->user)->post(route('patients.store'), [
        'name' => 'Audit Test Patient',
        'date_of_birth' => '1990-01-01',
        'gender' => 'male',
        'status' => 'active',
    ]);

    $log = AuditLog::where('action', 'created')
        ->where('module', 'Patient')
        ->where('auditable_type', 'App\Models\Patient')
        ->latest()
        ->first();

    $this->assertNotNull($log);
    $this->assertEquals($this->user->id, $log->user_id);
    $this->assertNotNull($log->new_values);
});

test('patient update triggers audit log with changes', function () {
    $patient = Patient::create([
        'patient_number' => 'PAT-AUDIT-001',
        'name' => 'Original Name',
        'status' => 'active',
    ]);

    $this->actingAs($this->user)->put(route('patients.update', $patient), [
        'name' => 'Updated Name',
        'status' => 'active',
    ]);

    $log = AuditLog::where('action', 'updated')
        ->where('module', 'Patient')
        ->where('auditable_id', $patient->id)
        ->latest()
        ->first();

    $this->assertNotNull($log);
    $this->assertEquals('Original Name', $log->old_values['name'] ?? null);
    $this->assertEquals('Updated Name', $log->new_values['name'] ?? null);
});

test('patient delete triggers audit log', function () {
    $patient = Patient::create([
        'patient_number' => 'PAT-AUDIT-002',
        'name' => 'Delete Me',
        'status' => 'active',
    ]);

    $this->actingAs($this->user)->delete(route('patients.destroy', $patient));

    $log = AuditLog::where('action', 'deleted')
        ->where('module', 'Patient')
        ->where('auditable_id', $patient->id)
        ->latest()
        ->first();

    $this->assertNotNull($log);
});

test('patient restore triggers audit log', function () {
    $patient = Patient::create([
        'patient_number' => 'PAT-AUDIT-003',
        'name' => 'Restore Me',
        'status' => 'active',
    ]);
    $patient->delete();

    $this->actingAs($this->user)->post(route('patients.restore', $patient->id));

    $log = AuditLog::where('action', 'restored')
        ->where('module', 'Patient')
        ->where('auditable_id', $patient->id)
        ->latest()
        ->first();

    $this->assertNotNull($log);
});

test('expense create triggers audit log', function () {
    $category = ExpenseCategory::create(['name' => 'Test Category', 'status' => 'active']);

    $this->actingAs($this->user)->post(route('expenses.store'), [
        'expense_category_id' => $category->id,
        'amount' => 100.00,
        'payment_method' => 'cash',
        'expense_date' => '2026-08-25',
        'description' => 'Audit test expense',
    ]);

    $log = AuditLog::where('action', 'created')
        ->where('module', 'Expense')
        ->latest()
        ->first();

    $this->assertNotNull($log);
    $this->assertEquals($this->user->id, $log->user_id);
});

test('expense cancel triggers status_changed audit log', function () {
    $category = ExpenseCategory::create(['name' => 'Test Category', 'status' => 'active']);
    $expense = Expense::create([
        'expense_number' => 'EXP-AUDIT-001',
        'expense_category_id' => $category->id,
        'amount' => 50.00,
        'payment_method' => 'cash',
        'expense_date' => '2026-08-25',
        'description' => 'Cancel me',
        'status' => 'active',
        'created_by' => $this->user->id,
    ]);

    $this->actingAs($this->user)->delete(route('expenses.destroy', $expense));

    $log = AuditLog::where('action', 'status_changed')
        ->where('module', 'Expense')
        ->where('auditable_id', $expense->id)
        ->latest()
        ->first();

    $this->assertNotNull($log);
    $this->assertEquals('active', $log->old_values['status'] ?? null);
    $this->assertEquals('cancelled', $log->new_values['status'] ?? null);
});

test('login creates audit log', function () {
    $this->actingAs($this->user);
    AuditService::logLogin();

    $log = AuditLog::where('action', 'login')->latest()->first();
    $this->assertNotNull($log);
    $this->assertEquals($this->user->id, $log->user_id);
});

test('logout creates audit log', function () {
    AuditService::logLogout();

    $log = AuditLog::where('action', 'logout')->latest()->first();
    $this->assertNotNull($log);
});

test('role change creates audit log', function () {
    AuditService::logRoleChanged($this->user->id, ['admin'], ['doctor']);

    $log = AuditLog::where('action', 'role_changed')->latest()->first();
    $this->assertNotNull($log);
    $this->assertEquals(['admin'], $log->old_values['roles']);
    $this->assertEquals(['doctor'], $log->new_values['roles']);
});

test('audit log stores IP address', function () {
    $log = AuditService::log('created', 'Patient');
    $this->assertNotNull($log->ip_address);
});

test('audit log stores user agent', function () {
    $log = AuditService::log('created', 'Patient');
    $this->assertNotNull($log->user_agent);
});

test('audit log without model stores null auditable fields', function () {
    $log = AuditService::logLogin();

    $this->assertNull($log->auditable_type);
    $this->assertNull($log->auditable_id);
});

test('unauthorized user cannot access audit logs', function () {
    $user = User::factory()->create();
    $response = $this->actingAs($user)->get(route('audit-logs.index'));
    $response->assertForbidden();
});

test('guest cannot access audit logs', function () {
    $response = $this->get(route('audit-logs.index'));
    $response->assertRedirect();
});

test('audit log user relationship works', function () {
    $log = AuditLog::create([
        'action' => 'created',
        'module' => 'Patient',
        'user_id' => $this->user->id,
    ]);

    $this->assertNotNull($log->user);
    $this->assertEquals($this->user->id, $log->user->id);
});

test('audit log with null user shows System', function () {
    $log = AuditLog::create([
        'action' => 'login',
        'module' => 'Auth',
        'user_id' => null,
    ]);

    $this->assertNull($log->user);
});

test('audit constants contain all expected modules', function () {
    $expected = ['Patient', 'Doctor', 'Appointment', 'Consultation', 'Prescription', 'Invoice', 'Payment', 'Expense', 'Investigation', 'Backup'];
    foreach ($expected as $module) {
        $this->assertArrayHasKey($module, AuditLog::MODULES);
    }
});

test('audit constants contain all expected actions', function () {
    $expected = ['created', 'updated', 'deleted', 'restored', 'cancelled', 'completed', 'status_changed', 'login', 'logout', 'backup_created', 'backup_restored'];
    foreach ($expected as $action) {
        $this->assertArrayHasKey($action, AuditLog::ACTIONS);
    }
});

test('formatted_changes returns null when no values', function () {
    $log = AuditLog::create(['action' => 'login', 'module' => 'Auth']);
    $this->assertNull($log->formatted_changes);
});

test('backup_created audit log works', function () {
    $log = AuditService::log('backup_created', 'Backup', null, 'Full backup created');
    $this->assertNotNull($log);
    $this->assertEquals('backup_created', $log->action);
    $this->assertEquals('Backup', $log->module);
});

test('backup_restored audit log works', function () {
    $log = AuditService::log('backup_restored', 'Backup', null, 'Backup restored');
    $this->assertNotNull($log);
    $this->assertEquals('backup_restored', $log->action);
});

test('backup_deleted audit log works', function () {
    $log = AuditService::log('backup_deleted', 'Backup', null, 'Backup removed');
    $this->assertNotNull($log);
    $this->assertEquals('backup_deleted', $log->action);
});

test('audit log created_at timestamp is stored', function () {
    $log = AuditService::log('created', 'Patient');
    $this->assertNotNull($log->created_at);
});
