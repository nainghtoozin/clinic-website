<?php

namespace App\Http\Controllers;

use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Services\AuditService;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class ExpenseController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request)
    {
        Gate::authorize('expense.view');

        $query = Expense::with(['expenseCategory', 'createdBy']);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('expense_number', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%")
                    ->orWhere('vendor', 'like', "%{$search}%");
            });
        }

        if ($request->filled('category_id')) {
            $query->where('expense_category_id', $request->category_id);
        }

        if ($request->filled('payment_method')) {
            $query->where('payment_method', $request->payment_method);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('date_from')) {
            $query->where('expense_date', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->where('expense_date', '<=', $request->date_to);
        }

        $expenses = $query->latest('expense_date')->latest('id')->paginate(20)->withQueryString();

        $categories = ExpenseCategory::active()->orderBy('name')->get();

        return view('expenses.index', compact('expenses', 'categories'));
    }

    public function store(Request $request)
    {
        Gate::authorize('expense.create');

        $validated = $request->validate([
            'expense_category_id' => 'required|exists:expense_categories,id',
            'amount' => 'required|numeric|min:0.01|max:99999999.99',
            'payment_method' => 'required|in:' . implode(',', array_keys(Expense::PAYMENT_METHODS)),
            'expense_date' => 'required|date',
            'vendor' => 'nullable|string|max:200',
            'description' => 'required|string|max:500',
            'note' => 'nullable|string|max:2000',
            'reference_number' => 'nullable|string|max:100',
        ]);

        $validated['expense_number'] = Expense::generateExpenseNumber();
        $validated['created_by'] = auth()->id();
        $validated['status'] = 'active';

        $expense = Expense::create($validated);
        AuditService::logCreated($expense, 'Expense');

        NotificationService::notifyAdmins(
            'expense',
            'Expense Recorded',
            "Expense of \${$validated['amount']} recorded: {$validated['description']}.",
            $expense,
            'expense',
            'created'
        );

        if ($request->ajax()) {
            return response()->json(['success' => true, 'message' => 'Expense recorded.']);
        }

        return back()->with('success', 'Expense recorded.');
    }

    public function show(Expense $expense)
    {
        Gate::authorize('expense.view');

        $expense->load(['expenseCategory', 'createdBy']);

        if (request()->ajax()) {
            return response()->json($expense);
        }

        return view('expenses.show', compact('expense'));
    }

    public function update(Request $request, Expense $expense)
    {
        Gate::authorize('expense.edit');

        $validated = $request->validate([
            'expense_category_id' => 'required|exists:expense_categories,id',
            'amount' => 'required|numeric|min:0.01|max:99999999.99',
            'payment_method' => 'required|in:' . implode(',', array_keys(Expense::PAYMENT_METHODS)),
            'expense_date' => 'required|date',
            'vendor' => 'nullable|string|max:200',
            'description' => 'required|string|max:500',
            'note' => 'nullable|string|max:2000',
            'reference_number' => 'nullable|string|max:100',
        ]);

        $old = $expense->toArray();
        $expense->update($validated);
        AuditService::logUpdated($expense, 'Expense', $old, $validated);

        if ($request->ajax()) {
            return response()->json(['success' => true, 'message' => 'Expense updated.']);
        }

        return back()->with('success', 'Expense updated.');
    }

    public function destroy(Expense $expense)
    {
        Gate::authorize('expense.delete');

        $oldStatus = $expense->status;
        $expense->update(['status' => 'cancelled']);
        AuditService::logStatusChange($expense, 'Expense', $oldStatus, 'cancelled');

        return back()->with('success', 'Expense cancelled.');
    }
}
