@extends('layouts.app')
@section('title', 'Stock Ledger: ' . $product->name)

@section('content')
<div class="mb-3">
    <a href="{{ route('products.index') }}" class="btn btn-sm btn-secondary">&larr; Back to Products</a>
</div>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h3 mb-0">{{ $product->name }} <span class="badge bg-success fs-6">Stock Ledger</span></h1>
        <div class="text-muted mt-1">SKU: {{ $product->sku ?? 'N/A' }}</div>
    </div>
    <div class="d-flex gap-2">
        <button onclick="window.print()" class="btn btn-outline-secondary"><i class="bi bi-printer"></i> Print</button>
    </div>
</div>

<div class="card shadow-sm mb-4">
    <div class="card-header bg-white">
        <form method="GET" action="{{ route('products.show', $product->id) }}" class="row g-2 align-items-center">
            <div class="col-auto">
                <input type="text" name="start_date" class="form-control js-date" placeholder="Start Date" value="{{ request('start_date') }}">
            </div>
            <div class="col-auto">
                <input type="text" name="end_date" class="form-control js-date" placeholder="End Date" value="{{ request('end_date') }}">
            </div>
            <div class="col-auto">
                <button type="submit" class="btn btn-secondary">Filter Ledger</button>
            </div>
        </form>
    </div>
    <div class="table-responsive">
        <table class="table table-bordered table-hover mb-0">
            <thead class="table-light">
                <tr>
                    <th>Date</th>
                    <th>Type</th>
                    <th>Ref</th>
                    <th>Party / Reason</th>
                    <th class="text-end text-success">Qty IN ({{ $product->unit }})</th>
                    <th class="text-end text-danger">Qty OUT ({{ $product->unit }})</th>
                    <th class="text-end bg-light">Stock Bal.</th>
                </tr>
            </thead>
            <tbody>
                <tr class="table-secondary">
                    <td colspan="4"><strong>Brought Forward</strong></td>
                    <td class="text-end"></td>
                    <td class="text-end"></td>
                    <td class="text-end"><strong>{{ number_format($broughtForward, 2) }}</strong></td>
                </tr>
                @if(count($paginator) > 0)
                    @foreach($paginator as $txn)
                    <tr>
                        <td>{{ \Carbon\Carbon::parse($txn['date'])->format('d-m-y') }}</td>
                        <td>
                            <span class="badge 
                            {{ $txn['type'] == 'Sale' ? 'bg-primary' : '' }}
                            {{ $txn['type'] == 'Purchase' ? 'bg-warning text-dark' : '' }}
                            {{ $txn['type'] == 'Adjustment' ? 'bg-info text-dark' : '' }}
                            ">{{ $txn['type'] }}</span>
                        </td>
                        <td>
                            @if($txn['route'])
                                <a href="{{ $txn['route'] }}">{{ $txn['ref'] }}</a>
                            @else
                                {{ $txn['ref'] }}
                            @endif
                        </td>
                        <td class="text-muted">{{ $txn['metadata'] }}</td>
                        <td class="text-end text-success">{{ $txn['qty_in'] > 0 ? number_format($txn['qty_in'], 2) : '' }}</td>
                        <td class="text-end text-danger">{{ $txn['qty_out'] > 0 ? number_format($txn['qty_out'], 2) : '' }}</td>
                        <td class="text-end bg-light fw-bold">{{ number_format($txn['balance'], 2) }}</td>
                    </tr>
                    @endforeach
                @else
                    <tr>
                        <td colspan="7" class="text-center py-4 text-muted">No stock movement found for the selected period.</td>
                    </tr>
                @endif
                <tr class="table-dark">
                    <td colspan="4" class="text-end"><strong>Closing Stock:</strong></td>
                    <td class="text-end"></td>
                    <td class="text-end"></td>
                    <td class="text-end"><strong>{{ number_format($runningQty, 2) }} {{ $product->unit }}</strong></td>
                </tr>
            </tbody>
        </table>
    </div>
    @if($paginator->hasPages())
    <div class="card-footer bg-white">
        {{ $paginator->links() }}
    </div>
    @endif
</div>
@endsection
