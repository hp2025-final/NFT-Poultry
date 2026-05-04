<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>@yield('title', 'Report')</title>
    <style>
        @page { size: A4; margin: 15mm 12mm; }
        body { font-family: 'Helvetica', 'Arial', sans-serif; font-size: 11pt; margin: 0; padding: 0; }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .font-bold { font-weight: bold; }
        .table { width: 100%; border-collapse: collapse; margin-top: 15px; font-size: 10pt; }
        .table th, .table td { border: 1px solid #333; padding: 6px; }
        .table th { background-color: #efefef; }
        .company-header h1 { margin: 0 0 5px 0; font-size: 24pt; }
        .company-header p { margin: 2px 0; font-size: 10pt; color: #555; }
        .report-title { font-size: 16pt; font-weight: bold; margin-bottom: 10px; text-transform: uppercase; }
        .header-meta { margin-top: 15px; font-size: 10pt; display: flex; justify-content: space-between; }
    </style>
</head>
<body>
    @php
        $company = \App\Models\CompanyInfo::first();
    @endphp
    <div class="company-header text-center">
        <h1>{{ $company->name ?? 'Company Name' }}</h1>
        <p>{{ $company->address ?? 'Address' }} | Ph: {{ $company->phone ?? 'Phone' }}</p>
    </div>
    
    <div style="border-bottom: 2px solid #000; margin-top: 10px; margin-bottom: 15px;"></div>
    
    <div class="text-center report-title">
        @yield('report_name')
    </div>

    @yield('content')
</body>
</html>
