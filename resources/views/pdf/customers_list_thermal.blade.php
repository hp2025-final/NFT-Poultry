<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Customers Report - Thermal</title>
    <style>
        @page { margin: 10px; }
        body { font-family: 'Courier', monospace; font-size: 11px; line-height: 1.2; width: 100%; }
        .table { width: 100%; border-collapse: collapse; margin-bottom: 5px; }
        .table td { padding: 3px 0; font-size: 11px; vertical-align: top; }
        .divider { border-top: 1px dashed #000; margin: 4px 0; }
        .page-break { page-break-after: always; }
    </style>
</head>
<body>
    @php $company = \App\Models\CompanyInfo::first(); @endphp
    
    @foreach($customers as $index => $customer)
    <div class="{{ $index < count($customers) - 1 ? 'page-break' : '' }}">
        <div style="text-align: center; font-weight: bold; font-size: 14px; margin-bottom: 5px;">
            <span style="font-size: 16px;">{{ $company->name ?? 'Company Name' }}</span><br>
            <span style="font-size: 12px;">Cust: {{ $customer->name }}</span>
        </div>
        
        <div><strong>Date:</strong> {{ \Carbon\Carbon::parse($startDate)->format('d-M-y') }} to {{ \Carbon\Carbon::parse($endDate)->format('d-M-y') }}</div>
        
        <table class="table" style="margin-top: 10px;">
            <tr>
                <td style="font-weight: bold; font-size: 13px;">Opening Balance:</td>
                <td style="font-weight: bold; font-size: 13px;" align="right">{{ number_format($customer->computed_opening, 0) }}</td>
            </tr>
        </table>
        
        <div class="divider"></div>
        
        <table class="table">
            <tbody>
                @php $currentBalance = $customer->computed_opening; @endphp
                @forelse($customer->transactions as $txn)
                @php 
                    $currentBalance += $txn['debit'];
                    $currentBalance -= $txn['credit'];
                    $isFirstLine = true;
                @endphp
                
                @foreach($txn['lines'] as $line)
                    <tr>
                        <td style="width: 50%;">
                            {{ $line['line1'] }} {{ $line['line2'] }}
                        </td>
                        <td style="width: 25%;">@if($isFirstLine && $txn['debit'] > 0) {{ number_format($txn['debit'], 0) }} @endif @if($isFirstLine && $txn['credit'] > 0) -{{ number_format($txn['credit'], 0) }} @endif</td>
                        <td style="width: 25%;" align="right">@if($isFirstLine) {{ number_format($currentBalance, 0) }} @endif</td>
                    </tr>
                    @php $isFirstLine = false; @endphp
                @endforeach
                <tr><td colspan="3"><div class="divider"></div></td></tr>
                @empty
                <tr>
                    <td colspan="3" align="center" style="padding: 10px 0;">No transactions found.</td>
                </tr>
                <tr><td colspan="3"><div class="divider"></div></td></tr>
                @endforelse
            </tbody>
        </table>

        <table class="table">
            <tr>
                <td style="font-weight: bold; font-size: 13px;">Closing Balance:</td>
                <td style="font-weight: bold; font-size: 13px;" align="right">{{ number_format($customer->computed_balance, 0) }}</td>
            </tr>
        </table>
        
        <div style="text-align: center; margin-top: 15px; font-size: 9px; opacity: 0.8; margin-bottom: 20px;">
            <p>Generated on {{ date('Y-m-d H:i') }}</p>
        </div>
    </div>
    @endforeach
</body>
</html>
