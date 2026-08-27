<?php

namespace App\Models;

use App\Services\ClinicSettingsService;
use Illuminate\Database\Eloquent\Model;

class QueueTicket extends Model
{
    protected $fillable = [
        'patient_id',
        'doctor_id',
        'appointment_id',
        'queue_date',
        'ticket_number',
        'status',
        'checked_in_at',
        'called_at',
        'consultation_started_at',
        'completed_at',
        'notes',
    ];

    protected $casts = [
        'queue_date' => 'date',
        'checked_in_at' => 'datetime',
        'called_at' => 'datetime',
        'consultation_started_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

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

    public static function generateTicketNumber(string $date): string
    {
        $prefix = ClinicSettingsService::get('queue.ticket_prefix', 'A');
        $seqLen = ClinicSettingsService::getInt('queue.ticket_sequence_length', 3);
        $lastTicket = static::whereDate('queue_date', $date)
            ->orderBy('ticket_number', 'desc')
            ->value('ticket_number');

        $regex = '/^' . preg_quote($prefix) . '(\d{' . $seqLen . '})$/';
        if ($lastTicket && preg_match($regex, $lastTicket, $matches)) {
            $nextNumber = (int) $matches[1] + 1;
        } else {
            $nextNumber = 1;
        }

        return $prefix . str_pad($nextNumber, $seqLen, '0', STR_PAD_LEFT);
    }

    public function isWaiting(): bool
    {
        return $this->status === 'waiting';
    }

    public function isCalled(): bool
    {
        return $this->status === 'called';
    }

    public function isInConsultation(): bool
    {
        return $this->status === 'in_consultation';
    }

    public function isCancelled(): bool
    {
        return $this->status === 'cancelled';
    }

    public function canBeCalled(): bool
    {
        return $this->status === 'waiting';
    }

    public function canStartConsultation(): bool
    {
        return $this->status === 'called';
    }

    public function canBeCancelled(): bool
    {
        return in_array($this->status, ['waiting', 'called']);
    }

    public function scopeToday($query)
    {
        return $query->whereDate('queue_date', now()->toDateString());
    }

    public function scopeForDoctor($query, $doctorId)
    {
        return $query->where('doctor_id', $doctorId);
    }

    public function scopeActive($query)
    {
        return $query->whereIn('status', ['waiting', 'called', 'in_consultation']);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('queue_date')
            ->orderByRaw("CASE status WHEN 'waiting' THEN 1 WHEN 'called' THEN 2 WHEN 'in_consultation' THEN 3 WHEN 'cancelled' THEN 4 END")
            ->orderBy('checked_in_at')
            ->orderBy('ticket_number');
    }
}
