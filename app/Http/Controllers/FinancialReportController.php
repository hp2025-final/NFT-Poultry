<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Account;
use App\Models\Customer;
use App\Models\Supplier;
use App\Models\Product;
use App\Models\Sale;
use App\Models\Purchase;
use App\Models\Expense;
use App\Models\EquityTxn;
use App\Models\Receipt;
use App\Models\Payment;

class FinancialReportController extends Controller
{
    private function getBalances() {
        $customerBalances = Customer::all()->map(function($c) {
            $sales = Sale::where('customer_id', $c->id)->sum('total_amount');
            $receipts = Receipt::where('customer_id', $c->id)->sum('amount');
            return [
                'name' => $c->name,
                'balance' => $c->opening_balance + $sales - $receipts,
            ];
        });

        $supplierBalances = Supplier::all()->map(function($s) {
            $purchases = Purchase::where('supplier_id', $s->id)->sum('total_amount');
            $payments = Payment::where('supplier_id', $s->id)->sum('amount');
            return [
                'name' => $s->name,
                'balance' => $s->opening_balance + $purchases - $payments,
            ];
        });

        $accountBalances = Account::all()->map(function($a) {
            $receipts = Receipt::where('account_id', $a->id)->sum('amount');
            $capital = EquityTxn::where('account_id', $a->id)->where('type', 'capital')->sum('amount');
            
            $payments = Payment::where('account_id', $a->id)->sum('amount');
            $expenses = Expense::where('account_id', $a->id)->sum('amount');
            $drawings = EquityTxn::where('account_id', $a->id)->where('type', 'drawing')->sum('amount');
            
            return [
                'name' => $a->name,
                'type' => $a->type,
                'balance' => $a->opening_balance + $receipts + $capital - $payments - $expenses - $drawings,
            ];
        });

        $stockValue = Product::where('is_active', true)->get()->sum(function($p) {
            return $p->stock_qty * $p->purchase_price; 
        });

        return compact('customerBalances', 'supplierBalances', 'accountBalances', 'stockValue');
    }

    public function trialBalance()
    {
        extract($this->getBalances());

        $totalSales = Sale::sum('total_amount');
        $totalPurchases = Purchase::sum('total_amount');
        $totalExpenses = Expense::sum('amount');
        $totalCapital = EquityTxn::where('type', 'capital')->sum('amount');
        $totalDrawings = EquityTxn::where('type', 'drawing')->sum('amount');

        $openingInventory = Product::all()->sum(function($p) {
            return $p->opening_qty * $p->purchase_price;
        });

        // Debit Balances
        $assetCustomers = $customerBalances->where('balance', '>', 0);
        $assetSuppliers = $supplierBalances->where('balance', '<', 0); // Advance given to supplier is Asset (Debit)
        $assetAccounts = $accountBalances->where('balance', '>', 0);
        
        // Credit Balances
        $liabCustomers = $customerBalances->where('balance', '<', 0); // Advance from customer is Liability (Credit)
        $liabSuppliers = $supplierBalances->where('balance', '>', 0);
        $liabAccounts = $accountBalances->where('balance', '<', 0);

        return view('reports.trial_balance', compact(
            'assetCustomers', 'assetSuppliers', 'assetAccounts', 'stockValue', 'openingInventory',
            'liabCustomers', 'liabSuppliers', 'liabAccounts',
            'totalSales', 'totalPurchases', 'totalExpenses', 'totalCapital', 'totalDrawings'
        ));
    }

    public function profitLoss()
    {
        $totalSales = Sale::sum('total_amount');
        
        $openingInventory = Product::all()->sum(function($p) {
            return $p->opening_qty * $p->purchase_price;
        });
        
        $purchases = Purchase::sum('total_amount');
        
        $closingInventory = Product::where('is_active', true)->get()->sum(function($p) {
            return $p->stock_qty * $p->purchase_price;
        });

        $cogs = $openingInventory + $purchases - $closingInventory;
        $grossProfit = $totalSales - $cogs;
        
        $expensesByCategory = Expense::with('category')->get()->groupBy(function($e) {
            return $e->category->name ?? 'Uncategorized';
        })->map(function($exps) {
            return $exps->sum('amount');
        });
        
        $totalExpenses = $expensesByCategory->sum();
        $netProfit = $grossProfit - $totalExpenses;

        return view('reports.profit_loss', compact(
            'totalSales', 'openingInventory', 'purchases', 'closingInventory', 'cogs',
            'grossProfit', 'expensesByCategory', 'totalExpenses', 'netProfit'
        ));
    }

    public function balanceSheet()
    {
        extract($this->getBalances());
        
        $currentAssets = [
            'Cash & Bank Balances' => $accountBalances->where('balance', '>', 0)->sum('balance'),
            'Accounts Receivable (Customers)' => $customerBalances->where('balance', '>', 0)->sum('balance'),
            'Supplier Advances Given' => abs($supplierBalances->where('balance', '<', 0)->sum('balance')),
            'Closing Inventory Value' => $stockValue
        ];
        $totalAssets = array_sum($currentAssets);

        $currentLiabilities = [
            'Bank Overdrafts' => abs($accountBalances->where('balance', '<', 0)->sum('balance')),
            'Accounts Payable (Suppliers)' => $supplierBalances->where('balance', '>', 0)->sum('balance'),
            'Customer Advances Received' => abs($customerBalances->where('balance', '<', 0)->sum('balance')),
        ];
        $totalLiabilities = array_sum($currentLiabilities);

        // Net Profit Calculation
        $totalSales = Sale::sum('total_amount');
        $openingInventory = Product::all()->sum(function($p) { return $p->opening_qty * $p->purchase_price; });
        $purchases = Purchase::sum('total_amount');
        $cogs = $openingInventory + $purchases - $stockValue;
        $netProfit = $totalSales - $cogs - Expense::sum('amount');

        $equity = [
            'Capital Injected' => EquityTxn::where('type', 'capital')->sum('amount'),
            'Less: Drawings' => EquityTxn::where('type', 'drawing')->sum('amount'),
            'Add: Net Profit' => $netProfit
        ];
        $totalEquity = $equity['Capital Injected'] - $equity['Less: Drawings'] + $equity['Add: Net Profit'];

        return view('reports.balance_sheet', compact(
            'currentAssets', 'totalAssets', 'currentLiabilities', 'totalLiabilities', 'equity', 'totalEquity'
        ));
    }
}
