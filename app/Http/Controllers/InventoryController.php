<?php

namespace App\Http\Controllers;

use App\Models\InventoryBatch;
use App\Models\Medicine;
use App\Models\Prescription;
use App\Models\StockMovement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Validator;

class InventoryController extends Controller
{
    public function index(Request $request)
    {
        Gate::authorize('inventory.view');

        $query = Medicine::query();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('generic_name', 'like', "%{$search}%");
            });
        }

        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        if ($request->filled('stock_status')) {
            $status = $request->stock_status;
            if ($status === 'low') {
                $query->whereColumn('stock_quantity', '<=', 'minimum_stock_level');
            } elseif ($status === 'out') {
                $query->where('stock_quantity', 0);
            } elseif ($status === 'normal') {
                $query->whereColumn('stock_quantity', '>', 'minimum_stock_level');
            }
        }

        if ($request->filled('expiry_status')) {
            $status = $request->expiry_status;
            if ($status === 'expired') {
                $query->whereHas('inventoryBatches', function ($q) {
                    $q->where('quantity', '>', 0)->where('expiry_date', '<=', now());
                });
            } elseif ($status === 'expiring') {
                $query->whereHas('inventoryBatches', function ($q) {
                    $q->where('quantity', '>', 0)
                        ->where('expiry_date', '>', now())
                        ->where('expiry_date', '<=', now()->addDays(30));
                });
            } elseif ($status === 'normal') {
                $query->whereDoesntHave('inventoryBatches', function ($q) {
                    $q->where('quantity', '>', 0)->where('expiry_date', '<=', now()->addDays(30));
                });
            }
        }

        $medicines = $query
            ->with(['inventoryBatches' => fn ($q) => $q->where('quantity', '>', 0)->orderBy('expiry_date')->orderBy('id')])
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();
        $categories = Medicine::whereNotNull('category')->distinct()->pluck('category');

        return view('inventory.index', compact('medicines', 'categories'));
    }

    public function dashboard()
    {
        Gate::authorize('inventory.view');

        $totalMedicines = Medicine::count();
        $lowStock = Medicine::whereColumn('stock_quantity', '<=', 'minimum_stock_level')->count();
        $outOfStock = Medicine::where('stock_quantity', 0)->count();

        // Batch/expiry aware quantities
        $usableStockTotal = (int) InventoryBatch::where('quantity', '>', 0)
            ->where(fn ($q) => $q->whereNull('expiry_date')->orWhere('expiry_date', '>', now()))
            ->sum('quantity');
        $expiredStockTotal = (int) InventoryBatch::where('quantity', '>', 0)
            ->where('expiry_date', '<=', now())
            ->sum('quantity');
        $expiringSoonTotal = (int) InventoryBatch::where('quantity', '>', 0)
            ->where('expiry_date', '>', now())
            ->where('expiry_date', '<=', now()->addDays(30))
            ->sum('quantity');

        // Medicine-level counts (kept for the existing KPIs)
        $expired = Medicine::where('expiry_date', '<', now())->count();
        $expiringSoon = Medicine::where('expiry_date', '>=', now())
            ->where('expiry_date', '<=', now()->addDays(30))
            ->count();

        $recentMovements = StockMovement::with(['medicine', 'performer', 'inventoryBatch', 'reference'])
            ->latest('movement_date')
            ->latest('id')
            ->take(10)
            ->get();

        $lowStockMedicines = Medicine::with('prescriptionItems')
            ->whereColumn('stock_quantity', '<=', 'minimum_stock_level')
            ->orderBy('stock_quantity')
            ->take(5)
            ->get();

        $expiringBatches = InventoryBatch::with('medicine')
            ->where('quantity', '>', 0)
            ->where('expiry_date', '>', now())
            ->where('expiry_date', '<=', now()->addDays(30))
            ->orderBy('expiry_date')
            ->take(5)
            ->get();

        $expiredBatches = InventoryBatch::with('medicine')
            ->where('quantity', '>', 0)
            ->where('expiry_date', '<=', now())
            ->orderBy('expiry_date')
            ->take(5)
            ->get();

        $totalStockValue = Medicine::query()
            ->selectRaw('SUM(COALESCE(stock_quantity, 0) * COALESCE(unit_price, 0)) as value')
            ->value('value') ?? 0;

        return view('inventory.dashboard', compact(
            'totalMedicines', 'lowStock', 'outOfStock', 'expired', 'expiringSoon',
            'usableStockTotal', 'expiredStockTotal', 'expiringSoonTotal',
            'recentMovements', 'lowStockMedicines', 'expiringBatches',
            'expiredBatches', 'totalStockValue'
        ));
    }

    public function movements(Request $request)
    {
        Gate::authorize('inventory.view');

        $validator = Validator::make($request->all(), [
            'medicine_id' => 'nullable|integer|exists:medicines,id',
            'type' => 'nullable|in:opening,stock_in,stock_out,adjustment,dispensed,expired',
            'date_from' => 'nullable|date_format:Y-m-d',
            'date_to' => 'nullable|date_format:Y-m-d',
            'per_page' => 'nullable|integer|in:15,30,50',
        ]);

        // Malformed filter values are rejected safely (never break the page).
        if ($validator->fails()) {
            return redirect()->route('inventory.movements');
        }

        $validated = $validator->validated();

        $dateFrom = $validated['date_from'] ?? null;
        $dateTo = $validated['date_to'] ?? null;

        if ($dateFrom && $dateTo && $dateFrom > $dateTo) {
            return redirect()->route('inventory.movements')
                ->withErrors(['date_range' => __('app.inventory.date_range_invalid')])
                ->withInput();
        }

        $query = StockMovement::with(['medicine', 'performer', 'inventoryBatch', 'reference']);

        if ($request->filled('medicine_id')) {
            $query->where('medicine_id', $validated['medicine_id']);
        }

        if ($request->filled('type')) {
            $query->where('type', $validated['type']);
        }

        if ($dateFrom) {
            $query->whereDate('movement_date', '>=', $dateFrom);
        }

        if ($dateTo) {
            $query->whereDate('movement_date', '<=', $dateTo);
        }

        $perPage = $validated['per_page'] ?? 15;

        $movements = $query
            ->orderBy('movement_date', 'desc')
            ->orderBy('id', 'desc')
            ->paginate($perPage)
            ->withQueryString();

        $medicines = Medicine::orderBy('name')->pluck('name', 'id');

        return view('inventory.movements', compact('movements', 'medicines', 'perPage'));
    }

    public function stockInForm(Medicine $medicine)
    {
        Gate::authorize('inventory.stock_in');

        return view('inventory.stock-in', compact('medicine'));
    }

    public function stockIn(Request $request, Medicine $medicine)
    {
        Gate::authorize('inventory.stock_in');

        $validated = $request->validate([
            'batch_number' => 'required|string|max:100',
            'quantity' => 'required|integer|min:1',
            'received_date' => 'required|date',
            'expiry_date' => 'required|date|after_or_equal:received_date',
            'unit_cost' => 'nullable|numeric|min:0',
            'supplier' => 'nullable|string|max:255',
            'reason' => 'nullable|string|max:500',
        ]);

        DB::transaction(function () use ($medicine, $validated) {
            $batch = InventoryBatch::create([
                'medicine_id' => $medicine->id,
                'batch_number' => $validated['batch_number'],
                'quantity' => 0,
                'received_date' => $validated['received_date'],
                'expiry_date' => $validated['expiry_date'],
                'unit_cost' => $validated['unit_cost'] ?? null,
                'supplier' => $validated['supplier'] ?? null,
                'notes' => $validated['reason'] ?? null,
                'status' => InventoryBatch::STATUS_ACTIVE,
            ]);

            $batch->stockIn($validated['quantity'], $validated['reason'] ?? 'Stock replenishment', auth()->id());
        });

        return redirect()->route('medicines.show', $medicine)
            ->with('success', "Stock in: +{$validated['quantity']} units recorded for batch {$validated['batch_number']}.");
    }

    public function stockOutForm(Medicine $medicine)
    {
        Gate::authorize('inventory.stock_out');

        $batches = $medicine->fefoBatches()->get();

        return view('inventory.stock-out', compact('medicine', 'batches'));
    }

    public function stockOut(Request $request, Medicine $medicine)
    {
        Gate::authorize('inventory.stock_out');

        $validated = $request->validate([
            'inventory_batch_id' => 'required|integer|exists:inventory_batches,id',
            'quantity' => 'required|integer|min:1',
            'reason' => 'nullable|string|max:500',
        ]);

        $batch = InventoryBatch::where('medicine_id', $medicine->id)
            ->findOrFail($validated['inventory_batch_id']);

        try {
            $batch->stockOut($validated['quantity'], $validated['reason'] ?? null, auth()->id());
        } catch (\RuntimeException $e) {
            return back()->withErrors(['quantity' => $e->getMessage()])->withInput();
        }

        return redirect()->route('medicines.show', $medicine)
            ->with('success', "Stock out: -{$validated['quantity']} units recorded from batch {$batch->batch_number}.");
    }

    public function adjustForm(Medicine $medicine)
    {
        Gate::authorize('inventory.adjust');

        $batches = $medicine->inventoryBatches()
            ->where('quantity', '>', 0)
            ->orderBy('expiry_date')
            ->get();

        return view('inventory.adjust', compact('medicine', 'batches'));
    }

    public function adjust(Request $request, Medicine $medicine)
    {
        Gate::authorize('inventory.adjust');

        $validated = $request->validate([
            'inventory_batch_id' => 'required|integer|exists:inventory_batches,id',
            'quantity' => 'required|integer|min:1',
            'direction' => 'required|in:increase,decrease',
            'reason' => 'required|string|max:500',
        ]);

        $batch = InventoryBatch::where('medicine_id', $medicine->id)
            ->findOrFail($validated['inventory_batch_id']);

        try {
            $batch->adjust(
                $validated['quantity'],
                $validated['direction'] === 'increase',
                $validated['reason'],
                auth()->id()
            );
        } catch (\RuntimeException $e) {
            return back()->withErrors(['quantity' => $e->getMessage()])->withInput();
        }

        return redirect()->route('medicines.show', $medicine)
            ->with('success', "Stock adjustment recorded for batch {$batch->batch_number}.");
    }

    /**
     * Write off the remaining quantity of an expired batch (expiry workflow).
     */
    public function expireBatch(Request $request, InventoryBatch $batch)
    {
        Gate::authorize('inventory.adjust');

        if (! $batch->isExpired()) {
            return back()->with('error', 'This batch is not expired yet.');
        }

        try {
            $batch->writeOffExpired(auth()->id());
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        return redirect()->route('medicines.show', $batch->medicine_id)
            ->with('success', "Expired stock from batch {$batch->batch_number} written off.");
    }

    /**
     * Batch-level expiry report.
     */
    public function expiry(Request $request)
    {
        Gate::authorize('inventory.view');

        $query = InventoryBatch::with('medicine');
        $status = $request->input('status');

        if ($status === 'expired') {
            $query->where('quantity', '>', 0)->where('expiry_date', '<=', now());
        } elseif ($status === 'expiring') {
            $query->where('quantity', '>', 0)
                ->where('expiry_date', '>', now())
                ->where('expiry_date', '<=', now()->addDays(30));
        } elseif ($status === 'active') {
            $query->where('quantity', '>', 0)
                ->where(function ($q) {
                    $q->whereNull('expiry_date')->orWhere('expiry_date', '>', now()->addDays(30));
                });
        } elseif ($status === 'depleted') {
            $query->where('quantity', 0);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('batch_number', 'like', "%{$search}%")
                  ->orWhereHas('medicine', function ($m) use ($search) {
                      $m->where('name', 'like', "%{$search}%")
                        ->orWhere('generic_name', 'like', "%{$search}%");
                  });
            });
        }

        $batches = $query
            ->orderByRaw('expiry_date IS NULL')
            ->orderBy('expiry_date', 'asc')
            ->orderBy('id', 'asc')
            ->paginate(20)
            ->withQueryString();

        $expiredCount = InventoryBatch::where('quantity', '>', 0)->where('expiry_date', '<=', now())->count();
        $expiringCount = InventoryBatch::where('quantity', '>', 0)
            ->where('expiry_date', '>', now())
            ->where('expiry_date', '<=', now()->addDays(30))->count();
        $activeCount = InventoryBatch::where('quantity', '>', 0)
            ->where(function ($q) {
                $q->whereNull('expiry_date')->orWhere('expiry_date', '>', now()->addDays(30));
            })->count();
        $depletedCount = InventoryBatch::where('quantity', 0)->count();

        return view('inventory.expiry', compact(
            'batches', 'expiredCount', 'expiringCount', 'activeCount', 'depletedCount', 'status'
        ));
    }

    /**
     * Delete an unused stock batch. A batch with any transaction history
     * (stock-out, adjustment, dispensing, expiry) is never deleted — the audit
     * trail is preserved. Enforced server-side, never just in the UI.
     */
    public function destroyBatch(Request $request, InventoryBatch $batch)
    {
        Gate::authorize('inventory.adjust');

        if (! $batch->canDelete()) {
            return back()->with('error', "Batch {$batch->batch_number} cannot be deleted. {$batch->deleteBlockReason()}");
        }

        $medicine = $batch->medicine;

        $batch->delete();

        if ($medicine) {
            $medicine->reconcileStock();
        }

        return back()->with('success', "Unused stock batch {$batch->batch_number} deleted.");
    }

    public function dispenseForm(Prescription $prescription)
    {
        Gate::authorize('inventory.dispense');

        $prescription->load([
            'items.medicine.inventoryBatches' => fn ($q) => $q->orderByRaw('expiry_date IS NULL')
                ->orderBy('expiry_date', 'asc')
                ->orderBy('id', 'asc'),
            'patient',
            'doctor',
        ]);

        return view('inventory.dispense', compact('prescription'));
    }

    public function dispense(Request $request, Prescription $prescription)
    {
        Gate::authorize('inventory.dispense');

        $validated = $request->validate([
            'dispensed_quantities' => 'required|array',
            'dispensed_quantities.*' => 'required|integer|min:0',
            'batch_selections' => 'nullable|array',
            'batch_selections.*' => 'nullable|integer|exists:inventory_batches,id',
        ]);

        $prescription->load(['items.medicine']);

        DB::beginTransaction();

        try {
            foreach ($prescription->items as $item) {
                $dispensedQty = $validated['dispensed_quantities'][$item->id] ?? 0;

                if ($dispensedQty > $item->quantity) {
                    DB::rollBack();

                    return back()->withErrors(['dispensed_quantities.' . $item->id => 'Cannot dispense more than prescribed (' . $item->quantity . ')'])->withInput();
                }

                if ($dispensedQty <= 0) {
                    continue;
                }

                $medicine = $item->medicine;

                if (! $medicine) {
                    DB::rollBack();

                    return back()->withErrors(['dispensed_quantities.' . $item->id => 'Medicine no longer exists.'])->withInput();
                }

                $selectedBatchId = $validated['batch_selections'][$item->id] ?? null;
                $dispenseReason = "Dispensed for prescription {$prescription->prescription_number}";

                if ($selectedBatchId) {
                    // Manual batch selection by an authorized user.
                    $batch = InventoryBatch::where('medicine_id', $medicine->id)->findOrFail($selectedBatchId);

                    if ($batch->isExpired()) {
                        DB::rollBack();

                        return back()->withErrors(['dispensed_quantities.' . $item->id => 'Cannot dispense from expired batch ' . $batch->batch_number])->withInput();
                    }

                    if ($dispensedQty > $batch->quantity) {
                        DB::rollBack();

                        return back()->withErrors(['dispensed_quantities.' . $item->id => 'Insufficient stock in batch ' . $batch->batch_number . '. Available: ' . $batch->quantity])->withInput();
                    }

                    $batch->stockOut($dispensedQty, $dispenseReason, auth()->id(), $prescription, 'dispensed');
                } else {
                    // Automatic FEFO selection.
                    try {
                        $medicine->deductFromBatches($dispensedQty, $dispenseReason, auth()->id(), $prescription);
                    } catch (\RuntimeException $e) {
                        DB::rollBack();

                        return back()->withErrors(['dispensed_quantities.' . $item->id => $e->getMessage()])->withInput();
                    }
                }
            }

            $prescription->markAsDispensed();
            DB::commit();

            return redirect()->route('prescriptions.show', $prescription)
                ->with('success', 'Prescription dispensed successfully.');
        } catch (\Exception $e) {
            DB::rollBack();

            return back()->with('error', 'Dispensing failed: ' . $e->getMessage())->withInput();
        }
    }
}
