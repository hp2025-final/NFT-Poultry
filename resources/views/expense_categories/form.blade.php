@extends('layouts.app')
@section('title', isset($category) ? 'Edit Category' : 'New Category')

@section('content')
<div class="mb-3">
    <a href="{{ route('expense_categories.index') }}" class="btn btn-sm btn-secondary">&larr; Back to Categories</a>
</div>

<div class="card shadow-sm" style="max-width: 500px;">
    <div class="card-header bg-white">
        <h5 class="mb-0">{{ isset($category) ? 'Edit Category' : 'New Category' }}</h5>
    </div>
    <div class="card-body">
        <form method="POST" action="{{ isset($category) ? route('expense_categories.update', $category->id) : route('expense_categories.store') }}">
            @csrf
            @if(isset($category)) @method('PUT') @endif
            
            <div class="mb-3">
                <label class="form-label">Category Name <span class="text-danger">*</span></label>
                <input type="text" name="name" class="form-control" value="{{ old('name', $category->name ?? '') }}" required>
            </div>

            <div class="mb-3 form-check">
                <input type="hidden" name="is_active" value="0">
                <input type="checkbox" class="form-check-input" id="is_active" name="is_active" value="1" {{ old('is_active', $category->is_active ?? true) ? 'checked' : '' }}>
                <label class="form-check-label" for="is_active">Active status</label>
            </div>

            <div class="mt-4">
                <button type="submit" class="btn btn-primary px-4">Save Category</button>
            </div>
        </form>
    </div>
</div>
@endsection
