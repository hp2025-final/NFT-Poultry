@extends('layouts.app')

@section('title', 'Purchases - NF Dev')

@section('content')
<div class="row align-items-center mb-4">
    <div class="col-sm-6">
        <h2 class="mb-0">Purchases</h2>
    </div>
    <div class="col-sm-6 text-sm-end mt-3 mt-sm-0">
        <a href="{{ route('purchases.bulk') }}" class="btn btn-outline-primary me-2">
            <i class="bi bi-stack me-1"></i>Bulk Purchase
        </a>
        <a href="{{ route('purchases.create') }}" class="btn btn-primary">
            <i class="bi bi-cart-plus me-1"></i>New Purchase
        </a>
    </div>
</div>

<div class="card border-0 shadow-sm mb-4">
    <div class="card-body">
        <form method="GET" action="{{ route('purchases.index') }}" class="row g-3 align-items-end">
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
                        <th>Supplier</th>
                        <th class="text-end">Qty (KG)</th>
                        <th class="text-end">Rate</th>
                        <th class="text-end">Amount</th>
                        <th class="text-center">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($purchases as $purchase)
                    <tr>
                        <td class="ps-3">{{ \Carbon\Carbon::parse($purchase->date)->format('d-m-y') }}</td>
                        <td>{{ $purchase->supplier->name }}</td>
                        <td class="text-end">{{ number_format($purchase->items->sum('qty'), 2) }}</td>
                        <td class="text-end">{{ $purchase->items->count() == 1 ? number_format($purchase->items->first()->price, 0) : '-' }}</td>
                        <td class="text-end fw-bold">{{ number_format($purchase->total_amount, 0) }}</td>
                        <td class="text-center">
                            <div class="d-flex justify-content-center gap-1 flex-wrap">
                                <a href="{{ route('reports.purchase_invoice_thermal', $purchase->id) }}" target="_blank" class="btn btn-sm btn-outline-success" title="Print Thermal 70mm">
                                    <i class="bi bi-printer"></i>
                                </a>
                                <a href="{{ route('purchases.edit', $purchase->id) }}" class="btn btn-sm btn-outline-primary" title="Edit">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <form action="{{ route('purchases.destroy', $purchase->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this purchase? Stock will be corrected.');" class="d-inline">
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
                            No purchase records found.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($purchases->hasPages())
    <div class="card-footer bg-white border-0 py-3">
        {{ $purchases->withQueryString()->links() }}
    </div>
    @endif
</div>
@endsection
