@extends('layouts.app')
@section('title', isset($product) ? 'Edit Product' : 'New Product')

@section('content')
<div class="mb-3">
    <a href="{{ route('products.index') }}" class="btn btn-sm btn-secondary">&larr; Back to Products</a>
</div>

<div class="card shadow-sm" style="max-width: 800px;">
    <div class="card-header bg-white">
        <h5 class="mb-0">{{ isset($product) ? 'Edit Product: '.$product->name : 'New Product' }}</h5>
    </div>
    <div class="card-body">
        <form method="POST" action="{{ isset($product) ? route('products.update', $product->id) : route('products.store') }}">
            @csrf
            @if(isset($product)) @method('PUT') @endif

            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label">SKU</label>
                    <input type="text" name="sku" class="form-control" value="{{ old('sku', $product->sku ?? '') }}">
                </div>
                <div class="col-md-8">
                    <label class="form-label">Name <span class="text-danger">*</span></label>
                    <input type="text" name="name" class="form-control" value="{{ old('name', $product->name ?? '') }}" required>
                </div>
                
                <div class="col-md-4">
                    <label class="form-label">Unit</label>
                    <input type="text" name="unit" class="form-control" value="{{ old('unit', $product->unit ?? 'KG') }}" placeholder="KG, PCS, etc">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Purchase Price</label>
                    <input type="number" step="0.01" name="purchase_price" class="form-control" value="{{ old('purchase_price', $product->purchase_price ?? 0) }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Sale Price</label>
                    <input type="number" step="0.01" name="sale_price" class="form-control" value="{{ old('sale_price', $product->sale_price ?? 0) }}">
                </div>

                <div class="col-md-4">
                    <label class="form-label">Opening Qty</label>
                    <input type="number" step="0.01" name="opening_qty" class="form-control" value="{{ old('opening_qty', $product->opening_qty ?? 0) }}">
                </div>
            </div>

            <div class="mt-4">
                <button type="submit" class="btn btn-primary">Save Product</button>
            </div>
        </form>
    </div>
</div>
@endsection
