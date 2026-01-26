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
            margin: 26px 28px;
            color: #111827;
            font-size: 12px;
        }

        /* Encabezados */
        .title {
            font-size: 18px;
            font-weight: 900;
            letter-spacing: .4px;
            margin: 0;
        }

        .label {
            color: #6b7280;
            font-weight: 700;
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: .6px;
        }

        .value {
            font-weight: 800;
            color: #111827;
        }

        /* Cards formales (menos “rounded”, más factura) */
        .card {
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            padding: 12px;
            background: #fff;
        }

        .right {
            text-align: right;
        }

        .muted {
            color: #6b7280;
            font-weight: 600;
        }

        /* Tabla grid */
        table {
            width: 100%;
            border-collapse: collapse;
        }

        .top-gap {
            height: 10px;
        }

        .section-gap {
            height: 14px;
        }

        /* Logo */
        .logo {
            width: 220px;
            height: 90px;
            object-fit: contain;
        }

        /* Invoice Total */
        .total-box .total-title {
            font-weight: 900;
            font-size: 11px;
            color: #6b7280;
            letter-spacing: .6px;
        }

        .total-box .total-amount {
            font-size: 22px;
            font-weight: 900;
            margin: 6px 0 10px 0;
        }

        .kv td {
            padding: 4px 0;
            vertical-align: top;
        }

        .kv td:first-child {
            width: 92px;
            color: #6b7280;
            font-weight: 700;
        }

        .kv td:last-child {
            font-weight: 900;
        }

        /* Items table - estilo contable */
        .items {
            margin-top: 14px;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            overflow: hidden;
        }

        .items thead th {
            background: #f9fafb;
            border-bottom: 1px solid #e5e7eb;
            padding: 10px;
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: .6px;
            color: #6b7280;
            font-weight: 900;
        }

        .items tbody td {
            border-bottom: 1px solid #eef2f7;
            padding: 10px;
            font-size: 12px;
            vertical-align: top;
        }

        .num {
            text-align: right;
            white-space: nowrap;
        }

        /* Totales */
        .totals {
            margin-top: 10px;
            width: 100%;
            border-collapse: collapse;
        }

        .totals td {
            padding: 10px;
            font-weight: 900;
            border-top: 2px solid #111827;
            font-size: 13px;
        }

        .totals td:last-child {
            text-align: right;
        }

        /* Footer */
        .footer {
            margin-top: 12px;
            font-size: 10px;
            color: #6b7280;
            text-align: center;
        }

        /* ============================================
       IMAGEN FOOTER: SIEMPRE 1 PÁGINA
       - Reservamos espacio fijo (contenedor)
       - La imagen se ajusta dentro sin crecer
    ============================================ */

        .footer-image-wrap {
            margin-top: 12px;
            width: 100%;
            height: 140px;
            /* ✅ AJUSTA AQUÍ: reserva espacio fijo */
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            overflow: hidden;
            page-break-inside: avoid;
            /* evita corte dentro del contenedor */
        }

        .footer-image-wrap img {
            width: 100%;
            height: 140px;
            /* mismo que el contenedor */
            object-fit: contain;
            /* ✅ encaja sin salirse */
            display: block;
        }

        /* Evita que dompdf intente partir secciones críticas */
        .avoid-break {
            page-break-inside: avoid;
        }

        .logo-wrap {
            width: 100%;
            height: 90px;
            /* altura controlada */
            overflow: hidden;
        }

        .logo-img {
            width: 100%;
            height: 90px;
            object-fit: contain;
            /* ✅ no distorsiona */
            display: block;
        }

        .footer-image-wrap {
            margin-top: 12px;
            width: 100%;
            height: 120px;
            /* ✅ para 5 filas */
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            overflow: hidden;
            page-break-inside: avoid;
        }

        .footer-img {
            width: 100%;
            height: 120px;
            object-fit: contain;
            /* ✅ no distorsiona */
            display: block;
            background: #fff;
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
                    <div class="logo-wrap">
                        @if (!empty($agencyInfo->agency_logo))
                            <img class="logo-img" src="{{ public_path('storage/' . $agencyInfo->agency_logo) }}"
                                alt="Logo">
                        @else
                            <div class="muted"
                                style="height:90px; display:flex; align-items:center; justify-content:center;">
                                LOGO
                            </div>
                        @endif
                    </div>
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

    @if (!empty($agencyInfo->invoice_footer_image) && !empty($agencyInfo->invoice_footer_enabled))
        <div class="footer-image-wrap avoid-break">
            <img class="footer-img" src="{{ public_path('storage/' . $agencyInfo->invoice_footer_image) }}"
                alt="Footer Image">
        </div>
    @endif





    <div class="small-footer">
        {{ $agencyInfo->agency_name ?? '' }} • {{ date('Y') }}
    </div>

</body>

</html>
