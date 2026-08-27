<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\Consultation;
use App\Models\Invoice;
use App\Models\Investigation;
use App\Models\Patient;
use App\Models\Payment;
use App\Models\Prescription;
use App\Models\QueueTicket;
use App\Services\ClinicSettingsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class PrintController extends Controller
{
    public function appointment(Appointment $appointment)
    {
        Gate::authorize('appointment.view');
        $appointment->load(['patient', 'doctor.department', 'department']);
        return view('print.appointment', compact('appointment'));
    }

    public function queueTicket(QueueTicket $ticket)
    {
        Gate::authorize('queue.view');
        $ticket->load(['patient', 'doctor.department']);
        return view('print.queue-ticket', compact('ticket'));
    }

    public function prescription(Prescription $prescription)
    {
        Gate::authorize('prescription.view');
        $prescription->load(['patient', 'doctor.department', 'items.medicine']);
        return view('print.prescription', compact('prescription'));
    }

    public function investigation(Investigation $investigation)
    {
        Gate::authorize('investigation.view');
        $investigation->load(['patient', 'doctor.department', 'labTest']);
        return view('print.investigation', compact('investigation'));
    }

    public function invoice(Invoice $invoice)
    {
        Gate::authorize('invoice.view');
        $invoice->load(['patient', 'doctor', 'items', 'payments.recordedBy']);
        return view('print.invoice', compact('invoice'));
    }

    public function receipt(Payment $payment)
    {
        Gate::authorize('payment.view');
        $payment->load(['invoice.patient', 'invoice.items', 'recordedBy']);
        return view('print.receipt', compact('payment'));
    }

    public function medicalRecord(Patient $patient)
    {
        Gate::authorize('patient.view');
        $patient->load([
            'appointments.doctor',
            'prescriptions.doctor',
            'prescriptions.items.medicine',
            'investigations.doctor',
            'investigations.labTest',
        ]);

        $consultations = Consultation::where('patient_id', $patient->id)
            ->with(['doctor', 'vitalSign'])
            ->orderByDesc('created_at')
            ->limit(20)
            ->get();

        $vitalSigns = \App\Models\VitalSign::whereHas('consultation', fn ($q) => $q->where('patient_id', $patient->id))
            ->orderByDesc('recorded_at')
            ->limit(20)
            ->get();

        $invoices = Invoice::where('patient_id', $patient->id)
            ->with('items')
            ->orderByDesc('created_at')
            ->limit(20)
            ->get();

        $prescriptions = $patient->prescriptions()->with(['doctor', 'items.medicine'])->orderByDesc('created_at')->limit(20)->get();
        $investigations = $patient->investigations()->with(['doctor', 'labTest'])->orderByDesc('created_at')->limit(20)->get();
        $appointments = $patient->appointments()->with(['doctor', 'department'])->orderByDesc('date')->limit(20)->get();

        return view('print.medical-record', compact('patient', 'consultations', 'vitalSigns', 'invoices', 'prescriptions', 'investigations', 'appointments'));
    }

    public function report(Request $request, string $type)
    {
        $permission = match ($type) {
            'financial' => 'report.financial',
            'appointment' => 'report.appointment',
            'patient' => 'report.patient',
            'inventory' => 'report.inventory',
            default => abort(404),
        };
        Gate::authorize($permission);

        $validated = $request->validate([
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date',
        ]);

        $startDate = $validated['start_date'] ?? now()->startOfMonth()->toDateString();
        $endDate = $validated['end_date'] ?? now()->toDateString();

        $data = match ($type) {
            'financial' => $this->financialReportData($startDate, $endDate),
            'appointment' => $this->appointmentReportData($startDate, $endDate),
            'patient' => $this->patientReportData($startDate, $endDate),
            'inventory' => $this->inventoryReportData($startDate, $endDate),
            default => abort(404),
        };

        return view("print.report-{$type}", array_merge($data, [
            'startDate' => $startDate,
            'endDate' => $endDate,
            'reportType' => $type,
        ]));
    }

    private function financialReportData(string $startDate, string $endDate): array
    {
        $invoices = Invoice::whereBetween('created_at', [$startDate, $endDate . ' 23:59:59'])
            ->with('items')
            ->get();

        $payments = Payment::whereBetween('paid_at', [$startDate, $endDate . ' 23:59:59'])
            ->with('invoice.patient')
            ->get();

        $expenses = \App\Models\Expense::whereBetween('expense_date', [$startDate, $endDate])
            ->get();

        return [
            'invoices' => $invoices,
            'payments' => $payments,
            'expenses' => $expenses,
            'totalRevenue' => (float) $invoices->sum('total'),
            'totalPaid' => (float) $payments->sum('amount'),
            'totalExpenses' => (float) $expenses->sum('amount'),
            'netIncome' => (float) $payments->sum('amount') - (float) $expenses->sum('amount'),
        ];
    }

    private function appointmentReportData(string $startDate, string $endDate): array
    {
        $appointments = Appointment::whereBetween('date', [$startDate, $endDate])
            ->with(['patient', 'doctor', 'department'])
            ->orderBy('date')
            ->get();

        return [
            'appointments' => $appointments,
            'total' => $appointments->count(),
            'completed' => $appointments->where('status', 'completed')->count(),
            'cancelled' => $appointments->where('status', 'cancelled')->count(),
            'pending' => $appointments->whereIn('status', ['pending', 'scheduled', 'confirmed'])->count(),
        ];
    }

    private function patientReportData(string $startDate, string $endDate): array
    {
        $patients = Patient::whereBetween('created_at', [$startDate, $endDate . ' 23:59:59'])
            ->withCount(['appointments', 'prescriptions', 'invoices'])
            ->orderByDesc('created_at')
            ->get();

        return [
            'patients' => $patients,
            'total' => $patients->count(),
        ];
    }

    private function inventoryReportData(string $startDate, string $endDate): array
    {
        $movements = \App\Models\StockMovement::whereBetween('created_at', [$startDate, $endDate . ' 23:59:59'])
            ->with(['medicine', 'performer'])
            ->orderByDesc('created_at')
            ->get();

        return [
            'movements' => $movements,
            'total' => $movements->count(),
        ];
    }
}
