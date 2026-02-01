<?php

namespace Database\Seeders;

use App\Models\Department;
use App\Models\Doctor;
use App\Models\Location;
use App\Models\User;
use App\Models\Service;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */

    public function run(): void
    {
        // Roles & Permissions
        $this->call(PermissionSeeder::class);

        // Users
        $users = User::factory()->count(3)->create();

        $superAdminRole = Role::where('name', 'super-admin')->first();

        // ပထမ user ကို super-admin ပေးမယ်
        $users->first()->assignRole($superAdminRole);

        // Other seeders
        Department::factory()->count(5)->create();
        Location::factory()->count(5)->create();
        Doctor::factory()->count(30)->create();

        $this->call(ServiceSeeder::class);
    }
}
