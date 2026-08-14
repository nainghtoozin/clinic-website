<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasFactory, HasRoles, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'avatar',
        'password',
        'is_active',
        'phone',
        'position',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
        ];
    }

    public function doctor()
    {
        return $this->hasOne(Doctor::class);
    }

    public function userSettings(): HasMany
    {
        return $this->hasMany(UserSetting::class);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeInactive(Builder $query): Builder
    {
        return $query->where('is_active', false);
    }

    public function isActive(): bool
    {
        return $this->is_active;
    }

    /**
     * Whether deactivating this user would leave the system with no usable
     * administrator (super-admin or admin). Used to protect critical accounts.
     */
    public function wouldRemoveLastAdministrator(): bool
    {
        $criticalRoles = ['super-admin', 'admin'];

        if ($this->roles->pluck('name')->intersect($criticalRoles)->isEmpty()) {
            return false;
        }

        $otherAdministratorExists = static::query()
            ->whereKeyNot($this->getKey())
            ->where('is_active', true)
            ->whereHas('roles', fn (Builder $query) => $query->whereIn('name', $criticalRoles))
            ->exists();

        return ! $otherAdministratorExists;
    }
}
