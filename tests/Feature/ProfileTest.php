<?php

use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

test('profile page is displayed', function () {
    $user = User::factory()->create();

    $response = $this
        ->actingAs($user)
        ->get('/profile');

    $response->assertOk();
});

test('profile information can be updated with phone', function () {
    $user = User::factory()->create();

    $response = $this
        ->actingAs($user)
        ->patch('/profile', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'phone' => '09-123456789',
        ]);

    $response
        ->assertSessionHasNoErrors()
        ->assertRedirect('/profile');

    $user->refresh();

    $this->assertSame('Test User', $user->name);
    $this->assertSame('test@example.com', $user->email);
    $this->assertSame('09-123456789', $user->phone);
    $this->assertNull($user->email_verified_at);
});

test('phone field is optional', function () {
    $user = User::factory()->create(['phone' => '09-111']);

    $this->actingAs($user)->patch('/profile', [
        'name' => 'No Phone',
        'email' => $user->email,
        'phone' => null,
    ])->assertSessionHasNoErrors();

    $this->assertNull($user->refresh()->phone);
});

test('an avatar can be uploaded', function () {
    Storage::fake('public');

    $user = User::factory()->create();

    $this->actingAs($user)->patch('/profile', [
        'name' => $user->name,
        'email' => $user->email,
        'avatar' => UploadedFile::fake()->image('avatar.jpg'),
    ])->assertSessionHasNoErrors();

    $user->refresh();

    $this->assertNotNull($user->avatar);
    Storage::disk('public')->assertExists($user->avatar);
});

test('an uploaded avatar replaces and cleans up the old one', function () {
    Storage::fake('public');

    $user = User::factory()->create();
    $old = UploadedFile::fake()->image('old.jpg')->store('avatars', 'public');
    $user->update(['avatar' => $old]);

    $this->actingAs($user)->patch('/profile', [
        'name' => $user->name,
        'email' => $user->email,
        'avatar' => UploadedFile::fake()->image('new.jpg'),
    ])->assertSessionHasNoErrors();

    $user->refresh();

    $this->assertNotSame($old, $user->avatar);
    Storage::disk('public')->assertMissing($old);
    Storage::disk('public')->assertExists($user->avatar);
});

test('a non-image avatar file is rejected', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->patch('/profile', [
            'name' => $user->name,
            'email' => $user->email,
            'avatar' => UploadedFile::fake()->create('document.pdf', 100),
        ])
        ->assertSessionHasErrors('avatar');

    $this->assertNull($user->refresh()->avatar);
});

test('an oversized avatar file is rejected', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->patch('/profile', [
            'name' => $user->name,
            'email' => $user->email,
            'avatar' => UploadedFile::fake()->image('big.jpg')->size(3000),
        ])
        ->assertSessionHasErrors('avatar');

    $this->assertNull($user->refresh()->avatar);
});

test('a user cannot update another users profile', function () {
    $user = User::factory()->create(['email' => 'alice@example.com']);
    $other = User::factory()->create(['email' => 'bob@example.com', 'name' => 'Bob']);

    $this->actingAs($user)->patch(route('profile.update'), [
        'name' => 'Bob',
        'email' => 'bob@example.com',
    ])->assertSessionHasErrors('email');

    $other->refresh();
    $this->assertSame('Bob', $other->name);
});

test('email must be unique', function () {
    $user = User::factory()->create();
    $other = User::factory()->create(['email' => 'taken@example.com']);

    $this->actingAs($user)
        ->patch('/profile', [
            'name' => $user->name,
            'email' => 'taken@example.com',
        ])
        ->assertSessionHasErrors('email');

    $user->refresh();
    $this->assertNotSame('taken@example.com', $user->email);
});

test('profile update does not allow changing role, permission or admin fields', function () {
    $user = User::factory()->create();

    \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
    \Spatie\Permission\Models\Permission::firstOrCreate(['name' => 'staff.view', 'guard_name' => 'web']);
    $user->assignRole('admin');

    $this->actingAs($user)->patch('/profile', [
        'name' => 'New Name',
        'email' => $user->email,
        'is_active' => false,
        'role' => 'staff',
        'permissions' => ['staff.view'],
    ])->assertSessionHasNoErrors();

    $user->refresh();

    $this->assertSame('New Name', $user->name);
    $this->assertTrue($user->is_active);
    $this->assertTrue($user->hasRole('admin'));
});

test('the settings profile form is rendered on the settings page', function () {
    $user = User::factory()->create([
        'name' => 'Test User',
        'phone' => '09-123456789',
    ]);

    $this->actingAs($user)->get(route('user.settings'))
        ->assertOk()
        ->assertSee('name="avatar"', false)
        ->assertSee('name="name"', false)
        ->assertSee('name="email"', false)
        ->assertSee('name="phone"', false)
        ->assertSee('Test User')
        ->assertSee('09-123456789');
});

test('email verification status is unchanged when the email address is unchanged', function () {
    $user = User::factory()->create();

    $response = $this
        ->actingAs($user)
        ->patch('/profile', [
            'name' => 'Test User',
            'email' => $user->email,
        ]);

    $response
        ->assertSessionHasNoErrors()
        ->assertRedirect('/profile');

    $this->assertNotNull($user->refresh()->email_verified_at);
});

test('user can deactivate their account', function () {
    $user = User::factory()->create();

    $response = $this
        ->actingAs($user)
        ->from('/profile')
        ->delete('/profile', [
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

test('correct password must be provided to delete account', function () {
    $user = User::factory()->create();

    $response = $this
        ->actingAs($user)
        ->from('/profile')
        ->delete('/profile', [
            'password' => 'wrong-password',
            'confirm_email' => $user->email,
        ]);

    $response
        ->assertSessionHasErrorsIn('userDeletion', 'password')
        ->assertRedirect('/profile');

    $this->assertNotNull($user->fresh());
});
