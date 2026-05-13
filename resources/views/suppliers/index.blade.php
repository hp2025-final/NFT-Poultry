@extends('layouts.app')

@section('title', 'Suppliers - NF Dev')

@section('content')
<div class="row align-items-center mb-4">
    <div class="col-sm-6">
        <h2 class="mb-0"><i class="bi bi-truck me-2"></i>Suppliers</h2>
    </div>
    <div class="col-sm-6 text-sm-end mt-3 mt-sm-0">
        <a href="{{ route('suppliers.create') }}" class="btn btn-primary">
            <i class="bi bi-truck me-1"></i>New Supplier
        </a>
    </div>
</div>

{{-- Filter Card --}}
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body">
        <form method="GET" action="{{ route('suppliers.index') }}" class="row g-3 align-items-end">
            <div class="col-lg-3 col-md-6">
                <label class="form-label"><i class="bi bi-search me-1"></i>Search</label>
                <input type="text" name="q" class="form-control" value="{{ request('q') }}" placeholder="Name or phone...">
            </div>
            <div class="col-lg-2 col-md-6">
                <label class="form-label"><i class="bi bi-funnel me-1"></i>Status</label>
                <select name="show" class="form-select">
                    <option value="active" {{ $show == 'active' ? 'selected' : '' }}>Active</option>
                    <option value="archived" {{ $show == 'archived' ? 'selected' : '' }}>Deactive</option>
                    <option value="all" {{ $show == 'all' ? 'selected' : '' }}>All</option>
                </select>
            </div>
            <div class="col-lg-2 col-md-6">
                <label class="form-label"><i class="bi bi-calendar-event me-1"></i>Date From</label>
                <input type="date" name="start_date" class="form-control js-date" value="{{ $startDate }}">
            </div>
            <div class="col-lg-2 col-md-6">
                <label class="form-label"><i class="bi bi-calendar-check me-1"></i>Date To</label>
                <input type="date" name="end_date" class="form-control js-date" value="{{ $endDate }}">
            </div>
            <div class="col-lg-3 col-md-12">
                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary flex-fill">
                        <i class="bi bi-funnel-fill me-1"></i>Filter
                    </button>
                    <a href="{{ route('suppliers.index') }}" class="btn btn-outline-secondary flex-fill">
                        <i class="bi bi-arrow-counterclockwise me-1"></i>Reset
                    </a>
                </div>
            </div>
        </form>
    </div>
</div>

{{-- Print Buttons --}}
<div class="d-flex gap-2 mb-3 justify-content-end">
    <a href="{{ route('reports.suppliers_list_thermal', request()->query()) }}" target="_blank" class="btn btn-outline-dark btn-sm">
        <i class="bi bi-printer me-1"></i>Thermal Print (70mm)
    </a>
    <a href="{{ route('reports.suppliers_list_a4', request()->query()) }}" target="_blank" class="btn btn-outline-dark btn-sm">
        <i class="bi bi-file-earmark-text me-1"></i>A4 Print
    </a>
</div>

{{-- Data Table --}}
@php
    $totalOpening = $suppliers->sum('computed_opening');
    $totalDr = $suppliers->sum('computed_dr');
    $totalCr = $suppliers->sum('computed_cr');
    $totalBalance = $suppliers->sum('computed_balance');
@endphp
<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-light">
                    <tr>
                        <th class="ps-3">Name</th>
                        <th class="text-end">Opening</th>
                        <th class="text-end">Payment</th>
                        <th class="text-end">Purchase</th>
                        <th class="text-end">Balance</th>
                        <th class="text-center">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($suppliers as $supplier)
                    <tr>
                        <td class="ps-3">
                            <div class="fw-bold">{{ $supplier->name }}</div>
                            <div class="text-muted small">{{ $supplier->phone ?? '--' }}</div>
                        </td>
                        <td class="text-end">{{ number_format($supplier->computed_opening, 0) }}</td>
                        <td class="text-end fw-semibold">{{ number_format($supplier->computed_dr, 0) }}</td>
                        <td class="text-end fw-semibold">{{ number_format($supplier->computed_cr, 0) }}</td>
                        <td class="text-end fw-bold">{{ number_format($supplier->computed_balance, 0) }}</td>
                        <td class="text-center text-nowrap">
                            <div class="btn-group gap-1">
                                <a href="{{ route('suppliers.show', $supplier->id) }}" class="btn btn-sm btn-outline-dark rounded-pill" title="Ledger">
                                    <i class="bi bi-journal-text"></i>
                                </a>
                                <a href="{{ route('suppliers.edit', $supplier->id) }}" class="btn btn-sm btn-outline-dark rounded-pill" title="Edit">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <form action="{{ route('suppliers.toggle', $supplier->id) }}" method="POST" class="d-inline">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-outline-dark rounded-pill" title="{{ $supplier->is_active ? 'Deactivate' : 'Activate' }}">
                                        <i class="bi bi-{{ $supplier->is_active ? 'slash-circle' : 'check-circle' }}"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center py-5 text-muted">
                            <i class="bi bi-truck d-block fs-1 mb-2"></i>No suppliers found.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
                @if($suppliers->count())
                <tfoot class="bg-light">
                    <tr class="fw-bold">
                        <td class="ps-3">Totals ({{ $suppliers->count() }})</td>
                        <td class="text-end">{{ number_format($totalOpening, 0) }}</td>
                        <td class="text-end">{{ number_format($totalDr, 0) }}</td>
                        <td class="text-end">{{ number_format($totalCr, 0) }}</td>
                        <td class="text-end">{{ number_format($totalBalance, 0) }}</td>
                        <td></td>
                    </tr>
                </tfoot>
                @endif
            </table>
        </div>
    </div>
</div>
@endsection
