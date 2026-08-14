<?php

use App\Models\User;
use App\Models\UserSetting;

test('authenticated user can open the account settings page', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->get(route('user.settings'));

    $response
        ->assertOk()
        ->assertSee('Appearance')
        ->assertSee('Language &amp; Region', false)
        ->assertSee('Preferences')
        ->assertSee('Security')
        ->assertSee('Account');
});

test('guest is redirected to the login page when opening settings', function () {
    $this->get(route('user.settings'))->assertRedirect(route('login'));
});

test('settings page shows defaults when no rows exist', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->get(route('user.settings'))
        ->assertOk()
        ->assertSee('value="light"', false)
        ->assertSee('value="comfortable"', false)
        ->assertSee('value="expanded"', false)
        ->assertSee('value="en"', false)
        ->assertSee('value="UTC"', false)
        ->assertSee('value="Y-m-d"', false)
        ->assertSee('value="H:i"', false)
        ->assertSee('value="month"', false)
        ->assertSee('name="preferences[show_weekends]" value="1"', false);

    $this->assertDatabaseCount('user_settings', 0);
});

test('settings can be saved for the authenticated user', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->post(route('user.settings.store'), [
        'appearance' => ['theme' => 'dark', 'table_density' => 'compact', 'sidebar' => 'collapsed'],
        'localization' => ['language' => 'en', 'timezone' => 'Europe/Paris', 'date_format' => 'd/m/Y', 'time_format' => 'h:i A'],
        'preferences' => ['calendar_view' => 'week', 'week_starts_on' => 'monday', 'show_weekends' => '0'],
    ]);

    $response->assertSessionHasNoErrors()->assertRedirect();

    $this->assertDatabaseHas('user_settings', [
        'user_id' => $user->id,
        'category' => 'appearance',
        'key' => 'theme',
        'value' => 'dark',
    ]);
    $this->assertDatabaseHas('user_settings', [
        'user_id' => $user->id,
        'category' => 'localization',
        'key' => 'timezone',
        'value' => 'Europe/Paris',
    ]);
    $this->assertDatabaseHas('user_settings', [
        'user_id' => $user->id,
        'category' => 'preferences',
        'key' => 'show_weekends',
        'value' => '0',
    ]);
});

test('saving settings upserts rows instead of duplicating them', function () {
    $user = User::factory()->create();

    $payload = fn (string $theme) => [
        'appearance' => ['theme' => $theme, 'table_density' => 'comfortable', 'sidebar' => 'expanded'],
        'localization' => ['language' => 'en', 'timezone' => 'UTC', 'date_format' => 'Y-m-d', 'time_format' => 'H:i'],
        'preferences' => ['calendar_view' => 'month', 'week_starts_on' => 'sunday', 'show_weekends' => '1'],
    ];

    $this->actingAs($user)->post(route('user.settings.store'), $payload('dark'));
    $this->actingAs($user)->post(route('user.settings.store'), $payload('system'));

    $this->assertDatabaseCount('user_settings', 10);
    $this->assertDatabaseHas('user_settings', [
        'user_id' => $user->id,
        'category' => 'appearance',
        'key' => 'theme',
        'value' => 'system',
    ]);
});

test('saved settings are rendered on the settings page', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->post(route('user.settings.store'), [
        'appearance' => ['theme' => 'dark', 'table_density' => 'compact', 'sidebar' => 'collapsed'],
        'localization' => ['language' => 'en', 'timezone' => 'America/New_York', 'date_format' => 'M d, Y', 'time_format' => 'g:i A'],
        'preferences' => ['calendar_view' => 'list', 'week_starts_on' => 'saturday', 'show_weekends' => '0'],
    ]);

    $this->actingAs($user)->get(route('user.settings'))
        ->assertOk()
        ->assertSee('value="dark"', false)
        ->assertSee('value="compact"', false)
        ->assertSee('value="collapsed"', false)
        ->assertSee('value="America/New_York"', false)
        ->assertSee('value="M d, Y"', false)
        ->assertSee('value="g:i A"', false)
        ->assertSee('value="list"', false)
        ->assertSee('value="saturday"', false)
        ->assertSee('name="preferences[show_weekends]" value="1"', false);
});

test('invalid settings values are rejected', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->post(route('user.settings.store'), [
        'appearance' => ['theme' => 'neon', 'table_density' => 'comfortable', 'sidebar' => 'expanded'],
        'localization' => ['language' => 'en', 'timezone' => 'UTC', 'date_format' => 'Y-m-d', 'time_format' => 'H:i'],
        'preferences' => ['calendar_view' => 'month', 'week_starts_on' => 'sunday', 'show_weekends' => '1'],
    ])->assertSessionHasErrors('appearance.theme');

    $this->assertDatabaseCount('user_settings', 0);
});

test('one users settings are not visible to another user', function () {
    $alice = User::factory()->create();
    $bob = User::factory()->create();

    UserSetting::create([
        'user_id' => $alice->id,
        'category' => 'appearance',
        'key' => 'theme',
        'value' => 'dark',
    ]);

    $this->actingAs($bob)->get(route('user.settings'))
        ->assertOk()
        ->assertSee('value="light"', false);
});

test('the user settings model belongs to a user and columns are fillable', function () {
    $user = User::factory()->create();

    $setting = $user->userSettings()->create([
        'category' => 'appearance',
        'key' => 'sidebar',
        'value' => 'collapsed',
    ]);

    $this->assertTrue($setting->user->is($user));
    $this->assertSame('collapsed', $setting->value);
});

test('deleting a user removes their settings via cascade', function () {
    $user = User::factory()->create();

    $user->userSettings()->create([
        'category' => 'appearance',
        'key' => 'theme',
        'value' => 'dark',
    ]);

    $user->delete();

    $this->assertDatabaseMissing('user_settings', ['user_id' => $user->id]);
});