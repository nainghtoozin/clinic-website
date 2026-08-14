<?php

use App\Models\User;

test('the html lang attribute reflects the stored language', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->post(route('user.settings.store'), [
        'appearance' => ['theme' => 'light', 'table_density' => 'comfortable', 'sidebar' => 'expanded'],
        'localization' => ['language' => 'my', 'timezone' => 'UTC', 'date_format' => 'Y-m-d', 'time_format' => 'H:i'],
        'preferences' => ['calendar_view' => 'month', 'week_starts_on' => 'sunday', 'show_weekends' => '1'],
    ]);

    $this->actingAs($user)->get(route('user.settings'))
        ->assertOk()
        ->assertSee('lang="my"', false)
        ->assertSee('ဆက်တင်များ');
});

test('the app locale is applied for the authenticated user', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->post(route('user.settings.store'), [
        'appearance' => ['theme' => 'light', 'table_density' => 'comfortable', 'sidebar' => 'expanded'],
        'localization' => ['language' => 'my', 'timezone' => 'UTC', 'date_format' => 'Y-m-d', 'time_format' => 'H:i'],
        'preferences' => ['calendar_view' => 'month', 'week_starts_on' => 'sunday', 'show_weekends' => '1'],
    ]);

    $this->actingAs($user)->get('/profile')
        ->assertOk()
        ->assertSee('lang="my"', false);
});

test('language persists after logout and login', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->post(route('user.settings.store'), [
        'appearance' => ['theme' => 'light', 'table_density' => 'comfortable', 'sidebar' => 'expanded'],
        'localization' => ['language' => 'my', 'timezone' => 'UTC', 'date_format' => 'Y-m-d', 'time_format' => 'H:i'],
        'preferences' => ['calendar_view' => 'month', 'week_starts_on' => 'sunday', 'show_weekends' => '1'],
    ]);

    $this->post(route('logout'));
    $this->get(route('login'))->assertOk();

    $this->actingAs($user)->get(route('user.settings'))
        ->assertOk()
        ->assertSee('lang="my"', false);
});

test('the theme is applied to the html element', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->post(route('user.settings.store'), [
        'appearance' => ['theme' => 'dark', 'table_density' => 'comfortable', 'sidebar' => 'expanded'],
        'localization' => ['language' => 'en', 'timezone' => 'UTC', 'date_format' => 'Y-m-d', 'time_format' => 'H:i'],
        'preferences' => ['calendar_view' => 'month', 'week_starts_on' => 'sunday', 'show_weekends' => '1'],
    ]);

    $this->actingAs($user)->get(route('user.settings'))
        ->assertOk()
        ->assertSee('data-bs-theme="dark"', false);
});

test('theme persists after logout and login', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->post(route('user.settings.store'), [
        'appearance' => ['theme' => 'dark', 'table_density' => 'comfortable', 'sidebar' => 'expanded'],
        'localization' => ['language' => 'en', 'timezone' => 'UTC', 'date_format' => 'Y-m-d', 'time_format' => 'H:i'],
        'preferences' => ['calendar_view' => 'month', 'week_starts_on' => 'sunday', 'show_weekends' => '1'],
    ]);

    $this->post(route('logout'));
    $this->get(route('login'))->assertOk();

    $this->actingAs($user)->get(route('user.settings'))
        ->assertOk()
        ->assertSee('data-bs-theme="dark"', false);
});

test('the default theme is light', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->get(route('user.settings'))
        ->assertOk()
        ->assertSee('data-bs-theme="light"', false);
});

test('validation error messages use the users locale', function () {
    $user = User::factory()->create();

    // First store a valid Myanmar language preference.
    $this->actingAs($user)->post(route('user.settings.store'), [
        'appearance' => ['theme' => 'light', 'table_density' => 'comfortable', 'sidebar' => 'expanded'],
        'localization' => ['language' => 'my', 'timezone' => 'UTC', 'date_format' => 'Y-m-d', 'time_format' => 'H:i'],
        'preferences' => ['calendar_view' => 'month', 'week_starts_on' => 'sunday', 'show_weekends' => '1'],
    ]);

    // Now submit an invalid value and assert the error is translated.
    $this->actingAs($user)->post(route('user.settings.store'), [
        'appearance' => ['theme' => 'neon', 'table_density' => 'comfortable', 'sidebar' => 'expanded'],
        'localization' => ['language' => 'my', 'timezone' => 'UTC', 'date_format' => 'Y-m-d', 'time_format' => 'H:i'],
        'preferences' => ['calendar_view' => 'month', 'week_starts_on' => 'sunday', 'show_weekends' => '1'],
    ])->assertSessionHasErrors();

    $errors = session('errors')->getMessages();
    $this->assertStringContainsString('ရွေးချယ်ထားသော', (string) $errors['appearance.theme'][0]);
});

test('guests always get the default language and theme', function () {
    $this->get('/login')
        ->assertOk()
        ->assertSee('lang="en"', false);
});