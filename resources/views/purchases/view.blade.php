@extends('layouts.app')

@section('title', 'Purchase #' . $purchase->id . ' - NF Dev')

@section('content')
<div class="row align-items-center mb-4">
    <div class="col-sm-6">
        <h2 class="mb-0">Purchase #{{ $purchase->id }}</h2>
        <p class="text-muted mb-0">Recorded on {{ \Carbon\Carbon::parse($purchase->date)->format('d-m-y') }}</p>
    </div>
    <div class="col-sm-6 text-sm-end mt-3 mt-sm-0">
        <div class="dropdown d-inline-block">
            <button class="btn btn-primary dropdown-toggle shadow-sm" type="button" data-bs-toggle="dropdown">
                <i class="bi bi-printer me-1"></i>Print Invoice
            </button>
            <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0">
                <li><a class="dropdown-item" href="{{ route('reports.purchase_invoice_pdf', $purchase->id) }}" target="_blank"><i class="bi bi-file-pdf me-2"></i>A4 PDF</a></li>
                <li><a class="dropdown-item" href="{{ route('reports.purchase_invoice_thermal', $purchase->id) }}" target="_blank"><i class="bi bi-printer me-2"></i>Thermal Print</a></li>
            </ul>
        </div>
        <a href="{{ route('purchases.edit', $purchase->id) }}" class="btn btn-outline-secondary ms-2 shadow-sm">
            <i class="bi bi-pencil me-1"></i>Edit
        </a>
    </div>
</div>

<div class="row g-4">
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body">
                <h6 class="text-muted small text-uppercase fw-bold mb-3 opacity-75">Supplier Information</h6>
                <h5 class="fw-bold mb-1 text-primary">{{ $purchase->supplier->name }}</h5>
                <p class="text-muted mb-0"><i class="bi bi-telephone me-1 small"></i>{{ $purchase->supplier->phone ?? 'No phone' }}</p>
                <hr class="bg-light opacity-50">
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted small">Date:</span>
                    <span class="fw-bold small">{{ \Carbon\Carbon::parse($purchase->date)->format('d-m-y') }}</span>
                </div>
                <div class="d-flex justify-content-between">
                    <span class="text-muted small">Total Items:</span>
                    <span class="fw-bold small">{{ $purchase->items->count() }} line(s)</span>
                </div>
            </div>
        </div>

        <div class="card border-0 shadow-sm bg-light">
            <div class="card-body text-center p-4">
                <h6 class="text-muted small text-uppercase fw-bold mb-2 opacity-75">Purchase Amount</h6>
                <h2 class="fw-bold mb-0">{{ number_format($purchase->total_amount, 2) }}</h2>
            </div>
        </div>
    </div>

    <div class="col-lg-8">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white py-3 border-0">
                <h5 class="mb-0 fw-bold">Items Purchased</h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-light text-uppercase small fw-bold">
                            <tr>
                                <th class="ps-3 border-0" style="width: 50px;">#</th>
                                <th class="border-0">Product</th>
                                <th class="text-center border-0">Quantity (KG)</th>
                                <th class="text-end border-0">Unit Cost</th>
                                <th class="text-end pe-3 border-0">Sub-Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($purchase->items as $index => $item)
                            <tr>
                                <td class="ps-3 text-muted">{{ $index + 1 }}</td>
                                <td>
                                    <div class="fw-bold text-dark">{{ $item->product->name }}</div>
                                    <div class="text-muted x-small">SKU: {{ $item->product->sku ?? 'N/A' }}</div>
                                </td>
                                <td class="text-center fw-bold">{{ number_format($item->qty, 2) }}</td>
                                <td class="text-end">{{ number_format($item->price, 2) }}</td>
                                <td class="text-end fw-bold pe-3">{{ number_format($item->amount, 2) }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                        <tfoot class="bg-light">
                            <tr>
                                <td colspan="4" class="text-end fw-bold py-3">Grand Total:</td>
                                <td class="text-end fw-bold pe-3 py-3 text-primary fs-5">{{ number_format($purchase->total_amount, 2) }}</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>

        <div class="mt-4 p-3 bg-light rounded text-muted x-small border border-dark border-opacity-10 d-flex align-items-center">
            <i class="bi bi-info-circle-fill me-2 fs-5 text-primary"></i>
            <div>This purchase has automatically increased the inventory stock for the items listed above.</div>
        </div>
    </div>
</div>
@endsection
