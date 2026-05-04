@extends('layouts.app')

@section('title', 'Daily Customer Report - NF Dev')

@section('content')
<div class="row align-items-center mb-4">
    <div class="col-sm-6">
        <h2 class="mb-0">Daily Customer Report</h2>
        <p class="text-muted mb-0">Summary of customer activity and balances.</p>
    </div>
    <div class="col-sm-6 text-sm-end mt-3 mt-sm-0">
        <div class="dropdown d-inline-block">
            <button class="btn btn-primary dropdown-toggle shadow-sm" type="button" data-bs-toggle="dropdown">
                <i class="bi bi-printer me-1"></i>Print Report
            </button>
            <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0">
                <li><a class="dropdown-item" href="{{ route('reports.daily_customer_pdf', request()->query()) }}" target="_blank"><i class="bi bi-file-pdf me-2"></i>A4 Landscape PDF</a></li>
                <li><a class="dropdown-item" href="{{ route('reports.daily_customer_thermal', request()->query()) }}" target="_blank"><i class="bi bi-printer me-2"></i>Thermal Print</a></li>
            </ul>
        </div>
    </div>
</div>

<div class="card border-0 shadow-sm mb-4">
    <div class="card-body">
        <form method="GET" action="{{ route('reports.daily_customer') }}" class="row g-3 align-items-end">
            <div class="col-md-3">
                <label class="form-label small fw-bold text-uppercase">Start Date</label>
                <input type="text" name="start_date" class="form-control js-date" value="{{ $start->format('Y-m-d') }}">
            </div>
            <div class="col-md-3">
                <label class="form-label small fw-bold text-uppercase">End Date</label>
                <input type="text" name="end_date" class="form-control js-date" value="{{ $end->format('Y-m-d') }}">
            </div>
            <div class="col-md-3">
                <label class="form-label small fw-bold text-uppercase">Show Customers</label>
                <select name="filter" class="form-select">
                    <option value="activity" {{ $filter == 'activity' ? 'selected' : '' }}>Only with Activity</option>
                    <option value="all" {{ $filter == 'all' ? 'selected' : '' }}>All Active Customers</option>
                </select>
            </div>
            <div class="col-md-3">
                <button type="submit" class="btn btn-primary w-100 shadow-sm">
                    <i class="bi bi-filter me-1"></i>Generate Report
                </button>
            </div>
        </form>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-bordered align-middle mb-0 small">
                <thead class="bg-light text-uppercase fw-bold">
                    <tr>
                        <th rowspan="2" class="ps-3 align-middle">Customer</th>
                        <th rowspan="2" class="text-end align-middle">Opening</th>
                        <th colspan="3" class="text-center">Sales Activity</th>
                        <th colspan="2" class="text-center">Receipts Activity</th>
                        <th rowspan="2" class="text-end align-middle pe-3">Closing</th>
                    </tr>
                    <tr>
                        <th class="text-center">Invoices</th>
                        <th class="text-center">Items</th>
                        <th class="text-end">Total Sale</th>
                        <th class="text-center">Ref</th>
                        <th class="text-end">Total Rcvd</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($report as $row)
                    <tr>
                        <td class="ps-3 fw-bold">{{ $row['customer']->name }}</td>
                        <td class="text-end">{{ number_format($row['opening'], 2) }}</td>
                        
                        {{-- Sales Column --}}
                        <td class="text-center small py-1">
                            @foreach($row['sale_items'] as $item)
                                <div>#{{ $item['invoice'] }}</div>
                            @endforeach
                        </td>
                        <td class="small py-1">
                            @foreach($row['sale_items'] as $item)
                                <div>{{ $item['product'] }} ({{ $item['qty'] }}KG)</div>
                            @endforeach
                        </td>
                        <td class="text-end fw-bold text-primary">{{ number_format($row['total_sales'], 2) }}</td>
                        
                        {{-- Receipts Column --}}
                        <td class="text-center small py-1">
                            @foreach($row['receipt_items'] as $item)
                                <div>#{{ $item['receipt_id'] }}</div>
                            @endforeach
                        </td>
                        <td class="text-end fw-bold text-success">{{ number_format($row['total_receipts'], 2) }}</td>
                        
                        <td class="text-end fw-bold pe-3">{{ number_format($row['closing'], 2) }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="text-center py-5 text-muted">No activity found for selected criteria.</td>
                    </tr>
                    @endforelse
                </tbody>
                <tfoot class="bg-light fw-bold fs-6">
                    <tr>
                        <td class="ps-3">GRAND TOTALS:</td>
                        <td class="text-end">{{ number_format($grandTotals['opening'], 2) }}</td>
                        <td colspan="2"></td>
                        <td class="text-end text-primary">{{ number_format($grandTotals['sales'], 2) }}</td>
                        <td></td>
                        <td class="text-end text-success">{{ number_format($grandTotals['receipts'], 2) }}</td>
                        <td class="text-end pe-3">{{ number_format($grandTotals['closing'], 2) }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>
@endsection
