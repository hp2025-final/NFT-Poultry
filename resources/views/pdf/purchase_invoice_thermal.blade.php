<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Thermal Purchase Receipt #{{ $purchase->id }}</title>
    <style>
        @page { size: 79mm auto; margin: 2mm 8mm 2mm 1mm; }
        body { font-family: 'Courier', monospace; font-size: 11px; line-height: 1.2; width: 100%; }
        .center { text-align: center; }
        .bold { font-weight: bold; }
        .divider { border-top: 1px dashed #000; margin: 5px 0; }
        .table { width: 100%; border-collapse: collapse; margin-bottom: 5px; }
        .table td { padding: 3px 0; font-size: 10px; }
    </style>
</head>
<body>
    <div class="center">
        <div class="bold" style="font-size: 14px;">NF DEV STORE</div>
        <div class="divider"></div>
        <div class="bold">PURCHASE VOUCHER</div>
        <div>ID: #{{ $purchase->id }}</div>
        <div>Date: {{ \Carbon\Carbon::parse($purchase->date)->format('d-m-y') }}</div>
        <div class="divider"></div>
    </div>

    <div><strong>Sup:</strong> {{ $purchase->supplier->name }}</div>
    <div class="divider"></div>

    <table class="table">
        <thead>
            <tr class="bold">
                <th align="left">Item</th>
                <th align="center">Qty</th>
                <th align="right">Cost</th>
            </tr>
        </thead>
        <tbody>
            @foreach($purchase->items as $item)
            <tr>
                <td>{{ Str::limit($item->product->name, 20) }}</td>
                <td align="center">{{ number_format($item->qty, 1) }}</td>
                <td align="right">{{ number_format($item->amount, 0) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="divider"></div>
    <table class="table">
        <tr class="bold">
            <td style="font-size: 13px;">PURCHASE TOTAL:</td>
            <td align="right" style="font-size: 13px;">Rs. {{ number_format($purchase->total_amount, 0) }}</td>
        </tr>
    </table>
    <div class="divider"></div>

    <div class="center" style="font-size: 9px; margin-top: 20px;">
        <div style="border-top:1px solid #000; width:150px; margin: 0 auto; padding-top: 5px;">Receiver Signature</div>
        <div style="margin-top: 10px;">{{ date('Y-m-d H:i') }}</div>
    </div>
</body>
</html>
