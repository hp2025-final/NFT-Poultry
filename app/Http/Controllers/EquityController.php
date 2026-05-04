<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\EquityTxn;
use App\Models\Account;

class EquityController extends Controller
{
    public function index(Request $request)
    {
        $query = EquityTxn::with('account')->orderBy('date', 'desc')->orderBy('id', 'desc');

        if ($request->filled('start_date')) {
            $query->where('date', '>=', $request->start_date);
        }
        if ($request->filled('end_date')) {
            $query->where('date', '<=', $request->end_date);
        }

        $txns = $query->paginate(15);
        $totalCapital = EquityTxn::where('type', 'capital')->sum('amount');
        $totalDrawings = EquityTxn::where('type', 'drawing')->sum('amount');
        $netEquity = $totalCapital - $totalDrawings;

        return view('equity.index', compact('txns', 'totalCapital', 'totalDrawings', 'netEquity'));
    }

    public function create()
    {
        $accounts = Account::where('is_active', true)->orderBy('name')->get();
        return view('equity.form', compact('accounts'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'date' => 'required|date',
            'account_id' => 'required|exists:accounts,id',
            'type' => 'required|in:capital,drawing',
            'amount' => 'required|numeric|min:0.01',
            'note' => 'nullable|string|max:255',
        ]);

        EquityTxn::create($request->all());

        return redirect()->route('equity.index')->with('success', 'Equity transaction recorded successfully.');
    }
}
