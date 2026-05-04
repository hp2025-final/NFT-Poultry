@extends('pdf.layout_a4')

@section('title', 'Daily Consolidated Report - ' . \Carbon\Carbon::parse($date)->format('d-M-Y'))

@section('content')
<div class="header">
    <div style="float: left; width: 50%;">
        <h2>Daily Consolidated Report</h2>
        <p><strong>Date:</strong> {{ \Carbon\Carbon::parse($date)->format('d-M-Y') }}</p>
    </div>
    <div style="clear: both;"></div>
</div>

<div class="summary-box">
    <h3>Trading & Cash Flow Summary</h3>
    <table class="table">
        <thead>
            <tr>
                <th style="width: 33%">Purchases (Stock In)</th>
                <th style="width: 33%">Sales (Stock Out)</th>
                <th style="width: 33%">Trading Balance</th>
            </tr>
        </thead>
        <tbody>
            @php
                $tradingQty = $totals['sales_kg'] - $totals['purchases_kg'];
                $tradingAmt = $totals['sales_amount'] - $totals['purchases_amount'];
            @endphp
            <tr>
                <td style="text-align: center;">
                    <strong>{{ number_format($totals['purchases_kg'], 2) }} KG</strong><br>
                    Amt: {{ number_format($totals['purchases_amount'], 0) }}
                </td>
                <td style="text-align: center;">
                    <strong>{{ number_format($totals['sales_kg'], 2) }} KG</strong><br>
                    Amt: {{ number_format($totals['sales_amount'], 0) }}
                </td>
                <td style="text-align: center;">
                    <strong>{{ $tradingQty > 0 ? '+' : '' }}{{ number_format($tradingQty, 2) }} KG</strong><br>
                    Amt: {{ $tradingAmt > 0 ? '+' : '' }}{{ number_format($tradingAmt, 0) }}
                </td>
            </tr>
        </tbody>
    </table>

    <table class="table" style="margin-top: 10px;">
        <thead>
            <tr>
                <th style="width: 33%">Receipts (Money In)</th>
                <th style="width: 33%">Payments & Expenses (Money Out)</th>
                <th style="width: 33%">Net Cash Flow</th>
            </tr>
        </thead>
        <tbody>
            @php
                $moneyIn = $totals['receipts'] + $totals['capital_in'];
                $moneyOut = $totals['payments'] + $totals['expenses'] + $totals['drawing_out'];
            @endphp
            <tr>
                <td style="text-align: center;">
                    <strong>{{ number_format($moneyIn, 0) }}</strong>
                </td>
                <td style="text-align: center;">
                    <strong>{{ number_format($moneyOut, 0) }}</strong>
                </td>
                <td style="text-align: center;">
                    <strong>{{ $totals['net_cash_flow'] > 0 ? '+' : '' }}{{ number_format($totals['net_cash_flow'], 0) }}</strong>
                </td>
            </tr>
        </tbody>
    </table>
</div>

<div style="width: 48%; float: left; margin-right: 2%;">
    <h3>Purchases & Stock Adjustments</h3>
    <table class="table">
        <thead>
            <tr>
                <th>Supplier</th>
                <th class="text-right">KG</th>
                <th class="text-right">Rate</th>
                <th class="text-right">Total</th>
            </tr>
        </thead>
        <tbody>
            @php
                $totalPurchaseKG = 0;
                $totalPurchaseAmount = 0;
            @endphp
            @foreach($purchases as $p)
                @php
                    $kg = $p->items->sum('qty');
                    $rate = $p->items->first() ? $p->items->first()->price : 0;
                    $totalPurchaseKG += $kg;
                    $totalPurchaseAmount += $p->total_amount;
                @endphp
                <tr>
                    <td>{{ $p->supplier->name ?? 'N/A' }}</td>
                    <td class="text-right">{{ number_format($kg, 2) }}</td>
                    <td class="text-right">{{ number_format($rate, 0) }}</td>
                    <td class="text-right">{{ number_format($p->total_amount, 0) }}</td>
                </tr>
            @endforeach

            @foreach($adjustments as $a)
                @php
                    $kg = $a->type === 'decrease' ? -$a->qty : $a->qty;
                    $totalPurchaseKG += $kg;
                    $amount = $a->type === 'decrease' ? -$a->amount : $a->amount;
                @endphp
                <tr>
                    <td>Adj: System/Variance</td>
                    <td class="text-right">{{ number_format($kg, 2) }}</td>
                    <td class="text-right">{{ number_format($a->unit_cost, 0) }}</td>
                    <td class="text-right">{{ number_format($amount, 0) }}</td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td class="text-right"><strong>Total:</strong></td>
                <td class="text-right"><strong>{{ number_format($totalPurchaseKG, 2) }}</strong></td>
                <td class="text-right"><strong>{{ $totalPurchaseKG > 0 ? number_format($totalPurchaseAmount / $totalPurchaseKG, 2) : '0.00' }}</strong></td>
                <td class="text-right"><strong>{{ number_format($totalPurchaseAmount, 0) }}</strong></td>
            </tr>
        </tfoot>
    </table>
