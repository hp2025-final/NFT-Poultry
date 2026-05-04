@extends('layouts.app')

@section('title', (isset($expense) ? 'Edit Expense' : 'New Expense') . ' - NF Dev')

@section('content')
<div class="row align-items-center mb-4">
    <div class="col-sm-6">
        <h2 class="mb-0">{{ isset($expense) ? 'Edit Expense #'.$expense->id : 'New Expense' }}</h2>
        <p class="text-muted mb-0 small uppercase">Record business spending.</p>
    </div>
    <div class="col-sm-6 text-sm-end mt-3 mt-sm-0">
        <a href="{{ route('expenses.index') }}" class="btn btn-outline-secondary shadow-sm">
            <i class="bi bi-arrow-left me-1"></i>Back to Expenses
        </a>
    </div>
</div>

<div class="row g-4 justify-content-center">
    <div class="col-lg-6">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white py-3 border-0">
                <h5 class="mb-0 fw-bold">Expense Details</h5>
            </div>
            <div class="card-body">
                <form action="{{ isset($expense) ? route('expenses.update', $expense->id) : route('expenses.store') }}" method="POST">
                    @csrf
                    @if(isset($expense)) @method('PUT') @endif

                    <div class="mb-3">
                        <label class="form-label fw-bold small text-uppercase">Expense Date</label>
                        <input type="text" name="date" class="form-control js-date @error('date') is-invalid @enderror" 
                            value="{{ old('date', $expense->date ?? date('Y-m-d')) }}" required>
                        @error('date') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold small text-uppercase">Expense Category</label>
                        <select name="expense_category_id" class="form-select @error('expense_category_id') is-invalid @enderror" required>
                            <option value="">Select Category</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}" {{ old('expense_category_id', $expense->expense_category_id ?? '') == $category->id ? 'selected' : '' }}>
                                    {{ $category->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('expense_category_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold small text-uppercase">Paid From (Account)</label>
                        <select name="account_id" class="form-select @error('account_id') is-invalid @enderror">
                            <option value="">Select Account (Optional)</option>
                            @foreach($accounts as $account)
                                <option value="{{ $account->id }}" {{ old('account_id', $expense->account_id ?? '') == $account->id ? 'selected' : '' }}>
                                    {{ $account->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('account_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold small text-uppercase">Amount</label>
                        <input type="number" name="amount" step="0.01" min="0.01" class="form-control @error('amount') is-invalid @enderror" 
                            value="{{ old('amount', $expense->amount ?? '') }}" required placeholder="0.00">
                        @error('amount') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold small text-uppercase">Note (Optional)</label>
                        <textarea name="note" class="form-control @error('note') is-invalid @enderror" rows="3" placeholder="Enter any details about this expense...">{{ old('note', $expense->note ?? '') }}</textarea>
                        @error('note') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="d-grid mt-4">
                        <button type="submit" class="btn btn-primary btn-lg shadow-sm">
                            <i class="bi bi-save me-1"></i>{{ isset($expense) ? 'Update' : 'Save' }} Expense
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
