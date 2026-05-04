@extends('layouts.app')
@section('title', isset($supplier) ? 'Edit Supplier' : 'New Supplier')

@section('content')
<div class="mb-3">
    <a href="{{ route('suppliers.index') }}" class="btn btn-sm btn-secondary">&larr; Back to Suppliers</a>
</div>

<div class="card shadow-sm" style="max-width: 600px;">
    <div class="card-header bg-white">
        <h5 class="mb-0">{{ isset($supplier) ? 'Edit Supplier' : 'New Supplier' }}</h5>
    </div>
    <div class="card-body">
        <form method="POST" action="{{ isset($supplier) ? route('suppliers.update', $supplier->id) : route('suppliers.store') }}">
            @csrf
            @if(isset($supplier)) @method('PUT') @endif
            
            <div class="mb-3">
                <label class="form-label">Supplier Name <span class="text-danger">*</span></label>
                <input type="text" name="name" class="form-control" value="{{ old('name', $supplier->name ?? '') }}" required>
            </div>

            <div class="mb-3">
                <label class="form-label">Phone</label>
                <input type="text" name="phone" class="form-control" value="{{ old('phone', $supplier->phone ?? '') }}">
            </div>

            <div class="mb-3">
                <label class="form-label">Opening Balance</label>
                <input type="number" step="0.01" name="opening_balance" class="form-control" value="{{ old('opening_balance', $supplier->opening_balance ?? 0) }}">
                <div class="form-text">Positive = We owe supplier. Negative = Supplier owes us.</div>
            </div>

            <div class="mt-4">
                <button type="submit" class="btn btn-primary px-4">Save Supplier</button>
            </div>
        </form>
    </div>
</div>
@endsection
