@extends('layouts.app')

@section('title', 'Bulk Customer Sale - NF Dev')

@section('content')
<div class="row align-items-center mb-4">
    <div class="col-sm-6">
        <h2 class="mb-0">Bulk Customer Sale</h2>
        <p class="text-muted mb-0">Record sales for all active customers for a single product.</p>
    </div>
    <div class="col-sm-6 text-sm-end mt-3 mt-sm-0">
        <a href="{{ route('sales.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i>Back to Sales
        </a>
    </div>
</div>

<form action="{{ route('sales.storeBulk') }}" method="POST" onsubmit="return confirm('Are you sure you want to record these bulk sales?');">
    @csrf

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label fw-bold small text-uppercase">Sale Date</label>
                    <input type="text" name="date" class="form-control js-date @error('date') is-invalid @enderror" value="{{ old('date', date('Y-m-d')) }}" required>
                    @error('date') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-bold small text-uppercase">Product</label>
                    <select name="product_id" class="form-select @error('product_id') is-invalid @enderror" required>
                        @if($product)
                            <option value="{{ $product->id }}">{{ $product->name }} (Price: {{ number_format($product->sale_price, 2) }})</option>
                        @else
                            <option value="">No active products found</option>
                        @endif
                    </select>
                    @error('product_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" id="bulkSaleTable">
                    <thead class="bg-light">
                        <tr>
                            <th class="ps-3" style="width: 50px;">#</th>
                            <th>Customer Name</th>
                            <th style="width: 160px;">Quantity (KG)</th>
                            <th style="width: 160px;">Rate (Price)</th>
                            <th class="text-end pe-3" style="width: 140px;">Amount</th>
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
                            <td>
                                <input type="number" name="customers[{{ $index }}][qty]" step="0.25" min="0" 
                                    class="form-control form-control-sm bulk-qty @error("customers.$index.qty") is-invalid @enderror" 
                                    value="{{ old("customers.$index.qty") }}" placeholder="0.25 steps"
                                    data-row="{{ $index }}">
                            </td>
                            <td>
                                <input type="number" name="customers[{{ $index }}][rate]" step="0.01" min="0" 
                                    class="form-control form-control-sm bulk-rate @error("customers.$index.rate") is-invalid @enderror" 
                                    value="{{ old("customers.$index.rate") }}" placeholder="Default: {{ $product->sale_price ?? 0 }}"
                                    data-row="{{ $index }}" data-default="{{ $product->sale_price ?? 0 }}">
                            </td>
                            <td class="text-end pe-3 fw-bold">
                                <span class="row-amount" id="amount-{{ $index }}">0</span>
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
                            <td class="ps-3" colspan="2">Totals</td>
                            <td class="text-center" id="totalQty">0</td>
                            <td></td>
                            <td class="text-end pe-3" id="totalAmount">0</td>
                        </tr>
                    </tfoot>
                    @endif
                </table>
            </div>
        </div>
    </div>

    <div class="d-grid gap-2 d-md-flex justify-content-md-end mb-4">
        <button type="submit" class="btn btn-primary btn-lg px-5 shadow-sm">
            <i class="bi bi-check2-circle me-2"></i>Record Bulk Sales
        </button>
    </div>
</form>

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    function recalc() {
        let totalQty = 0, totalAmount = 0;
        document.querySelectorAll('.bulk-qty').forEach(function(el) {
            const row = el.dataset.row;
            const qty = parseFloat(el.value) || 0;
            const rateEl = document.querySelector('.bulk-rate[data-row="' + row + '"]');
            const rate = parseFloat(rateEl.value) || parseFloat(rateEl.dataset.default) || 0;
            const amount = qty * rate;
            document.getElementById('amount-' + row).textContent = amount > 0 ? number_format(amount) : '0';
            totalQty += qty;
            totalAmount += amount;
        });
        document.getElementById('totalQty').textContent = totalQty > 0 ? number_format(totalQty) : '0';
        document.getElementById('totalAmount').textContent = totalAmount > 0 ? number_format(totalAmount) : '0';
    }
    function number_format(n) {
        return n.toLocaleString('en-US', {minimumFractionDigits: 0, maximumFractionDigits: 2});
    }
    document.querySelectorAll('.bulk-qty, .bulk-rate').forEach(function(el) {
        el.addEventListener('input', recalc);
    });
    recalc();
});
</script>
@endsection
@endsection
