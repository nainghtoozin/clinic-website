<?php

use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Models\User;
use Spatie\Permission\Models\Permission;

beforeEach(function () {
    $this->permissions = [
        'expense.view', 'expense.create', 'expense.edit', 'expense.delete',
        'expense_category.view', 'expense_category.create', 'expense_category.edit', 'expense_category.delete',
        'report.financial',
    ];

    foreach ($this->permissions as $perm) {
        Permission::firstOrCreate(['name' => $perm, 'guard_name' => 'web']);
    }

    $this->user = User::factory()->create();
    $this->user->givePermissionTo($this->permissions);

    $this->category = ExpenseCategory::create([
        'name' => 'Medical Supplies',
        'slug' => 'medical-supplies',
        'description' => 'Medical consumables',
        'is_active' => true,
    ]);

    $this->expense = Expense::create([
        'expense_number' => 'EXP-20260825-0001',
        'expense_category_id' => $this->category->id,
        'amount' => 150.00,
        'payment_method' => 'cash',
        'expense_date' => now()->toDateString(),
        'description' => 'Bandages and gauze',
        'vendor' => 'Medical Supply Co.',
        'created_by' => $this->user->id,
        'status' => 'active',
    ]);
});

// --- EXPENSE CATEGORY TESTS ---

test('expense category index loads', function () {
    $response = $this->actingAs($this->user)->get(route('expense-categories.index'));
    $response->assertOk();
    $response->assertSee('Medical Supplies');
});

test('expense category can be created', function () {
    $response = $this->actingAs($this->user)->post(route('expense-categories.store'), [
        'name' => 'Office Supplies',
        'description' => 'Paper, pens, etc.',
    ]);

    $response->assertRedirect();
    $this->assertDatabaseHas('expense_categories', [
        'name' => 'Office Supplies',
        'slug' => 'office-supplies',
    ]);
});

test('expense category validation requires name', function () {
    $response = $this->actingAs($this->user)->post(route('expense-categories.store'), []);
    $response->assertSessionHasErrors(['name']);
});

test('expense category name must be unique', function () {
    $response = $this->actingAs($this->user)->post(route('expense-categories.store'), [
        'name' => 'Medical Supplies',
    ]);
    $response->assertSessionHasErrors(['name']);
});

test('expense category can be updated', function () {
    $response = $this->actingAs($this->user)->put(route('expense-categories.update', $this->category), [
        'name' => 'Medical Supplies Updated',
        'description' => 'Updated description',
    ]);

    $response->assertRedirect();
    $this->assertDatabaseHas('expense_categories', [
        'id' => $this->category->id,
        'name' => 'Medical Supplies Updated',
        'slug' => 'medical-supplies-updated',
    ]);
});

test('expense category can be toggled inactive', function () {
    $response = $this->actingAs($this->user)->put(route('expense-categories.update', $this->category), [
        'name' => $this->category->name,
        'is_active' => false,
    ]);

    $response->assertRedirect();
    $this->assertDatabaseHas('expense_categories', [
        'id' => $this->category->id,
        'is_active' => false,
    ]);
});

test('expense category with expenses cannot be deleted', function () {
    $response = $this->actingAs($this->user)->delete(route('expense-categories.destroy', $this->category));
    $response->assertSessionHasErrors('error');
    $this->assertDatabaseHas('expense_categories', ['id' => $this->category->id]);
});

test('expense category without expenses can be deleted', function () {
    $cat = ExpenseCategory::create(['name' => 'Empty Category', 'slug' => 'empty-category']);
    $response = $this->actingAs($this->user)->delete(route('expense-categories.destroy', $cat));
    $response->assertRedirect();
    $this->assertDatabaseMissing('expense_categories', ['id' => $cat->id]);
});

test('expense category requires create permission', function () {
    $user = User::factory()->create();
    $response = $this->actingAs($user)->post(route('expense-categories.store'), [
        'name' => 'Test Category',
    ]);
    $response->assertForbidden();
});

// --- EXPENSE TESTS ---

test('expense index page loads', function () {
    $response = $this->actingAs($this->user)->get(route('expenses.index'));
    $response->assertOk();
    $response->assertSee('EXP-20260825-0001');
    $response->assertSee('Bandages and gauze');
});

