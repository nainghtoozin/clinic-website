<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Service extends Model
{
    protected $fillable = [
        'service_image',
        'user_id',
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
        'status' => 'boolean',
    ];

    public function imageUrl()
    {
        return $this->service_image
            ? asset('storage/' . $this->service_image)
            : asset('images/service-default.png');
    }
}
