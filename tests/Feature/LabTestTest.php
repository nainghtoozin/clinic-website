<?php

use App\Models\Doctor;
use App\Models\Department;
use App\Models\LabTest;
use App\Models\User;
use Spatie\Permission\Models\Permission;

beforeEach(function () {
    $this->permissions = [
        'lab_test.view', 'lab_test.create', 'lab_test.edit', 'lab_test.delete',
    ];

    foreach ($this->permissions as $perm) {
        Permission::firstOrCreate(['name' => $perm, 'guard_name' => 'web']);
    }

    $this->user = User::factory()->create();
    $this->user->givePermissionTo($this->permissions);

    $this->department = Department::create(['name' => 'Cardiology', 'slug' => 'cardiology', 'description' => 'Heart care']);
    $this->doctor = Doctor::create([
        'name' => 'Dr. Smith',
        'slug' => 'dr-smith',
        'department_id' => $this->department->id,
        'is_available' => true,
        'available_days' => [1, 2, 3, 4, 5],
        'start_time' => '09:00:00',
        'end_time' => '17:00:00',
        'user_id' => $this->user->id,
        'consultation_fee' => 100.00,
    ]);
});

// --- INDEX ---

test('lab test index page loads successfully', function () {
    $response = $this->actingAs($this->user)->get(route('lab-tests.index'));
    $response->assertOk();
});

test('lab test index shows lab tests', function () {
    LabTest::create(['name' => 'Complete Blood Count', 'code' => 'CBC', 'category' => 'Hematology', 'price' => 25.00, 'is_active' => true]);
    LabTest::create(['name' => 'Lipid Profile', 'code' => 'LP', 'category' => 'Chemistry', 'price' => 40.00, 'is_active' => true]);

    $response = $this->actingAs($this->user)->get(route('lab-tests.index'));
    $response->assertSee('Complete Blood Count');
    $response->assertSee('Lipid Profile');
});

test('lab test index shows empty state when no tests', function () {
    $response = $this->actingAs($this->user)->get(route('lab-tests.index'));
    $response->assertSee('No Lab Tests Found');
});

test('lab test index search works', function () {
    LabTest::create(['name' => 'Complete Blood Count', 'code' => 'CBC', 'category' => 'Hematology', 'price' => 25.00]);
    LabTest::create(['name' => 'Lipid Profile', 'code' => 'LP', 'category' => 'Chemistry', 'price' => 40.00]);

    $response = $this->actingAs($this->user)->get(route('lab-tests.index', ['search' => 'Blood']));
    $response->assertSee('Complete Blood Count');
    $response->assertDontSee('Lipid Profile');
});

test('lab test index category filter works', function () {
    LabTest::create(['name' => 'CBC', 'code' => 'CBC', 'category' => 'Hematology', 'price' => 25.00]);
    LabTest::create(['name' => 'LP', 'code' => 'LP', 'category' => 'Chemistry', 'price' => 40.00]);

    $response = $this->actingAs($this->user)->get(route('lab-tests.index', ['category' => 'Hematology']));
    $response->assertSee('CBC');
    $response->assertDontSee('LP');
});

// --- CREATE ---

test('lab test create form loads', function () {
    $response = $this->actingAs($this->user)->get(route('lab-tests.create'));
    $response->assertOk();
    $response->assertSee('Add Lab Test');
});

test('lab test can be created', function () {
    $response = $this->actingAs($this->user)->post(route('lab-tests.store'), [
        'name' => 'Complete Blood Count',
        'code' => 'CBC',
        'category' => 'Hematology',
        'description' => 'Full blood count test',
        'sample_type' => 'Blood',
        'reference_range' => '4.0-11.0',
        'unit' => 'x10^9/L',
        'price' => 25.00,
        'is_active' => true,
    ]);

    $response->assertRedirect();
    $this->assertDatabaseHas('lab_tests', ['code' => 'CBC', 'name' => 'Complete Blood Count']);
});

test('lab test validation requires name', function () {
    $response = $this->actingAs($this->user)->post(route('lab-tests.store'), [
        'code' => 'CBC',
        'category' => 'Hematology',
        'price' => 25.00,
    ]);

    $response->assertSessionHasErrors('name');
});

test('lab test validation requires code', function () {
    $response = $this->actingAs($this->user)->post(route('lab-tests.store'), [
        'name' => 'CBC',
        'category' => 'Hematology',
        'price' => 25.00,
    ]);

    $response->assertSessionHasErrors('code');
});

test('lab test validation requires price', function () {
    $response = $this->actingAs($this->user)->post(route('lab-tests.store'), [
        'name' => 'CBC',
        'code' => 'CBC',
        'category' => 'Hematology',
    ]);

    $response->assertSessionHasErrors('price');
});

// --- SHOW ---

test('lab test show page loads', function () {
    $test = LabTest::create(['name' => 'CBC', 'code' => 'CBC', 'category' => 'Hematology', 'price' => 25.00]);
    $response = $this->actingAs($this->user)->get(route('lab-tests.show', $test));
    $response->assertOk();
    $response->assertSee('CBC');
});

// --- EDIT ---

test('lab test edit form loads', function () {
    $test = LabTest::create(['name' => 'CBC', 'code' => 'CBC', 'category' => 'Hematology', 'price' => 25.00]);
    $response = $this->actingAs($this->user)->get(route('lab-tests.edit', $test));
    $response->assertOk();
    $response->assertSee('Edit Lab Test');
});

test('lab test can be updated', function () {
    $test = LabTest::create(['name' => 'CBC', 'code' => 'CBC', 'category' => 'Hematology', 'price' => 25.00]);

    $response = $this->actingAs($this->user)->put(route('lab-tests.update', $test), [
        'name' => 'Complete Blood Count',
        'code' => 'CBC',
        'category' => 'Hematology',
        'price' => 30.00,
    ]);

    $response->assertRedirect();
    $this->assertDatabaseHas('lab_tests', ['id' => $test->id, 'price' => 30.00]);
});

// --- DELETE ---

test('lab test can be deleted', function () {
    $test = LabTest::create(['name' => 'CBC', 'code' => 'CBC', 'category' => 'Hematology', 'price' => 25.00]);

    $response = $this->actingAs($this->user)->delete(route('lab-tests.destroy', $test));
    $response->assertRedirect();
    $this->assertDatabaseMissing('lab_tests', ['id' => $test->id]);
});

// --- AUTHORIZATION ---

test('unauthorized user cannot access lab test index', function () {
    $user = User::factory()->create();
    $response = $this->actingAs($user)->get(route('lab-tests.index'));
    $response->assertForbidden();
});

test('unauthorized user cannot create lab test', function () {
    $user = User::factory()->create();
    $response = $this->actingAs($user)->get(route('lab-tests.create'));
    $response->assertForbidden();
});

// --- CATEGORY FILTERING ---

test('lab test index shows categories', function () {
    LabTest::create(['name' => 'CBC', 'code' => 'CBC', 'category' => 'Hematology', 'price' => 25.00]);
    LabTest::create(['name' => 'X-Ray Chest', 'code' => 'XRC', 'category' => 'Radiology', 'price' => 50.00]);

    $response = $this->actingAs($this->user)->get(route('lab-tests.index'));
    $response->assertSee('Hematology');
    $response->assertSee('Radiology');
});
