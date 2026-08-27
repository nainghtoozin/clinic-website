<?php

namespace App\Models;

use App\Enums\AppointmentStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Appointment extends Model
{
    protected $fillable = [
        'patient_id',
        'appointment_number',
        'name',
        'email',
        'phone',
        'date',
        'time',
        'duration',
        'doctor_id',
        'department_id',
        'message',
        'cancel_reason',
        'status',
        'source',
    ];

    protected $casts = [
        'date' => 'date',
        'duration' => 'integer',
        'status' => AppointmentStatus::class,
    ];

    protected static function booted()
    {
        static::created(function (Appointment $appointment) {
            $appointment->statusHistories()->create([
                'from_status' => null,
                'to_status' => $appointment->status?->value ?? AppointmentStatus::Pending->value,
                'note' => $appointment->source === 'public'
                    ? 'Appointment request created.'
                    : 'Appointment scheduled.',
                'changed_by' => auth()->id(),
            ]);
        });
    }

    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }

    public function doctor()
    {
        return $this->belongsTo(Doctor::class);
    }

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function statusHistories()
    {
        return $this->hasMany(AppointmentStatusHistory::class);
    }

    public function communications()
    {
        return $this->hasMany(Communication::class);
    }

    public static function generateAppointmentNumber(): string
    {
        $today = now()->format('Ymd');
        $prefix = 'APT-' . $today . '-';

        $lastToday = static::where('appointment_number', 'like', $prefix . '%')
            ->orderBy('appointment_number', 'desc')
            ->value('appointment_number');

        if ($lastToday && preg_match('/^APT-\d{8}-(\d{4})$/', $lastToday, $matches)) {
            $nextNumber = (int) $matches[1] + 1;
        } else {
            $nextNumber = 1;
        }

        return $prefix . str_pad($nextNumber, 4, '0', STR_PAD_LEFT);
    }

    public function endTime(): ?\Carbon\Carbon
    {
        if (!$this->date || !$this->time || !$this->duration) {
            return null;
        }

        $dateStr = is_string($this->date) ? $this->date : $this->date->format('Y-m-d');

        return \Carbon\Carbon::parse($dateStr . ' ' . substr($this->time, 0, 5))->addMinutes($this->duration);
    }

    public function isOverlapping(): bool
    {
        if (!$this->doctor_id || !$this->date || !$this->time || !$this->duration) {
            return false;
        }

        $myStart = $this->time;
        $myEnd = $this->endTime()->format('H:i:s');

        $driver = DB::getDriverName();
        $endTimeExpr = $driver === 'mysql'
            ? 'TIME(DATE_ADD(time, INTERVAL duration MINUTE))'
            : "time(time, '+' || duration || ' minutes')";

        $myStartParam = strlen($myStart) === 5 ? $myStart . ':00' : $myStart;

        return static::where('doctor_id', $this->doctor_id)
            ->where('date', $this->date)
            ->where('id', '!=', $this->id)
            ->whereNotIn('status', [AppointmentStatus::Cancelled->value])
            ->whereRaw("time < ? AND {$endTimeExpr} > ?", [$myEnd, $myStartParam])
            ->exists();
    }

    public function isCancelled(): bool
    {
        return $this->status === AppointmentStatus::Cancelled;
    }

    public function isScheduled(): bool
    {
        return $this->status === AppointmentStatus::Scheduled;
    }

    public function isConfirmed(): bool
    {
        return $this->status === AppointmentStatus::Confirmed;
    }

    public function isCheckedIn(): bool
    {
        return $this->status === AppointmentStatus::CheckedIn;
    }

    public function isCompleted(): bool
    {
        return $this->status === AppointmentStatus::Completed;
    }
}
