<?php

namespace App\Http\Controllers;

use App\Models\Consultation;
use App\Models\Doctor;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\InventoryBatch;
use App\Models\Medicine;
use App\Models\Patient;
use App\Models\Prescription;
use App\Models\Service;
use App\Models\StockMovement;
use Illuminate\Http\Request;
use App\Services\AuditService;
use App\Services\NotificationService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

class InvoiceController extends Controller
{
    public function index(Request $request)
    {
        Gate::authorize('invoice.view');

        $query = Invoice::with(['patient', 'doctor']);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('invoice_number', 'like', "%{$search}%")
                  ->orWhereHas('patient', fn($q2) => $q2->where('name', 'like', "%{$search}%"));
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('patient_id')) {
            $query->where('patient_id', $request->patient_id);
        }

        if ($request->filled('doctor_id')) {
            $query->where('doctor_id', $request->doctor_id);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $invoices = $query->latest()->paginate(15)->withQueryString();

        return view('invoices.index', compact('invoices'));
    }

    public function create(Request $request)
    {
        Gate::authorize('invoice.create');

        $consultation = null;
        $patient = null;
        $doctor = null;

        if ($request->filled('consultation_id')) {
            $consultation = Consultation::with(['patient', 'doctor', 'prescriptions.items.medicine'])
                ->findOrFail($request->consultation_id);

            if (!$consultation->isCompleted()) {
                return back()->with('error', 'Invoice can only be created for completed consultations.');
            }

            if ($consultation->invoice) {
                return redirect()->route('invoices.show', $consultation->invoice)
                    ->with('error', 'An invoice already exists for this consultation.');
            }

            $patient = $consultation->patient;
            $doctor = $consultation->doctor;
        }

        if ($request->filled('patient_id') && !$patient) {
            $patient = Patient::findOrFail($request->patient_id);
        }

        $patients = Patient::active()->orderBy('name')->get();
        $doctors = Doctor::orderBy('name')->get();
        $services = Service::where('status', true)->orderBy('title')->get();
        $medicines = Medicine::active()->orderBy('name')->get();

        return view('invoices.create', compact('consultation', 'patient', 'doctor', 'patients', 'doctors', 'services', 'medicines'));
    }

    public function store(Request $request)
    {
        Gate::authorize('invoice.create');

        $validated = $request->validate([
            'patient_id' => 'required|exists:patients,id',
            'doctor_id' => 'nullable|exists:doctors,id',
            'consultation_id' => 'nullable|exists:consultations,id',
            'appointment_id' => 'nullable|exists:appointments,id',
            'discount' => 'nullable|numeric|min:0',
            'tax' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string|max:2000',
            'items' => 'required|array|min:1',
            'items.*.description' => 'required|string|max:255',
            'items.*.type' => 'required|in:consultation,medicine,service,other',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.unit_price' => 'required|numeric|min:0',
        ]);

        if (isset($validated['consultation_id'])) {
            $consultation = Consultation::findOrFail($validated['consultation_id']);
            if (!$consultation->isCompleted()) {
                return back()->with('error', 'Invoice can only be created for completed consultations.');
            }
            if ($consultation->invoice) {
                return back()->with('error', 'An invoice already exists for this consultation.');
            }
            $validated['patient_id'] = $consultation->patient_id;
            $validated['doctor_id'] = $consultation->doctor_id;
            $validated['appointment_id'] = $consultation->appointment_id;
        }

        $validated['discount'] = $validated['discount'] ?? 0;
        $validated['tax'] = $validated['tax'] ?? 0;

        $invoice = DB::transaction(function () use ($validated) {
            $invoice = Invoice::create([
                'patient_id' => $validated['patient_id'],
                'doctor_id' => $validated['doctor_id'] ?? null,
                'consultation_id' => $validated['consultation_id'] ?? null,
                'appointment_id' => $validated['appointment_id'] ?? null,
                'discount' => $validated['discount'],
                'tax' => $validated['tax'],
                'status' => 'draft',
                'notes' => $validated['notes'] ?? null,
            ]);

            $subtotal = 0;
            foreach ($validated['items'] as $itemData) {
                $itemTotal = (int) $itemData['quantity'] * (float) $itemData['unit_price'];
                InvoiceItem::create([
                    'invoice_id' => $invoice->id,
                    'description' => $itemData['description'],
                    'type' => $itemData['type'],
                    'quantity' => $itemData['quantity'],
                    'unit_price' => $itemData['unit_price'],
                    'total' => $itemTotal,
                ]);
                $subtotal += $itemTotal;
            }

            $total = max(0, $subtotal - $validated['discount'] + $validated['tax']);
            $invoice->update([
                'subtotal' => $subtotal,
                'total' => $total,
                'balance' => $total,
            ]);

            return $invoice;
        });

        AuditService::logCreated($invoice, 'Invoice');

        NotificationService::notify(
            $invoice->doctor->user_id ?? auth()->id(),
            'invoice',
            'Invoice Created',
            "Invoice {$invoice->invoice_number} has been created for {$invoice->patient->name}.",
            $invoice,
            'invoice',
            'created',
            route('invoices.show', $invoice)
        );

        return redirect()->route('invoices.show', $invoice)
            ->with('success', 'Invoice created successfully.');
    }

