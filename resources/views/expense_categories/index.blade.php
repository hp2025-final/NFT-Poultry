@extends('layouts.app')

@section('title', 'Expense Categories - NF Dev')

@section('content')
<div class="row align-items-center mb-4">
    <div class="col-sm-6">
        <h2 class="mb-0">Expense Categories</h2>
    </div>
    <div class="col-sm-6 text-sm-end mt-3 mt-sm-0">
        <a href="{{ route('expense_categories.create') }}" class="btn btn-primary shadow-sm">
            <i class="bi bi-tag me-1"></i>New Category
        </a>
    </div>
</div>

<div class="card border-0 shadow-sm mb-4">
    <div class="card-body">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
            <form method="GET" action="{{ route('expense_categories.index') }}" class="d-flex gap-2 flex-grow-1" style="max-width: 400px;">
                <input type="text" name="q" class="form-control" value="{{ request('q') }}" placeholder="Search categories...">
                <button type="submit" class="btn btn-primary">Search</button>
            </form>
            <div class="btn-group shadow-sm">
                <a href="{{ route('expense_categories.index', ['show' => 'active']) }}" class="btn btn-outline-primary {{ $show === 'active' ? 'active' : '' }}">Active</a>
                <a href="{{ route('expense_categories.index', ['show' => 'archived']) }}" class="btn btn-outline-primary {{ $show === 'archived' ? 'active' : '' }}">Archived</a>
            </div>
        </div>
    </div>
</div>

<div class="row g-4">
    @forelse($categories as $category)
    <div class="col-md-4 col-xl-3">
        <div class="card border-0 shadow-sm h-100 {{ !$category->is_active ? 'opacity-75' : '' }}">
            <div class="card-body d-flex justify-content-between align-items-center">
                <div>
                    <h6 class="fw-bold mb-0 {{ !$category->is_active ? 'text-decoration-line-through text-muted' : '' }}">{{ $category->name }}</h6>
                </div>
                <div class="dropdown">
                    <button class="btn btn-link p-0 text-muted dropdown-toggle no-caret" type="button" data-bs-toggle="dropdown">
                        <i class="bi bi-three-dots-vertical"></i>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end border-0 shadow-sm">
                        <li><a class="dropdown-item" href="{{ route('expense_categories.edit', $category->id) }}"><i class="bi bi-pencil me-2"></i>Edit</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <form action="{{ route('expense_categories.toggle', $category->id) }}" method="POST">
                                @csrf
                                <button type="submit" class="dropdown-item {{ $category->is_active ? 'text-danger' : 'text-success' }}">
                                    <i class="bi bi-{{ $category->is_active ? 'archive' : 'arrow-counterclockwise' }} me-2"></i>
                                    {{ $category->is_active ? 'Archive' : 'Restore' }}
                                </button>
                            </form>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
    @empty
    <div class="col-12 text-center py-5 text-muted">No expense categories found.</div>
    @endforelse
</div>

<div class="mt-4">
    {{ $categories->withQueryString()->links() }}
</div>
@endsection
