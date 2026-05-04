<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Sale Invoice #{{ $sale->id }}</title>
    <style>
        body { font-family: sans-serif; font-size: 13px; color: #333; line-height: 1.5; }
        .header { text-align: center; margin-bottom: 30px; border-bottom: 2px solid #333; padding-bottom: 10px; }
        .company-name { font-size: 24px; font-weight: bold; margin-bottom: 5px; }
        .invoice-title { font-size: 18px; font-weight: bold; text-transform: uppercase; margin-top: 10px; }
        
        .info-table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        .info-table td { width: 50%; vertical-align: top; }
        
        .items-table { width: 100%; border-collapse: collapse; margin-bottom: 30px; }
        .items-table th { background: #f0f0f0; border: 1px solid #ddd; padding: 10px; text-align: left; font-size: 12px; }
        .items-table td { border: 1px solid #ddd; padding: 10px; }
        
        .totals-table { width: 40%; float: right; border-collapse: collapse; }
        .totals-table td { padding: 8px; border-bottom: 1px solid #ddd; }
        .grand-total { font-weight: bold; font-size: 16px; background: #f9f9f9; }
        
        .footer { margin-top: 50px; text-align: center; font-size: 11px; color: #777; clear: both; }
        .signature-box { margin-top: 50px; }
        .signature-line { border-bottom: 1px solid #333; width: 200px; display: inline-block; }
    </style>
</head>
<body>
    @php $company = \App\Models\CompanyInfo::first(); @endphp
    <div class="header">
        <div class="company-name">{{ $company->name ?? 'Company Name' }}</div>
        @if($company->address ?? false)<div style="font-size: 12px; color: #555;">{{ $company->address }}</div>@endif
        @if($company->phone ?? false)<div style="font-size: 12px; color: #555;">Ph: {{ $company->phone }}</div>@endif
        <div class="invoice-title">Sale Invoice</div>
    </div>

    <table class="info-table">
        <tr>
            <td>
                <strong>Customer:</strong><br>
                {{ $sale->customer->name }}<br>
                Phone: {{ $sale->customer->phone ?? 'N/A' }}
            </td>
            <td style="text-align: right;">
                <strong>Invoice ID:</strong> #{{ $sale->id }}<br>
                <strong>Date:</strong> {{ \Carbon\Carbon::parse($sale->date)->format('d-m-y') }}<br>
                <strong>Status:</strong> Paid
            </td>
        </tr>
    </table>

    <table class="items-table">
        <thead>
            <tr>
                <th style="width: 40px;">#</th>
                <th>Product Description</th>
                <th style="width: 80px; text-align: center;">Quantity</th>
                <th style="width: 100px; text-align: right;">Unit Price</th>
                <th style="width: 120px; text-align: right;">Amount</th>
            </tr>
        </thead>
        <tbody>
            @foreach($sale->items as $index => $item)
            <tr>
                <td style="text-align: center;">{{ $index + 1 }}</td>
                <td>{{ $item->product->name }}</td>
                <td style="text-align: center;">{{ number_format($item->qty, 2) }} KG</td>
                <td style="text-align: right;">{{ number_format($item->price, 2) }}</td>
                <td style="text-align: right;">{{ number_format($item->amount, 2) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <table class="totals-table">
        <tr class="grand-total">
            <td><strong>Grand Total (Rs.)</strong></td>
            <td style="text-align: right;"><strong>{{ number_format($sale->total_amount, 2) }}</strong></td>
        </tr>
    </table>

    <div class="signature-box" style="margin-top: 80px;">
        <div style="float: left; text-align: center;">
            <div class="signature-line"></div>
            <div style="margin-top: 5px;">Customer Signature</div>
        </div>
        <div style="float: right; text-align: center;">
            <div class="signature-line"></div>
            <div style="margin-top: 5px;">Authorized Signature</div>
        </div>
    </div>

    <div class="footer">
        Generated on {{ date('d-m-y H:i:s') }} | Software by NF Dev Team
    </div>
</body>
</html>
