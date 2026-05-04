@extends('layouts.app')
@section('title', isset($customer) ? 'Edit Customer' : 'New Customer')

@section('content')
<div class="mb-3">
    <a href="{{ route('customers.index') }}" class="btn btn-sm btn-secondary">&larr; Back to Customers</a>
</div>

<div class="card shadow-sm" style="max-width: 600px;">
    <div class="card-header bg-white">
        <h5 class="mb-0">{{ isset($customer) ? 'Edit Customer' : 'New Customer' }}</h5>
    </div>
    <div class="card-body">
        <form method="POST" action="{{ isset($customer) ? route('customers.update', $customer->id) : route('customers.store') }}">
            @csrf
            @if(isset($customer)) @method('PUT') @endif
            
            <div class="mb-3">
                <label class="form-label">Customer Name <span class="text-danger">*</span></label>
                <input type="text" name="name" class="form-control" value="{{ old('name', $customer->name ?? '') }}" required>
            </div>

            <div class="mb-3">
                <label class="form-label">Phone</label>
                <input type="text" name="phone" class="form-control" value="{{ old('phone', $customer->phone ?? '') }}">
            </div>

            <div class="mb-3">
                <label class="form-label">Opening Balance</label>
                <input type="number" step="0.01" name="opening_balance" class="form-control" value="{{ old('opening_balance', $customer->opening_balance ?? 0) }}">
                <div class="form-text">Positive = Customer owes us. Negative = We owe customer.</div>
            </div>

            <div class="mt-4">
                <button type="submit" class="btn btn-primary px-4">Save Customer</button>
            </div>
        </form>
    </div>
</div>
@endsection
