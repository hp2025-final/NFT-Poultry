<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\Sale;
use App\Models\Purchase;
use App\Models\Account;
use App\Models\Supplier;
use App\Models\Customer;
use App\Services\FinancialService;

class ReportController extends Controller
{
    protected $finService;

    public function __construct(FinancialService $finService)
    {
        $this->finService = $finService;
    }

    public function salesPdf(Request $request)
    {
        $start = $request->start_date ?Carbon::parse($request->start_date) : Carbon::today();
        $end = $request->end_date ?Carbon::parse($request->end_date) : Carbon::today();

        $sales = Sale::with('customer')
            ->whereBetween('date', [$start->format('Y-m-d'), $end->format('Y-m-d')])
            ->orderBy('date')
            ->get();

        $pdf = Pdf::loadView('pdf.sales', compact('sales', 'start', 'end'));
        return $pdf->stream('sales_report.pdf');
    }

    public function purchasesPdf(Request $request)
    {
        $start = $request->start_date ?Carbon::parse($request->start_date) : Carbon::today();
        $end = $request->end_date ?Carbon::parse($request->end_date) : Carbon::today();

        $purchases = Purchase::with('supplier')
            ->whereBetween('date', [$start->format('Y-m-d'), $end->format('Y-m-d')])
            ->orderBy('date')
            ->get();

        $pdf = Pdf::loadView('pdf.purchases', compact('purchases', 'start', 'end'));
        return $pdf->stream('purchases_report.pdf');
    }

    public function accountsPdf(Request $request)
    {
        $start = $request->start_date ?Carbon::parse($request->start_date) : Carbon::now()->startOfMonth();
        $end = $request->end_date ?Carbon::parse($request->end_date) : Carbon::now();

        $accounts = Account::where('is_active', true)->get();
        $ledgers = [];

        foreach ($accounts as $acc) {
            $ledger = $this->finService->getAccountBalances($acc->id, $start, $end);
            $ledgers[] = [
                'name' => $acc->name,
                'opening' => $ledger->opening,
                'dr' => $ledger->dr,
                'cr' => $ledger->cr,
                'closing' => $ledger->closing,
            ];
        }

        $pdf = Pdf::loadView('pdf.accounts', compact('ledgers', 'start', 'end'));
        return $pdf->stream('accounts_report.pdf');
    }

    public function suppliersPdf(Request $request)
    {
        $start = $request->start_date ?Carbon::parse($request->start_date) : Carbon::now()->startOfMonth();
        $end = $request->end_date ?Carbon::parse($request->end_date) : Carbon::now();

        $suppliers = Supplier::where('is_active', true)->get();
        $ledgers = [];

        foreach ($suppliers as $sup) {
            $ledger = $this->finService->getSupplierBalances($sup->id, $start, $end);
            $ledgers[] = [
                'name' => $sup->name,
                'opening' => $ledger->opening,
                'dr' => $ledger->dr,
                'cr' => $ledger->cr,
                'closing' => $ledger->closing,
            ];
        }

        $pdf = Pdf::loadView('pdf.suppliers', compact('ledgers', 'start', 'end'));
        return $pdf->stream('suppliers_report.pdf');
    }

