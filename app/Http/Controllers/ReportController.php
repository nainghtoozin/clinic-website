<?php

namespace App\Http\Controllers;

use App\Enums\AppointmentStatus;
use App\Models\Appointment;
use App\Models\Consultation;
use App\Models\Doctor;
use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Models\Invoice;
use App\Models\InvoiceItem;
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

        $dateFrom = $request->get('date_from', now()->startOfMonth()->toDateString());
        $dateTo = $request->get('date_to', now()->toDateString());
        $status = $request->get('status');

        $invoiceQuery = Invoice::query();
        $invoiceQuery->whereBetween('created_at', [$dateFrom . ' 00:00:00', $dateTo . ' 23:59:59']);
        if ($status) {
            $invoiceQuery->where('status', $status);
        }

        $totalInvoiced = (clone $invoiceQuery)->whereNotIn('status', ['cancelled'])->sum('total');
        $totalPaid = (clone $invoiceQuery)->sum('amount_paid');
        $totalOutstanding = (clone $invoiceQuery)->whereIn('status', ['issued', 'partially_paid'])->sum('balance');
        $cancelledTotal = (clone $invoiceQuery)->where('status', 'cancelled')->sum('total');
        $invoiceCount = (clone $invoiceQuery)->count();

        $paymentQuery = Payment::query();
        $paymentQuery->whereBetween('paid_at', [$dateFrom . ' 00:00:00', $dateTo . ' 23:59:59']);
        $totalPayments = (clone $paymentQuery)->sum('amount');
        $paymentCount = (clone $paymentQuery)->count();
        $paymentsByMethod = (clone $paymentQuery)
            ->select('payment_method', DB::raw('SUM(amount) as total'), DB::raw('COUNT(*) as count'))
            ->groupBy('payment_method')
            ->get();

        $expenseQuery = Expense::active();
        $expenseQuery->whereBetween('expense_date', [$dateFrom, $dateTo]);
        $totalExpenses = (clone $expenseQuery)->sum('amount');
        $expenseCount = (clone $expenseQuery)->count();
        $expensesByCategory = (clone $expenseQuery)
            ->join('expense_categories', 'expenses.expense_category_id', '=', 'expense_categories.id')
            ->select('expense_categories.name as category_name', DB::raw('SUM(expenses.amount) as total'), DB::raw('COUNT(*) as count'))
            ->groupBy('expense_categories.name')
            ->get();

        $netIncome = $totalPayments - $totalExpenses;

        $revenueBySource = InvoiceItem::whereHas('invoice', function ($q) use ($dateFrom, $dateTo) {
            $q->whereBetween('created_at', [$dateFrom . ' 00:00:00', $dateTo . ' 23:59:59'])
              ->whereNotIn('status', ['cancelled']);
        })
            ->select('type', DB::raw('SUM(total) as total'), DB::raw('COUNT(*) as count'))
            ->groupBy('type')
            ->get();

        $invoices = $invoiceQuery->with(['patient', 'doctor'])->orderBy('created_at', 'desc')->paginate(20)->withQueryString();

        return view('reports.financial', compact(
            'invoices', 'totalInvoiced', 'totalPaid', 'totalOutstanding', 'cancelledTotal',
            'totalPayments', 'paymentCount', 'paymentsByMethod',
            'totalExpenses', 'expenseCount', 'expensesByCategory',
            'netIncome', 'revenueBySource', 'invoiceCount',
            'dateFrom', 'dateTo'
        ));
    }

    public function financialExport(Request $request)
    {
        Gate::authorize('report.financial');

        $dateFrom = $request->get('date_from', now()->startOfMonth()->toDateString());
        $dateTo = $request->get('date_to', now()->toDateString());

        $invoices = Invoice::with(['patient', 'doctor'])
            ->whereBetween('created_at', [$dateFrom . ' 00:00:00', $dateTo . ' 23:59:59'])
            ->orderBy('created_at', 'desc')
            ->get();

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="financial-report-' . $dateFrom . '-to-' . $dateTo . '.csv"',
        ];

        $callback = function () use ($invoices, $dateFrom, $dateTo) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Financial Report', $dateFrom, 'to', $dateTo]);
            fputcsv($handle, []);
            fputcsv($handle, ['Invoice #', 'Patient', 'Date', 'Status', 'Total', 'Paid', 'Balance']);

            foreach ($invoices as $invoice) {
                fputcsv($handle, [
                    $invoice->invoice_number,
                    $invoice->patient->name ?? '-',
                    $invoice->created_at->format('Y-m-d'),
                    $invoice->status,
                    number_format($invoice->total, 2),
                    number_format($invoice->amount_paid, 2),
                    number_format($invoice->balance, 2),
                ]);
            }

            fputcsv($handle, []);
            fputcsv($handle, ['Total Invoiced', number_format($invoices->sum('total'), 2)]);
            fputcsv($handle, ['Total Paid', number_format($invoices->sum('amount_paid'), 2)]);
            fputcsv($handle, ['Outstanding', number_format($invoices->whereNotIn('status', ['cancelled', 'paid'])->sum('balance'), 2)]);

            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function expenseReport(Request $request)
    {
        Gate::authorize('report.financial');

        $dateFrom = $request->get('date_from', now()->startOfMonth()->toDateString());
        $dateTo = $request->get('date_to', now()->toDateString());
        $categoryId = $request->get('category_id');

        $query = Expense::with(['expenseCategory', 'createdBy'])
            ->whereBetween('expense_date', [$dateFrom, $dateTo])
            ->where('status', 'active');

        if ($categoryId) {
            $query->where('expense_category_id', $categoryId);
        }

        $expenses = $query->orderBy('expense_date', 'desc')->paginate(20)->withQueryString();

        $totalExpenses = (clone $query)->without('createdBy')->sum('amount');
        $byCategory = Expense::select('expense_category_id', DB::raw('SUM(amount) as total'), DB::raw('COUNT(*) as count'))
            ->whereBetween('expense_date', [$dateFrom, $dateTo])
            ->where('status', 'active')
            ->groupBy('expense_category_id')
            ->join('expense_categories', 'expenses.expense_category_id', '=', 'expense_categories.id')
            ->select('expense_categories.name as category_name', 'expenses.expense_category_id', DB::raw('SUM(expenses.amount) as total'), DB::raw('COUNT(*) as count'))
            ->groupBy('expense_categories.name', 'expenses.expense_category_id')
            ->get();

        $categories = ExpenseCategory::active()->orderBy('name')->get();

        return view('reports.expense', compact(
            'expenses', 'totalExpenses', 'byCategory', 'categories', 'dateFrom', 'dateTo'
        ));
    }

    public function profitReport(Request $request)
    {
        Gate::authorize('report.financial');

        $dateFrom = $request->get('date_from', now()->startOfMonth()->toDateString());
        $dateTo = $request->get('date_to', now()->toDateString());

        $totalRevenue = Payment::whereBetween('paid_at', [$dateFrom . ' 00:00:00', $dateTo . ' 23:59:59'])
            ->sum('amount');

        $totalExpenses = Expense::active()
            ->whereBetween('expense_date', [$dateFrom, $dateTo])
            ->sum('amount');

        $netIncome = $totalRevenue - $totalExpenses;

        $dailyRevenue = Payment::whereBetween('paid_at', [$dateFrom . ' 00:00:00', $dateTo . ' 23:59:59'])
            ->select(DB::raw('DATE(paid_at) as date'), DB::raw('SUM(amount) as total'))
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        $dailyExpenses = Expense::active()
            ->whereBetween('expense_date', [$dateFrom, $dateTo])
            ->select('expense_date as date', DB::raw('SUM(amount) as total'))
            ->groupBy('expense_date')
            ->orderBy('expense_date')
            ->get();

        $dailyData = collect();
        $startDate = \Carbon\Carbon::parse($dateFrom);
        $endDate = \Carbon\Carbon::parse($dateTo);
        while ($startDate->lte($endDate)) {
            $dateStr = $startDate->toDateString();
            $rev = $dailyRevenue->firstWhere('date', $dateStr)?->total ?? 0;
            $exp = $dailyExpenses->firstWhere('date', $dateStr)?->total ?? 0;
            $dailyData->push([
                'date' => $dateStr,
                'revenue' => $rev,
                'expenses' => $exp,
                'net' => $rev - $exp,
            ]);
            $startDate->addDay();
        }

        return view('reports.profit', compact(
            'totalRevenue', 'totalExpenses', 'netIncome', 'dailyData', 'dateFrom', 'dateTo'
        ));
    }

    public function paymentMethodReport(Request $request)
    {
        Gate::authorize('report.financial');

        $dateFrom = $request->get('date_from', now()->startOfMonth()->toDateString());
        $dateTo = $request->get('date_to', now()->toDateString());

        $paymentsByMethod = Payment::whereBetween('paid_at', [$dateFrom . ' 00:00:00', $dateTo . ' 23:59:59'])
            ->select('payment_method', DB::raw('SUM(amount) as total'), DB::raw('COUNT(*) as count'))
            ->groupBy('payment_method')
            ->orderBy('total', 'desc')
            ->get();

        $totalPayments = $paymentsByMethod->sum('total');
        $totalCount = $paymentsByMethod->sum('count');

        return view('reports.payment-method', compact(
            'paymentsByMethod', 'totalPayments', 'totalCount', 'dateFrom', 'dateTo'
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
