@extends('layouts.app')

@section('title', 'Bulk Receipts - NF Dev')

@section('content')
<div class="row align-items-center mb-4">
    <div class="col-sm-6">
        <h2 class="mb-0">Bulk Receipts</h2>
        <p class="text-muted mb-0">Record payments received from multiple customers.</p>
    </div>
    <div class="col-sm-6 text-sm-end mt-3 mt-sm-0">
        <a href="{{ route('receipts.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i>Back to Receipts
        </a>
    </div>
</div>

<form action="{{ route('receipts.storeBulk') }}" method="POST" onsubmit="return confirm('Are you sure you want to record these bulk receipts?');">
    @csrf

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label fw-bold small text-uppercase">Receipt Date</label>
                    <input type="text" name="date" class="form-control js-date @error('date') is-invalid @enderror" value="{{ old('date', date('Y-m-d')) }}" required>
                    @error('date') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-bold small text-uppercase">Deposit Account</label>
                    <select name="account_id" class="form-select @error('account_id') is-invalid @enderror" required>
                        @foreach($accounts as $account)
                            <option value="{{ $account->id }}" {{ old('account_id', $defaultAccountId ?? '') == $account->id ? 'selected' : '' }}>
                                {{ $account->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('account_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th class="ps-3" style="width: 50px;">#</th>
                            <th>Customer Name</th>
                            <th class="text-end" style="width: 150px;">Opening Balance</th>
                            <th style="width: 200px;">Amount Received</th>
                            <th style="width: 250px;">Note</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($customers as $index => $customer)
                        <tr>
                            <td class="ps-3 text-muted small">{{ $index + 1 }}</td>
                            <td>
                                <strong>{{ $customer->name }}</strong>
                                <input type="hidden" name="customers[{{ $index }}][id]" value="{{ $customer->id }}">
                            </td>
                            <td class="text-end fw-bold text-primary">
                                {{ number_format($customer->opening_balance_on_date, 2) }}
                            </td>
                            <td>
                                <input type="number" name="customers[{{ $index }}][amount]" step="0.01" min="0"
                                    class="form-control form-control-sm bulk-amount @error("customers.$index.amount") is-invalid @enderror"
                                    value="{{ old("customers.$index.amount") }}" placeholder="0.00">
                            </td>
                            <td>
                                <input type="text" name="customers[{{ $index }}][note]" class="form-control form-control-sm @error("customers.$index.note") is-invalid @enderror"
                                    value="{{ old("customers.$index.note") }}" placeholder="Optional note">
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="text-center py-5">No active customers found.</td>
                        </tr>
                        @endforelse
                    </tbody>
                    @if($customers->count())
                    <tfoot class="bg-light">
                        <tr class="fw-bold">
                            <td class="ps-3" colspan="2">Total</td>
                            <td class="text-end text-primary">{{ number_format($customers->sum('opening_balance_on_date'), 2) }}</td>
                            <td id="totalAmount">0</td>
                            <td></td>
                        </tr>
                    </tfoot>
                    @endif
                </table>
            </div>
        </div>
    </div>

    <div class="d-grid gap-2 d-md-flex justify-content-md-end mb-4">
        <button type="submit" class="btn btn-primary btn-lg px-5 shadow-sm">
            <i class="bi bi-check2-circle me-2"></i>Record Bulk Receipts
        </button>
    </div>
</form>

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    function recalc() {
        let total = 0;
        document.querySelectorAll('.bulk-amount').forEach(function(el) {
            total += parseFloat(el.value) || 0;
        });
        document.getElementById('totalAmount').textContent = total > 0 ? total.toLocaleString('en-US', {minimumFractionDigits: 0, maximumFractionDigits: 2}) : '0';
    }
    document.querySelectorAll('.bulk-amount').forEach(function(el) {
        el.addEventListener('input', recalc);
    });

    // Reload page on date change to update opening balances
    const dateInput = document.querySelector('.js-date');
    if (dateInput && dateInput._flatpickr) {
        dateInput._flatpickr.set('onChange', function(selectedDates, dateStr) {
            window.location.href = "{{ route('receipts.bulk') }}?date=" + dateStr;
        });
    }

    recalc();
});
</script>
@endsection
@endsection