</div>

<div style="width: 50%; float: left;">
    <h3>Sales Invoices</h3>
    <table class="table">
        <thead>
            <tr>
                <th>Customer</th>
                <th class="text-right">KG</th>
                <th class="text-right">Rate</th>
                <th class="text-right">Total</th>
            </tr>
        </thead>
        <tbody>
            @php
                $totalSalesKG = 0;
                $totalSalesAmount = 0;
            @endphp
            @foreach($sales as $s)
                @php
                    $kg = $s->items->sum('qty');
                    $rate = $s->items->first() ? $s->items->first()->price : 0;
                    $totalSalesKG += $kg;
                    $totalSalesAmount += $s->total_amount;
                @endphp
                <tr>
                    <td>{{ $s->customer->name ?? 'N/A' }}</td>
                    <td class="text-right">{{ number_format($kg, 2) }}</td>
                    <td class="text-right">{{ number_format($rate, 0) }}</td>
                    <td class="text-right">{{ number_format($s->total_amount, 0) }}</td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td class="text-right"><strong>Total:</strong></td>
                <td class="text-right"><strong>{{ number_format($totalSalesKG, 2) }}</strong></td>
                <td class="text-right"><strong>{{ $totalSalesKG > 0 ? number_format($totalSalesAmount / $totalSalesKG, 2) : '0.00' }}</strong></td>
                <td class="text-right"><strong>{{ number_format($totalSalesAmount, 0) }}</strong></td>
            </tr>
        </tfoot>
    </table>
</div>

<div style="clear: both; margin-bottom: 20px;"></div>

<div style="width: 48%; float: left; margin-right: 2%;">
    <h3>Receipts (Money In)</h3>
    <table class="table">
        <thead>
            <tr>
                <th>Customer</th>
                <th class="text-right">Amount</th>
            </tr>
        </thead>
        <tbody>
            @php $rTotal = 0; @endphp
            @foreach($receipts as $r)
                @php $rTotal += $r->amount; @endphp
                <tr>
                    <td>{{ $r->customer->name ?? 'N/A' }}</td>
                    <td class="text-right">{{ number_format($r->amount, 0) }}</td>
                </tr>
            @endforeach
            @foreach($equity->where('type', 'capital') as $c)
                @php $rTotal += $c->amount; @endphp
                <tr>
                    <td>Capital: {{ $c->account->name ?? 'N/A' }}</td>
                    <td class="text-right">{{ number_format($c->amount, 0) }}</td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td class="text-right"><strong>Total:</strong></td>
                <td class="text-right"><strong>{{ number_format($rTotal, 0) }}</strong></td>
            </tr>
        </tfoot>
    </table>
</div>

<div style="width: 50%; float: left;">
    <h3>Payments & Expenses (Money Out)</h3>
    <table class="table">
        <thead>
            <tr>
                <th>Supplier / Expense</th>
                <th class="text-right">Amount</th>
            </tr>
        </thead>
        <tbody>
            @php $pTotal = 0; @endphp
            @foreach($payments as $p)
                @php $pTotal += $p->amount; @endphp
                <tr>
                    <td>{{ $p->supplier->name ?? 'N/A' }}</td>
                    <td class="text-right">{{ number_format($p->amount, 0) }}</td>
                </tr>
            @endforeach
            @foreach($expenses as $e)
                @php $pTotal += $e->amount; @endphp
                <tr>
                    <td>Exp: {{ $e->category->name ?? 'N/A' }}</td>
                    <td class="text-right">{{ number_format($e->amount, 0) }}</td>
                </tr>
            @endforeach
            @foreach($equity->where('type', 'drawing') as $d)
                @php $pTotal += $d->amount; @endphp
                <tr>
                    <td>Drawing: {{ $d->account->name ?? 'N/A' }}</td>
                    <td class="text-right">{{ number_format($d->amount, 0) }}</td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td class="text-right"><strong>Total:</strong></td>
                <td class="text-right"><strong>{{ number_format($pTotal, 0) }}</strong></td>
            </tr>
        </tfoot>
    </table>
</div>

<div style="clear: both;"></div>
@endsection
