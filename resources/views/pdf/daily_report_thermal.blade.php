@extends('pdf.layout_thermal')

@section('content')
<div style="text-align: center; margin-bottom: 5px;">
    <strong>Daily Consolidated Report</strong><br>
    Date: {{ \Carbon\Carbon::parse($date)->format('d-M-Y') }}
</div>

<div class="divider"></div>
<div style="text-align: center; font-weight: bold;">Trading Balance</div>
<table class="table">
    @php
        $tradingQty = $totals['sales_kg'] - $totals['purchases_kg'];
        $tradingAmt = $totals['sales_amount'] - $totals['purchases_amount'];
    @endphp
    <tr><td>Purchases In:</td><td class="text-right">{{ number_format($totals['purchases_kg'], 2) }}KG / {{ number_format($totals['purchases_amount'], 0) }}</td></tr>
    <tr><td>Sales Out:</td><td class="text-right">{{ number_format($totals['sales_kg'], 2) }}KG / {{ number_format($totals['sales_amount'], 0) }}</td></tr>
    <tr><td><strong>Net Trad:</strong></td><td class="text-right"><strong>{{ $tradingQty > 0 ? '+' : '' }}{{ number_format($tradingQty, 2) }}KG / {{ $tradingAmt > 0 ? '+' : '' }}{{ number_format($tradingAmt, 0) }}</strong></td></tr>
</table>

<div class="divider"></div>
<div style="text-align: center; font-weight: bold;">Cash Flow</div>
<table class="table">
    @php
        $moneyIn = $totals['receipts'] + $totals['capital_in'];
        $moneyOut = $totals['payments'] + $totals['expenses'] + $totals['drawing_out'];
    @endphp
    <tr><td>Receipts (In):</td><td class="text-right">{{ number_format($moneyIn, 0) }}</td></tr>
    <tr><td>Payments (Out):</td><td class="text-right">{{ number_format($moneyOut, 0) }}</td></tr>
    <tr><td><strong>Net Cash:</strong></td><td class="text-right"><strong>{{ $totals['net_cash_flow'] > 0 ? '+' : '' }}{{ number_format($totals['net_cash_flow'], 0) }}</strong></td></tr>
</table>

<div class="divider"></div>
<div style="text-align: center; font-weight: bold;">Purchases & Adjustments</div>
<table class="table">
    <thead>
        <tr>
            <th style="width:40%">Supplier</th>
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
                <td>{{ substr($p->supplier->name ?? 'N/A', 0, 10) }}</td>
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
                <td>Adj: Sys</td>
                <td class="text-right">{{ number_format($kg, 2) }}</td>
                <td class="text-right">{{ number_format($a->unit_cost, 0) }}</td>
                <td class="text-right">{{ number_format($amount, 0) }}</td>
            </tr>
        @endforeach
    </tbody>
</table>
<div style="text-align: right; font-weight: bold; border-top: 1px dashed #000; margin-top: 2px;">
    Tot KG: {{ number_format($totalPurchaseKG, 2) }} &nbsp; Tot Amt: {{ number_format($totalPurchaseAmount, 0) }}
</div>

<div class="divider"></div>
<div style="text-align: center; font-weight: bold;">Sales Invoices</div>
<table class="table">
    <thead>
        <tr>
            <th style="width:40%">Customer</th>
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
                <td>{{ substr($s->customer->name ?? 'N/A', 0, 10) }}</td>
                <td class="text-right">{{ number_format($kg, 2) }}</td>
                <td class="text-right">{{ number_format($rate, 0) }}</td>
                <td class="text-right">{{ number_format($s->total_amount, 0) }}</td>
            </tr>
        @endforeach
    </tbody>
</table>
<div style="text-align: right; font-weight: bold; border-top: 1px dashed #000; margin-top: 2px;">
    Tot KG: {{ number_format($totalSalesKG, 2) }} &nbsp; Tot Amt: {{ number_format($totalSalesAmount, 0) }}
</div>

<div class="divider"></div>
<div style="text-align: center; font-weight: bold;">Receipts (In)</div>
<table class="table">
    <tbody>
        @php $rTotal = 0; @endphp
        @foreach($receipts as $r)
            @php $rTotal += $r->amount; @endphp
            <tr>
                <td>{{ substr($r->customer->name ?? 'N/A', 0, 15) }}</td>
                <td class="text-right">{{ number_format($r->amount, 0) }}</td>
            </tr>
        @endforeach
        @foreach($equity->where('type', 'capital') as $c)
            @php $rTotal += $c->amount; @endphp
            <tr>
                <td>Cap: {{ substr($c->account->name ?? 'N/A', 0, 10) }}</td>
                <td class="text-right">{{ number_format($c->amount, 0) }}</td>
            </tr>
        @endforeach
    </tbody>
</table>
<div style="text-align: right; font-weight: bold; border-top: 1px dashed #000; margin-top: 2px;">
    Tot Amt: {{ number_format($rTotal, 0) }}
</div>

<div class="divider"></div>
<div style="text-align: center; font-weight: bold;">Payments & Exp (Out)</div>
<table class="table">
    <tbody>
        @php $pTotal = 0; @endphp
        @foreach($payments as $p)
            @php $pTotal += $p->amount; @endphp
            <tr>
                <td>{{ substr($p->supplier->name ?? 'N/A', 0, 15) }}</td>
                <td class="text-right">{{ number_format($p->amount, 0) }}</td>
            </tr>
        @endforeach
        @foreach($expenses as $e)
            @php $pTotal += $e->amount; @endphp
            <tr>
                <td>Exp: {{ substr($e->category->name ?? 'N/A', 0, 10) }}</td>
                <td class="text-right">{{ number_format($e->amount, 0) }}</td>
            </tr>
        @endforeach
        @foreach($equity->where('type', 'drawing') as $d)
            @php $pTotal += $d->amount; @endphp
            <tr>
                <td>Draw: {{ substr($d->account->name ?? 'N/A', 0, 10) }}</td>
                <td class="text-right">{{ number_format($d->amount, 0) }}</td>
            </tr>
        @endforeach
    </tbody>
</table>
<div style="text-align: right; font-weight: bold; border-top: 1px dashed #000; margin-top: 2px;">
    Tot Amt: {{ number_format($pTotal, 0) }}
</div>
<div class="divider" style="margin-top: 10px;"></div>
<div style="text-align: center; font-size: 10px;">End of Report</div>
@endsection
