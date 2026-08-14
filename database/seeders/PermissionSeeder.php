<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class PermissionSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = [
            // Patient
            'patient.view',
            'patient.create',
            'patient.edit',
            'patient.delete',

            // Doctor
            'doctor.view',
            'doctor.create',
            'doctor.edit',
            'doctor.delete',

            // Appointment
            'appointment.view',
            'appointment.create',
            'appointment.edit',
            'appointment.cancel',
            'appointment.delete',

            // Queue
            'queue.view',
            'queue.checkin',
            'queue.call',
            'queue.consult',
            'queue.cancel',

            // Consultation
            'consultation.view',
            'consultation.create',
            'consultation.edit',
            'consultation.complete',

            // Prescription
            'prescription.view',
            'prescription.create',
            'prescription.edit',
            'prescription.delete',

            // Medicine
            'medicine.view',
            'medicine.create',
            'medicine.edit',
            'medicine.delete',

            // Invoice
            'invoice.view',
            'invoice.create',
            'invoice.edit',
            'invoice.cancel',

            // Payment
            'payment.view',
            'payment.create',
            'payment.cancel',

            // Staff
            'staff.view',
            'staff.create',
            'staff.edit',
            'staff.delete',

            // Role
            'role.view',
            'role.create',
            'role.edit',
            'role.delete',

            // Department
            'department.view',
            'department.create',
            'department.edit',
            'department.delete',

            // Service
            'service.view',
            'service.create',
            'service.edit',
            'service.delete',

            // Location
            'location.view',
            'location.create',
            'location.edit',
            'location.delete',

            // Settings
            'settings.view',
            'settings.edit',

            // Inventory
            'inventory.view',
            'inventory.opening_stock',
            'inventory.stock_in',
            'inventory.stock_out',
            'inventory.adjust',
            'inventory.dispense',

            // Dashboard
            'dashboard.view',

            // Reports
            'report.patient',
            'report.appointment',
            'report.consultation',
            'report.financial',
            'report.inventory',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate([
                'name' => $permission,
                'guard_name' => 'web',
            ]);
        }

        // Super Admin — full access
        $superAdmin = Role::firstOrCreate(['name' => 'super-admin', 'guard_name' => 'web']);
        $superAdmin->syncPermissions(Permission::all());

        // Admin — everything except role management
        $admin = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        $adminPermissions = Permission::whereNotIn('name', ['role.view', 'role.create', 'role.edit', 'role.delete'])->get();
        $admin->syncPermissions($adminPermissions);

        // Doctor — clinical + patient access
        $doctor = Role::firstOrCreate(['name' => 'doctor', 'guard_name' => 'web']);
        $doctor->syncPermissions([
            'patient.view',
            'patient.create',
            'patient.edit',
            'appointment.view',
            'appointment.create',
            'appointment.edit',
            'appointment.cancel',
            'appointment.delete',
            'queue.view',
            'queue.consult',
            'consultation.view',
            'consultation.create',
            'consultation.edit',
            'consultation.complete',
            'prescription.view',
            'prescription.create',
            'prescription.edit',
            'prescription.delete',
            'medicine.view',
            'medicine.create',
            'medicine.edit',
            'invoice.view',
            'dashboard.view',
            'report.patient',
            'report.appointment',
            'report.consultation',
            'report.inventory',
        ]);

        // Receptionist — patient registration, appointments, check-in, billing
        $receptionist = Role::firstOrCreate(['name' => 'receptionist', 'guard_name' => 'web']);
        $receptionist->syncPermissions([
            'patient.view',
            'patient.create',
            'patient.edit',
            'patient.delete',
            'doctor.view',
            'appointment.view',
            'appointment.create',
            'appointment.edit',
            'appointment.cancel',
            'appointment.delete',
            'queue.view',
            'queue.checkin',
            'queue.call',
            'queue.cancel',
            'invoice.view',
            'invoice.create',
            'invoice.edit',
            'invoice.cancel',
            'payment.view',
            'payment.create',
            'medicine.view',
            'dashboard.view',
            'report.patient',
            'report.appointment',
        ]);

        // Nurse — patient, queue, consultation-related
        $nurse = Role::firstOrCreate(['name' => 'nurse', 'guard_name' => 'web']);
        $nurse->syncPermissions([
            'patient.view',
            'patient.create',
            'patient.edit',
            'doctor.view',
            'appointment.view',
            'queue.view',
            'queue.checkin',
            'queue.call',
            'queue.cancel',
            'consultation.view',
            'consultation.create',
            'consultation.edit',
            'consultation.complete',
            'medicine.view',
            'prescription.view',
            'dashboard.view',
            'report.patient',
            'report.appointment',
            'report.consultation',
        ]);
    }
}
