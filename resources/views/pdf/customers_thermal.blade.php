<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Customer Ledger - Thermal</title>
    <style>
        @page { size: 79mm auto; margin: 2mm 8mm 2mm 1mm; }
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
    
    <div style="font-size: 12px;"><strong>Cust:</strong> {{ $customer->name }} (#{{ $customer->id }})</div>
    <div style="font-size: 11px;"><strong>Date:</strong> 
        {{ $start ? $start->format('d-M-y') : 'All Time' }} to {{ $end ? $end->format('d-M-y') : 'Now' }}
    </div>
    
    <div style="margin-top: 10px; margin-bottom: 5px; font-weight: bold; font-size: 14px;">
        Previous Balance: {{ number_format($ledger->opening, 0) }}
    </div>
    
    <div class="divider"></div>

    <table class="table">
        <tbody>
            @php $currentBalance = $ledger->opening; @endphp
            @forelse($transactions as $txn)
            @php 
                $currentBalance += $txn['debit'];
                $currentBalance -= $txn['credit'];
            @endphp
            
            <tr>
                <td style="font-size: 10px; width: 65px;">{{ \Carbon\Carbon::parse($txn['date'])->format('d-m-y') }}</td>
                <td style="font-size: 14px; font-weight: bold;">
                    @if($txn['debit'] > 0) Sale: {{ number_format($txn['debit'], 0) }} @endif
                    @if($txn['credit'] > 0) Recv: {{ number_format($txn['credit'], 0) }} @endif
                </td>
                <td style="font-size: 11px; font-weight: bold;" align="right">B:{{ number_format($currentBalance, 0) }}</td>
            </tr>
            
            @foreach($txn['lines'] as $line)
                <tr>
                    <td colspan="3" style="font-size: 10px; padding-left: 10px; padding-bottom: 2px;">
                        - {{ $line['line1'] }} {{ $line['line2'] }}
                    </td>
                </tr>
            @endforeach
            <tr><td colspan="3"><div class="divider" style="border-top: 1px dotted #ccc;"></div></td></tr>
            @empty
            <tr>
                <td colspan="3" align="center" style="padding: 10px 0;">No transactions found.</td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <div class="divider"></div>
    <div style="margin-top: 5px; margin-bottom: 5px; font-weight: bold; font-size: 16px;">
        Closing Balance: {{ number_format($ledger->closing, 0) }}
    </div>

    <div style="text-align: center; margin-top: 15px; font-size: 9px; opacity: 0.8;">
        <p>This is a computer generated summary.</p>
        <p>Thank you!</p>
        <div style="margin-top: 10px;">{{ date('Y-m-d H:i') }}</div>
    </div>
</body>
</html>