    private function getLedgerTransactions(Customer $customer, $startDate, $endDate)
    {
        $openingBalance = $customer->opening_balance;
        
        if ($startDate) {
            $pastSales = \App\Models\Sale::where('customer_id', $customer->id)->where('date', '<', $startDate)->sum('total_amount');
            $pastReceipts = \App\Models\Receipt::where('customer_id', $customer->id)->where('date', '<', $startDate)->sum('amount');
            $openingBalance += ($pastSales - $pastReceipts);
        }

        $sales = \App\Models\Sale::with('items.product')->where('customer_id', $customer->id)
            ->when($startDate, fn($query) => $query->where('date', '>=', $startDate))
            ->when($endDate, fn($query) => $query->where('date', '<=', $endDate))
            ->get()->map(function($sale) {
                $lines = [];
                foreach ($sale->items as $item) {
                    $qtyStr = fmod($item->qty, 1) == 0 ? intval($item->qty) : number_format($item->qty, 2);
                    $priceStr = number_format($item->price, 0);
                    $unit = $item->product->unit ?? 'KG';
                    
                    $lines[] = [
                        'line1' => $item->product->name ?? 'Item',
                        'line2' => "{$qtyStr}{$unit}x{$priceStr}"
                    ];
                }

                return [
                    'date' => $sale->date,
                    'type' => 'sale',
                    'ref' => $sale->id,
                    'debit' => $sale->total_amount,
                    'credit' => 0,
                    'lines' => $lines
                ];
            });

        $receipts = \App\Models\Receipt::with('account')->where('customer_id', $customer->id)
            ->when($startDate, fn($query) => $query->where('date', '>=', $startDate))
            ->when($endDate, fn($query) => $query->where('date', '<=', $endDate))
            ->get()->map(function($receipt) {
                $noteLine = !empty($receipt->note) ? $receipt->note : ($receipt->account->name ?? 'Cash');
                return [
                    'date' => $receipt->date,
                    'type' => 'receipt',
                    'ref' => $receipt->id,
                    'debit' => 0,
                    'credit' => $receipt->amount,
                    'lines' => [
                        [
                            'line1' => 'Receipt',
                            'line2' => \Illuminate\Support\Str::limit($noteLine, 20)
                        ]
                    ]
                ];
            });

        $transactions = $sales->concat($receipts)->sortBy('date')->values()->toArray();
        
        $runningBalance = $openingBalance;
        foreach ($transactions as &$txn) {
            $runningBalance += $txn['debit'];
            $runningBalance -= $txn['credit'];
            $txn['balance'] = $runningBalance;
        }

        return [
            'transactions' => $transactions,
            'openingBalance' => $openingBalance,
            'closingBalance' => $runningBalance,
        ];
    }

    public function customersA4(Request $request, $id)
    {
        $start = $request->start_date ? Carbon::parse($request->start_date) : Carbon::now()->startOfMonth();
        $end = $request->end_date ? Carbon::parse($request->end_date) : Carbon::now();

        $customer = Customer::findOrFail($id);
        $ledger = $this->finService->getCustomerBalances($id, $start, $end);
        
        $ledgerData = $this->getLedgerTransactions($customer, $start->format('Y-m-d'), $end->format('Y-m-d'));
        $transactions = $ledgerData['transactions'];

        $pdf = Pdf::loadView('pdf.customers_a4', compact('customer', 'ledger', 'start', 'end', 'transactions'));
        return $pdf->stream('customer_a4_' . $id . '.pdf');
    }

    public function customersThermal(Request $request, $id)
    {
        $start = $request->start_date ?Carbon::parse($request->start_date) : Carbon::now()->startOfMonth();
        $end = $request->end_date ?Carbon::parse($request->end_date) : Carbon::now();

        $customer = Customer::findOrFail($id);
        $ledger = $this->finService->getCustomerBalances($id, $start, $end);
        
        $ledgerData = $this->getLedgerTransactions($customer, $start->format('Y-m-d'), $end->format('Y-m-d'));
        $transactions = $ledgerData['transactions'];

        $pdf = Pdf::loadView('pdf.customers_thermal', compact('customer', 'ledger', 'start', 'end', 'transactions'));
        
        // Dynamic height based on transactions
        $dynamicHeight = 250 + (count($transactions) * 35);
        $pdf->setPaper([0.0, 0.0, 226.77, $dynamicHeight], 'portrait');
        return $pdf->stream('customer_thermal_' . $id . '.pdf');
    }

    public function customersClosingThermal(Request $request)
    {
        $customers = Customer::where('is_active', true)->get();
        $balances = [];
        $total = 0;
        foreach ($customers as $c) {
            $ledger = $this->finService->getCustomerBalances($c->id);
            if ($ledger->closing != 0) {
                $balances[] = [
                    'name' => $c->name,
                    'closing' => $ledger->closing
                ];
                $total += $ledger->closing;
            }
        }

        $pdf = Pdf::loadView('pdf.customers_closing_thermal', compact('balances', 'total'));
        $pdf->setPaper([0.0, 0.0, 226.77, 1000.0], 'portrait');
        return $pdf->stream('customers_closing.pdf');
    }

