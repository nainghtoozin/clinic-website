<?php

namespace App\Http\Controllers;

use App\Models\LabTest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class LabTestController extends Controller
{
    public function index(Request $request)
    {
        Gate::authorize('lab_test.view');

        $query = LabTest::query();

        if ($request->filled('search')) {
            $query->search($request->search);
        }

        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        if ($request->filled('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }

        $categories = LabTest::distinct()->whereNotNull('category')->pluck('category')->sort()->values();
        $labTests = $query->latest()->paginate(15)->withQueryString();

        return view('lab-tests.index', compact('labTests', 'categories'));
    }

    public function create()
    {
        Gate::authorize('lab_test.create');

        return view('lab-tests.create');
    }

    public function store(Request $request)
    {
        Gate::authorize('lab_test.create');

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:lab_tests,code',
            'category' => 'nullable|string|max:255',
            'description' => 'nullable|string|max:1000',
            'sample_type' => 'nullable|string|max:255',
            'reference_range' => 'nullable|string|max:255',
            'unit' => 'nullable|string|max:50',
            'price' => 'required|numeric|min:0',
            'is_active' => 'boolean',
        ]);

        LabTest::create($validated);

        return redirect()->route('lab-tests.index')
            ->with('success', 'Lab test created successfully.');
    }

    public function show(LabTest $labTest)
    {
        Gate::authorize('lab_test.view');

        $labTest->loadCount('investigations');

        return view('lab-tests.show', compact('labTest'));
    }

    public function edit(LabTest $labTest)
    {
        Gate::authorize('lab_test.edit');

        return view('lab-tests.edit', compact('labTest'));
    }

    public function update(Request $request, LabTest $labTest)
    {
        Gate::authorize('lab_test.edit');

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:lab_tests,code,' . $labTest->id,
            'category' => 'nullable|string|max:255',
            'description' => 'nullable|string|max:1000',
            'sample_type' => 'nullable|string|max:255',
            'reference_range' => 'nullable|string|max:255',
            'unit' => 'nullable|string|max:50',
            'price' => 'required|numeric|min:0',
            'is_active' => 'boolean',
        ]);

        $labTest->update($validated);

        return redirect()->route('lab-tests.index')
            ->with('success', 'Lab test updated successfully.');
    }

    public function destroy(LabTest $labTest)
    {
        Gate::authorize('lab_test.delete');

        $labTest->delete();

        return redirect()->route('lab-tests.index')
            ->with('success', 'Lab test deleted successfully.');
    }
}
