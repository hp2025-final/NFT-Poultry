@extends('layouts.app')

@section('title', (isset($payment) ? 'Edit Payment' : 'New Payment') . ' - NF Dev')

@section('content')
<div class="row align-items-center mb-4">
    <div class="col-sm-6">
        <h2 class="mb-0">{{ isset($payment) ? 'Edit Payment #'.$payment->id : 'New Payment' }}</h2>
        <p class="text-muted mb-0">Record a payment made to a supplier.</p>
    </div>
    <div class="col-sm-6 text-sm-end mt-3 mt-sm-0">
        <a href="{{ route('payments.index') }}" class="btn btn-outline-secondary shadow-sm">
            <i class="bi bi-arrow-left me-1"></i>Back to Payments
        </a>
    </div>
</div>

<div class="row g-4 justify-content-center">
    <div class="col-lg-6">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white py-3 border-0">
                <h5 class="mb-0 fw-bold">Payment Information</h5>
            </div>
            <div class="card-body">
                <form action="{{ isset($payment) ? route('payments.update', $payment->id) : route('payments.store') }}" method="POST">
                    @csrf
                    @if(isset($payment)) @method('PUT') @endif

                    <div class="mb-3">
                        <label class="form-label fw-bold small text-uppercase">Payment Date</label>
                        <input type="text" name="date" class="form-control js-date @error('date') is-invalid @enderror" 
                            value="{{ old('date', $payment->date ?? date('Y-m-d')) }}" required>
                        @error('date') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold small text-uppercase">Supplier</label>
                        <select name="supplier_id" class="form-select @error('supplier_id') is-invalid @enderror" required>
                            <option value="">Select Supplier</option>
                            @foreach($suppliers as $supplier)
                                <option value="{{ $supplier->id }}" {{ old('supplier_id', $payment->supplier_id ?? '') == $supplier->id ? 'selected' : '' }}>
                                    {{ $supplier->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('supplier_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold small text-uppercase">Paid From (Account)</label>
                        <select name="account_id" class="form-select @error('account_id') is-invalid @enderror" required>
                            <option value="">Select Account</option>
                            @foreach($accounts as $account)
                                <option value="{{ $account->id }}" {{ old('account_id', $payment->account_id ?? '') == $account->id ? 'selected' : '' }}>
                                    {{ $account->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('account_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold small text-uppercase">Amount Paid</label>
                        <input type="number" name="amount" step="0.01" min="0.01" class="form-control @error('amount') is-invalid @enderror" 
                            value="{{ old('amount', $payment->amount ?? '') }}" required placeholder="0.00">
                        @error('amount') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold small text-uppercase">Note (Optional)</label>
                        <textarea name="note" class="form-control @error('note') is-invalid @enderror" rows="3" placeholder="Enter transaction reference or note...">{{ old('note', $payment->note ?? '') }}</textarea>
                        @error('note') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="d-grid mt-4">
                        <button type="submit" class="btn btn-primary btn-lg shadow-sm">
                            <i class="bi bi-save me-1"></i>{{ isset($payment) ? 'Update' : 'Save' }} Payment
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
