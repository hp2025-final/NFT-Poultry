<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Customers Report - Thermal</title>
    <style>
        @page { margin: 0mm 12mm 0mm 0mm; }
        body {
            font-family: 'Courier', monospace;
            font-size: 11px;
            font-weight: bold;
            line-height: 1.2;
            width: 100%;
            margin: 0;
            padding: 0;
        }
        .solid { border-top: 1.5px solid #000; margin: 2px 0; }
        .dashed { border-top: 1px dashed #000; margin: 1px 0; }
        table { width: 100%; border-collapse: collapse; }
        td { padding: 1px 0; vertical-align: top; font-size: 11px; font-weight: bold; }
    </style>
</head>
<body>
    @php $company = \App\Models\CompanyInfo::first(); @endphp

    @foreach($customers as $index => $customer)
    <div style="page-break-after: always;">

        {{-- Company Name --}}
        <div style="font-size: 18px;">{{ $company->name ?? 'Company Name' }}</div>
        <div class="solid"></div>

        {{-- Customer Info --}}
        <div style="font-size: 11px;">Customer Name: {{ $customer->name }}</div>
        <div style="font-size: 11px;">Customer ID: (#{{ $customer->id }})</div>
        <div class="dashed"></div>

        {{-- Date Range --}}
        <table>
            <tr>
                <td>Date:</td>
                <td align="right">{{ \Carbon\Carbon::parse($startDate)->format('d-M-y') }} to {{ \Carbon\Carbon::parse($endDate)->format('d-M-y') }}</td>
            </tr>
        </table>

        <div class="solid"></div>

        {{-- Opening Balance --}}
        <table>
            <tr>
                <td style="font-size: 13px;">Opening Balance:</td>
                <td style="font-size: 13px;" align="right">{{ number_format($customer->computed_opening, 0) }}</td>
            </tr>
        </table>

        <div class="solid"></div>

        {{-- SALE Section --}}
        <div style="font-size: 12px; margin: 2px 0 0 0;">SALE</div>
        <div class="solid"></div>

        @if(count($customer->sale_details) > 0)
            @foreach($customer->sale_details as $sale)
            <table>
                <tr>
                    <td style="font-size: 10px;">
                        {{ \Carbon\Carbon::parse($sale['date'])->format('d-m-y') }}
                        {{ fmod($sale['qty'], 1) == 0 ? intval($sale['qty']) : number_format($sale['qty'], 2) }}
                        {{ $sale['unit'] }} X {{ number_format($sale['price'], 0) }} =
                    </td>
                    <td style="font-size: 10px; width: 55px;" align="right">{{ number_format($sale['amount'], 0) }}</td>
                </tr>
            </table>
            <div class="dashed"></div>
            @endforeach

            <table>
                <tr>
                    <td style="font-size: 13px;">Total Sale:</td>
                    <td style="font-size: 13px;" align="right">{{ number_format($customer->computed_dr, 0) }}</td>
                </tr>
            </table>
        @else
            <div style="font-size: 10px; margin: 2px 0;">No sales in this period.</div>
        @endif

        <div class="solid"></div>

        {{-- RECEIVE Section --}}
        <div style="font-size: 12px; margin: 2px 0 0 0;">RECEIVE</div>
        <div class="solid"></div>

        @if(count($customer->receipt_details) > 0)
            @foreach($customer->receipt_details as $receipt)
            <table>
                <tr>
                    <td style="font-size: 10px;">
                        {{ \Carbon\Carbon::parse($receipt['date'])->format('d-m-y') }} Receive {{ $receipt['account'] }}
                    </td>
                    <td style="font-size: 10px; width: 55px;" align="right">{{ number_format($receipt['amount'], 0) }}</td>
                </tr>
            </table>
            <div class="dashed"></div>
            @endforeach

            <table>
                <tr>
                    <td style="font-size: 13px;">Total Receive:</td>
                    <td style="font-size: 13px;" align="right">{{ number_format($customer->computed_cr, 0) }}</td>
                </tr>
            </table>
        @else
            <div style="font-size: 10px; margin: 2px 0;">No receives in this period.</div>
        @endif

        <div class="solid"></div>

        {{-- Closing Balance --}}
        <table>
            <tr>
                <td style="font-size: 14px;">Closing Balance:</td>
                <td style="font-size: 14px;" align="right">{{ number_format($customer->computed_balance, 0) }}</td>
            </tr>
        </table>

    </div>
    @endforeach
</body>
</html>