    public function show(Invoice $invoice)
    {
        Gate::authorize('invoice.view');

        $invoice->load(['patient', 'doctor', 'appointment', 'consultation', 'items', 'payments.recordedBy']);

        $latestPayment = $invoice->payments()->first();

        return view('invoices.show', compact('invoice', 'latestPayment'));
    }

    public function edit(Invoice $invoice)
    {
        Gate::authorize('invoice.edit');

        $invoice->load(['patient', 'doctor', 'consultation', 'items']);

        if ($invoice->isPaid() || $invoice->isCancelled()) {
            return back()->with('error', 'Cannot edit a paid or cancelled invoice.');
        }

        $patients = Patient::active()->orderBy('name')->get();
        $doctors = Doctor::orderBy('name')->get();
        $services = Service::where('status', true)->orderBy('title')->get();
        $medicines = Medicine::active()->orderBy('name')->get();

        return view('invoices.edit', compact('invoice', 'patients', 'doctors', 'services', 'medicines'));
    }

    public function update(Request $request, Invoice $invoice)
    {
        Gate::authorize('invoice.edit');

        if ($invoice->isPaid() || $invoice->isCancelled()) {
            return back()->with('error', 'Cannot update a paid or cancelled invoice.');
        }

        $validated = $request->validate([
            'discount' => 'nullable|numeric|min:0',
            'tax' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string|max:2000',
            'items' => 'required|array|min:1',
            'items.*.description' => 'required|string|max:255',
            'items.*.type' => 'required|in:consultation,medicine,service,other',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.unit_price' => 'required|numeric|min:0',
        ]);

        $validated['discount'] = $validated['discount'] ?? 0;
        $validated['tax'] = $validated['tax'] ?? 0;

        $old = $invoice->toArray();

        DB::transaction(function () use ($invoice, $validated) {
            $invoice->update([
                'discount' => $validated['discount'],
                'tax' => $validated['tax'],
                'notes' => $validated['notes'] ?? null,
            ]);

            $invoice->items()->delete();

            $subtotal = 0;
            foreach ($validated['items'] as $itemData) {
                $itemTotal = (int) $itemData['quantity'] * (float) $itemData['unit_price'];
                InvoiceItem::create([
                    'invoice_id' => $invoice->id,
                    'description' => $itemData['description'],
                    'type' => $itemData['type'],
                    'quantity' => $itemData['quantity'],
                    'unit_price' => $itemData['unit_price'],
                    'total' => $itemTotal,
                ]);
                $subtotal += $itemTotal;
            }

            $total = max(0, $subtotal - $validated['discount'] + $validated['tax']);
            $balance = max(0, $total - (float) $invoice->amount_paid);

            $invoice->update([
                'subtotal' => $subtotal,
                'total' => $total,
                'balance' => $balance,
            ]);

            $invoice->recalculateStatus();
        });

        AuditService::logUpdated($invoice, 'Invoice', $old, $validated);

        return redirect()->route('invoices.show', $invoice)
            ->with('success', 'Invoice updated successfully.');
    }

    public function issue(Invoice $invoice)
    {
        Gate::authorize('invoice.edit');

        if (!$invoice->isDraft()) {
            return back()->with('error', 'Only draft invoices can be issued.');
        }

        $oldStatus = $invoice->status;
        $invoice->update([
            'status' => 'issued',
            'issued_at' => now(),
        ]);

        AuditService::logStatusChange($invoice, 'Invoice', $oldStatus, 'issued');

        NotificationService::notifyAdmins(
            'invoice',
            'Invoice Issued',
            "Invoice {$invoice->invoice_number} has been issued and is ready for payment.",
            $invoice,
            'invoice',
            'issued',
            route('invoices.show', $invoice)
        );

        return redirect()->route('invoices.show', $invoice)
            ->with('success', 'Invoice issued successfully.');
    }

