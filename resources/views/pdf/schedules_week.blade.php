<!doctype html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <style>
        @page {
            margin: 22px 22px 18px 22px;
        }

        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 11px;
            color: #0f172a;
        }

        /* ===== Header ===== */
        .header {
            position: relative;
            height: 120px;
            margin-bottom: 16px;
        }

        .agency-logo {
            width: 110px;
            /* ajusta si lo quieres más grande */
            height: auto;
            display: block;
        }

        .range-box {
            position: absolute;
            right: 0;
            top: 34px;
            text-align: right;
        }

        .range-box .range {
            font-size: 14px;
            font-weight: 900;
            color: #111;
        }

        .range-box .agency {
            margin-top: 3px;
            font-size: 11px;
            color: #64748b;
            font-weight: 700;
        }

        /* ===== Table ===== */
        table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
        }

        th,
        td {
            border: 1px solid #111;
            padding: 0;
            vertical-align: top;
        }

        thead th {
            background: #111;
            color: #fff;
            font-weight: 900;
            font-size: 14px;
            letter-spacing: .4px;
            text-transform: uppercase;
            text-align: center;
            padding: 10px 6px;
        }

        thead th.name-col {
            width: 170px;
            text-align: left;
            padding-left: 12px;
        }

        /* altura de filas (clave para que NO quede aplastado) */
        tbody td {
            height: 64px;
        }

        /* nombre */
        .nameCell {
            padding: 10px 12px;
        }

        .nameCell .n {
            font-size: 14px;
            color: #0f172a;
            font-family: 'Montserrat';
            font-weight: normal;
            font-style: normal;
            src: url("{{ storage_path('fonts/montserrat/Montserrat-Regular.ttf') }}") format('truetype');
        }

        .nameCell .t {
            margin-top: 2px;
            font-size: 10px;
            color: #64748b;
            font-family: 'Montserrat';
            font-weight: normal;
            font-style: normal;
            src: url("{{ storage_path('fonts/montserrat/Montserrat-Regular.ttf') }}") format('truetype');
        }

        /* ===== Shift cell ===== */
        .shiftWrap {
            height: 64px;
            padding: 8px 8px 8px 12px;
            box-sizing: border-box;
            border-left: 10px solid #94a3b8;
            /* color del shift */
            position: relative;
        }

        /* texto de hora "9a - 5:30p" */
        .shiftTime {

            font-size: 14px;
            color: #0f172a;
            line-height: 1.15;
            margin-top: 2px;
            font-family: 'Montserrat';
            font-weight: normal;
            font-style: normal;
            src: url("{{ storage_path('fonts/montserrat/Montserrat-Regular.ttf') }}") format('truetype');
        }

        /* etiqueta abajo (csr) */
        .shiftTag {
            margin-top: 4px;
            font-size: 12px;
            color: #64748b;
            text-transform: lowercase;
            font-family: 'Montserrat';
            font-weight: normal;
            font-style: normal;
            src: url("{{ storage_path('fonts/montserrat/Montserrat-Regular.ttf') }}") format('truetype');
        }

        /* cuando no hay shift */
        .empty {
            border-left-color: #e5e7eb;
            color: #94a3b8;
        }

        /* OFF */
        .off {
            color: #111;
        }

        @font-face {
            font-family: 'Montserrat';
            font-weight: normal;
            font-style: normal;
            src: url("{{ storage_path('fonts/montserrat/Montserrat-Regular.ttf') }}") format('truetype');
        }

        @font-face {
            font-family: 'Montserrat';
            font-weight: bold;
            font-style: normal;
            src: url("{{ storage_path('fonts/montserrat/Montserrat-Bold.ttf') }}") format('truetype');
        }

        body {
            font-family: 'Montserrat', sans-serif;
        }
    </style>
</head>

<body>

    <div class="header">
        <div class="logo-box">
            @if ($logoBase64)
                <img src="{{ $logoBase64 }}" class="agency-logo">
            @endif

        </div>

        <div class="range-box">
            <div class="range">{{ $start->format('M d, Y') }} - {{ $end->format('M d, Y') }}</div>
            <div class="agency">Agency: {{ $agency }}</div>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th class="name-col">NAME</th>
                @foreach ($days as $d)
                    <th>{{ strtoupper($d->format('D')) }}<br>{{ $d->format('d') }}</th>
                @endforeach
            </tr>
        </thead>

        <tbody>
            @foreach ($rows as $r)
                <tr>
                    <td class="nameCell">
                        <div class="n">{{ $r['name'] }}</div>
                    </td>

                    @foreach ($days as $d)
                        @php
                            $dk = $d->toDateString();
                            $cell = $r['cells'][$dk] ?? ['text' => '', 'color' => '#e5e7eb'];

                            // convertir "7:00 am - 1:30 pm" => "7a - 1:30p"
                            $raw = trim($cell['text'] ?? '');
                            $pretty = $raw;

                            $convert = function ($t) {
                                $t = preg_replace('/\s+/', ' ', trim($t));
                                // 7:00 am -> 7a
                                $t = preg_replace('/\b(\d{1,2}):00\s*(am|pm)\b/i', '$1$2', $t);
                                // 1:30 pm -> 1:30p
                                $t = preg_replace('/\b(\d{1,2}:\d{2})\s*(am|pm)\b/i', '$1$2', $t);
                                // am/pm -> a/p
                                $t = preg_replace('/\b(am)\b/i', 'a', $t);
                                $t = preg_replace('/\b(pm)\b/i', 'p', $t);
                                // limpia espacios
                                return preg_replace('/\s+/', ' ', trim($t));
                            };

                            // si es OFF, no convertimos horas
                            $isOffText = str_starts_with($raw, 'OFF') || str_contains($raw, 'OFF -');
                            if (!$isOffText && str_contains($raw, '-')) {
                                $pretty = $convert($raw);
                            }

                            $color = $cell['color'] ?? '#94a3b8';
                            $hasShift = trim($raw) !== '';
                        @endphp

                        <td>
                            @if ($hasShift)
                                <div class="shiftWrap {{ $isOffText ? 'off' : '' }}"
                                    style="border-left-color: {{ $color }};">
                                    <div class="shiftTime">{{ $pretty }}</div>
                                    {{-- etiqueta (si luego agregas role en DB, aquí la imprimes). Por ahora fijo --}}
                                    <div class="shiftTag">csr</div>
                                </div>
                            @else
                                <div class="shiftWrap empty">
                                    <div class="shiftTime">—</div>
                                    <div class="shiftTag">—</div>
                                </div>
                            @endif
                        </td>
                    @endforeach
                </tr>
            @endforeach
        </tbody>
    </table>

</body>

</html>