test('expense index shows empty state', function () {
    Expense::truncate();
    $response = $this->actingAs($this->user)->get(route('expenses.index'));
    $response->assertOk();
    $response->assertSee('No Expenses Found');
});

test('expense index filters by category', function () {
    $otherCategory = ExpenseCategory::create(['name' => 'Rent', 'slug' => 'rent']);
    Expense::create([
        'expense_number' => 'EXP-20260825-0002',
        'expense_category_id' => $otherCategory->id,
        'amount' => 500.00,
        'payment_method' => 'bank_transfer',
        'expense_date' => now()->toDateString(),
        'description' => 'Monthly rent',
        'created_by' => $this->user->id,
        'status' => 'active',
    ]);

    $response = $this->actingAs($this->user)->get(route('expenses.index', ['category_id' => $this->category->id]));
    $response->assertOk();
    $response->assertSee('Bandages and gauze');
    $response->assertDontSee('Monthly rent');
});

test('expense index filters by date range', function () {
    $response = $this->actingAs($this->user)->get(route('expenses.index', [
        'date_from' => now()->addDay()->toDateString(),
        'date_to' => now()->addWeek()->toDateString(),
    ]));
    $response->assertOk();
    $response->assertSee('No Expenses Found');
});

test('expense index filters by payment method', function () {
    $response = $this->actingAs($this->user)->get(route('expenses.index', ['payment_method' => 'bank_transfer']));
    $response->assertOk();
    $response->assertSee('No Expenses Found');
});

test('expense index searches by description', function () {
    $response = $this->actingAs($this->user)->get(route('expenses.index', ['search' => 'Bandages']));
    $response->assertOk();
    $response->assertSee('Bandages and gauze');
});

test('expense can be created', function () {
    $response = $this->actingAs($this->user)->post(route('expenses.store'), [
        'expense_category_id' => $this->category->id,
        'amount' => 250.50,
        'payment_method' => 'card',
        'expense_date' => now()->toDateString(),
        'description' => 'Stethoscope purchase',
        'vendor' => 'Medical Equipment Inc.',
        'reference_number' => 'REF-001',
    ]);

    $response->assertRedirect();
    $this->assertDatabaseHas('expenses', [
        'expense_category_id' => $this->category->id,
        'amount' => 250.50,
        'payment_method' => 'card',
        'description' => 'Stethoscope purchase',
        'vendor' => 'Medical Equipment Inc.',
        'created_by' => $this->user->id,
        'status' => 'active',
    ]);
});

test('expense store validates required fields', function () {
    $response = $this->actingAs($this->user)->post(route('expenses.store'), []);
    $response->assertSessionHasErrors(['expense_category_id', 'amount', 'payment_method', 'expense_date', 'description']);
});

test('expense amount must be positive', function () {
    $response = $this->actingAs($this->user)->post(route('expenses.store'), [
        'expense_category_id' => $this->category->id,
        'amount' => -10,
        'payment_method' => 'cash',
        'expense_date' => now()->toDateString(),
        'description' => 'Test',
    ]);
    $response->assertSessionHasErrors(['amount']);
});

test('expense payment method must be valid', function () {
    $response = $this->actingAs($this->user)->post(route('expenses.store'), [
        'expense_category_id' => $this->category->id,
        'amount' => 100,
        'payment_method' => 'invalid',
        'expense_date' => now()->toDateString(),
        'description' => 'Test',
    ]);
    $response->assertSessionHasErrors(['payment_method']);
});

test('expense show page loads', function () {
    $response = $this->actingAs($this->user)->get(route('expenses.show', $this->expense));
    $response->assertOk();
    $response->assertSee('EXP-20260825-0001');
    $response->assertSee('Bandages and gauze');
    $response->assertSee('Medical Supplies');
});

test('expense can be updated', function () {
    $response = $this->actingAs($this->user)->put(route('expenses.update', $this->expense), [
        'expense_category_id' => $this->category->id,
        'amount' => 200.00,
        'payment_method' => 'cash',
        'expense_date' => now()->toDateString(),
        'description' => 'Updated description',
    ]);

    $response->assertRedirect();
    $this->assertDatabaseHas('expenses', [
        'id' => $this->expense->id,
        'amount' => 200.00,
        'description' => 'Updated description',
    ]);
});

