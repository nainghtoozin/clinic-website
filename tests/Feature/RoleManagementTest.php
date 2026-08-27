<?php

use App\Models\User;
use App\Services\AuditService;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    $this->permissions = [
        'role.view', 'role.create', 'role.edit', 'role.delete',
        'patient.view', 'patient.create', 'patient.edit',
    ];

    foreach ($this->permissions as $perm) {
        Permission::firstOrCreate(['name' => $perm, 'guard_name' => 'web']);
    }

    $this->user = User::factory()->create();
    $this->user->givePermissionTo($this->permissions);
});

test('role list accessible only to authorized users', function () {
    $response = $this->actingAs($this->user)->get(route('roles.index'));
    $response->assertOk();
});

test('unauthorized user cannot manage roles', function () {
    $user = User::factory()->create();
    $response = $this->actingAs($user)->get(route('roles.index'));
    $response->assertForbidden();
});

test('permission assignment works', function () {
    $role = Role::create(['name' => 'test-role']);

    $response = $this->actingAs($this->user)->put(route('roles.update', $role), [
        'name' => 'test-role',
        'permissions' => ['patient.view', 'patient.create'],
    ]);

    $response->assertRedirect();
    $role->refresh();
    $this->assertTrue($role->hasPermissionTo('patient.view'));
    $this->assertTrue($role->hasPermissionTo('patient.create'));
    $this->assertFalse($role->hasPermissionTo('patient.edit'));
});

test('permission changes persist immediately', function () {
    $role = Role::create(['name' => 'test-role']);
    $role->givePermissionTo('patient.view');

    $this->actingAs($this->user)->put(route('roles.update', $role), [
        'name' => 'test-role',
        'permissions' => ['patient.create'],
    ]);

    $role->refresh();
    $this->assertFalse($role->hasPermissionTo('patient.view'));
    $this->assertTrue($role->hasPermissionTo('patient.create'));
});

test('permission changes create audit logs', function () {
    $role = Role::create(['name' => 'test-role']);
    $role->givePermissionTo('patient.view');

    $this->actingAs($this->user)->put(route('roles.update', $role), [
        'name' => 'test-role',
        'permissions' => ['patient.create'],
    ]);

    $this->assertDatabaseHas('audit_logs', [
        'module' => 'Role',
        'action' => 'updated',
        'user_id' => $this->user->id,
    ]);
});

test('super admin cannot be deleted', function () {
    $superAdmin = Role::firstOrCreate(['name' => 'super-admin', 'guard_name' => 'web']);

    $response = $this->actingAs($this->user)->delete(route('roles.destroy', $superAdmin));

    $response->assertSessionHas('error');
    $this->assertDatabaseHas('roles', ['name' => 'super-admin']);
});

test('role with assigned users cannot be deleted', function () {
    $role = Role::create(['name' => 'test-role']);
    $user = User::factory()->create();
    $user->assignRole('test-role');

    $response = $this->actingAs($this->user)->delete(route('roles.destroy', $role));

    $response->assertSessionHas('error');
    $this->assertDatabaseHas('roles', ['name' => 'test-role']);
});

test('role deletion creates audit log', function () {
    $role = Role::create(['name' => 'deletable-role']);

    $this->actingAs($this->user)->delete(route('roles.destroy', $role));

    $this->assertDatabaseHas('audit_logs', [
        'module' => 'Role',
        'action' => 'deleted',
        'user_id' => $this->user->id,
    ]);
});

test('role creation creates audit log', function () {
    $this->actingAs($this->user)->post(route('roles.store'), [
        'name' => 'new-audit-role',
        'permissions' => ['patient.view'],
    ]);

    $this->assertDatabaseHas('audit_logs', [
        'module' => 'Role',
        'action' => 'created',
        'user_id' => $this->user->id,
    ]);
});
