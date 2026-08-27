<?php

namespace App\Models;

use App\Enums\DayOfWeek;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Doctor extends Model
{

    use HasFactory;
    protected $fillable = [
        'name',
        'slug',
        'gender',
        'profile_image',
        'title',
        'role',
        'qualifications',
        'experience_years',
        'board_certified',
        'primary_department',
        'short_description',
        'biography',
        'location',
        'department_id',
        'is_available',
        'availability_note',
        'available_days',
        'start_time',
        'end_time',
        'break_start',
        'break_end',
        'is_featured',
        'user_id',
        'is_active',
    ];

    protected $casts = [
        'available_days' => 'array',
        'is_available' => 'boolean',
        'board_certified' => 'boolean',
        'is_featured' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function dayLabels(): array
    {
        return collect($this->available_days)
            ->map(fn($d) => DayOfWeek::from($d)->label())
            ->toArray();
    }

    public function hasBreak(): bool
    {
        return !empty($this->break_start) && !empty($this->break_end);
    }

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function appointments()
    {
        return $this->hasMany(Appointment::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function invoices()
    {
        return $this->hasMany(Invoice::class);
    }

    public function investigations()
    {
        return $this->hasMany(Investigation::class);
    }

    public function location()
    {
        return $this->belongsTo(Location::class);
    }

    public function unavailableDates()
    {
        return $this->hasMany(DoctorUnavailableDate::class);
    }

    public function hasUnavailableDate($date): bool
    {
        $date = $date instanceof \Carbon\Carbon ? $date->toDateString() : \Carbon\Carbon::parse($date)->toDateString();

        return DoctorUnavailableDate::where('doctor_id', $this->id)
            ->whereDate('date', $date)
            ->exists();
    }
}
