@extends('layouts.app')

@section('title', $account->name . ' - Account Statement - NF Dev')

@section('content')
<div class="row align-items-center mb-4">
    <div class="col-sm-6">
        <h2 class="mb-0">{{ $account->name }}</h2>
        <p class="text-muted mb-0">Statement of Transactions</p>
    </div>
    <div class="col-sm-6 text-sm-end mt-3 mt-sm-0">
        <div class="btn-group shadow-sm">
            <a href="{{ route('accounts.edit', $account->id) }}" class="btn btn-outline-secondary">
                <i class="bi bi-pencil me-1"></i>Edit
            </a>
            <a href="{{ route('accounts.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-1"></i>Back
            </a>
        </div>
    </div>
</div>

<div class="card border-0 shadow-sm mb-4">
    <div class="card-body">
        <form method="GET" action="{{ route('accounts.show', $account->id) }}" class="row g-3 align-items-end">
            <div class="col-md-4">
                <label class="form-label small fw-bold text-uppercase">Start Date</label>
                <input type="text" name="start_date" class="form-control js-date" value="{{ request('start_date') }}" placeholder="YYYY-MM-DD">
            </div>
            <div class="col-md-4">
                <label class="form-label small fw-bold text-uppercase">End Date</label>
                <input type="text" name="end_date" class="form-control js-date" value="{{ request('end_date') }}" placeholder="YYYY-MM-DD">
            </div>
            <div class="col-md-4">
                <button type="submit" class="btn btn-primary w-100">
                    <i class="bi bi-filter me-1"></i>Filter Statement
                </button>
            </div>
        </form>
    </div>
</div>

<div class="row g-4 mb-4">
    <div class="col-md-4">
        <div class="card border-0 shadow-sm bg-primary text-white p-3 text-center">
            <div class="small text-uppercase opacity-75">Opening Balance</div>
            <h3 class="fw-bold mb-0 mt-2">{{ number_format($openingBalance, 2) }}</h3>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-0 shadow-sm bg-info text-white p-3 text-center">
            <div class="small text-uppercase opacity-75">Brought Forward</div>
            <h3 class="fw-bold mb-0 mt-2">{{ number_format($broughtForward, 2) }}</h3>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-0 shadow-sm bg-success text-white p-3 text-center">
            <div class="small text-uppercase opacity-75">Current Balance</div>
            <h3 class="fw-bold mb-0 mt-2">{{ number_format($runningBalance, 2) }}</h3>
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
                        <th>Reference</th>
                        <th>Note</th>
                        <th class="text-end">Debit (+)</th>
                        <th class="text-end">Credit (-)</th>
                        <th class="text-end pe-3">Balance</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td colspan="6" class="ps-3 text-muted fw-bold py-2">Brought Forward</td>
                        <td class="text-end pe-3 fw-bold py-2">{{ number_format($broughtForward, 2) }}</td>
                    </tr>
                    @forelse($paginator as $txn)
                    <tr>
                        <td class="ps-3 small">{{ \Carbon\Carbon::parse($txn['date'])->format('d-m-y') }}</td>
                        <td>
                            <span class="badge bg-light text-dark border">{{ $txn['type'] }}</span>
                        </td>
                        <td class="small">{{ $txn['ref'] }}</td>
                        <td class="small text-muted">{{ Str::limit($txn['note'], 30) }}</td>
                        <td class="text-end text-success fw-bold">
                            {{ $txn['debit'] > 0 ? number_format($txn['debit'], 2) : '-' }}
                        </td>
                        <td class="text-end text-danger fw-bold">
                            {{ $txn['credit'] > 0 ? number_format($txn['credit'], 2) : '-' }}
                        </td>
                        <td class="text-end pe-3 fw-bold">{{ number_format($txn['balance'], 2) }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center py-5 text-muted">No transactions found for these dates.</td>
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
