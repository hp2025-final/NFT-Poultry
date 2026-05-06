<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Receipt;
use App\Models\Customer;
use App\Models\Account;
use App\Services\FinancialService;
use Carbon\Carbon;

class ReceiptController extends Controller
{
    protected $finService;

    public function __construct(FinancialService $finService)
    {
        $this->finService = $finService;
    }
    public function index(Request $request)
    {
        $startDate = $request->input('start_date', date('Y-m-d'));
        $endDate = $request->input('end_date', date('Y-m-d'));

        $receipts = Receipt::with(['customer', 'account'])
            ->whereBetween('date', [$startDate, $endDate])
            ->orderBy('date', 'desc')
            ->orderBy('id', 'desc')
            ->paginate(20);

        return view('receipts.index', compact('receipts'));
    }

    public function create()
    {
        $customers = Customer::where('is_active', true)->orderBy('name')->get();
        $accounts = Account::where('is_active', true)->orderBy('name')->get();
        return view('receipts.form', compact('customers', 'accounts'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'date' => 'required|date',
            'customer_id' => 'required|exists:customers,id',
            'account_id' => 'required|exists:accounts,id',
            'amount' => 'required|numeric|min:0.01',
            'note' => 'nullable|string|max:255',
        ]);

        Receipt::create($request->all());

        return redirect()->route('receipts.index')->with('success', 'Receipt recorded successfully.');
    }

    public function edit(Receipt $receipt)
    {
        $customers = Customer::where('is_active', true)->orderBy('name')->get();
        $accounts = Account::where('is_active', true)->orderBy('name')->get();
        return view('receipts.form', compact('receipt', 'customers', 'accounts'));
    }

    public function update(Request $request, Receipt $receipt)
    {
        $request->validate([
            'date' => 'required|date',
            'customer_id' => 'required|exists:customers,id',
            'account_id' => 'required|exists:accounts,id',
            'amount' => 'required|numeric|min:0.01',
            'note' => 'nullable|string|max:255',
        ]);

        $receipt->update($request->all());

        return redirect()->route('receipts.index')->with('success', 'Receipt updated successfully.');
    }

    public function destroy(Receipt $receipt)
    {
        $receipt->delete();
        return redirect()->route('receipts.index')->with('success', 'Receipt deleted successfully.');
    }

    public function bulk(Request $request)
    {
        $date = $request->input('date', date('Y-m-d'));
        $customers = Customer::where('is_active', true)->orderBy('name')->get();

        foreach ($customers as $customer) {
            $customer->opening_balance_on_date = $this->finService->getCustomerBalances(
                $customer->id, 
                Carbon::parse($date), 
                Carbon::parse($date)
            )->opening;
        }

        $accounts = Account::where('is_active', true)->orderBy('name')->get();
        // Default to Cash1 account
        $defaultAccount = $accounts->first(function ($acc) {
            return stripos($acc->name, 'cash') !== false;
        });
        $defaultAccountId = $defaultAccount ? $defaultAccount->id : ($accounts->first()->id ?? null);
        return view('receipts.bulk', compact('customers', 'accounts', 'defaultAccountId', 'date'));
    }

    public function storeBulk(Request $request)
    {
        $request->validate([
            'date' => 'required|date',
            'account_id' => 'required|exists:accounts,id',
            'customers' => 'required|array|min:1',
            'customers.*.id' => 'required|exists:customers,id',
            'customers.*.amount' => 'nullable|numeric|min:0',
            'customers.*.note' => 'nullable|string|max:255',
        ]);

        try {
            DB::beginTransaction();

            $count = 0;
            foreach ($request->customers as $custData) {
                if (!empty($custData['amount']) && (float) $custData['amount'] > 0) {
                    Receipt::create([
                        'date' => $request->date,
                        'account_id' => $request->account_id,
                        'customer_id' => $custData['id'],
                        'amount' => (float) $custData['amount'],
                        'note' => $custData['note'] ?? null,
                    ]);
                    $count++;
                }
            }

            DB::commit();
            return redirect()->route('receipts.index')->with('success', "$count Bulk receipts recorded successfully.");

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', $e->getMessage())->withInput();
        }
    }
}
