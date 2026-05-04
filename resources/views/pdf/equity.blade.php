<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Owner's Equity Report</title>
    <style>
        body { font-family: sans-serif; font-size: 13px; color: #333; line-height: 1.6; }
        .header { text-align: center; margin-bottom: 30px; border-bottom: 1px solid #333; padding-bottom: 5px; }
        .title { font-size: 20px; font-weight: bold; }
        
        .row { width: 100%; margin-bottom: 10px; }
        .label { float: left; width: 300px; }
        .value { float: right; width: 150px; text-align: right; }
        .clear { clear: both; }
        
        .section-header { background: #f0f0f0; padding: 5px; font-weight: bold; margin-bottom: 10px; }
        .total-row { border-top: 2px solid #333; margin-top: 15px; font-size: 16px; font-weight: bold; }
    </style>
</head>
<body>
    <div class="header">
        <div class="title">STATEMENT OF OWNER'S EQUITY</div>
        <div>Period: {{ $start->format('d-m-y') }} to {{ $end->format('d-m-y') }}</div>
    </div>

    <div class="section-header">Income Summary (Calculated)</div>
    <div class="row">
        <div class="label">Total Sales Revenue</div>
        <div class="value">{{ number_format($sales, 2) }}</div>
        <div class="clear"></div>
    </div>
    <div class="row">
        <div class="label">Cost of Goods Sold (Purchases Used)</div>
        <div class="value">({{ number_format($cogs, 2) }})</div>
        <div class="clear"></div>
    </div>
    <div class="row">
        <div class="label">Total Operating Expenses</div>
        <div class="value">({{ number_format($expenses, 2) }})</div>
        <div class="clear"></div>
    </div>
    <div class="row bold">
        <div class="label">Estimated Net Profit for Period</div>
        <div class="value" style="border-top:1px solid #999;">{{ number_format($netProfit, 2) }}</div>
        <div class="clear"></div>
    </div>

    <div class="section-header" style="margin-top:20px;">Owner's Equity Movement</div>
    <div class="row">
        <div class="label">Owner's Capital Injected (Selected Period)</div>
        <div class="value">+ {{ number_format($capital, 2) }}</div>
        <div class="clear"></div>
    </div>
    <div class="row">
        <div class="label">Owner's Drawings (Selected Period)</div>
        <div class="value">- {{ number_format($drawings, 2) }}</div>
        <div class="clear"></div>
    </div>
    <div class="row">
        <div class="label">Add: Net Profit for Period</div>
        <div class="value">+ {{ number_format($netProfit, 2) }}</div>
        <div class="clear"></div>
    </div>

    <div class="total-row">
        <div class="label">Net Equity Increase / (Decrease)</div>
        <div class="value">{{ number_format($capital - $drawings + $netProfit, 2) }}</div>
        <div class="clear"></div>
    </div>

    <div style="font-size: 10px; text-align: center; margin-top: 80px;">
        This is an estimate based on selected period and does not include opening values.<br>
        Generated on {{ date('d-m-y H:i:s') }}
    </div>
</body>
</html>
