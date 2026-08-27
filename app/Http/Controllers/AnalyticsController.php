<?php

namespace App\Http\Controllers;

use App\Enums\AppointmentStatus;
use App\Models\Appointment;
use App\Models\Consultation;
use App\Models\Department;
use App\Models\Doctor;
use App\Models\Expense;
use App\Models\Investigation;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Medicine;
use App\Models\Payment;
use App\Models\Patient;
use App\Models\Prescription;
use App\Models\PrescriptionItem;
use App\Models\StockMovement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

class AnalyticsController extends Controller
{
    public function index(Request $request)
    {
        Gate::authorize('dashboard.view');

        $dateFrom = $request->input('date_from', now()->startOfMonth()->toDateString());
        $dateTo = $request->input('date_to', now()->toDateString());

        if ($dateFrom > $dateTo) {
            $dateFrom = $dateTo;
        }

        // Previous period for comparisons
        $prevDateFrom = \Carbon\Carbon::parse($dateFrom)->copy()->subDay()->subDays(
            \Carbon\Carbon::parse($dateFrom)->diffInDays(\Carbon\Carbon::parse($dateTo))
        )->toDateString();
        $prevDateTo = \Carbon\Carbon::parse($dateFrom)->copy()->subDay()->toDateString();

        // ─── PATIENT ANALYTICS ───
        $totalPatients = Patient::count();
        $newPatients = Patient::whereDate('created_at', '>=', $dateFrom)
            ->whereDate('created_at', '<=', $dateTo)->count();
        $prevNewPatients = Patient::whereDate('created_at', '>=', $prevDateFrom)
            ->whereDate('created_at', '<=', $prevDateTo)->count();

        $returningPatients = Patient::whereHas('appointments', function ($q) use ($dateFrom, $dateTo) {
            $q->whereDate('date', '>=', $dateFrom)->whereDate('date', '<=', $dateTo);
        })->whereHas('appointments', function ($q) use ($dateFrom) {
            $q->whereDate('created_at', '<', $dateFrom);
        })->count();

        $activePatients = Patient::active()->count();

        $patientsByGender = Patient::select('gender', DB::raw('COUNT(*) as count'))
            ->groupBy('gender')->get();

        $patientsByBloodGroup = Patient::whereNotNull('blood_group')
            ->where('blood_group', '!=', '')
            ->select('blood_group', DB::raw('COUNT(*) as count'))
            ->groupBy('blood_group')
            ->orderBy('count', 'desc')
            ->get();

        $ageGroups = ['Under 18' => 0, '18-30' => 0, '31-45' => 0, '46-60' => 0, 'Over 60' => 0];
        $dobPatients = Patient::whereNotNull('date_of_birth')->select('date_of_birth')->get();
        foreach ($dobPatients as $p) {
            $age = $p->date_of_birth->age;
            if ($age < 18) $ageGroups['Under 18']++;
            elseif ($age <= 30) $ageGroups['18-30']++;
            elseif ($age <= 45) $ageGroups['31-45']++;
            elseif ($age <= 60) $ageGroups['46-60']++;
            else $ageGroups['Over 60']++;
        }
        $patientsByAgeGroup = collect();
        foreach ($ageGroups as $group => $count) {
            $patientsByAgeGroup->push((object) ['age_group' => $group, 'count' => $count]);
        }

        $patientTrend = Patient::select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('COUNT(*) as count')
            )
            ->whereDate('created_at', '>=', $dateFrom)
            ->whereDate('created_at', '<=', $dateTo)
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        // ─── APPOINTMENT ANALYTICS ───
        $totalAppointments = Appointment::whereDate('date', '>=', $dateFrom)
            ->whereDate('date', '<=', $dateTo)->count();
        $prevTotalAppointments = Appointment::whereDate('date', '>=', $prevDateFrom)
            ->whereDate('date', '<=', $prevDateTo)->count();

