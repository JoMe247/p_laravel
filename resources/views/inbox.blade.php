<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inbox WhatsApp</title>
    <link rel="icon" href="img/favicon.png">

    <!-- Styles -->
    <link rel="stylesheet" href="{{ asset('css/variables.css') }}">
    <link rel="stylesheet" href="{{ asset('css/dash.css') }}">
    <link rel="stylesheet" href="{{ asset('css/main.css') }}">
    <link rel="stylesheet" href="{{ asset('css/dropdown.css') }}">
    <link rel="stylesheet" href="{{ asset('css/graph.css') }}">
    <link rel="stylesheet" href="{{ asset('css/editCustomer.css') }}">
    <link rel="stylesheet" href="{{ asset('css/ui_elements.css') }}">
    <link rel="stylesheet" href="{{ asset('css/inbox.css') }}">

    <!-- Icons -->
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
</head>

<body>
    <div id="main-container">
        @include('menu')

        <section id="dash">
            <div id="lower-table-clients" type="fullscreen">
                <div class="inbox-container mt-10">
                    <div class="inbox-card">
                        <h1>📥 Inbox WhatsApp</h1>

                        <div class="inbox-actions">
                            <a href="{{ route('sent') }}" class="btn btn-primary">📤 Ir a Enviar</a>

                            <form method="POST" action="{{ route('inbox.sync') }}">
                                @csrf
                                <button type="submit" class="btn btn-secondary">🔄 Sincronizar</button>
                            </form>

                            <button type="button" class="btn btn-danger" onclick="bulkDelete()">🗑️ Eliminar
                                seleccionados</button>
                        </div>

                        <!-- 🔍 Cuadro de búsqueda -->
                        <div class="search-bar">
                            <input type="text" id="searchInput" placeholder="Buscar por fecha, número o mensaje...">
                        </div>

                        @if (session('ok'))
                            <div class="alert alert-success">{{ session('ok') }}</div>
                        @endif

                        @if ($errors->any())
                            <div class="alert alert-error">
                                <ul>
                                    @foreach ($errors->all() as $e)
                                        <li>{{ $e }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <!-- Form oculto para borrado múltiple -->
                        <form id="bulkDeleteForm" method="POST" action="{{ route('inbox.deleteMultiple') }}"
                            style="display:none;">
                            @csrf
                            @method('DELETE')
                            <div id="bulk-hidden-inputs"></div>
                        </form>

                        <div class="overflow-x-auto">
                            <table class="inbox-table" id="inboxTable">
                                <thead>
                                    <tr>
                                        <th><input type="checkbox" id="select-all"></th>
                                        <th>Fecha</th>
                                        <th>De</th>
                                        <th>Dirección</th>
                                        <th>Estado</th>
                                        <th>Mensaje</th>
                                        <th>Responder</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($messages as $m)
                                        <tr>
                                            <td><input type="checkbox" class="row-check" value="{{ $m->id }}">
                                            </td>
                                            <td>{{ $m->date_sent?->format('Y-m-d H:i') }}</td>
                                            <td>{{ $m->from }}</td>
                                            <td>
                                                <span
                                                    class="badge {{ $m->direction_label === 'Entrante' ? 'badge-in' : 'badge-out' }}">
                                                    {{ $m->direction_label }}
                                                </span>
                                            </td>
                                            <td>
                                                <span
                                                    class="badge
                                                @if ($m->status_label === 'Entregado') badge-success
                                                @elseif($m->status_label === 'En cola') badge-warning
                                                @elseif($m->status_label === 'No entregado' || $m->status_label === 'Fallido') badge-error
                                                @else badge-default @endif">
                                                    {{ $m->status_label }}
                                                </span>
                                            </td>
                                            <td class="message-body">{{ $m->body }}</td>

                                            <!-- RESPONDER -->
                                            <td>
                                                @if ($m->direction_label === 'Entrante' && $m->date_sent)
                                                    @php
                                                        // obtener la fecha del mensaje y convertirla a la zona configurada en app.php
                                                        $receivedAt = \Carbon\Carbon::parse($m->date_sent)->setTimezone(
                                                            config('app.timezone'),
                                                        );
                                                        $diffHours = $receivedAt->diffInHours(
                                                            \Carbon\Carbon::now(config('app.timezone')),
                                                        );
                                                    @endphp

                                                    @if ($diffHours < 24)
                                                        <!-- Dentro de 24 horas: mostrar formulario normal -->
                                                        <form method="POST" action="{{ route('send.action') }}"
                                                            class="reply-form">
                                                            @csrf
                                                            <input type="hidden" name="to"
                                                                value="{{ $m->from }}">
                                                            <textarea name="body" rows="2" placeholder="Escribe tu respuesta..." class="reply-textarea"></textarea>
                                                            <button type="submit" class="btn btn-send">Enviar</button>
                                                        </form>
                                                    @else
                                                        <!-- Después de 24 horas: mostrar botón WhatsApp Web -->
                                                        <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $m->from) }}"
                                                            target="_blank" class="btn btn-whatsapp">
                                                            <i class='bx bxl-whatsapp'></i> Responder en WhatsApp
                                                        </a>
                                                    @endif
                                                @else
                                                    <span class="no-reply">—</span>
                                                @endif
                                            </td>

                                            <!-- ACCIONES -->
                                            <td>
                                                <form method="POST" action="{{ route('inbox.delete', $m->id) }}"
                                                    onsubmit="return confirm('¿Seguro que quieres eliminar este mensaje?');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-danger">🗑️</button>
                                                </form>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="8" class="text-center">No hay mensajes</td>
                                        </tr>
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

    <!-- Scripts -->
    <script src="{{ asset('js/dropdown.js') }}"></script>
    <script src="{{ asset('js/menu.js') }}"></script>
    <script src="{{ asset('js/table.js') }}"></script>
    <script src="{{ asset('js/settings.js') }}"></script>
    <script src="{{ asset('js/operations.js') }}"></script>
    <script src="{{ asset('js/inbox.js') }}"></script>
</body>

</html>
