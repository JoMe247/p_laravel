<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <title>Invoice PDF</title>
    <style>
        @font-face {
            font-family: 'Montserrat';
            font-style: normal;
            font-weight: 400;
            /* Regular */
             src: url("{{ storage_path('fonts/montserrat/Montserrat-Regular.ttf') }}") format("truetype");
        }

        @font-face {
            font-family: 'Montserrat';
            font-style: normal;
            font-weight: 700;
            /* Bold */
            src: url("{{ storage_path('fonts/montserrat/Montserrat-Bold.ttf') }}") format("truetype");
        }

        /* =========================================================
   VARIABLES RÁPIDAS
   ✅ Cambia aquí tamaños y espacios globales
========================================================= */
        :root {
            /* (1) Tamaño base del documento */
            --font-base: 12px;

            /* (2) Tamaños clave */
            --title-size: 22px;
            /* "INVOICE" (si lo usas) */
            --total-size: 24px;
            /* total grande */
            --label-size: 10px;
            /* labels (Invoice#, Date...) */
            --table-head-size: 10px;
            /* header tabla */
            --table-row-size: 11.5px;
            /* filas tabla */
            --footer-size: 10px;
            /* footer abajo */

            /* (3) Colores */
            --text: #111827;
            --muted: #6b7280;
            --border: #e5e7eb;
            --border-soft: #eef2f7;
            --head-bg: #f9fafb;

            /* (4) Bordes y padding */
            --radius: 8px;
            --pad: 12px;

            /* ✅ alto del logo (no distorsiona) */
            --footer-img-h: 105px;
            /* ✅ alto imagen inferior (no distorsiona) */
        }

        /* =========================================================
   BASE
========================================================= */
        body {
            font-family: 'Montserrat', sans-serif;
            /* ✅ solo Montserrat */
            font-weight: 400;
        }

        body {
            margin: 0px;
            /* ✅ márgenes del PDF */
            color: var(--text);
            font-size: var(--font-base);
            /* ✅ cambia tamaño general */
            line-height: 1.25;
        }

        /* Utilidades */
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

        /* =========================================================
   TIPOGRAFÍA FORMAL
========================================================= */
        .h-invoice {
            font-size: var(--title-size);
            /* ✅ tamaño título */
            font-weight: 700;
            letter-spacing: .9px;
            margin: 0;
        }

        .label {
            font-size: var(--label-size);
            /* ✅ tamaño labels */
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .75px;
            color: var(--muted);
        }

        .value {
            font-weight: 700;
            color: var(--text);
        }

        /* =========================================================
   CAJAS / CARDS (FORMAL)
========================================================= */

        .card {
            padding: var(--pad);
            background: #fff;

        }

        /* Separadores de espacio (por si los usas) */
        .sp-10 {
            height: 50px;
        }

        .sp-14 {
            height: 14px;
        }

        /* =========================================================
   LOGO (NO DISTORSIÓN)
   ✅ Cambia el alto en --logo-h
========================================================= */
        .logo-wrap {
            width: 70%;
            overflow: hidden;
        }

        .logo-img {
            width: 100%;
            max-height: 100%;
            object-fit: contain;
        }

        /* =========================================================
   CAJA TOTAL (derecha)
========================================================= */
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
            /* ✅ tamaño total grande */
            font-weight: 700;
            margin: 0 0 10px 0;
        }

        /* Key/Value list */
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
            /* ✅ ancho labels */
            color: var(--muted);
            font-weight: 700;
        }

        .kv td:last-child {
            font-weight: 700;
        }

        /* =========================================================
   TABLA DE ITEMS (FORMAL / CLÁSICA)
========================================================= */
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

        /* =========================================================
   GRAND TOTAL (LÍNEA FINAL)
========================================================= */
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

        /* =========================================================
   IMAGEN INFERIOR (NO DISTORSIÓN / 1 PÁGINA)
   ✅ Alto fijo en --footer-img-h
========================================================= */
        .footer-image-wrap {

        }

        .footer-img {
            display: inline-block;
            /* clave para centrar */
            max-width: 100%;
            /* controla ancho */
            max-height: 300px;
            /* controla alto */
            object-fit: contain;
            /* NO distorsiona */
            background: #fff;
            position: absolute;
            bottom: 30px;
            left: 50%;
            transform: translateX(-50%);
            
        }

        /* =========================================================
   FOOTER PEQUEÑO
========================================================= */
        .pdf-fixed-footer {
            position: fixed;
            right: 28px;
            /* debe coincidir con margen derecho @page */
            bottom: 14px;
            /* separación del borde inferior */
            font-size: 10px;
            color: #6b7280;
            font-weight: 400;
            text-align: right;
            white-space: nowrap;
        }
    </style>

