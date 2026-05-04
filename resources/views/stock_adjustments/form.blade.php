@extends('layouts.app')

@section('title', (isset($stockAdjustment) ? 'Edit Stock Adjustment' : 'New Stock Adjustment') . ' - NF Dev')

@section('content')
<div class="row align-items-center mb-4">
    <div class="col-sm-6">
        <h2 class="mb-0">{{ isset($stockAdjustment) ? 'Edit Adjustment' : 'New Adjustment' }}</h2>
        <p class="text-muted mb-0">Manually adjust item stock levels.</p>
    </div>
    <div class="col-sm-6 text-sm-end mt-3 mt-sm-0">
        <a href="{{ route('stock_adjustments.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i>Back to Adjustments
        </a>
    </div>
</div>

<div class="row g-4 justify-content-center">
    <div class="col-lg-6">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white py-3 border-0">
                <h5 class="mb-0 fw-bold">Adjustment Information</h5>
            </div>
            <div class="card-body">
                <form action="{{ isset($stockAdjustment) ? route('stock_adjustments.update', $stockAdjustment->id) : route('stock_adjustments.store') }}" method="POST">
                    @csrf
                    @if(isset($stockAdjustment)) @method('PUT') @endif

                    <div class="mb-3">
                        <label class="form-label fw-bold small text-uppercase">Adjustment Date</label>
                        <input type="text" name="date" class="form-control js-date @error('date') is-invalid @enderror" 
                            value="{{ old('date', $stockAdjustment->date ?? date('Y-m-d')) }}" required>
                        @error('date') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold small text-uppercase">Product</label>
                        <select name="product_id" id="product_id" class="form-select @error('product_id') is-invalid @enderror" required>
                            <option value="">Select Product</option>
                            @foreach($products as $product)
                                <option value="{{ $product->id }}" {{ old('product_id', $stockAdjustment->product_id ?? '') == $product->id ? 'selected' : '' }}>
                                    {{ $product->name }} (Available: {{ number_format($product->stock_qty, 2) }})
                                </option>
                            @endforeach
                        </select>
                        @error('product_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold small text-uppercase">Adjustment Type</label>
                        <div class="d-flex gap-4 p-2 bg-light rounded">
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="type" id="type_increase" value="increase" 
                                    {{ old('type', $stockAdjustment->type ?? 'increase') === 'increase' ? 'checked' : '' }} required>
                                <label class="form-check-label text-success fw-bold" for="type_increase">
                                    <i class="bi bi-plus-circle-fill me-1"></i>Increase Stock (+)
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="type" id="type_decrease" value="decrease" 
                                    {{ old('type', $stockAdjustment->type ?? '') === 'decrease' ? 'checked' : '' }} required>
                                <label class="form-check-label text-danger fw-bold" for="type_decrease">
                                    <i class="bi bi-dash-circle-fill me-1"></i>Decrease Stock (-)
                                </label>
                            </div>
                        </div>
                        @error('type') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                    </div>

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold small text-uppercase">Quantity (KG)</label>
                            <input type="number" name="qty" step="0.01" min="0.01" class="form-control @error('qty') is-invalid @enderror" 
                                value="{{ old('qty', $stockAdjustment->qty ?? '') }}" required placeholder="0.00">
                            @error('qty') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold small text-uppercase">Unit Cost (Optional)</label>
                            <input type="number" name="unit_cost" step="0.01" min="0" class="form-control @error('unit_cost') is-invalid @enderror" 
                                value="{{ old('unit_cost', $stockAdjustment->unit_cost ?? '') }}" placeholder="0.00">
                            <small class="text-muted fs-x-small">Defaults to product purchase price</small>
                            @error('unit_cost') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>

                    <div class="mb-3 mt-3">
                        <label class="form-label fw-bold small text-uppercase">Note / Reason</label>
                        <textarea name="note" class="form-control @error('note') is-invalid @enderror" rows="3" placeholder="Enter reason for adjustment...">{{ old('note', $stockAdjustment->note ?? '') }}</textarea>
                        @error('note') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="d-grid mt-4">
                        <button type="submit" class="btn btn-primary btn-lg shadow-sm">
                            <i class="bi bi-save me-1"></i>{{ isset($stockAdjustment) ? 'Update' : 'Save' }} Adjustment
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
