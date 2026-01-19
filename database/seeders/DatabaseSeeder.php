<?php

namespace Database\Seeders;

use App\Models\Department;
use App\Models\Doctor;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Ensure at least one user exists
        User::factory()->count(3)->create();

        // Departments (5)
        Department::factory()->count(5)->create();

        // Doctors (30)
        Doctor::factory()->count(30)->create();

        $this->call([
            PermissionSeeder::class,
        ]);
    }
}
