@extends('layouts.app')

@section('title', (isset($purchase) ? 'Edit Purchase' : 'New Purchase') . ' - NF Dev')

@section('content')
<div class="row align-items-center mb-4">
    <div class="col-sm-6">
        <h2 class="mb-0">{{ isset($purchase) ? 'Edit Purchase #'.$purchase->id : 'New Purchase' }}</h2>
        <p class="text-muted mb-0">Record items bought from a supplier.</p>
    </div>
    <div class="col-sm-6 text-sm-end mt-3 mt-sm-0">
        <a href="{{ route('purchases.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i>Back to Purchases
        </a>
    </div>
</div>

@php
    $firstItem = isset($purchase) ? $purchase->items->first() : null;
    $defaultProduct = $firstItem ? $firstItem->product : $products->first();
@endphp

<form action="{{ isset($purchase) ? route('purchases.update', $purchase->id) : route('purchases.store') }}" method="POST">
    @csrf
    @if(isset($purchase)) @method('PUT') @endif

    {{-- Hidden single-item fields with index 0 --}}
    <input type="hidden" name="items[0][product_id]" id="hidden_product_id" value="{{ $defaultProduct->id ?? '' }}">

    {{-- ======= Card 1: Purchase Details ======= --}}
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white py-3 border-0">
            <h5 class="mb-0 fw-bold">Purchase Details</h5>
        </div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label fw-bold small text-uppercase">Date</label>
                    <input type="text" name="date" id="purchase_date"
                        class="form-control js-date @error('date') is-invalid @enderror"
                        value="{{ old('date', isset($purchase) ? $purchase->date : date('Y-m-d')) }}" required>
                    @error('date') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-bold small text-uppercase">Supplier</label>
                    <select name="supplier_id" class="form-select @error('supplier_id') is-invalid @enderror" required>
                        <option value="">Select Supplier</option>
                        @foreach($suppliers as $supplier)
                            <option value="{{ $supplier->id }}" {{ old('supplier_id', $purchase->supplier_id ?? '') == $supplier->id ? 'selected' : '' }}>
                                {{ $supplier->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('supplier_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
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
                <div class="col-md-3">
                    <label class="form-label fw-bold small text-uppercase">Purchased Qty</label>
                    <input type="number" name="items[0][qty]" id="purchased_qty" step="0.25" min="0.25"
                        class="form-control @error('items.0.qty') is-invalid @enderror"
                        value="{{ old('items.0.qty', $firstItem->qty ?? '') }}"
                        placeholder="0.00" required>
                    @error('items.0.qty') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-bold small text-uppercase">Finished Qty</label>
                    <input type="number" name="items[0][finished_qty]" id="finished_qty" step="0.01" min="0"
                        class="form-control @error('items.0.finished_qty') is-invalid @enderror"
                        value="{{ old('items.0.finished_qty', $firstItem->finished_qty ?? '') }}"
                        placeholder="Same as Purchased">
                    @error('items.0.finished_qty') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-bold small text-uppercase">Unit Price</label>
                    <input type="number" name="items[0][price]" id="unit_price" step="0.01" min="0"
                        class="form-control @error('items.0.price') is-invalid @enderror"
                        value="{{ old('items.0.price', $firstItem->price ?? ($defaultProduct->purchase_price ?? '')) }}"
                        placeholder="0.00" required>
                    @error('items.0.price') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-bold small text-uppercase">Total</label>
                    <div class="form-control bg-light fw-bold text-end" id="total_display">0.00</div>
                </div>
            </div>
        </div>
    </div>

    {{-- Submit --}}
    <div class="d-grid">
        <button type="submit" class="btn btn-success btn-lg shadow-sm">
            <i class="bi bi-save me-1"></i>{{ isset($purchase) ? 'Update' : 'Save' }} Purchase
        </button>
    </div>
</form>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const qtyInput   = document.getElementById('purchased_qty');
    const priceInput = document.getElementById('unit_price');
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
