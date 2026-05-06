@extends('layouts.app')

@section('title', 'Customers - NF Dev')

@section('content')
<div class="row align-items-center mb-4">
    <div class="col-sm-6">
        <h2 class="mb-0"><i class="bi bi-people me-2"></i>Customers</h2>
    </div>
    <div class="col-sm-6 text-sm-end mt-3 mt-sm-0">
        <a href="{{ route('customers.create') }}" class="btn btn-primary">
            <i class="bi bi-person-plus me-1"></i>New Customer
        </a>
    </div>
</div>

{{-- Filter Card --}}
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body">
        <form method="GET" action="{{ route('customers.index') }}" class="row g-3 align-items-end">
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
                    <option value="active_transactions" {{ $show == 'active_transactions' ? 'selected' : '' }}>Active + Transactions</option>
                    <option value="all_transactions" {{ $show == 'all_transactions' ? 'selected' : '' }}>All + Transactions</option>
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
                    <a href="{{ route('customers.index') }}" class="btn btn-outline-secondary flex-fill">
                        <i class="bi bi-arrow-counterclockwise me-1"></i>Reset
                    </a>
                </div>
            </div>
        </form>
    </div>
</div>

{{-- Print Buttons --}}
<div class="d-flex gap-2 mb-3 justify-content-end">
    <a href="{{ route('reports.customers_list_thermal', request()->query()) }}" target="_blank" class="btn btn-outline-dark btn-sm">
        <i class="bi bi-printer me-1"></i>Thermal Print (79mm)
    </a>
    <a href="{{ route('reports.customers_list_a4', request()->query()) }}" target="_blank" class="btn btn-outline-dark btn-sm">
        <i class="bi bi-file-earmark-text me-1"></i>A4 Print
    </a>
</div>

{{-- Data Table --}}
@php
    $totalOpening = $customers->sum('computed_opening');
    $totalDr = $customers->sum('computed_dr');
    $totalCr = $customers->sum('computed_cr');
    $totalBalance = $customers->sum('computed_balance');
@endphp
<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-light">
                    <tr>
                        <th class="ps-3" style="width: 40px;">
                            <input type="checkbox" id="selectAll" class="form-check-input">
                        </th>
                        <th>Name</th>
                        <th class="text-end">Opening</th>
                        <th class="text-end">Sale</th>
                        <th class="text-end">Receive</th>
                        <th class="text-end">Balance</th>
                        <th class="text-center">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($customers as $customer)
                    <tr>
                        <td class="ps-3">
                            <input type="checkbox" name="customer_ids[]" value="{{ $customer->id }}" class="form-check-input customer-checkbox">
                        </td>
                        <td>
                            <div class="fw-bold">{{ $customer->name }}</div>
                            <div class="text-muted small">{{ $customer->phone ?? '--' }}</div>
                        </td>
                        <td class="text-end">{{ number_format($customer->computed_opening, 0) }}</td>
                        <td class="text-end fw-semibold">{{ number_format($customer->computed_dr, 0) }}</td>
                        <td class="text-end fw-semibold">{{ number_format($customer->computed_cr, 0) }}</td>
                        <td class="text-end fw-bold">{{ number_format($customer->computed_balance, 0) }}</td>
                        <td class="text-center text-nowrap">
                            <div class="btn-group gap-1">
                                <a href="{{ route('customers.show', $customer->id) }}" class="btn btn-sm btn-outline-dark rounded-pill" title="Ledger">
                                    <i class="bi bi-journal-text"></i>
                                </a>
                                <a href="{{ route('customers.edit', $customer->id) }}" class="btn btn-sm btn-outline-dark rounded-pill" title="Edit">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <form action="{{ route('customers.toggle', $customer->id) }}" method="POST" class="d-inline">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-outline-dark rounded-pill" title="{{ $customer->is_active ? 'Deactivate' : 'Activate' }}">
                                        <i class="bi bi-{{ $customer->is_active ? 'slash-circle' : 'check-circle' }}"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center py-5 text-muted">
                            <i class="bi bi-people d-block fs-1 mb-2"></i>No customers found.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
                @if($customers->count())
                <tfoot class="bg-light">
                    <tr class="fw-bold">
                        <td class="ps-3" colspan="2">Totals ({{ $customers->count() }})</td>
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

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const selectAll = document.getElementById('selectAll');
    const checkboxes = document.querySelectorAll('.customer-checkbox');
    const thermalBtn = document.querySelector('a[href*="customers-list/thermal"]');
    const a4Btn = document.querySelector('a[href*="customers-list/a4"]');
    
    const baseThermalUrl = "{!! route('reports.customers_list_thermal', request()->query()) !!}";
    const baseA4Url = "{!! route('reports.customers_list_a4', request()->query()) !!}";

    function updatePrintLinks() {
        const selectedIds = Array.from(checkboxes)
            .filter(i => i.checked)
            .map(i => i.value);
        
        if (selectedIds.length > 0) {
            const idsParam = selectedIds.join(',');
            thermalBtn.href = baseThermalUrl + (baseThermalUrl.includes('?') ? '&' : '?') + 'ids=' + idsParam;
            a4Btn.href = baseA4Url + (baseA4Url.includes('?') ? '&' : '?') + 'ids=' + idsParam;
            thermalBtn.classList.replace('btn-outline-dark', 'btn-primary');
            a4Btn.classList.replace('btn-outline-dark', 'btn-primary');
        } else {
            thermalBtn.href = baseThermalUrl;
            a4Btn.href = baseA4Url;
            thermalBtn.classList.replace('btn-primary', 'btn-outline-dark');
            a4Btn.classList.replace('btn-primary', 'btn-outline-dark');
        }
    }

    selectAll.addEventListener('change', function() {
        checkboxes.forEach(cb => cb.checked = selectAll.checked);
        updatePrintLinks();
    });

    checkboxes.forEach(cb => {
        cb.addEventListener('change', updatePrintLinks);
    });
});
</script>
@endsection
@endsection
