<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <title>Estimate PDF</title>
    <style>
        @font-face {
            font-family: 'Montserrat';
            font-style: normal;
            font-weight: 400;
            src: url("{{ storage_path('fonts/montserrat/Montserrat-Regular.ttf') }}") format("truetype");
        }

        @font-face {
            font-family: 'Montserrat';
            font-style: normal;
            font-weight: 700;
            src: url("{{ storage_path('fonts/montserrat/Montserrat-Bold.ttf') }}") format("truetype");
        }

        :root {
            --font-base: 12px;
            --title-size: 22px;
            --total-size: 24px;
            --label-size: 10px;
            --table-head-size: 10px;
            --table-row-size: 11.5px;
            --footer-size: 10px;

            --text: #111827;
            --muted: #6b7280;
            --border: #e5e7eb;
            --border-soft: #eef2f7;
            --head-bg: #f9fafb;

            --radius: 8px;
            --pad: 12px;

            --footer-img-h: 105px;
        }

        body {
            font-family: 'Montserrat', sans-serif;
            font-weight: 400;
            margin: 0px;
            color: var(--text);
            font-size: var(--font-base);
            line-height: 1.25;
        }

        .right {
            text-align: right;
        }

        .muted {
            color: var(--muted);
            font-weight: 400;
        }

        .bold {
            font-weight: 700;
        }

        .avoid-break {
            page-break-inside: avoid;
        }

        @page {
            margin: 26px 28px 42px 28px;
        }

        .h-invoice {
            font-size: var(--title-size);
            font-weight: 700;
            letter-spacing: .9px;
            margin: 0;
        }

        .label {
            font-size: var(--label-size);
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .75px;
            color: var(--muted);
        }

        .value {
            font-weight: 700;
            color: var(--text);
        }

        .card {
            padding: var(--pad);
            background: #fff;
        }

        .logo-wrap {
            width: 70%;
            overflow: hidden;
        }

        .logo-img {
            width: 100%;
            max-height: 100%;
            object-fit: contain;
        }

        .total-box {
            padding: 14px;
            background: #fff;
        }

        .total-title {
            font-size: var(--label-size);
            font-weight: 700;
            color: var(--muted);
            text-transform: uppercase;
            letter-spacing: .75px;
            margin-bottom: 6px;
        }

        .total-big {
            font-size: var(--total-size);
            font-weight: 700;
            margin: 0 0 10px 0;
        }

        .kv {
            width: 100%;
            border-collapse: collapse;
        }

        .kv td {
            padding: 0px 0;
            vertical-align: top;
        }

        .kv td:first-child {
            width: 112px;
            color: var(--muted);
            font-weight: 700;
        }

        .kv td:last-child {
            font-weight: 700;
        }

        .items {
            margin-top: 14px;
            width: 100%;
            border-collapse: collapse;
            border: 1px solid var(--border);
            border-radius: var(--radius);
            overflow: hidden;
        }

        .items thead th {
            background: var(--head-bg);
            border-bottom: 1px solid var(--border);
            padding: 10px 10px;
            font-size: var(--table-head-size);
            text-transform: uppercase;
            letter-spacing: .75px;
            color: var(--muted);
            font-weight: 700;
        }

        .items tbody td {
            padding: 10px 10px;
            border-bottom: 1px solid var(--border-soft);
            font-size: var(--table-row-size);
            font-weight: 400;
        }

        .items tbody tr:last-child td {
            border-bottom: none;
        }

        .num {
            text-align: right;
            white-space: nowrap;
        }

        .footer-total {
            margin-top: 10px;
            width: 100%;
            border-collapse: collapse;
        }

        .footer-total td {
            padding: 10px 0;
            font-weight: 700;
            border-top: 2px solid #111827;
        }

        .footer-total td:last-child {
            text-align: right;
        }

        .footer-img {
            display: inline-block;
            max-width: 100%;
            max-height: 300px;
            object-fit: contain;
            background: #fff;
            position: absolute;
            bottom: 30px;
            left: 50%;
            transform: translateX(-50%);
        }

        .pdf-fixed-footer {
            position: fixed;
            right: 28px;
            bottom: 14px;
            font-size: 10px;
            color: #6b7280;
            font-weight: 400;
            text-align: right;
            white-space: nowrap;
        }
    </style>
</head>

