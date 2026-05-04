@extends('layouts.app')

@section('title', 'Balance Sheet Statement - NF Dev')

@section('content')
<div class="row align-items-center mb-4">
    <div class="col-sm-6">
        <h2 class="mb-0 text-primary fw-bold">Balance Sheet Statement</h2>
        <p class="text-muted mb-0 small uppercase">Current Financial Position</p>
    </div>
    <div class="col-sm-6 text-sm-end mt-3 mt-sm-0">
        <button onclick="window.print()" class="btn btn-outline-secondary shadow-sm no-print">
            <i class="bi bi-printer me-1"></i>Print Report
        </button>
    </div>
</div>

<div class="row g-4 overflow-hidden">
    {{-- Left Column: Assets --}}
    <div class="col-lg-6">
        <div class="card border-0 shadow-sm h-100 overflow-hidden">
            <div class="card-header bg-primary text-white py-3 border-0">
                <h5 class="mb-0 fw-bold">ASSETS (Value)</h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <tbody class="border-top-0">
                            {{-- Current Assets --}}
                            <tr class="bg-light table-active">
                                <td class="ps-3 fw-bold py-2" colspan="2">CURRENT ASSETS</td>
                            </tr>
                            @foreach($currentAssets as $name => $balance)
                            <tr>
                                <td class="ps-4 py-3">{{ $name }}</td>
                                <td class="text-end pe-3 py-3 fw-bold">{{ number_format($balance, 2) }}</td>
                            </tr>
                            @endforeach
                            
                            <tr class="fw-bold fs-5 bg-primary bg-opacity-10">
                                <td class="ps-3 py-4">TOTAL ASSETS</td>
                                <td class="text-end pe-3 py-4 text-decoration-underline text-primary">{{ number_format($totalAssets, 2) }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    {{-- Right Column: Liabilities & Equity --}}
    <div class="col-lg-6">
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-danger text-white py-3 border-0">
                <h5 class="mb-0 fw-bold">LIABILITIES (Payables)</h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table mb-0">
                        <tbody class="border-top-0">
                            @foreach($currentLiabilities as $name => $balance)
                            <tr>
                                <td class="ps-4 py-3">{{ $name }}</td>
                                <td class="text-end pe-3 py-3 fw-bold text-danger">{{ number_format($balance, 2) }}</td>
                            </tr>
                            @endforeach
                            <tr class="fw-bold bg-danger bg-opacity-10">
                                <td class="ps-3 py-3">TOTAL LIABILITIES</td>
                                <td class="text-end pe-3 py-3">{{ number_format($totalLiabilities, 2) }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="card border-0 shadow-sm overflow-hidden">
            <div class="card-header bg-success text-white py-3 border-0">
                <h5 class="mb-0 fw-bold">EQUITY (Ownership)</h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <tbody class="border-top-0">
                            @foreach($equity as $name => $balance)
                            <tr>
                                <td class="ps-4 py-3">{{ $name }}</td>
                                <td class="text-end pe-3 py-3 fw-bold">{{ number_format($balance, 2) }}</td>
                            </tr>
                            @endforeach
                            <tr class="fw-bold fs-5 bg-success bg-opacity-10">
                                <td class="ps-3 py-4">TOTAL EQUITY</td>
                                <td class="text-end pe-3 py-4 text-decoration-underline text-success">{{ number_format($totalEquity, 2) }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="mt-4 card border-0 shadow-sm {{ abs($totalAssets - ($totalLiabilities + $totalEquity)) < 0.01 ? 'bg-success text-white' : 'bg-danger text-white' }}">
            <div class="card-body p-4 d-flex justify-content-between align-items-center">
                <h5 class="mb-0 fw-bold">Total Liab. & Equity:</h5>
                <h4 class="mb-0 fw-bold">{{ number_format($totalLiabilities + $totalEquity, 2) }}</h4>
            </div>
        </div>
    </div>
</div>
@endsection
