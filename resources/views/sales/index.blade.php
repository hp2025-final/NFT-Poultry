@extends('layouts.app')

@section('title', 'Sales - NF Dev')

@section('content')
<div class="row align-items-center mb-4">
    <div class="col-sm-6">
        <h2 class="mb-0">Sales</h2>
    </div>
    <div class="col-sm-6 text-sm-end mt-3 mt-sm-0">
        <a href="{{ route('sales.bulk') }}" class="btn btn-outline-primary me-2">
            <i class="bi bi-stack me-1"></i>Bulk Sale
        </a>
        <a href="{{ route('sales.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-lg me-1"></i>New Sale
        </a>
    </div>
</div>

<div class="card border-0 shadow-sm mb-4">
    <div class="card-body">
        <form method="GET" action="{{ route('sales.index') }}" class="row g-3 align-items-end">
            <div class="col-md-4">
                <label class="form-label">Start Date</label>
                <input type="text" name="start_date" class="form-control js-date" value="{{ request('start_date', date('Y-m-d')) }}" placeholder="YYYY-MM-DD">
            </div>
            <div class="col-md-4">
                <label class="form-label">End Date</label>
                <input type="text" name="end_date" class="form-control js-date" value="{{ request('end_date', date('Y-m-d')) }}" placeholder="YYYY-MM-DD">
            </div>
            <div class="col-md-4">
                <button type="submit" class="btn btn-primary w-100">
                    <i class="bi bi-filter me-1"></i>Filter
                </button>
            </div>
        </form>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-light">
                    <tr>
                        <th class="ps-3">Date</th>
                        <th>Customer</th>
                        <th class="text-end">Qty (KG)</th>
                        <th class="text-end">Rate</th>
                        <th class="text-end">Amount</th>
                        <th class="text-center">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($sales as $sale)
                    <tr>
                        <td class="ps-3">{{ \Carbon\Carbon::parse($sale->date)->format('d-m-y') }}</td>
                        <td>{{ $sale->customer->name }}</td>
                        <td class="text-end">{{ number_format($sale->items->sum('qty'), 2) }}</td>
                        <td class="text-end">{{ $sale->items->count() == 1 ? number_format($sale->items->first()->price, 0) : '-' }}</td>
                        <td class="text-end fw-bold">{{ number_format($sale->total_amount, 0) }}</td>
                        <td class="text-center">
                            <div class="d-flex justify-content-center gap-1 flex-wrap">
                                {{-- Thermal Print --}}
                                <a href="{{ route('reports.sale_invoice_thermal', $sale->id) }}" target="_blank" class="btn btn-sm btn-outline-success" title="Print Thermal 70mm">
                                    <i class="bi bi-printer"></i>
                                </a>
                                {{-- Edit --}}
                                <a href="{{ route('sales.edit', $sale->id) }}" class="btn btn-sm btn-outline-primary" title="Edit">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                {{-- Delete --}}
                                <form action="{{ route('sales.destroy', $sale->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this sale? Stock will be restored.');" class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center py-5 text-muted">
                            <i class="bi bi-inbox fs-1 d-block mb-3"></i>
                            No sales records found.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($sales->hasPages())
    <div class="card-footer bg-white border-0 py-3">
        {{ $sales->withQueryString()->links() }}
    </div>
    @endif
</div>
@endsection
