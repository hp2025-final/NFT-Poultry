@extends('pdf.layout_thermal')

@section('title', 'Suppliers Report')
@section('receipt_type', 'SUPPLIERS REPORT')

@section('content')
<div class="meta-info text-center">
    <p>{{ $startDate }} to {{ $endDate }}</p>
</div>

<div class="dashed-line"></div>

<table class="table">
    <thead>
        <tr>
            <th style="text-align:left;">Name</th>
            <th style="text-align:right;">Op.</th>
            <th style="text-align:right;">Pay.</th>
            <th style="text-align:right;">Pur.</th>
            <th style="text-align:right;">Bal.</th>
        </tr>
    </thead>
    <tbody>
        @foreach($suppliers as $s)
        <tr>
            <td>{{ Str::limit($s->name, 12) }}</td>
            <td style="text-align:right;">{{ number_format($s->computed_opening, 0) }}</td>
            <td style="text-align:right;">{{ number_format($s->computed_dr, 0) }}</td>
            <td style="text-align:right;">{{ number_format($s->computed_cr, 0) }}</td>
            <td style="text-align:right;" class="strong">{{ number_format($s->computed_balance, 0) }}</td>
        </tr>
        @endforeach
    </tbody>
</table>

<div class="dashed-line"></div>

<table style="width:100%; font-size:8pt;">
    <tr>
        <td class="strong">Total ({{ $suppliers->count() }})</td>
        <td style="text-align:right;" class="strong">{{ number_format($suppliers->sum('computed_opening'), 0) }}</td>
        <td style="text-align:right;" class="strong">{{ number_format($suppliers->sum('computed_dr'), 0) }}</td>
        <td style="text-align:right;" class="strong">{{ number_format($suppliers->sum('computed_cr'), 0) }}</td>
        <td style="text-align:right;" class="strong">{{ number_format($suppliers->sum('computed_balance'), 0) }}</td>
    </tr>
</table>
@endsection
