@extends('layouts.app')
@section('title', isset($account) ? 'Edit Account' : 'New Account')

@section('content')
<div class="mb-3">
    <a href="{{ route('accounts.index') }}" class="btn btn-sm btn-secondary">&larr; Back to Accounts</a>
</div>

<div class="card shadow-sm" style="max-width: 600px;">
    <div class="card-header bg-white">
        <h5 class="mb-0">{{ isset($account) ? 'Edit Account' : 'New Account' }}</h5>
    </div>
    <div class="card-body">
        <form method="POST" action="{{ isset($account) ? route('accounts.update', $account->id) : route('accounts.store') }}">
            @csrf
            @if(isset($account)) @method('PUT') @endif
            
            <div class="mb-3">
                <label class="form-label">Account Name <span class="text-danger">*</span></label>
                <input type="text" name="name" class="form-control" value="{{ old('name', $account->name ?? '') }}" required>
            </div>

            <div class="mb-3">
                <label class="form-label">Type <span class="text-danger">*</span></label>
                <select name="type" class="form-select" required>
                    <option value="cash" {{ old('type', $account->type ?? '') == 'cash' ? 'selected' : '' }}>Cash</option>
                    <option value="bank" {{ old('type', $account->type ?? '') == 'bank' ? 'selected' : '' }}>Bank</option>
                </select>
            </div>

            <div class="mb-3">
                <label class="form-label">Opening Balance</label>
                <input type="number" step="0.01" name="opening_balance" class="form-control" value="{{ old('opening_balance', $account->opening_balance ?? 0) }}">
                <div class="form-text">Set the initial money in this account when starting the ledger.</div>
            </div>

            <div class="mb-3 form-check">
                <input type="hidden" name="is_active" value="0">
                <input type="checkbox" class="form-check-input" id="is_active" name="is_active" value="1" {{ old('is_active', $account->is_active ?? true) ? 'checked' : '' }}>
                <label class="form-check-label" for="is_active">Active status</label>
            </div>

            <div class="mt-4">
                <button type="submit" class="btn btn-primary px-4">Save Account</button>
            </div>
        </form>
    </div>
</div>
@endsection