    public function equityPdf(Request $request)
    {
        $start = $request->start_date ?Carbon::parse($request->start_date) : Carbon::now()->startOfYear();
        $end = $request->end_date ?Carbon::parse($request->end_date) : Carbon::now();

        $sales = Sale::whereBetween('date', [$start->format('Y-m-d'), $end->format('Y-m-d')])->sum('total_amount');
        $cogs = Purchase::whereBetween('date', [$start->format('Y-m-d'), $end->format('Y-m-d')])->sum('total_amount');
        $expenses = \App\Models\Expense::whereBetween('date', [$start->format('Y-m-d'), $end->format('Y-m-d')])->sum('amount');

        $netProfit = $sales - $cogs - $expenses;

        $capital = \App\Models\EquityTxn::whereRaw('LOWER(type) = ?', ['capital'])->whereBetween('date', [$start->format('Y-m-d'), $end->format('Y-m-d')])->sum('amount');
        $drawings = \App\Models\EquityTxn::whereRaw('LOWER(type) = ?', ['drawing'])->whereBetween('date', [$start->format('Y-m-d'), $end->format('Y-m-d')])->sum('amount');

        $pdf = Pdf::loadView('pdf.equity', compact('start', 'end', 'sales', 'cogs', 'expenses', 'netProfit', 'capital', 'drawings'));
        return $pdf->stream('owner_equity_report.pdf');
    }

    /* ===== Individual Sale Invoice PDFs ===== */

    public function saleInvoicePdf($id)
    {
        $sale = Sale::with(['customer', 'items.product'])->findOrFail($id);
        $pdf = Pdf::loadView('pdf.sale_invoice', compact('sale'));
        return $pdf->stream('sale_invoice_' . $id . '.pdf');
    }

    public function saleInvoiceThermal($id)
    {
        $sale = Sale::with(['customer', 'items.product'])->findOrFail($id);

        // Dynamic height: base ~280pt for header/footer/meta + ~15pt per item row
        $itemCount = $sale->items->count();
        $dynamicHeight = 280 + ($itemCount * 15);

        $pdf = Pdf::loadView('pdf.sale_invoice_thermal', compact('sale'));
        $pdf->setPaper([0.0, 0.0, 226.77, $dynamicHeight], 'portrait');
        return $pdf->stream('sale_thermal_' . $id . '.pdf');
    }

    /* ===== Individual Purchase Invoice PDFs ===== */

    public function purchaseInvoicePdf($id)
    {
        $purchase = Purchase::with(['supplier', 'items.product'])->findOrFail($id);
        $pdf = Pdf::loadView('pdf.purchase_invoice', compact('purchase'));
        return $pdf->stream('purchase_invoice_' . $id . '.pdf');
    }

    public function purchaseInvoiceThermal($id)
    {
        $purchase = Purchase::with(['supplier', 'items.product'])->findOrFail($id);
        $pdf = Pdf::loadView('pdf.purchase_invoice_thermal', compact('purchase'));
        $pdf->setPaper([0.0, 0.0, 226.77, 800.0], 'portrait');
        return $pdf->stream('purchase_thermal_' . $id . '.pdf');
    }

    /* ===== Daily Customer Report ===== */

