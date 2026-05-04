<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text-html; charset=utf-8"/>
    <title>@yield('title', 'Receipt')</title>
    <style>
        @page { size: 79mm auto; margin: 2mm; }
        body { font-family: 'Helvetica', 'Arial', sans-serif; font-size: 9pt; margin: 0; padding: 0; line-height: 1.3; width: 75mm; }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .table { width: 100%; border-collapse: collapse; margin-top: 5px; font-size: 8pt; }
        .table th, .table td { border-bottom: 1px dashed #000; padding: 3px 0; }
        .company-header h2 { margin: 0 0 2px 0; font-size: 14pt; font-weight: bold; }
        .company-header p { margin: 0; font-size: 8pt; }
        .strong { font-weight: bold; }
        .dashed-line { border-top: 1px dashed #000; margin: 5px 0; }
        .meta-info { font-size: 8pt; margin-bottom: 5px; }
        .meta-info p { margin: 1px 0; }
    </style>
</head>
<body>
    @php
        $company = \App\Models\CompanyInfo::first();
    @endphp
    <div class="company-header text-center">
        <h2>{{ $company->name ?? 'Company Name' }}</h2>
        <p>Ph: {{ $company->phone ?? 'Phone' }}</p>
    </div>
    
    <div class="dashed-line"></div>
    
    <div class="text-center strong" style="font-size: 10pt; margin-bottom: 3px;">
        @yield('receipt_type')
    </div>
    
    <div>
        @yield('content')
    </div>

    <div class="dashed-line"></div>
    <div class="text-center" style="font-size: 7pt; margin-top: 10px;">
        Printed: {{ \Carbon\Carbon::now()->format('d-m-y h:i A') }}
    </div>
</body>
</html>
