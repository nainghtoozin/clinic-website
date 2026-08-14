<?php

use App\Models\Patient;
use App\Models\User;
use Spatie\Permission\Models\Permission;

beforeEach(function () {
    Permission::firstOrCreate(['name' => 'patient.view', 'guard_name' => 'web']);
    Permission::firstOrCreate(['name' => 'patient.create', 'guard_name' => 'web']);
    Permission::firstOrCreate(['name' => 'patient.edit', 'guard_name' => 'web']);
    Permission::firstOrCreate(['name' => 'patient.delete', 'guard_name' => 'web']);

    $this->user = User::factory()->create();
    $this->user->givePermissionTo(['patient.view', 'patient.create', 'patient.edit', 'patient.delete']);
});

test('patient index page requires authentication', function () {
    $response = $this->get(route('patients.index'));
    $response->assertRedirect(route('login'));
});

test('patient index page displays patients', function () {
    Patient::factory()->count(3)->create();

    $response = $this->actingAs($this->user)->get(route('patients.index'));

    $response->assertOk();
    $response->assertSee('Patients');
});

test('patient index page supports search', function () {
    Patient::factory()->create(['name' => 'John Doe']);
    Patient::factory()->create(['name' => 'Jane Smith']);

    $response = $this->actingAs($this->user)->get(route('patients.index', ['search' => 'John']));

    $response->assertOk();
    $response->assertSee('John Doe');
    $response->assertDontSee('Jane Smith');
});

test('patient index page supports status filter', function () {
    Patient::factory()->create(['status' => 'active']);
    Patient::factory()->create(['status' => 'inactive']);

    $response = $this->actingAs($this->user)->get(route('patients.index', ['status' => 'active']));

    $response->assertOk();
});

test('patient index page supports gender filter', function () {
    Patient::factory()->create(['gender' => 'male']);
    Patient::factory()->create(['gender' => 'female']);

    $response = $this->actingAs($this->user)->get(route('patients.index', ['gender' => 'male']));

    $response->assertOk();
});

test('patient create page is accessible', function () {
    $response = $this->actingAs($this->user)->get(route('patients.create'));

    $response->assertOk();
    $response->assertSee('Register New Patient');
});

test('patient can be stored', function () {
    $response = $this->actingAs($this->user)->post(route('patients.store'), [
        'name' => 'Test Patient',
        'email' => 'test@example.com',
        'phone' => '1234567890',
        'date_of_birth' => '1990-01-15',
        'gender' => 'male',
        'blood_group' => 'O+',
        'address' => '123 Test Street',
        'status' => 'active',
    ]);

    $response->assertRedirect();
    $this->assertDatabaseHas('patients', [
        'name' => 'Test Patient',
        'email' => 'test@example.com',
    ]);
});

test('patient show page displays patient details', function () {
    $patient = Patient::factory()->create(['name' => 'Display Patient']);

    $response = $this->actingAs($this->user)->get(route('patients.show', $patient));

    $response->assertOk();
    $response->assertSee('Display Patient');
    $response->assertSee('Patient Profile');
});

test('patient edit page displays patient data', function () {
    $patient = Patient::factory()->create(['name' => 'Edit Patient']);

    $response = $this->actingAs($this->user)->get(route('patients.edit', $patient));

    $response->assertOk();
    $response->assertSee('Edit Patient');
    $response->assertSee('Edit Patient');
});

test('patient can be updated', function () {
    $patient = Patient::factory()->create(['name' => 'Old Name']);

    $response = $this->actingAs($this->user)->put(route('patients.update', $patient), [
        'name' => 'New Name',
        'status' => 'active',
    ]);

    $response->assertRedirect(route('patients.index'));
    $this->assertDatabaseHas('patients', [
        'id' => $patient->id,
        'name' => 'New Name',
    ]);
});

test('patient can be soft deleted', function () {
    $patient = Patient::factory()->create();

    $response = $this->actingAs($this->user)->delete(route('patients.destroy', $patient));

    $response->assertRedirect(route('patients.index'));
    $this->assertSoftDeleted('patients', ['id' => $patient->id]);
});

test('deleted patient can be restored', function () {
    $patient = Patient::factory()->create();
    $patient->delete();

    $response = $this->actingAs($this->user)->post(route('patients.restore', $patient));

    $response->assertRedirect();
    $this->assertDatabaseHas('patients', ['id' => $patient->id, 'deleted_at' => null]);
});

test('patient without permission cannot access index', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->get(route('patients.index'));

    $response->assertForbidden();
});

test('patient without permission cannot create', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->get(route('patients.create'));

    $response->assertForbidden();
});
