@extends('layouts.app')

@section('title', 'Profit & Loss Statement - NF Dev')

@section('content')
<div class="row align-items-center mb-4">
    <div class="col-sm-6">
        <h2 class="mb-0 text-primary fw-bold">Profit & Loss Statement</h2>
        <p class="text-muted mb-0 small uppercase">All-time Financial Performance</p>
    </div>
    <div class="col-sm-6 text-sm-end mt-3 mt-sm-0">
        <button onclick="window.print()" class="btn btn-outline-secondary shadow-sm no-print">
            <i class="bi bi-printer me-1"></i>Print Report
        </button>
    </div>
</div>

<div class="row g-4">
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm overflow-hidden">
            <div class="card-header bg-white py-3 border-0">
                <h5 class="mb-0 fw-bold">Operating Results</h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <tbody class="border-top-0">
                            {{-- Income Section --}}
                            <tr class="bg-light table-active">
                                <td class="ps-3 fw-bold py-2" colspan="2">INCOME</td>
                            </tr>
                            <tr>
                                <td class="ps-4 py-2">Total Sales Revenue</td>
                                <td class="text-end pe-3 fw-bold">{{ number_format($totalSales, 2) }}</td>
                            </tr>
                            
                            {{-- COGS Section --}}
                            <tr class="bg-light table-active">
                                <td class="ps-3 fw-bold py-2" colspan="2">COST OF GOODS SOLD (COGS)</td>
                            </tr>
                            <tr>
                                <td class="ps-4 py-2">Opening Inventory Value</td>
                                <td class="text-end pe-3">{{ number_format($openingInventory, 2) }}</td>
                            </tr>
                            <tr>
                                <td class="ps-4 py-2">Add: Total Purchases</td>
                                <td class="text-end pe-3 border-bottom">{{ number_format($purchases, 2) }}</td>
                            </tr>
                            <tr>
                                <td class="ps-4 py-2">Less: Closing Inventory Value</td>
                                <td class="text-end pe-3 text-secondary italic">({{ number_format($closingInventory, 2) }})</td>
                            </tr>
                            <tr class="fw-bold bg-light-subtle">
                                <td class="ps-3 py-2 text-uppercase">Total Cost of Goods Sold</td>
                                <td class="text-end pe-3 border-top">{{ number_format($cogs, 2) }}</td>
                            </tr>

                            <tr class="fw-bold fs-5 text-primary border-top border-bottom">
                                <td class="ps-3 py-3">GROSS PROFIT</td>
                                <td class="text-end pe-3 py-3">{{ number_format($grossProfit, 2) }}</td>
                            </tr>

                            {{-- Expenses Section --}}
                            <tr class="bg-light table-active">
                                <td class="ps-3 fw-bold py-2" colspan="2">OPERATING EXPENSES</td>
                            </tr>
                            @foreach($expensesByCategory as $category => $amount)
                            <tr>
                                <td class="ps-4 py-2 text-capitalize">{{ $category }}</td>
                                <td class="text-end pe-3">{{ number_format($amount, 2) }}</td>
                            </tr>
                            @endforeach
                            <tr class="fw-bold bg-light-subtle">
                                <td class="ps-3 py-2 text-uppercase">Total Operating Expenses</td>
                                <td class="text-end pe-3 border-top text-danger">({{ number_format($totalExpenses, 2) }})</td>
                            </tr>

                            <tr class="fw-bold fs-4 {{ $netProfit >= 0 ? 'text-success' : 'text-danger' }} bg-light">
                                <td class="ps-3 py-4">NET PROFIT / LOSS</td>
                                <td class="text-end pe-3 py-4 text-decoration-underline">{{ number_format($netProfit, 2) }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm bg-primary text-white mb-4">
            <div class="card-body p-4 text-center">
                <div class="opacity-75 small text-uppercase mb-2">Total Sales</div>
                <h2 class="fw-bold mb-0">{{ number_format($totalSales, 2) }}</h2>
            </div>
        </div>
        
        <div class="card border-0 shadow-sm bg-success text-white mb-4">
            <div class="card-body p-4 text-center">
                <div class="opacity-75 small text-uppercase mb-2 text-white">Gross Profit Margin</div>
                <h2 class="fw-bold mb-0">
                    {{ $totalSales > 0 ? number_format(($grossProfit / $totalSales) * 100, 1) : '0' }}%
                </h2>
            </div>
        </div>

        <div class="card border-0 shadow-sm border-start border-4 border-info">
            <div class="card-body">
                <h6 class="fw-bold text-uppercase small text-muted mb-3 opacity-75">Inventory Status</h6>
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted">Opening:</span>
                    <span class="fw-bold">{{ number_format($openingInventory, 2) }}</span>
                </div>
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted">Closing:</span>
                    <span class="fw-bold">{{ number_format($closingInventory, 2) }}</span>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