<body>

    {{-- TOP: Logo + Agency info --}}
    <table class="top-grid" style="width:100%; border-collapse:collapse;">
        <tr>
            <td style="width:100%; vertical-align:top;">
                <div class="card">
                    <table style="width:100%; border-collapse:collapse;">
                        <tr>
                            <td style="width:35%; vertical-align:middle;">
                                <div class="logo-wrap">
                                    @if (!empty($agencyInfo->agency_logo))
                                    <img class="logo-img"
                                        src="{{ public_path('storage/' . $agencyInfo->agency_logo) }}"
                                        alt="Logo">
                                    @else
                                    <div class="muted" style="height:90px; display:flex; align-items:center; justify-content:center;">
                                        LOGO
                                    </div>
                                    @endif
                                </div>
                            </td>

                            <td style="width:65%; vertical-align:top; padding-left:0px; font-size:20px;">
                                <div style="font-weight:700;">
                                    {{ $agencyInfo->agency_name ?? '' }}
                                </div>
                                <div class="muted">{{ $agencyInfo->agency_address ?? '' }}</div>
                                <div class="muted">{{ $agencyInfo->office_phone ?? '' }}</div>
                                @if (!empty($agencyInfo->office_email))
                                <div class="muted">{{ $agencyInfo->office_email }}</div>
                                @endif
                            </td>
                        </tr>
                    </table>
                </div>
            </td>
        </tr>
    </table>

    {{-- MID: TOTAL + META | CUSTOMER --}}
    <table class="mid-grid" style="width:100%; border-collapse:collapse; margin-top:14px;">
        <tr>

            <td style="width:45%; vertical-align:top; padding-right:12px;">
                <div class="total-box" style="font-size: 12px">

                    <div class="total-title" style="font-size: 14px">Estimate total</div>
                    <div class="total-big">
                        ${{ number_format((float) ($grandTotal ?: 0), 2) }}
                    </div>

                    <table class="kv">
                        <tr>
                            <td>Next payment date</td>
                            <td class="bold">{{ $estimate->next_py_date ?? '' }}</td>
                        </tr>
                        <tr>
                            <td>Estimate #</td>
                            <td class="bold">{{ $estimate->estimate_number ?? '' }}</td>
                        </tr>
                        <tr>
                            <td>Date</td>
                            <td class="bold">{{ $estimate->creation_date ?? '' }}</td>
                        </tr>
                        <tr>
                            <td>Due date</td>
                            <td class="bold">{{ $estimate->payment_date ?? '' }}</td>
                        </tr>
                        <tr>
                            <td>Policy #</td>
                            <td class="bold">{{ $estimate->policy_number ?? '' }}</td>
                        </tr>
                        <tr>
                            <td>Sales agent</td>
                            <td class="bold">{{ $estimate->created_by_name ?? '' }}</td>
                        </tr>
                    </table>

                </div>
            </td>

            <td style="width:55%; vertical-align:top; padding-left:12px;">
                <div class="card right" style="font-size: 20px">
                    <div class="label" style="margin-bottom:6px; font-size:14px;">Estimate to</div>

                    <div class="bold" style="margin-bottom:2px;">
                        {{ $customer->Name ?? '' }}
                    </div>

                    @if (!empty($customer->Address))
                    <div class="muted">{{ $customer->Address }}</div>
                    @endif

                    <div class="muted">
                        {{ $customer->City ?? '' }}{{ !empty($customer->State) ? ', ' . $customer->State : '' }}
                    </div>

                    @if (!empty($customer->Email1))
                    <div class="muted">{{ $customer->Email1 }}</div>
                    @endif

                    @if (!empty($customer->Phone))
                    <div class="muted">{{ $customer->Phone }}</div>
                    @endif
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

    {{-- ✅ Footer image independiente de estimates --}}
    @if (!empty($agencyInfo->estimate_footer_image) && !empty($agencyInfo->estimate_footer_enabled))
    <div class="footer-image-wrap avoid-break">
        <img class="footer-img"
            src="{{ public_path('storage/' . $agencyInfo->estimate_footer_image) }}"
            alt="Footer Image">
    </div>
    @endif

    <div class="pdf-fixed-footer">
        {{ $agencyInfo->agency_name ?? '' }} • {{ date('Y') }}
    </div>

</body>

</html>