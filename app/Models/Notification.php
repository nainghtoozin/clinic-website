<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Notification extends Model
{
    protected $fillable = [
        'user_id',
        'type',
        'title',
        'message',
        'module',
        'action',
        'notifiable_type',
        'notifiable_id',
        'is_read',
        'read_at',
        'url',
        'metadata',
    ];

    protected $casts = [
        'is_read' => 'boolean',
        'read_at' => 'datetime',
        'metadata' => 'array',
    ];

    public const TYPES = [
        'appointment' => 'Appointment',
        'queue' => 'Queue',
        'consultation' => 'Consultation',
        'prescription' => 'Prescription',
        'investigation' => 'Investigation',
        'inventory' => 'Inventory',
        'expiry' => 'Expiry',
        'invoice' => 'Invoice',
        'payment' => 'Payment',
        'expense' => 'Expense',
        'communication' => 'Communication',
        'backup' => 'Backup',
        'system' => 'System',
    ];

    public const MODULE_ICONS = [
        'appointment' => 'bi-calendar-check',
        'queue' => 'bi-people',
        'consultation' => 'bi-clipboard2-pulse',
        'prescription' => 'bi-capsule',
        'investigation' => 'bi-flask',
        'inventory' => 'bi-box-seam',
        'expiry' => 'bi-exclamation-triangle',
        'invoice' => 'bi-receipt',
        'payment' => 'bi-credit-card',
        'expense' => 'bi-cash',
        'communication' => 'bi-chat-dots',
        'backup' => 'bi-cloud-download',
        'system' => 'bi-gear',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function notifiable()
    {
        if (!$this->notifiable_type || !$this->notifiable_id) {
            return null;
        }

        $type = $this->notifiable_type;

        if (!class_exists($type)) {
            return null;
        }

        return $type::find($this->notifiable_id);
    }

    public function markAsRead(): void
    {
        if (!$this->is_read) {
            $this->update(['is_read' => true, 'read_at' => now()]);
        }
    }

    public function markAsUnread(): void
    {
        $this->update(['is_read' => false, 'read_at' => null]);
    }

    public function scopeUnread(Builder $query): Builder
    {
        return $query->where('is_read', false);
    }

    public function scopeForUser(Builder $query, int $userId): Builder
    {
        return $query->where('user_id', $userId);
    }

    public function scopeForModule(Builder $query, string $module): Builder
    {
        return $query->where('module', $module);
    }

    public function scopeForType(Builder $query, string $type): Builder
    {
        return $query->where('type', $type);
    }

    public function scopeSearch(Builder $query, string $search): Builder
    {
        return $query->where(function (Builder $q) use ($search) {
            $q->where('title', 'like', "%{$search}%")
              ->orWhere('message', 'like', "%{$search}%");
        });
    }

    public function getTypeLabelAttribute(): string
    {
        return self::TYPES[$this->type] ?? ucfirst($this->type);
    }

    public function getIconAttribute(): string
    {
        return self::MODULE_ICONS[$this->type] ?? 'bi-bell';
    }

    public function getTimeAgoAttribute(): string
    {
        return $this->created_at->diffForHumans();
    }
}