    private function buildDailyCustomerData(Request $request)
    {
        $start = $request->start_date ?Carbon::parse($request->start_date) : Carbon::today();
        $end = $request->end_date ?Carbon::parse($request->end_date) : Carbon::today();
        $filter = $request->get('filter', 'activity'); // 'all' or 'activity'

        $customers = Customer::where('is_active', true)->orderBy('name')->get();
        $report = [];
        $grandTotals = ['opening' => 0, 'sales' => 0, 'receipts' => 0, 'closing' => 0];

        foreach ($customers as $customer) {
            // Calculate opening balance (as of start_date)
            $salesBefore = Sale::where('customer_id', $customer->id)
                ->where('date', '<', $start->format('Y-m-d'))->sum('total_amount');
            $receiptsBefore = \App\Models\Receipt::where('customer_id', $customer->id)
                ->where('date', '<', $start->format('Y-m-d'))->sum('amount');
            $opening = ($customer->opening_balance ?? 0) + $salesBefore - $receiptsBefore;

            // Get sales with items between dates
            $sales = Sale::with('items.product')
                ->where('customer_id', $customer->id)
                ->whereBetween('date', [$start->format('Y-m-d'), $end->format('Y-m-d')])
                ->orderBy('date')
                ->get();

            $saleItems = [];
            $totalSales = 0;
            foreach ($sales as $sale) {
                foreach ($sale->items as $item) {
                    $saleItems[] = [
                        'date' => $sale->date,
                        'invoice' => $sale->id,
                        'product' => $item->product->name,
                        'qty' => $item->qty,
                        'price' => $item->price,
                        'amount' => $item->amount,
                    ];
                    $totalSales += $item->amount;
                }
            }

            // Get receipts between dates
            $receipts = \App\Models\Receipt::with('account')
                ->where('customer_id', $customer->id)
                ->whereBetween('date', [$start->format('Y-m-d'), $end->format('Y-m-d')])
                ->orderBy('date')
                ->get();

            $receiptItems = [];
            $totalReceipts = 0;
            foreach ($receipts as $receipt) {
                $receiptItems[] = [
                    'date' => $receipt->date,
                    'receipt_id' => $receipt->id,
                    'amount' => $receipt->amount,
                    'account' => $receipt->account->name ?? 'N/A',
                    'note' => $receipt->note,
                ];
                $totalReceipts += $receipt->amount;
            }

            $closing = $opening + $totalSales - $totalReceipts;

            // Apply filter
            if ($filter === 'activity' && count($saleItems) === 0 && count($receiptItems) === 0) {
                continue;
            }

            $report[] = [
                'customer' => $customer,
                'opening' => $opening,
                'sale_items' => $saleItems,
                'total_sales' => $totalSales,
                'receipt_items' => $receiptItems,
                'total_receipts' => $totalReceipts,
                'closing' => $closing,
            ];

            $grandTotals['opening'] += $opening;
            $grandTotals['sales'] += $totalSales;
            $grandTotals['receipts'] += $totalReceipts;
            $grandTotals['closing'] += $closing;
        }

        return compact('report', 'grandTotals', 'start', 'end', 'filter');
    }

    public function dailyCustomer(Request $request)
    {
        $data = $this->buildDailyCustomerData($request);
        return view('reports.daily_customer', $data);
    }

    public function dailyCustomerPdf(Request $request)
    {
        $data = $this->buildDailyCustomerData($request);
        $pdf = Pdf::loadView('pdf.daily_customer', $data);
        $pdf->setPaper('a4', 'landscape');
        return $pdf->stream('daily_customer_report.pdf');
    }

    public function dailyCustomerThermal(Request $request)
    {
        $data = $this->buildDailyCustomerData($request);
        // Height auto for continuous roll — each customer has page-break-after
        $pdf = Pdf::loadView('pdf.daily_customer_thermal', $data);
        $pdf->setPaper([0.0, 0.0, 226.77, 300.0], 'portrait');
        return $pdf->stream('daily_customer_thermal.pdf');
    }

    /* ===== Customers List Reports ===== */

