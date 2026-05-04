<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Daily Customer Report</title>
    <style>
        @page { margin: 20px; }
        body { font-family: sans-serif; font-size: 11px; color: #333; line-height: 1.3; }
        .header { text-align: center; margin-bottom: 20px; }
        .company-name { font-size: 18px; font-weight: bold; }
        .report-title { font-size: 14px; text-transform: uppercase; margin-top: 5px; text-decoration: underline; }
        
        .table { width: 100%; border-collapse: collapse; margin-bottom: 15px; table-layout: fixed; }
        .table th, .table td { border: 1px solid #999; padding: 5px; vertical-align: top; overflow: hidden; }
        .table th { background: #eee; text-align: center; font-weight: bold; text-transform: uppercase; font-size: 10px; }
        
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .bold { font-weight: bold; }
        .bg-light { background: #f9f9f9; }
        
        .footer { position: fixed; bottom: 0; width: 100%; font-size: 9px; text-align: center; color: #777; }
    </style>
</head>
<body>
    @php $company = \App\Models\CompanyInfo::first(); @endphp
    <div class="header">
        <div class="company-name">{{ $company->name ?? 'Company Name' }}</div>
        <div class="report-title">Daily Customer Activity Report</div>
        <div style="margin-top: 5px;">Period: {{ $start->format('d-m-y') }} to {{ $end->format('d-m-y') }}</div>
    </div>

    <table class="table">
        <thead>
            <tr>
                <th style="width: 15%;">Customer</th>
                <th style="width: 10%;">Opening</th>
                <th style="width: 12%;">Invoices</th>
                <th style="width: 20%;">Items Details</th>
                <th style="width: 10%;">Total Sale</th>
                <th style="width: 10%;">RCT Ref</th>
                <th style="width: 10%;">Received</th>
                <th style="width: 13%;">Closing</th>
            </tr>
        </thead>
        <tbody>
            @foreach($report as $row)
            <tr>
                <td class="bold">{{ $row['customer']->name }}</td>
                <td class="text-right">{{ number_format($row['opening'], 2) }}</td>
                
                <td class="text-center font-x-small">
                    @foreach($row['sale_items'] as $item)
                        <div>#{{ $item['invoice'] }}</div>
                    @endforeach
                </td>
                <td class="font-x-small">
                    @foreach($row['sale_items'] as $item)
                        <div>{{ $item['product'] }} ({{ number_format($item['qty'],1) }}KG)</div>
                    @endforeach
                </td>
                <td class="text-right bold">{{ number_format($row['total_sales'], 2) }}</td>
                
                <td class="text-center font-x-small">
                    @foreach($row['receipt_items'] as $item)
                        <div>#{{ $item['receipt_id'] }}</div>
                    @endforeach
                </td>
                <td class="text-right bold">{{ number_format($row['total_receipts'], 2) }}</td>
                
                <td class="text-right bold bg-light">{{ number_format($row['closing'], 2) }}</td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr class="bg-light bold" style="font-size: 12px;">
                <td class="text-right">GRAND TOTALS:</td>
                <td class="text-right">{{ number_format($grandTotals['opening'], 2) }}</td>
                <td colspan="2"></td>
                <td class="text-right">{{ number_format($grandTotals['sales'], 2) }}</td>
                <td></td>
                <td class="text-right">{{ number_format($grandTotals['receipts'], 2) }}</td>
                <td class="text-right">{{ number_format($grandTotals['closing'], 2) }}</td>
            </tr>
        </tfoot>
    </table>

    <div class="footer">
        Generated on {{ date('d-m-y H:i:s') }} | Page 1 of 1
    </div>
</body>
</html>
