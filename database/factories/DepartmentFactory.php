<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class DepartmentFactory extends Factory
{
    public function definition(): array
    {
        $name = $this->faker->unique()->randomElement([
            'Cardiology',
            'Neurology',
            'Orthopedics',
            'Pediatrics',
            'Dermatology',
            'Gynecology',
            'Radiology',
            'ENT',
        ]);

        return [
            'name' => $name,
            'slug' => Str::slug($name),
        ];
    }
}
