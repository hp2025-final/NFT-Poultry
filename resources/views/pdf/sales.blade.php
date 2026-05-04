<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Sales Summary Report</title>
    <style>
        body { font-family: sans-serif; font-size: 11px; color: #333; line-height: 1.4; }
        .header { text-align: center; margin-bottom: 25px; border-bottom: 2px solid #333; padding-bottom: 10px; }
        .report-title { font-size: 16px; font-weight: bold; text-transform: uppercase; }
        .period { font-size: 12px; margin-top: 5px; }
        
        .table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        .table th, .table td { border: 1px solid #999; padding: 8px; }
        .table th { background: #f0f0f0; text-align: left; }
        
        .text-right { text-align: right; }
        .bold { font-weight: bold; }
    </style>
</head>
<body>
    <div class="header">
        <div class="report-title">Sales Summary Report</div>
        <div class="period">Period: {{ $start->format('d-m-y') }} to {{ $end->format('d-m-y') }}</div>
    </div>

    <table class="table">
        <thead>
            <tr>
                <th style="width: 100px;">Date</th>
                <th style="width: 80px;">Invoice #</th>
                <th>Customer Name</th>
                <th class="text-right" style="width: 120px;">Total Amount</th>
            </tr>
        </thead>
        <tbody>
            @php $total = 0; @endphp
            @foreach($sales as $s)
            <tr>
                <td>{{ \Carbon\Carbon::parse($s->date)->format('d-m-y') }}</td>
                <td>#{{ $s->id }}</td>
                <td>{{ $s->customer->name }}</td>
                <td class="text-right bold border-bottom">{{ number_format($s->total_amount, 2) }}</td>
            </tr>
            @php $total += $s->total_amount; @endphp
            @endforeach
        </tbody>
        <tfoot>
            <tr class="bold">
                <td colspan="3" class="text-right">GRAND TOTAL SALES (Selected Period):</td>
                <td class="text-right" style="border-top: 2px solid #000;">Rs. {{ number_format($total, 2) }}</td>
            </tr>
        </tfoot>
    </table>

    <div style="font-size: 10px; text-align: center; margin-top: 50px;">
        Generated @ {{ date('d-m-y H:i:s') }} | NF DEV Business Manager
    </div>
</body>
</html>
