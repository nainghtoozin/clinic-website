<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Step 1: Create patients from existing appointment data
        // Group by email to avoid duplicate patients (use phone as fallback for unique identification)
        $appointments = DB::table('appointments')
            ->select('name', 'email', 'phone')
            ->distinct()
            ->get();

        $patientMap = []; // email/phone => patient_id
        $patientNumber = 1;

        foreach ($appointments as $appointment) {
            // Create a unique key based on email (primary) or phone (fallback)
            $key = $appointment->email ?: $appointment->phone;

            if (!$key || isset($patientMap[$key])) {
                continue;
            }

            // Create the patient
            $patientId = DB::table('patients')->insertGetId([
                'patient_number' => 'P-' . str_pad($patientNumber, 6, '0', STR_PAD_LEFT),
                'name' => $appointment->name,
                'email' => $appointment->email,
                'phone' => $appointment->phone,
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $patientMap[$key] = $patientId;
            $patientNumber++;
        }

        // Step 2: Link appointments to patients and generate appointment numbers
        $appointmentNumber = 1;
        $allAppointments = DB::table('appointments')->orderBy('id')->get();

        foreach ($allAppointments as $appointment) {
            // Find the patient based on email or phone
            $key = $appointment->email ?: $appointment->phone;
            $patientId = $patientMap[$key] ?? null;

            DB::table('appointments')
                ->where('id', $appointment->id)
                ->update([
                    'patient_id' => $patientId,
                    'appointment_number' => 'APT-' . str_pad($appointmentNumber, 6, '0', STR_PAD_LEFT),
                    'updated_at' => now(),
                ]);

            $appointmentNumber++;
        }
    }

    public function down(): void
    {
        // Clear the new columns (patient_id will be set to null due to nullable constraint)
        DB::table('appointments')->update([
            'patient_id' => null,
            'appointment_number' => null,
        ]);

        // Note: We do NOT delete the created patients here.
        // The patients table may contain data entered independently of appointments.
        // Safe rollback only clears the appointment linkage.
    }
};
