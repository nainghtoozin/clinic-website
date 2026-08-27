<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Backup extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'backup_number',
        'type',
        'status',
        'filename',
        'size',
        'disk',
        'notes',
        'metadata',
        'is_safety',
        'created_by',
    ];

    protected $casts = [
        'size' => 'integer',
        'metadata' => 'array',
        'is_safety' => 'boolean',
    ];

    public const TYPES = [
        'full' => 'Full Backup',
        'database' => 'Database Only',
        'files' => 'Files Only',
    ];

    public const STATUSES = [
        'pending' => 'Pending',
        'creating' => 'Creating',
        'completed' => 'Completed',
        'failed' => 'Failed',
        'restoring' => 'Restoring',
        'restored' => 'Restored',
    ];

    public static function generateBackupNumber(): string
    {
        $today = now()->format('Ymd');
        $prefix = 'BKP-' . $today . '-';

        $lastToday = static::where('backup_number', 'like', $prefix . '%')
            ->orderBy('backup_number', 'desc')
            ->value('backup_number');

        if ($lastToday && preg_match('/^BKP-\d{8}-(\d{4})$/', $lastToday, $matches)) {
            $nextNumber = (int) $matches[1] + 1;
        } else {
            $nextNumber = 1;
        }

        return $prefix . str_pad($nextNumber, 4, '0', STR_PAD_LEFT);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function getTypeLabelAttribute(): string
    {
        return self::TYPES[$this->type] ?? ucfirst($this->type);
    }

    public function getStatusLabelAttribute(): string
    {
        return self::STATUSES[$this->status] ?? ucfirst($this->status);
    }

    public function getStatusBadgeClassAttribute(): string
    {
        return match ($this->attributes['status'] ?? $this->status) {
            'pending' => 'bg-warning text-dark',
            'creating' => 'bg-info',
            'completed' => 'bg-success',
            'failed' => 'bg-danger',
            'restoring' => 'bg-info',
            'restored' => 'bg-success',
            default => 'bg-secondary',
        };
    }

    public function getTypeBadgeClassAttribute(): string
    {
        return match ($this->attributes['type'] ?? $this->type) {
            'full' => 'bg-primary',
            'database' => 'bg-info',
            'files' => 'bg-warning text-dark',
            default => 'bg-secondary',
        };
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    public function isFailed(): bool
    {
        return $this->status === 'failed';
    }

    public function isSafetyBackup(): bool
    {
        return $this->is_safety === true;
    }

    public function getFormattedSizeAttribute(): string
    {
        $bytes = $this->size;
        $units = ['B', 'KB', 'MB', 'GB'];
        $i = 0;
        while ($bytes >= 1024 && $i < count($units) - 1) {
            $bytes /= 1024;
            $i++;
        }
        return round($bytes, 2) . ' ' . $units[$i];
    }
}