    public function cancel(Invoice $invoice)
    {
        Gate::authorize('invoice.cancel');

        if ($invoice->isPaid()) {
            return back()->with('error', 'Cannot cancel a fully paid invoice.');
        }

        $oldStatus = $invoice->status;
        $invoice->update(['status' => 'cancelled']);

        AuditService::logStatusChange($invoice, 'Invoice', $oldStatus, 'cancelled');

        NotificationService::notifyAdmins(
            'invoice',
            'Invoice Cancelled',
            "Invoice {$invoice->invoice_number} has been cancelled.",
            $invoice,
            'invoice',
            'cancelled',
            route('invoices.show', $invoice)
        );

        return redirect()->route('invoices.show', $invoice)
            ->with('success', 'Invoice cancelled successfully.');
    }

    public function destroy(Invoice $invoice, Request $request)
    {
        Gate::authorize('invoice.delete');

        if (!$invoice->isCancelled()) {
            return back()->with('error', 'Only cancelled invoices can be deleted.');
        }

        if ($invoice->payments()->exists()) {
            return back()->with('error', 'This invoice has payment records. Payments must be reversed before deletion.');
        }

        $validated = $request->validate([
            'reason' => 'required|string|max:1000',
        ]);

        $oldAttributes = $invoice->getAttributes();
        $medicineItems = $invoice->items()->where('type', 'medicine')->get();

        DB::transaction(function () use ($invoice, $medicineItems) {
            // Reverse inventory for medicine items
            foreach ($medicineItems as $item) {
                $medicine = Medicine::where('name', 'like', $item->description . '%')->first();
                if (!$medicine) continue;

                $movements = StockMovement::where('reference_type', Invoice::class)
                    ->where('reference_id', $invoice->id)
                    ->where('medicine_id', $medicine->id)
                    ->get();

                foreach ($movements as $movement) {
                    if ($movement->inventory_batch_id) {
                        $batch = InventoryBatch::find($movement->inventory_batch_id);
                        if ($batch && $batch->quantity >= abs($movement->quantity)) {
                            $batch->stockIn(
                                abs($movement->quantity),
                                "Reversal for deleted invoice {$invoice->invoice_number}",
                                auth()->id(),
                                $invoice
                            );
                        }
                    } else {
                        $medicine->stockIn(
                            abs($movement->quantity),
                            "Reversal for deleted invoice {$invoice->invoice_number}",
                            auth()->id(),
                            $invoice
                        );
                    }
                }
            }

            $invoice->items()->delete();
            $invoice->delete();
        });

        AuditService::log(
            'deleted',
            'Invoice',
            $invoice,
            "Invoice {$invoice->invoice_number} soft-deleted. Reason: {$validated['reason']}",
            $oldAttributes,
            null,
            [
                'reason' => $validated['reason'],
                'patient_id' => $invoice->patient_id,
                'patient_name' => $invoice->patient?->name,
                'total' => $invoice->total,
                'status' => $oldAttributes['status'] ?? 'cancelled',
                'medicine_items_reversed' => $medicineItems->count(),
            ]
        );

        return redirect()->route('invoices.index')
            ->with('success', 'Invoice deleted successfully.');
    }

    public function addMedicineItems(Request $request, Invoice $invoice)
    {
        Gate::authorize('invoice.edit');

        if ($invoice->isPaid() || $invoice->isCancelled()) {
            return back()->with('error', 'Cannot modify a paid or cancelled invoice.');
        }

        if ($request->filled('prescription_id')) {
            $prescription = Prescription::with('items.medicine')->findOrFail($request->prescription_id);

            if ($prescription->patient_id !== $invoice->patient_id) {
                return back()->with('error', 'Prescription does not belong to this invoice patient.');
            }

            DB::transaction(function () use ($invoice, $prescription) {
                foreach ($prescription->items as $prescriptionItem) {
                    $medicine = $prescriptionItem->medicine;
                    if (!$medicine) continue;

                    $existingItem = $invoice->items()
                        ->where('type', 'medicine')
                        ->where('description', $medicine->name)
                        ->first();

                    if ($existingItem) {
                        $newQty = $existingItem->quantity + $prescriptionItem->quantity;
                        $existingItem->update([
                            'quantity' => $newQty,
                            'total' => $newQty * $existingItem->unit_price,
                        ]);
                    } else {
                        $itemTotal = $prescriptionItem->quantity * (float) $medicine->unit_price;
                        InvoiceItem::create([
                            'invoice_id' => $invoice->id,
                            'description' => $medicine->name . ($medicine->strength ? " ({$medicine->strength})" : ''),
                            'type' => 'medicine',
                            'quantity' => $prescriptionItem->quantity,
                            'unit_price' => $medicine->unit_price,
                            'total' => $itemTotal,
                        ]);
                    }
                }

                $invoice->recalculateTotals();
            });

            return back()->with('success', 'Medicine items added to invoice.');
        }

        return back()->with('error', 'No prescription selected.');
    }
}
