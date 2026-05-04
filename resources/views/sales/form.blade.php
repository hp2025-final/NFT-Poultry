@extends('layouts.app')

@section('title', (isset($sale) ? 'Edit Sale' : 'New Sale') . ' - NF Dev')

@section('content')
<div class="row align-items-center mb-4">
    <div class="col-sm-6">
        <h2 class="mb-0">{{ isset($sale) ? 'Edit Sale #'.$sale->id : 'New Sale' }}</h2>
        <p class="text-muted mb-0">Record a new sales transaction for a customer.</p>
    </div>
    <div class="col-sm-6 text-sm-end mt-3 mt-sm-0">
        <a href="{{ route('sales.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i>Back to Sales
        </a>
    </div>
</div>

@php
    $firstItem   = isset($sale) ? $sale->items->first() : null;
    $defaultProduct = $firstItem ? $firstItem->product : $products->first();
@endphp

<form action="{{ isset($sale) ? route('sales.update', $sale->id) : route('sales.store') }}" method="POST">
    @csrf
    @if(isset($sale)) @method('PUT') @endif

    {{-- Hidden single-item fields --}}
    <input type="hidden" name="items[0][product_id]" id="hidden_product_id" value="{{ $defaultProduct->id ?? '' }}">

    {{-- ======= Card 1: Sale Details ======= --}}
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white py-3 border-0">
            <h5 class="mb-0 fw-bold">Sale Details</h5>
        </div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label fw-bold small text-uppercase">Date</label>
                    <input type="text" name="date" id="sale_date"
                        class="form-control js-date @error('date') is-invalid @enderror"
                        value="{{ old('date', isset($sale) ? $sale->date : date('Y-m-d')) }}" required>
                    @error('date') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-bold small text-uppercase">Customer</label>
                    <select name="customer_id" class="form-select @error('customer_id') is-invalid @enderror" required>
                        <option value="">Select Customer</option>
                        @foreach($customers as $customer)
                            <option value="{{ $customer->id }}" {{ old('customer_id', $sale->customer_id ?? '') == $customer->id ? 'selected' : '' }}>
                                {{ $customer->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('customer_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-bold small text-uppercase">Product</label>
                    <input type="text" class="form-control bg-light" value="{{ $defaultProduct->name ?? 'N/A' }}" readonly>
                </div>
            </div>
        </div>
    </div>

    {{-- ======= Card 2: Quantity & Pricing ======= --}}
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white py-3 border-0">
            <h5 class="mb-0 fw-bold">Quantity &amp; Pricing</h5>
        </div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label fw-bold small text-uppercase">Qty (KG)</label>
                    <input type="number" name="items[0][qty]" id="sale_qty" step="0.25" min="0.25"
                        class="form-control @error('items.0.qty') is-invalid @enderror"
                        value="{{ old('items.0.qty', $firstItem->qty ?? '') }}"
                        placeholder="0.00" required>
                    @error('items.0.qty') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-bold small text-uppercase">Unit Price</label>
                    <input type="number" name="items[0][price]" id="sale_price" step="0.01" min="0"
                        class="form-control @error('items.0.price') is-invalid @enderror"
                        value="{{ old('items.0.price', $firstItem->price ?? ($defaultProduct->sale_price ?? '')) }}"
                        placeholder="0.00" required>
                    @error('items.0.price') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-bold small text-uppercase">Total</label>
                    <div class="form-control bg-light fw-bold text-end" id="total_display">0.00</div>
                </div>
            </div>
        </div>
    </div>

    {{-- Submit --}}
    <div class="d-grid">
        <button type="submit" class="btn btn-success btn-lg shadow-sm">
            <i class="bi bi-save me-1"></i>{{ isset($sale) ? 'Update' : 'Save' }} Transaction
        </button>
    </div>
</form>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const qtyInput   = document.getElementById('sale_qty');
    const priceInput = document.getElementById('sale_price');
    const totalEl    = document.getElementById('total_display');

    function updateTotal() {
        const qty   = parseFloat(qtyInput.value)   || 0;
        const price = parseFloat(priceInput.value)  || 0;
        totalEl.textContent = (qty * price).toLocaleString('en-US', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        });
    }

    qtyInput.addEventListener('input', updateTotal);
    priceInput.addEventListener('input', updateTotal);

    // Initialise on load (for edit form)
    updateTotal();
});
</script>
@endsection
