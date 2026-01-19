<?php

namespace Database\Factories;

use App\Models\Doctor;
use App\Models\Department;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class DoctorFactory extends Factory
{
    protected $model = Doctor::class;

    public function definition(): array
    {
        $name = 'Dr. ' . $this->faker->name();

        return [
            // Basic
            'name' => $name,
            'slug' => Str::slug($name),
            'gender' => $this->faker->randomElement(['male', 'female', 'other']),
            'profile_image' => null,

            // Professional
            'title' => $this->faker->randomElement([
                'Cardiologist',
                'Medical Specialist',
                'Consultant Physician',
                'Senior Consultant',
            ]),
            'role' => $this->faker->randomElement([
                'Senior Consultant',
                'Consultant',
                'Resident Doctor',
            ]),
            'qualifications' => $this->faker->randomElement([
                'MBBS',
                'MBBS, MD',
                'MBBS, MS',
                'MBBS, FCPS',
            ]),
            'experience_years' => $this->faker->numberBetween(1, 35),
            'board_certified' => $this->faker->boolean(70),

            // Department
            'department_id' => Department::inRandomOrder()->first()?->id,
            'primary_department' => null,

            // Description
            'short_description' => $this->faker->sentence(10),
            'biography' => $this->faker->paragraphs(3, true),
            'location' => $this->faker->city(),

            // Status
            'is_available' => $this->faker->boolean(80),
            'availability_note' => $this->faker->optional()->sentence(),
            'is_featured' => $this->faker->boolean(20),

            // User
            'user_id' => User::inRandomOrder()->first()->id ?? User::factory(),
        ];
    }
}
