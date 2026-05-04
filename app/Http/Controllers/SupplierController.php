<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Supplier;

class SupplierController extends Controller
{
    public function index(Request $request)
    {
        $query = Supplier::query();

        if ($request->filled('q')) {
            $search = $request->q;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', '%' . $search . '%')
                  ->orWhere('phone', 'like', '%' . $search . '%');
            });
        }

        $show = $request->get('show', 'active');
        if ($show === 'active') {
            $query->where('is_active', true);
        } elseif ($show === 'archived') {
            $query->where('is_active', false);
        }

        $startDate = $request->get('start_date', date('Y-m-d'));
        $endDate   = $request->get('end_date', date('Y-m-d'));

        $suppliers = $query->orderBy('name')->get();

        // Calculate financial data for each supplier
        foreach ($suppliers as $supplier) {
            // Opening balance = base opening + all transactions before start_date
            $pastPurchases = \App\Models\Purchase::where('supplier_id', $supplier->id)->where('date', '<', $startDate)->sum('total_amount');
            $pastPayments  = \App\Models\Payment::where('supplier_id', $supplier->id)->where('date', '<', $startDate)->sum('amount');
            $supplier->computed_opening = $supplier->opening_balance + $pastPurchases - $pastPayments;

            // Dr = total payments between dates (we paid them)
            $supplier->computed_dr = \App\Models\Payment::where('supplier_id', $supplier->id)
                ->whereBetween('date', [$startDate, $endDate])->sum('amount');

            // Cr = total purchases between dates (we owe them)
            $supplier->computed_cr = \App\Models\Purchase::where('supplier_id', $supplier->id)
                ->whereBetween('date', [$startDate, $endDate])->sum('total_amount');

            // Balance (closing) = opening + cr - dr
            $supplier->computed_balance = $supplier->computed_opening + $supplier->computed_cr - $supplier->computed_dr;
        }

        return view('suppliers.index', compact('suppliers', 'show', 'startDate', 'endDate'));
    }

    public function create()
    {
        return view('suppliers.form');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:200',
            'phone' => 'nullable|string|max:50',
            'opening_balance' => 'nullable|numeric',
        ]);

        Supplier::create([
            'name' => $request->name,
            'phone' => $request->phone,
            'opening_balance' => $request->opening_balance ?? 0,
            'is_active' => true,
        ]);

        return redirect()->route('suppliers.index')->with('success', 'Supplier created successfully.');
    }

    public function edit(Supplier $supplier)
    {
        return view('suppliers.form', compact('supplier'));
    }

    public function update(Request $request, Supplier $supplier)
    {
        $request->validate([
            'name' => 'required|string|max:200',
            'phone' => 'nullable|string|max:50',
            'opening_balance' => 'nullable|numeric',
        ]);

        $supplier->update([
            'name' => $request->name,
            'phone' => $request->phone,
            'opening_balance' => $request->opening_balance ?? 0,
        ]);

        return redirect()->route('suppliers.index')->with('success', 'Supplier updated successfully.');
    }

    public function toggleActive(Supplier $supplier)
    {
        $supplier->update(['is_active' => !$supplier->is_active]);
        $status = $supplier->is_active ? 'restored' : 'archived';
        return redirect()->back()->with('success', "Supplier $status successfully.");
    }

    public function show(Request $request, Supplier $supplier)
    {
        $openingBalance = $supplier->opening_balance;
        
        if ($request->filled('start_date')) {
            $pastPurchases = \App\Models\Purchase::where('supplier_id', $supplier->id)->where('date', '<', $request->start_date)->sum('total_amount');
            $pastPayments = \App\Models\Payment::where('supplier_id', $supplier->id)->where('date', '<', $request->start_date)->sum('amount');
            $openingBalance += ($pastPurchases - $pastPayments);
        }

        $purchases = \App\Models\Purchase::with('items.product')->where('supplier_id', $supplier->id)
            ->when($request->start_date, fn($query) => $query->where('date', '>=', $request->start_date))
            ->when($request->end_date, fn($query) => $query->where('date', '<=', $request->end_date))
            ->get()->map(function($purchase) {
                $itemsDesc = $purchase->items->map(function($item) {
                    $qtyStr = fmod($item->qty, 1) == 0 ? intval($item->qty) : number_format($item->qty, 2);
                    $priceStr = number_format($item->price, 2);
                    $unit = $item->product->unit ?? 'Units';
                    return "{$item->product->name} {$qtyStr} {$unit} x Rs. {$priceStr}";
                })->implode(', ');
                
                $ref = '#INV-' . str_pad($purchase->id, 3, '0', STR_PAD_LEFT);
                if ($itemsDesc) {
                    $ref .= ' ' . $itemsDesc;
                }

                return [
                    'date' => $purchase->date,
                    'type' => 'Purchase Invoice',
                    'ref' => $ref,
                    'debit' => 0,
                    'credit' => $purchase->total_amount, // We owe them 
                    'note' => $purchase->note,
                ];
            });

        $payments = \App\Models\Payment::where('supplier_id', $supplier->id)
            ->when($request->start_date, fn($query) => $query->where('date', '>=', $request->start_date))
            ->when($request->end_date, fn($query) => $query->where('date', '<=', $request->end_date))
            ->get()->map(function($payment) {
                return [
                    'date' => $payment->date,
                    'type' => 'Cash Payment',
                    'ref' => '#PAY-' . str_pad($payment->id, 3, '0', STR_PAD_LEFT),
                    'debit' => $payment->amount, // We paid them
                    'credit' => 0,
                    'note' => $payment->note,
                ];
            });

        $transactions = $purchases->concat($payments)->sortBy('date')->values()->toArray();
        
        $runningBalance = $openingBalance;
        foreach ($transactions as &$txn) {
            $runningBalance += $txn['credit'];
            $runningBalance -= $txn['debit'];
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

        return view('suppliers.show', compact('supplier', 'paginator', 'openingBalance', 'runningBalance', 'broughtForward'));
    }
}