        $appointmentsByStatus = Appointment::select('status', DB::raw('COUNT(*) as count'))
            ->whereDate('date', '>=', $dateFrom)
            ->whereDate('date', '<=', $dateTo)
            ->groupBy('status')
            ->get();

        $appointmentsByDoctor = Appointment::selectRaw(
                'doctor_id,
                COUNT(*) as total,
                SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as completed,
                SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as cancelled',
                [AppointmentStatus::Completed->value, AppointmentStatus::Cancelled->value]
            )
            ->whereDate('date', '>=', $dateFrom)
            ->whereDate('date', '<=', $dateTo)
            ->groupBy('doctor_id')
            ->orderBy('total', 'desc')
            ->get()
            ->map(function ($row) {
                $row->doctor = Doctor::find($row->doctor_id);
                return $row;
            });

        $appointmentsByDepartment = Appointment::join('doctors', 'appointments.doctor_id', '=', 'doctors.id')
            ->join('departments', 'doctors.department_id', '=', 'departments.id')
            ->select('departments.name as department_name', DB::raw('COUNT(*) as count'))
            ->whereDate('appointments.date', '>=', $dateFrom)
            ->whereDate('appointments.date', '<=', $dateTo)
            ->groupBy('departments.name')
            ->orderBy('count', 'desc')
            ->get();

        $appointmentTrend = Appointment::select(
                DB::raw('DATE(date) as date'),
                DB::raw('COUNT(*) as count')
            )
            ->whereDate('date', '>=', $dateFrom)
            ->whereDate('date', '<=', $dateTo)
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        // ─── DOCTOR ANALYTICS (single GROUP BY query) ───
        $doctorIds = Doctor::with('department')->where('is_active', true)->pluck('id');
        $apptStats = Appointment::whereBetween('date', [$dateFrom, $dateTo])
            ->whereIn('doctor_id', $doctorIds)
            ->selectRaw('doctor_id,
                COUNT(*) as appointment_count,
                SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as completed_count,
                SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as cancelled_count,
                COUNT(DISTINCT patient_id) as patient_count',
                [AppointmentStatus::Completed->value, AppointmentStatus::Cancelled->value])
            ->groupBy('doctor_id')
            ->get()
            ->keyBy('doctor_id');
        $consultCounts = Consultation::whereBetween('created_at', [$dateFrom, $dateTo])
            ->whereIn('doctor_id', $doctorIds)
            ->selectRaw('doctor_id, COUNT(*) as consultation_count')
            ->groupBy('doctor_id')->get()->keyBy('doctor_id');
        $prescCounts = Prescription::whereBetween('created_at', [$dateFrom, $dateTo])
            ->whereIn('doctor_id', $doctorIds)
            ->selectRaw('doctor_id, COUNT(*) as prescription_count')
            ->groupBy('doctor_id')->get()->keyBy('doctor_id');
        $investCounts = Investigation::whereBetween('created_at', [$dateFrom, $dateTo])
            ->whereIn('doctor_id', $doctorIds)
            ->selectRaw('doctor_id, COUNT(*) as investigation_count')
            ->groupBy('doctor_id')->get()->keyBy('doctor_id');
        $revenueByDoctor = [];
        if (Gate::allows('report.financial')) {
            $revenueByDoctor = Invoice::whereBetween('created_at', [$dateFrom, $dateTo])
                ->whereIn('doctor_id', $doctorIds)
                ->whereNotIn('status', ['cancelled'])
                ->selectRaw('doctor_id, SUM(total) as revenue')
                ->groupBy('doctor_id')->get()->keyBy('doctor_id');
        }
        $doctorStatsAll = Doctor::with('department')->where('is_active', true)->get()
            ->map(function ($doctor) use ($apptStats, $consultCounts, $prescCounts, $investCounts, $revenueByDoctor) {
                $appt = $apptStats[$doctor->id] ?? null;
                $doctor->appointment_count = $appt->appointment_count ?? 0;
                $doctor->completed_count = $appt->completed_count ?? 0;
                $doctor->cancelled_count = $appt->cancelled_count ?? 0;
                $doctor->patient_count = $appt->patient_count ?? 0;
                $doctor->consultation_count = ($consultCounts[$doctor->id] ?? null)->consultation_count ?? 0;
                $doctor->prescription_count = ($prescCounts[$doctor->id] ?? null)->prescription_count ?? 0;
                $doctor->investigation_count = ($investCounts[$doctor->id] ?? null)->investigation_count ?? 0;
                $doctor->revenue = ($revenueByDoctor[$doctor->id] ?? null)->revenue ?? 0;
                return $doctor;
            })
            ->sortByDesc('appointment_count')
            ->values();

