<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AuditLog extends Model
{
    protected $fillable = [
        'user_id',
        'action',
        'module',
        'auditable_type',
        'auditable_id',
        'description',
        'old_values',
        'new_values',
        'metadata',
        'ip_address',
        'user_agent',
    ];

    protected $casts = [
        'old_values' => 'array',
        'new_values' => 'array',
        'metadata' => 'array',
    ];

    public const ACTIONS = [
        'created' => 'Created',
        'updated' => 'Updated',
        'deleted' => 'Deleted',
        'restored' => 'Restored',
        'cancelled' => 'Cancelled',
        'approved' => 'Approved',
        'rejected' => 'Rejected',
        'completed' => 'Completed',
        'status_changed' => 'Status Changed',
        'login' => 'Login',
        'logout' => 'Logout',
        'backup_created' => 'Backup Created',
        'backup_restored' => 'Backup Restored',
        'backup_deleted' => 'Backup Deleted',
        'permission_changed' => 'Permission Changed',
        'role_changed' => 'Role Changed',
    ];

    public const MODULES = [
        'Patient' => 'Patient',
        'Doctor' => 'Doctor',
        'Appointment' => 'Appointment',
        'Queue' => 'Queue',
        'Consultation' => 'Consultation',
        'VitalSigns' => 'Vital Signs',
        'Prescription' => 'Prescription',
        'Medicine' => 'Medicine',
        'Inventory' => 'Inventory',
        'StockMovement' => 'Stock Movement',
        'Investigation' => 'Investigation',
        'Invoice' => 'Invoice',
        'Payment' => 'Payment',
        'Expense' => 'Expense',
        'Communication' => 'Communication',
        'User' => 'User',
        'Role' => 'Role',
        'Settings' => 'Settings',
        'Backup' => 'Backup',
        'Auth' => 'Authentication',
        'Staff' => 'Staff',
    ];

    public const SENSITIVE_FIELDS = [
        'password',
        'password_confirmation',
        'current_password',
        'new_password',
        'new_password_confirmation',
        'remember_token',
        'api_token',
        'email_verified_at',
        'app_key',
        'database_password',
        'secret',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function auditable()
    {
        return $this->morphTo();
    }

    public function getActionLabelAttribute(): string
    {
        return self::ACTIONS[$this->action] ?? ucfirst(str_replace('_', ' ', $this->action));
    }

    public function getModuleLabelAttribute(): string
    {
        return self::MODULES[$this->module] ?? ucfirst($this->module);
    }

    public function getActionBadgeClassAttribute(): string
    {
        return match ($this->action) {
            'created' => 'bg-success',
            'updated' => 'bg-primary',
            'deleted' => 'bg-danger',
            'restored' => 'bg-info',
            'cancelled' => 'bg-warning text-dark',
            'approved' => 'bg-success',
            'rejected' => 'bg-danger',
            'completed' => 'bg-success',
            'status_changed' => 'bg-info',
            'login', 'logout' => 'bg-secondary',
            'backup_created', 'backup_restored', 'backup_deleted' => 'bg-info',
            'permission_changed', 'role_changed' => 'bg-warning text-dark',
            default => 'bg-secondary',
        };
    }

    public function getFormattedChangesAttribute(): ?array
    {
        if (empty($this->old_values) && empty($this->new_values)) {
            return null;
        }

        $changes = [];
        $allKeys = array_unique(array_merge(array_keys($this->old_values ?? []), array_keys($this->new_values ?? [])));

        foreach ($allKeys as $key) {
            if (in_array($key, self::SENSITIVE_FIELDS)) {
                continue;
            }
            $changes[$key] = [
                'old' => $this->old_values[$key] ?? null,
                'new' => $this->new_values[$key] ?? null,
            ];
        }

        return empty($changes) ? null : $changes;
    }

    public function scopeForModule($query, string $module)
    {
        return $query->where('module', $module);
    }

    public function scopeForAction($query, string $action)
    {
        return $query->where('action', $action);
    }

    public function scopeForUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeDateRange($query, ?string $from, ?string $to)
    {
        if ($from) {
            $query->where('created_at', '>=', $from);
        }
        if ($to) {
            $query->where('created_at', '<=', $to . ' 23:59:59');
        }
        return $query;
    }

    public function scopeSearch($query, ?string $search)
    {
        if (!$search) {
            return $query;
        }
        return $query->where(function ($q) use ($search) {
            $q->where('description', 'like', "%{$search}%")
              ->orWhere('module', 'like', "%{$search}%")
              ->orWhere('action', 'like', "%{$search}%")
              ->orWhereHas('user', function ($uq) use ($search) {
                  $uq->where('name', 'like', "%{$search}%");
              });
        });
    }

    public static function sanitizeValues(array $values): array
    {
        foreach (self::SENSITIVE_FIELDS as $field) {
            unset($values[$field]);
        }
        return $values;
    }
}
