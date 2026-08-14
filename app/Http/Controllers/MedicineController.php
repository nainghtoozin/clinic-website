<?php

namespace App\Http\Controllers;

use App\Models\Medicine;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class MedicineController extends Controller
{
    public function index(Request $request)
    {
        Gate::authorize('medicine.view');

        $query = Medicine::query();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('generic_name', 'like', "%{$search}%")
                  ->orWhere('category', 'like', "%{$search}%");
            });
        }

        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        if ($request->filled('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }

        $medicines = $query->orderBy('name')->paginate(15)->withQueryString();
        $categories = Medicine::whereNotNull('category')->distinct()->pluck('category');

        return view('medicines.index', compact('medicines', 'categories'));
    }

    public function create()
    {
        Gate::authorize('medicine.create');

        return view('medicines.create');
    }

    public function store(Request $request)
    {
        Gate::authorize('medicine.create');

        $validated = $request->validate([
            'name'            => 'required|string|max:255',
            'generic_name'    => 'nullable|string|max:255',
            'manufacturer'    => 'nullable|string|max:255',
            'category'        => 'nullable|string|max:255',
            'form'            => 'nullable|string|max:255',
            'strength'        => 'nullable|string|max:255',
            'unit'            => 'nullable|string|max:50',
            'unit_price'      => 'required|numeric|min:0',
            'cost_price'      => 'nullable|numeric|min:0',
            'selling_price'   => 'nullable|numeric|min:0',
            'stock_quantity'  => 'required|integer|min:0',
            'minimum_stock_level' => 'nullable|integer|min:0',
            'expiry_date'     => 'nullable|date',
            'is_active'       => 'boolean',
            'notes'           => 'nullable|string|max:1000',
        ]);

        $validated['is_active'] = $request->boolean('is_active');

        $medicine = Medicine::create($validated);

        return redirect()->route('medicines.show', $medicine)
            ->with('success', 'Medicine created successfully.');
    }

    public function show(Medicine $medicine)
    {
        Gate::authorize('medicine.view');

        $movements = $medicine->stockMovements()->with('performer')->latest('movement_date')->latest('id')->take(10)->get();

        return view('medicines.show', compact('medicine', 'movements'));
    }

    public function edit(Medicine $medicine)
    {
        Gate::authorize('medicine.edit');

        return view('medicines.edit', compact('medicine'));
    }

    public function update(Request $request, Medicine $medicine)
    {
        Gate::authorize('medicine.edit');

        $validated = $request->validate([
            'name'            => 'required|string|max:255',
            'generic_name'    => 'nullable|string|max:255',
            'manufacturer'    => 'nullable|string|max:255',
            'category'        => 'nullable|string|max:255',
            'form'            => 'nullable|string|max:255',
            'strength'        => 'nullable|string|max:255',
            'unit'            => 'nullable|string|max:50',
            'unit_price'      => 'required|numeric|min:0',
            'cost_price'      => 'nullable|numeric|min:0',
            'selling_price'   => 'nullable|numeric|min:0',
            'stock_quantity'  => 'required|integer|min:0',
            'minimum_stock_level' => 'nullable|integer|min:0',
            'expiry_date'     => 'nullable|date',
            'is_active'       => 'boolean',
            'notes'           => 'nullable|string|max:1000',
        ]);

        $validated['is_active'] = $request->boolean('is_active');

        $medicine->update($validated);

        return redirect()->route('medicines.show', $medicine)
            ->with('success', 'Medicine updated successfully.');
    }

    public function destroy(Medicine $medicine)
    {
        Gate::authorize('medicine.delete');

        if ($medicine->prescriptionItems()->count() > 0) {
            return back()->with('error', 'Cannot delete medicine that has been prescribed.');
        }

        $medicine->delete();

        return redirect()->route('medicines.index')
            ->with('success', 'Medicine deleted successfully.');
    }
}