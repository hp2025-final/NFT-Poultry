@extends('layouts.app')

@section('title', 'Receipts - NF Dev')

@section('content')
<div class="row align-items-center mb-4">
    <div class="col-sm-6">
        <h2 class="mb-0">Receipts</h2>
    </div>
    <div class="col-sm-6 text-sm-end mt-3 mt-sm-0">
        <a href="{{ route('receipts.bulk') }}" class="btn btn-outline-primary me-2">
            <i class="bi bi-collection me-1"></i>Bulk Receipts
        </a>
        <a href="{{ route('receipts.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-lg me-1"></i>New Receipt
        </a>
    </div>
</div>

<div class="card border-0 shadow-sm mb-4">
    <div class="card-body">
        <form method="GET" action="{{ route('receipts.index') }}" class="row g-3 align-items-end">
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
                        <th>Account</th>
                        <th class="text-end">Amount</th>
                        <th class="text-center">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($receipts as $receipt)
                    <tr>
                        <td class="ps-3">{{ \Carbon\Carbon::parse($receipt->date)->format('d-m-y') }}</td>
                        <td>{{ $receipt->customer->name }}</td>
                        <td>
                            <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle">
                                <i class="bi bi-bank me-1 small"></i>{{ $receipt->account->name }}
                            </span>
                        </td>
                        <td class="text-end fw-bold text-success">{{ number_format($receipt->amount, 0) }}</td>
                        <td class="text-center">
                            <div class="d-flex justify-content-center gap-1 flex-wrap">
                                <a href="{{ route('receipts.edit', $receipt->id) }}" class="btn btn-sm btn-outline-primary" title="Edit">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <form action="{{ route('receipts.destroy', $receipt->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this receipt?');" class="d-inline">
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
                        <td colspan="5" class="text-center py-5 text-muted">
                            <i class="bi bi-inbox fs-1 d-block mb-3"></i>
                            No receipts found.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($receipts->hasPages())
    <div class="card-footer bg-white border-0 py-3">
        {{ $receipts->withQueryString()->links() }}
    </div>
    @endif
</div>
@endsection
