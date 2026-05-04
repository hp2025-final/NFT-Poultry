@extends('layouts.app')
@section('title', 'Record Equity Transaction')

@section('content')
<div class="mb-3">
    <a href="{{ route('equity.index') }}" class="btn btn-sm btn-secondary">&larr; Back to Equity Ledger</a>
</div>

<div class="card shadow-sm" style="max-width: 600px;">
    <div class="card-header bg-white">
        <h5 class="mb-0">Record Equity Transaction</h5>
    </div>
    <div class="card-body">
        <form method="POST" action="{{ route('equity.store') }}">
            @csrf
            
            <div class="mb-3">
                <label class="form-label">Date <span class="text-danger">*</span></label>
                <input type="text" name="date" class="form-control js-date" value="{{ old('date', date('Y-m-d')) }}" required>
            </div>

            <div class="mb-3">
                <label class="form-label">Transaction Type <span class="text-danger">*</span></label>
                <select name="type" class="form-select" required>
                    <option value="capital" {{ old('type') == 'capital' ? 'selected' : '' }}>Capital Injection (Money In)</option>
                    <option value="drawing" {{ old('type') == 'drawing' ? 'selected' : '' }}>Drawing (Money Out)</option>
                </select>
            </div>

            <div class="mb-3">
                <label class="form-label">Cash/Bank Account <span class="text-danger">*</span></label>
                <select name="account_id" class="form-select search-select" required>
                    <option value="">-- Select Account --</option>
                    @foreach($accounts as $a)
                        <option value="{{ $a->id }}" {{ old('account_id') == $a->id ? 'selected' : '' }}>{{ $a->name }}</option>
                    @endforeach
                </select>
                <div class="form-text">The account that receives the capital, or issues the drawing.</div>
            </div>

            <div class="mb-3">
                <label class="form-label">Amount <span class="text-danger">*</span></label>
                <input type="number" step="0.01" min="0.01" name="amount" class="form-control" value="{{ old('amount') }}" required>
            </div>

            <div class="mb-3">
                <label class="form-label">Note</label>
                <textarea name="note" class="form-control" rows="2">{{ old('note') }}</textarea>
            </div>

            <div class="mt-4">
                <button type="submit" class="btn btn-primary px-4">Save Transaction</button>
            </div>
        </form>
    </div>
</div>
@endsection