        $doctorPage = (int) $request->input('doctor_page', 1);
        $doctorPerPage = 10;
        $doctorTotal = $doctorStatsAll->count();
        $doctorStats = $doctorStatsAll->slice(($doctorPage - 1) * $doctorPerPage, $doctorPerPage)->values();
        $doctorPaginator = new \Illuminate\Pagination\LengthAwarePaginator(
            $doctorStats, $doctorTotal, $doctorPerPage, $doctorPage,
            ['page_name' => 'doctor_page']
        );

        // ─── CONSULTATION ANALYTICS ───
        $totalConsultations = Consultation::whereDate('created_at', '>=', $dateFrom)
            ->whereDate('created_at', '<=', $dateTo)->count();
        $completedConsultations = Consultation::whereDate('created_at', '>=', $dateFrom)
            ->whereDate('created_at', '<=', $dateTo)
            ->where('status', 'completed')->count();
        $draftConsultations = Consultation::whereDate('created_at', '>=', $dateFrom)
            ->whereDate('created_at', '<=', $dateTo)
            ->where('status', 'draft')->count();

        $consultationTrend = Consultation::select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('COUNT(*) as count')
            )
            ->whereDate('created_at', '>=', $dateFrom)
            ->whereDate('created_at', '<=', $dateTo)
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        // ─── DIAGNOSIS ANALYTICS ───
        $topDiagnoses = Consultation::where('status', 'completed')
            ->whereNotNull('diagnosis')
            ->where('diagnosis', '!=', '')
            ->whereDate('created_at', '>=', $dateFrom)
            ->whereDate('created_at', '<=', $dateTo)
            ->select('diagnosis', DB::raw('COUNT(*) as count'))
            ->groupBy('diagnosis')
            ->orderBy('count', 'desc')
            ->limit(15)
            ->get();

        // ─── PRESCRIPTION & MEDICINE ANALYTICS ───
        $totalPrescriptions = Prescription::whereDate('created_at', '>=', $dateFrom)
            ->whereDate('created_at', '<=', $dateTo)->count();

        $medicinePage = (int) $request->input('medicine_page', 1);
        $medicinePerPage = 10;
        $topMedicinesQuery = PrescriptionItem::join('prescriptions', 'prescription_items.prescription_id', '=', 'prescriptions.id')
            ->join('medicines', 'prescription_items.medicine_id', '=', 'medicines.id')
            ->select('medicines.name as medicine_name', DB::raw('SUM(prescription_items.quantity) as total_quantity'), DB::raw('COUNT(*) as prescription_count'))
            ->whereDate('prescriptions.created_at', '>=', $dateFrom)
            ->whereDate('prescriptions.created_at', '<=', $dateTo)
            ->groupBy('medicines.id', 'medicines.name')
            ->orderBy('prescription_count', 'desc');
        $topMedicinesPaginator = $topMedicinesQuery->paginate($medicinePerPage, ['*'], 'medicine_page', $medicinePage);
        $topMedicines = $topMedicinesPaginator;

        $prescriptionTrend = Prescription::select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('COUNT(*) as count')
            )
            ->whereDate('created_at', '>=', $dateFrom)
            ->whereDate('created_at', '<=', $dateTo)
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        // ─── INVENTORY ANALYTICS ───
        $totalMedicines = Medicine::where('is_active', true)->count();
        $lowStockCount = Medicine::whereColumn('stock_quantity', '<=', 'minimum_stock_level')
            ->where('is_active', true)->count();
        $outOfStockCount = Medicine::where('stock_quantity', 0)->where('is_active', true)->count();
        $expiredCount = Medicine::where('expiry_date', '<', now())->where('is_active', true)->count();
        $expiringSoonCount = Medicine::where('expiry_date', '>=', now())
            ->where('expiry_date', '<=', now()->addDays(30))->where('is_active', true)->count();

        $stockMovementsByType = StockMovement::select('type', DB::raw('COUNT(*) as count'), DB::raw('SUM(ABS(quantity)) as total_quantity'))
            ->whereDate('movement_date', '>=', $dateFrom)
            ->whereDate('movement_date', '<=', $dateTo)
            ->groupBy('type')
            ->get();

        $stockMovementTrend = StockMovement::select(
                DB::raw('DATE(movement_date) as date'),
                DB::raw('COUNT(*) as count')
            )
            ->whereDate('movement_date', '>=', $dateFrom)
            ->whereDate('movement_date', '<=', $dateTo)
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        $fastMovingMedicines = StockMovement::join('medicines', 'stock_movements.medicine_id', '=', 'medicines.id')
            ->select('medicines.name as medicine_name', DB::raw('SUM(ABS(stock_movements.quantity)) as total_moved'))
            ->whereIn('stock_movements.type', ['dispensed', 'stock_out'])
            ->whereDate('stock_movements.movement_date', '>=', $dateFrom)
            ->whereDate('stock_movements.movement_date', '<=', $dateTo)
            ->groupBy('medicines.id', 'medicines.name')
            ->orderBy('total_moved', 'desc')
            ->limit(10)
            ->get();

        // ─── LAB / INVESTIGATION ANALYTICS ───
        $totalInvestigations = Investigation::whereDate('created_at', '>=', $dateFrom)
            ->whereDate('created_at', '<=', $dateTo)->count();
        $investigationsByStatus = Investigation::select('status', DB::raw('COUNT(*) as count'))
            ->whereDate('created_at', '>=', $dateFrom)
            ->whereDate('created_at', '<=', $dateTo)
            ->groupBy('status')
            ->get();
        $investigationsByTest = Investigation::join('lab_tests', 'investigations.lab_test_id', '=', 'lab_tests.id')
            ->select('lab_tests.name as test_name', DB::raw('COUNT(*) as count'))
            ->whereDate('investigations.created_at', '>=', $dateFrom)
            ->whereDate('investigations.created_at', '<=', $dateTo)
            ->groupBy('lab_tests.id', 'lab_tests.name')
            ->orderBy('count', 'desc')
            ->limit(10)
            ->get();
        $investigationTrend = Investigation::select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('COUNT(*) as count')
            )
            ->whereDate('created_at', '>=', $dateFrom)
            ->whereDate('created_at', '<=', $dateTo)
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        // ─── FINANCIAL ANALYTICS ───
        $totalRevenue = Payment::whereBetween('paid_at', [$dateFrom . ' 00:00:00', $dateTo . ' 23:59:59'])
            ->sum('amount');
        $prevTotalRevenue = Payment::whereBetween('paid_at', [$prevDateFrom . ' 00:00:00', $prevDateTo . ' 23:59:59'])
            ->sum('amount');

        $totalExpensesVal = Expense::active()
            ->whereBetween('expense_date', [$dateFrom, $dateTo])
            ->sum('amount');
        $prevTotalExpenses = Expense::active()
            ->whereBetween('expense_date', [$prevDateFrom, $prevDateTo])
            ->sum('amount');

        $netIncome = $totalRevenue - $totalExpensesVal;
        $prevNetIncome = $prevTotalRevenue - $prevTotalExpenses;

        $totalPaid = Invoice::whereBetween('created_at', [$dateFrom . ' 00:00:00', $dateTo . ' 23:59:59'])
            ->sum('amount_paid');
        $totalOutstanding = Invoice::whereIn('status', ['issued', 'partially_paid'])
            ->sum('balance');
        $cancelledInvoicesTotal = Invoice::where('status', 'cancelled')
            ->whereBetween('created_at', [$dateFrom . ' 00:00:00', $dateTo . ' 23:59:59'])
            ->sum('total');

        $revenueBySource = InvoiceItem::whereHas('invoice', function ($q) use ($dateFrom, $dateTo) {
            $q->whereBetween('created_at', [$dateFrom . ' 00:00:00', $dateTo . ' 23:59:59'])
              ->whereNotIn('status', ['cancelled']);
        })
            ->select('type', DB::raw('SUM(total) as total'), DB::raw('COUNT(*) as count'))
            ->groupBy('type')
            ->get();

        $expensesByCategory = Expense::active()
            ->whereBetween('expense_date', [$dateFrom, $dateTo])
            ->join('expense_categories', 'expenses.expense_category_id', '=', 'expense_categories.id')
            ->select('expense_categories.name as category_name', DB::raw('SUM(expenses.amount) as total'), DB::raw('COUNT(*) as count'))
            ->groupBy('expense_categories.name')
            ->orderBy('total', 'desc')
            ->get();

        $paymentsByMethod = Payment::whereBetween('paid_at', [$dateFrom . ' 00:00:00', $dateTo . ' 23:59:59'])
            ->select('payment_method', DB::raw('SUM(amount) as total'), DB::raw('COUNT(*) as count'))
            ->groupBy('payment_method')
            ->orderBy('total', 'desc')
            ->get();

        $revenueExpenseTrend = $this->buildFinancialTrend($dateFrom, $dateTo);

        // ─── COMPARISON ANALYTICS ───
        $comparison = [
            'revenue' => $this->safeComparison($totalRevenue, $prevTotalRevenue),
            'expenses' => $this->safeComparison($totalExpensesVal, $prevTotalExpenses),
            'net_income' => $this->safeComparison($netIncome, $prevNetIncome),
            'patients' => $this->safeComparison($newPatients, $prevNewPatients),
            'appointments' => $this->safeComparison($totalAppointments, $prevTotalAppointments),
        ];

        return view('analytics.index', compact(
            // Patient
            'totalPatients', 'newPatients', 'returningPatients', 'activePatients',
            'patientsByGender', 'patientsByBloodGroup', 'patientsByAgeGroup', 'patientTrend',
            // Appointment
            'totalAppointments', 'appointmentsByStatus', 'appointmentsByDoctor',
            'appointmentsByDepartment', 'appointmentTrend',
            // Doctor
            'doctorStats', 'doctorPaginator',
            // Consultation
            'totalConsultations', 'completedConsultations', 'draftConsultations', 'consultationTrend',
            // Diagnosis
            'topDiagnoses',
            // Prescription
            'totalPrescriptions', 'topMedicines', 'topMedicinesPaginator', 'prescriptionTrend',
            // Inventory
            'totalMedicines', 'lowStockCount', 'outOfStockCount', 'expiredCount', 'expiringSoonCount',
            'stockMovementsByType', 'stockMovementTrend', 'fastMovingMedicines',
            // Lab
            'totalInvestigations', 'investigationsByStatus', 'investigationsByTest', 'investigationTrend',
            // Financial
            'totalRevenue', 'totalExpensesVal', 'netIncome', 'totalPaid', 'totalOutstanding',
            'cancelledInvoicesTotal', 'revenueBySource', 'expensesByCategory', 'paymentsByMethod',
            'revenueExpenseTrend',
            // Comparison
            'comparison',
            // Date
            'dateFrom', 'dateTo',
        ));
    }

    public function export(Request $request, string $type)
    {
        Gate::authorize('dashboard.view');

        $dateFrom = $request->input('date_from', now()->startOfMonth()->toDateString());
        $dateTo = $request->input('date_to', now()->toDateString());

        $filename = "analytics-{$type}-{$dateFrom}-to-{$dateTo}.csv";

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function () use ($type, $dateFrom, $dateTo) {
            $handle = fopen('php://output', 'w');

            match ($type) {
                'patients' => $this->exportPatients($handle, $dateFrom, $dateTo),
                'appointments' => $this->exportAppointments($handle, $dateFrom, $dateTo),
                'doctors' => $this->exportDoctors($handle, $dateFrom, $dateTo),
                'consultations' => $this->exportConsultations($handle, $dateFrom, $dateTo),
                'prescriptions' => $this->exportPrescriptions($handle, $dateFrom, $dateTo),
                'inventory' => $this->exportInventory($handle, $dateFrom, $dateTo),
                'investigations' => $this->exportInvestigations($handle, $dateFrom, $dateTo),
                'financial' => $this->exportFinancial($handle, $dateFrom, $dateTo),
                default => fputcsv($handle, ['Invalid export type']),
            };

            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }

    protected function buildFinancialTrend(string $dateFrom, string $dateTo)
    {
        $dailyRevenue = Payment::whereBetween('paid_at', [$dateFrom . ' 00:00:00', $dateTo . ' 23:59:59'])
            ->select(DB::raw('DATE(paid_at) as date'), DB::raw('SUM(amount) as total'))
            ->groupBy('date')
            ->get()
            ->keyBy('date');

        $dailyExpenses = Expense::active()
            ->whereBetween('expense_date', [$dateFrom, $dateTo])
            ->select('expense_date as date', DB::raw('SUM(amount) as total'))
            ->groupBy('expense_date')
            ->get()
            ->keyBy('date');

        $trend = collect();
        $startDate = \Carbon\Carbon::parse($dateFrom);
        $endDate = \Carbon\Carbon::parse($dateTo);

        while ($startDate->lte($endDate)) {
            $dateStr = $startDate->toDateString();
            $rev = $dailyRevenue[$dateStr]->total ?? 0;
            $exp = $dailyExpenses[$dateStr]->total ?? 0;
            $trend->push([
                'date' => $dateStr,
                'revenue' => (float) $rev,
                'expenses' => (float) $exp,
                'net' => (float) ($rev - $exp),
            ]);
            $startDate->addDay();
        }

        return $trend;
    }

    protected function safeComparison(float $current, float $previous): array
    {
        if ($previous == 0) {
            return [
                'current' => $current,
                'previous' => $previous,
                'difference' => $current,
                'percentage' => $current > 0 ? 100.0 : 0.0,
                'direction' => $current > 0 ? 'up' : 'same',
            ];
        }

        $difference = $current - $previous;
        $percentage = abs($difference) / abs($previous) * 100;

        return [
            'current' => $current,
            'previous' => $previous,
            'difference' => $difference,
            'percentage' => round($percentage, 1),
            'direction' => $difference > 0 ? 'up' : ($difference < 0 ? 'down' : 'same'),
        ];
    }

    protected function exportPatients($handle, string $dateFrom, string $dateTo): void
    {
        fputcsv($handle, ['Patient Analytics', "{$dateFrom} to {$dateTo}"]);
        fputcsv($handle, []);
        fputcsv($handle, ['Metric', 'Value']);
        fputcsv($handle, ['Total Patients', Patient::count()]);
        fputcsv($handle, ['New Patients', Patient::whereDate('created_at', '>=', $dateFrom)->whereDate('created_at', '<=', $dateTo)->count()]);
        fputcsv($handle, ['Active Patients', Patient::active()->count()]);
        fputcsv($handle, []);
        fputcsv($handle, ['Gender', 'Count']);
        Patient::select('gender', DB::raw('COUNT(*) as count'))->groupBy('gender')->each(function ($row) use ($handle) {
            fputcsv($handle, [$row->gender ?? 'Unknown', $row->count]);
        });
    }

    protected function exportAppointments($handle, string $dateFrom, string $dateTo): void
    {
        fputcsv($handle, ['Appointment Analytics', "{$dateFrom} to {$dateTo}"]);
        fputcsv($handle, []);
        fputcsv($handle, ['Status', 'Count']);
        Appointment::select('status', DB::raw('COUNT(*) as count'))
            ->whereDate('date', '>=', $dateFrom)
            ->whereDate('date', '<=', $dateTo)
            ->groupBy('status')
            ->each(function ($row) use ($handle) {
                fputcsv($handle, [$row->status, $row->count]);
            });
    }

    protected function exportDoctors($handle, string $dateFrom, string $dateTo): void
    {
        fputcsv($handle, ['Doctor Analytics', "{$dateFrom} to {$dateTo}"]);
        fputcsv($handle, []);
        fputcsv($handle, ['Doctor', 'Department', 'Appointments', 'Completed', 'Cancelled', 'Patients', 'Consultations']);
        $apptStats = Appointment::whereBetween('date', [$dateFrom, $dateTo])
            ->selectRaw('doctor_id,
                COUNT(*) as total,
                SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as completed,
                SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as cancelled,
                COUNT(DISTINCT patient_id) as patients',
                [AppointmentStatus::Completed->value, AppointmentStatus::Cancelled->value])
            ->groupBy('doctor_id')->get()->keyBy('doctor_id');
        $consultCounts = Consultation::whereBetween('created_at', [$dateFrom, $dateTo])
            ->selectRaw('doctor_id, COUNT(*) as consultations')
            ->groupBy('doctor_id')->get()->keyBy('doctor_id');
        Doctor::where('is_active', true)->with('department')->each(function ($doc) use ($handle, $apptStats, $consultCounts) {
            $appt = $apptStats[$doc->id] ?? null;
            $consult = $consultCounts[$doc->id] ?? null;
            fputcsv($handle, [
                $doc->name, $doc->department?->name ?? '-',
                $appt->total ?? 0, $appt->completed ?? 0, $appt->cancelled ?? 0,
                $appt->patients ?? 0, $consult->consultations ?? 0,
            ]);
        });
    }

    protected function exportConsultations($handle, string $dateFrom, string $dateTo): void
    {
        fputcsv($handle, ['Consultation Analytics', "{$dateFrom} to {$dateTo}"]);
        fputcsv($handle, []);
        fputcsv($handle, ['Metric', 'Value']);
        fputcsv($handle, ['Total Consultations', Consultation::whereDate('created_at', '>=', $dateFrom)->whereDate('created_at', '<=', $dateTo)->count()]);
        fputcsv($handle, ['Completed', Consultation::whereDate('created_at', '>=', $dateFrom)->whereDate('created_at', '<=', $dateTo)->where('status', 'completed')->count()]);
        fputcsv($handle, ['Draft', Consultation::whereDate('created_at', '>=', $dateFrom)->whereDate('created_at', '<=', $dateTo)->where('status', 'draft')->count()]);
        fputcsv($handle, []);
        fputcsv($handle, ['Diagnosis', 'Count']);
        Consultation::where('status', 'completed')->whereNotNull('diagnosis')->where('diagnosis', '!=', '')
            ->whereDate('created_at', '>=', $dateFrom)->whereDate('created_at', '<=', $dateTo)
            ->select('diagnosis', DB::raw('COUNT(*) as count'))->groupBy('diagnosis')->orderBy('count', 'desc')->limit(20)
            ->each(function ($row) use ($handle) {
                fputcsv($handle, [$row->diagnosis, $row->count]);
            });
    }

    protected function exportPrescriptions($handle, string $dateFrom, string $dateTo): void
    {
        fputcsv($handle, ['Prescription Analytics', "{$dateFrom} to {$dateTo}"]);
        fputcsv($handle, []);
        fputcsv($handle, ['Total Prescriptions', Prescription::whereDate('created_at', '>=', $dateFrom)->whereDate('created_at', '<=', $dateTo)->count()]);
        fputcsv($handle, []);
        fputcsv($handle, ['Medicine', 'Total Quantity', 'Prescription Count']);
        PrescriptionItem::join('prescriptions', 'prescription_items.prescription_id', '=', 'prescriptions.id')
            ->join('medicines', 'prescription_items.medicine_id', '=', 'medicines.id')
            ->select('medicines.name', DB::raw('SUM(prescription_items.quantity) as qty'), DB::raw('COUNT(*) as cnt'))
            ->whereDate('prescriptions.created_at', '>=', $dateFrom)
            ->whereDate('prescriptions.created_at', '<=', $dateTo)
            ->groupBy('medicines.id', 'medicines.name')
            ->orderBy('cnt', 'desc')
            ->each(function ($row) use ($handle) {
                fputcsv($handle, [$row->name, $row->qty, $row->cnt]);
            });
    }

    protected function exportInventory($handle, string $dateFrom, string $dateTo): void
    {
        fputcsv($handle, ['Inventory Analytics', "{$dateFrom} to {$dateTo}"]);
        fputcsv($handle, []);
        fputcsv($handle, ['Metric', 'Value']);
        fputcsv($handle, ['Total Active Medicines', Medicine::where('is_active', true)->count()]);
        fputcsv($handle, ['Low Stock', Medicine::whereColumn('stock_quantity', '<=', 'minimum_stock_level')->where('is_active', true)->count()]);
        fputcsv($handle, ['Out of Stock', Medicine::where('stock_quantity', 0)->where('is_active', true)->count()]);
        fputcsv($handle, ['Expired', Medicine::where('expiry_date', '<', now())->where('is_active', true)->count()]);
        fputcsv($handle, []);
        fputcsv($handle, ['Medicine', 'Stock', 'Status']);
        Medicine::where('is_active', true)->orderBy('name')->each(function ($med) use ($handle) {
            fputcsv($handle, [$med->name, $med->stock_quantity, $med->stock_status]);
        });
    }

    protected function exportInvestigations($handle, string $dateFrom, string $dateTo): void
    {
        fputcsv($handle, ['Investigation Analytics', "{$dateFrom} to {$dateTo}"]);
        fputcsv($handle, []);
        fputcsv($handle, ['Status', 'Count']);
        Investigation::select('status', DB::raw('COUNT(*) as count'))
            ->whereDate('created_at', '>=', $dateFrom)
            ->whereDate('created_at', '<=', $dateTo)
            ->groupBy('status')
            ->each(function ($row) use ($handle) {
                fputcsv($handle, [$row->status, $row->count]);
            });
    }

    protected function exportFinancial($handle, string $dateFrom, string $dateTo): void
    {
        fputcsv($handle, ['Financial Analytics', "{$dateFrom} to {$dateTo}"]);
        fputcsv($handle, []);
        fputcsv($handle, ['Metric', 'Value']);
        fputcsv($handle, ['Revenue', number_format(Payment::whereBetween('paid_at', [$dateFrom . ' 00:00:00', $dateTo . ' 23:59:59'])->sum('amount'), 2)]);
        fputcsv($handle, ['Expenses', number_format(Expense::active()->whereBetween('expense_date', [$dateFrom, $dateTo])->sum('amount'), 2)]);
        $rev = Payment::whereBetween('paid_at', [$dateFrom . ' 00:00:00', $dateTo . ' 23:59:59'])->sum('amount');
        $exp = Expense::active()->whereBetween('expense_date', [$dateFrom, $dateTo])->sum('amount');
        fputcsv($handle, ['Net Income', number_format($rev - $exp, 2)]);
        fputcsv($handle, ['Outstanding', number_format(Invoice::whereIn('status', ['issued', 'partially_paid'])->sum('balance'), 2)]);
    }
}
