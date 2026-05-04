@extends('layouts.app')

@section('title', 'Payments - NF Dev')

@section('content')
<div class="row align-items-center mb-4">
    <div class="col-sm-6">
        <h2 class="mb-0">Supplier Payments</h2>
    </div>
    <div class="col-sm-6 text-sm-end mt-3 mt-sm-0">
        <a href="{{ route('payments.bulk') }}" class="btn btn-outline-primary me-2">
            <i class="bi bi-collection me-1"></i>Bulk Payments
        </a>
        <a href="{{ route('payments.create') }}" class="btn btn-primary">
            <i class="bi bi-credit-card me-1"></i>New Payment
        </a>
    </div>
</div>

<div class="card border-0 shadow-sm mb-4">
    <div class="card-body">
        <form method="GET" action="{{ route('payments.index') }}" class="row g-3 align-items-end">
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
                        <th>Account</th>
                        <th class="text-end">Amount</th>
                        <th class="text-center">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($payments as $payment)
                    <tr>
                        <td class="ps-3">{{ \Carbon\Carbon::parse($payment->date)->format('d-m-y') }}</td>
                        <td>{{ $payment->supplier->name }}</td>
                        <td>
                            <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle">
                                <i class="bi bi-bank me-1 small"></i>{{ $payment->account->name }}
                            </span>
                        </td>
                        <td class="text-end fw-bold text-danger">{{ number_format($payment->amount, 0) }}</td>
                        <td class="text-center">
                            <div class="d-flex justify-content-center gap-1 flex-wrap">
                                <a href="{{ route('payments.edit', $payment->id) }}" class="btn btn-sm btn-outline-primary" title="Edit">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <form action="{{ route('payments.destroy', $payment->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this payment?');" class="d-inline">
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
                            No payments recorded.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($payments->hasPages())
    <div class="card-footer bg-white border-0 py-3">
        {{ $payments->withQueryString()->links() }}
    </div>
    @endif
</div>
@endsection
