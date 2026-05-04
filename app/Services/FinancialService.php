<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use App\Models\Account;
use App\Models\Customer;
use App\Models\Supplier;
use App\Models\Receipt;
use App\Models\Payment;
use App\Models\Expense;
use App\Models\EquityTxn;
use App\Models\Sale;
use App\Models\Purchase;

class FinancialService
{
    /**
     * Calculate balances for a specific account.
     */
    public function getAccountBalances($accountId, Carbon $start = null, Carbon $end = null)
    {
        $start = $start ?: Carbon::now()->startOfMonth();
        $end = $end ?: Carbon::now();

        $account = Account::findOrFail($accountId);

        $receipts_before = Receipt::where('account_id', $account->id)->where('date', '<', $start->format('Y-m-d'))->sum('amount');
        $payments_before = Payment::where('account_id', $account->id)->where('date', '<', $start->format('Y-m-d'))->sum('amount');
        $expenses_before = Expense::where('account_id', $account->id)->where('date', '<', $start->format('Y-m-d'))->sum('amount');
        
        $capital_before = EquityTxn::where('account_id', $account->id)
            ->where('date', '<', $start->format('Y-m-d'))
            ->whereRaw('LOWER(type) = ?', ['capital'])
            ->sum('amount');
            
        $drawings_before = EquityTxn::where('account_id', $account->id)
            ->where('date', '<', $start->format('Y-m-d'))
            ->whereRaw('LOWER(type) = ?', ['drawing'])
            ->sum('amount');

        $opening = ($account->opening_balance ?? 0) 
            + $receipts_before + $capital_before 
            - $payments_before - $expenses_before - $drawings_before;

        $receipts_in = Receipt::where('account_id', $account->id)->whereBetween('date', [$start->format('Y-m-d'), $end->format('Y-m-d')])->sum('amount');
        $capital_in = EquityTxn::where('account_id', $account->id)
            ->whereBetween('date', [$start->format('Y-m-d'), $end->format('Y-m-d')])
            ->whereRaw('LOWER(type) = ?', ['capital'])
            ->sum('amount');
            
        $payments_in = Payment::where('account_id', $account->id)->whereBetween('date', [$start->format('Y-m-d'), $end->format('Y-m-d')])->sum('amount');
        $expenses_in = Expense::where('account_id', $account->id)->whereBetween('date', [$start->format('Y-m-d'), $end->format('Y-m-d')])->sum('amount');
        $drawings_in = EquityTxn::where('account_id', $account->id)
            ->whereBetween('date', [$start->format('Y-m-d'), $end->format('Y-m-d')])
            ->whereRaw('LOWER(type) = ?', ['drawing'])
            ->sum('amount');

        $dr = $receipts_in + $capital_in;
        $cr = $payments_in + $expenses_in + $drawings_in;
        $closing = $opening + $dr - $cr;

        return (object)[
            'opening' => $opening,
            'dr' => $dr,
            'cr' => $cr,
            'closing' => $closing
        ];
    }

    /**
     * Calculate balances for a specific customer.
     */
    public function getCustomerBalances($customerId, Carbon $start = null, Carbon $end = null)
    {
        $start = $start ?: Carbon::now()->startOfMonth();
        $end = $end ?: Carbon::now();

        $customer = Customer::findOrFail($customerId);

        $sales_before = Sale::where('customer_id', $customer->id)->where('date', '<', $start->format('Y-m-d'))->sum('total_amount');
        $receipts_before = Receipt::where('customer_id', $customer->id)->where('date', '<', $start->format('Y-m-d'))->sum('amount');

        $opening = ($customer->opening_balance ?? 0) + $sales_before - $receipts_before;

        $sales_in = Sale::where('customer_id', $customer->id)->whereBetween('date', [$start->format('Y-m-d'), $end->format('Y-m-d')])->sum('total_amount');
        $receipts_in = Receipt::where('customer_id', $customer->id)->whereBetween('date', [$start->format('Y-m-d'), $end->format('Y-m-d')])->sum('amount');

        $dr = $sales_in;
        $cr = $receipts_in;
        $closing = $opening + $dr - $cr;

        return (object)[
            'opening' => $opening,
            'dr' => $dr,
            'cr' => $cr,
            'closing' => $closing
        ];
    }

    /**
     * Calculate balances for a specific supplier.
     */
    public function getSupplierBalances($supplierId, Carbon $start = null, Carbon $end = null)
    {
        $start = $start ?: Carbon::now()->startOfMonth();
        $end = $end ?: Carbon::now();

        $supplier = Supplier::findOrFail($supplierId);

        $purchases_before = Purchase::where('supplier_id', $supplier->id)->where('date', '<', $start->format('Y-m-d'))->sum('total_amount');
        $payments_before = Payment::where('supplier_id', $supplier->id)->where('date', '<', $start->format('Y-m-d'))->sum('amount');

        $opening = ($supplier->opening_balance ?? 0) + $purchases_before - $payments_before;

        $purchases_in = Purchase::where('supplier_id', $supplier->id)->whereBetween('date', [$start->format('Y-m-d'), $end->format('Y-m-d')])->sum('total_amount');
        $payments_in = Payment::where('supplier_id', $supplier->id)->whereBetween('date', [$start->format('Y-m-d'), $end->format('Y-m-d')])->sum('amount');

        $dr = $purchases_in;
        $cr = $payments_in;
        $closing = $opening + $dr - $cr;

        return (object)[
            'opening' => $opening,
            'dr' => $dr,
            'cr' => $cr,
            'closing' => $closing
        ];
    }
}
