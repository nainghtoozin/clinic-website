<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;

class PermissionSeeder extends Seeder
{
    public function run()
    {
        $permissions = [
            // Patient
            'patient.view',
            'patient.create',
            'patient.edit',
            'patient.delete',

            'doctors.view',
            'doctors.create',
            'doctors.edit',
            'doctors.delete',

            // Appointment
            'appointment.view',
            'appointment.create',
            'appointment.edit',
            'appointment.delete',

            // Invoice
            'invoice.view',
            'invoice.create',
            'invoice.refund',

            // User
            'user.view',
            'user.create',
            'user.edit',
            'user.delete',

            'role.view',
            'role.create',
            'role.edit',
            'role.delete'
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate([
                'name' => $permission
            ]);
        }
    }
}
