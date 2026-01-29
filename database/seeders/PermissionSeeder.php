<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

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

            'doctor.view',
            'doctor.create',
            'doctor.edit',
            'doctor.delete',

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

            // Role
            'role.view',
            'role.create',
            'role.edit',
            'role.delete',

            //Department
            'department.view',
            'department.create',
            'department.edit',
            'department.delete',

            //Service
            'service.view',
            'service.create',
            'service.edit',
            'service.delete',

            //Location
            'location.view',
            'location.create',
            'location.edit',
            'location.delete',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate([
                'name' => $permission,
                'guard_name' => 'web',
            ]);
        }

        $role = Role::firstOrCreate([
            'name' => 'super-admin',
            'guard_name' => 'web'
        ]);

        $role->syncPermissions(Permission::all());
    }
}
