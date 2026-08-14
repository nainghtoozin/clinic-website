<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InvoiceItem extends Model
{
    protected $fillable = [
        'invoice_id',
        'description',
        'type',
        'quantity',
        'unit_price',
        'total',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'unit_price' => 'decimal:2',
        'total' => 'decimal:2',
    ];

    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }

    public function recalculateTotal(): void
    {
        $total = (float) $this->quantity * (float) $this->unit_price;
        $this->update(['total' => $total]);
    }

    public function getTypeLabel(): string
    {
        return match ($this->type) {
            'consultation' => 'Consultation Fee',
            'medicine' => 'Medicine',
            'service' => 'Service',
            'other' => 'Other',
            default => ucfirst($this->type),
        };
    }
}
