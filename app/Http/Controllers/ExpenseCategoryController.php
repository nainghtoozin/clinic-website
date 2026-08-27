<?php

namespace App\Http\Controllers;

use App\Models\ExpenseCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;

class ExpenseCategoryController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        Gate::authorize('expense_category.view');

        $categories = ExpenseCategory::withCount('expenses')
            ->withSum('expenses', 'amount')
            ->orderBy('sort_order')
            ->orderBy('name')
            ->paginate(20);

        return view('expense-categories.index', compact('categories'));
    }

    public function store(Request $request)
    {
        Gate::authorize('expense_category.create');

        $validated = $request->validate([
            'name' => 'required|string|max:100|unique:expense_categories,name',
            'description' => 'nullable|string|max:500',
        ]);

        $validated['slug'] = Str::slug($validated['name']);

        ExpenseCategory::create($validated);

        if ($request->ajax()) {
            return response()->json(['success' => true, 'message' => 'Category created.']);
        }

        return back()->with('success', 'Category created.');
    }

    public function update(Request $request, ExpenseCategory $expenseCategory)
    {
        Gate::authorize('expense_category.edit');

        $validated = $request->validate([
            'name' => 'required|string|max:100|unique:expense_categories,name,' . $expenseCategory->id,
            'description' => 'nullable|string|max:500',
            'is_active' => 'boolean',
        ]);

        if (isset($validated['name'])) {
            $validated['slug'] = Str::slug($validated['name']);
        }

        $expenseCategory->update($validated);

        if ($request->ajax()) {
            return response()->json(['success' => true, 'message' => 'Category updated.']);
        }

        return back()->with('success', 'Category updated.');
    }

    public function destroy(ExpenseCategory $expenseCategory)
    {
        Gate::authorize('expense_category.delete');

        if ($expenseCategory->expenses()->exists()) {
            return back()->withErrors(['error' => 'Cannot delete category with existing expenses.']);
        }

        $expenseCategory->delete();

        return back()->with('success', 'Category deleted.');
    }
}
