@extends('layouts.app')

@section('title', (isset($receipt) ? 'Edit Receipt' : 'New Receipt') . ' - NF Dev')

@section('content')
<div class="row align-items-center mb-4">
    <div class="col-sm-6">
        <h2 class="mb-0">{{ isset($receipt) ? 'Edit Receipt #'.$receipt->id : 'New Receipt' }}</h2>
        <p class="text-muted mb-0">Record a payment received from a customer.</p>
    </div>
    <div class="col-sm-6 text-sm-end mt-3 mt-sm-0">
        <a href="{{ route('receipts.index') }}" class="btn btn-outline-secondary shadow-sm">
            <i class="bi bi-arrow-left me-1"></i>Back to Receipts
        </a>
    </div>
</div>

<div class="row g-4 justify-content-center">
    <div class="col-lg-6">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white py-3 border-0">
                <h5 class="mb-0 fw-bold">Receipt Information</h5>
            </div>
            <div class="card-body">
                <form action="{{ isset($receipt) ? route('receipts.update', $receipt->id) : route('receipts.store') }}" method="POST">
                    @csrf
                    @if(isset($receipt)) @method('PUT') @endif

                    <div class="mb-3">
                        <label class="form-label fw-bold small text-uppercase">Receipt Date</label>
                        <input type="text" name="date" class="form-control js-date @error('date') is-invalid @enderror" 
                            value="{{ old('date', $receipt->date ?? date('Y-m-d')) }}" required>
                        @error('date') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold small text-uppercase">Customer</label>
                        <select name="customer_id" class="form-select @error('customer_id') is-invalid @enderror" required>
                            <option value="">Select Customer</option>
                            @foreach($customers as $customer)
                                <option value="{{ $customer->id }}" {{ old('customer_id', $receipt->customer_id ?? '') == $customer->id ? 'selected' : '' }}>
                                    {{ $customer->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('customer_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold small text-uppercase">Deposit Account</label>
                        <select name="account_id" class="form-select @error('account_id') is-invalid @enderror" required>
                            <option value="">Select Account</option>
                            @foreach($accounts as $account)
                                <option value="{{ $account->id }}" {{ old('account_id', $receipt->account_id ?? '') == $account->id ? 'selected' : '' }}>
                                    {{ $account->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('account_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold small text-uppercase">Amount Received</label>
                        <input type="number" name="amount" step="0.01" min="0.01" class="form-control @error('amount') is-invalid @enderror" 
                            value="{{ old('amount', $receipt->amount ?? '') }}" required placeholder="0.00">
                        @error('amount') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold small text-uppercase">Note (Optional)</label>
                        <textarea name="note" class="form-control @error('note') is-invalid @enderror" rows="3" placeholder="Enter any specific details...">{{ old('note', $receipt->note ?? '') }}</textarea>
                        @error('note') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="d-grid mt-4">
                        <button type="submit" class="btn btn-primary btn-lg shadow-sm">
                            <i class="bi bi-save me-1"></i>{{ isset($receipt) ? 'Update' : 'Save' }} Receipt
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
