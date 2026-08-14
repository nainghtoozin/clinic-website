<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Service extends Model
{
    protected $fillable = [
        'service_image',
        'title',
        'slug',
        'category',
        'description',
        'icon',
        'features',
        'price',
        'status',
    ];

    protected $casts = [
        'features' => 'array',
        'price' => 'decimal:2',
        'status' => 'boolean',
    ];

    public function imageUrl()
    {
        return $this->service_image
            ? asset('storage/' . $this->service_image)
            : asset('images/service-default.png');
    }
}
