@props(['documentTitle' => null, 'documentNumber' => null, 'documentDate' => null, 'paperSize' => 'a4'])

@php
    $clinic = clinic_header_data();
@endphp

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $documentTitle ?? $clinic['name'] }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <style>
        :root {
            --primary: #4f46e5;
            --text: #1f2937;
            --text-muted: #6b7280;
            --border: #e5e7eb;
            --bg: #ffffff;
        }

        @page {
            size: A4;
            margin: 15mm;
        }

        @page a5 {
            size: A5;
            margin: 10mm;
        }

        @page receipt {
            size: 80mm auto;
            margin: 5mm;
        }

        * {
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }

        body {
            font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
            font-size: 14px;
            line-height: 1.5;
            color: var(--text);
            background: var(--bg);
            margin: 0;
            padding: 0;
        }

        .print-document {
            max-width: 210mm;
            margin: 0 auto;
            padding: 20px;
        }

        .paper-a5 .print-document {
            max-width: 148mm;
        }

        .paper-receipt .print-document {
            max-width: 70mm;
            padding: 10px;
            font-size: 12px;
        }

        /* Clinic Header */
        .clinic-header {
            text-align: center;
            border-bottom: 2px solid var(--primary);
            padding-bottom: 15px;
            margin-bottom: 20px;
        }

        .clinic-header img {
            max-height: 60px;
            margin-bottom: 8px;
        }

        .clinic-header h4 {
            margin: 0;
            font-size: 20px;
            font-weight: 700;
            color: var(--primary);
        }

        .clinic-header .clinic-details {
            font-size: 12px;
            color: var(--text-muted);
            margin-top: 4px;
        }

        .paper-receipt .clinic-header h4 {
            font-size: 14px;
        }

        .paper-receipt .clinic-header .clinic-details {
            font-size: 10px;
        }

        /* Document Title */
        .doc-title {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 20px;
            flex-wrap: wrap;
            gap: 10px;
        }

        .doc-title h5 {
            margin: 0;
            font-size: 16px;
            font-weight: 600;
            color: var(--primary);
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .doc-title .doc-meta {
            text-align: right;
            font-size: 13px;
        }

        .doc-title .doc-meta .doc-number {
            font-weight: 600;
        }

        /* Info Boxes */
        .info-box {
            background: #f8fafc;
            border: 1px solid var(--border);
            border-radius: 6px;
            padding: 12px 15px;
            margin-bottom: 15px;
        }

        .info-box h6 {
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: var(--text-muted);
            margin-bottom: 6px;
            font-weight: 600;
        }

        .info-box p {
            margin: 2px 0;
            font-size: 13px;
        }

        /* Tables */
        .doc-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }

        .doc-table th {
            background: #f1f5f9;
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 0.3px;
            color: var(--text-muted);
            font-weight: 600;
            padding: 8px 10px;
            border-bottom: 2px solid var(--border);
            text-align: left;
        }

        .doc-table td {
            padding: 8px 10px;
            border-bottom: 1px solid var(--border);
            font-size: 13px;
            vertical-align: top;
        }

        .doc-table .text-end {
            text-align: right;
        }

        /* Totals */
        .doc-totals {
            display: flex;
            justify-content: flex-end;
            margin-bottom: 20px;
        }

        .doc-totals table {
            width: 250px;
        }

        .doc-totals td {
            padding: 4px 10px;
            font-size: 13px;
        }

        .doc-totals .total-row td {
            font-weight: 700;
            font-size: 15px;
            border-top: 2px solid var(--primary);
            padding-top: 8px;
        }

        /* Footer */
        .doc-footer {
            margin-top: 30px;
            padding-top: 15px;
            border-top: 1px solid var(--border);
            font-size: 12px;
            color: var(--text-muted);
        }

        .doc-footer .footer-text {
            text-align: center;
            font-style: italic;
        }

        .signature-area {
            display: flex;
            justify-content: space-between;
            margin-top: 40px;
            padding-top: 10px;
        }

        .signature-line {
            width: 180px;
            text-align: center;
            border-top: 1px solid var(--text);
            padding-top: 5px;
            font-size: 12px;
        }

        /* Status badges */
        .status-badge {
            display: inline-block;
            padding: 2px 8px;
            border-radius: 4px;
            font-size: 11px;
            font-weight: 600;
            text-transform: uppercase;
        }

        .status-paid { background: #d1fae5; color: #065f46; }
        .status-pending { background: #fef3c7; color: #92400e; }
        .status-partial { background: #dbeafe; color: #1e40af; }
        .status-cancelled { background: #fee2e2; color: #991b1b; }

        /* Print-specific */
        .no-print {
            margin-bottom: 20px;
        }

        @media print {
            .no-print { display: none !important; }

            body {
                background: white !important;
                margin: 0;
                padding: 0;
            }

            .print-document {
                padding: 0;
                max-width: none;
            }

            body.paper-a5 {
                /* A5 is handled by @page named page */
            }

            body.paper-receipt .print-document {
                max-width: 70mm;
                padding: 10px;
                font-size: 12px;
            }

            .card, .info-box {
                border: 1px solid #dee2e6 !important;
                box-shadow: none !important;
            }

            .table th {
                background: #f1f5f9 !important;
            }

            .page-break {
                page-break-before: always;
            }

            .avoid-break {
                page-break-inside: avoid;
            }

            a {
                text-decoration: none;
                color: inherit;
            }
        }

        @media print and (max-width: 148mm) {
            @page {
                size: A5;
                margin: 10mm;
            }
        }

        /* Receipt-specific overrides */
        .paper-receipt .print-document {
            max-width: 70mm;
        }

        .paper-receipt .info-box {
            padding: 8px;
        }

        .paper-receipt .doc-table th,
        .paper-receipt .doc-table td {
            padding: 4px 6px;
            font-size: 11px;
        }

        .paper-receipt .signature-area {
            margin-top: 20px;
        }
    </style>
</head>
<body class="paper-{{ $paperSize }}">
    <div class="print-document">
        {{-- Clinic Header --}}
        <div class="clinic-header">
            @if($clinic['logo'])
                <img src="{{ asset('storage/' . $clinic['logo']) }}" alt="{{ $clinic['name'] }}">
            @endif
            <h4>{{ $clinic['name'] }}</h4>
            <div class="clinic-details">
                @if($clinic['address'])<div>{{ $clinic['address'] }}</div>@endif
                @if($clinic['phone'] || $clinic['email'])
                    <div>
                        @if($clinic['phone'])Phone: {{ $clinic['phone'] }}@endif
                        @if($clinic['phone'] && $clinic['email']) | @endif
                        @if($clinic['email']){{ $clinic['email'] }}@endif
                    </div>
                @endif
            </div>
        </div>

        {{-- Document Title Bar --}}
        @if($documentTitle)
            <div class="doc-title">
                <h5>{{ $documentTitle }}</h5>
                <div class="doc-meta">
                    @if($documentNumber)<div class="doc-number">{{ $documentNumber }}</div>@endif
                    @if($documentDate)<div>{{ $documentDate }}</div>@endif
                </div>
            </div>
        @endif

        {{-- Document Content --}}
        {{ $slot }}

        {{-- Footer --}}
        <div class="doc-footer">
            @if($clinic['footer'])
                <div class="footer-text">{{ $clinic['footer'] }}</div>
            @endif
            <div class="text-center mt-2">
                <small>Printed on {{ now()->format('d M Y, h:i A') }}</small>
            </div>
        </div>
    </div>
</body>
</html>
