<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Expense extends Model
{
    protected $fillable = [
        'expense_number',
        'expense_category_id',
        'amount',
        'payment_method',
        'expense_date',
        'vendor',
        'description',
        'note',
        'reference_number',
        'status',
        'created_by',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'expense_date' => 'date',
    ];

    public const PAYMENT_METHODS = [
        'cash' => 'Cash',
        'card' => 'Card',
        'bank_transfer' => 'Bank Transfer',
        'mobile_payment' => 'Mobile Payment',
        'other' => 'Other',
    ];

    public const STATUSES = [
        'active' => 'Active',
        'cancelled' => 'Cancelled',
    ];

    public static function generateExpenseNumber(): string
    {
        $today = now()->format('Ymd');
        $prefix = 'EXP-' . $today . '-';

        $lastToday = static::where('expense_number', 'like', $prefix . '%')
            ->orderBy('expense_number', 'desc')
            ->value('expense_number');

        if ($lastToday && preg_match('/^EXP-\d{8}-(\d{4})$/', $lastToday, $matches)) {
            $nextNumber = (int) $matches[1] + 1;
        } else {
            $nextNumber = 1;
        }

        return $prefix . str_pad($nextNumber, 4, '0', STR_PAD_LEFT);
    }

    public function expenseCategory(): BelongsTo
    {
        return $this->belongsTo(ExpenseCategory::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function getPaymentMethodLabelAttribute(): string
    {
        return self::PAYMENT_METHODS[$this->payment_method] ?? ucfirst($this->payment_method);
    }

    public function getStatusLabelAttribute(): string
    {
        return self::STATUSES[$this->status] ?? ucfirst($this->status);
    }

    public function getPaymentMethodBadgeClass(): string
    {
        return match ($this->payment_method) {
            'cash' => 'bg-success',
            'card' => 'bg-primary',
            'bank_transfer' => 'bg-info',
            'mobile_payment' => 'bg-warning text-dark',
            default => 'bg-secondary',
        };
    }

    public function getStatusBadgeClass(): string
    {
        return match ($this->status) {
            'active' => 'bg-success',
            'cancelled' => 'bg-danger',
            default => 'bg-secondary',
        };
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function isCancelled(): bool
    {
        return $this->status === 'cancelled';
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }
}
