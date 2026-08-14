<?php

use App\Models\Doctor;
use App\Models\Invoice;
use App\Models\Patient;
use App\Models\Payment;
use App\Models\User;
use Spatie\Permission\Models\Role;

test('the settings account section renders the delete account form', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->get(route('user.settings'))
        ->assertOk()
        ->assertSee('name="password"', false)
        ->assertSee('name="confirm_email"', false)
        ->assertSee(__('app.account.submit'));
});

test('an account can be deactivated with the current password and confirmation', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)
        ->from(route('user.settings'))
        ->delete(route('profile.destroy'), [
            'password' => 'password',
            'confirm_email' => $user->email,
        ]);

    $response
        ->assertSessionHasNoErrors()
        ->assertRedirect(route('login'));

    $this->assertGuest();

    $user->refresh();
    $this->assertFalse($user->is_active);
    $this->assertNotNull($user->fresh());
});

test('a wrong password cannot deactivate the account', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)
        ->from(route('user.settings'))
        ->delete(route('profile.destroy'), [
            'password' => 'wrong-password',
            'confirm_email' => $user->email,
        ]);

    $response
        ->assertSessionHasErrorsIn('userDeletion', 'password');

    $this->assertTrue($user->refresh()->is_active);
    $this->assertAuthenticated();
});

test('an explicit email confirmation is required to deactivate', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)
        ->from(route('user.settings'))
        ->delete(route('profile.destroy'), [
            'password' => 'password',
        ]);

    $response
        ->assertSessionHasErrorsIn('userDeletion', 'confirm_email');

    $this->assertTrue($user->refresh()->is_active);
});

test('the confirmation email must match the account email', function () {
    $user = User::factory()->create(['email' => 'alice@example.com']);

    $response = $this->actingAs($user)
        ->from(route('user.settings'))
        ->delete(route('profile.destroy'), [
            'password' => 'password',
            'confirm_email' => 'someone-else@example.com',
        ]);

    $response
        ->assertSessionHasErrorsIn('userDeletion', 'confirm_email');

    $this->assertTrue($user->refresh()->is_active);
});

test('an account cannot be used to deactivate another account', function () {
    $user = User::factory()->create(['email' => 'alice@example.com']);
    $other = User::factory()->create(['email' => 'bob@example.com']);

    $this->actingAs($user)
        ->from(route('user.settings'))
        ->delete(route('profile.destroy'), [
            'password' => 'password',
            'confirm_email' => $other->email,
        ])
        ->assertSessionHasErrorsIn('userDeletion', 'confirm_email');

    $this->assertTrue($other->refresh()->is_active);
    $this->assertTrue($user->refresh()->is_active);
});

test('clinical and billing records are preserved when an account is deactivated', function () {
    $user = User::factory()->create();

    $doctor = Doctor::factory()->create(['user_id' => $user->id]);
    $patient = Patient::factory()->create();
    $invoice = Invoice::create([
        'patient_id' => $patient->id,
        'status' => 'paid',
        'total' => 5000,
        'amount_paid' => 5000,
        'balance' => 0,
    ]);
    $payment = Payment::create([
        'invoice_id' => $invoice->id,
        'amount' => 5000,
        'payment_method' => 'cash',
        'recorded_by' => $user->id,
        'paid_at' => now(),
    ]);

    $this->actingAs($user)
        ->delete(route('profile.destroy'), [
            'password' => 'password',
            'confirm_email' => $user->email,
        ])
        ->assertRedirect(route('login'));

    // The user row remains (deactivated), and its clinical/audit links survive.
    $this->assertDatabaseHas('users', ['id' => $user->id, 'is_active' => 0]);
    $this->assertDatabaseHas('doctors', ['id' => $doctor->id, 'user_id' => $user->id]);
    $this->assertDatabaseHas('invoices', ['id' => $invoice->id]);
    $this->assertDatabaseHas('payments', ['id' => $payment->id, 'recorded_by' => $user->id]);
});

test('the session is terminated and the deactivated account cannot sign in again', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->delete(route('profile.destroy'), [
            'password' => 'password',
            'confirm_email' => $user->email,
        ])
        ->assertRedirect(route('login'));

    $this->assertGuest();

    $this->post(route('login'), [
        'email' => $user->email,
        'password' => 'password',
    ])->assertSessionHasErrors('email');

    $this->assertGuest();
});

test('the last active administrator cannot deactivate their account', function () {
    Role::firstOrCreate(['name' => 'super-admin', 'guard_name' => 'web']);

    $admin = User::factory()->create();
    $admin->assignRole('super-admin');

    $response = $this->actingAs($admin)
        ->from(route('user.settings'))
        ->delete(route('profile.destroy'), [
            'password' => 'password',
            'confirm_email' => $admin->email,
        ]);

    $response->assertSessionHasErrorsIn('userDeletion', 'password');

    $this->assertTrue($admin->refresh()->is_active);
    $this->assertAuthenticatedAs($admin);
});

test('the last active administrator sees the protection notice instead of the form', function () {
    Role::firstOrCreate(['name' => 'super-admin', 'guard_name' => 'web']);

    $admin = User::factory()->create();
    $admin->assignRole('super-admin');

    $this->actingAs($admin)->get(route('user.settings'))
        ->assertOk()
        ->assertSee(__('app.account.delete_prevented_last_admin'))
        ->assertDontSee('name="confirm_email"', false);
});

test('an administrator can deactivate their account while another administrator is active', function () {
    Role::firstOrCreate(['name' => 'super-admin', 'guard_name' => 'web']);

    $super = User::factory()->create();
    $super->assignRole('super-admin');

    $admin = User::factory()->create();

    $this->actingAs($admin)
        ->delete(route('profile.destroy'), [
            'password' => 'password',
            'confirm_email' => $admin->email,
        ])
        ->assertRedirect(route('login'));

    $this->assertFalse($admin->refresh()->is_active);
    $this->assertTrue($super->refresh()->is_active);
});

test('a guest cannot deactivate an account', function () {
    $user = User::factory()->create();

    $this->delete(route('profile.destroy'), [
        'password' => 'password',
        'confirm_email' => $user->email,
    ])->assertRedirect(route('login'));

    $this->assertTrue($user->refresh()->is_active);
});