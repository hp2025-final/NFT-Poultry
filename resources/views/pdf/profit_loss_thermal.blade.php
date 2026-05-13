<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Profit & Loss - Thermal</title>
    <style>
        @page { size: 79mm auto; margin: 2mm 8mm 2mm 1mm; }
        body { font-family: 'Courier', monospace; font-size: 12px; line-height: 1.3; width: 100%; font-weight: bold; }
        .center { text-align: center; }
        .bold { font-weight: bold; }
        .divider { border-top: 2px solid #000; margin: 8px 0; }
        .divider-thin { border-top: 1px dashed #000; margin: 5px 0; }
        .table { width: 100%; border-collapse: collapse; margin-bottom: 5px; page-break-inside: avoid; }
        .table td { padding: 3px 0; font-size: 12px; }
        .text-right { text-align: right; }
        .summary-box { border: 2px solid #000; padding: 8px; margin-bottom: 15px; page-break-inside: avoid; }
        .breakup-header { background: #eee; padding: 4px; margin-top: 15px; text-align: center; border: 1px solid #000; font-size: 13px; page-break-inside: avoid; }
    </style>
</head>
<body>
    @php 
        $company = \App\Models\CompanyInfo::first(); 
        $startDT = \Carbon\Carbon::parse($start);
        $endDT = \Carbon\Carbon::parse($end);
    @endphp

    <div class="center">
        <div class="bold" style="font-size: 18px;">PROFIT & LOSS</div>
        <div class="bold" style="font-size: 14px;">{{ $company->name ?? 'NF Dev' }}</div>
        <div style="font-size: 12px;">{{ $startDT->format('d-m-Y') }} to {{ $endDT->format('d-m-Y') }}</div>
        <div class="divider"></div>
    </div>

    <div class="summary-box">
        <div class="bold center" style="text-decoration: underline; margin-bottom: 8px; font-size: 14px;">GRAND SUMMARY</div>
        <table class="table">
            <tr>
                <td>TOTAL SALE:</td>
                <td class="text-right">{{ number_format($totalSales, 0) }}</td>
            </tr>
            <tr>
                <td>TOTAL PURCHASE:</td>
                <td class="text-right">{{ number_format($totalPurchases, 0) }}</td>
            </tr>
            <tr>
                <td>GROSS PROFIT:</td>
                <td class="text-right">{{ number_format($grossProfit, 0) }}</td>
            </tr>
            <tr>
                <td>TOTAL EXPENSE:</td>
                <td class="text-right">{{ number_format($totalExpenses, 0) }}</td>
            </tr>
            <tr style="font-size: 15px;">
                <td class="bold">NET PROFIT:</td>
                <td class="text-right bold">{{ number_format($netProfit, 0) }}</td>
            </tr>
            <tr>
                <td>STOCK ADJ:</td>
                <td class="text-right">{{ number_format($totalStockAdjustment, 0) }}</td>
            </tr>
        </table>
    </div>

    <div class="bold center" style="margin-top: 10px; font-size: 14px;">DAILY BREAKUP</div>

    @foreach($dailyBreakdown as $row)
    <div class="breakup-header">Breakup {{ date('d-m-Y', strtotime($row['date'])) }}</div>
    <table class="table">
        <tr>
            <td>Sale:</td>
            <td class="text-right">{{ number_format($row['sales'], 0) }}</td>
        </tr>
        <tr>
            <td>Purchase:</td>
            <td class="text-right">{{ number_format($row['purchases'], 0) }}</td>
        </tr>
        <tr>
            <td>Gross Profit:</td>
            <td class="text-right">{{ number_format($row['gross'], 0) }}</td>
        </tr>
        <tr>
            <td>Expense:</td>
            <td class="text-right">{{ number_format($row['expenses'], 0) }}</td>
        </tr>
        <tr class="bold">
            <td>Net Profit:</td>
            <td class="text-right">{{ number_format($row['net'], 0) }}</td>
        </tr>
        <tr>
            <td>Stock Adjustment:</td>
            <td class="text-right">{{ number_format($row['stock_adj'], 0) }}</td>
        </tr>
    </table>
    <div class="divider-thin"></div>
    @endforeach

    <div class="center" style="margin-top: 20px; font-size: 10px;">
        Printed @ {{ date('d-m-Y H:i') }}
    </div>
</body>
</html>
