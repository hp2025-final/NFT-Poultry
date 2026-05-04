<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Customer;

class CustomerController extends Controller
{
    public function index(Request $request)
    {
        $query = Customer::query();

        if ($request->filled('q')) {
            $search = $request->q;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', '%' . $search . '%')
                  ->orWhere('phone', 'like', '%' . $search . '%');
            });
        }

        $show = $request->get('show', 'active');
        if (in_array($show, ['active', 'active_transactions'])) {
            $query->where('is_active', true);
        } elseif ($show === 'archived') {
            $query->where('is_active', false);
        }

        $startDate = $request->get('start_date', date('Y-m-d'));
        $endDate   = $request->get('end_date', date('Y-m-d'));

        $customers = $query->orderBy('name')->get();

        // Calculate financial data for each customer
        foreach ($customers as $customer) {
            // Opening balance = base opening + all transactions before start_date
            $pastSales    = \App\Models\Sale::where('customer_id', $customer->id)->where('date', '<', $startDate)->sum('total_amount');
            $pastReceipts = \App\Models\Receipt::where('customer_id', $customer->id)->where('date', '<', $startDate)->sum('amount');
            $customer->computed_opening = $customer->opening_balance + $pastSales - $pastReceipts;

            // Dr = total sales between dates
            $customer->computed_dr = \App\Models\Sale::where('customer_id', $customer->id)
                ->whereBetween('date', [$startDate, $endDate])->sum('total_amount');

            // Cr = total receipts between dates
            $customer->computed_cr = \App\Models\Receipt::where('customer_id', $customer->id)
                ->whereBetween('date', [$startDate, $endDate])->sum('amount');

            // Balance (closing) = opening + dr - cr
            $customer->computed_balance = $customer->computed_opening + $customer->computed_dr - $customer->computed_cr;
        }

        if (in_array($show, ['active_transactions', 'all_transactions'])) {
            $customers = $customers->filter(function ($customer) {
                return $customer->computed_dr != 0 || $customer->computed_cr != 0;
            })->values();
        }

        return view('customers.index', compact('customers', 'show', 'startDate', 'endDate'));
    }

    public function create()
    {
        return view('customers.form');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:200',
            'phone' => 'nullable|string|max:50',
            'opening_balance' => 'nullable|numeric',
        ]);

        Customer::create([
            'name' => $request->name,
            'phone' => $request->phone,
            'opening_balance' => $request->opening_balance ?? 0,
            'is_active' => true,
        ]);

        return redirect()->route('customers.index')->with('success', 'Customer created successfully.');
    }

    public function edit(Customer $customer)
    {
        return view('customers.form', compact('customer'));
    }

    public function update(Request $request, Customer $customer)
    {
        $request->validate([
            'name' => 'required|string|max:200',
            'phone' => 'nullable|string|max:50',
            'opening_balance' => 'nullable|numeric',
        ]);

        $customer->update([
            'name' => $request->name,
            'phone' => $request->phone,
            'opening_balance' => $request->opening_balance ?? 0,
        ]);

        return redirect()->route('customers.index')->with('success', 'Customer updated successfully.');
    }

    public function toggleActive(Customer $customer)
    {
        $customer->update(['is_active' => !$customer->is_active]);
        $status = $customer->is_active ? 'restored' : 'archived';
        return redirect()->back()->with('success', "Customer $status successfully.");
    }

    public function show(Request $request, Customer $customer)
    {
        $openingBalance = $customer->opening_balance;
        
        if ($request->filled('start_date')) {
            $pastSales = \App\Models\Sale::where('customer_id', $customer->id)->where('date', '<', $request->start_date)->sum('total_amount');
            $pastReceipts = \App\Models\Receipt::where('customer_id', $customer->id)->where('date', '<', $request->start_date)->sum('amount');
            $openingBalance += ($pastSales - $pastReceipts);
        }

        $sales = \App\Models\Sale::with('items.product')->where('customer_id', $customer->id)
            ->when($request->start_date, fn($query) => $query->where('date', '>=', $request->start_date))
            ->when($request->end_date, fn($query) => $query->where('date', '<=', $request->end_date))
            ->get()->map(function($sale) {
                $itemsDesc = $sale->items->map(function($item) {
                    $qtyStr = fmod($item->qty, 1) == 0 ? intval($item->qty) : number_format($item->qty, 2);
                    $priceStr = number_format($item->price, 2);
                    $unit = $item->product->unit ?? 'Units';
                    return "{$item->product->name} {$qtyStr} {$unit} x Rs. {$priceStr}";
                })->implode(', ');
                
                $ref = '#INV-' . str_pad($sale->id, 3, '0', STR_PAD_LEFT);
                if ($itemsDesc) {
                    $ref .= ' ' . $itemsDesc;
                }

                return [
                    'date' => $sale->date,
                    'type' => 'Sale Invoice',
                    'ref' => $ref,
                    'debit' => $sale->total_amount,
                    'credit' => 0,
                    'note' => $sale->note,
                ];
            });

        $receipts = \App\Models\Receipt::where('customer_id', $customer->id)
            ->when($request->start_date, fn($query) => $query->where('date', '>=', $request->start_date))
            ->when($request->end_date, fn($query) => $query->where('date', '<=', $request->end_date))
            ->get()->map(function($receipt) {
                return [
                    'date' => $receipt->date,
                    'type' => 'Cash Receipt',
                    'ref' => '#RCT-' . str_pad($receipt->id, 3, '0', STR_PAD_LEFT),
                    'debit' => 0,
                    'credit' => $receipt->amount,
                    'note' => $receipt->note,
                ];
            });

        $transactions = $sales->concat($receipts)->sortBy('date')->values()->toArray();
        
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

        return view('customers.show', compact('customer', 'paginator', 'openingBalance', 'runningBalance', 'broughtForward'));
    }
}
