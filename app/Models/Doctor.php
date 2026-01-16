<?php

namespace App\Models;

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
        'is_available',
        'availability_note',
        'is_featured'
    ];

    protected $casts = [
        'board_certified' => 'boolean',
        'is_available' => 'boolean',
        'is_featured' => 'boolean',
    ];

    public function departments()
    {
        return $this->belongsToMany(Department::class);
    }

    public function locations()
    {
        return $this->belongsToMany(Location::class);
    }

    public function schedules()
    {
        return $this->hasMany(DoctorSchedule::class);
    }

    public function reviews()
    {
        return $this->hasMany(DoctorReview::class);
    }

    public function highlights()
    {
        return $this->hasMany(DoctorHighlight::class);
    }
}
