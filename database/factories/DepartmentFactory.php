<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class DepartmentFactory extends Factory
{
    public function definition(): array
    {
        $name = $this->faker->randomElement([
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
            'name'        => $name,
            'slug'        => Str::slug($name) . '-' . $this->faker->unique()->numberBetween(1, 9999),
            'category'    => $this->faker->randomElement([
                'Heart Care',
                'Brain & Nerves',
                'Bone & Joint',
                'Children Health',
                'Skin Care',
                'Women Health',
                'Medical Imaging',
                'Ear Nose Throat',
            ]),
            'description' => $this->faker->sentence(20),
            'icon'        => $this->faker->randomElement([
                'fas fa-heartbeat',
                'fas fa-brain',
                'fas fa-bone',
                'fas fa-baby',
                'fas fa-allergies',
                'fas fa-female',
                'fas fa-x-ray',
                'fas fa-head-side-mask',
            ]),
            'image'       => 'departments/' . $this->faker->numberBetween(1, 6) . '.webp',
            'is_active'   => $this->faker->boolean(90),
            'sort_order'  => $this->faker->numberBetween(1, 20),
        ];
    }
}
