<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Medicine extends Model
{
    protected $fillable = [
        'name',
        'generic_name',
        'manufacturer',
        'category',
        'form',
        'strength',
        'unit_price',
        'stock_quantity',
        'is_active',
        'notes',
        'cost_price',
        'selling_price',
        'unit',
        'minimum_stock_level',
        'expiry_date',
    ];

    protected $casts = [
        'unit_price' => 'decimal:2',
        'cost_price' => 'decimal:2',
        'selling_price' => 'decimal:2',
        'stock_quantity' => 'integer',
        'minimum_stock_level' => 'integer',
        'is_active' => 'boolean',
        'expiry_date' => 'date',
    ];

    public function prescriptionItems()
    {
        return $this->hasMany(PrescriptionItem::class);
    }

    public function stockMovements()
    {
        return $this->hasMany(StockMovement::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function isLowStock(): bool
    {
        return $this->stock_quantity <= $this->minimum_stock_level;
    }

    public function isExpired(): bool
    {
        return $this->expiry_date && $this->expiry_date->isPast();
    }

    public function isExpiringSoon(int $days = 30): bool
    {
        return $this->expiry_date && $this->expiry_date->diffInDays(now()) <= $days && !$this->isExpired();
    }

    public function getStockStatusAttribute(): string
    {
        if ($this->isExpired()) return 'expired';
        if ($this->isExpiringSoon()) return 'expiring';
        if ($this->isLowStock()) return 'low';
        return 'normal';
    }

    public function setOpeningStock(int $quantity, int $performedBy = null): StockMovement
    {
        return DB::transaction(function () use ($quantity, $performedBy) {
            $this->update(['stock_quantity' => $quantity]);

            return StockMovement::create([
                'medicine_id' => $this->id,
                'type' => 'opening',
                'quantity' => $quantity,
                'balance_after' => $quantity,
                'reason' => 'Initial opening stock',
                'performed_by' => $performedBy,
                'movement_date' => now()->toDateString(),
            ]);
        });
    }

    public function stockIn(int $quantity, string $reason = null, int $performedBy = null, $reference = null): StockMovement
    {
        return DB::transaction(function () use ($quantity, $reason, $performedBy, $reference) {
            $this->increment('stock_quantity', $quantity);
            $this->refresh();

            return StockMovement::create([
                'medicine_id' => $this->id,
                'type' => 'stock_in',
                'quantity' => $quantity,
                'balance_after' => $this->stock_quantity,
                'reference_type' => $reference ? get_class($reference) : null,
                'reference_id' => $reference?->id,
                'reason' => $reason,
                'performed_by' => $performedBy,
                'movement_date' => now()->toDateString(),
            ]);
        });
    }

    public function stockOut(int $quantity, string $reason = null, int $performedBy = null, $reference = null): StockMovement
    {
        return DB::transaction(function () use ($quantity, $reason, $performedBy, $reference) {
            if ($this->stock_quantity < $quantity) {
                throw new \RuntimeException(
                    "Insufficient stock for {$this->name}. Available: {$this->stock_quantity}, Requested: {$quantity}"
                );
            }

            $this->decrement('stock_quantity', $quantity);
            $this->refresh();

            return StockMovement::create([
                'medicine_id' => $this->id,
                'type' => 'stock_out',
                'quantity' => $quantity,
                'balance_after' => $this->stock_quantity,
                'reference_type' => $reference ? get_class($reference) : null,
                'reference_id' => $reference?->id,
                'reason' => $reason,
                'performed_by' => $performedBy,
                'movement_date' => now()->toDateString(),
            ]);
        });
    }

    public function adjust(int $quantity, bool $isIncrease, string $reason, int $performedBy = null): StockMovement
    {
        if (empty(trim($reason))) {
            throw new \InvalidArgumentException('Adjustment reason is required.');
        }

        return DB::transaction(function () use ($quantity, $isIncrease, $reason, $performedBy) {
            if ($isIncrease) {
                $this->increment('stock_quantity', $quantity);
            } else {
                if ($this->stock_quantity < $quantity) {
                    throw new \RuntimeException(
                        "Insufficient stock for {$this->name}. Available: {$this->stock_quantity}, Requested: {$quantity}"
                    );
                }
                $this->decrement('stock_quantity', $quantity);
            }
            $this->refresh();

            return StockMovement::create([
                'medicine_id' => $this->id,
                'type' => 'adjustment',
                'quantity' => $isIncrease ? $quantity : -$quantity,
                'balance_after' => $this->stock_quantity,
                'reason' => $reason,
                'performed_by' => $performedBy,
                'movement_date' => now()->toDateString(),
            ]);
        });
    }
}
