<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Communication extends Model
{
    protected $fillable = [
        'patient_id',
        'appointment_id',
        'user_id',
        'contact_method',
        'purpose',
        'outcome',
        'contacted_at',
        'note',
        'follow_up_date',
        'follow_up_note',
        'follow_up_completed',
    ];

    protected $casts = [
        'contacted_at' => 'datetime',
        'follow_up_date' => 'date',
        'follow_up_completed' => 'boolean',
    ];

    public const CONTACT_METHODS = [
        'phone' => 'Phone',
        'in_person' => 'In Person',
        'sms' => 'SMS',
        'email' => 'Email',
        'telegram' => 'Telegram',
        'other' => 'Other',
    ];

    public const PURPOSES = [
        'appointment_confirmation' => 'Appointment Confirmation',
        'rejection' => 'Appointment Rejection',
        'reschedule' => 'Appointment Reschedule',
        'cancellation' => 'Appointment Cancellation',
        'reminder' => 'Appointment Reminder',
        'follow_up' => 'Follow-up',
        'test_result' => 'Test Result Notification',
        'general' => 'General Patient Contact',
        'other' => 'Other',
    ];

    public const OUTCOMES = [
        'contacted' => 'Contacted',
        'no_answer' => 'No Answer',
        'callback_requested' => 'Patient Requested Callback',
        'confirmed' => 'Confirmed',
        'rescheduled' => 'Rescheduled',
        'cancelled' => 'Cancelled',
        'informed' => 'Informed',
        'other' => 'Other',
    ];

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    public function appointment(): BelongsTo
    {
        return $this->belongsTo(Appointment::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function getContactMethodLabelAttribute(): string
    {
        return self::CONTACT_METHODS[$this->contact_method] ?? ucfirst($this->contact_method);
    }

    public function getPurposeLabelAttribute(): string
    {
        return self::PURPOSES[$this->purpose] ?? ucfirst($this->purpose);
    }

    public function getOutcomeLabelAttribute(): string
    {
        return self::OUTCOMES[$this->outcome] ?? ucfirst($this->outcome);
    }

    public function getContactMethodBadgeClass(): string
    {
        return match ($this->contact_method) {
            'phone' => 'bg-primary',
            'in_person' => 'bg-success',
            'sms' => 'bg-info',
            'email' => 'bg-warning text-dark',
            'telegram' => 'bg-secondary',
            default => 'bg-secondary',
        };
    }

    public function getPurposeBadgeClass(): string
    {
        return match ($this->purpose) {
            'appointment_confirmation' => 'bg-success',
            'rejection' => 'bg-danger',
            'reschedule' => 'bg-info',
            'cancellation' => 'bg-danger',
            'reminder' => 'bg-warning text-dark',
            'follow_up' => 'bg-primary',
            'test_result' => 'bg-info',
            'general' => 'bg-secondary',
            default => 'bg-secondary',
        };
    }

    public function getOutcomeBadgeClass(): string
    {
        return match ($this->outcome) {
            'contacted' => 'bg-success',
            'no_answer' => 'bg-warning text-dark',
            'callback_requested' => 'bg-info',
            'confirmed' => 'bg-success',
            'rescheduled' => 'bg-info',
            'cancelled' => 'bg-danger',
            'informed' => 'bg-secondary',
            default => 'bg-secondary',
        };
    }

    public function isFollowUpPending(): bool
    {
        return $this->follow_up_date !== null && !$this->follow_up_completed;
    }

    public function isFollowUpOverdue(): bool
    {
        return $this->isFollowUpPending() && $this->follow_up_date->isPast();
    }

    public function isFollowUpDueToday(): bool
    {
        return $this->isFollowUpPending() && $this->follow_up_date->isToday();
    }

    public function scopePendingFollowUps($query)
    {
        return $query->where('follow_up_date', '!=', null)
            ->where('follow_up_completed', false);
    }

    public function scopeOverdueFollowUps($query)
    {
        return $query->pendingFollowUps()
            ->where('follow_up_date', '<', now()->toDateString());
    }

    public function scopeDueTodayFollowUps($query)
    {
        return $query->pendingFollowUps()
            ->whereDate('follow_up_date', now()->toDateString());
    }

    public function scopeUpcomingFollowUps($query)
    {
        return $query->pendingFollowUps()
            ->where('follow_up_date', '>', now()->toDateString());
    }
}
