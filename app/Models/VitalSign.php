<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VitalSign extends Model
{
    protected $fillable = [
        'consultation_id',
        'blood_pressure',
        'temperature',
        'pulse',
        'respiratory_rate',
        'weight',
        'height',
        'oxygen_saturation',
        'recorded_at',
    ];

    protected $casts = [
        'temperature' => 'decimal:1',
        'pulse' => 'integer',
        'respiratory_rate' => 'integer',
        'weight' => 'decimal:2',
        'height' => 'decimal:2',
        'oxygen_saturation' => 'decimal:2',
        'recorded_at' => 'datetime',
    ];

    public function consultation()
    {
        return $this->belongsTo(Consultation::class);
    }
}
