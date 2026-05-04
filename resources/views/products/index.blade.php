@extends('layouts.app')

@section('title', 'Products - NF Dev')

@section('content')
<div class="row align-items-center mb-4">
    <div class="col-sm-6">
        <h2 class="mb-0">Inventory Products</h2>
    </div>
    <div class="col-sm-6 text-sm-end mt-3 mt-sm-0">
        <a href="{{ route('products.create') }}" class="btn btn-primary shadow-sm">
            <i class="bi bi-box-seam me-1"></i>Add New Product
        </a>
    </div>
</div>

<div class="card border-0 shadow-sm mb-4">
    <div class="card-body">
        <form method="GET" action="{{ route('products.index') }}" class="row g-3">
            <div class="col-md-10">
                <div class="input-group">
                    <span class="input-group-text bg-white border-end-0"><i class="bi bi-search text-muted"></i></span>
                    <input type="text" name="search" class="form-control border-start-0" value="{{ request('search') }}" placeholder="Search by name, SKU or category...">
                </div>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary w-100 shadow-sm">Search</button>
            </div>
        </form>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-light">
                    <tr>
                        <th class="ps-3">Product Info</th>
                        <th>Purchase Price</th>
                        <th>Sale Price</th>
                        <th class="text-center">Current Stock</th>
                        <th class="text-center">Status</th>
                        <th class="text-center">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($products as $product)
                    <tr>
                        <td class="ps-3">
                            <div class="d-flex align-items-center">
                                <div class="bg-light rounded p-2 me-3">
                                    <i class="bi bi-box fs-5 text-secondary"></i>
                                </div>
                                <div>
                                    <div class="fw-bold">{{ $product->name }}</div>
                                    <div class="text-muted small">SKU: <span class="bg-light rounded px-1">{{ $product->sku ?? 'N/A' }}</span></div>
                                </div>
                            </div>
                        </td>
                        <td>{{ number_format($product->purchase_price, 2) }}</td>
                        <td class="fw-bold text-primary">{{ number_format($product->sale_price, 2) }}</td>
                        <td class="text-center">
                            @php
                                $stockClass = $product->stock_qty <= 5 ? 'text-danger fw-bold' : ($product->stock_qty <= 20 ? 'text-warning' : 'text-dark');
                            @endphp
                            <span class="{{ $stockClass }} fs-5">
                                {{ number_format($product->stock_qty, 2) }}
                            </span>
                            <div class="small text-muted">KG</div>
                        </td>
                        <td class="text-center">
                            @if($product->is_active)
                                <span class="badge bg-success border border-success px-3 py-2 rounded-pill">Active</span>
                            @else
                                <span class="badge bg-danger border border-danger px-3 py-2 rounded-pill">Inactive</span>
                            @endif
                        </td>
                        <td class="text-center">
                            <div class="btn-group gap-1">
                                <a href="{{ route('products.show', $product->id) }}" class="btn btn-sm btn-outline-info rounded-pill" title="View Details">
                                    <i class="bi bi-eye"></i>
                                </a>
                                <a href="{{ route('products.edit', $product->id) }}" class="btn btn-sm btn-outline-primary rounded-pill" title="Edit">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <form action="{{ route('products.toggle', $product->id) }}" method="POST" class="d-inline">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-outline-{{ $product->is_active ? 'warning' : 'success' }} rounded-pill" title="{{ $product->is_active ? 'Deactivate' : 'Activate' }}">
                                        <i class="bi bi-power"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center py-5 text-muted">
                            No products found in inventory.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($products->hasPages())
    <div class="card-footer bg-white border-0 py-3">
        {{ $products->withQueryString()->links() }}
    </div>
    @endif
</div>
@endsection
