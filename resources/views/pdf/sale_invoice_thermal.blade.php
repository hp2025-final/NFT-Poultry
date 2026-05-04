<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Thermal Sale Receipt #{{ $sale->id }}</title>
    <style>
        @page {
            size: 79mm auto;
            margin: 4mm 3mm 3mm 3mm;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Courier New', 'Courier', monospace;
            font-size: 10px;
            line-height: 1.3;
            width: 100%;
        }

        .center {
            text-align: center;
        }

        .bold {
            font-weight: bold;
        }

        .divider {
            border-top: 1px dashed #000;
            margin: 5px 0;
        }

        /* Company Header */
        .company-name {
            font-size: 14px;
            font-weight: bold;
            margin: 0;
        }

        .company-detail {
            font-size: 9px;
            margin: 1px 0;
        }

        /* Meta table for Customer / Date */
        .meta-table {
            width: 100%;
            border-collapse: collapse;
            margin: 3px 0;
            font-size: 10px;
        }

        .meta-table td {
            padding: 1px 0;
            vertical-align: top;
        }

        /* Items table */
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin: 2px 0;
        }

        .items-table th {
            font-size: 9px;
            font-weight: bold;
            padding: 3px 0;
            border-bottom: 1px dashed #000;
        }

        .items-table td {
            font-size: 9px;
            padding: 3px 0;
            vertical-align: top;
        }

        /* Total table */
        .total-table {
            width: 100%;
            border-collapse: collapse;
            margin: 3px 0;
        }

        .total-table td {
            padding: 2px 0;
            font-size: 13px;
            font-weight: bold;
        }

        /* Footer */
        .footer {
            margin-top: 6px;
            font-size: 8px;
            text-transform: uppercase;
        }

        .footer p {
            margin: 1px 0;
        }
    </style>
</head>

<body>
    @php
        $company = \App\Models\CompanyInfo::first();
    @endphp

    {{-- ===== CENTER: Company Info ===== --}}
    <div class="divider"></div>
    <div class="center">
        <p class="company-name">{{ $company->name ?? 'Company Name' }}</p>
        <p class="company-detail">{{ $company->address ?? '' }}</p>
        <p class="company-detail">Ph: {{ $company->phone ?? '' }}</p>
        @if($company->email ?? false)
            <p class="company-detail">{{ $company->email }}</p>
        @endif
    </div>

    <div class="divider"></div>

    <div class="center bold" style="font-size: 11px; margin: 2px 0;">SALE RECEIPT</div>
    <div class="center" style="font-size: 9px;">Inv: #{{ $sale->id }}</div>

    <div class="divider"></div>

    {{-- ===== LEFT: Customer | RIGHT: Date ===== --}}
    <table class="meta-table">
        <tr>
            <td style="text-align: left;"><strong>Customer:</strong> {{ $sale->customer->name }}</td>
            <td style="text-align: right;"><strong>Date:</strong>
                {{ \Carbon\Carbon::parse($sale->date)->format('d-m-y') }}</td>
        </tr>
    </table>

    <div class="divider"></div>

    {{-- ===== LEFT: Item | CENTER: Qty | RIGHT: Rate & Amount ===== --}}
    <table class="items-table">
        <thead>
            <tr>
                <th style="text-align: left; width: 32%;">Item</th>
                <th style="text-align: center; width: 18%;">Qty</th>
                <th style="text-align: right; width: 25%;">Rate</th>
                <th style="text-align: right; width: 25%;">Amount</th>
            </tr>
        </thead>
        <tbody>
            @foreach($sale->items as $item)
                <tr>
                    <td style="text-align: left;">{{ Str::limit($item->product->name, 16) }}</td>
                    <td style="text-align: center;">{{ number_format($item->qty, 2) }}</td>
                    <td style="text-align: right;">{{ number_format($item->price, 0) }}</td>
                    <td style="text-align: right;">{{ number_format($item->amount, 0) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="divider"></div>

    {{-- ===== Grand Total ===== --}}
    <table class="total-table">
        <tr>
            <td style="text-align: left;">TOTAL:</td>
            <td style="text-align: right;">Rs. {{ number_format($sale->total_amount, 0) }}</td>
        </tr>
    </table>

    <div class="divider"></div>

    {{-- ===== Footer ===== --}}
    <div class="center footer">
        <p>Thank you for your business!</p>
        <p>Items once sold are not returnable.</p>
        <div style="font-size: 7px; margin-top: 5px;">Printed: {{ date('d/m/Y h:i A') }}</div>
    </div>
</body>

</html>