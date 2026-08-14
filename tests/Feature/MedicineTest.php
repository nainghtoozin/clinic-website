<?php

use App\Models\Medicine;
use App\Models\User;
use Spatie\Permission\Models\Permission;

beforeEach(function () {
    $this->permissions = [
        'medicine.view', 'medicine.create', 'medicine.edit', 'medicine.delete',
    ];

    foreach ($this->permissions as $perm) {
        Permission::firstOrCreate(['name' => $perm, 'guard_name' => 'web']);
    }

    $this->user = User::factory()->create();
    $this->user->givePermissionTo($this->permissions);
});

// --- MEDICINE CRUD TESTS ---

test('medicine can be created', function () {
    $response = $this->actingAs($this->user)->post(route('medicines.store'), [
        'name' => 'Amoxicillin',
        'generic_name' => 'Amoxicillin Trihydrate',
        'manufacturer' => 'PharmaCorp',
        'category' => 'Antibiotics',
        'form' => 'capsule',
        'strength' => '500mg',
        'unit_price' => 15.50,
        'stock_quantity' => 100,
        'is_active' => true,
    ]);

    $response->assertRedirect();
    $this->assertDatabaseHas('medicines', [
        'name' => 'Amoxicillin',
        'category' => 'Antibiotics',
    ]);
});

test('medicine name is required', function () {
    $response = $this->actingAs($this->user)->post(route('medicines.store'), [
        'name' => '',
        'unit_price' => 10.00,
        'stock_quantity' => 50,
    ]);

    $response->assertSessionHasErrors('name');
});

test('medicine unit price is required', function () {
    $response = $this->actingAs($this->user)->post(route('medicines.store'), [
        'name' => 'Test Medicine',
        'unit_price' => '',
        'stock_quantity' => 50,
    ]);

    $response->assertSessionHasErrors('unit_price');
});

test('medicine stock quantity is required', function () {
    $response = $this->actingAs($this->user)->post(route('medicines.store'), [
        'name' => 'Test Medicine',
        'unit_price' => 10.00,
        'stock_quantity' => '',
    ]);

    $response->assertSessionHasErrors('stock_quantity');
});

test('medicine can be updated', function () {
    $medicine = Medicine::create([
        'name' => 'Original Name',
        'unit_price' => 10.00,
        'stock_quantity' => 50,
    ]);

    $response = $this->actingAs($this->user)->put(route('medicines.update', $medicine), [
        'name' => 'Updated Name',
        'unit_price' => 15.00,
        'stock_quantity' => 100,
    ]);

    $response->assertRedirect();
    $this->assertDatabaseHas('medicines', [
        'id' => $medicine->id,
        'name' => 'Updated Name',
    ]);
});

test('medicine can be deleted', function () {
    $medicine = Medicine::create([
        'name' => 'Test Medicine',
        'unit_price' => 10.00,
        'stock_quantity' => 50,
    ]);

    $response = $this->actingAs($this->user)->delete(route('medicines.destroy', $medicine));

    $response->assertRedirect();
    $this->assertDatabaseMissing('medicines', ['id' => $medicine->id]);
});

test('medicine can be viewed', function () {
    $medicine = Medicine::create([
        'name' => 'Test Medicine',
        'unit_price' => 10.00,
        'stock_quantity' => 50,
    ]);

    $response = $this->actingAs($this->user)->get(route('medicines.show', $medicine));

    $response->assertOk();
    $response->assertSee('Test Medicine');
});

test('medicine list can be viewed', function () {
    $response = $this->actingAs($this->user)->get(route('medicines.index'));

    $response->assertOk();
});

test('medicine search works', function () {
    Medicine::create(['name' => 'Amoxicillin', 'unit_price' => 10.00, 'stock_quantity' => 50]);
    Medicine::create(['name' => 'Paracetamol', 'unit_price' => 5.00, 'stock_quantity' => 100]);

    $response = $this->actingAs($this->user)->get(route('medicines.index', ['search' => 'Amoxicillin']));

    $response->assertOk();
    $response->assertSee('Amoxicillin');
    $response->assertDontSee('Paracetamol');
});

test('medicine category filter works', function () {
    Medicine::create(['name' => 'Amoxicillin', 'category' => 'Antibiotics', 'unit_price' => 10.00, 'stock_quantity' => 50]);
    Medicine::create(['name' => 'Paracetamol', 'category' => 'Painkillers', 'unit_price' => 5.00, 'stock_quantity' => 100]);

    $response = $this->actingAs($this->user)->get(route('medicines.index', ['category' => 'Antibiotics']));

    $response->assertOk();
    $response->assertSee('Amoxicillin');
    $response->assertDontSee('Paracetamol');
});

// --- AUTHORIZATION TESTS ---

test('unauthorized user cannot access medicines', function () {
    $user = User::factory()->create();
    $response = $this->actingAs($user)->get(route('medicines.index'));
    $response->assertForbidden();
});

test('unauthorized user cannot create medicine', function () {
    $user = User::factory()->create();
    $response = $this->actingAs($user)->get(route('medicines.create'));
    $response->assertForbidden();
});

test('unauthorized user cannot edit medicine', function () {
    $user = User::factory()->create();
    $medicine = Medicine::create(['name' => 'Test', 'unit_price' => 10.00, 'stock_quantity' => 50]);
    $response = $this->actingAs($user)->get(route('medicines.edit', $medicine));
    $response->assertForbidden();
});

test('unauthorized user cannot delete medicine', function () {
    $user = User::factory()->create();
    $medicine = Medicine::create(['name' => 'Test', 'unit_price' => 10.00, 'stock_quantity' => 50]);
    $response = $this->actingAs($user)->delete(route('medicines.destroy', $medicine));
    $response->assertForbidden();
});