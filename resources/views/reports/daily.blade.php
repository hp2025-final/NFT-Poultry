@extends('layouts.app')
@section('title', 'Daily Report - ' . \Carbon\Carbon::parse($date)->format('d-m-y'))

@section('content')
    <div class="page-header d-flex justify-content-between align-items-center mb-3">
        <h1><i class="bi bi-calendar-day me-2"></i>Daily Consolidated Report</h1>
        <div class="d-flex align-items-center gap-2">
            <form action="{{ route('reports.daily') }}" method="GET" class="d-flex align-items-center gap-2 m-0">
                <input type="date" name="date" class="form-control" value="{{ request('date', date('Y-m-d')) }}" required>
                <button type="submit" class="btn btn-dark d-flex align-items-center">
                    <i class="bi bi-search me-1"></i> Load
                </button>
            </form>
            <a href="{{ route('reports.daily_pdf', ['date' => request('date', date('Y-m-d'))]) }}" target="_blank" class="btn btn-outline-danger d-flex align-items-center">
                <i class="bi bi-file-pdf me-1"></i> A4 Print
            </a>
            <a href="{{ route('reports.daily_thermal', ['date' => request('date', date('Y-m-d'))]) }}" target="_blank" class="btn btn-outline-secondary d-flex align-items-center">
                <i class="bi bi-receipt me-1"></i> Thermal
            </a>
        </div>
    </div>

    {{-- Summary Tables --}}
    <div class="row g-3 mb-4">
        <div class="col-12">
            <div class="card h-100 shadow-sm border-0">
                <div class="card-header bg-white border-bottom py-3">
                    <h5 class="mb-0 fw-bold"><i class="bi bi-bar-chart-fill me-2 text-primary"></i>Daily Summary</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-bordered mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="text-center" style="width: 33.33%;">Purchases (Stock In)</th>
                                    <th class="text-center" style="width: 33.33%;">Sales (Stock Out)</th>
                                    <th class="text-center" style="width: 33.33%;">Trading Balance</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php
                                    $tradingQty = $totals['sales_kg'] - $totals['purchases_kg'];
                                    $tradingAmt = $totals['sales_amount'] - $totals['purchases_amount'];
                                @endphp
                                <tr>
                                    <td class="text-center py-3">
                                        <div class="fs-5 fw-bold">{{ number_format($totals['purchases_kg'], 2) }} KG</div>
                                        <div class="text-muted">Amt: {{ number_format($totals['purchases_amount'], 0) }}</div>
                                    </td>
                                    <td class="text-center py-3">
                                        <div class="fs-5 fw-bold">{{ number_format($totals['sales_kg'], 2) }} KG</div>
                                        <div class="text-muted">Amt: {{ number_format($totals['sales_amount'], 0) }}</div>
                                    </td>
                                    <td class="text-center py-3" style="background-color: {{ $tradingAmt >= 0 ? '#f0fdf4' : '#fef2f2' }};">
                                        <div class="fs-5 fw-bold {{ $tradingQty >= 0 ? 'text-success' : 'text-danger' }}">
                                            {{ $tradingQty > 0 ? '+' : '' }}{{ number_format($tradingQty, 2) }} KG
                                        </div>
                                        <div class="fw-bold {{ $tradingAmt >= 0 ? 'text-success' : 'text-danger' }}">
                                            Amt: {{ $tradingAmt > 0 ? '+' : '' }}{{ number_format($tradingAmt, 0) }}
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                            <thead class="table-light" style="border-top: 3px solid #dee2e6;">
                                <tr>
                                    <th class="text-center">Receipts (Money In)</th>
                                    <th class="text-center">Payments & Expenses (Money Out)</th>
                                    <th class="text-center">Net Cash Flow</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php
                                    $moneyIn = $totals['receipts'] + $totals['capital_in'];
                                    $moneyOut = $totals['payments'] + $totals['expenses'] + $totals['drawing_out'];
                                @endphp
                                <tr>
                                    <td class="text-center py-3">
                                        <div class="fs-4 fw-bold text-success">{{ number_format($moneyIn, 0) }}</div>
                                    </td>
                                    <td class="text-center py-3">
                                        <div class="fs-4 fw-bold text-danger">{{ number_format($moneyOut, 0) }}</div>
                                    </td>
                                    <td class="text-center py-3" style="background-color: {{ $totals['net_cash_flow'] >= 0 ? '#ecfdf5' : '#fef2f2' }};">
                                        <div class="fs-4 fw-bold" style="color: {{ $totals['net_cash_flow'] >= 0 ? '#065f46' : '#991b1b' }};">
                                            {{ $totals['net_cash_flow'] > 0 ? '+' : '' }}{{ number_format($totals['net_cash_flow'], 0) }}
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3">
        {{-- Purchase Invoices and Stock Adjustments Table --}}
        <div class="col-md-6">
            <div class="card h-100">
                <div class="card-header" style="background:#fffbeb; color:#92400e; font-weight:600;">
                    <i class="bi bi-bag me-1"></i>Purchase Invoices and Stock Adjustments
                </div>
                <div class="table-wrapper">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>Supplier</th>
                                <th class="text-end">KG</th>
                                <th class="text-end">Rate</th>
                                <th class="text-end">Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php
                                $totalPurchaseKG = 0;
                                $totalPurchaseAmount = 0;
                            @endphp
                            @forelse($purchases as $p)
                                @php
                                    $kg = $p->items->sum('qty');
                                    $rate = $p->items->first() ? $p->items->first()->price : 0;
                                    $totalPurchaseKG += $kg;
                                    $totalPurchaseAmount += $p->total_amount;
                                @endphp
                                <tr>
                                    <td>{{ $p->supplier->name ?? 'N/A' }}</td>
                                    <td class="text-end">{{ number_format($kg, 2) }}</td>
                                    <td class="text-end">{{ number_format($rate, 0) }}</td>
                                    <td class="text-end fw-bold">{{ number_format($p->total_amount, 0) }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="4" class="text-center text-muted">No purchases</td></tr>
                            @endforelse

                            @forelse($adjustments as $a)
                                @php
                                    $kg = $a->type === 'decrease' ? -$a->qty : $a->qty;
                                    $totalPurchaseKG += $kg;
                                    $amount = $a->type === 'decrease' ? -$a->amount : $a->amount;
                                @endphp
                                <tr style="background-color: #f8f9fa;">
                                    <td>Adj: System/Variance</td>
                                    <td class="text-end {{ $kg < 0 ? 'text-danger' : 'text-success' }}">{{ number_format($kg, 2) }}</td>
                                    <td class="text-end">{{ number_format($a->unit_cost, 0) }}</td>
                                    <td class="text-end fw-bold {{ $amount < 0 ? 'text-danger' : 'text-success' }}">{{ number_format($amount, 0) }}</td>
                                </tr>
                            @empty
                            @endforelse
                        </tbody>
                        <tfoot>
                            <tr class="table-active">
                                <td class="text-end fw-bold">Final Total:</td>
                                <td class="text-end fw-bold">{{ number_format($totalPurchaseKG, 2) }} KG</td>
                                <td class="text-end fw-bold">{{ $totalPurchaseKG > 0 ? number_format($totalPurchaseAmount / $totalPurchaseKG, 2) : '0.00' }}</td>
                                <td class="text-end fw-bold">{{ number_format($totalPurchaseAmount, 0) }}</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>

        {{-- Sales Table --}}
        <div class="col-md-6">
            <div class="card h-100">
                <div class="card-header" style="background:#eff6ff; color:#1e40af; font-weight:600;">
                    <i class="bi bi-receipt me-1"></i>Sales Invoices
                </div>
                <div class="table-wrapper">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>Customer</th>
                                <th class="text-end">KG</th>
                                <th class="text-end">Rate</th>
                                <th class="text-end">Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php
                                $totalSalesKG = 0;
                                $totalSalesAmount = 0;
                            @endphp
                            @forelse($sales as $s)
                                @php
                                    $kg = $s->items->sum('qty');
                                    $rate = $s->items->first() ? $s->items->first()->price : 0;
                                    $totalSalesKG += $kg;
                                    $totalSalesAmount += $s->total_amount;
                                @endphp
                                <tr>
                                    <td>{{ $s->customer->name ?? 'N/A' }}</td>
                                    <td class="text-end">{{ number_format($kg, 2) }}</td>
                                    <td class="text-end">{{ number_format($rate, 0) }}</td>
                                    <td class="text-end fw-bold">{{ number_format($s->total_amount, 0) }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="4" class="text-center text-muted">No sales</td></tr>
                            @endforelse
                        </tbody>
                        <tfoot>
                            <tr class="table-active">
                                <td class="text-end fw-bold">Final Total:</td>
                                <td class="text-end fw-bold">{{ number_format($totalSalesKG, 2) }} KG</td>
                                <td class="text-end fw-bold">{{ $totalSalesKG > 0 ? number_format($totalSalesAmount / $totalSalesKG, 2) : '0.00' }}</td>
                                <td class="text-end fw-bold">{{ number_format($totalSalesAmount, 0) }}</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>

        {{-- Receipts Table --}}
        <div class="col-md-6">
            <div class="card h-100">
                <div class="card-header" style="background:#ecfdf5; color:#065f46; font-weight:600;">
                    <i class="bi bi-arrow-down-circle me-1"></i>Receipts (Money In)
                </div>
                <div class="table-wrapper">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Customer</th>
                                <th class="text-end">Amount</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php
                                $totalReceipts = 0;
                            @endphp
                            @forelse($receipts as $r)
                                @php
                                    $totalReceipts += $r->amount;
                                @endphp
                                <tr>
                                    <td><strong>#{{ $r->id }}</strong></td>
                                    <td>{{ $r->customer->name ?? 'N/A' }}</td>
                                    <td class="text-end text-success">{{ number_format($r->amount, 0) }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="3" class="text-center text-muted">No receipts</td></tr>
                            @endforelse
                        </tbody>
                        <tfoot>
                            <tr class="table-active">
                                <td colspan="2" class="text-end fw-bold">Final Total:</td>
                                <td class="text-end fw-bold text-success">{{ number_format($totalReceipts, 0) }}</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>

        {{-- Payments & Expenses Table --}}
        <div class="col-md-6">
            <div class="card h-100">
                <div class="card-header" style="background:#fef2f2; color:#991b1b; font-weight:600;">
                    <i class="bi bi-arrow-up-circle me-1"></i>Payments & Expenses (Money Out)
                </div>
                <div class="table-wrapper">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Supplier / Category</th>
                                <th class="text-end">Amount</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php
                                $totalPayments = 0;
                            @endphp
                            @foreach($payments as $p)
                                @php
                                    $totalPayments += $p->amount;
                                @endphp
                                <tr>
                                    <td><strong>P-{{ $p->id }}</strong></td>
                                    <td>{{ $p->supplier->name ?? 'N/A' }} <span class="badge bg-info ms-1">Supplier</span></td>
                                    <td class="text-end text-danger">{{ number_format($p->amount, 0) }}</td>
                                </tr>
                            @endforeach
                            @foreach($expenses as $e)
                                @php
                                    $totalPayments += $e->amount;
                                @endphp
                                <tr>
                                    <td><strong>E-{{ $e->id }}</strong></td>
                                    <td>{{ $e->category->name ?? 'N/A' }} <span class="badge bg-warning text-dark ms-1">Expense</span></td>
                                    <td class="text-end text-danger">{{ number_format($e->amount, 0) }}</td>
                                </tr>
                            @endforeach
                            @if($payments->isEmpty() && $expenses->isEmpty())
                                <tr><td colspan="3" class="text-center text-muted">No outgoing cash</td></tr>
                            @endif
                        </tbody>
                        <tfoot>
                            <tr class="table-active">
                                <td colspan="2" class="text-end fw-bold">Final Total:</td>
                                <td class="text-end fw-bold text-danger">{{ number_format($totalPayments, 0) }}</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection
