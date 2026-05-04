@extends('layouts.app')

@section('title', 'Expenses - NF Dev')

@section('content')
<div class="row align-items-center mb-4">
    <div class="col-sm-6">
        <h2 class="mb-0">Expense Management</h2>
        <p class="text-muted mb-0 small uppercase">Track costs and business spending.</p>
    </div>
    <div class="col-sm-6 text-sm-end mt-3 mt-sm-0">
        <a href="{{ route('expenses.create') }}" class="btn btn-primary shadow-sm">
            <i class="bi bi-receipt me-1"></i>Record Expense
        </a>
    </div>
</div>

<div class="card border-0 shadow-sm mb-4">
    <div class="card-body">
        <form method="GET" action="{{ route('expenses.index') }}" class="row g-3 align-items-end">
            <div class="col-md-4">
                <label class="form-label small fw-bold text-uppercase">Start Date</label>
                <input type="text" name="start_date" class="form-control js-date" value="{{ request('start_date', \Carbon\Carbon::now()->startOfMonth()->format('Y-m-d')) }}">
            </div>
            <div class="col-md-4">
                <label class="form-label small fw-bold text-uppercase">End Date</label>
                <input type="text" name="end_date" class="form-control js-date" value="{{ request('end_date', \Carbon\Carbon::now()->format('Y-m-d')) }}">
            </div>
            <div class="col-md-4">
                <button type="submit" class="btn btn-primary w-100 shadow-sm">
                    <i class="bi bi-filter me-1"></i>Filter
                </button>
            </div>
        </form>
    </div>
</div>

<div class="card border-0 shadow-sm overflow-hidden text-nowrap">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-light text-uppercase small fw-bold">
                    <tr>
                        <th class="ps-3 border-0">Date</th>
                        <th class="border-0">Category</th>
                        <th class="border-0">Paid From</th>
                        <th class="border-0">Note</th>
                        <th class="text-end border-0">Amount</th>
                        <th class="text-center border-0">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($expenses as $expense)
                    <tr>
                        <td class="ps-3 small">{{ \Carbon\Carbon::parse($expense->date)->format('d-m-y') }}</td>
                        <td>
                            <span class="badge bg-danger-subtle text-danger border border-danger-subtle x-small">
                                {{ $expense->category->name ?? 'Uncategorized' }}
                            </span>
                        </td>
                        <td>
                            <span class="badge bg-secondary-subtle text-dark border border-secondary-subtle x-small">
                                <i class="bi bi-bank me-1"></i>{{ $expense->account->name ?? 'Cash' }}
                            </span>
                        </td>
                        <td class="small text-muted">{{ Str::limit($expense->note, 30) }}</td>
                        <td class="text-end fw-bold text-danger">{{ number_format($expense->amount, 2) }}</td>
                        <td class="text-center">
                            <div class="btn-group gap-1">
                                <a href="{{ route('expenses.edit', $expense->id) }}" class="btn btn-sm btn-outline-primary rounded-pill px-3">
                                    <i class="bi bi-pencil me-1 small"></i>Edit
                                </a>
                                <form action="{{ route('expenses.destroy', $expense->id) }}" method="POST" onsubmit="return confirm('Delete this expense?');" class="d-inline">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger rounded-pill px-3">
                                        <i class="bi bi-trash me-1 small"></i>Delete
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center py-5 text-muted">No expenses recorded for this period.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($expenses->hasPages())
    <div class="card-footer bg-white border-0 py-3">
        {{ $expenses->withQueryString()->links() }}
    </div>
    @endif
</div>
@endsection