</head>

<body>

    {{-- TOP: Logo (izq) + Agency info (der) EN LA MISMA FILA --}}
    <table class="top-grid" style="width:100%; border-collapse:collapse;">
        <tr>
            <td style="width:100%; vertical-align:top;">

                <div class="card">
                    <table style="width:100%; border-collapse:collapse;">
                        <tr>
                            {{-- LOGO (izquierda) --}}
                            <td style="width:35%; vertical-align:middle;">
                                <div class="logo-wrap">
                                    @if (!empty($agencyInfo->agency_logo))
                                        <img class="logo-img"
                                            src="{{ public_path('storage/' . $agencyInfo->agency_logo) }}"
                                            alt="Logo">
                                    @else
                                        <div class="muted"
                                            style="height:90px; display:flex; align-items:center; justify-content:center;">
                                            LOGO
                                        </div>
                                    @endif
                                </div>
                            </td>

                            {{-- AGENCY INFO (derecha del logo) --}}
                            <td style="width:65%; vertical-align:top; padding-left:0px; font-size:20px;">
                                {{-- ✅ AQUÍ CAMBIAS LA SEPARACIÓN DEL TEXTO VS LOGO:
                                 padding-left: 10px (más pegado)
                                 padding-left: 24px (más separado) --}}
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



    {{-- MID: Invoice To (izq) + Totals/Invoice meta (der) --}}
    {{-- MID: Customer (izq) + Totals/Invoice meta (der) --}}
    {{-- MID: TOTAL + META (izq) | CUSTOMER (der) --}}
    <table class="mid-grid" style="width:100%; border-collapse:collapse; margin-top:14px;">
        <tr>

            {{-- IZQUIERDA: TOTAL + META --}}
            <td style="width:45%; vertical-align:top; padding-right:12px;">
                <div class="total-box" style="font-size: 12px">

                    <div class="total-title" style="font-size: 14px">Invoice total</div>
                    <div class="total-big">
                        ${{ number_format((float) ($grandTotal ?: 0), 2) }}
                    </div>

                    <table class="kv">
                        <tr>
                            <td>Next payment date</td>
                            <td class="bold">{{ $invoice->next_py_date ?? '' }}</td>
                        </tr>
                        <tr>
                            <td>Invoice #</td>
                            <td class="bold">{{ $invoice->invoice_number ?? '' }}</td>
                        </tr>
                        <tr>
                            <td>Date</td>
                            <td class="bold">{{ $invoice->creation_date ?? '' }}</td>
                        </tr>
                        <tr>
                            <td>Due date</td>
                            <td class="bold">{{ $invoice->payment_date ?? '' }}</td>
                        </tr>
                        <tr>
                            <td>Policy #</td>
                            <td class="bold">{{ $invoice->policy_number ?? '' }}</td>
                        </tr>
                        <tr>
                            <td>Sales agent</td>
                            <td class="bold">{{ $invoice->created_by_name ?? '' }}</td>
                        </tr>
                    </table>

                </div>
            </td>

            {{-- DERECHA: CUSTOMER --}}
            <td style="width:55%; vertical-align:top; padding-left:12px;">
                <div class="card right" style="font-size: 20px">
                    <div class="label" style="margin-bottom:6px; font-size:14px;">Invoice to</div>

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

    @if (!empty($agencyInfo->invoice_footer_image) && !empty($agencyInfo->invoice_footer_enabled))
        <div class="footer-image-wrap avoid-break">
            <img class="footer-img" src="{{ public_path('storage/' . $agencyInfo->invoice_footer_image) }}"
                alt="Footer Image">
        </div>
    @endif


    <div class="pdf-fixed-footer">
        {{ $agencyInfo->agency_name ?? '' }} • {{ date('Y') }}
    </div>


</body>

</html>
