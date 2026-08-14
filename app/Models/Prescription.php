<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Prescription extends Model
{
    protected $fillable = [
        'patient_id',
        'doctor_id',
        'consultation_id',
        'prescription_number',
        'notes',
        'prescribed_date',
        'status',
        'dispensed_at',
    ];

    protected $casts = [
        'prescribed_date' => 'date',
        'dispensed_at' => 'datetime',
    ];

    public static function boot()
    {
        parent::boot();

        static::creating(function ($prescription) {
            if (empty($prescription->prescription_number)) {
                $prescription->prescription_number = self::generatePrescriptionNumber();
            }
        });
    }

    public static function generatePrescriptionNumber(): string
    {
        $date = now()->format('Ymd');
        $lastPrescription = self::where('prescription_number', 'like', "RX-{$date}-%")
            ->orderByDesc('prescription_number')
            ->first();

        if ($lastPrescription) {
            $lastNumber = (int) substr($lastPrescription->prescription_number, -4);
            $newNumber = str_pad($lastNumber + 1, 4, '0', STR_PAD_LEFT);
        } else {
            $newNumber = '0001';
        }

        return "RX-{$date}-{$newNumber}";
    }

    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }

    public function doctor()
    {
        return $this->belongsTo(Doctor::class);
    }

    public function consultation()
    {
        return $this->belongsTo(Consultation::class);
    }

    public function items()
    {
        return $this->hasMany(PrescriptionItem::class);
    }

    public function getTotalAttribute(): float
    {
        return $this->items->sum(function ($item) {
            return $item->medicine->unit_price * $item->quantity;
        });
    }

    public function isDispensed(): bool
    {
        return $this->status === 'dispensed';
    }

    public function markAsDispensed(): void
    {
        $this->update(['status' => 'dispensed', 'dispensed_at' => now()]);
    }
}