<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Customers Closing Balances - Thermal</title>
    <style>
        @page { margin: 10px; }
        body { font-family: 'Courier', monospace; font-size: 11px; line-height: 1.2; width: 100%; }
        .center { text-align: center; }
        .bold { font-weight: bold; }
        .divider { border-top: 1px dashed #000; margin: 5px 0; }
        .table { width: 100%; border-collapse: collapse; margin-bottom: 5px; }
        .table td { padding: 3px 0; font-size: 10px; }
        .text-right { text-align: right; }
    </style>
</head>
<body>
    @php $company = \App\Models\CompanyInfo::first(); @endphp
    <div class="center">
        <div class="bold" style="font-size: 13px;">CUSTOMER CLOSING BALANCES</div>
        <div>{{ $company->name ?? 'Company Name' }}</div>
        <div class="divider"></div>
        <div>Date: {{ date('d-m-y H:i') }}</div>
        <div class="divider"></div>
    </div>

    <table class="table">
        <thead>
            <tr class="bold">
                <th align="left" style="width: 70%;">Customer</th>
                <th align="right">Balance</th>
            </tr>
        </thead>
        <tbody>
            @foreach($balances as $b)
            <tr>
                <td>{{ Str::limit($b['name'], 25) }}</td>
                <td align="right">{{ number_format($b['closing'], 0) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="divider"></div>
    <table class="table">
        <tr class="bold">
            <td style="font-size: 12px;">GRAND TOTAL:</td>
            <td align="right" style="font-size: 12px;">Rs. {{ number_format($total, 0) }}</td>
        </tr>
    </table>
    <div class="divider"></div>

    <div class="center" style="margin-top: 20px; font-size: 9px; opacity: 0.8;">
        <p>Software by NF Dev Team</p>
    </div>
</body>
</html>
