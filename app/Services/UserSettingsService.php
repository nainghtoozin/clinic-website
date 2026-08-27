<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Collection;

class UserSettingsService
{
    /**
     * Default settings per category. These are the fallback values returned
     * whenever a user has not saved an override. Stored per-user in the
     * user_settings table — never in the global config.
     */
    protected array $defaults = [
        'appearance' => [
            'theme' => 'light',
            'table_density' => 'comfortable',
            'sidebar' => 'expanded',
        ],
        'localization' => [
            'language' => 'en',
            'timezone' => 'UTC',
            'date_format' => 'Y-m-d',
            'time_format' => 'H:i',
        ],
        'preferences' => [
            'calendar_view' => 'month',
            'week_starts_on' => 'sunday',
            'show_weekends' => true,
        ],
        'notifications' => [
            'appointment_notifications' => true,
            'queue_notifications' => true,
            'consultation_notifications' => true,
            'prescription_notifications' => true,
            'investigation_notifications' => true,
            'inventory_notifications' => true,
            'expiry_notifications' => true,
            'invoice_notifications' => true,
            'payment_notifications' => true,
            'expense_notifications' => true,
            'communication_notifications' => true,
            'backup_notifications' => true,
            'system_notifications' => true,
        ],
    ];

    public function categories(): array
    {
        return array_keys($this->defaults);
    }

    public function defaults(?string $category = null): array
    {
        if ($category !== null) {
            return $this->defaults[$category] ?? [];
        }

        return $this->defaults;
    }

    /**
     * Merge defaults with the user's stored settings for all categories.
     *
     * @return array<string, array<string, mixed>>
     */
    public function all(User $user): array
    {
        $settings = $user->userSettings()
            ->get()
            ->groupBy('category');

        $result = [];

        foreach ($this->defaults as $category => $keys) {
            $result[$category] = $this->mergeCategory($keys, $settings->get($category, collect()));
        }

        return $result;
    }

    /**
     * Resolved settings (defaults + stored) for a single category.
     */
    public function category(User $user, string $category): array
    {
        $keys = $this->defaults[$category] ?? [];

        $stored = $user->userSettings()
            ->where('category', $category)
            ->get();

        return $this->mergeCategory($keys, $stored);
    }

    /**
     * Get one resolved value for the user.
     */
    public function get(User $user, string $category, string $key, mixed $default = null): mixed
    {
        $stored = $user->userSettings()
            ->where('category', $category)
            ->where('key', $key)
            ->value('value');

        if ($stored !== null) {
            return $this->decode($stored);
        }

        return $default ?? $this->defaults[$category][$key] ?? null;
    }

    /**
     * Persist a batch of validated values for a category (update or create).
     */
    public function save(User $user, string $category, array $values): void
    {
        foreach ($values as $key => $value) {
            $user->userSettings()->updateOrCreate(
                ['category' => $category, 'key' => $key],
                ['value' => $this->encode($value)],
            );
        }
    }

    /**
     * Remove a stored override so the default applies again.
     */
    public function forget(User $user, string $category, string $key): void
    {
        $user->userSettings()
            ->where('category', $category)
            ->where('key', $key)
            ->delete();
    }

    protected function mergeCategory(array $keys, Collection $stored): array
    {
        $values = $stored->pluck('value', 'key')
            ->map(fn ($value) => $this->decode($value))
            ->toArray();

        return array_merge($keys, $values);
    }

    protected function encode(mixed $value): string
    {
        return is_bool($value)
            ? ($value ? '1' : '0')
            : (string) $value;
    }

    protected function decode(mixed $value): mixed
    {
        if ($value === '1' || $value === '0') {
            return (bool) $value;
        }

        return $value;
    }
}
