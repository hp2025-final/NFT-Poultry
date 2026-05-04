@extends('layouts.app')

@section('title', $supplier->name . ' - Supplier Ledger - NF Dev')

@section('content')
<div class="row align-items-center mb-4">
    <div class="col-sm-6">
        <h2 class="mb-0">{{ $supplier->name }}</h2>
        <p class="text-muted mb-0">Supplier Account Ledger</p>
    </div>
    <div class="col-sm-6 text-sm-end mt-3 mt-sm-0">
        <div class="btn-group shadow-sm">
            <a href="{{ route('suppliers.edit', $supplier->id) }}" class="btn btn-outline-secondary">
                <i class="bi bi-pencil me-1"></i>Edit
            </a>
            <a href="{{ route('suppliers.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-1"></i>Back
            </a>
        </div>
    </div>
</div>

<div class="card border-0 shadow-sm mb-4">
    <div class="card-body">
        <form method="GET" action="{{ route('suppliers.show', $supplier->id) }}" class="row g-3 align-items-end">
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
                    <i class="bi bi-filter me-1"></i>Filter Ledger
                </button>
            </div>
        </form>
    </div>
</div>

<div class="row g-4 mb-4">
    <div class="col-md-4">
        <div class="card border-0 shadow-sm p-3 text-center bg-light">
            <div class="small text-uppercase fw-bold text-muted">Opening Balance</div>
            <h3 class="fw-bold mb-0 mt-2">{{ number_format($openingBalance, 2) }}</h3>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-0 shadow-sm p-3 text-center bg-light">
            <div class="small text-uppercase fw-bold text-muted">Brought Forward</div>
            <h3 class="fw-bold mb-0 mt-2">{{ number_format($broughtForward, 2) }}</h3>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-0 shadow-sm p-3 text-center {{ $runningBalance > 0 ? 'bg-danger-subtle text-danger' : 'bg-success-subtle text-success' }}">
            <div class="small text-uppercase fw-bold">Current Balance</div>
            <h3 class="fw-bold mb-0 mt-2">{{ number_format($runningBalance, 2) }}</h3>
            <small class="fw-bold">{{ $runningBalance > 0 ? 'Payable (We owe them)' : ($runningBalance < 0 ? 'Receivable (Advance)' : 'Clear') }}</small>
        </div>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-light text-uppercase small fw-bold">
                    <tr>
                        <th class="ps-3">Date</th>
                        <th>Type</th>
                        <th>Reference / Description</th>
                        <th class="text-end">Payment (Dr)</th>
                        <th class="text-end">Purchase (Cr)</th>
                        <th class="text-end pe-3">Balance</th>
                    </tr>
                </thead>
                <tbody>
                    <tr class="table-light">
                        <td colspan="5" class="ps-3 fw-bold py-2">Brought Forward</td>
                        <td class="text-end pe-3 fw-bold py-2">{{ number_format($broughtForward, 2) }}</td>
                    </tr>
                    @forelse($paginator as $txn)
                    <tr>
                        <td class="ps-3 small">{{ \Carbon\Carbon::parse($txn['date'])->format('d-m-y') }}</td>
                        <td>
                            <span class="badge {{ str_contains($txn['type'], 'Purchase') ? 'bg-warning-subtle text-dark' : 'bg-info-subtle text-info' }} border border-opacity-50">
                                {{ $txn['type'] }}
                            </span>
                        </td>
                        <td>
                            <div class="small fw-bold text-dark">{{ $txn['ref'] }}</div>
                            @if(!empty($txn['note']))
                                <div class="x-small text-muted italic">{{ $txn['note'] }}</div>
                            @endif
                        </td>
                        <td class="text-end text-info fw-bold">
                            {{ $txn['debit'] > 0 ? number_format($txn['debit'], 2) : '-' }}
                        </td>
                        <td class="text-end text-dark fw-bold">
                            {{ $txn['credit'] > 0 ? number_format($txn['credit'], 2) : '-' }}
                        </td>
                        <td class="text-end pe-3 fw-bold text-danger">{{ number_format($txn['balance'], 2) }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center py-5 text-muted">No transactions found for this period.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($paginator->hasPages())
    <div class="card-footer bg-white border-0 py-3">
        {{ $paginator->appends(request()->query())->links() }}
    </div>
    @endif
</div>
@endsection
