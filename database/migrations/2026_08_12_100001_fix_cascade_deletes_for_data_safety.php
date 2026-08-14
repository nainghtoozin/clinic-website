<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::getConnection()->getDriverName() !== 'mysql') {
            return;
        }

        $db = DB::connection('mysql');

        // doctors.department_id: CASCADE -> RESTRICT
        $db->statement('ALTER TABLE `doctors` DROP FOREIGN KEY `doctors_department_id_foreign`');
        $db->statement('ALTER TABLE `doctors` ADD CONSTRAINT `doctors_department_id_foreign` FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`) ON DELETE RESTRICT');

        // doctors.user_id: CASCADE -> RESTRICT
        $db->statement('ALTER TABLE `doctors` DROP FOREIGN KEY `doctors_user_id_foreign`');
        $db->statement('ALTER TABLE `doctors` ADD CONSTRAINT `doctors_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE RESTRICT');

        // doctors.location_id: CASCADE -> SET NULL
        $db->statement('ALTER TABLE `doctors` DROP FOREIGN KEY `doctors_location_id_foreign`');
        $db->statement('ALTER TABLE `doctors` ADD CONSTRAINT `doctors_location_id_foreign` FOREIGN KEY (`location_id`) REFERENCES `locations` (`id`) ON DELETE SET NULL');

        // appointments.department_id: CASCADE -> RESTRICT
        $db->statement('ALTER TABLE `appointments` DROP FOREIGN KEY `appointments_department_id_foreign`');
        $db->statement('ALTER TABLE `appointments` ADD CONSTRAINT `appointments_department_id_foreign` FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`) ON DELETE RESTRICT');

        // appointments.doctor_id: CASCADE -> RESTRICT
        $db->statement('ALTER TABLE `appointments` DROP FOREIGN KEY `appointments_doctor_id_foreign`');
        $db->statement('ALTER TABLE `appointments` ADD CONSTRAINT `appointments_doctor_id_foreign` FOREIGN KEY (`doctor_id`) REFERENCES `doctors` (`id`) ON DELETE RESTRICT');

        // queue_tickets.patient_id: CASCADE -> RESTRICT
        $db->statement('ALTER TABLE `queue_tickets` DROP FOREIGN KEY `queue_tickets_patient_id_foreign`');
        $db->statement('ALTER TABLE `queue_tickets` ADD CONSTRAINT `queue_tickets_patient_id_foreign` FOREIGN KEY (`patient_id`) REFERENCES `patients` (`id`) ON DELETE RESTRICT');

        // queue_tickets.doctor_id: CASCADE -> RESTRICT
        $db->statement('ALTER TABLE `queue_tickets` DROP FOREIGN KEY `queue_tickets_doctor_id_foreign`');
        $db->statement('ALTER TABLE `queue_tickets` ADD CONSTRAINT `queue_tickets_doctor_id_foreign` FOREIGN KEY (`doctor_id`) REFERENCES `doctors` (`id`) ON DELETE RESTRICT');

        // consultations.patient_id: CASCADE -> RESTRICT
        $db->statement('ALTER TABLE `consultations` DROP FOREIGN KEY `consultations_patient_id_foreign`');
        $db->statement('ALTER TABLE `consultations` ADD CONSTRAINT `consultations_patient_id_foreign` FOREIGN KEY (`patient_id`) REFERENCES `patients` (`id`) ON DELETE RESTRICT');

        // consultations.doctor_id: CASCADE -> RESTRICT
        $db->statement('ALTER TABLE `consultations` DROP FOREIGN KEY `consultations_doctor_id_foreign`');
        $db->statement('ALTER TABLE `consultations` ADD CONSTRAINT `consultations_doctor_id_foreign` FOREIGN KEY (`doctor_id`) REFERENCES `doctors` (`id`) ON DELETE RESTRICT');

        // prescriptions.patient_id: CASCADE -> RESTRICT
        $db->statement('ALTER TABLE `prescriptions` DROP FOREIGN KEY `prescriptions_patient_id_foreign`');
        $db->statement('ALTER TABLE `prescriptions` ADD CONSTRAINT `prescriptions_patient_id_foreign` FOREIGN KEY (`patient_id`) REFERENCES `patients` (`id`) ON DELETE RESTRICT');

        // prescriptions.doctor_id: CASCADE -> RESTRICT
        $db->statement('ALTER TABLE `prescriptions` DROP FOREIGN KEY `prescriptions_doctor_id_foreign`');
        $db->statement('ALTER TABLE `prescriptions` ADD CONSTRAINT `prescriptions_doctor_id_foreign` FOREIGN KEY (`doctor_id`) REFERENCES `doctors` (`id`) ON DELETE RESTRICT');

        // prescription_items.medicine_id: CASCADE -> RESTRICT
        $db->statement('ALTER TABLE `prescription_items` DROP FOREIGN KEY `prescription_items_medicine_id_foreign`');
        $db->statement('ALTER TABLE `prescription_items` ADD CONSTRAINT `prescription_items_medicine_id_foreign` FOREIGN KEY (`medicine_id`) REFERENCES `medicines` (`id`) ON DELETE RESTRICT');

        // invoices.patient_id: CASCADE -> RESTRICT
        $db->statement('ALTER TABLE `invoices` DROP FOREIGN KEY `invoices_patient_id_foreign`');
        $db->statement('ALTER TABLE `invoices` ADD CONSTRAINT `invoices_patient_id_foreign` FOREIGN KEY (`patient_id`) REFERENCES `patients` (`id`) ON DELETE RESTRICT');

        // payments.invoice_id: CASCADE -> RESTRICT
        $db->statement('ALTER TABLE `payments` DROP FOREIGN KEY `payments_invoice_id_foreign`');
        $db->statement('ALTER TABLE `payments` ADD CONSTRAINT `payments_invoice_id_foreign` FOREIGN KEY (`invoice_id`) REFERENCES `invoices` (`id`) ON DELETE RESTRICT');

        // stock_movements.medicine_id: CASCADE -> RESTRICT
        $db->statement('ALTER TABLE `stock_movements` DROP FOREIGN KEY `stock_movements_medicine_id_foreign`');
        $db->statement('ALTER TABLE `stock_movements` ADD CONSTRAINT `stock_movements_medicine_id_foreign` FOREIGN KEY (`medicine_id`) REFERENCES `medicines` (`id`) ON DELETE RESTRICT');
    }

    public function down(): void
    {
        if (Schema::getConnection()->getDriverName() !== 'mysql') {
            return;
        }

        $db = DB::connection('mysql');

        // Revert all constraints back to CASCADE
        $db->statement('ALTER TABLE `doctors` DROP FOREIGN KEY `doctors_department_id_foreign`');
        $db->statement('ALTER TABLE `doctors` ADD CONSTRAINT `doctors_department_id_foreign` FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`) ON DELETE CASCADE');

        $db->statement('ALTER TABLE `doctors` DROP FOREIGN KEY `doctors_user_id_foreign`');
        $db->statement('ALTER TABLE `doctors` ADD CONSTRAINT `doctors_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE');

        $db->statement('ALTER TABLE `doctors` DROP FOREIGN KEY `doctors_location_id_foreign`');
        $db->statement('ALTER TABLE `doctors` ADD CONSTRAINT `doctors_location_id_foreign` FOREIGN KEY (`location_id`) REFERENCES `locations` (`id`) ON DELETE CASCADE');

        $db->statement('ALTER TABLE `appointments` DROP FOREIGN KEY `appointments_department_id_foreign`');
        $db->statement('ALTER TABLE `appointments` ADD CONSTRAINT `appointments_department_id_foreign` FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`) ON DELETE CASCADE');

        $db->statement('ALTER TABLE `appointments` DROP FOREIGN KEY `appointments_doctor_id_foreign`');
        $db->statement('ALTER TABLE `appointments` ADD CONSTRAINT `appointments_doctor_id_foreign` FOREIGN KEY (`doctor_id`) REFERENCES `doctors` (`id`) ON DELETE CASCADE');

        $db->statement('ALTER TABLE `queue_tickets` DROP FOREIGN KEY `queue_tickets_patient_id_foreign`');
        $db->statement('ALTER TABLE `queue_tickets` ADD CONSTRAINT `queue_tickets_patient_id_foreign` FOREIGN KEY (`patient_id`) REFERENCES `patients` (`id`) ON DELETE CASCADE');

        $db->statement('ALTER TABLE `queue_tickets` DROP FOREIGN KEY `queue_tickets_doctor_id_foreign`');
        $db->statement('ALTER TABLE `queue_tickets` ADD CONSTRAINT `queue_tickets_doctor_id_foreign` FOREIGN KEY (`doctor_id`) REFERENCES `doctors` (`id`) ON DELETE CASCADE');

        $db->statement('ALTER TABLE `consultations` DROP FOREIGN KEY `consultations_patient_id_foreign`');
        $db->statement('ALTER TABLE `consultations` ADD CONSTRAINT `consultations_patient_id_foreign` FOREIGN KEY (`patient_id`) REFERENCES `patients` (`id`) ON DELETE CASCADE');

        $db->statement('ALTER TABLE `consultations` DROP FOREIGN KEY `consultations_doctor_id_foreign`');
        $db->statement('ALTER TABLE `consultations` ADD CONSTRAINT `consultations_doctor_id_foreign` FOREIGN KEY (`doctor_id`) REFERENCES `doctors` (`id`) ON DELETE CASCADE');

        $db->statement('ALTER TABLE `prescriptions` DROP FOREIGN KEY `prescriptions_patient_id_foreign`');
        $db->statement('ALTER TABLE `prescriptions` ADD CONSTRAINT `prescriptions_patient_id_foreign` FOREIGN KEY (`patient_id`) REFERENCES `patients` (`id`) ON DELETE CASCADE');

        $db->statement('ALTER TABLE `prescriptions` DROP FOREIGN KEY `prescriptions_doctor_id_foreign`');
        $db->statement('ALTER TABLE `prescriptions` ADD CONSTRAINT `prescriptions_doctor_id_foreign` FOREIGN KEY (`doctor_id`) REFERENCES `doctors` (`id`) ON DELETE CASCADE');

        $db->statement('ALTER TABLE `prescription_items` DROP FOREIGN KEY `prescription_items_medicine_id_foreign`');
        $db->statement('ALTER TABLE `prescription_items` ADD CONSTRAINT `prescription_items_medicine_id_foreign` FOREIGN KEY (`medicine_id`) REFERENCES `medicines` (`id`) ON DELETE CASCADE');

        $db->statement('ALTER TABLE `invoices` DROP FOREIGN KEY `invoices_patient_id_foreign`');
        $db->statement('ALTER TABLE `invoices` ADD CONSTRAINT `invoices_patient_id_foreign` FOREIGN KEY (`patient_id`) REFERENCES `patients` (`id`) ON DELETE CASCADE');

        $db->statement('ALTER TABLE `payments` DROP FOREIGN KEY `payments_invoice_id_foreign`');
        $db->statement('ALTER TABLE `payments` ADD CONSTRAINT `payments_invoice_id_foreign` FOREIGN KEY (`invoice_id`) REFERENCES `invoices` (`id`) ON DELETE CASCADE');

        $db->statement('ALTER TABLE `stock_movements` DROP FOREIGN KEY `stock_movements_medicine_id_foreign`');
        $db->statement('ALTER TABLE `stock_movements` ADD CONSTRAINT `stock_movements_medicine_id_foreign` FOREIGN KEY (`medicine_id`) REFERENCES `medicines` (`id`) ON DELETE CASCADE');
    }
};
