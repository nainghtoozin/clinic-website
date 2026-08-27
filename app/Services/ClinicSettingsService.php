<?php

namespace App\Services;

use App\Models\Setting;
use Illuminate\Support\Facades\Cache;

class ClinicSettingsService
{
    protected const DEFAULTS = [
        'site.site_name' => 'Clinic',
        'site.email' => null,
        'site.phone' => null,
        'site.address' => null,
        'site.logo' => null,
        'site.social.facebook' => null,
        'site.social.twitter' => null,
        'site.social.instagram' => null,
        'site.social.linkedin' => null,

        'clinic_name' => null,
        'clinic_email' => null,
        'clinic_phone' => null,
        'clinic_address' => null,
        'clinic_currency' => 'USD',
        'clinic_opening_hours' => null,
        'clinic_default_fee' => null,
        'clinic_tax_rate' => null,
        'clinic_receipt_footer' => null,

        'appointment.default_duration' => 30,
        'appointment.min_duration' => 15,
        'appointment.max_duration' => 180,
        'appointment.advance_booking_days' => 90,
        'appointment.cancellation_hours' => 24,

        'queue.ticket_prefix' => 'A',
        'queue.ticket_sequence_length' => 3,

        'invoice.prefix' => 'INV',
        'invoice.sequence_length' => 4,
        'invoice.default_tax_rate' => null,

        'prescription.prefix' => 'RX',
        'prescription.sequence_length' => 4,

        'inventory.expiry_warning_days' => 30,
    ];

    public static function get(string $key, mixed $default = null): mixed
    {
        $default = $default ?? self::DEFAULTS[$key] ?? null;

        return cache()->rememberForever("setting:{$key}", function () use ($key, $default) {
            return Setting::where('key', $key)->value('value') ?? $default;
        });
    }

    public static function getArray(string $key, mixed $default = null): mixed
    {
        $value = self::get($key, $default);

        if (is_string($value) && str_starts_with($value, '[')) {
            $decoded = json_decode($value, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                return $decoded;
            }
        }

        return $value;
    }

    public static function getInt(string $key, int $default = 0): int
    {
        return (int) self::get($key, $default);
    }

    public static function getFloat(string $key, float $default = 0.0): float
    {
        return (float) self::get($key, $default);
    }

    public static function getBool(string $key, bool $default = false): bool
    {
        $value = self::get($key, $default);

        return in_array($value, ['1', 'true', true], true);
    }

    public static function set(string $key, mixed $value, string $group = 'clinic'): void
    {
        Setting::updateOrCreate(
            ['key' => $key],
            ['value' => $value, 'group' => $group]
        );

        Cache::forget("setting:{$key}");
    }

    public static function setMany(array $keyValuePairs, string $group = 'clinic'): void
    {
        foreach ($keyValuePairs as $key => $value) {
            self::set($key, $value, $group);
        }
    }

    public static function all(string $group): array
    {
        return Setting::where('group', $group)
            ->pluck('value', 'key')
            ->toArray();
    }

    public static function forget(string $key): void
    {
        Setting::where('key', $key)->delete();
        Cache::forget("setting:{$key}");
    }

    public static function flush(): void
    {
        $keys = array_keys(self::DEFAULTS);
        foreach ($keys as $key) {
            Cache::forget("setting:{$key}");
        }
    }
}
