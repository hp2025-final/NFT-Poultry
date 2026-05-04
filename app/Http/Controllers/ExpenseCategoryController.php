<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ExpenseCategory;

class ExpenseCategoryController extends Controller
{
    public function index(Request $request)
    {
        $query = ExpenseCategory::query();

        if ($request->filled('q')) {
            $query->where('name', 'like', '%' . $request->q . '%');
        }

        $show = $request->get('show', 'active');
        if ($show === 'active') {
            $query->where('is_active', true);
        } elseif ($show === 'archived') {
            $query->where('is_active', false);
        }

        $categories = $query->orderBy('name')->paginate(15);
        
        return view('expense_categories.index', compact('categories', 'show'));
    }

    public function create()
    {
        return view('expense_categories.form');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:100',
        ]);

        ExpenseCategory::create([
            'name' => $request->name,
            'is_active' => true,
        ]);

        return redirect()->route('expense_categories.index')->with('success', 'Expense category created successfully.');
    }

    public function edit(ExpenseCategory $expenseCategory)
    {
        return view('expense_categories.form', compact('expenseCategory'));
    }

    public function update(Request $request, ExpenseCategory $expenseCategory)
    {
        $request->validate([
            'name' => 'required|string|max:100',
        ]);

        $expenseCategory->update([
            'name' => $request->name,
        ]);

        return redirect()->route('expense_categories.index')->with('success', 'Expense category updated successfully.');
    }

    public function toggleActive(ExpenseCategory $expenseCategory)
    {
        $expenseCategory->update(['is_active' => !$expenseCategory->is_active]);
        $status = $expenseCategory->is_active ? 'restored' : 'archived';
        return redirect()->back()->with('success', "Expense category $status successfully.");
    }
}
