<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;

class InventoryBatch extends Model
{
    public const STATUS_ACTIVE = 'active';

    public const STATUS_DEPLETED = 'depleted';

    protected $fillable = [
        'medicine_id',
        'batch_number',
        'quantity',
        'received_date',
        'expiry_date',
        'unit_cost',
        'supplier',
        'notes',
        'status',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'received_date' => 'date',
        'expiry_date' => 'date',
        'unit_cost' => 'decimal:2',
    ];

    public function medicine(): BelongsTo
    {
        return $this->belongsTo(Medicine::class);
    }

    public function stockMovements(): HasMany
    {
        return $this->hasMany(StockMovement::class);
    }

    /**
     * Whether this batch has any historical dependency (stock-outs, adjustments,
     * dispensing, expiry write-offs). Its own initial stock-in record is not a
     * blocker — that is simply the batch's "birth" record and an untouched,
     * freshly-stocked batch is safe to remove.
     */
    public function hasHistory(): bool
    {
        return $this->stockMovements()
            ->where('type', '!=', 'stock_in')
            ->exists();
    }

    /**
     * True when the batch may be safely deleted (no transaction history).
     */
    public function canDelete(): bool
    {
        return ! $this->hasHistory();
    }

    /**
     * Human-readable reason why deletion is blocked, when it is.
     */
    public function deleteBlockReason(): string
    {
        if ($this->canDelete()) {
            return '';
        }

        return "This batch has stock movement / dispensing / adjustment history and must remain for audit purposes.";
    }

    public function isExpired(): bool
    {
        return $this->expiry_date !== null && $this->expiry_date->isPast();
    }

    public function isExpiringSoon(int $days = 30): bool
    {
        return ! $this->isExpired()
            && $this->quantity > 0
            && $this->expiry_date !== null
            && $this->expiry_date->isFuture()
            && abs($this->expiry_date->diffInDays(now())) <= $days;
    }

    /**
     * Derived expiry state: active | expiring | expired | depleted.
     */
    public function getExpiryStatusAttribute(): string
    {
        if ($this->quantity <= 0) {
            return 'depleted';
        }

        if ($this->isExpired()) {
            return 'expired';
        }

        if ($this->isExpiringSoon()) {
            return 'expiring';
        }

        return 'active';
    }

    public function getStatusBadgeAttribute(): string
    {
        return match ($this->expiry_status) {
            'active' => 'bg-success',
            'expiring' => 'bg-warning text-dark',
            'expired' => 'bg-danger',
            'depleted' => 'bg-secondary',
        };
    }

    public function getExpiryStatusLabelAttribute(): string
    {
        return ucfirst(str_replace('_', ' ', $this->expiry_status));
    }

    /**
     * Record stock received against this batch (adds to batch quantity).
     */
    public function stockIn(int $quantity, ?string $reason = null, ?int $performedBy = null, $reference = null): StockMovement
    {
        return DB::transaction(function () use ($quantity, $reason, $performedBy, $reference) {
            $before = $this->quantity;

            $this->increment('quantity', $quantity);
            $this->refresh();
            $this->medicine()->first()?->reconcileStock();

            return StockMovement::create([
                'medicine_id' => $this->medicine_id,
                'inventory_batch_id' => $this->id,
                'type' => 'stock_in',
                'quantity' => $quantity,
                'balance_before' => $before,
                'balance_after' => $this->quantity,
                'reference_type' => $reference ? get_class($reference) : null,
                'reference_id' => $reference?->id,
                'reason' => $reason,
                'performed_by' => $performedBy,
                'movement_date' => now()->toDateString(),
            ]);
        });
    }

    /**
     * Remove stock from this specific batch. Expired batches can never be
     * deducted automatically or manually.
     */
    public function stockOut(int $quantity, ?string $reason = null, ?int $performedBy = null, $reference = null, string $type = 'stock_out', bool $reconcileMedicine = true): StockMovement
    {
        return DB::transaction(function () use ($quantity, $reason, $performedBy, $reference, $type, $reconcileMedicine) {
            if ($this->isExpired()) {
                throw new \RuntimeException(
                    "Cannot remove stock from expired batch {$this->batch_number}."
                );
            }

            if ($quantity > $this->quantity) {
                throw new \RuntimeException(
                    "Insufficient batch stock. Batch: {$this->batch_number}, Available: {$this->quantity}, Requested: {$quantity}"
                );
            }

            $before = $this->quantity;

            $this->decrement('quantity', $quantity);
            $this->refresh();

            if ($this->quantity <= 0) {
                $this->update(['status' => self::STATUS_DEPLETED]);
            }

            if ($reconcileMedicine) {
                $this->medicine()->first()?->reconcileStock();
            }

            return StockMovement::create([
                'medicine_id' => $this->medicine_id,
                'inventory_batch_id' => $this->id,
                'type' => $type,
                'quantity' => $quantity,
                'balance_before' => $before,
                'balance_after' => $this->quantity,
                'reference_type' => $reference ? get_class($reference) : null,
                'reference_id' => $reference?->id,
                'reason' => $reason,
                'performed_by' => $performedBy,
                'movement_date' => now()->toDateString(),
            ]);
        });
    }

    /**
     * Adjust this specific batch only (increase or decrease). Decrease records a
     * negative quantity and is the expiry/damage/loss write-off workflow.
     */
    public function adjust(int $quantity, bool $isIncrease, string $reason, ?int $performedBy = null, string $type = 'adjustment'): StockMovement
    {
        return DB::transaction(function () use ($quantity, $isIncrease, $reason, $performedBy, $type) {
            $before = $this->quantity;

            if ($isIncrease) {
                $this->increment('quantity', $quantity);
            } else {
                if ($quantity > $this->quantity) {
                    throw new \RuntimeException(
                        "Insufficient batch stock. Batch: {$this->batch_number}, Available: {$this->quantity}, Requested: {$quantity}"
                    );
                }
                $this->decrement('quantity', $quantity);
            }

            $this->refresh();

            if ($this->quantity <= 0) {
                $this->update(['status' => self::STATUS_DEPLETED]);
            }

            $this->medicine()->first()?->reconcileStock();

            return StockMovement::create([
                'medicine_id' => $this->medicine_id,
                'inventory_batch_id' => $this->id,
                'type' => $type,
                'quantity' => $isIncrease ? $quantity : -$quantity,
                'balance_before' => $before,
                'balance_after' => $this->quantity,
                'reason' => $reason,
                'performed_by' => $performedBy,
                'movement_date' => now()->toDateString(),
            ]);
        });
    }

    /**
     * Write off the full remaining quantity of an expired batch.
     */
    public function writeOffExpired(?int $performedBy = null, ?string $reason = null): StockMovement
    {
        return $this->adjust(
            $this->quantity,
            false,
            $reason ?? 'Expired stock written off',
            $performedBy,
            'expired'
        );
    }
}
