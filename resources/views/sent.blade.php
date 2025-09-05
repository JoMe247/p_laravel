<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Enviados WhatsApp</title>
    <link rel="icon" href="img/favicon.png">

    <!-- Styles -->
    <link rel="stylesheet" href="{{ asset('css/variables.css') }}">
    <link rel="stylesheet" href="{{ asset('css/dash.css') }}">
    <link rel="stylesheet" href="{{ asset('css/main.css') }}">
    <link rel="stylesheet" href="{{ asset('css/dropdown.css') }}">
    <link rel="stylesheet" href="{{ asset('css/graph.css') }}">
    <link rel="stylesheet" href="{{ asset('css/editCustomer.css') }}">
    <link rel="stylesheet" href="{{ asset('css/ui_elements.css') }}">
    <link rel="stylesheet" href="{{ asset('css/sent.css') }}">

    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
</head>

<body>
<div id="main-container">
    @include('menu')

    <section id="dash">
        <div id="lower-table-clients" type="fullscreen">
            <div class="sent-container mt-10">
                <div class="sent-card">
                    <h1>📨 Mensajes Enviados</h1>

                    <div class="sent-actions">
                        <a href="{{ route('inbox') }}" class="btn btn-secondary">📥 Volver al Inbox</a>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="sent-table">
                            <thead>
                                <tr>
                                    <th>Fecha</th>
                                    <th>Para</th>
                                    <th>Estado</th>
                                    <th>Mensaje</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($messages as $m)
                                    <tr>
                                        <td>{{ $m->date_sent?->format('Y-m-d H:i') }}</td>
                                        <td>{{ $m->to }}</td>
                                        <td>
                                            <span class="badge
                                                @if ($m->status_label === 'Entregado') badge-success
                                                @elseif($m->status_label === 'En cola') badge-warning
                                                @elseif($m->status_label === 'No entregado' || $m->status_label === 'Fallido') badge-error
                                                @else badge-default @endif">
                                                {{ $m->status_label }}
                                            </span>
                                        </td>
                                        <td class="message-body">{{ $m->body }}</td>
                                    </tr>
                                @empty
                                    <tr><td colspan="4" class="text-center">No hay mensajes enviados</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-4">{{ $messages->links() }}</div>
                </div>
            </div>
        </div>
    </section>
</div>

<script src="{{ asset('js/dropdown.js') }}"></script>
<script src="{{ asset('js/menu.js') }}"></script>
<script src="{{ asset('js/table.js') }}"></script>
<script src="{{ asset('js/settings.js') }}"></script>
<script src="{{ asset('js/operations.js') }}"></script>
</body>
</html>
