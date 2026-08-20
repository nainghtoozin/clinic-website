<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StockMovement extends Model
{
    protected $fillable = [
        'medicine_id',
        'inventory_batch_id',
        'type',
        'quantity',
        'balance_before',
        'balance_after',
        'reference_type',
        'reference_id',
        'reason',
        'performed_by',
        'movement_date',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'balance_before' => 'integer',
        'balance_after' => 'integer',
        'movement_date' => 'date',
    ];

    public function medicine()
    {
        return $this->belongsTo(Medicine::class);
    }

    public function inventoryBatch()
    {
        return $this->belongsTo(InventoryBatch::class);
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
            'dispensed' => 'Dispensed',
            'expired' => 'Expired',
            default => ucfirst($this->type),
        };
    }

    /**
     * Presentable detail used by the movement detail modal.
     *
     * Requires medicine, performer, inventoryBatch and reference to be loaded
     * (eager-load them when building lists).
     *
     * @return array<string, mixed>
     */
    public function detail(): array
    {
        $reference = null;

        if ($this->relationLoaded('reference') && $this->reference) {
            $reference = $this->reference->prescription_number
                ?? '#' . $this->reference->getKey();
        }

        return [
            'id' => $this->id,
            'type' => $this->type_label,
            'type_key' => $this->type,
            'medicine' => $this->medicine?->name ?? 'Deleted medicine',
            'batch' => $this->inventoryBatch?->batch_number,
            'quantity' => $this->quantity,
            'quantity_display' => ($this->quantity > 0 ? '+' : '') . $this->quantity,
            'before' => $this->balance_before,
            'after' => $this->balance_after,
            'date' => $this->created_at
                ? $this->created_at->format('M d, Y H:i')
                : (string) $this->movement_date,
            'by' => $this->performer?->name ?? 'System',
            'reference' => $reference,
            'reason' => $this->reason,
            'note' => $this->inventoryBatch?->notes,
        ];
    }
}
