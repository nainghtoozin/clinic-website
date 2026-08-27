<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DoctorUnavailableDate extends Model
{
    protected $fillable = [
        'doctor_id',
        'date',
        'reason',
        'type',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'date' => 'date',
    ];

    public function doctor()
    {
        return $this->belongsTo(Doctor::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function getTypeLabel(): string
    {
        return match ($this->type) {
            'leave' => 'Leave',
            'holiday' => 'Holiday',
            'training' => 'Training',
            'emergency' => 'Emergency',
            'other' => 'Other',
            default => ucfirst($this->type),
        };
    }

    public function getTypeBadgeClass(): string
    {
        return match ($this->type) {
            'leave' => 'bg-warning text-dark',
            'holiday' => 'bg-info text-dark',
            'training' => 'bg-primary',
            'emergency' => 'bg-danger',
            'other' => 'bg-secondary',
            default => 'bg-secondary',
        };
    }
}
