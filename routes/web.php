<?php

use App\Http\Controllers\AppointmentController;
use App\Http\Controllers\CommunicationController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\ConsultationController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DepartmentController;
use App\Http\Controllers\DoctorController;
use App\Http\Controllers\ExpenseCategoryController;
use App\Http\Controllers\ExpenseController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\InventoryController;
use App\Http\Controllers\InvestigationController;
use App\Http\Controllers\LabTestController;
use App\Http\Controllers\LocationController;
use App\Http\Controllers\MedicineController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\NotificationCenterController;
use App\Http\Controllers\MedicalRecordController;
use App\Http\Controllers\PatientController;
use App\Http\Controllers\PrescriptionController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PublicAppointmentController;
use App\Http\Controllers\PublicController;
use App\Http\Controllers\QueueController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\ServiceController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\StaffController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\UserSettingController;
use Illuminate\Support\Facades\Route;

Route::prefix('/')->group(function () {
    Route::get('/', [PublicController::class, 'index'])->name('public.index');
    Route::get('/error', [PublicController::class, 'error'])->name('public.error');
    Route::get('/about', [PublicController::class, 'about'])->name('public.about');
    Route::get('/contact', [PublicController::class, 'contact'])->name('public.contact');
    Route::get('/department_details', [PublicController::class, 'department_details'])->name('public.department_details');
    Route::get('/department', [PublicController::class, 'department'])->name('public.department');
    Route::get('/doctor-list', [PublicController::class, 'doctors'])->name('public.doctor-list');
    Route::get('/faq', [PublicController::class, 'faq'])->name('public.faq');
    Route::get('/gallery', [PublicController::class, 'gallery'])->name('public.gallery');
    Route::get('/gallery_details', [PublicController::class, 'gallery_details'])->name('public.gallery_details');
    Route::get('/privacy', [PublicController::class, 'privacy'])->name('public.privacy');
    Route::get('/service_details/{service:slug}', [PublicController::class, 'service_details'])->name('public.service_details');
    Route::get('/service', [PublicController::class, 'services'])->name('public.services');
    Route::get('/starter_page', [PublicController::class, 'starter_page'])->name('public.starter_page');
    Route::get('/terms', [PublicController::class, 'terms'])->name('public.terms');
    Route::get('/testimonial', [PublicController::class, 'testimonial'])->name('public.testimonial');
});

// Public Appointment Request (no authentication required)
Route::get('/appointment', [PublicAppointmentController::class, 'create'])
    ->name('public.appointment.create');
Route::post('/appointment', [PublicAppointmentController::class, 'store'])
    ->name('public.appointment.store');
Route::get('/appointment/success', [PublicAppointmentController::class, 'success'])
    ->name('public.appointment.success');

// Public availability lookups (availability-first booking, no authentication)
Route::get('/appointment/doctors', [PublicAppointmentController::class, 'doctors'])
    ->name('public.appointment.doctors');
Route::get('/appointment/availability', [PublicAppointmentController::class, 'availability'])
    ->name('public.appointment.availability');

// Contact Form
Route::post('/contact', [ContactController::class, 'store'])
    ->name('public.contact.store');


