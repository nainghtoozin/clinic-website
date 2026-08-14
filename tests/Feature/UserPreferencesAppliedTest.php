<?php

use App\Models\User;
use Illuminate\Support\Carbon;

beforeEach(function () {
    Carbon::setTestNow('2026-08-14 15:30:00 UTC');
});

afterEach(function () {
    Carbon::setTestNow();
});

test('preferences persist after a refresh', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->post(route('user.settings.store'), [
        'appearance' => ['theme' => 'dark', 'table_density' => 'compact', 'sidebar' => 'collapsed'],
        'localization' => ['language' => 'en', 'timezone' => 'Asia/Yangon', 'date_format' => 'd/m/Y', 'time_format' => 'H:i'],
        'preferences' => ['calendar_view' => 'week', 'week_starts_on' => 'monday', 'show_weekends' => '0'],
    ])->assertSessionHasNoErrors();

    $this->actingAs($user)->get(route('user.settings'))
        ->assertOk()
        ->assertSee('value="compact"', false)
        ->assertSee('value="collapsed"', false)
        ->assertSee('value="Asia/Yangon"', false)
        ->assertSee('value="d/m/Y"', false)
        ->assertSee('value="H:i"', false);
});

test('the topbar date respects the users timezone and date format', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->post(route('user.settings.store'), [
        'appearance' => ['theme' => 'light', 'table_density' => 'comfortable', 'sidebar' => 'expanded'],
        'localization' => ['language' => 'en', 'timezone' => 'Asia/Yangon', 'date_format' => 'd/m/Y', 'time_format' => 'H:i'],
        'preferences' => ['calendar_view' => 'month', 'week_starts_on' => 'sunday', 'show_weekends' => '1'],
    ]);

    $this->actingAs($user)->get(route('user.settings'))
        ->assertOk()
        ->assertSee(Carbon::now('Asia/Yangon')->format('d/m/Y'), false);
});

test('the collapsed sidebar preference is applied to the body class', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->post(route('user.settings.store'), [
        'appearance' => ['theme' => 'light', 'table_density' => 'comfortable', 'sidebar' => 'collapsed'],
        'localization' => ['language' => 'en', 'timezone' => 'UTC', 'date_format' => 'Y-m-d', 'time_format' => 'H:i'],
        'preferences' => ['calendar_view' => 'month', 'week_starts_on' => 'sunday', 'show_weekends' => '1'],
    ]);

    $this->actingAs($user)->get(route('user.settings'))
        ->assertOk()
        ->assertSee('class="sidebar-collapsed ', false);
});

test('the expanded sidebar preference is applied to the body class', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->get(route('user.settings'))
        ->assertOk()
        ->assertSee('class="density-comfortable"', false);
});

test('the compact table density preference is applied to the body class', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->post(route('user.settings.store'), [
        'appearance' => ['theme' => 'light', 'table_density' => 'compact', 'sidebar' => 'expanded'],
        'localization' => ['language' => 'en', 'timezone' => 'UTC', 'date_format' => 'Y-m-d', 'time_format' => 'H:i'],
        'preferences' => ['calendar_view' => 'month', 'week_starts_on' => 'sunday', 'show_weekends' => '1'],
    ]);

    $this->actingAs($user)->get(route('user.settings'))
        ->assertOk()
        ->assertSee('class="density-compact"', false);
});

test('the stored database timestamp is not altered by user preferences', function () {
    $user = User::factory()->create();

    $stored = $user->created_at->copy();

    $this->actingAs($user)->post(route('user.settings.store'), [
        'appearance' => ['theme' => 'light', 'table_density' => 'comfortable', 'sidebar' => 'expanded'],
        'localization' => ['language' => 'en', 'timezone' => 'Asia/Yangon', 'date_format' => 'd/m/Y', 'time_format' => 'H:i'],
        'preferences' => ['calendar_view' => 'month', 'week_starts_on' => 'monday', 'show_weekends' => '1'],
    ]);

    $this->actingAs($user)->get(route('user.settings'))->assertOk();

    $user->refresh();

    $this->assertTrue(
        $user->created_at->eq($stored),
        'The database timestamp was changed by presentation preferences.'
    );
});

test('guest pages keep the application timezone and default formats', function () {
    $this->get(route('login'))
        ->assertOk()
        ->assertStatus(200);
});
