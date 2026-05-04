@extends('layouts.app')

@section('title', 'Stock Adjustments - NF Dev')

@section('content')
<div class="row align-items-center mb-4">
    <div class="col-sm-6">
        <h2 class="mb-0">Stock Adjustments</h2>
        <p class="text-muted mb-0">Manually increase or decrease inventory stock.</p>
    </div>
    <div class="col-sm-6 text-sm-end mt-3 mt-sm-0">
        <a href="{{ route('stock_adjustments.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-lg me-1"></i>New Adjustment
        </a>
    </div>
</div>

<div class="card border-0 shadow-sm mb-4">
    <div class="card-body">
        <form method="GET" action="{{ route('stock_adjustments.index') }}" class="row g-3 align-items-end">
            <div class="col-md-4">
                <label class="form-label">Start Date</label>
                <input type="text" name="start_date" class="form-control js-date" value="{{ request('start_date') }}" placeholder="YYYY-MM-DD">
            </div>
            <div class="col-md-4">
                <label class="form-label">End Date</label>
                <input type="text" name="end_date" class="form-control js-date" value="{{ request('end_date') }}" placeholder="YYYY-MM-DD">
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
                <thead class="bg-light text-uppercase small fw-bold">
                    <tr>
                        <th class="ps-3">Date</th>
                        <th>Product</th>
                        <th class="text-center">Type</th>
                        <th class="text-center">Qty (KG)</th>
                        <th class="text-end">Unit Cost</th>
                        <th class="text-end">Total Amount</th>
                        <th class="text-center">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($adjustments as $adj)
                    <tr>
                        <td class="ps-3">{{ \Carbon\Carbon::parse($adj->date)->format('d-m-y') }}</td>
                        <td>
                            <div class="fw-bold">{{ $adj->product->name }}</div>
                            <div class="text-muted x-small">{{ Str::limit($adj->note, 40) }}</div>
                        </td>
                        <td class="text-center">
                            @if($adj->type === 'increase')
                                <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1">
                                    <i class="bi bi-arrow-up me-1"></i>Increase
                                </span>
                            @else
                                <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-2 py-1">
                                    <i class="bi bi-arrow-down me-1"></i>Decrease
                                </span>
                            @endif
                        </td>
                        <td class="text-center fw-bold">{{ number_format($adj->qty, 2) }}</td>
                        <td class="text-end">{{ number_format($adj->unit_cost, 2) }}</td>
                        <td class="text-end fw-bold">{{ number_format($adj->amount, 2) }}</td>
                        <td class="text-center">
                            <div class="dropdown">
                                <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                    Actions
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0">
                                    <li><a class="dropdown-item" href="{{ route('stock_adjustments.edit', $adj->id) }}"><i class="bi bi-pencil me-2"></i>Edit</a></li>
                                    <li><hr class="dropdown-divider"></li>
                                    <li>
                                        <form action="{{ route('stock_adjustments.destroy', $adj->id) }}" method="POST" onsubmit="return confirm('Are you sure? This will reverse the stock change.');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="dropdown-item text-danger"><i class="bi bi-trash me-2"></i>Delete</button>
                                        </form>
                                    </li>
                                </ul>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center py-5 text-muted">No stock adjustments found.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($adjustments->hasPages())
    <div class="card-footer bg-white border-0 py-3">
        {{ $adjustments->withQueryString()->links() }}
    </div>
    @endif
</div>
@endsection
