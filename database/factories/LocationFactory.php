<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class LocationFactory extends Factory
{
    public function definition(): array
    {
        $name = $this->faker->unique()->randomElement([
            'Downtown Clinic',
            'City Center',
            'North Branch',
            'South Branch',
            'East Wing',
            'West Wing',
            'Main Hospital',
        ]);

        return [
            'name'        => $name,
            'slug'        => Str::slug($name),
            'address' => $this->faker->sentence(15),
            'is_active'   => $this->faker->boolean(90), // mostly active
        ];
    }
}
