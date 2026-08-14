<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

class PaymentController extends Controller
{
    public function index(Request $request)
    {
        Gate::authorize('payment.view');

        $query = Payment::with(['invoice.patient', 'recordedBy']);

        if ($request->filled('invoice_id')) {
            $query->where('invoice_id', $request->invoice_id);
        }

        if ($request->filled('payment_method')) {
            $query->where('payment_method', $request->payment_method);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('paid_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('paid_at', '<=', $request->date_to);
        }

        $payments = $query->latest('paid_at')->paginate(15)->withQueryString();

        return view('payments.index', compact('payments'));
    }

    public function create(Request $request)
    {
        Gate::authorize('payment.create');

        $invoice = null;

        if ($request->filled('invoice_id')) {
            $invoice = Invoice::with(['patient', 'doctor', 'items'])
                ->findOrFail($request->invoice_id);

            if (!$invoice->canReceivePayment()) {
                return back()->with('error', 'This invoice cannot receive payments.');
            }
        }

        $invoices = Invoice::whereIn('status', ['issued', 'partially_paid'])
            ->with('patient')
            ->latest()
            ->get();

        return view('payments.create', compact('invoice', 'invoices'));
    }

    public function store(Request $request)
    {
        Gate::authorize('payment.create');

        $validated = $request->validate([
            'invoice_id' => 'required|exists:invoices,id',
            'amount' => 'required|numeric|min:0.01',
            'payment_method' => 'required|in:cash,card,bank_transfer,mobile_payment',
            'reference_number' => 'nullable|string|max:255',
            'notes' => 'nullable|string|max:1000',
            'paid_at' => 'required|date',
        ]);

        $invoice = Invoice::findOrFail($validated['invoice_id']);

        if (!$invoice->canReceivePayment()) {
            return back()->with('error', 'This invoice cannot receive payments.')->withInput();
        }

        $paymentAmount = (float) $validated['amount'];
        $currentBalance = (float) $invoice->balance;

        if ($paymentAmount > $currentBalance + 0.01) {
            return back()->with('error', "Payment amount (\$" . number_format($paymentAmount, 2) . ") exceeds remaining balance (\$" . number_format($currentBalance, 2) . ").")->withInput();
        }

        DB::transaction(function () use ($validated) {
            $invoice = Invoice::lockForUpdate()->findOrFail($validated['invoice_id']);

            Payment::create([
                'invoice_id' => $validated['invoice_id'],
                'amount' => $validated['amount'],
                'payment_method' => $validated['payment_method'],
                'reference_number' => $validated['reference_number'] ?? null,
                'notes' => $validated['notes'] ?? null,
                'recorded_by' => auth()->id(),
                'paid_at' => $validated['paid_at'],
            ]);

            $invoice->update([
                'amount_paid' => round((float) $invoice->amount_paid + (float) $validated['amount'], 2),
                'balance' => round(max(0, (float) $invoice->total - ((float) $invoice->amount_paid + (float) $validated['amount'])), 2),
            ]);

            $invoice->recalculateStatus();
        });

        return redirect()->route('invoices.show', $invoice)
            ->with('success', 'Payment recorded successfully.');
    }

    public function show(Payment $payment)
    {
        Gate::authorize('payment.view');

        $payment->load(['invoice.patient', 'invoice.doctor', 'recordedBy']);

        return view('payments.show', compact('payment'));
    }

    public function destroy(Payment $payment)
    {
        Gate::authorize('payment.cancel');

        $payment->load('invoice');

        if ($payment->invoice->isPaid()) {
            return back()->with('error', 'Cannot delete a payment from a fully paid invoice.');
        }

        DB::transaction(function () use ($payment) {
            $invoice = $payment->invoice;
            $invoice->update([
                'amount_paid' => round((float) $invoice->amount_paid - (float) $payment->amount, 2),
                'balance' => round((float) $invoice->balance + (float) $payment->amount, 2),
            ]);

            $invoice->recalculateStatus();

            $payment->delete();
        });

        return back()->with('success', 'Payment deleted successfully.');
    }

    public function receipt(Payment $payment)
    {
        Gate::authorize('payment.view');

        $payment->load(['invoice.patient', 'invoice.doctor', 'invoice.items', 'recordedBy']);

        return view('payments.receipt', compact('payment'));
    }
}
