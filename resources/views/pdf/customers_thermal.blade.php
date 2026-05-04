<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Customer Ledger - Thermal</title>
    <style>
        @page { margin: 10px; }
        body { font-family: 'Courier', monospace; font-size: 11px; line-height: 1.2; width: 100%; }
        .table { width: 100%; border-collapse: collapse; margin-bottom: 5px; }
        .table td { padding: 3px 0; font-size: 11px; vertical-align: top; }
        .divider { border-top: 1px dashed #000; margin: 4px 0; }
        .text-right { text-align: right; }
    </style>
</head>
<body>
    @php $company = \App\Models\CompanyInfo::first(); @endphp
    <div style="text-align: center; font-weight: bold; font-size: 14px; margin-bottom: 5px;">
        <span style="font-size: 16px;">{{ $company->name ?? 'Company Name' }}</span><br>
        CUSTOMER LEDGER
    </div>
    
    <div><strong>Cust:</strong> {{ $customer->name }}</div>
    <div><strong>Date:</strong> {{ $start->format('d-M-y') }} to {{ $end->format('d-M-y') }}</div>
    <div style="margin-top: 10px; margin-bottom: 5px; font-weight: bold; font-size: 13px;">Opening Balance: {{ number_format($ledger->opening, 0) }}</div>
    
    <div class="divider"></div>
    <table class="table" style="margin-top: 0px;">
        <thead>
            <tr>
                <td style="width: 22%">Date</td>
                <td style="width: 32%">Desc.</td>
                <td style="width: 15%">Amt</td>
                <td style="width: 15%">Receive</td>
                <td style="width: 16%" align="right">Bal</td>
            </tr>
        </thead>
    </table>
    <div class="divider"></div>

    <table class="table">
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
                        {{ $line['line1'] }}<br>
                        {{ $line['line2'] }}
                    </td>
                    <td>@if($isFirstLine && $txn['debit'] > 0) {{ number_format($txn['debit'], 0) }} @endif</td>
                    <td>@if($isFirstLine && $txn['credit'] > 0) {{ number_format($txn['credit'], 0) }} @endif</td>
                    <td align="right">@if($isFirstLine) {{ number_format($currentBalance, 0) }} @endif</td>
                </tr>
                @php $isFirstLine = false; @endphp
            @endforeach
            <tr><td colspan="5"><div class="divider" style="border-top: 1px dotted #ccc;"></div></td></tr>
            @empty
            <tr>
                <td colspan="5" align="center" style="padding: 10px 0;">No transactions found.</td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <div class="divider"></div>
    <div style="margin-top: 5px; margin-bottom: 5px; font-weight: bold; font-size: 13px;">Closing Balance: {{ number_format($ledger->closing, 0) }}</div>

    <div style="text-align: center; margin-top: 15px; font-size: 9px; opacity: 0.8;">
        <p>This is a computer generated summary.</p>
        <p>Thank you!</p>
        <div style="margin-top: 10px;">{{ date('Y-m-d H:i') }}</div>
    </div>
</body>
</html>
