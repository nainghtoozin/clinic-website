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
use App\Models\StockMovement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

class ReportController extends Controller
{
    public function index()
    {
        Gate::authorize('dashboard.view');
        return view('reports.index');
    }

    public function patient(Request $request)
    {
        Gate::authorize('report.patient');

        $query = Patient::query();

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('gender')) {
            $query->where('gender', $request->gender);
        }
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('patient_number', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        $totalPatients = $query->count();
        $newToday = (clone $query)->whereDate('created_at', now()->toDateString())->count();
        $activePatients = Patient::active()->count();

        $patients = $query->orderBy('created_at', 'desc')->paginate(20)->withQueryString();

        return view('reports.patient', compact('patients', 'totalPatients', 'newToday', 'activePatients'));
    }

    public function appointment(Request $request)
    {
        Gate::authorize('report.appointment');

        $query = Appointment::with(['patient', 'doctor']);

        if ($request->filled('date_from')) {
            $query->whereDate('date', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('date', '<=', $request->date_to);
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('doctor_id')) {
            $query->where('doctor_id', $request->doctor_id);
        }
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('appointment_number', 'like', "%{$search}%");
            });
        }

        $totalAppointments = $query->count();
        $completedCount = (clone $query)->where('status', AppointmentStatus::Completed)->count();
        $cancelledCount = (clone $query)->where('status', AppointmentStatus::Cancelled)->count();
        $scheduledCount = (clone $query)->where('status', AppointmentStatus::Scheduled)->count();

        $appointments = $query->orderBy('date', 'desc')->orderBy('time')->paginate(20)->withQueryString();
        $doctors = Doctor::orderBy('name')->pluck('name', 'id');

        return view('reports.appointment', compact(
            'appointments', 'totalAppointments', 'completedCount', 'cancelledCount',
            'scheduledCount', 'doctors'
        ));
    }

    public function consultation(Request $request)
    {
        Gate::authorize('report.consultation');

        $query = Consultation::with(['patient', 'doctor']);

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }
        if ($request->filled('doctor_id')) {
            $query->where('doctor_id', $request->doctor_id);
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $totalConsultations = $query->count();
        $completedCount = (clone $query)->where('status', 'completed')->count();
        $draftCount = (clone $query)->where('status', 'draft')->count();

        $consultations = $query->orderBy('created_at', 'desc')->paginate(20)->withQueryString();
        $doctors = Doctor::orderBy('name')->pluck('name', 'id');

        return view('reports.consultation', compact(
            'consultations', 'totalConsultations', 'completedCount', 'draftCount', 'doctors'
        ));
    }

    public function financial(Request $request)
    {
        Gate::authorize('report.financial');

        $query = Invoice::query();
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $totalInvoiced = (clone $query)->whereNotIn('status', ['cancelled'])->sum('total');
        $totalPaid = (clone $query)->sum('amount_paid');
        $totalOutstanding = (clone $query)->whereIn('status', ['issued', 'partially_paid'])->sum('balance');

        $paymentQuery = Payment::query();
        if ($request->filled('date_from')) {
            $paymentQuery->whereDate('paid_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $paymentQuery->whereDate('paid_at', '<=', $request->date_to);
        }
        if ($request->filled('payment_method')) {
            $paymentQuery->where('payment_method', $request->payment_method);
        }

        $totalPayments = (clone $paymentQuery)->sum('amount');
        $paymentCount = (clone $paymentQuery)->count();
        $paymentsByMethod = (clone $paymentQuery)
            ->select('payment_method', DB::raw('SUM(amount) as total'), DB::raw('COUNT(*) as count'))
            ->groupBy('payment_method')
            ->get();

        $invoices = $query->with(['patient', 'doctor'])->orderBy('created_at', 'desc')->paginate(20)->withQueryString();

        return view('reports.financial', compact(
            'invoices', 'totalInvoiced', 'totalPaid', 'totalOutstanding',
            'totalPayments', 'paymentCount', 'paymentsByMethod'
        ));
    }

    public function inventory(Request $request)
    {
        Gate::authorize('report.inventory');

        $query = Medicine::query();

        if ($request->filled('stock_status')) {
            $status = $request->stock_status;
            if ($status === 'low') {
                $query->whereColumn('stock_quantity', '<=', 'minimum_stock_level');
            } elseif ($status === 'out') {
                $query->where('stock_quantity', 0);
            } elseif ($status === 'expired') {
                $query->where('expiry_date', '<', now());
            } elseif ($status === 'expiring') {
                $query->where('expiry_date', '>=', now())
                      ->where('expiry_date', '<=', now()->addDays(30));
            }
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('generic_name', 'like', "%{$search}%");
            });
        }

        $totalMedicines = Medicine::count();
        $lowStock = Medicine::whereColumn('stock_quantity', '<=', 'minimum_stock_level')->where('is_active', true)->count();
        $outOfStock = Medicine::where('stock_quantity', 0)->where('is_active', true)->count();
        $expired = Medicine::where('expiry_date', '<', now())->where('is_active', true)->count();
        $expiringSoon = Medicine::where('expiry_date', '>=', now())
            ->where('expiry_date', '<=', now()->addDays(30))->where('is_active', true)->count();

        $medicines = $query->orderBy('name')->paginate(20)->withQueryString();

        $recentMovements = StockMovement::with(['medicine', 'performer'])
            ->latest('movement_date')->latest('id')->take(20)->get();

        return view('reports.inventory', compact(
            'medicines', 'totalMedicines', 'lowStock', 'outOfStock', 'expired',
            'expiringSoon', 'recentMovements'
        ));
    }
}
