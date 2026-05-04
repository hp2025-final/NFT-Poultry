<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Account;

class AccountController extends Controller
{
    public function index(Request $request)
    {
        $query = Account::query();

        if ($request->filled('q')) {
            $query->where('name', 'like', '%' . $request->q . '%');
        }

        $show = $request->get('show', 'active');
        if ($show === 'active') {
            $query->where('is_active', true);
        } elseif ($show === 'archived') {
            $query->where('is_active', false);
        }

        $accounts = $query->orderBy('name')->paginate(15);
        
        return view('accounts.index', compact('accounts', 'show'));
    }

    public function create()
    {
        return view('accounts.form');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|string|in:cash,bank',
            'opening_balance' => 'nullable|numeric',
        ]);

        Account::create([
            'name' => $request->name,
            'type' => $request->type,
            'opening_balance' => $request->opening_balance ?? 0,
            'is_active' => true,
        ]);

        return redirect()->route('accounts.index')->with('success', 'Account created successfully.');
    }

    public function edit(Account $account)
    {
        return view('accounts.form', compact('account'));
    }

    public function update(Request $request, Account $account)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|string|in:cash,bank',
            'opening_balance' => 'nullable|numeric',
        ]);

        $account->update([
            'name' => $request->name,
            'type' => $request->type,
            'opening_balance' => $request->opening_balance ?? 0,
        ]);

        return redirect()->route('accounts.index')->with('success', 'Account updated successfully.');
    }

    public function toggleActive(Account $account)
    {
        $account->update(['is_active' => !$account->is_active]);
        $status = $account->is_active ? 'restored' : 'archived';
        return redirect()->back()->with('success', "Account $status successfully.");
    }

    public function show(Request $request, Account $account)
    {
        $openingBalance = $account->opening_balance;
        
        if ($request->filled('start_date')) {
            $pastReceipts = \App\Models\Receipt::where('account_id', $account->id)->where('date', '<', $request->start_date)->sum('amount');
            $pastPayments = \App\Models\Payment::where('account_id', $account->id)->where('date', '<', $request->start_date)->sum('amount');
            $pastExpenses = \App\Models\Expense::where('account_id', $account->id)->where('date', '<', $request->start_date)->sum('amount');
            $pastCapital = \App\Models\EquityTxn::where('account_id', $account->id)->where('type', 'capital')->where('date', '<', $request->start_date)->sum('amount');
            $pastDrawings = \App\Models\EquityTxn::where('account_id', $account->id)->where('type', 'drawing')->where('date', '<', $request->start_date)->sum('amount');
            
            $openingBalance += ($pastReceipts + $pastCapital - $pastPayments - $pastExpenses - $pastDrawings);
        }

        $receipts = \App\Models\Receipt::where('account_id', $account->id)
            ->when($request->start_date, fn($query) => $query->where('date', '>=', $request->start_date))
            ->when($request->end_date, fn($query) => $query->where('date', '<=', $request->end_date))
            ->get()->map(function($r) {
                return [
                    'date' => $r->date, 'type' => 'Receipt', 'ref' => '#RCT-' . $r->id,
                    'note' => $r->note, 'debit' => $r->amount, 'credit' => 0
                ];
            });

        $payments = \App\Models\Payment::where('account_id', $account->id)
            ->when($request->start_date, fn($query) => $query->where('date', '>=', $request->start_date))
            ->when($request->end_date, fn($query) => $query->where('date', '<=', $request->end_date))
            ->get()->map(function($p) {
                return [
                    'date' => $p->date, 'type' => 'Payment', 'ref' => '#PAY-' . $p->id,
                    'note' => $p->note, 'debit' => 0, 'credit' => $p->amount
                ];
            });

        $expenses = \App\Models\Expense::where('account_id', $account->id)
            ->when($request->start_date, fn($query) => $query->where('date', '>=', $request->start_date))
            ->when($request->end_date, fn($query) => $query->where('date', '<=', $request->end_date))
            ->get()->map(function($e) {
                return [
                    'date' => $e->date, 'type' => 'Expense', 'ref' => '#EXP-' . $e->id,
                    'note' => $e->note, 'debit' => 0, 'credit' => $e->amount
                ];
            });

        $equities = \App\Models\EquityTxn::where('account_id', $account->id)
            ->when($request->start_date, fn($query) => $query->where('date', '>=', $request->start_date))
            ->when($request->end_date, fn($query) => $query->where('date', '<=', $request->end_date))
            ->get()->map(function($eq) {
                return [
                    'date' => $eq->date, 'type' => 'Equity', 'ref' => '#EQT-' . $eq->id,
                    'note' => $eq->note, 
                    'debit' => $eq->type === 'capital' ? $eq->amount : 0, 
                    'credit' => $eq->type === 'drawing' ? $eq->amount : 0
                ];
            });

        $transactions = $receipts->concat($payments)->concat($expenses)->concat($equities)->sortBy('date')->values()->toArray();
        
        $runningBalance = $openingBalance;
        foreach ($transactions as &$txn) {
            $runningBalance += $txn['debit'];
            $runningBalance -= $txn['credit'];
            $txn['balance'] = $runningBalance;
        }

        $perPage = 30;
        $page = $request->get('page', 1);
        $offset = ($page - 1) * $perPage;
        
        $paginatedItems = array_slice($transactions, $offset, $perPage);
        $paginator = new \Illuminate\Pagination\LengthAwarePaginator(
            $paginatedItems, count($transactions), $perPage, $page,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        $broughtForward = $page == 1 ? $openingBalance : ($offset > 0 && isset($transactions[$offset - 1]) ? $transactions[$offset - 1]['balance'] : $openingBalance);

        return view('accounts.show', compact('account', 'paginator', 'openingBalance', 'runningBalance', 'broughtForward'));
    }
}
