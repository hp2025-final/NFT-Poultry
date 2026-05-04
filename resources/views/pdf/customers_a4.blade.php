<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Customer Ledger - {{ $customer->name }}</title>
    <style>
        body { font-family: sans-serif; font-size: 13px; color: #333; line-height: 1.5; }
        .header { text-align: center; margin-bottom: 30px; border-bottom: 2px solid #333; padding-bottom: 10px; }
        .company-name { font-size: 24px; font-weight: bold; margin-bottom: 5px; }
        .report-title { font-size: 18px; font-weight: bold; text-transform: uppercase; margin-top: 10px; }
        
        .info-table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        .info-table td { width: 50%; vertical-align: top; }
        
        .items-table { width: 100%; border-collapse: collapse; margin-bottom: 30px; }
        .items-table th { background: #f0f0f0; border-top: 2px solid #333; border-bottom: 2px solid #333; padding: 10px 5px; text-align: left; font-size: 12px; font-weight: bold; }
        .items-table td { border-bottom: 1px dotted #ccc; padding: 10px 5px; vertical-align: top; }
        
        .footer { margin-top: 50px; text-align: center; font-size: 11px; color: #777; clear: both; }
    </style>
</head>
<body>
    @php $company = \App\Models\CompanyInfo::first(); @endphp
    <div class="header">
        <div class="company-name">{{ $company->name ?? 'Company Name' }}</div>
        @if($company->address ?? false)<div style="font-size: 12px; color: #555;">{{ $company->address }}</div>@endif
        @if($company->phone ?? false)<div style="font-size: 12px; color: #555;">Ph: {{ $company->phone }}</div>@endif
        <div class="report-title">Customer Ledger</div>
    </div>

    <table class="info-table">
        <tr>
            <td>
                <strong>Customer:</strong><br>
                {{ $customer->name }}<br>
                Phone: {{ $customer->phone ?? 'N/A' }}
            </td>
            <td style="text-align: right;">
                <strong>Period:</strong><br>
                {{ $start->format('d-M-y') }} to {{ $end->format('d-M-y') }}
            </td>
        </tr>
    </table>

    <div style="font-size: 14px; font-weight: bold; margin-bottom: 10px;">
        Opening Balance: {{ number_format($ledger->opening, 2) }}
    </div>

    <table class="items-table">
        <thead>
            <tr>
                <th style="width: 15%;">Date</th>
                <th style="width: 35%;">Desc.</th>
                <th style="width: 15%;">Amt</th>
                <th style="width: 15%;">Receive</th>
                <th style="width: 20%; text-align: right;">Bal</th>
            </tr>
        </thead>
        <tbody>
            @php $currentBalance = $ledger->opening; @endphp
            @forelse($transactions as $txn)
            @php 
                $currentBalance += $txn['debit'];
                $currentBalance -= $txn['credit'];
                $isFirstLine = true;
            @endphp
            
            @foreach($txn['lines'] as $line)
                <tr>
                    <td>@if($isFirstLine) {{ \Carbon\Carbon::parse($txn['date'])->format('d-m-y') }} @endif</td>
                    <td>
                        <div style="font-weight: bold;">{{ $line['line1'] }}</div>
                        <div style="color: #555; font-size: 11px;">{{ $line['line2'] }}</div>
                    </td>
                    <td>@if($isFirstLine && $txn['debit'] > 0) {{ number_format($txn['debit'], 2) }} @endif</td>
                    <td>@if($isFirstLine && $txn['credit'] > 0) {{ number_format($txn['credit'], 2) }} @endif</td>
                    <td style="text-align: right; font-weight: bold;">@if($isFirstLine) {{ number_format($currentBalance, 2) }} @endif</td>
                </tr>
                @php $isFirstLine = false; @endphp
            @endforeach
            @empty
            <tr>
                <td colspan="5" style="text-align: center; padding: 20px;">No transactions found for this period.</td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <div style="font-size: 16px; font-weight: bold; padding-top: 10px; border-top: 2px solid #333;">
        Closing Balance: {{ number_format($ledger->closing, 2) }}
    </div>

    <div class="footer">
        Generated on {{ date('d-m-y H:i:s') }}
    </div>
</body>
</html>
