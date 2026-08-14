<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StockMovement extends Model
{
    protected $fillable = [
        'medicine_id',
        'type',
        'quantity',
        'balance_after',
        'reference_type',
        'reference_id',
        'reason',
        'performed_by',
        'movement_date',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'balance_after' => 'integer',
        'movement_date' => 'date',
    ];

    public function medicine()
    {
        return $this->belongsTo(Medicine::class);
    }

    public function performer()
    {
        return $this->belongsTo(User::class, 'performed_by');
    }

    public function reference()
    {
        return $this->morphTo();
    }

    public function getTypeLabelAttribute(): string
    {
        return match ($this->type) {
            'opening' => 'Opening Stock',
            'stock_in' => 'Stock In',
            'stock_out' => 'Stock Out',
            'adjustment' => 'Adjustment',
            default => ucfirst($this->type),
        };
    }
}
