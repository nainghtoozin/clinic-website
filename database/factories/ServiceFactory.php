<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class ServiceFactory extends Factory
{
    public function definition(): array
    {
        $title = $this->faker->unique()->words(3, true);

        return [
            'service_image' => 'services/default.png',
            'title' => ucfirst($title),
            'slug' => Str::slug($title),
            'category' => $this->faker->randomElement([
                'General',
                'Dental',
                'Eye Care',
                'Cardiology',
                'Laboratory'
            ]),
            'description' => $this->faker->paragraph(3),
            'icon' => $this->faker->randomElement([
                'fa-heartbeat',
                'fa-tooth',
                'fa-eye',
                'fa-user-md',
                'fa-stethoscope'
            ]),
            'features' => [
                $this->faker->sentence(),
                $this->faker->sentence(),
                $this->faker->sentence(),
            ],
            'price' => $this->faker->randomFloat(2, 20, 300),
            'status' => true,
        ];
    }
}
