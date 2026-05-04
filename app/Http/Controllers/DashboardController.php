<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Models\Sale;
use App\Models\Purchase;
use App\Models\Product;
use App\Models\Customer;
use App\Models\Supplier;
use App\Models\Receipt;
use App\Models\Payment;

class DashboardController extends Controller
{
    public function index()
    {
        $today = Carbon::today();
        $startOfMonth = Carbon::now()->startOfMonth();
        $now = Carbon::now();

        // Aggregated queries — no N+1
        $todaySales = Sale::whereDate('date', $today)->sum('total_amount');
        $monthSales = Sale::whereBetween('date', [$startOfMonth->format('Y-m-d'), $now->format('Y-m-d')])->sum('total_amount');
        $monthPurchases = Purchase::whereBetween('date', [$startOfMonth->format('Y-m-d'), $now->format('Y-m-d')])->sum('total_amount');
        $productCount = Product::where('is_active', true)->count();

        // Optimized Receivables: opening_balance + all sales - all receipts (single query each)
        $customerOpenings = Customer::where('is_active', true)->sum('opening_balance');
        $customerSales = Sale::whereIn('customer_id', Customer::where('is_active', true)->pluck('id'))->sum('total_amount');
        $customerReceipts = Receipt::whereIn('customer_id', Customer::where('is_active', true)->pluck('id'))->sum('amount');
        $receivables = $customerOpenings + $customerSales - $customerReceipts;

        // Optimized Payables: opening_balance + all purchases - all payments (single query each)
        $supplierOpenings = Supplier::where('is_active', true)->sum('opening_balance');
        $supplierPurchases = Purchase::whereIn('supplier_id', Supplier::where('is_active', true)->pluck('id'))->sum('total_amount');
        $supplierPayments = Payment::whereIn('supplier_id', Supplier::where('is_active', true)->pluck('id'))->sum('amount');
        $payables = $supplierOpenings + $supplierPurchases - $supplierPayments;

        return view('dashboard', compact(
            'today',
            'todaySales',
            'monthSales',
            'monthPurchases',
            'productCount',
            'receivables',
            'payables'
        ));
    }
}
