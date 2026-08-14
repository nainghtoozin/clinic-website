<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
    protected $fillable = [
        'invoice_number',
        'patient_id',
        'doctor_id',
        'appointment_id',
        'consultation_id',
        'subtotal',
        'discount',
        'tax',
        'total',
        'amount_paid',
        'balance',
        'status',
        'notes',
        'issued_at',
    ];

    protected $casts = [
        'subtotal' => 'decimal:2',
        'discount' => 'decimal:2',
        'tax' => 'decimal:2',
        'total' => 'decimal:2',
        'amount_paid' => 'decimal:2',
        'balance' => 'decimal:2',
        'issued_at' => 'datetime',
    ];

    public static function boot()
    {
        parent::boot();
        static::creating(function ($invoice) {
            if (empty($invoice->invoice_number)) {
                $invoice->invoice_number = self::generateInvoiceNumber();
            }
        });
    }

    public static function generateInvoiceNumber(): string
    {
        $today = now()->format('Ymd');
        $prefix = "INV-{$today}-";
        $lastInvoice = static::where('invoice_number', 'like', "{$prefix}%")
            ->orderByDesc('invoice_number')
            ->value('invoice_number');

        if ($lastInvoice && preg_match('/^INV-\d{8}-(\d{4})$/', $lastInvoice, $matches)) {
            $nextNumber = (int) $matches[1] + 1;
        } else {
            $nextNumber = 1;
        }

        return $prefix . str_pad($nextNumber, 4, '0', STR_PAD_LEFT);
    }

    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }

    public function doctor()
    {
        return $this->belongsTo(Doctor::class);
    }

    public function appointment()
    {
        return $this->belongsTo(Appointment::class);
    }

    public function consultation()
    {
        return $this->belongsTo(Consultation::class);
    }

    public function items()
    {
        return $this->hasMany(InvoiceItem::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function recalculateTotals(): void
    {
        $subtotal = (float) $this->items()->sum('total');
        $discount = (float) $this->discount;
        $tax = (float) $this->tax;
        $total = max(0, $subtotal - $discount + $tax);
        $amountPaid = (float) $this->amount_paid;
        $balance = max(0, $total - $amountPaid);

        $this->update([
            'subtotal' => $subtotal,
            'total' => $total,
            'balance' => $balance,
        ]);
    }

    public function recalculateStatus(): void
    {
        $this->refresh();
        if ($this->amount_paid <= 0) {
            $newStatus = 'issued';
        } elseif ($this->balance <= 0) {
            $newStatus = 'paid';
        } else {
            $newStatus = 'partially_paid';
        }
        if ($this->status !== $newStatus) {
            $this->update(['status' => $newStatus]);
        }
    }

    public function isDraft(): bool
    {
        return $this->status === 'draft';
    }

    public function isIssued(): bool
    {
        return $this->status === 'issued';
    }

    public function isPartiallyPaid(): bool
    {
        return $this->status === 'partially_paid';
    }

    public function isPaid(): bool
    {
        return $this->status === 'paid';
    }

    public function isCancelled(): bool
    {
        return $this->status === 'cancelled';
    }

    public function canReceivePayment(): bool
    {
        return in_array($this->status, ['issued', 'partially_paid']);
    }

    public function getStatusBadgeClass(): string
    {
        return match ($this->status) {
            'draft' => 'bg-secondary',
            'issued' => 'bg-warning text-dark',
            'partially_paid' => 'bg-info text-dark',
            'paid' => 'bg-success',
            'cancelled' => 'bg-danger',
            default => 'bg-secondary',
        };
    }

    public function getStatusLabel(): string
    {
        return match ($this->status) {
            'draft' => 'Draft',
            'issued' => 'Unpaid',
            'partially_paid' => 'Partially Paid',
            'paid' => 'Paid',
            'cancelled' => 'Cancelled',
            default => ucfirst($this->status),
        };
    }
}
