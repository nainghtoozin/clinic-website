<?php

namespace App\Http\Controllers;

use App\Enums\AppointmentStatus;
use App\Models\Appointment;
use App\Models\Consultation;
use App\Models\Doctor;
use App\Models\Invoice;
use App\Models\Medicine;
use App\Models\Payment;
use App\Models\Patient;
use App\Models\Prescription;
use App\Models\QueueTicket;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

class DashboardController extends Controller
{
    public function index()
    {
        Gate::authorize('dashboard.view');

        $today = now()->toDateString();

        // Appointment KPIs
        $todayAppointments = Appointment::whereDate('date', $today)->count();
        $appointmentsScheduled = Appointment::whereDate('date', $today)
            ->where('status', AppointmentStatus::Scheduled)->count();
        $appointmentsConfirmed = Appointment::whereDate('date', $today)
            ->where('status', AppointmentStatus::Confirmed)->count();
        $appointmentsCheckedIn = Appointment::whereDate('date', $today)
            ->where('status', AppointmentStatus::CheckedIn)->count();
        $appointmentsCompleted = Appointment::whereDate('date', $today)
            ->where('status', AppointmentStatus::Completed)->count();
        $appointmentsCancelled = Appointment::whereDate('date', $today)
            ->where('status', AppointmentStatus::Cancelled)->count();

        // Queue KPIs
        $queueWaiting = QueueTicket::today()->where('status', 'waiting')->count();
        $queueCalled = QueueTicket::today()->where('status', 'called')->count();
        $queueInConsultation = QueueTicket::today()->where('status', 'in_consultation')->count();
        $queueCompleted = QueueTicket::today()->where('status', 'completed')->count();

        // Patient KPIs
        $todayNewPatients = Patient::whereDate('created_at', $today)->count();
        $totalActivePatients = Patient::active()->count();
        $totalPatients = Patient::count();

        // Consultation KPIs
        $todayConsultations = Consultation::whereDate('created_at', $today)->count();
        $completedConsultations = Consultation::whereDate('created_at', $today)
            ->where('status', 'completed')->count();

        // Overall KPIs
        $totalAppointments = Appointment::whereNotIn('status', [AppointmentStatus::Cancelled->value])->count();
        $availableDoctors = Doctor::where('is_available', true)->count();
        $prescriptionsToday = Prescription::whereDate('created_at', $today)->count();

        // Financial KPIs (admin only — checked in view)
        $todayInvoiced = Invoice::whereDate('created_at', $today)
            ->whereNotIn('status', ['cancelled'])->sum('total');
        $todayPaid = Payment::whereDate('paid_at', $today)->sum('amount');
        $outstandingBalance = Invoice::whereIn('status', ['issued', 'partially_paid'])->sum('balance');
        $currentOutstandingInvoices = Invoice::whereIn('status', ['issued', 'partially_paid'])->count();

        // Inventory KPIs
        $lowStockCount = Medicine::whereColumn('stock_quantity', '<=', 'minimum_stock_level')
            ->where('is_active', true)->count();
        $expiredCount = Medicine::where('expiry_date', '<', now())
            ->where('is_active', true)->count();
        $expiringSoonCount = Medicine::where('expiry_date', '>=', now())
            ->where('expiry_date', '<=', now()->addDays(30))
            ->where('is_active', true)->count();
        $totalActiveMedicines = Medicine::where('is_active', true)->count();

        // Today's appointments list
        $todayAppointmentsList = Appointment::with(['patient', 'doctor'])
            ->whereDate('date', $today)
            ->orderBy('time')
            ->get();

        // Doctor summary for today
        $doctors = Doctor::with('department')->where('is_available', true)->get();
        $doctorIds = $doctors->pluck('id');

        $apptCounts = Appointment::whereIn('doctor_id', $doctorIds)
            ->whereDate('date', $today)
            ->selectRaw('doctor_id, COUNT(*) as total, SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as completed', [AppointmentStatus::Completed->value])
            ->groupBy('doctor_id')
            ->get()
            ->keyBy('doctor_id');

        $consultCounts = Consultation::whereIn('doctor_id', $doctorIds)
            ->whereDate('created_at', $today)
            ->selectRaw('doctor_id, COUNT(*) as total')
            ->groupBy('doctor_id')
            ->get()
            ->keyBy('doctor_id');

        $doctorSummary = $doctors->map(function ($doctor) use ($apptCounts, $consultCounts) {
            $doctor->today_appointments = $apptCounts[$doctor->id]->total ?? 0;
            $doctor->today_completed = $apptCounts[$doctor->id]->completed ?? 0;
            $doctor->today_consultations = $consultCounts[$doctor->id]->total ?? 0;
            return $doctor;
        });

        return view('dashboard', compact(
            'todayAppointments',
            'appointmentsScheduled',
            'appointmentsConfirmed',
            'appointmentsCheckedIn',
            'appointmentsCompleted',
            'appointmentsCancelled',
            'queueWaiting',
            'queueCalled',
            'queueInConsultation',
            'queueCompleted',
            'todayNewPatients',
            'totalActivePatients',
            'totalPatients',
            'todayConsultations',
            'completedConsultations',
            'totalAppointments',
            'availableDoctors',
            'prescriptionsToday',
            'todayInvoiced',
            'todayPaid',
            'outstandingBalance',
            'currentOutstandingInvoices',
            'lowStockCount',
            'expiredCount',
            'expiringSoonCount',
            'totalActiveMedicines',
            'todayAppointmentsList',
            'doctorSummary',
        ));
    }
}
