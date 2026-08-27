<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Consultation extends Model
{
    protected $fillable = [
        'patient_id',
        'doctor_id',
        'appointment_id',
        'queue_ticket_id',
        'symptoms',
        'diagnosis',
        'clinical_notes',
        'treatment_plan',
        'follow_up_date',
        'follow_up_notes',
        'status',
    ];

    protected $casts = [
        'follow_up_date' => 'date',
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

    public function queueTicket()
    {
        return $this->belongsTo(QueueTicket::class, 'queue_ticket_id');
    }

    public function vitalSign()
    {
        return $this->hasOne(VitalSign::class);
    }

    public function prescriptions()
    {
        return $this->hasMany(Prescription::class);
    }

    public function invoice()
    {
        return $this->hasOne(Invoice::class);
    }

    public function investigations()
    {
        return $this->hasMany(Investigation::class);
    }

    public function isDraft(): bool
    {
        return $this->status === 'draft';
    }

    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }
}
