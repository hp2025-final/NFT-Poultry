<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Accounts Report</title>
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
        <div class="report-title">Accounts Summary Report</div>
        <div class="period">Period: {{ $start->format('d-m-y') }} to {{ $end->format('d-m-y') }}</div>
    </div>

    <table class="table">
        <thead>
            <tr>
                <th>Account Name</th>
                <th class="text-right">Opening Balance</th>
                <th class="text-right">Total Debit (+)</th>
                <th class="text-right">Total Credit (-)</th>
                <th class="text-right">Closing Balance</th>
            </tr>
        </thead>
        <tbody>
            @php $totalClosing = 0; @endphp
            @foreach($ledgers as $l)
            <tr>
                <td>{{ $l['name'] }}</td>
                <td class="text-right">{{ number_format($l['opening'], 2) }}</td>
                <td class="text-right">{{ number_format($l['dr'], 2) }}</td>
                <td class="text-right">{{ number_format($l['cr'], 2) }}</td>
                <td class="text-right bold">{{ number_format($l['closing'], 2) }}</td>
            </tr>
            @php $totalClosing += $l['closing']; @endphp
            @endforeach
        </tbody>
        <tfoot>
            <tr class="bold">
                <td colspan="4" class="text-right">TOTAL NET LIQUIDITY:</td>
                <td class="text-right" style="border-top: 2px solid #000;">Rs. {{ number_format($totalClosing, 2) }}</td>
            </tr>
        </tfoot>
    </table>

    <div style="font-size: 10px; text-align: center; margin-top: 50px;">
        Generated @ {{ date('d-m-y H:i:s') }} | NF DEV Business Manager
    </div>
</body>
</html>
