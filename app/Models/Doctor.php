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
        'location',
        'department_id',
        'is_available',
        'availability_note',
        'is_featured',
        'user_id',
    ];

    protected $casts = [
        'is_available' => 'boolean',
        'board_certified' => 'boolean',
        'is_featured' => 'boolean',
    ];

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

    public function location()
    {
        return $this->belongsTo(Location::class);
    }
}
