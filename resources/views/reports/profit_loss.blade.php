@extends('layouts.app')

@section('title', 'Profit & Loss Statement - NF Dev')

@section('content')
<div class="row align-items-center mb-4 no-print">
    <div class="col-sm-6">
        <h2 class="mb-0 text-primary fw-bold">Profit & Loss Statement</h2>
        <p class="text-muted mb-0 small text-uppercase">Financial Performance Analysis</p>
    </div>
    <div class="col-sm-6 text-sm-end mt-3 mt-sm-0">
        <a href="{{ route('reports.profit_loss_thermal', ['start_date' => $start, 'end_date' => $end]) }}" target="_blank" class="btn btn-outline-info shadow-sm no-print me-2">
            <i class="bi bi-printer-fill me-1"></i>Print Thermal (70mm)
        </a>
        <button onclick="window.print()" class="btn btn-outline-secondary shadow-sm no-print">
            <i class="bi bi-printer me-1"></i>Print Report
        </button>
    </div>
</div>

{{-- Date Filter Card --}}
<div class="card border-0 shadow-sm mb-4 no-print">
    <div class="card-body">
        <form action="{{ route('reports.profit_loss') }}" method="GET" class="row g-3 align-items-end">
            <div class="col-md-4">
                <label class="form-label fw-bold small text-uppercase">From Date</label>
                <input type="text" name="start_date" class="form-control js-date" value="{{ $start }}">
            </div>
            <div class="col-md-4">
                <label class="form-label fw-bold small text-uppercase">To Date</label>
                <input type="text" name="end_date" class="form-control js-date" value="{{ $end }}">
            </div>
            <div class="col-md-4">
                <button type="submit" class="btn btn-primary w-100">
                    <i class="bi bi-filter me-1"></i>Filter Report
                </button>
            </div>
        </form>
    </div>
</div>

{{-- Summary Section --}}
<div class="row g-3 mb-4">
    <div class="col-md-4">
        <div class="card border-0 shadow-sm h-100 border-start border-4 border-primary">
            <div class="card-body">
                <h6 class="text-muted small text-uppercase fw-bold mb-1">Total Sale</h6>
                <h3 class="fw-bold mb-0 text-primary">{{ number_format($totalSales, 2) }}</h3>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-0 shadow-sm h-100 border-start border-4 border-warning">
            <div class="card-body">
                <h6 class="text-muted small text-uppercase fw-bold mb-1">Total Purchase</h6>
                <h3 class="fw-bold mb-0 text-warning">{{ number_format($totalPurchases, 2) }}</h3>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-0 shadow-sm h-100 border-start border-4 border-info">
            <div class="card-body">
                <h6 class="text-muted small text-uppercase fw-bold mb-1">Gross Profit</h6>
                <h3 class="fw-bold mb-0 text-info">{{ number_format($grossProfit, 2) }}</h3>
                <small class="text-muted">(Sale - Purchase)</small>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-0 shadow-sm h-100 border-start border-4 border-danger">
            <div class="card-body">
                <h6 class="text-muted small text-uppercase fw-bold mb-1">Total Expense</h6>
                <h3 class="fw-bold mb-0 text-danger">{{ number_format($totalExpenses, 2) }}</h3>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-0 shadow-sm h-100 border-start border-4 border-success">
            <div class="card-body">
                <h6 class="text-muted small text-uppercase fw-bold mb-1">Net Profit</h6>
                <h3 class="fw-bold mb-0 text-success">{{ number_format($netProfit, 2) }}</h3>
                <small class="text-muted">(Gross Profit - Expense)</small>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-0 shadow-sm h-100 border-start border-4 border-secondary">
            <div class="card-body">
                <h6 class="text-muted small text-uppercase fw-bold mb-1">Total Stock Adjustment</h6>
                <h3 class="fw-bold mb-0 text-secondary">{{ number_format($totalStockAdjustment, 2) }}</h3>
            </div>
        </div>
    </div>
</div>

{{-- Daily Breakdown Table --}}
<div class="card border-0 shadow-sm">
    <div class="card-header bg-white py-3 border-0">
        <h5 class="mb-0 fw-bold"><i class="bi bi-calendar3 me-2"></i>Daily Breakdown</h5>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-light">
                    <tr>
                        <th class="ps-3">Date</th>
                        <th class="text-end">Sale</th>
                        <th class="text-end">Purchase</th>
                        <th class="text-end text-info">Gross Profit</th>
                        <th class="text-end text-danger">Expense</th>
                        <th class="text-end text-success">Net Profit</th>
                        <th class="text-end pe-3">Stock Adj.</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($dailyBreakdown as $row)
                    <tr>
                        <td class="ps-3 fw-bold">{{ date('d-m-Y', strtotime($row['date'])) }}</td>
                        <td class="text-end">{{ number_format($row['sales'], 2) }}</td>
                        <td class="text-end">{{ number_format($row['purchases'], 2) }}</td>
                        <td class="text-end fw-bold text-info">{{ number_format($row['gross'], 2) }}</td>
                        <td class="text-end text-danger">{{ number_format($row['expenses'], 2) }}</td>
                        <td class="text-end fw-bold text-success">{{ number_format($row['net'], 2) }}</td>
                        <td class="text-end pe-3 text-secondary">{{ number_format($row['stock_adj'], 2) }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center py-5 text-muted">No transactions found for the selected period.</td>
                    </tr>
                    @endforelse
                </tbody>
                @if(count($dailyBreakdown))
                <tfoot class="bg-light fw-bold border-top-2">
                    <tr>
                        <td class="ps-3">TOTALS</td>
                        <td class="text-end">{{ number_format(collect($dailyBreakdown)->sum('sales'), 2) }}</td>
                        <td class="text-end">{{ number_format(collect($dailyBreakdown)->sum('purchases'), 2) }}</td>
                        <td class="text-end text-info">{{ number_format(collect($dailyBreakdown)->sum('gross'), 2) }}</td>
                        <td class="text-end text-danger">{{ number_format(collect($dailyBreakdown)->sum('expenses'), 2) }}</td>
                        <td class="text-end text-success">{{ number_format(collect($dailyBreakdown)->sum('net'), 2) }}</td>
                        <td class="text-end pe-3 text-secondary">{{ number_format(collect($dailyBreakdown)->sum('stock_adj'), 2) }}</td>
                    </tr>
                </tfoot>
                @endif
            </table>
        </div>
    </div>
</div>
@endsection
