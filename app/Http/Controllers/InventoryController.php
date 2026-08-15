<?php

namespace App\Http\Controllers;

use App\Models\Medicine;
use App\Models\Prescription;
use App\Models\StockMovement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

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
            if ($request->expiry_status === 'expired') {
                $query->where('expiry_date', '<', now());
            } elseif ($request->expiry_status === 'expiring') {
                $query->where('expiry_date', '>=', now())
                      ->where('expiry_date', '<=', now()->addDays(30));
            } elseif ($request->expiry_status === 'normal') {
                $query->where(function ($q) {
                    $q->whereNull('expiry_date')->orWhere('expiry_date', '>', now()->addDays(30));
                });
            }
        }

        $medicines = $query->orderBy('name')->paginate(15)->withQueryString();
        $categories = Medicine::whereNotNull('category')->distinct()->pluck('category');

        return view('inventory.index', compact('medicines', 'categories'));
    }

    public function dashboard()
    {
        Gate::authorize('inventory.view');

        $totalMedicines = Medicine::count();
        $lowStock = Medicine::whereColumn('stock_quantity', '<=', 'minimum_stock_level')->count();
        $outOfStock = Medicine::where('stock_quantity', 0)->count();
        $expired = Medicine::where('expiry_date', '<', now())->count();
        $expiringSoon = Medicine::where('expiry_date', '>=', now())
            ->where('expiry_date', '<=', now()->addDays(30))
            ->count();

        $recentMovements = StockMovement::with(['medicine', 'performer'])
            ->latest('movement_date')
            ->latest('id')
            ->take(10)
            ->get();

        $lowStockMedicines = Medicine::with('prescriptionItems')
            ->whereColumn('stock_quantity', '<=', 'minimum_stock_level')
            ->orderBy('stock_quantity')
            ->take(5)
            ->get();

        $expiringMedicines = Medicine::where('expiry_date', '>=', now())
            ->where('expiry_date', '<=', now()->addDays(30))
            ->orderBy('expiry_date')
            ->take(5)
            ->get();

        $expiredMedicines = Medicine::where('expiry_date', '<', now())
            ->orderBy('expiry_date')
            ->take(5)
            ->get();

        $totalStockValue = Medicine::query()
            ->selectRaw('SUM(COALESCE(stock_quantity, 0) * COALESCE(unit_price, 0)) as value')
            ->value('value') ?? 0;

        return view('inventory.dashboard', compact(
            'totalMedicines', 'lowStock', 'outOfStock', 'expired', 'expiringSoon',
            'recentMovements', 'lowStockMedicines', 'expiringMedicines',
            'expiredMedicines', 'totalStockValue'
        ));
    }

    public function movements(Request $request)
    {
        Gate::authorize('inventory.view');

        $query = StockMovement::with(['medicine', 'performer']);

        if ($request->filled('medicine_id')) {
            $query->where('medicine_id', $request->medicine_id);
        }

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('date_from')) {
            $query->where('movement_date', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->where('movement_date', '<=', $request->date_to);
        }

        $movements = $query->orderBy('movement_date', 'desc')
            ->orderBy('id', 'desc')
            ->paginate(20)
            ->withQueryString();

        $medicines = Medicine::orderBy('name')->pluck('name', 'id');

        return view('inventory.movements', compact('movements', 'medicines'));
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
            'quantity' => 'required|integer|min:1',
            'reason' => 'nullable|string|max:500',
        ]);

        $medicine->stockIn(
            $validated['quantity'],
            $validated['reason'] ?? 'Stock replenishment',
            auth()->id()
        );

        return redirect()->route('medicines.show', $medicine)
            ->with('success', "Stock in: +{$validated['quantity']} units recorded.");
    }

    public function stockOutForm(Medicine $medicine)
    {
        Gate::authorize('inventory.stock_out');

        return view('inventory.stock-out', compact('medicine'));
    }

    public function stockOut(Request $request, Medicine $medicine)
    {
        Gate::authorize('inventory.stock_out');

        $validated = $request->validate([
            'quantity' => 'required|integer|min:1',
            'reason' => 'nullable|string|max:500',
        ]);

        if ($validated['quantity'] > $medicine->stock_quantity) {
            return back()->withErrors(['quantity' => 'Insufficient stock. Available: ' . $medicine->stock_quantity])->withInput();
        }

        $medicine->stockOut(
            $validated['quantity'],
            $validated['reason'] ?? null,
            auth()->id()
        );

        return redirect()->route('medicines.show', $medicine)
            ->with('success', "Stock out: -{$validated['quantity']} units recorded.");
    }

    public function adjustForm(Medicine $medicine)
    {
        Gate::authorize('inventory.adjust');

        return view('inventory.adjust', compact('medicine'));
    }

    public function adjust(Request $request, Medicine $medicine)
    {
        Gate::authorize('inventory.adjust');

        $validated = $request->validate([
            'quantity' => 'required|integer|min:1',
            'direction' => 'required|in:increase,decrease',
            'reason' => 'required|string|max:500',
        ]);

        if ($validated['direction'] === 'decrease' && $validated['quantity'] > $medicine->stock_quantity) {
            return back()->withErrors(['quantity' => 'Insufficient stock. Available: ' . $medicine->stock_quantity])->withInput();
        }

        $medicine->adjust(
            $validated['quantity'],
            $validated['direction'] === 'increase',
            $validated['reason'],
            auth()->id()
        );

        return redirect()->route('medicines.show', $medicine)
            ->with('success', "Stock adjustment recorded.");
    }

    public function dispenseForm(Prescription $prescription)
    {
        Gate::authorize('inventory.dispense');

        $prescription->load(['items.medicine', 'patient', 'doctor']);

        return view('inventory.dispense', compact('prescription'));
    }

    public function dispense(Request $request, Prescription $prescription)
    {
        Gate::authorize('inventory.dispense');

        $validated = $request->validate([
            'dispensed_quantities' => 'required|array',
            'dispensed_quantities.*' => 'required|integer|min:0',
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

                if ($dispensedQty > 0) {
                    if ($dispensedQty > $item->medicine->stock_quantity) {
                        DB::rollBack();
                        return back()->withErrors(['dispensed_quantities.' . $item->id => 'Insufficient stock. Available: ' . $item->medicine->stock_quantity])->withInput();
                    }

                    $item->medicine->stockOut(
                        $dispensedQty,
                        "Dispensed for prescription {$prescription->prescription_number}",
                        auth()->id(),
                        $prescription
                    );
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
