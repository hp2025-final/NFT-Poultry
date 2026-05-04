@extends('layouts.app')

@section('title', 'Equity Transactions - NF Dev')

@section('content')
<div class="row align-items-center mb-4">
    <div class="col-sm-6">
        <h2 class="mb-0">Equity Transactions</h2>
        <p class="text-muted mb-0 small uppercase">Manage owner's capital and drawings.</p>
    </div>
    <div class="col-sm-6 text-sm-end mt-3 mt-sm-0">
        <a href="{{ route('equity.create') }}" class="btn btn-primary shadow-sm">
            <i class="bi bi-person-plus-fill me-1"></i>Record Equity Transaction
        </a>
    </div>
</div>

<div class="row g-4 mb-4">
    <div class="col-md-4">
        <div class="card border-0 shadow-sm bg-primary text-white p-3 text-center">
            <div class="small text-uppercase opacity-75">Total Capital</div>
            <h3 class="fw-bold mb-0 mt-2">{{ number_format($totalCapital, 2) }}</h3>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-0 shadow-sm bg-danger text-white p-3 text-center">
            <div class="small text-uppercase opacity-75">Total Drawings</div>
            <h3 class="fw-bold mb-0 mt-2">{{ number_format($totalDrawings, 2) }}</h3>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-0 shadow-sm bg-success text-white p-3 text-center">
            <div class="small text-uppercase opacity-75">Net Owner's Equity</div>
            <h3 class="fw-bold mb-0 mt-2">{{ number_format($netEquity, 2) }}</h3>
        </div>
    </div>
</div>

<div class="card border-0 shadow-sm mb-4">
    <div class="card-body">
        <form method="GET" action="{{ route('equity.index') }}" class="row g-3 align-items-end">
            <div class="col-md-4">
                <label class="form-label small fw-bold text-uppercase">Start Date</label>
                <input type="text" name="start_date" class="form-control js-date" value="{{ request('start_date') }}" placeholder="YYYY-MM-DD">
            </div>
            <div class="col-md-4">
                <label class="form-label small fw-bold text-uppercase">End Date</label>
                <input type="text" name="end_date" class="form-control js-date" value="{{ request('end_date') }}" placeholder="YYYY-MM-DD">
            </div>
            <div class="col-md-4">
                <button type="submit" class="btn btn-primary w-100 shadow-sm">
                    <i class="bi bi-filter me-1"></i>Filter
                </button>
            </div>
        </form>
    </div>
</div>

<div class="card border-0 shadow-sm overflow-hidden">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-light text-uppercase small fw-bold">
                    <tr>
                        <th class="ps-3 border-0">Date</th>
                        <th class="border-0">Account</th>
                        <th class="border-0">Type</th>
                        <th class="text-end border-0">Amount</th>
                        <th class="border-0 ps-4">Note</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($txns as $txn)
                    <tr>
                        <td class="ps-3 small">{{ \Carbon\Carbon::parse($txn->date)->format('d-m-y') }}</td>
                        <td>
                            <span class="badge bg-secondary-subtle text-dark border border-secondary-subtle x-small">
                                <i class="bi bi-bank me-1"></i>{{ $txn->account->name }}
                            </span>
                        </td>
                        <td>
                            @if($txn->type === 'capital')
                                <span class="badge bg-success border border-success px-3 py-1 rounded-pill">Capital</span>
                            @else
                                <span class="badge bg-danger border border-danger px-3 py-1 rounded-pill">Drawing</span>
                            @endif
                        </td>
                        <td class="text-end fw-bold {{ $txn->type === 'capital' ? 'text-success' : 'text-danger' }}">
                            {{ $txn->type === 'drawing' ? '-' : '+' }} {{ number_format($txn->amount, 2) }}
                        </td>
                        <td class="ps-4 small text-muted">{{ $txn->note ?? '-' }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="text-center py-5 text-muted">No equity transactions found.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($txns->hasPages())
    <div class="card-footer bg-white border-0 py-3">
        {{ $txns->withQueryString()->links() }}
    </div>
    @endif
</div>
@endsection
