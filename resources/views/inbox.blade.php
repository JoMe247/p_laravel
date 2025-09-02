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

    <!-- Jquery -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
</head>

<body>
    <div id="main-container">

        <!-- Menu lateral -->
        @include('menu')

        <section id="dash">

            <div id="lower-table-clients" type="fullscreen">

                <div class="inbox-container mt-10">
                    <div class="inbox-card">
                        <h1>📥 Inbox WhatsApp</h1>

                        <div class="inbox-actions">
                            <a href="{{ route('send.form') }}" class="btn btn-primary">📤 Ir a Enviar</a>

                            <form method="POST" action="{{ route('inbox.sync') }}">
                                @csrf
                                <button type="submit" class="btn btn-secondary">🔄 Sincronizar</button>
                            </form>
                        </div>

                        <!-- Formulario para eliminar múltiples -->
                        <form id="delete-form" method="POST" action="{{ route('inbox.deleteMultiple') }}">
                            @csrf
                            @method('DELETE')

                            <button type="submit" class="btn btn-danger" style="margin:10px 0;">
                                🗑️ Eliminar seleccionados
                            </button>

                            <div class="overflow-x-auto">
                                <table class="inbox-table">
                                    <thead>
                                        <tr>
                                            <th><input type="checkbox" id="select-all"></th>
                                            <th>Fecha</th>
                                            <th>De</th>
                                            <th>Para</th>
                                            <th>Dirección</th>
                                            <th>Estado</th>
                                            <th>Mensaje</th>
                                            <th>Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($messages as $m)
                                            <tr>
                                                <td>
                                                    <input type="checkbox" name="messages[]" value="{{ $m->id }}">
                                                </td>
                                                <td>{{ $m->date_sent?->format('Y-m-d H:i') }}</td>
                                                <td>{{ $m->from }}</td>
                                                <td>{{ $m->to }}</td>
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
                                                <td>{{ $m->body }}</td>
                                                <td class="actions">
                                                    <!-- Botón de eliminar individual -->
                                                    <form method="POST" action="{{ route('inbox.delete', $m->id) }}"
                                                        onsubmit="return confirm('¿Seguro que quieres eliminar este mensaje?');" style="display:inline-block;">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-danger">🗑️</button>
                                                    </form>

                                                    @if($m->direction_label === 'Entrante')
                                                        <!-- Botón para responder -->
                                                        <button type="button" class="btn btn-success" onclick="window.location='./send'">
                                                            💬 Responder
                                                        </button>
                                                    @endif
                                                </td>
                                            </tr>
                                            @if($m->direction_label === 'Entrante')
                                                <tr id="reply-form-{{ $m->id }}" style="display:none;">
                                                    <td colspan="8">
                                                        <form method="POST" action="{{ route('send.action') }}">
                                                            @csrf
                                                            <input type="hidden" name="to" value="{{ $m->from }}">
                                                            <div style="display:flex; gap:10px; align-items:center;">
                                                                <textarea name="body" rows="2" placeholder="Escribe tu respuesta..." class="form-control" style="flex:1;"></textarea>
                                                                <button type="submit" class="btn btn-primary">📤 Enviar</button>
                                                            </div>
                                                        </form>
                                                    </td>
                                                </tr>
                                            @endif
                                        @empty
                                            <tr>
                                                <td colspan="8" class="text-center">No hay mensajes</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </form>

                        <div class="mt-4">
                            {{ $messages->links() }}
                        </div>
                    </div>
                </div>

                <!-- Scripts -->
                <script>
                    // Seleccionar/Deseleccionar todos
                    document.getElementById('select-all').addEventListener('change', function() {
                        const checkboxes = document.querySelectorAll('input[name="messages[]"]');
                        checkboxes.forEach(cb => cb.checked = this.checked);
                    });

                    // Mostrar/Ocultar formulario de respuesta
                    function toggleReplyForm(id) {
                        const row = document.getElementById('reply-form-' + id);
                        row.style.display = row.style.display === 'none' ? '' : 'none';
                    }
                </script>

                <script src="{{ asset('js/dropdown.js') }}"></script>
                <script src="{{ asset('js/menu.js') }}"></script>
                <script src="{{ asset('js/table.js') }}"></script>
                <script src="{{ asset('js/settings.js') }}"></script>
                <script src="{{ asset('js/operations.js') }}"></script>

</body>
</html>
