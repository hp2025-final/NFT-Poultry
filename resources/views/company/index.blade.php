@extends('layouts.app')
@section('title', 'Company Profile')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h3">Company Profile</h1>
</div>

@if(session('success'))
<div class="alert alert-success">{{ session('success') }}</div>
@endif

<div class="row">
    <div class="col-md-6">
        <div class="card shadow-sm">
            <div class="card-header bg-white">
                <h5 class="mb-0">Edit Profile</h5>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('settings.company.update') }}">
                    @csrf
                    
                    <div class="mb-3">
                        <label class="form-label">Company Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control" value="{{ old('name', $company->name ?? 'Default Company Name') }}" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Phone <span class="text-danger">*</span></label>
                        <input type="text" name="phone" class="form-control" value="{{ old('phone', $company->phone ?? '') }}" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Email <span class="text-danger">*</span></label>
                        <input type="email" name="email" class="form-control" value="{{ old('email', $company->email ?? '') }}" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Address <span class="text-danger">*</span></label>
                        <textarea name="address" class="form-control" rows="3" required>{{ old('address', $company->address ?? '') }}</textarea>
                    </div>

                    <div class="mt-4">
                        <button type="submit" class="btn btn-primary px-4">Update Profile</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <div class="col-md-6">
        <div class="card shadow-sm border-info">
            <div class="card-body bg-light">
                <h5 class="card-title text-info"><i class="bi bi-info-circle"></i> Headers & Footers</h5>
                <p class="card-text text-muted">
                    Your company profile details are automatically applied to the header headers of all printable documents including:
                </p>
                <ul>
                    <li>A4 Sales & Purchase Invoices</li>
                    <li>Account & Supplier Ledgers</li>
                    <li>Statement of Owner's Equity</li>
                    <li>70mm Thermal Receipts</li>
                </ul>
                <p class="mb-0">
                    <small>Changing your address and phone number will immediately affect all newly generated PDFs across the accounting system.</small>
                </p>
            </div>
        </div>
    </div>
</div>
@endsection
