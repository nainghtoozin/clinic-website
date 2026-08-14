<?php

namespace App\Models;

use App\Enums\AppointmentStatus;
use Illuminate\Database\Eloquent\Model;

class AppointmentStatusHistory extends Model
{
    protected $fillable = [
        'appointment_id',
        'from_status',
        'to_status',
        'note',
        'changed_by',
    ];

    protected $casts = [
        'from_status' => AppointmentStatus::class,
        'to_status' => AppointmentStatus::class,
    ];

    public function appointment()
    {
        return $this->belongsTo(Appointment::class);
    }

    public function changedBy()
    {
        return $this->belongsTo(User::class, 'changed_by');
    }

    public function getFromLabelAttribute(): string
    {
        return $this->from_status?->label() ?? 'Request';
    }
}