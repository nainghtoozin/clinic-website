<?php

use App\Models\User;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    foreach (['role.view', 'settings.view', 'dashboard.view'] as $perm) {
        Permission::firstOrCreate(['name' => $perm, 'guard_name' => 'web']);
    }
});

test('the settings menu is visible to any authenticated user with dashboard access', function () {
    $user = User::factory()->create();
    $user->givePermissionTo('dashboard.view');

    $this->actingAs($user)->get(route('dashboard', absolute: false))
        ->assertOk()
        ->assertSee(__('app.nav.settings'))
        ->assertSee(route('user.settings'), false);
});

test('the settings menu opens the user settings page', function () {
    $user = User::factory()->create();
    $user->givePermissionTo('dashboard.view');

    $this->actingAs($user)->get(route('dashboard', absolute: false))
        ->assertOk()
        ->assertSee('href="' . route('user.settings') . '"', false);

    $this->actingAs($user)->get(route('user.settings'))
        ->assertOk()
        ->assertSee(__('app.settings.title'));
});

test('the settings link is highlighted on the user settings page', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->get(route('user.settings'))
        ->assertOk()
        ->assertSee('settings/account" class="active"', false);
});

test('roles and permissions menu is visible to a super admin', function () {
    $role = Role::firstOrCreate(['name' => 'super-admin', 'guard_name' => 'web']);
    $role->syncPermissions(Permission::all());

    $admin = User::factory()->create();
    $admin->assignRole($role);

    $this->actingAs($admin)->get(route('dashboard', absolute: false))
        ->assertOk()
        ->assertSee(__('app.nav.roles_permissions'))
        ->assertSee('href="' . route('roles.index') . '"', false);
});

test('roles and permissions menu is hidden from a user without the role permission', function () {
    $adminRole = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
    $adminRole->syncPermissions(Permission::whereNotIn('name', ['role.view'])->get());

    $admin = User::factory()->create();
    $admin->assignRole($adminRole);

    $this->actingAs($admin)->get(route('dashboard', absolute: false))
        ->assertOk()
        ->assertDontSee(__('app.nav.roles_permissions'))
        ->assertDontSee('href="' . route('roles.index') . '"', false);
});

test('super admin can open the roles page from the sidebar route', function () {
    $role = Role::firstOrCreate(['name' => 'super-admin', 'guard_name' => 'web']);
    $role->syncPermissions(Permission::all());

    $admin = User::factory()->create();
    $admin->assignRole($role);

    $this->actingAs($admin)->get(route('roles.index'))
        ->assertOk()
        ->assertSee(__('app.nav.roles_permissions'));
});

test('a user without the role permission cannot open the roles page', function () {
    $adminRole = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
    $adminRole->syncPermissions(Permission::whereNotIn('name', ['role.view'])->get());

    $admin = User::factory()->create();
    $admin->assignRole($adminRole);

    $this->actingAs($admin)->get(route('roles.index'))->assertForbidden();
});

test('a guest cannot open settings or roles', function () {
    $this->get(route('user.settings'))->assertRedirect(route('login'));
    $this->get(route('roles.index'))->assertRedirect(route('login'));
});

test('the sidebar sections are always expanded (no accordion)', function () {
    $user = User::factory()->create();
    $user->givePermissionTo('dashboard.view');

    $html = $this->actingAs($user)->get(route('dashboard'))->assertOk()->getContent();

    // No accordion state or collapsible containers should be present.
    expect($html)->not->toContain('sidebarAccordion');
    expect($html)->not->toContain('nav-section-toggle');
    expect($html)->not->toContain('nav-collapse');

    // Section labels and their menu items are rendered directly (always visible).
    expect($html)->toContain(e(__('app.nav.section_patients')));
    expect($html)->toContain(e(__('app.nav.section_clinical')));
    expect($html)->toContain(e(__('app.nav.section_inventory')));
    expect($html)->toContain(e(__('app.nav.section_billing')));
    expect($html)->toContain(e(__('app.nav.section_management')));
});