test('expense can be cancelled', function () {
    $response = $this->actingAs($this->user)->delete(route('expenses.destroy', $this->expense));
    $response->assertRedirect();
    $this->assertDatabaseHas('expenses', [
        'id' => $this->expense->id,
        'status' => 'cancelled',
    ]);
});

test('expense requires view permission', function () {
    $user = User::factory()->create();
    $response = $this->actingAs($user)->get(route('expenses.index'));
    $response->assertForbidden();
});

test('expense requires create permission', function () {
    $user = User::factory()->create();
    $response = $this->actingAs($user)->post(route('expenses.store'), [
        'expense_category_id' => $this->category->id,
        'amount' => 100,
        'payment_method' => 'cash',
        'expense_date' => now()->toDateString(),
        'description' => 'Test',
    ]);
    $response->assertForbidden();
});

// --- EXPENSE NUMBER GENERATION ---

test('expense number follows correct format', function () {
    $number = Expense::generateExpenseNumber();
    $this->assertMatchesRegularExpression('/^EXP-\d{8}-\d{4}$/', $number);
});

test('expense number increments correctly', function () {
    $number = Expense::generateExpenseNumber();
    $this->assertMatchesRegularExpression('/^EXP-\d{8}-\d{4}$/', $number);
    // The number should be different from the existing expense
    $this->assertNotEquals($this->expense->expense_number, $number);
});

// --- FINANCIAL REPORT TESTS ---

test('financial report shows expense data', function () {
    $response = $this->actingAs($this->user)->get(route('reports.financial'));
    $response->assertOk();
    $response->assertSee('Total Revenue');
    $response->assertSee('Total Expenses');
    $response->assertSee('Net Income');
});

test('financial report respects date range', function () {
    $response = $this->actingAs($this->user)->get(route('reports.financial', [
        'date_from' => now()->startOfMonth()->toDateString(),
        'date_to' => now()->toDateString(),
    ]));
    $response->assertOk();
    $response->assertSee('Total Revenue');
});

test('expense report loads', function () {
    $response = $this->actingAs($this->user)->get(route('reports.expense', [
        'date_from' => now()->subDay()->toDateString(),
        'date_to' => now()->addDay()->toDateString(),
    ]));
    $response->assertOk();
    $response->assertSee('Medical Supplies');
});

test('expense report filters by date', function () {
    $response = $this->actingAs($this->user)->get(route('reports.expense', [
        'date_from' => now()->addDays(10)->toDateString(),
        'date_to' => now()->addDays(20)->toDateString(),
    ]));
    $response->assertOk();
    $response->assertSee('No expenses found');
});

test('profit report loads', function () {
    $response = $this->actingAs($this->user)->get(route('reports.profit'));
    $response->assertOk();
    $response->assertSee('Total Revenue');
    $response->assertSee('Total Expenses');
    $response->assertSee('Net Income');
});

test('payment method report loads', function () {
    $response = $this->actingAs($this->user)->get(route('reports.payment-method'));
    $response->assertOk();
    $response->assertSee('Total Payments');
});

// --- MODEL TESTS ---

test('expense model has correct relationships', function () {
    $this->assertNotNull($this->expense->expenseCategory);
    $this->assertEquals($this->category->id, $this->expense->expenseCategory->id);
    $this->assertNotNull($this->expense->createdBy);
    $this->assertEquals($this->user->id, $this->expense->createdBy->id);
});

test('expense model helpers work', function () {
    $this->assertEquals('Cash', $this->expense->payment_method_label);
    $this->assertEquals('Active', $this->expense->status_label);
    $this->assertTrue($this->expense->isActive());
    $this->assertFalse($this->expense->isCancelled());
});

test('expense category has expenses relationship', function () {
    $this->assertEquals(1, $this->category->expenses->count());
});

test('expense scope active works', function () {
    $this->assertNotNull(Expense::active()->first());
});

test('expense category scope active works', function () {
    $this->assertNotNull(ExpenseCategory::active()->first());
});
