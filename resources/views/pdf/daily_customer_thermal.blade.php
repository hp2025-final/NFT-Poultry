<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Daily Customer Summary - Thermal</title>
    <style>
        @page { margin: 10px; }
        body { font-family: 'Courier', monospace; font-size: 10px; line-height: 1.1; width: 100%; }
        .center { text-align: center; }
        .bold { font-weight: bold; }
        .divider { border-top: 1px dashed #000; margin: 4px 0; }
        .table { width: 100%; border-collapse: collapse; margin-bottom: 2px; }
        .table td { padding: 1px 0; font-size: 9px; }
        .customer-row { background: #eee; font-weight: bold; padding: 2px; }
        .text-right { text-align: right; }
        .page-break { page-break-after: always; }
    </style>
</head>
<body>
    @php $company = \App\Models\CompanyInfo::first(); @endphp
    <div class="center">
        <div class="bold" style="font-size: 13px;">DAILY CUSTOMER REPORT</div>
        <div>{{ $company->name ?? 'Company Name' }}</div>
        <small>{{ $start->format('d-m-y') }} to {{ $end->format('d-m-y') }}</small>
        <div class="divider"></div>
    </div>

    @foreach($report as $row)
    <div class="customer-row">{{ $row['customer']->name }}</div>
    <table class="table">
        <tr>
            <td>Opening:</td>
            <td align="right">{{ number_format($row['opening'], 0) }}</td>
        </tr>
        <tr>
            <td class="bold">Sales (+)</td>
            <td align="right" class="bold">{{ number_format($row['total_sales'], 0) }}</td>
        </tr>
        <tr>
            <td class="bold">Receipts (-)</td>
            <td align="right" class="bold">{{ number_format($row['total_receipts'], 0) }}</td>
        </tr>
        <tr>
            <td class="bold">Closing:</td>
            <td align="right" class="bold" style="font-size: 11px;">Rs. {{ number_format($row['closing'], 0) }}</td>
        </tr>
    </table>
    <div class="divider" style="border-top-style: dotted;"></div>
    @endforeach

    <div class="center" style="margin-top: 10px; border: 1px solid #000; padding: 5px;">
        <div class="bold">GRAND TOTAL SUMMARY</div>
        <table class="table" style="margin-top: 5px;">
            <tr><td>Total Opening:</td><td align="right">{{ number_format($grandTotals['opening'], 0) }}</td></tr>
            <tr><td>Total Sales:</td><td align="right">{{ number_format($grandTotals['sales'], 0) }}</td></tr>
            <tr><td>Total Receipts:</td><td align="right">{{ number_format($grandTotals['receipts'], 0) }}</td></tr>
            <tr><td class="bold">TOTAL CLOSING:</td><td align="right" class="bold">Rs. {{ number_format($grandTotals['closing'], 0) }}</td></tr>
        </table>
    </div>

    <div class="center" style="margin-top: 15px; font-size: 8px;">
        Generated @ {{ date('d-m-y H:i') }}
    </div>
</body>
</html>
