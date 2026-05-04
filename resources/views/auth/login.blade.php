@extends('layouts.app')

@section('title', 'Login - NF Dev')

@section('content')
<div class="row justify-content-center mt-5">
    <div class="col-11 col-sm-8 col-md-5 col-lg-4">
        <div class="text-center mb-4">
            <div style="font-size:3rem; color:var(--accent-primary);"><i class="bi bi-box-seam"></i></div>
            <h2 class="fw-bold mt-2" style="color:#0f172a;">NF Dev</h2>
            <p class="text-muted">Sign in to your accounting dashboard</p>
        </div>
        <div class="card" style="border-top: 3px solid var(--accent-primary);">
            <div class="card-body p-4">
                <form method="POST" action="{{ route('login.post') }}">
                    @csrf
                    <div class="mb-3">
                        <label for="username" class="form-label"><i class="bi bi-person me-1"></i>Username</label>
                        <input type="text" class="form-control" id="username" name="username" value="{{ old('username') }}" required autofocus placeholder="Enter your username">
                    </div>
                    <div class="mb-3">
                        <label for="password" class="form-label"><i class="bi bi-lock me-1"></i>Password</label>
                        <input type="password" class="form-control" id="password" name="password" required placeholder="Enter your password">
                    </div>
                    @if(session('error'))
                        <div class="alert alert-danger py-2"><i class="bi bi-exclamation-triangle me-1"></i>{{ session('error') }}</div>
                    @endif
                    <button type="submit" class="btn btn-primary w-100 mt-2"><i class="bi bi-box-arrow-in-right me-1"></i>Sign In</button>
                </form>
            </div>
        </div>
        <div class="text-center mt-3 text-muted" style="font-size:.75rem">
            &copy; {{ date('Y') }} NF Dev Accounting System
        </div>
    </div>
</div>
@endsection
