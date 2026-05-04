@extends('layouts.app')

@section('title', 'Sale #'.$sale->id.' - NF Dev')

@section('content')
<div class="row align-items-center mb-4">
    <div class="col-sm-6">
        <h2 class="mb-0">Sale #{{ $sale->id }}</h2>
        <p class="text-muted mb-0">Recorded on {{ \Carbon\Carbon::parse($sale->date)->format('d-m-y') }}</p>
    </div>
    <div class="col-sm-6 text-sm-end mt-3 mt-sm-0">
        <div class="dropdown d-inline-block">
            <button class="btn btn-primary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                <i class="bi bi-printer me-1"></i>Print Invoice
            </button>
            <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0">
                <li><a class="dropdown-item" href="{{ route('reports.sale_invoice_pdf', $sale->id) }}" target="_blank"><i class="bi bi-file-pdf me-2"></i>A4 PDF</a></li>
                <li><a class="dropdown-item" href="{{ route('reports.sale_invoice_thermal', $sale->id) }}" target="_blank"><i class="bi bi-printer me-2"></i>Thermal Print</a></li>
            </ul>
        </div>
        <a href="{{ route('sales.edit', $sale->id) }}" class="btn btn-outline-secondary ms-2">
            <i class="bi bi-pencil me-1"></i>Edit
        </a>
    </div>
</div>

<div class="row g-4">
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body">
                <h6 class="text-muted small text-uppercase fw-bold mb-3">Customer Information</h6>
                <h5 class="fw-bold mb-1">{{ $sale->customer->name }}</h5>
                <p class="text-muted mb-0">{{ $sale->customer->phone ?? 'No phone' }}</p>
                <hr>
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted">Transaction Date:</span>
                    <span class="fw-bold">{{ \Carbon\Carbon::parse($sale->date)->format('d-m-y') }}</span>
                </div>
                <div class="d-flex justify-content-between">
                    <span class="text-muted">Invoice Status:</span>
                    <span class="badge bg-success-subtle text-success">Completed</span>
                </div>
            </div>
        </div>

        <div class="card border-0 shadow-sm">
            <div class="card-body text-center p-4">
                <h3 class="text-muted small text-uppercase fw-bold mb-2">Total Amount</h3>
                <h2 class="fw-bold mb-0 text-primary">{{ number_format($sale->total_amount, 2) }}</h2>
            </div>
        </div>
    </div>

    <div class="col-lg-8">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white py-3 border-0">
                <h5 class="mb-0 fw-bold">Invoice Items</h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-light text-uppercase small fw-bold">
                            <tr>
                                <th class="ps-3">#</th>
                                <th>Product Name</th>
                                <th class="text-center">Quantity (KG)</th>
                                <th class="text-end">Unit Price</th>
                                <th class="text-end pe-3">Sub-Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($sale->items as $index => $item)
                            <tr>
                                <td class="ps-3 text-muted">{{ $index + 1 }}</td>
                                <td>{{ $item->product->name }}</td>
                                <td class="text-center">{{ number_format($item->qty, 2) }}</td>
                                <td class="text-end">{{ number_format($item->price, 2) }}</td>
                                <td class="text-end fw-bold pe-3">{{ number_format($item->amount, 2) }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                        <tfoot class="bg-light">
                            <tr>
                                <td colspan="4" class="text-end fw-bold py-3">Grand Total:</td>
                                <td class="text-end fw-bold pe-3 py-3 text-primary fs-5">{{ number_format($sale->total_amount, 2) }}</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>

        <div class="mt-4 p-3 bg-light rounded small text-muted">
            <i class="bi bi-info-circle me-1"></i>This transaction was recorded in the system and has adjusted the inventory stock automatically.
        </div>
    </div>
</div>
@endsection
