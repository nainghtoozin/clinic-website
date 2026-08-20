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
use App\Services\UserSettingsService;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Validator;

class DashboardController extends Controller
{
    public function __construct(
        protected UserSettingsService $settings
    ) {
    }

    public function index(Request $request)
    {
        Gate::authorize('dashboard.view');

        $validator = Validator::make($request->all(), [
            'period' => ['nullable', 'in:today,yesterday,this_week,this_month'],
            'date_from' => ['nullable', 'required_with:date_to', 'date_format:Y-m-d'],
            'date_to' => ['nullable', 'required_with:date_from', 'date_format:Y-m-d'],
        ]);

        $today = now()->toDateString();

        // Malformed / partial filters fall back to the clean dashboard URL.
        if ($validator->fails()) {
            return redirect()->route('dashboard');
        }

        $data = $validator->validated();
        $period = $data['period'] ?? null;
        $dateFrom = $data['date_from'] ?? null;
        $dateTo = $data['date_to'] ?? null;

        // A custom range must supply both dates.
        if (($dateFrom === null) !== ($dateTo === null)) {
            return redirect()->route('dashboard');
        }

        if ($dateFrom !== null && $dateTo !== null) {
            if ($dateFrom > $dateTo) {
                return redirect()->route('dashboard')
                    ->withErrors(['date_range' => __('app.dashboard.date_range_invalid')])
                    ->withInput();
            }
        } elseif ($period !== null) {
            [$dateFrom, $dateTo] = $this->resolvePreset($period, $today);
        } else {
            $dateFrom = $today;
            $dateTo = $today;
        }

        $selectedPeriod = $period;
        $dateFromLabel = Carbon::parse($dateFrom)->format('M d, Y');
        $dateToLabel = Carbon::parse($dateTo)->format('M d, Y');
        $isToday = $dateFrom === $today && $dateTo === $today;
        $isSingleDay = $dateFrom === $dateTo;

        // Appointment KPIs (date-sensitive)
        $todayAppointments = Appointment::whereDate('date', '>=', $dateFrom)->whereDate('date', '<=', $dateTo)->count();
        $appointmentsScheduled = Appointment::whereDate('date', '>=', $dateFrom)->whereDate('date', '<=', $dateTo)
            ->where('status', AppointmentStatus::Scheduled)->count();
        $appointmentsConfirmed = Appointment::whereDate('date', '>=', $dateFrom)->whereDate('date', '<=', $dateTo)
            ->where('status', AppointmentStatus::Confirmed)->count();
        $appointmentsCheckedIn = Appointment::whereDate('date', '>=', $dateFrom)->whereDate('date', '<=', $dateTo)
            ->where('status', AppointmentStatus::CheckedIn)->count();
        $appointmentsCompleted = Appointment::whereDate('date', '>=', $dateFrom)->whereDate('date', '<=', $dateTo)
            ->where('status', AppointmentStatus::Completed)->count();
        $appointmentsCancelled = Appointment::whereDate('date', '>=', $dateFrom)->whereDate('date', '<=', $dateTo)
            ->where('status', AppointmentStatus::Cancelled)->count();
        // Total appointments in the selected range (cancelled excluded, matching the
        // existing business meaning of this KPI).
        $totalAppointments = Appointment::whereDate('date', '>=', $dateFrom)
            ->whereDate('date', '<=', $dateTo)
            ->whereNotIn('status', [AppointmentStatus::Cancelled->value])
            ->count();

        // Queue KPIs (date-sensitive via queue_date; today → today behaves like
        // the existing current-day queue scope)
        $queueWaiting = QueueTicket::whereDate('queue_date', '>=', $dateFrom)->whereDate('queue_date', '<=', $dateTo)->where('status', 'waiting')->count();
        $queueCalled = QueueTicket::whereDate('queue_date', '>=', $dateFrom)->whereDate('queue_date', '<=', $dateTo)->where('status', 'called')->count();
        $queueInConsultation = QueueTicket::whereDate('queue_date', '>=', $dateFrom)->whereDate('queue_date', '<=', $dateTo)->where('status', 'in_consultation')->count();
        $queueCompleted = QueueTicket::whereDate('queue_date', '>=', $dateFrom)->whereDate('queue_date', '<=', $dateTo)->where('status', 'completed')->count();

        // Patient KPIs
        $todayNewPatients = Patient::whereDate('created_at', '>=', $dateFrom)
            ->whereDate('created_at', '<=', $dateTo)->count();
        $totalActivePatients = Patient::active()->count();
        $totalPatients = Patient::count();

        // Consultation KPIs (date-sensitive)
        $todayConsultations = Consultation::whereDate('created_at', '>=', $dateFrom)
            ->whereDate('created_at', '<=', $dateTo)->count();
        $completedConsultations = Consultation::whereDate('created_at', '>=', $dateFrom)
            ->whereDate('created_at', '<=', $dateTo)
            ->where('status', 'completed')->count();

        // Overall KPIs (lifetime — intentionally not date-filtered)
        $availableDoctors = Doctor::where('is_available', true)->count();
        $prescriptionsToday = Prescription::whereDate('created_at', '>=', $dateFrom)
            ->whereDate('created_at', '<=', $dateTo)->count();

        // Financial KPIs (admin only — checked in view)
        $todayInvoiced = Invoice::whereDate('created_at', '>=', $dateFrom)
            ->whereDate('created_at', '<=', $dateTo)
            ->whereNotIn('status', ['cancelled'])->sum('total');
        $todayPaid = Payment::whereDate('paid_at', '>=', $dateFrom)
            ->whereDate('paid_at', '<=', $dateTo)->sum('amount');
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

        // Appointment list for the selected range
        $todayAppointmentsList = Appointment::with(['patient', 'doctor'])
            ->whereDate('date', '>=', $dateFrom)
            ->whereDate('date', '<=', $dateTo)
            ->orderBy('date')
            ->orderBy('time')
            ->get();

        // Doctor summary for the selected range — dashboard summary only, so load
        // at most 5 doctors. Full roster lives on the Doctors page.
        $doctors = Doctor::with('department')
            ->where('is_available', true)
            ->orderBy('name')
            ->limit(5)
            ->get();
        $doctorIds = $doctors->pluck('id');

        $apptCounts = Appointment::whereIn('doctor_id', $doctorIds)
            ->whereDate('date', '>=', $dateFrom)
            ->whereDate('date', '<=', $dateTo)
            ->selectRaw('doctor_id, COUNT(*) as total, SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as completed', [AppointmentStatus::Completed->value])
            ->groupBy('doctor_id')
            ->get()
            ->keyBy('doctor_id');

        $consultCounts = Consultation::whereIn('doctor_id', $doctorIds)
            ->whereDate('created_at', '>=', $dateFrom)
            ->whereDate('created_at', '<=', $dateTo)
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
            'dateFrom',
            'dateTo',
            'dateFromLabel',
            'dateToLabel',
            'selectedPeriod',
            'isToday',
            'isSingleDay',
        ));
    }

    /**
     * Resolve a quick preset into a [dateFrom, dateTo] range.
     *
     * @return array{0: string, 1: string}
     */
    protected function resolvePreset(string $period, string $today): array
    {
        return match ($period) {
            'yesterday' => [now()->subDay()->toDateString(), now()->subDay()->toDateString()],
            'this_week' => $this->weekRange(),
            'this_month' => [now()->startOfMonth()->toDateString(), now()->endOfMonth()->toDateString()],
            default => [$today, $today],
        };
    }

    /**
     * Current calendar week, respecting the user's week start preference.
     *
     * @return array{0: string, 1: string}
     */
    protected function weekRange(): array
    {
        $weekStart = (string) $this->settings->get(
            auth()->user(),
            'preferences',
            'week_starts_on',
            'sunday'
        );

        $start = now()->startOfWeek($weekStart === 'monday' ? Carbon::MONDAY : Carbon::SUNDAY);
        $end = $start->copy()->addDays(6);

        return [$start->toDateString(), $end->toDateString()];
    }
}