Route::get('/dashboard', [DashboardController::class, 'index'])->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware(['auth'])->group(function () {
    Route::resource('roles', RoleController::class);
    Route::resource('users', UserController::class);
    Route::resource('doctors', DoctorController::class);
    Route::post('/doctors/{doctor}/unavailable-dates', [DoctorController::class, 'storeUnavailableDate'])
        ->name('doctors.unavailable-dates.store');
    Route::delete('/doctors/{doctor}/unavailable-dates/{unavailableDate}', [DoctorController::class, 'destroyUnavailableDate'])
        ->name('doctors.unavailable-dates.destroy');
    Route::resource('departments', DepartmentController::class);
    Route::resource('locations', LocationController::class);
    Route::resource('services', ServiceController::class);

    Route::resource('patients', PatientController::class);

    Route::get('/patients/{patient}/appointments-json', [PatientController::class, 'appointmentsJson'])
        ->name('patients.appointments-json');

    Route::post('patients/{patient}/restore', [PatientController::class, 'restore'])
        ->name('patients.restore');

    Route::get('patients/{patient}/medical-record', [MedicalRecordController::class, 'show'])
        ->name('patients.medical-record');

    Route::get('/appointments/availability', [AppointmentController::class, 'availableSlots'])
        ->name('appointments.availability');

    Route::resource('appointments', AppointmentController::class);

    Route::post('/appointments/{appointment}/confirm', [AppointmentController::class, 'confirm'])
        ->name('appointments.confirm');

    Route::post('/appointments/{appointment}/cancel', [AppointmentController::class, 'cancel'])
        ->name('appointments.cancel');

    Route::post('/appointments/{appointment}/complete', [AppointmentController::class, 'complete'])
        ->name('appointments.complete');

    Route::post('/appointments/{appointment}/status', [AppointmentController::class, 'updateStatus'])
        ->name('appointments.status');

    Route::get('/doctors/available', [DoctorController::class, 'availableDoctors'])
        ->name('doctors.available');

    // Queue Management
    Route::get('/queue', [QueueController::class, 'index'])->name('queue.index');
    Route::get('/queue/checkin', [QueueController::class, 'checkinForm'])->name('queue.checkin.form');
    Route::post('/queue/checkin', [QueueController::class, 'checkin'])->name('queue.checkin');
    Route::get('/queue/walkin', [QueueController::class, 'walkinForm'])->name('queue.walkin.form');
    Route::post('/queue/walkin', [QueueController::class, 'walkin'])->name('queue.walkin');
    Route::post('/queue/call-next', [QueueController::class, 'callNext'])->name('queue.call-next');
    Route::post('/queue/{ticket}/call', [QueueController::class, 'callTicket'])->name('queue.call-ticket');
    Route::post('/queue/{ticket}/start-consultation', [QueueController::class, 'startConsultation'])->name('queue.start-consultation');
    Route::post('/queue/{ticket}/cancel', [QueueController::class, 'cancelTicket'])->name('queue.cancel-ticket');
    Route::get('/queue/appointments', [QueueController::class, 'appointments'])->name('queue.appointments');

    // Consultation Management
    Route::resource('consultations', ConsultationController::class)->except(['destroy']);
    Route::post('/consultations/{consultation}/complete', [ConsultationController::class, 'complete'])
        ->name('consultations.complete');

    // Medicine Management
    Route::resource('medicines', MedicineController::class);

    // Prescription Management
    Route::resource('prescriptions', PrescriptionController::class);

    // Lab Test Catalog
    Route::resource('lab-tests', LabTestController::class);

    // Investigations
    Route::resource('investigations', InvestigationController::class)->except(['update']);
    Route::put('investigations/{investigation}', [InvestigationController::class, 'update'])
        ->name('investigations.update');
    Route::post('investigations/{investigation}/status', [InvestigationController::class, 'updateStatus'])
        ->name('investigations.status');
    Route::post('investigations/{investigation}/result', [InvestigationController::class, 'enterResult'])
        ->name('investigations.result');

    // Communications
    Route::resource('communications', CommunicationController::class)->except(['create', 'edit']);
    Route::post('/communications/{communication}/complete-follow-up', [CommunicationController::class, 'completeFollowUp'])
        ->name('communications.complete-follow-up');
    Route::get('/communications/patient/{patient}', [CommunicationController::class, 'patientCommunications'])
        ->name('communications.patient');
    Route::get('/follow-ups', [CommunicationController::class, 'followUps'])->name('communications.follow-ups');

    // Inventory Management
    Route::get('/inventory', [InventoryController::class, 'dashboard'])->name('inventory.dashboard');
    Route::get('/inventory/medicines', [InventoryController::class, 'index'])->name('inventory.index');
    Route::get('/inventory/movements', [InventoryController::class, 'movements'])->name('inventory.movements');
    Route::get('/inventory/expiry', [InventoryController::class, 'expiry'])->name('inventory.expiry');
    Route::get('/inventory/medicines/{medicine}/stock-in', [InventoryController::class, 'stockInForm'])->name('inventory.stock-in.form');
    Route::post('/inventory/medicines/{medicine}/stock-in', [InventoryController::class, 'stockIn'])->name('inventory.stock-in');
    Route::get('/inventory/medicines/{medicine}/stock-out', [InventoryController::class, 'stockOutForm'])->name('inventory.stock-out.form');
    Route::post('/inventory/medicines/{medicine}/stock-out', [InventoryController::class, 'stockOut'])->name('inventory.stock-out');
    Route::get('/inventory/medicines/{medicine}/adjust', [InventoryController::class, 'adjustForm'])->name('inventory.adjust.form');
    Route::post('/inventory/medicines/{medicine}/adjust', [InventoryController::class, 'adjust'])->name('inventory.adjust');
    Route::post('/inventory/batches/{batch}/expire', [InventoryController::class, 'expireBatch'])->name('inventory.batch.expire');
    Route::delete('/inventory/batches/{batch}', [InventoryController::class, 'destroyBatch'])->name('inventory.batch.destroy');
    Route::get('/inventory/prescriptions/{prescription}/dispense', [InventoryController::class, 'dispenseForm'])->name('inventory.dispense.form');
    Route::post('/inventory/prescriptions/{prescription}/dispense', [InventoryController::class, 'dispense'])->name('inventory.dispense');

    // Billing
    Route::resource('invoices', InvoiceController::class);
    Route::post('/invoices/{invoice}/issue', [InvoiceController::class, 'issue'])->name('invoices.issue');
    Route::post('/invoices/{invoice}/cancel', [InvoiceController::class, 'cancel'])->name('invoices.cancel');
    Route::post('/invoices/{invoice}/add-medicine-items', [InvoiceController::class, 'addMedicineItems'])->name('invoices.add-medicine-items');

    Route::resource('payments', PaymentController::class)->except(['edit', 'update']);
    Route::get('/payments/{payment}/receipt', [PaymentController::class, 'receipt'])->name('payments.receipt');

    // Expense Management
    Route::resource('expenses', ExpenseController::class);
    Route::resource('expense-categories', ExpenseCategoryController::class)->except(['create', 'edit', 'show']);

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Account-level user settings
    Route::get('/settings/account', [UserSettingController::class, 'index'])->name('user.settings');
    Route::post('/settings/account', [UserSettingController::class, 'store'])->name('user.settings.store');

    // Staff Management
    Route::resource('staff', StaffController::class);
    Route::patch('/staff/{staff}/toggle-status', [StaffController::class, 'toggleStatus'])
        ->name('staff.toggle-status');

    // Settings
    Route::get('settings', [\App\Http\Controllers\SettingController::class, 'index'])->name('settings.index');
    Route::get('settings/website', [SettingController::class, 'edit'])->name('settings.website.edit');
    Route::post('settings/website', [SettingController::class, 'update'])->name('settings.website.update');
    Route::get('settings/clinic', [SettingController::class, 'clinic'])->name('settings.clinic');
    Route::post('settings/clinic', [SettingController::class, 'updateClinic'])->name('settings.clinic.update');
    Route::get('settings/appointment', [SettingController::class, 'appointment'])->name('settings.appointment');
    Route::post('settings/appointment', [SettingController::class, 'updateAppointment'])->name('settings.appointment.update');
    Route::get('settings/queue', [SettingController::class, 'queue'])->name('settings.queue');
    Route::post('settings/queue', [SettingController::class, 'updateQueue'])->name('settings.queue.update');
    Route::get('settings/billing', [SettingController::class, 'billing'])->name('settings.billing');
    Route::post('settings/billing', [SettingController::class, 'updateBilling'])->name('settings.billing.update');
    Route::get('settings/inventory', [SettingController::class, 'inventory'])->name('settings.inventory');
    Route::post('settings/inventory', [SettingController::class, 'updateInventory'])->name('settings.inventory.update');
    Route::get('settings/prescription', [SettingController::class, 'prescription'])->name('settings.prescription');
    Route::post('settings/prescription', [SettingController::class, 'updatePrescription'])->name('settings.prescription.update');

    // Backup & Restore
    Route::get('/backups', [\App\Http\Controllers\BackupController::class, 'index'])->name('backups.index');
    Route::post('/backups', [\App\Http\Controllers\BackupController::class, 'store'])->name('backups.store');
    Route::get('/backups/{backup}', [\App\Http\Controllers\BackupController::class, 'show'])->name('backups.show');
    Route::get('/backups/{backup}/download', [\App\Http\Controllers\BackupController::class, 'download'])->name('backups.download');
    Route::post('/backups/{backup}/validate', [\App\Http\Controllers\BackupController::class, 'validateBackup'])->name('backups.validate');
    Route::post('/backups/{backup}/restore', [\App\Http\Controllers\BackupController::class, 'restore'])->name('backups.restore');
    Route::delete('/backups/{backup}', [\App\Http\Controllers\BackupController::class, 'destroy'])->name('backups.destroy');

    // Notification Center
    Route::get('/notifications', [NotificationCenterController::class, 'index'])->name('notifications.index');
    Route::get('/notifications/{notification}', [NotificationCenterController::class, 'show'])->name('notifications.show');
    Route::post('/notifications/{notification}/read', [NotificationCenterController::class, 'markRead'])->name('notifications.mark-read');
    Route::post('/notifications/{notification}/unread', [NotificationCenterController::class, 'markUnread'])->name('notifications.mark-unread');
    Route::post('/notifications/read-all', [NotificationCenterController::class, 'markAllRead'])->name('notifications.mark-all-read');
    Route::get('/notifications/unread/count', [NotificationCenterController::class, 'unreadCount'])->name('notifications.unread-count');
    Route::delete('/notifications/{notification}', [NotificationCenterController::class, 'destroy'])->name('notifications.destroy');

    // Audit Logs
    Route::get('/audit-logs', [\App\Http\Controllers\AuditController::class, 'index'])->name('audit-logs.index');
    Route::get('/audit-logs/{auditLog}', [\App\Http\Controllers\AuditController::class, 'show'])->name('audit-logs.show');

    // Reports
    Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
    Route::get('/reports/patients', [ReportController::class, 'patient'])->name('reports.patient');
    Route::get('/reports/appointments', [ReportController::class, 'appointment'])->name('reports.appointment');
    Route::get('/reports/consultations', [ReportController::class, 'consultation'])->name('reports.consultation');
    Route::get('/reports/financial', [ReportController::class, 'financial'])->name('reports.financial');
    Route::get('/reports/financial/export', [ReportController::class, 'financialExport'])->name('reports.financial.export');
    Route::get('/reports/expense', [ReportController::class, 'expenseReport'])->name('reports.expense');
    Route::get('/reports/profit', [ReportController::class, 'profitReport'])->name('reports.profit');
    Route::get('/reports/payment-method', [ReportController::class, 'paymentMethodReport'])->name('reports.payment-method');
    Route::get('/reports/inventory', [ReportController::class, 'inventory'])->name('reports.inventory');

    // Analytics
    Route::get('/analytics', [\App\Http\Controllers\AnalyticsController::class, 'index'])->name('analytics.index');
    Route::get('/analytics/export/{type}', [\App\Http\Controllers\AnalyticsController::class, 'export'])->name('analytics.export');

    // Print / Document Output
    Route::get('/print/appointments/{appointment}', [\App\Http\Controllers\PrintController::class, 'appointment'])->name('print.appointment');
    Route::get('/print/queue/{ticket}', [\App\Http\Controllers\PrintController::class, 'queueTicket'])->name('print.queue-ticket');
    Route::get('/print/prescriptions/{prescription}', [\App\Http\Controllers\PrintController::class, 'prescription'])->name('print.prescription');
    Route::get('/print/investigations/{investigation}', [\App\Http\Controllers\PrintController::class, 'investigation'])->name('print.investigation');
    Route::get('/print/invoices/{invoice}', [\App\Http\Controllers\PrintController::class, 'invoice'])->name('print.invoice');
    Route::get('/print/payments/{payment}/receipt', [\App\Http\Controllers\PrintController::class, 'receipt'])->name('print.receipt');
    Route::get('/print/patients/{patient}/medical-record', [\App\Http\Controllers\PrintController::class, 'medicalRecord'])->name('print.medical-record');
    Route::get('/print/reports/{type}', [\App\Http\Controllers\PrintController::class, 'report'])->name('print.report');
});

require __DIR__ . '/auth.php';
