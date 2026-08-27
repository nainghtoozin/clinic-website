<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Investigation extends Model
{
    protected $fillable = [
        'patient_id',
        'doctor_id',
        'consultation_id',
        'lab_test_id',
        'requested_date',
        'priority',
        'clinical_notes',
        'status',
        'result_value',
        'result_unit',
        'result_reference_range',
        'interpretation',
        'resulted_at',
        'result_status',
        'invoice_id',
    ];

    protected $casts = [
        'requested_date' => 'date',
        'resulted_at' => 'datetime',
    ];

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

    public function labTest()
    {
        return $this->belongsTo(LabTest::class);
    }

    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }

    public function isRequested(): bool
    {
        return $this->status === 'requested';
    }

    public function isInProgress(): bool
    {
        return $this->status === 'in_progress';
    }

    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    public function isCancelled(): bool
    {
        return $this->status === 'cancelled';
    }

    public function canTransitionTo(string $newStatus): bool
    {
        return match ($this->status) {
            'requested' => in_array($newStatus, ['in_progress', 'cancelled']),
            'in_progress' => in_array($newStatus, ['completed', 'cancelled']),
            default => false,
        };
    }

    public function getStatusBadgeClass(): string
    {
        return match ($this->status) {
            'requested' => 'bg-info text-dark',
            'in_progress' => 'bg-warning text-dark',
            'completed' => 'bg-success',
            'cancelled' => 'bg-secondary',
            default => 'bg-secondary',
        };
    }

    public function getStatusLabel(): string
    {
        return match ($this->status) {
            'requested' => 'Requested',
            'in_progress' => 'In Progress',
            'completed' => 'Completed',
            'cancelled' => 'Cancelled',
            default => ucfirst($this->status),
        };
    }

    public function getPriorityBadgeClass(): string
    {
        return match ($this->priority) {
            'urgent' => 'bg-danger',
            'stat' => 'bg-warning text-dark',
            'routine' => 'bg-secondary',
            default => 'bg-secondary',
        };
    }
}
