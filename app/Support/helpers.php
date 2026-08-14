<?php

use App\Models\Setting;
use App\Services\UserSettingsService;
use Illuminate\Support\Carbon;

if (!function_exists('setting')) {
    function setting(string $key, $default = null)
    {
        return cache()->rememberForever("setting:$key", function () use ($key, $default) {
            return Setting::where('key', $key)->value('value') ?? $default;
        });
    }
}

/**
 * Resolve the authenticated user's presentation preferences.
 *
 * These are presentation-level only. No global timezone is mutated and no
 * database timestamps are altered; Eloquent continues to store and read
 * timestamps in the application timezone.
 *
 * @return array{timezone: string, date_format: string, time_format: string, sidebar: string, table_density: string}
 */
if (!function_exists('user_preferences')) {
    function user_preferences(): array
    {
        static $prefs = null;

        if ($prefs !== null) {
            return $prefs;
        }

        $defaults = [
            'timezone' => (string) config('app.timezone', 'UTC'),
            'date_format' => 'M d, Y',
            'time_format' => 'h:i A',
            'sidebar' => 'expanded',
            'table_density' => 'comfortable',
        ];

        if (!auth()->check()) {
            return $prefs = $defaults;
        }

        $service = app(UserSettingsService::class);
        $user = auth()->user();

        return $prefs = [
            'timezone' => (string) $service->get($user, 'localization', 'timezone', $defaults['timezone']),
            'date_format' => (string) $service->get($user, 'localization', 'date_format', $defaults['date_format']),
            'time_format' => (string) $service->get($user, 'localization', 'time_format', $defaults['time_format']),
            'sidebar' => (string) $service->get($user, 'appearance', 'sidebar', $defaults['sidebar']),
            'table_density' => (string) $service->get($user, 'appearance', 'table_density', $defaults['table_density']),
        ];
    }
}

if (!function_exists('user_timezone')) {
    function user_timezone(): string
    {
        return user_preferences()['timezone'];
    }
}

if (!function_exists('user_date_format')) {
    function user_date_format(): string
    {
        return user_preferences()['date_format'];
    }
}

if (!function_exists('user_time_format')) {
    function user_time_format(): string
    {
        return user_preferences()['time_format'];
    }
}

if (!function_exists('user_sidebar_preference')) {
    function user_sidebar_preference(): string
    {
        return user_preferences()['sidebar'];
    }
}

if (!function_exists('user_table_density')) {
    function user_table_density(): string
    {
        return user_preferences()['table_density'];
    }
}

/**
 * Format a date-only value (no timezone shift). Safe for DATE columns.
 */
if (!function_exists('fmt_date')) {
    function fmt_date(mixed $value): string
    {
        if (blank($value)) {
            return '-';
        }

        return Carbon::parse($value)->format(user_date_format());
    }
}

/**
 * Format a time-only value (no timezone shift). Safe for TIME columns.
 */
if (!function_exists('fmt_time')) {
    function fmt_time(mixed $value): string
    {
        if (blank($value)) {
            return '-';
        }

        return Carbon::parse($value)->format(user_time_format());
    }
}

/**
 * Format a full timestamp in the user's timezone. Presentation-level only.
 */
if (!function_exists('fmt_datetime')) {
    function fmt_datetime(mixed $value): string
    {
        if (blank($value)) {
            return '-';
        }

        return Carbon::parse($value)
            ->setTimezone(user_timezone())
            ->format(user_date_format() . ' ' . user_time_format());
    }
}

/**
 * Today's date in the user's timezone using the preferred date format.
 */
if (!function_exists('fmt_today')) {
    function fmt_today(): string
    {
        return Carbon::now(user_timezone())->format(user_date_format());
    }
}
