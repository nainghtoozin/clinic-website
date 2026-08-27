<?php

namespace Database\Seeders;

use App\Enums\AppointmentStatus;
use App\Enums\DayOfWeek;
use App\Models\Appointment;
use App\Models\AppointmentStatusHistory;
use App\Models\Communication;
use App\Models\Consultation;
use App\Models\Department;
use App\Models\Doctor;
use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Models\Investigation;
use App\Models\InventoryBatch;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\LabTest;
use App\Models\Medicine;
use App\Models\Notification;
use App\Models\Patient;
use App\Models\Payment;
use App\Models\Prescription;
use App\Models\PrescriptionItem;
use App\Models\QueueTicket;
use App\Models\Setting;
use App\Models\StockMovement;
use App\Models\User;
use App\Models\VitalSign;
use App\Services\ClinicSettingsService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class DemoDataSeeder extends Seeder
{
    protected User $superAdmin;
    protected User $admin;
    protected array $doctors = [];
    protected array $patients = [];
    protected array $departments = [];
    protected array $appointments = [];
    protected array $consultations = [];
    protected array $medicines = [];
    protected array $invoices = [];

    /**
     * Run demo data seeding.
     * Safe to run repeatedly — uses firstOrCreate/updateOrCreate throughout.
     * Run explicitly: php artisan db:seed --class=DemoDataSeeder
     */
    public function run(): void
    {
        $this->seedRoles();
        $this->seedUsers();
        $this->seedDepartments();
        $this->seedDoctors();
        $this->seedPatients();
        $this->seedMedicines();
        $this->seedLabTests();
        $this->seedAppointments();
        $this->seedQueueTickets();
        $this->seedConsultations();
        $this->seedInvestigations();
        $this->seedPrescriptions();
        $this->seedInvoices();
        $this->seedExpenses();
        $this->seedCommunications();
        $this->seedNotifications();
        $this->seedSettings();

        $this->command?->info('Demo data seeded successfully.');
    }

    protected function seedRoles(): void
    {
        $this->call(PermissionSeeder::class);
    }

    // ──────────────────────────────────────────────
    // 2. DEMO USERS
    // ──────────────────────────────────────────────
    protected function seedUsers(): void
    {
        $demoPassword = Hash::make('password');

        $this->superAdmin = User::updateOrCreate(
            ['email' => 'superadmin@clinic-demo.test'],
            [
                'name' => 'Super Admin',
                'password' => $demoPassword,
                'email_verified_at' => now(),
                'is_active' => true,
                'phone' => '09-900000001',
                'position' => 'System Administrator',
            ]
        );
        $this->superAdmin->syncRoles('super-admin');

        $this->admin = User::updateOrCreate(
            ['email' => 'admin@clinic-demo.test'],
            [
                'name' => 'Admin User',
                'password' => $demoPassword,
                'email_verified_at' => now(),
                'is_active' => true,
                'phone' => '09-900000002',
                'position' => 'Clinic Administrator',
            ]
        );
        $this->admin->syncRoles('admin');

        $doctorUser = User::updateOrCreate(
            ['email' => 'doctor@clinic-demo.test'],
            [
                'name' => 'Dr. Demo Doctor',
                'password' => $demoPassword,
                'email_verified_at' => now(),
                'is_active' => true,
                'phone' => '09-900000003',
                'position' => 'Senior Consultant',
            ]
        );
        $doctorUser->syncRoles('doctor');

        $nurseUser = User::updateOrCreate(
            ['email' => 'nurse@clinic-demo.test'],
            [
                'name' => 'Nurse Demo',
                'password' => $demoPassword,
                'email_verified_at' => now(),
                'is_active' => true,
                'phone' => '09-900000004',
                'position' => 'Staff Nurse',
            ]
        );
        $nurseUser->syncRoles('nurse');

        $receptionUser = User::updateOrCreate(
            ['email' => 'reception@clinic-demo.test'],
            [
                'name' => 'Reception Demo',
                'password' => $demoPassword,
                'email_verified_at' => now(),
                'is_active' => true,
                'phone' => '09-900000005',
                'position' => 'Receptionist',
            ]
        );
        $receptionUser->syncRoles('receptionist');
    }

    // ──────────────────────────────────────────────
    // 3. DEPARTMENTS
    // ──────────────────────────────────────────────
    protected function seedDepartments(): void
    {
        $departments = [
            ['name' => 'General Medicine', 'slug' => 'general-medicine-demo', 'category' => 'Primary Care', 'description' => 'Comprehensive primary care and general health services', 'icon' => 'fas fa-stethoscope', 'sort_order' => 1],
            ['name' => 'Pediatrics', 'slug' => 'pediatrics-demo', 'category' => 'Children Health', 'description' => 'Specialized healthcare for infants, children, and adolescents', 'icon' => 'fas fa-baby', 'sort_order' => 2],
            ['name' => 'Cardiology', 'slug' => 'cardiology-demo', 'category' => 'Heart Care', 'description' => 'Diagnosis and treatment of heart and cardiovascular conditions', 'icon' => 'fas fa-heartbeat', 'sort_order' => 3],
            ['name' => 'Dermatology', 'slug' => 'dermatology-demo', 'category' => 'Skin Care', 'description' => 'Treatment of skin, hair, and nail conditions', 'icon' => 'fas fa-allergies', 'sort_order' => 4],
            ['name' => 'Laboratory', 'slug' => 'laboratory-demo', 'category' => 'Diagnostics', 'description' => 'Clinical laboratory testing and diagnostics', 'icon' => 'fas fa-flask', 'sort_order' => 5],
            ['name' => 'Pharmacy', 'slug' => 'pharmacy-demo', 'category' => 'Pharmaceutical', 'description' => 'Medication dispensing and pharmaceutical care', 'icon' => 'fas fa-pills', 'sort_order' => 6],
        ];

        foreach ($departments as $dept) {
            $this->departments[$dept['slug']] = Department::updateOrCreate(
                ['slug' => $dept['slug']],
                [...$dept, 'is_active' => true]
            );
        }
    }

    // ──────────────────────────────────────────────
    // 4. DOCTORS
    // ──────────────────────────────────────────────
    protected function seedDoctors(): void
    {
        $doctorData = [
            ['name' => 'Dr. Aung Myo', 'title' => 'General Practitioner', 'role' => 'Consultant', 'qualifications' => 'MBBS, BMedSc', 'experience_years' => 10, 'department_slug' => 'general-medicine-demo', 'specialization' => 'Family Medicine', 'available_days' => [1,2,3,4,5,6], 'start_time' => '08:00', 'end_time' => '16:00', 'break_start' => '12:00', 'break_end' => '13:00', 'consultation_fee' => 15000],
            ['name' => 'Dr. Thin Thin Aye', 'title' => 'Pediatrician', 'role' => 'Senior Consultant', 'qualifications' => 'MBBS, DCH, MD(Pediatrics)', 'experience_years' => 15, 'department_slug' => 'pediatrics-demo', 'specialization' => 'Pediatrics', 'available_days' => [1,2,3,4,5], 'start_time' => '09:00', 'end_time' => '17:00', 'break_start' => '12:30', 'break_end' => '13:30', 'consultation_fee' => 20000],
            ['name' => 'Dr. Kyaw Zin Lin', 'title' => 'Cardiologist', 'role' => 'Senior Consultant', 'qualifications' => 'MBBS, MD(Cardiology), FACC', 'experience_years' => 20, 'department_slug' => 'cardiology-demo', 'specialization' => 'Cardiology', 'available_days' => [1,3,5], 'start_time' => '09:00', 'end_time' => '15:00', 'break_start' => '12:00', 'break_end' => '13:00', 'consultation_fee' => 30000],
            ['name' => 'Dr. Mar Mar Aye', 'title' => 'Dermatologist', 'role' => 'Consultant', 'qualifications' => 'MBBS, DDV, FACD', 'experience_years' => 12, 'department_slug' => 'dermatology-demo', 'specialization' => 'Dermatology', 'available_days' => [2,4], 'start_time' => '10:00', 'end_time' => '16:00', 'break_start' => '13:00', 'break_end' => '14:00', 'consultation_fee' => 25000],
            ['name' => 'Dr. Zaw Min Htut', 'title' => 'General Practitioner', 'role' => 'Resident Doctor', 'qualifications' => 'MBBS', 'experience_years' => 5, 'department_slug' => 'general-medicine-demo', 'specialization' => 'General Practice', 'available_days' => [1,2,3,4,5,6,7], 'start_time' => '07:00', 'end_time' => '19:00', 'break_start' => '12:00', 'break_end' => '13:00', 'consultation_fee' => 10000],
        ];

        foreach ($doctorData as $data) {
            $user = User::updateOrCreate(
                ['email' => strtolower(str_replace(' ', '.', $data['name'])) . '@clinic-demo.test'],
                [
                    'name' => $data['name'],
                    'password' => Hash::make('password'),
                    'email_verified_at' => now(),
                    'is_active' => true,
                    'phone' => '09-9000000' . array_search($data, $doctorData) . '0',
                    'position' => $data['title'],
                ]
            );
            $user->syncRoles('doctor');

            $slug = \Illuminate\Support\Str::slug($data['name']) . '-demo';
            $this->doctors[] = Doctor::updateOrCreate(
                ['slug' => $slug],
                [
                    'name' => $data['name'],
                    'gender' => str_contains($data['name'], 'Aye') || str_contains($data['name'], 'Mar') ? 'female' : 'male',
                    'title' => $data['title'],
                    'role' => $data['role'],
                    'qualifications' => $data['qualifications'],
                    'experience_years' => $data['experience_years'],
                    'board_certified' => $data['experience_years'] > 10,
                    'department_id' => $this->departments[$data['department_slug']]->id,
                    'primary_department' => $data['specialization'],
                    'short_description' => "Experienced {$data['specialization']} specialist with {$data['experience_years']} years of practice.",
                    'biography' => "Dr. is a dedicated medical professional specializing in {$data['specialization']}. With {$data['experience_years']} years of clinical experience, they are committed to providing excellent patient care.",
                    'location' => 'Yangon',
                    'is_available' => true,
                    'availability_note' => 'Available on scheduled days',
                    'available_days' => $data['available_days'],
                    'start_time' => $data['start_time'],
                    'end_time' => $data['end_time'],
                    'break_start' => $data['break_start'],
                    'break_end' => $data['break_end'],
                    'is_featured' => $data['experience_years'] > 15,
                    'user_id' => $user->id,
                    'is_active' => true,
                    'consultation_fee' => $data['consultation_fee'],
                ]
            );
        }
    }

    // ──────────────────────────────────────────────
    // 5. PATIENTS
    // ──────────────────────────────────────────────
    protected function seedPatients(): void
    {
        $patientData = [
            ['name' => 'Ma Htwe Htwe Kyaw', 'gender' => 'female', 'dob' => '-45 years', 'phone' => '09-123456789', 'blood' => 'A+', 'allergies' => 'Penicillin', 'history' => 'Hypertension, controlled', 'emergency_name' => 'U Kyaw Kyaw', 'emergency_phone' => '09-987654321', 'status' => 'active'],
            ['name' => 'U Ba Ba', 'gender' => 'male', 'dob' => '-62 years', 'phone' => '09-234567890', 'blood' => 'B+', 'allergies' => null, 'history' => 'Type 2 Diabetes Mellitus', 'emergency_name' => 'Daw Mya Mya', 'emergency_phone' => '09-876543210', 'status' => 'active'],
            ['name' => 'Daw Su Su Nyein', 'gender' => 'female', 'dob' => '-35 years', 'phone' => '09-345678901', 'blood' => 'O+', 'allergies' => 'Aspirin', 'history' => 'Mild asthma', 'emergency_name' => 'U Nyein Nyein', 'emergency_phone' => '09-765432109', 'status' => 'active'],
            ['name' => 'Ko Myo Myo Aung', 'gender' => 'male', 'dob' => '-28 years', 'phone' => '09-456789012', 'blood' => 'AB+', 'allergies' => null, 'history' => null, 'emergency_name' => 'Daw Aung Aung', 'emergency_phone' => '09-654321098', 'status' => 'active'],
            ['name' => 'Ma Khin Mar Win', 'gender' => 'female', 'dob' => '-50 years', 'phone' => '09-567890123', 'blood' => 'A-', 'allergies' => 'Sulfonamides', 'history' => 'Hypothyroidism', 'emergency_name' => 'U Win Win', 'emergency_phone' => '09-543210987', 'status' => 'active'],
            ['name' => 'U Hla Hla Aung', 'gender' => 'male', 'dob' => '-70 years', 'phone' => '09-678901234', 'blood' => 'B-', 'allergies' => null, 'history' => 'Coronary artery disease, chronic kidney disease stage 3', 'emergency_name' => 'Daw Hla Hla', 'emergency_phone' => '09-432109876', 'status' => 'active'],
            ['name' => 'Ma Thet Thet Htun', 'gender' => 'female', 'dob' => '-22 years', 'phone' => '09-789012345', 'blood' => 'O-', 'allergies' => null, 'history' => null, 'emergency_name' => 'U Htun Htun', 'emergency_phone' => '09-321098765', 'status' => 'active'],
            ['name' => 'Daw Zin Mar', 'gender' => 'female', 'dob' => '-55 years', 'phone' => '09-890123456', 'blood' => 'A+', 'allergies' => 'Codeine', 'history' => 'Hyperlipidemia, GERD', 'emergency_name' => 'U Zaw Zaw', 'emergency_phone' => '09-210987654', 'status' => 'active'],
            ['name' => 'U Kyaw Kyaw Soe', 'gender' => 'male', 'dob' => '-40 years', 'phone' => '09-901234567', 'blood' => 'B+', 'allergies' => null, 'history' => 'Gout', 'emergency_name' => 'Daw Sandar', 'emergency_phone' => '09-109876543', 'status' => 'active'],
            ['name' => 'Ma Yin Yin Aye', 'gender' => 'female', 'dob' => '-18 years', 'phone' => '09-012345678', 'blood' => 'AB-', 'allergies' => null, 'history' => 'Iron deficiency anemia', 'emergency_name' => 'U Aye Aye', 'emergency_phone' => '09-098765432', 'status' => 'active'],
            ['name' => 'U Thein Thein', 'gender' => 'male', 'dob' => '-65 years', 'phone' => '09-112233445', 'blood' => 'O+', 'allergies' => 'Latex', 'history' => 'Benign prostatic hyperplasia', 'emergency_name' => 'Daw Thein Thein', 'emergency_phone' => '09-223344556', 'status' => 'active'],
            ['name' => 'Ma Cherry', 'gender' => 'female', 'dob' => '-30 years', 'phone' => '09-223344556', 'blood' => 'A+', 'allergies' => null, 'history' => null, 'emergency_name' => 'U Cherry', 'emergency_phone' => '09-334455667', 'status' => 'active'],
            ['name' => 'Ko Aung Aung', 'gender' => 'male', 'dob' => '-38 years', 'phone' => '09-334455667', 'blood' => 'B+', 'allergies' => 'Ibuprofen', 'history' => 'Peptic ulcer disease', 'emergency_name' => 'Daw Aung Aung', 'emergency_phone' => '09-445566778', 'status' => 'active'],
            ['name' => 'Daw Nu Nu', 'gender' => 'female', 'dob' => '-48 years', 'phone' => '09-445566778', 'blood' => 'O+', 'allergies' => null, 'history' => 'Rheumatoid arthritis', 'emergency_name' => 'U Nu Nu', 'emergency_phone' => '09-556677889', 'status' => 'active'],
            ['name' => 'U Tun Tun', 'gender' => 'male', 'dob' => '-58 years', 'phone' => '09-556677889', 'blood' => 'A-', 'allergies' => 'ACE inhibitors', 'history' => 'Heart failure, ejection fraction 40%', 'emergency_name' => 'Daw Tun Tun', 'emergency_phone' => '09-667788990', 'status' => 'active'],
            ['name' => 'Ma May', 'gender' => 'female', 'dob' => '-25 years', 'phone' => '09-667788990', 'blood' => 'B+', 'allergies' => null, 'history' => null, 'emergency_name' => 'U May', 'emergency_phone' => '09-778899001', 'status' => 'active'],
            ['name' => 'U Saw Saw', 'gender' => 'male', 'dob' => '-72 years', 'phone' => '09-778899001', 'blood' => 'AB+', 'allergies' => 'Contrast dye', 'history' => 'Atrial fibrillation on warfarin', 'emergency_name' => 'Daw Saw Saw', 'emergency_phone' => '09-889900112', 'status' => 'active'],
            ['name' => 'Ma Ni Ni', 'gender' => 'female', 'dob' => '-20 years', 'phone' => '09-889900112', 'blood' => 'O-', 'allergies' => null, 'history' => 'Seasonal allergic rhinitis', 'emergency_name' => 'U Ni Ni', 'emergency_phone' => '09-990011223', 'status' => 'active'],
            ['name' => 'U Soe Soe', 'gender' => 'male', 'dob' => '-43 years', 'phone' => '09-990011223', 'blood' => 'A+', 'allergies' => null, 'history' => 'Essential hypertension', 'emergency_name' => 'Daw Soe Soe', 'emergency_phone' => '09-001122334', 'status' => 'active'],
            ['name' => 'Daw Yin Yin', 'gender' => 'female', 'dob' => '-33 years', 'phone' => '09-001122334', 'blood' => 'B-', 'allergies' => 'Shellfish', 'history' => 'PCOS', 'emergency_name' => 'U Yin Yin', 'emergency_phone' => '09-112233445', 'status' => 'active'],
        ];

        foreach ($patientData as $i => $data) {
            $patientNumber = 'DEMO-P-' . str_pad($i + 1, 6, '0', STR_PAD_LEFT);
            $this->patients[] = Patient::updateOrCreate(
                ['patient_number' => $patientNumber],
                [
                    'name' => $data['name'],
                    'email' => strtolower(str_replace([' ', '.'], '', $data['name'])) . '@demo.test',
                    'phone' => $data['phone'],
                    'date_of_birth' => now()->addYears((int) str_replace('- ', '', $data['dob']))->toDateString(),
                    'gender' => $data['gender'],
                    'address' => 'No. ' . ($i + 1) . ', Example Street, Yangon, Myanmar',
                    'emergency_contact_name' => $data['emergency_name'],
                    'emergency_contact_phone' => $data['emergency_phone'],
                    'blood_group' => $data['blood'],
                    'allergies' => $data['allergies'],
                    'medical_history' => $data['history'],
                    'status' => $data['status'],
                ]
            );
        }
    }

    // ──────────────────────────────────────────────
    // 6. APPOINTMENTS
    // ──────────────────────────────────────────────
    protected function seedAppointments(): void
    {
        if (empty($this->doctors) || empty($this->patients)) {
            return;
        }

        $appointmentDefs = [
            // Past appointments (completed)
            ['patient_idx' => 0, 'doctor_idx' => 0, 'days_offset' => -7, 'time' => '09:00', 'status' => 'completed'],
            ['patient_idx' => 1, 'doctor_idx' => 0, 'days_offset' => -5, 'time' => '09:30', 'status' => 'completed'],
            ['patient_idx' => 2, 'doctor_idx' => 1, 'days_offset' => -3, 'time' => '10:00', 'status' => 'completed'],
            ['patient_idx' => 3, 'doctor_idx' => 2, 'days_offset' => -10, 'time' => '09:00', 'status' => 'completed'],
            ['patient_idx' => 4, 'doctor_idx' => 0, 'days_offset' => -2, 'time' => '10:00', 'status' => 'completed'],
            // Today's appointments
            ['patient_idx' => 5, 'doctor_idx' => 0, 'days_offset' => 0, 'time' => '09:00', 'status' => 'checked_in'],
            ['patient_idx' => 6, 'doctor_idx' => 0, 'days_offset' => 0, 'time' => '09:30', 'status' => 'confirmed'],
            ['patient_idx' => 7, 'doctor_idx' => 1, 'days_offset' => 0, 'time' => '10:00', 'status' => 'pending'],
            // Future appointments
            ['patient_idx' => 8, 'doctor_idx' => 2, 'days_offset' => 3, 'time' => '09:00', 'status' => 'scheduled'],
            ['patient_idx' => 9, 'doctor_idx' => 0, 'days_offset' => 5, 'time' => '10:00', 'status' => 'pending'],
            ['patient_idx' => 10, 'doctor_idx' => 3, 'days_offset' => 7, 'time' => '10:00', 'status' => 'confirmed'],
            ['patient_idx' => 11, 'doctor_idx' => 4, 'days_offset' => 2, 'time' => '08:00', 'status' => 'scheduled'],
            // Cancelled
            ['patient_idx' => 12, 'doctor_idx' => 0, 'days_offset' => -1, 'time' => '11:00', 'status' => 'cancelled', 'cancel_reason' => 'Patient request'],
        ];

        foreach ($appointmentDefs as $def) {
            $patient = $this->patients[$def['patient_idx']];
            $doctor = $this->doctors[$def['doctor_idx']];
            $targetDate = now()->addDays($def['days_offset']);
            $date = $this->findNextWorkingDay($doctor, $targetDate);

            if (!$date) continue;

            $aptNumber = Appointment::generateAppointmentNumber();
            $appointment = Appointment::updateOrCreate(
                [
                    'patient_id' => $patient->id,
                    'doctor_id' => $doctor->id,
                    'date' => $date->toDateString(),
                ],
                [
                    'appointment_number' => $aptNumber,
                    'name' => $patient->name,
                    'email' => $patient->email,
                    'phone' => $patient->phone,
                    'time' => $def['time'],
                    'duration' => 30,
                    'department_id' => $doctor->department_id,
                    'message' => 'Demo appointment',
                    'status' => $def['status'],
                    'source' => 'reception',
                    'cancel_reason' => $def['cancel_reason'] ?? null,
                ]
            );

            // Create status history
            AppointmentStatusHistory::updateOrCreate(
                ['appointment_id' => $appointment->id, 'to_status' => $def['status']],
                [
                    'from_status' => null,
                    'note' => 'Demo appointment created.',
                    'changed_by' => $this->superAdmin->id,
                ]
            );

            $this->appointments[] = $appointment;
        }
    }

    // ──────────────────────────────────────────────
    // 7. QUEUE DATA
    // ──────────────────────────────────────────────
    protected function seedQueueTickets(): void
    {
        $todayAppointments = array_filter(
            $this->appointments,
            fn($a) => $a->date->toDateString() === now()->toDateString() && !in_array($a->status->value, ['cancelled', 'completed'])
        );

        $todayStr = now()->toDateString();
        $seq = QueueTicket::whereDate('queue_date', $todayStr)->count() + 1;

        foreach ($todayAppointments as $appointment) {
            $existing = QueueTicket::where('patient_id', $appointment->patient_id)
                ->where('doctor_id', $appointment->doctor_id)
                ->whereDate('queue_date', $todayStr)
                ->first();

            if ($existing) {
                continue;
            }

            $ticketNumber = 'A' . str_pad($seq++, 3, '0', STR_PAD_LEFT);

            $status = match ($appointment->status->value) {
                'checked_in' => 'called',
                'confirmed' => 'waiting',
                default => 'waiting',
            };

            $checkedInAt = $appointment->created_at;
            $calledAt = $status === 'called' ? $checkedInAt->copy()->addMinutes(5) : null;

            QueueTicket::create([
                'patient_id' => $appointment->patient_id,
                'doctor_id' => $appointment->doctor_id,
                'queue_date' => $todayStr,
                'appointment_id' => $appointment->id,
                'ticket_number' => $ticketNumber,
                'status' => $status,
                'checked_in_at' => $checkedInAt,
                'called_at' => $calledAt,
            ]);
        }
    }

    // ──────────────────────────────────────────────
    // 8. CONSULTATIONS
    // ──────────────────────────────────────────────
    protected function seedConsultations(): void
    {
        $consultData = [
            ['patient_idx' => 0, 'doctor_idx' => 0, 'apt_idx' => 0, 'symptoms' => 'Recurrent headache, dizziness, fatigue for 2 weeks', 'diagnosis' => 'Essential Hypertension', 'notes' => 'BP elevated at 160/100. Patient reports stress at work. No family history of hypertension previously noted.', 'plan' => 'Start Amlodipine 5mg daily. Lifestyle modification advice given.', 'follow_up_offset' => 14, 'status' => 'completed'],
            ['patient_idx' => 1, 'doctor_idx' => 0, 'apt_idx' => 1, 'symptoms' => 'Increased thirst, frequent urination, blurred vision for 1 month', 'diagnosis' => 'Poorly controlled Type 2 Diabetes Mellitus', 'notes' => 'HbA1c 9.2%. Current medication Metformin 500mg BD. Fasting glucose 12.5 mmol/L.', 'plan' => 'Increase Metformin to 1000mg BD. Add Gliclazide 40mg. Diet counseling. Return in 2 weeks.', 'follow_up_offset' => 14, 'status' => 'completed'],
            ['patient_idx' => 2, 'doctor_idx' => 1, 'apt_idx' => 2, 'symptoms' => 'Cough for 5 days, fever 38.5°C, mild wheezing in a 3-year-old', 'diagnosis' => 'Acute Bronchitis with mild wheeze', 'notes' => 'Child appears well, no respiratory distress. Mild bilateral wheezing on auscultation. O2 sat 97%.', 'plan' => 'Salbutamol nebulization. Amoxicillin syrup 25mg/kg TDS for 5 days. Paracetamol for fever.', 'follow_up_offset' => 7, 'status' => 'completed'],
            ['patient_idx' => 3, 'doctor_idx' => 2, 'apt_idx' => 3, 'symptoms' => 'Chest tightness on exertion, shortness of breath climbing stairs for 3 months', 'diagnosis' => 'Stable Angina Pectoris', 'notes' => 'ECG: ST depression in leads V4-V6. BP 145/90. Lipid profile: Total cholesterol 7.2, LDL 4.8.', 'plan' => 'Start Aspirin 75mg, Atorvastatin 20mg, Bisoprolol 2.5mg. Stress test recommended. Cardiology follow-up.', 'follow_up_offset' => 30, 'status' => 'completed'],
            ['patient_idx' => 4, 'doctor_idx' => 0, 'apt_idx' => 4, 'symptoms' => 'Intermittent epigastric pain for 2 weeks, worse after meals, occasional nausea', 'diagnosis' => 'Gastroesophageal Reflux Disease (GERD)', 'notes' => 'Abdomen soft, mild epigastric tenderness. No guarding. Patient on hypothyroid medication.', 'plan' => 'Omeprazole 20mg BD for 4 weeks. Dietary advice: avoid spicy food, eat smaller meals. Review if no improvement.', 'follow_up_offset' => 28, 'status' => 'completed'],
            ['patient_idx' => 5, 'doctor_idx' => 0, 'apt_idx' => 5, 'symptoms' => 'Joint pain in knees and hands for 3 months, morning stiffness lasting 1 hour', 'diagnosis' => 'Early Rheumatoid Arthritis suspected', 'notes' => 'Symmetrical small joint involvement. Morning stiffness >30 min. RF positive, anti-CCP elevated.', 'plan' => 'Start Methotrexate 7.5mg weekly with Folic acid 5mg weekly. Refer to Rheumatology. X-ray hands and feet.', 'follow_up_offset' => 14, 'status' => 'completed'],
            ['patient_idx' => 6, 'doctor_idx' => 0, 'apt_idx' => 6, 'symptoms' => 'Annual checkup, no complaints', 'diagnosis' => 'Routine Health Checkup - Normal', 'notes' => 'All vitals within normal limits. BMI 23. No concerns.', 'plan' => 'Continue healthy lifestyle. Next checkup in 1 year.', 'follow_up_offset' => null, 'status' => 'completed'],
            ['patient_idx' => 14, 'doctor_idx' => 2, 'apt_idx' => 10, 'symptoms' => 'Palpitations, mild shortness of breath for 1 week', 'diagnosis' => 'Atrial Fibrillation with rapid ventricular response', 'notes' => 'Irregularly irregular pulse at 110bpm. ECG confirmed AF. INR currently 1.8 on warfarin.', 'plan' => 'Increase Bisoprolol rate control. Adjust warfarin target INR 2.5-3.5. Echo if not done recently.', 'follow_up_offset' => 7, 'status' => 'completed'],
        ];

        foreach ($consultData as $data) {
            $patient = $this->patients[$data['patient_idx']];
            $doctor = $this->doctors[$data['doctor_idx']];
            $appointment = $this->appointments[$data['apt_idx']] ?? null;

            $consultation = Consultation::updateOrCreate(
                [
                    'patient_id' => $patient->id,
                    'doctor_id' => $doctor->id,
                    'appointment_id' => $appointment?->id,
                ],
                [
                    'symptoms' => $data['symptoms'],
                    'diagnosis' => $data['diagnosis'],
                    'clinical_notes' => $data['notes'],
                    'treatment_plan' => $data['plan'],
                    'follow_up_date' => $data['follow_up_offset'] ? now()->addDays($data['follow_up_offset'])->toDateString() : null,
                    'follow_up_notes' => $data['follow_up_offset'] ? 'Follow up for review.' : null,
                    'status' => $data['status'],
                ]
            );

            $this->consultations[] = $consultation;

            // Vital signs for each consultation
            VitalSign::updateOrCreate(
                ['consultation_id' => $consultation->id],
                $this->getVitalSignsForConsultation($data['patient_idx'])
            );
        }
    }

    // ──────────────────────────────────────────────
    // 11. MEDICINES
    // ──────────────────────────────────────────────
    protected function seedMedicines(): void
    {
        $meds = [
            ['name' => 'Paracetamol 500mg', 'generic' => 'Paracetamol', 'manufacturer' => 'Health Pharma', 'category' => 'Analgesic', 'form' => 'Tablet', 'strength' => '500mg', 'price' => 50, 'cost' => 30, 'min_stock' => 100, 'expiry_offset_months' => 18],
            ['name' => 'Amoxicillin 500mg', 'generic' => 'Amoxicillin', 'manufacturer' => 'Medi Labs', 'category' => 'Antibiotic', 'form' => 'Capsule', 'strength' => '500mg', 'price' => 200, 'cost' => 120, 'min_stock' => 50, 'expiry_offset_months' => 12],
            ['name' => 'Cetirizine 10mg', 'generic' => 'Cetirizine', 'manufacturer' => 'AllerCare Pharma', 'category' => 'Antihistamine', 'form' => 'Tablet', 'strength' => '10mg', 'price' => 80, 'cost' => 45, 'min_stock' => 60, 'expiry_offset_months' => 24],
            ['name' => 'Omeprazole 20mg', 'generic' => 'Omeprazole', 'manufacturer' => 'Gastro Labs', 'category' => 'Proton Pump Inhibitor', 'form' => 'Capsule', 'strength' => '20mg', 'price' => 150, 'cost' => 80, 'min_stock' => 40, 'expiry_offset_months' => 18],
            ['name' => 'Ibuprofen 400mg', 'generic' => 'Ibuprofen', 'manufacturer' => 'Health Pharma', 'category' => 'NSAID', 'form' => 'Tablet', 'strength' => '400mg', 'price' => 60, 'cost' => 35, 'min_stock' => 80, 'expiry_offset_months' => 15],
            ['name' => 'Amlodipine 5mg', 'generic' => 'Amlodipine', 'manufacturer' => 'Cardio Pharma', 'category' => 'Antihypertensive', 'form' => 'Tablet', 'strength' => '5mg', 'price' => 120, 'cost' => 65, 'min_stock' => 30, 'expiry_offset_months' => 20],
            ['name' => 'Metformin 500mg', 'generic' => 'Metformin', 'manufacturer' => 'DiabetCare Labs', 'category' => 'Antidiabetic', 'form' => 'Tablet', 'strength' => '500mg', 'price' => 100, 'cost' => 55, 'min_stock' => 50, 'expiry_offset_months' => 24],
            ['name' => 'Salbutamol Inhaler', 'generic' => 'Salbutamol', 'manufacturer' => 'RespMed', 'category' => 'Bronchodilator', 'form' => 'Inhaler', 'strength' => '100mcg', 'price' => 3000, 'cost' => 1800, 'min_stock' => 10, 'expiry_offset_months' => 18],
            ['name' => 'Azithromycin 500mg', 'generic' => 'Azithromycin', 'manufacturer' => 'Medi Labs', 'category' => 'Antibiotic', 'form' => 'Tablet', 'strength' => '500mg', 'price' => 250, 'cost' => 150, 'min_stock' => 30, 'expiry_offset_months' => 20],
            ['name' => 'Prednisolone 5mg', 'generic' => 'Prednisolone', 'manufacturer' => 'Steroid Labs', 'category' => 'Corticosteroid', 'form' => 'Tablet', 'strength' => '5mg', 'price' => 40, 'cost' => 20, 'min_stock' => 40, 'expiry_offset_months' => 18],
            ['name' => 'Gliclazide 80mg', 'generic' => 'Gliclazide', 'manufacturer' => 'DiabetCare Labs', 'category' => 'Antidiabetic', 'form' => 'Tablet', 'strength' => '80mg', 'price' => 90, 'cost' => 50, 'min_stock' => 30, 'expiry_offset_months' => 24],
            ['name' => 'Atorvastatin 20mg', 'generic' => 'Atorvastatin', 'manufacturer' => 'Cardio Pharma', 'category' => 'Statin', 'form' => 'Tablet', 'strength' => '20mg', 'price' => 180, 'cost' => 100, 'min_stock' => 25, 'expiry_offset_months' => 20],
            ['name' => 'Salbutamol Syrup', 'generic' => 'Salbutamol', 'manufacturer' => 'RespMed', 'category' => 'Bronchodilator', 'form' => 'Syrup', 'strength' => '2mg/5ml', 'price' => 1500, 'cost' => 900, 'min_stock' => 15, 'expiry_offset_months' => 15],
            ['name' => 'Bisoprolol 5mg', 'generic' => 'Bisoprolol', 'manufacturer' => 'Cardio Pharma', 'category' => 'Beta Blocker', 'form' => 'Tablet', 'strength' => '5mg', 'price' => 100, 'cost' => 55, 'min_stock' => 25, 'expiry_offset_months' => 24],
            ['name' => 'Methotrexate 2.5mg', 'generic' => 'Methotrexate', 'manufacturer' => 'Immuno Labs', 'category' => 'Immunosuppressant', 'form' => 'Tablet', 'strength' => '2.5mg', 'price' => 500, 'cost' => 300, 'min_stock' => 10, 'expiry_offset_months' => 18],
        ];

        foreach ($meds as $med) {
            $medicine = Medicine::updateOrCreate(
                ['name' => $med['name']],
                [
                    'generic_name' => $med['generic'],
                    'manufacturer' => $med['manufacturer'],
                    'category' => $med['category'],
                    'form' => $med['form'],
                    'strength' => $med['strength'],
                    'unit_price' => $med['price'],
                    'selling_price' => $med['price'],
                    'cost_price' => $med['cost'],
                    'stock_quantity' => 0,
                    'minimum_stock_level' => $med['min_stock'],
                    'is_active' => true,
                ]
            );

            $this->medicines[] = $medicine;

            // Create inventory batches
            $batchNumber = 'BATCH-' . str_pad($medicine->id, 3, '0', STR_PAD_LEFT) . '-001';
            $quantity = $med['min_stock'] * 5;

            InventoryBatch::updateOrCreate(
                ['batch_number' => $batchNumber],
                [
                    'medicine_id' => $medicine->id,
                    'quantity' => $quantity,
                    'received_date' => now()->subDays(30)->toDateString(),
                    'expiry_date' => now()->addMonths($med['expiry_offset_months'])->toDateString(),
                    'unit_cost' => $med['cost'],
                    'supplier' => $med['manufacturer'],
                    'status' => 'active',
                ]
            );

            // Stock movement for the batch
            StockMovement::create([
                'medicine_id' => $medicine->id,
                'type' => 'stock_in',
                'quantity' => $quantity,
                'balance_after' => $quantity,
                'reason' => 'Demo stock',
                'movement_date' => now()->toDateString(),
            ]);

            $medicine->update(['stock_quantity' => $quantity]);

            // Expired batch for one medicine
            if ($medicine->name === 'Amoxicillin 500mg') {
                $expiredBatch = InventoryBatch::updateOrCreate(
                    ['batch_number' => 'BATCH-002-EXP'],
                    [
                        'medicine_id' => $medicine->id,
                        'quantity' => 20,
                        'received_date' => now()->subMonths(24)->toDateString(),
                        'expiry_date' => now()->subMonth()->toDateString(),
                        'unit_cost' => $med['cost'],
                        'supplier' => $med['manufacturer'],
                        'status' => 'active',
                    ]
                );

                StockMovement::create([
                    'medicine_id' => $medicine->id,
                    'inventory_batch_id' => $expiredBatch->id,
                    'type' => 'stock_in',
                    'quantity' => 20,
                    'balance_after' => $quantity + 20,
                    'reason' => 'Expired batch stock',
                    'movement_date' => now()->subMonths(24)->toDateString(),
                ]);

                $medicine->update(['stock_quantity' => $quantity + 20]);
            }

            // Expiring soon batch
            if ($medicine->name === 'Paracetamol 500mg') {
                $expiringBatch = InventoryBatch::updateOrCreate(
                    ['batch_number' => 'BATCH-001-EXP-SOON'],
                    [
                        'medicine_id' => $medicine->id,
                        'quantity' => 30,
                        'received_date' => now()->subMonths(6)->toDateString(),
                        'expiry_date' => now()->addDays(15)->toDateString(),
                        'unit_cost' => $med['cost'],
                        'supplier' => $med['manufacturer'],
                        'status' => 'active',
                    ]
                );

                StockMovement::create([
                    'medicine_id' => $medicine->id,
                    'inventory_batch_id' => $expiringBatch->id,
                    'type' => 'stock_in',
                    'quantity' => 30,
                    'balance_after' => $quantity + 30,
                    'reason' => 'Expiring soon batch',
                    'movement_date' => now()->subMonths(6)->toDateString(),
                ]);

                $medicine->update(['stock_quantity' => $quantity + 30]);
            }
        }
    }

    // ──────────────────────────────────────────────
    // 13. INVESTIGATIONS / LAB
    // ──────────────────────────────────────────────
    protected function seedLabTests(): void
    {
        $labTests = [
            ['name' => 'Complete Blood Count', 'code' => 'CBC-DEMO', 'category' => 'Hematology', 'description' => 'Full blood count with differential', 'sample_type' => 'Blood (EDTA)', 'reference_range' => 'WBC 4-11, Hb 12-16, Platelets 150-400', 'unit' => 'x10^9/L, g/L', 'price' => 8000],
            ['name' => 'Fasting Blood Glucose', 'code' => 'FBG-DEMO', 'category' => 'Biochemistry', 'description' => 'Fasting blood glucose level', 'sample_type' => 'Blood (Fluoride)', 'reference_range' => '3.9-5.5 mmol/L', 'unit' => 'mmol/L', 'price' => 3000],
            ['name' => 'Lipid Profile', 'code' => 'LP-DEMO', 'category' => 'Biochemistry', 'description' => 'Total cholesterol, HDL, LDL, Triglycerides', 'sample_type' => 'Blood (Serum)', 'reference_range' => 'TC<5.2, LDL<3.0, HDL>1.0', 'unit' => 'mmol/L', 'price' => 12000],
            ['name' => 'Urinalysis', 'code' => 'UA-DEMO', 'category' => 'Clinical Pathology', 'description' => 'Routine urine examination', 'sample_type' => 'Urine', 'reference_range' => 'Clear, pH 5-8, No protein/glucose', 'unit' => '-', 'price' => 3000],
            ['name' => 'Liver Function Test', 'code' => 'LFT-DEMO', 'category' => 'Biochemistry', 'description' => 'ALT, AST, ALP, Bilirubin, Albumin', 'sample_type' => 'Blood (Serum)', 'reference_range' => 'ALT<40, AST<40, Bilirubin<17', 'unit' => 'U/L, µmol/L, g/L', 'price' => 15000],
            ['name' => 'HbA1c', 'code' => 'HBA1C-DEMO', 'category' => 'Endocrinology', 'description' => 'Glycated hemoglobin', 'sample_type' => 'Blood (EDTA)', 'reference_range' => '<5.7%', 'unit' => '%', 'price' => 10000],
            ['name' => 'Renal Function Test', 'code' => 'RFT-DEMO', 'category' => 'Biochemistry', 'description' => 'Creatinine, BUN, Electrolytes', 'sample_type' => 'Blood (Serum)', 'reference_range' => 'Cr 60-110, BUN 2.5-7.8', 'unit' => 'µmol/L, mmol/L', 'price' => 12000],
        ];

        foreach ($labTests as $test) {
            LabTest::updateOrCreate(
                ['code' => $test['code']],
                [...$test, 'is_active' => true]
            );
        }
    }

    protected function seedInvestigations(): void
    {
        if (empty($this->consultations)) return;

        $tests = LabTest::whereIn('code', ['CBC-DEMO', 'FBG-DEMO', 'LP-DEMO', 'HBA1C-DEMO', 'LFT-DEMO', 'RFT-DEMO'])->get()->keyBy('code');

        $investData = [
            ['consult_idx' => 0, 'patient_idx' => 0, 'doctor_idx' => 0, 'test_code' => 'CBC-DEMO', 'priority' => 'routine', 'status' => 'completed', 'value' => 'WBC 7.2, Hb 138, Platelets 280', 'unit' => 'x10^9/L, g/L, x10^9/L', 'ref' => 'WBC 4-11, Hb 120-160, Plt 150-400', 'interp' => 'Normal complete blood count', 'notes' => 'Baseline CBC for hypertension workup'],
            ['consult_idx' => 1, 'patient_idx' => 1, 'doctor_idx' => 0, 'test_code' => 'FBG-DEMO', 'priority' => 'routine', 'status' => 'completed', 'value' => '12.5', 'unit' => 'mmol/L', 'ref' => '3.9-5.5', 'interp' => 'Significantly elevated — poorly controlled diabetes', 'notes' => 'Check current glucose control'],
            ['consult_idx' => 1, 'patient_idx' => 1, 'doctor_idx' => 0, 'test_code' => 'HBA1C-DEMO', 'priority' => 'routine', 'status' => 'completed', 'value' => '9.2', 'unit' => '%', 'ref' => '<5.7', 'interp' => 'Poor glycemic control — average glucose over 3 months significantly elevated', 'notes' => 'Assess 3-month glucose average'],
            ['consult_idx' => 3, 'patient_idx' => 3, 'doctor_idx' => 2, 'test_code' => 'LP-DEMO', 'priority' => 'urgent', 'status' => 'completed', 'value' => 'TC 7.2, LDL 4.8, HDL 0.9, TG 3.1', 'unit' => 'mmol/L', 'ref' => 'TC<5.2, LDL<3.0, HDL>1.0, TG<1.7', 'interp' => 'Dyslipidemia — high cardiovascular risk. LDL significantly above target.', 'notes' => 'Cardiovascular risk assessment'],
            ['consult_idx' => 3, 'patient_idx' => 3, 'doctor_idx' => 2, 'test_code' => 'RFT-DEMO', 'priority' => 'routine', 'status' => 'requested', 'value' => null, 'unit' => null, 'ref' => null, 'interp' => null, 'notes' => 'Renal function baseline before starting statin'],
            ['consult_idx' => 5, 'patient_idx' => 5, 'doctor_idx' => 0, 'test_code' => 'LFT-DEMO', 'priority' => 'routine', 'status' => 'requested', 'value' => null, 'unit' => null, 'ref' => null, 'interp' => null, 'notes' => 'Baseline LFT before starting Methotrexate'],
        ];

        foreach ($investData as $data) {
            $test = $tests[$data['test_code']] ?? null;
            if (!$test) continue;

            Investigation::updateOrCreate(
                [
                    'patient_id' => $this->patients[$data['patient_idx']]->id,
                    'doctor_id' => $this->doctors[$data['doctor_idx']]->id,
                    'lab_test_id' => $test->id,
                ],
                [
                    'consultation_id' => $this->consultations[$data['consult_idx']]->id ?? null,
                    'requested_date' => now()->subDays(3)->toDateString(),
                    'priority' => $data['priority'],
                    'clinical_notes' => $data['notes'],
                    'status' => $data['status'],
                    'result_value' => $data['value'],
                    'result_unit' => $data['unit'],
                    'result_reference_range' => $data['ref'],
                    'interpretation' => $data['interp'],
                    'resulted_at' => $data['status'] === 'completed' ? now()->subDay() : null,
                    'result_status' => $data['status'] === 'completed' ? 'verified' : 'pending',
                ]
            );
        }
    }

    // ──────────────────────────────────────────────
    // 10. PRESCRIPTIONS
    // ──────────────────────────────────────────────
    protected function seedPrescriptions(): void
    {
        if (empty($this->consultations) || empty($this->medicines)) return;

        $prescData = [
            ['consult_idx' => 0, 'patient_idx' => 0, 'doctor_idx' => 0, 'items' => [
                ['med_name' => 'Amlodipine 5mg', 'dosage' => '5mg', 'frequency' => 'Once daily', 'duration' => '30 days', 'qty' => 30, 'instructions' => 'Take in the morning'],
            ]],
            ['consult_idx' => 1, 'patient_idx' => 1, 'doctor_idx' => 0, 'items' => [
                ['med_name' => 'Metformin 500mg', 'dosage' => '1000mg', 'frequency' => 'Twice daily', 'duration' => '30 days', 'qty' => 60, 'instructions' => 'Take with meals'],
                ['med_name' => 'Gliclazide 80mg', 'dosage' => '80mg', 'frequency' => 'Once daily', 'duration' => '30 days', 'qty' => 30, 'instructions' => 'Take before breakfast'],
            ]],
            ['consult_idx' => 2, 'patient_idx' => 2, 'doctor_idx' => 1, 'items' => [
                ['med_name' => 'Amoxicillin 500mg', 'dosage' => '250mg', 'frequency' => 'Three times daily', 'duration' => '5 days', 'qty' => 15, 'instructions' => 'Take with food. For child weight 14kg.'],
                ['med_name' => 'Paracetamol 500mg', 'dosage' => '15mg/kg', 'frequency' => 'Every 6 hours as needed', 'duration' => '5 days', 'qty' => 10, 'instructions' => 'For fever only. Max 4 doses/day.'],
                ['med_name' => 'Salbutamol Syrup', 'dosage' => '2mg/5ml', 'frequency' => 'Three times daily', 'duration' => '5 days', 'qty' => 1, 'instructions' => '5ml three times daily'],
            ]],
            ['consult_idx' => 3, 'patient_idx' => 3, 'doctor_idx' => 2, 'items' => [
                ['med_name' => 'Atorvastatin 20mg', 'dosage' => '20mg', 'frequency' => 'Once daily at bedtime', 'duration' => '30 days', 'qty' => 30, 'instructions' => 'Take at bedtime'],
                ['med_name' => 'Bisoprolol 5mg', 'dosage' => '2.5mg', 'frequency' => 'Once daily', 'duration' => '30 days', 'qty' => 30, 'instructions' => 'Do not stop abruptly'],
            ]],
            ['consult_idx' => 4, 'patient_idx' => 4, 'doctor_idx' => 0, 'items' => [
                ['med_name' => 'Omeprazole 20mg', 'dosage' => '20mg', 'frequency' => 'Twice daily', 'duration' => '14 days', 'qty' => 28, 'instructions' => 'Take 30 minutes before meals'],
            ]],
            ['consult_idx' => 5, 'patient_idx' => 5, 'doctor_idx' => 0, 'items' => [
                ['med_name' => 'Methotrexate 2.5mg', 'dosage' => '7.5mg', 'frequency' => 'Once weekly', 'duration' => '12 weeks', 'qty' => 36, 'instructions' => 'Take on the same day each week. Folic acid 5mg on other days.'],
                ['med_name' => 'Paracetamol 500mg', 'dosage' => '500mg', 'frequency' => 'Every 8 hours as needed', 'duration' => '14 days', 'qty' => 28, 'instructions' => 'For joint pain relief'],
            ]],
        ];

        foreach ($prescData as $data) {
            $patient = $this->patients[$data['patient_idx']] ?? null;
            $doctor = $this->doctors[$data['doctor_idx']] ?? null;
            $consultation = $this->consultations[$data['consult_idx']] ?? null;

            if (!$patient || !$doctor || !$consultation) continue;

            $prescription = Prescription::updateOrCreate(
                [
                    'patient_id' => $patient->id,
                    'doctor_id' => $doctor->id,
                    'consultation_id' => $consultation->id,
                ],
                [
                    'notes' => 'Demo prescription',
                    'prescribed_date' => now()->subDays(3)->toDateString(),
                    'status' => 'active',
                ]
            );

            foreach ($data['items'] as $item) {
                $medicine = Medicine::where('name', $item['med_name'])->first();
                if (!$medicine) continue;

                PrescriptionItem::updateOrCreate(
                    [
                        'prescription_id' => $prescription->id,
                        'medicine_id' => $medicine->id,
                    ],
                    [
                        'dosage' => $item['dosage'],
                        'frequency' => $item['frequency'],
                        'duration' => $item['duration'],
                        'instructions' => $item['instructions'],
                        'quantity' => $item['qty'],
                    ]
                );
            }
        }
    }

    // ──────────────────────────────────────────────
    // 14. INVOICES
    // ──────────────────────────────────────────────
    protected function seedInvoices(): void
    {
        if (empty($this->consultations)) return;

        $invoiceData = [
            ['consult_idx' => 0, 'patient_idx' => 0, 'doctor_idx' => 0, 'apt_idx' => 0, 'fee' => 15000, 'paid_pct' => 1.0, 'method' => 'cash'],
            ['consult_idx' => 1, 'patient_idx' => 1, 'doctor_idx' => 0, 'apt_idx' => 1, 'fee' => 15000, 'paid_pct' => 1.0, 'method' => 'card'],
            ['consult_idx' => 2, 'patient_idx' => 2, 'doctor_idx' => 1, 'apt_idx' => 2, 'fee' => 20000, 'paid_pct' => 0.5, 'method' => 'cash'],
            ['consult_idx' => 3, 'patient_idx' => 3, 'doctor_idx' => 2, 'apt_idx' => 3, 'fee' => 30000, 'paid_pct' => 0.0, 'method' => null],
            ['consult_idx' => 4, 'patient_idx' => 4, 'doctor_idx' => 0, 'apt_idx' => 4, 'fee' => 15000, 'paid_pct' => 1.0, 'method' => 'mobile_payment'],
            ['consult_idx' => 5, 'patient_idx' => 5, 'doctor_idx' => 0, 'apt_idx' => 5, 'fee' => 15000, 'paid_pct' => 1.0, 'method' => 'bank_transfer'],
            ['consult_idx' => 6, 'patient_idx' => 6, 'doctor_idx' => 0, 'apt_idx' => 6, 'fee' => 15000, 'paid_pct' => 1.0, 'method' => 'cash'],
        ];

        foreach ($invoiceData as $data) {
            $patient = $this->patients[$data['patient_idx']] ?? null;
            $doctor = $this->doctors[$data['doctor_idx']] ?? null;
            $consultation = $this->consultations[$data['consult_idx']] ?? null;
            $appointment = $this->appointments[$data['apt_idx']] ?? null;

            if (!$patient || !$doctor || !$consultation) continue;

            $subtotal = $data['fee'];
            $tax = round($subtotal * 0.05, 2);
            $total = $subtotal + $tax;
            $amountPaid = round($total * $data['paid_pct'], 2);
            $balance = max(0, $total - $amountPaid);

            $status = 'issued';
            if ($amountPaid <= 0) $status = 'issued';
            elseif ($balance <= 0) $status = 'paid';
            else $status = 'partially_paid';

            $invoice = Invoice::updateOrCreate(
                [
                    'patient_id' => $patient->id,
                    'consultation_id' => $consultation->id,
                ],
                [
                    'doctor_id' => $doctor->id,
                    'appointment_id' => $appointment?->id,
                    'subtotal' => $subtotal,
                    'discount' => 0,
                    'tax' => $tax,
                    'total' => $total,
                    'amount_paid' => $amountPaid,
                    'balance' => $balance,
                    'status' => $status,
                    'notes' => 'Demo invoice',
                    'issued_at' => now()->subDays(2),
                ]
            );

            $this->invoices[] = $invoice;

            InvoiceItem::updateOrCreate(
                ['invoice_id' => $invoice->id, 'description' => 'Consultation Fee', 'type' => 'consultation'],
                ['quantity' => 1, 'unit_price' => $data['fee'], 'total' => $data['fee']]
            );

            if ($status === 'partially_paid' || $status === 'paid') {
                Payment::updateOrCreate(
                    ['invoice_id' => $invoice->id, 'reference_number' => 'DEMO-PAY-' . $invoice->id],
                    [
                        'amount' => $amountPaid,
                        'payment_method' => $data['method'],
                        'reference_number' => 'DEMO-PAY-' . $invoice->id,
                        'notes' => 'Demo payment',
                        'recorded_by' => $this->superAdmin->id,
                        'paid_at' => now()->subDays(1),
                    ]
                );
            }
        }
    }

    // ──────────────────────────────────────────────
    // 16. EXPENSES
    // ──────────────────────────────────────────────
    protected function seedExpenses(): void
    {
        $this->call(ExpenseCategorySeeder::class);

        $categories = ExpenseCategory::pluck('id', 'name');

        $expenseData = [
            ['category' => 'Medical Supplies', 'amount' => 500000, 'date_offset' => -30, 'method' => 'bank_transfer', 'vendor' => 'MediSupply Myanmar', 'desc' => 'Monthly medical supplies purchase'],
            ['category' => 'Rent', 'amount' => 2000000, 'date_offset' => -1, 'method' => 'bank_transfer', 'vendor' => 'Property Owner', 'desc' => 'Monthly clinic rent — August 2026'],
            ['category' => 'Utilities', 'amount' => 300000, 'date_offset' => -5, 'method' => 'mobile_payment', 'vendor' => 'Myanmar Electric Power', 'desc' => 'Electricity bill for August'],
            ['category' => 'Medicine Purchase', 'amount' => 3000000, 'date_offset' => -15, 'method' => 'bank_transfer', 'vendor' => 'Pharma Distributor Co.', 'desc' => 'Wholesale medicine restocking'],
            ['category' => 'Salary', 'amount' => 5000000, 'date_offset' => -1, 'method' => 'bank_transfer', 'vendor' => null, 'desc' => 'Monthly staff salaries — August 2026'],
            ['category' => 'Maintenance', 'amount' => 150000, 'date_offset' => -10, 'method' => 'cash', 'vendor' => 'ABC Maintenance', 'desc' => 'AC maintenance and filter replacement'],
            ['category' => 'Office Supplies', 'amount' => 80000, 'date_offset' => -7, 'method' => 'cash', 'vendor' => 'Office World', 'desc' => 'Printer paper, toner, and stationery'],
        ];

        foreach ($expenseData as $i => $data) {
            $catId = $categories[$data['category']] ?? null;
            if (!$catId) continue;

            $expNumber = 'EXP-DEMO-' . str_pad($i + 1, 4, '0', STR_PAD_LEFT);

            Expense::updateOrCreate(
                ['expense_number' => $expNumber],
                [
                    'expense_category_id' => $catId,
                    'amount' => $data['amount'],
                    'payment_method' => $data['method'],
                    'expense_date' => now()->addDays($data['date_offset'])->toDateString(),
                    'vendor' => $data['vendor'],
                    'description' => $data['desc'],
                    'status' => 'active',
                    'created_by' => $this->superAdmin->id,
                ]
            );
        }
    }

    // ──────────────────────────────────────────────
    // 17. COMMUNICATIONS
    // ──────────────────────────────────────────────
    protected function seedCommunications(): void
    {
        if (empty($this->patients) || empty($this->appointments)) return;

        $commData = [
            ['patient_idx' => 0, 'apt_idx' => 0, 'method' => 'phone', 'purpose' => 'appointment_confirmation', 'outcome' => 'confirmed', 'note' => 'Confirmed appointment for hypertension follow-up.', 'days_offset' => -7, 'follow_up' => null],
            ['patient_idx' => 8, 'apt_idx' => 9, 'method' => 'sms', 'purpose' => 'reminder', 'outcome' => 'informed', 'note' => 'Sent appointment reminder for next week.', 'days_offset' => -2, 'follow_up' => 3],
            ['patient_idx' => 9, 'apt_idx' => 10, 'method' => 'phone', 'purpose' => 'appointment_confirmation', 'outcome' => 'confirmed', 'note' => 'Patient confirmed dermatology appointment.', 'days_offset' => -5, 'follow_up' => null],
            ['patient_idx' => 11, 'apt_idx' => 4, 'method' => 'phone', 'purpose' => 'follow_up', 'outcome' => 'contacted', 'note' => 'Called for medication follow-up. Patient reports improvement.', 'days_offset' => -1, 'follow_up' => 7],
            ['patient_idx' => 7, 'apt_idx' => null, 'method' => 'email', 'purpose' => 'test_result', 'outcome' => 'informed', 'note' => 'Lab results available. Patient informed to collect.', 'days_offset' => -3, 'follow_up' => null],
            ['patient_idx' => 10, 'apt_idx' => null, 'method' => 'phone', 'purpose' => 'follow_up', 'outcome' => 'no_answer', 'note' => 'Called for cardiology follow-up. No answer. Will retry.', 'days_offset' => -2, 'follow_up' => 1],
        ];

        foreach ($commData as $data) {
            $patient = $this->patients[$data['patient_idx']] ?? null;
            $appointment = $data['apt_idx'] !== null ? ($this->appointments[$data['apt_idx']] ?? null) : null;

            if (!$patient) continue;

            Communication::updateOrCreate(
                [
                    'patient_id' => $patient->id,
                    'purpose' => $data['purpose'],
                    'user_id' => $this->superAdmin->id,
                ],
                [
                    'contact_method' => $data['method'],
                    'contacted_at' => now()->addDays($data['days_offset']),
                    'appointment_id' => $appointment?->id,
                    'user_id' => $this->superAdmin->id,
                    'purpose' => $data['purpose'],
                    'outcome' => $data['outcome'],
                    'note' => $data['note'],
                    'follow_up_date' => $data['follow_up'] ? now()->addDays($data['follow_up'])->toDateString() : null,
                    'follow_up_note' => $data['follow_up'] ? 'Follow up on previous communication.' : null,
                    'follow_up_completed' => false,
                ]
            );
        }
    }

    // ──────────────────────────────────────────────
    // 18. NOTIFICATIONS
    // ──────────────────────────────────────────────
    protected function seedNotifications(): void
    {
        Notification::updateOrCreate(
            ['user_id' => $this->superAdmin->id, 'type' => 'appointment', 'title' => 'New Appointment Booked'],
            [
                'message' => 'Patient Ma Htwe Htwe Kyaw has booked an appointment for hypertension follow-up.',
                'module' => 'appointment',
                'action' => 'created',
                'url' => '/appointments',
            ]
        );

        Notification::updateOrCreate(
            ['user_id' => $this->superAdmin->id, 'type' => 'consultation', 'title' => 'Consultation Completed'],
            [
                'message' => 'Dr. Aung Myo completed consultation for Ma Htwe Htwe Kyaw.',
                'module' => 'consultation',
                'action' => 'completed',
                'url' => '/consultations',
            ]
        );

        Notification::updateOrCreate(
            ['user_id' => $this->superAdmin->id, 'type' => 'payment', 'title' => 'Payment Received'],
            [
                'message' => 'Payment of 15,750 MMK received for Invoice DEMO.',
                'module' => 'payment',
                'action' => 'created',
                'url' => '/invoices',
            ]
        );

        Notification::updateOrCreate(
            ['user_id' => $this->superAdmin->id, 'type' => 'expiry', 'title' => 'Stock Expiring Soon'],
            [
                'message' => 'Amoxicillin 500mg batch BATCH-002-EXP has expired. 20 units need write-off.',
                'module' => 'expiry',
                'action' => 'warning',
                'is_read' => false,
                'url' => '/inventory',
            ]
        );
    }

    // ──────────────────────────────────────────────
    // 20. SETTINGS
    // ──────────────────────────────────────────────
    protected function seedSettings(): void
    {
        $defaults = [
            'site.site_name' => 'Demo Family Clinic',
            'clinic_name' => 'Demo Family Clinic',
            'clinic_email' => 'info@clinic-demo.test',
            'clinic_phone' => '09-000000000',
            'clinic_address' => 'No. 123, Example Street, Yangon, Myanmar',
            'clinic_currency' => 'MMK',
            'clinic_opening_hours' => 'Mon-Sat: 08:00-17:00',
            'clinic_default_fee' => '15000',
            'clinic_tax_rate' => '5',
            'clinic_receipt_footer' => 'Thank you for choosing Demo Family Clinic. Get well soon!',
            'appointment.default_duration' => '30',
            'queue.ticket_prefix' => 'A',
            'invoice.default_tax_rate' => '5',
            'inventory.expiry_warning_days' => '30',
        ];

        foreach ($defaults as $key => $value) {
            Setting::updateOrCreate(
                ['key' => $key],
                ['value' => $value, 'group' => 'clinic']
            );
        }

        ClinicSettingsService::flush();
    }

    // ──────────────────────────────────────────────
    // HELPERS
    // ──────────────────────────────────────────────
    protected function findNextWorkingDay(Doctor $doctor, \Carbon\Carbon $date, int $maxAttempts = 14): ?\Carbon\Carbon
    {
        $attempt = 0;
        while ($attempt < $maxAttempts) {
            $dayOfWeek = (int) $date->format('N');

            if (in_array($dayOfWeek, $doctor->available_days ?? []) && !$doctor->hasUnavailableDate($date)) {
                return $date;
            }

            $date = $date->copy()->addDay();
            $attempt++;
        }

        return null;
    }

    protected function getVitalSignsForConsultation(int $patientIdx): array
    {
        $vitals = [
            0 => ['blood_pressure' => '160/100', 'temperature' => 36.8, 'pulse' => 88, 'respiratory_rate' => 18, 'weight' => 72.5, 'height' => 165.0, 'oxygen_saturation' => 98.0],
            1 => ['blood_pressure' => '130/85', 'temperature' => 36.5, 'pulse' => 82, 'respiratory_rate' => 16, 'weight' => 85.2, 'height' => 170.0, 'oxygen_saturation' => 98.0],
            2 => ['blood_pressure' => '95/60', 'temperature' => 38.5, 'pulse' => 110, 'respiratory_rate' => 28, 'weight' => 14.0, 'height' => 98.0, 'oxygen_saturation' => 97.0],
            3 => ['blood_pressure' => '145/90', 'temperature' => 36.6, 'pulse' => 78, 'respiratory_rate' => 16, 'weight' => 78.0, 'height' => 172.0, 'oxygen_saturation' => 97.0],
            4 => ['blood_pressure' => '125/80', 'temperature' => 36.7, 'pulse' => 75, 'respiratory_rate' => 16, 'weight' => 62.0, 'height' => 158.0, 'oxygen_saturation' => 99.0],
            5 => ['blood_pressure' => '135/85', 'temperature' => 36.6, 'pulse' => 80, 'respiratory_rate' => 18, 'weight' => 82.0, 'height' => 175.0, 'oxygen_saturation' => 98.0],
            6 => ['blood_pressure' => '120/78', 'temperature' => 36.5, 'pulse' => 72, 'respiratory_rate' => 16, 'weight' => 68.0, 'height' => 162.0, 'oxygen_saturation' => 99.0],
            7 => ['blood_pressure' => '128/82', 'temperature' => 36.7, 'pulse' => 76, 'respiratory_rate' => 16, 'weight' => 70.0, 'height' => 167.0, 'oxygen_saturation' => 98.0],
        ];

        $v = $vitals[$patientIdx] ?? $vitals[0];
        $v['recorded_at'] = now();
        return $v;
    }
}
