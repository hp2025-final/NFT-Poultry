@extends('layouts.app')

@section('title', 'Accounts - NF Dev')

@section('content')
<div class="row align-items-center mb-4">
    <div class="col-sm-6">
        <h2 class="mb-0">Financial Accounts</h2>
        <p class="text-muted mb-0">Manage your cash and bank accounts.</p>
    </div>
    <div class="col-sm-6 text-sm-end mt-3 mt-sm-0">
        <a href="{{ route('accounts.create') }}" class="btn btn-primary shadow-sm">
            <i class="bi bi-bank me-1"></i>New Account
        </a>
    </div>
</div>

<div class="card border-0 shadow-sm mb-4">
    <div class="card-body">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
            <form method="GET" action="{{ route('accounts.index') }}" class="d-flex gap-2 flex-grow-1" style="max-width: 500px;">
                <div class="input-group">
                    <span class="input-group-text bg-white border-end-0"><i class="bi bi-search text-muted"></i></span>
                    <input type="text" name="q" class="form-control border-start-0" value="{{ request('q') }}" placeholder="Search accounts...">
                </div>
                <button type="submit" class="btn btn-primary px-4">Search</button>
            </form>

            <div class="btn-group shadow-sm" role="group">
                <a href="{{ route('accounts.index', ['show' => 'active', 'q' => request('q')]) }}" 
                   class="btn btn-outline-primary {{ $show === 'active' ? 'active' : '' }}">Active</a>
                <a href="{{ route('accounts.index', ['show' => 'archived', 'q' => request('q')]) }}" 
                   class="btn btn-outline-primary {{ $show === 'archived' ? 'active' : '' }}">Archived</a>
                <a href="{{ route('accounts.index', ['show' => 'all', 'q' => request('q')]) }}" 
                   class="btn btn-outline-primary {{ $show === 'all' ? 'active' : '' }}">All</a>
            </div>
        </div>
    </div>
</div>

<div class="row g-4">
    @forelse($accounts as $account)
    <div class="col-md-6 col-xl-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <div class="bg-primary bg-opacity-10 text-primary rounded p-3">
                        <i class="bi bi-{{ $account->type == 'bank' ? 'building-columns' : 'cash-stack' }} fs-4"></i>
                    </div>
                    <div class="dropdown">
                        <button class="btn btn-link text-decoration-none dropdown-toggle p-0" type="button" data-bs-toggle="dropdown">
                            <i class="bi bi-three-dots-vertical fs-5 text-muted"></i>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end border-0 shadow-sm">
                            <li><a class="dropdown-item" href="{{ route('accounts.show', $account->id) }}"><i class="bi bi-journal-text me-2"></i>Statement</a></li>
                            <li><a class="dropdown-item" href="{{ route('accounts.edit', $account->id) }}"><i class="bi bi-pencil me-2"></i>Edit Account</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <form action="{{ route('accounts.toggle', $account->id) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="dropdown-item {{ $account->is_active ? 'text-danger' : 'text-success' }}">
                                        <i class="bi bi-{{ $account->is_active ? 'archive' : 'arrow-counterclockwise' }} me-2"></i>
                                        {{ $account->is_active ? 'Archive' : 'Restore' }}
                                    </button>
                                </form>
                            </li>
                        </ul>
                    </div>
                </div>
                
                <h5 class="fw-bold mb-1">{{ $account->name }}</h5>
                <p class="text-muted small text-uppercase mb-3">{{ $account->type }} Account</p>
                
                <div class="p-3 bg-light rounded">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <span class="text-muted small text-uppercase fw-bold">Opening Balance</span>
                        <span class="fw-bold">{{ number_format($account->opening_balance, 2) }}</span>
                    </div>
                    {{-- Current Balance would need calculation if not in model, for now showing opening --}}
                </div>
            </div>
            <div class="card-footer bg-white border-0 pb-3">
                <a href="{{ route('accounts.show', $account->id) }}" class="btn btn-sm btn-outline-primary w-100 rounded-pill">
                    View Transactions <i class="bi bi-arrow-right ms-1"></i>
                </a>
            </div>
        </div>
    </div>
    @empty
    <div class="col-12 text-center py-5 text-muted">
        <i class="bi bi-bank fs-1 d-block mb-3 opacity-25"></i>
        No {{ $show }} accounts found.
    </div>
    @endforelse
</div>

<div class="mt-4">
    {{ $accounts->withQueryString()->links() }}
</div>
@endsection
