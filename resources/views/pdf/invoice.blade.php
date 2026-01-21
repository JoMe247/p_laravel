<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <title>Invoice PDF</title>
    <style>
        * {
            font-family: DejaVu Sans, sans-serif;
        }

        body {
            margin: 28px;
            color: #111827;
        }

        .top-grid {
            width: 100%;
            border-collapse: collapse;
        }

        .card {
            border: 1px solid #e5e7eb;
            border-radius: 10px;
            padding: 14px;
        }

        .logo {
            width: 220px;
            height: 120px;
            object-fit: contain;
        }

        .right {
            text-align: right;
        }

        .muted {
            color: #6b7280;
            font-size: 12px;
            font-weight: 600;
        }

        .h1 {
            font-size: 18px;
            font-weight: 900;
            margin: 0;
        }

        .h2 {
            font-size: 14px;
            font-weight: 800;
            margin: 0 0 8px 0;
        }

        .spacer {
            height: 14px;
        }

        .mid-grid {
            width: 100%;
            border-collapse: collapse;
            margin-top: 14px;
        }

        .box {
            border: 1px solid #e5e7eb;
            border-radius: 10px;
            padding: 12px;
            vertical-align: top;
        }

        .kv {
            width: 100%;
            border-collapse: collapse;
        }

        .kv td {
            padding: 3px 0;
            font-size: 12px;
        }

        .kv td:first-child {
            color: #6b7280;
            font-weight: 700;
            width: 95px;
        }

        .total-big {
            font-size: 22px;
            font-weight: 900;
            margin: 6px 0 10px 0;
        }

        table.items {
            width: 100%;
            border-collapse: collapse;
            margin-top: 16px;
            border: 1px solid #e5e7eb;
            border-radius: 10px;
            overflow: hidden;
        }

        table.items thead th {
            background: #f9fafb;
            border-bottom: 1px solid #e5e7eb;
            text-align: left;
            padding: 10px;
            font-size: 12px;
            font-weight: 900;
        }

        table.items tbody td {
            border-bottom: 1px solid #eef2f7;
            padding: 10px;
            font-size: 12px;
            vertical-align: top;
        }

        .num {
            text-align: right;
            white-space: nowrap;
        }

        .footer-total {
            width: 100%;
            margin-top: 10px;
            border-collapse: collapse;
        }

        .footer-total td {
            padding: 10px;
            font-weight: 900;
            font-size: 14px;
        }

        .footer-total td:last-child {
            text-align: right;
        }

        .small-footer {
            margin-top: 18px;
            text-align: center;
            font-size: 11px;
            color: #6b7280;
        }
    </style>
</head>

<body>

    {{-- TOP: Logo (izq) + Customer info (der) --}}
    <table class="top-grid" style="width:100%; border-collapse:collapse;">
        <tr>
            {{-- IZQUIERDA: Logo + Agency info separado --}}
            <td style="width:50%; vertical-align:top; padding-right:12px;">

                {{-- Logo SOLO --}}
                <div class="card">
                    @if (!empty($agencyInfo->agency_logo))
                        <img class="logo" src="{{ public_path('storage/' . $agencyInfo->agency_logo) }}" alt="Logo">
                    @else
                        <div class="muted"
                            style="height:120px; display:flex; align-items:center; justify-content:center;">
                            LOGO
                        </div>
                    @endif
                </div>

                <div style="height:10px;"></div>

                {{-- Agency info SEPARADO del logo --}}
                <div class="card">
                    <div style="font-weight:900; font-size:14px;">{{ $agencyInfo->agency_name ?? '' }}</div>
                    <div class="muted">{{ $agencyInfo->agency_address ?? '' }}</div>
                    <div class="muted">{{ $agencyInfo->office_phone ?? '' }}</div>
                    @if (!empty($agencyInfo->office_email))
                        <div class="muted">{{ $agencyInfo->office_email }}</div>
                    @endif
                </div>

            </td>

            {{-- DERECHA: Customer info (SOLO una vez) --}}
            <td style="width:50%; vertical-align:top; padding-left:12px;">
                <div class="card right">
                    <p class="h2" style="margin-bottom:6px;">Invoice to:</p>
                    <div style="font-weight:900;">{{ $customer->Name ?? '' }}</div>
                    <div class="muted">{{ $customer->Address ?? '' }}</div>
                    <div class="muted">
                        {{ $customer->City ?? '' }}{{ !empty($customer->State) ? ', ' . $customer->State : '' }}
                    </div>
                    <div class="muted">{{ $customer->Email1 ?? '' }}</div>
                    <div class="muted">{{ $customer->Phone ?? '' }}</div>
                </div>
            </td>
        </tr>
    </table>


    {{-- MID: Invoice To (izq) + Totals/Invoice meta (der) --}}
    <table class="mid-grid">
        <tr>
            <td style="width:55%;"></td>
            <td style="width:45%; padding-left:10px;">
                <div class="box">
                    <div class="muted" style="font-weight:900;">INVOICE TOTAL</div>
                    <div class="total-big">
                        ${{ number_format((float) ($grandTotal ?: 0), 2) }}
                    </div>

                    <table class="kv">
                        <tr>
                            <td>Invoice #</td>
                            <td style="font-weight:900;">{{ $invoice->invoice_number ?? '' }}</td>
                        </tr>
                        <tr>
                            <td>Date</td>
                            <td style="font-weight:900;">{{ $invoice->creation_date ?? '' }}</td>
                        </tr>
                        <tr>
                            <td>Due Date</td>
                            <td style="font-weight:900;">{{ $invoice->payment_date ?? '' }}</td>
                        </tr>
                        <tr>
                            <td>Policy #</td>
                            <td style="font-weight:900;">{{ $invoice->policy_number ?? '' }}</td>
                        </tr>
                        <tr>
                            <td>Sales Agent</td>
                            <td style="font-weight:900;">
                                {{ $invoice->created_by_name ?? '' }}
                            </td>
                        </tr>
                    </table>
                </div>
            </td>
        </tr>
    </table>

    {{-- ITEMS TABLE --}}
    <table class="items">
        <thead>
            <tr>
                <th style="width:46%;">Item</th>
                <th style="width:14%;" class="num">Amount</th>
                <th style="width:20%;" class="num">Price ($)</th>
                <th style="width:20%;" class="num">Line total</th>
            </tr>
        </thead>
        <tbody>
            @forelse($rows as $r)
                @php
                    $item = $r['item'] ?? '';
                    $qty = $r['amount'] ?? '';
                    $price = $r['price'] ?? '';
                    $line = $r['total'] ?? '';
                @endphp
                <tr>
                    <td>{{ $item }}</td>
                    <td class="num">{{ $qty }}</td>
                    <td class="num">${{ number_format((float) $price, 2) }}</td>
                    <td class="num">${{ number_format((float) $line, 2) }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="4" class="muted" style="text-align:center; padding:14px;">
                        No items
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    {{-- FOOTER TOTAL --}}
    <table class="footer-total">
        <tr>
            <td>Grand Total</td>
            <td>${{ number_format((float) ($grandTotal ?: 0), 2) }}</td>
        </tr>
    </table>

    @if (!empty($agencyInfo->invoice_footer_image))
        <div style="margin-top:18px;">
            <img src="{{ public_path('storage/' . $agencyInfo->invoice_footer_image) }}"
                style="width:100%; max-height:260px; object-fit:contain;">
        </div>
    @endif


    <div class="small-footer">
        {{ $agencyInfo->agency_name ?? '' }} • {{ date('Y') }}
    </div>

</body>

</html>
