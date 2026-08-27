<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LabTest extends Model
{
    protected $fillable = [
        'name',
        'code',
        'category',
        'description',
        'sample_type',
        'reference_range',
        'unit',
        'price',
        'is_active',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function investigations()
    {
        return $this->hasMany(Investigation::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeSearch($query, ?string $search)
    {
        if (!$search) return $query;

        return $query->where(function ($q) use ($search) {
            $q->where('name', 'like', "%{$search}%")
              ->orWhere('code', 'like', "%{$search}%")
              ->orWhere('category', 'like', "%{$search}%");
        });
    }
}
