@extends('layouts.app')
@section('title', 'Trial Balance')

@section('content')
<div class="page-header">
    <h1><i class="bi bi-journal-text me-2"></i>Trial Balance</h1>
    <button onclick="window.print()" class="btn btn-outline-secondary btn-sm"><i class="bi bi-printer me-1"></i>Print</button>
</div>

<div class="card">
    <div class="table-wrapper">

        <table class="table table-bordered table-hover mb-0">
            <thead class="table-light">
                <tr>
                    <th>Account / Ledger Name</th>
                    <th class="text-end" style="width: 20%">Debit</th>
                    <th class="text-end" style="width: 20%">Credit</th>
                </tr>
            </thead>
            <tbody>
                <!-- Standard Accounts -->
                <tr><td><strong>Sales Revenue</strong></td><td></td><td class="text-end">{{ number_format($totalSales, 0) }}</td></tr>
                <tr><td><strong>Purchases (COGS basis)</strong></td><td class="text-end">{{ number_format($totalPurchases, 0) }}</td><td></td></tr>
                <tr><td><strong>Operating Expenses</strong></td><td class="text-end">{{ number_format($totalExpenses, 0) }}</td><td></td></tr>
                <tr><td><strong>Capital Injected</strong></td><td></td><td class="text-end">{{ number_format($totalCapital, 0) }}</td></tr>
                <tr><td><strong>Drawings (Owner Withdrawals)</strong></td><td class="text-end">{{ number_format($totalDrawings, 0) }}</td><td></td></tr>
                
                <tr><td colspan="3" class="bg-light"><strong>Cash & Bank Accounts</strong></td></tr>
                @foreach($assetAccounts as $acc)
                    <tr><td>{{ $acc['name'] }}</td><td class="text-end">{{ number_format($acc['balance'], 0) }}</td><td></td></tr>
                @endforeach
                @foreach($liabAccounts as $acc)
                    <tr><td>{{ $acc['name'] }} (Overdraft)</td><td></td><td class="text-end">{{ number_format(abs($acc['balance']), 0) }}</td></tr>
                @endforeach

                <tr><td colspan="3" class="bg-light"><strong>Customers (Accounts Receivable)</strong></td></tr>
                @foreach($assetCustomers as $cust)
                    <tr><td>{{ $cust['name'] }}</td><td class="text-end">{{ number_format($cust['balance'], 0) }}</td><td></td></tr>
                @endforeach
                @foreach($liabCustomers as $cust)
                    <tr><td>{{ $cust['name'] }} (Advance)</td><td></td><td class="text-end">{{ number_format(abs($cust['balance']), 0) }}</td></tr>
                @endforeach

                <tr><td colspan="3" class="bg-light"><strong>Suppliers (Accounts Payable)</strong></td></tr>
                @foreach($assetSuppliers as $sup)
                    <tr><td>{{ $sup['name'] }} (Advance)</td><td class="text-end">{{ number_format(abs($sup['balance']), 0) }}</td><td></td></tr>
                @endforeach
                @foreach($liabSuppliers as $sup)
                    <tr><td>{{ $sup['name'] }}</td><td></td><td class="text-end">{{ number_format($sup['balance'], 0) }}</td></tr>
                @endforeach

                <!-- Note: Trial balance matches Debits & Credits perfectly only if Opening Inventory is booked as Debit, and Opening Balances are properly offset. 
                     For a simplified perpetual/periodic hybrid, we just show the unadjusted trial balance. -->
            </tbody>
            <tfoot class="table-dark">
                <tr>
                    <td class="text-end"><strong>Checking System Symmetry:</strong></td>
                    <td class="text-center" colspan="2"><small>(As a structural test, consult Balance Sheet for exact parity)</small></td>
                </tr>
            </tfoot>
        </table>
    </div>
</div>
@endsection