    private function buildCustomerListData(Request $request)
    {
        $query = Customer::query();

        if ($request->filled('q')) {
            $search = $request->q;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', '%' . $search . '%')
                  ->orWhere('phone', 'like', '%' . $search . '%');
            });
        }

        $showFilter = $request->get('show', 'active');
        if (in_array($showFilter, ['active', 'active_transactions'])) {
            $query->where('is_active', true);
        } elseif ($showFilter === 'archived') {
            $query->where('is_active', false);
        }

        $startDate = $request->get('start_date', date('Y-m-d'));
        $endDate   = $request->get('end_date', date('Y-m-d'));

        $customers = $query->orderBy('name')->get();

        foreach ($customers as $customer) {
            $pastSales    = Sale::where('customer_id', $customer->id)->where('date', '<', $startDate)->sum('total_amount');
            $pastReceipts = \App\Models\Receipt::where('customer_id', $customer->id)->where('date', '<', $startDate)->sum('amount');
            $customer->computed_opening = $customer->opening_balance + $pastSales - $pastReceipts;

            $customer->computed_dr = Sale::where('customer_id', $customer->id)
                ->whereBetween('date', [$startDate, $endDate])->sum('total_amount');

            $customer->computed_cr = \App\Models\Receipt::where('customer_id', $customer->id)
                ->whereBetween('date', [$startDate, $endDate])->sum('amount');

            $customer->computed_balance = $customer->computed_opening + $customer->computed_dr - $customer->computed_cr;
        }

        if (in_array($showFilter, ['active_transactions', 'all_transactions'])) {
            $customers = $customers->filter(function ($customer) {
                return $customer->computed_dr != 0 || $customer->computed_cr != 0;
            })->values();
        }

        return compact('customers', 'startDate', 'endDate', 'showFilter');
    }

    public function customersListA4(Request $request)
    {
        $data = $this->buildCustomerListData($request);
        $pdf = Pdf::loadView('pdf.customers_list', $data);
        return $pdf->stream('customers_report.pdf');
    }

    public function customersListThermal(Request $request)
    {
        $data = $this->buildCustomerListData($request);
        
        $start = $request->get('start_date', date('Y-m-d'));
        $end = $request->get('end_date', date('Y-m-d'));
        
        foreach ($data['customers'] as $customer) {
            $ledgerData = $this->getLedgerTransactions($customer, $start, $end);
            $customer->transactions = $ledgerData['transactions'];
        }

        $pdf = Pdf::loadView('pdf.customers_list_thermal', $data);
        // Set page dimensions to 80mm width (226.77pt) x 80mm height (226.77pt)
        $pdf->setPaper([0.0, 0.0, 226.77, 226.77], 'portrait');
        return $pdf->stream('customers_thermal.pdf');
    }

    /* ===== Suppliers List Reports ===== */

    private function buildSupplierListData(Request $request)
    {
        $query = Supplier::query();

        if ($request->filled('q')) {
            $search = $request->q;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', '%' . $search . '%')
                  ->orWhere('phone', 'like', '%' . $search . '%');
            });
        }

        $showFilter = $request->get('show', 'active');
        if ($showFilter === 'active') {
            $query->where('is_active', true);
        } elseif ($showFilter === 'archived') {
            $query->where('is_active', false);
        }

        $startDate = $request->get('start_date', date('Y-m-d'));
        $endDate   = $request->get('end_date', date('Y-m-d'));

        $suppliers = $query->orderBy('name')->get();

        foreach ($suppliers as $supplier) {
            $pastPurchases = Purchase::where('supplier_id', $supplier->id)->where('date', '<', $startDate)->sum('total_amount');
            $pastPayments  = \App\Models\Payment::where('supplier_id', $supplier->id)->where('date', '<', $startDate)->sum('amount');
            $supplier->computed_opening = $supplier->opening_balance + $pastPurchases - $pastPayments;

            $supplier->computed_dr = \App\Models\Payment::where('supplier_id', $supplier->id)
                ->whereBetween('date', [$startDate, $endDate])->sum('amount');

            $supplier->computed_cr = Purchase::where('supplier_id', $supplier->id)
                ->whereBetween('date', [$startDate, $endDate])->sum('total_amount');

            $supplier->computed_balance = $supplier->computed_opening + $supplier->computed_cr - $supplier->computed_dr;
        }

        return compact('suppliers', 'startDate', 'endDate', 'showFilter');
    }

    public function suppliersListA4(Request $request)
    {
        $data = $this->buildSupplierListData($request);
        $pdf = Pdf::loadView('pdf.suppliers_list', $data);
        return $pdf->stream('suppliers_report.pdf');
    }

    public function suppliersListThermal(Request $request)
    {
        $data = $this->buildSupplierListData($request);
        $rowCount = $data['suppliers']->count();
        $height = 200 + ($rowCount * 18);
        $pdf = Pdf::loadView('pdf.suppliers_list_thermal', $data);
        $pdf->setPaper([0.0, 0.0, 226.77, $height], 'portrait');
        return $pdf->stream('suppliers_thermal.pdf');
    }
}
