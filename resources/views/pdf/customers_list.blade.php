@extends('pdf.layout_a4')

@section('title', 'Customers Report')
@section('report_name', 'Customers Report')

@section('content')
<div style="font-size: 10pt; margin-bottom: 10px;">
    <strong>Period:</strong> {{ $startDate }} to {{ $endDate }}
    @if($showFilter != 'all') | <strong>Status:</strong> {{ ucfirst($showFilter) }} @endif
</div>

<table class="table">
    <thead>
        <tr>
            <th>Name</th>
            <th class="text-right">Opening</th>
            <th class="text-right">Sale</th>
            <th class="text-right">Receive</th>
            <th class="text-right">Balance</th>
        </tr>
    </thead>
    <tbody>
        @foreach($customers as $c)
        <tr>
            <td>{{ $c->name }}</td>
            <td class="text-right">{{ number_format($c->computed_opening, 0) }}</td>
            <td class="text-right">{{ number_format($c->computed_dr, 0) }}</td>
            <td class="text-right">{{ number_format($c->computed_cr, 0) }}</td>
            <td class="text-right font-bold">{{ number_format($c->computed_balance, 0) }}</td>
        </tr>
        @endforeach
    </tbody>
    <tfoot>
        <tr style="border-top: 2px solid #000;">
            <td class="font-bold">Totals ({{ $customers->count() }})</td>
            <td class="text-right font-bold">{{ number_format($customers->sum('computed_opening'), 0) }}</td>
            <td class="text-right font-bold">{{ number_format($customers->sum('computed_dr'), 0) }}</td>
            <td class="text-right font-bold">{{ number_format($customers->sum('computed_cr'), 0) }}</td>
            <td class="text-right font-bold">{{ number_format($customers->sum('computed_balance'), 0) }}</td>
        </tr>
    </tfoot>
</table>

<div style="font-size: 8pt; text-align: center; margin-top: 30px; color: #888;">
    Generated {{ date('d-m-y h:i A') }} | NF DEV
</div>
@endsection
