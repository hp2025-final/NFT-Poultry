<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\Sale;
use App\Models\Purchase;
use App\Models\Receipt;
use App\Models\Payment;
use App\Models\Expense;
use App\Models\EquityTxn;
use App\Models\StockAdjustment;

class DailyReportController extends Controller
{
    private function getDailyReportData($date)
    {
        $sales = Sale::with(['customer', 'items'])->where('date', $date)->get();
        $purchases = Purchase::with(['supplier', 'items'])->where('date', $date)->get();
        $receipts = Receipt::with(['customer', 'account'])->where('date', $date)->get();
        $payments = Payment::with(['supplier', 'account'])->where('date', $date)->get();
        $expenses = Expense::with(['category', 'account'])->where('date', $date)->get();
        $equity = EquityTxn::with('account')->where('date', $date)->get();
        $adjustments = StockAdjustment::with('product')->where('date', $date)->get();

        $purchaseKG = $purchases->map(function($p) { return $p->items->sum('qty'); })->sum();
        $adjKG = $adjustments->map(function($a) { return $a->type === 'decrease' ? -$a->qty : $a->qty; })->sum();
        $totalPurchaseKG = $purchaseKG + $adjKG;
        
        $salesKG = $sales->map(function($s) { return $s->items->sum('qty'); })->sum();

        $totals = [
            'sales_amount' => $sales->sum('total_amount'),
            'sales_kg' => $salesKG,
            'purchases_amount' => $purchases->sum('total_amount'),
            'purchases_kg' => $totalPurchaseKG,
            'receipts' => $receipts->sum('amount'),
            'payments' => $payments->sum('amount'),
            'expenses' => $expenses->sum('amount'),
            'capital_in' => $equity->where('type', 'capital')->sum('amount'),
            'drawing_out' => $equity->where('type', 'drawing')->sum('amount'),
            'net_cash_flow' => ($receipts->sum('amount') + $equity->where('type', 'capital')->sum('amount'))
                - ($payments->sum('amount') + $expenses->sum('amount') + $equity->where('type', 'drawing')->sum('amount'))
        ];

        return compact(
            'date', 'sales', 'purchases', 'receipts', 'payments',
            'expenses', 'equity', 'adjustments', 'totals'
        );
    }

    public function index(Request $request)
    {
        $date = $request->get('date') ? Carbon::parse($request->get('date'))->toDateString() : Carbon::now()->toDateString();
        $data = $this->getDailyReportData($date);
        return view('reports.daily', $data);
    }

    public function pdf(Request $request)
    {
        $date = $request->get('date') ? Carbon::parse($request->get('date'))->toDateString() : Carbon::now()->toDateString();
        $data = $this->getDailyReportData($date);
        
        $pdf = Pdf::loadView('pdf.daily_report', $data);
        return $pdf->stream('daily_report_' . $date . '.pdf');
    }

    public function thermal(Request $request)
    {
        $date = $request->get('date') ? Carbon::parse($request->get('date'))->toDateString() : Carbon::now()->toDateString();
        $data = $this->getDailyReportData($date);
        
        // Estimate height based on number of rows
        $rowCount = count($data['sales']) + count($data['purchases']) + count($data['adjustments']) + count($data['receipts']) + count($data['payments']) + count($data['expenses']);
        $estimatedHeight = 450 + ($rowCount * 30); // Base height for summary + padding, plus 30pt per row

        $pdf = Pdf::loadView('pdf.daily_report_thermal', $data);
        $pdf->setPaper([0.0, 0.0, 226.77, $estimatedHeight], 'portrait');
        return $pdf->stream('daily_report_thermal_' . $date . '.pdf');
    }
}
