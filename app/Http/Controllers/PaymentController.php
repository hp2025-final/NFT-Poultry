<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Payment;
use App\Models\Supplier;
use App\Models\Account;

class PaymentController extends Controller
{
    public function index(Request $request)
    {
        $startDate = $request->input('start_date', date('Y-m-d'));
        $endDate = $request->input('end_date', date('Y-m-d'));

        $payments = Payment::with(['supplier', 'account'])
            ->whereBetween('date', [$startDate, $endDate])
            ->orderBy('date', 'desc')
            ->orderBy('id', 'desc')
            ->paginate(20);

        return view('payments.index', compact('payments'));
    }

    public function create()
    {
        $suppliers = Supplier::where('is_active', true)->orderBy('name')->get();
        $accounts = Account::where('is_active', true)->orderBy('name')->get();
        return view('payments.form', compact('suppliers', 'accounts'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'date' => 'required|date',
            'supplier_id' => 'required|exists:suppliers,id',
            'account_id' => 'required|exists:accounts,id',
            'amount' => 'required|numeric|min:0.01',
            'note' => 'nullable|string|max:255',
        ]);

        Payment::create($request->all());

        return redirect()->route('payments.index')->with('success', 'Payment recorded successfully.');
    }

    public function edit(Payment $payment)
    {
        $suppliers = Supplier::where('is_active', true)->orderBy('name')->get();
        $accounts = Account::where('is_active', true)->orderBy('name')->get();
        return view('payments.form', compact('payment', 'suppliers', 'accounts'));
    }

    public function update(Request $request, Payment $payment)
    {
        $request->validate([
            'date' => 'required|date',
            'supplier_id' => 'required|exists:suppliers,id',
            'account_id' => 'required|exists:accounts,id',
            'amount' => 'required|numeric|min:0.01',
            'note' => 'nullable|string|max:255',
        ]);

        $payment->update($request->all());

        return redirect()->route('payments.index')->with('success', 'Payment updated successfully.');
    }

    public function destroy(Payment $payment)
    {
        $payment->delete();
        return redirect()->route('payments.index')->with('success', 'Payment deleted successfully.');
    }

    public function bulk()
    {
        $suppliers = Supplier::where('is_active', true)->orderBy('name')->get();
        $accounts = Account::where('is_active', true)->orderBy('name')->get();
        // Default to Cash account
        $defaultAccount = $accounts->first(function ($acc) {
            return stripos($acc->name, 'cash') !== false;
        });
        $defaultAccountId = $defaultAccount ? $defaultAccount->id : ($accounts->first()->id ?? null);
        return view('payments.bulk', compact('suppliers', 'accounts', 'defaultAccountId'));
    }

    public function storeBulk(Request $request)
    {
        $request->validate([
            'date' => 'required|date',
            'account_id' => 'required|exists:accounts,id',
            'suppliers' => 'required|array|min:1',
            'suppliers.*.id' => 'required|exists:suppliers,id',
            'suppliers.*.amount' => 'nullable|numeric|min:0',
            'suppliers.*.note' => 'nullable|string|max:255',
        ]);

        try {
            DB::beginTransaction();

            $count = 0;
            foreach ($request->suppliers as $supData) {
                if (!empty($supData['amount']) && (float) $supData['amount'] > 0) {
                    Payment::create([
                        'date' => $request->date,
                        'account_id' => $request->account_id,
                        'supplier_id' => $supData['id'],
                        'amount' => (float) $supData['amount'],
                        'note' => $supData['note'] ?? null,
                    ]);
                    $count++;
                }
            }

            DB::commit();
            return redirect()->route('payments.index')->with('success', "$count Bulk payments recorded successfully.");

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', $e->getMessage())->withInput();
        }
    }
}
