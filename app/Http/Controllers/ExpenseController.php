<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Models\Account;
use Carbon\Carbon;

class ExpenseController extends Controller
{
    public function index(Request $request)
    {
        $query = Expense::with(['category', 'account']);

        if ($request->filled('start_date') && $request->filled('end_date')) {
            $query->whereBetween('date', [$request->start_date, $request->end_date]);
        } else {
            // Default to current month
            $query->whereBetween('date', [Carbon::now()->startOfMonth()->format('Y-m-d'), Carbon::now()->format('Y-m-d')]);
        }

        $expenses = $query->orderBy('date', 'desc')->orderBy('id', 'desc')->paginate(20);
        
        return view('expenses.index', compact('expenses'));
    }

    public function create()
    {
        $categories = ExpenseCategory::where('is_active', true)->orderBy('name')->get();
        $accounts = Account::where('is_active', true)->orderBy('name')->get();
        return view('expenses.form', compact('categories', 'accounts'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'date' => 'required|date',
            'expense_category_id' => 'required|exists:expense_categories,id',
            'account_id' => 'nullable|exists:accounts,id',
            'amount' => 'required|numeric|min:0',
            'note' => 'nullable|string|max:255',
        ]);

        Expense::create([
            'date' => $request->date,
            'expense_category_id' => $request->expense_category_id,
            'account_id' => $request->account_id,
            'amount' => $request->amount,
            'note' => $request->note,
        ]);

        return redirect()->route('expenses.index')->with('success', 'Expense recorded successfully.');
    }

    public function edit(Expense $expense)
    {
        $categories = ExpenseCategory::where('is_active', true)->orderBy('name')->get();
        $accounts = Account::where('is_active', true)->orderBy('name')->get();
        return view('expenses.form', compact('expense', 'categories', 'accounts'));
    }

    public function update(Request $request, Expense $expense)
    {
        $request->validate([
            'date' => 'required|date',
            'expense_category_id' => 'required|exists:expense_categories,id',
            'account_id' => 'nullable|exists:accounts,id',
            'amount' => 'required|numeric|min:0',
            'note' => 'nullable|string|max:255',
        ]);

        $expense->update([
            'date' => $request->date,
            'expense_category_id' => $request->expense_category_id,
            'account_id' => $request->account_id,
            'amount' => $request->amount,
            'note' => $request->note,
        ]);

        return redirect()->route('expenses.index')->with('success', 'Expense updated successfully.');
    }

    public function destroy(Expense $expense)
    {
        $expense->delete();
        return redirect()->route('expenses.index')->with('success', 'Expense deleted successfully.');
    }
}
