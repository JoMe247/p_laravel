<!doctype html>
<html lang="es">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>SMS Inbox</title>
    <link rel="icon" href="img/favicon.png">

    <!-- Archivos CSS -->
    <link rel="stylesheet" href="{{ asset('css/variables.css') }}">
    <link rel="stylesheet" href="{{ asset('css/dash.css') }}">
    <link rel="stylesheet" href="{{ asset('css/main.css') }}">
    <link rel="stylesheet" href="{{ asset('css/dropdown.css') }}">
    <link rel="stylesheet" href="{{ asset('css/graph.css') }}">
    <link rel="stylesheet" href="{{ asset('css/editCustomer.css') }}">
    <link rel="stylesheet" href="{{ asset('css/ui_elements.css') }}">
    <link rel="stylesheet" href="{{ asset('css/inbox.css') }}">
    <link rel="stylesheet" href="{{ asset('css/sms-inbox.css') }}">

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
                        <h1>📥 SMS Inbox</h1>

                        <div class="sms-app">
                            <!-- Lista de contactos -->
                            <div class="sms-list">
                                <div class="top-actions">
                                    <button id="btnSync" class="btn secondary">Actualizar</button>
                                    <button id="btnDeleteSelected" class="btn danger" disabled>Eliminar
                                        seleccionadas</button>
                                    <label style="margin-left:auto;display:flex;align-items:center;gap:6px;">
                                        <input type="checkbox" id="checkAll"> Seleccionar todo
                                    </label>
                                    <input id="search" placeholder="Buscar..."
                                        style="margin-left:10px;padding:8px;border-radius:6px;border:1px solid #ddd" />
                                </div>

                                <div id="contacts">
                                    @forelse ($contacts as $c)
                                        <div class="sms-contact" data-contact="{{ $c['contact'] }}">
                                            <input type="checkbox" class="contact-check" value="{{ $c['contact'] }}"
                                                style="margin-right:8px;">
                                            <div class="meta" style="flex:1;">
                                                <div style="font-weight:600">{{ $c['contact'] }}</div>
                                                <div class="last">{{ Str::limit($c['last_body'], 60) }}</div>
                                            </div>
                                            <div style="font-size:12px;color:#999; margin-right:10px;">
                                                {{ $c['last_at'] ? \Carbon\Carbon::parse($c['last_at'])->diffForHumans() : '' }}
                                            </div>
                                        </div>
                                    @empty
                                        <div class="empty">No hay conversaciones. Presiona <strong>Sincronizar</strong>
                                            para leer mensajes desde Twilio.</div>
                                    @endforelse
                                </div>
                            </div>

                            <!-- Panel de chat -->
                            <div class="sms-chat">
                                <div
                                    style="padding:12px;border-bottom:1px solid #eee; display:flex;align-items:center; gap:12px;">
                                    <div id="currentContact" style="font-weight:700">Selecciona una conversación</div>
                                    <div style="margin-left:auto">
                                        <button type="button" id="btnDeleteCurrent" class="btn danger"
                                            style="margin-left:10px;">Eliminar conversación</button>
                                        <button id="openNew" class="btn secondary">Nuevo mensaje</button>
                                    </div>
                                </div>

                                <div class="messages" id="messagesPane">
                                    <div class="empty">Selecciona un contacto a la izquierda para ver el chat</div>
                                </div>

                                <div class="composer" id="composer" style="display:none;">
                                    <form id="sendForm" style="display:flex;width:100%;gap:8px;align-items:center;">
                                        @csrf
                                        <input type="hidden" name="to" id="toInput" />
                                        <textarea name="body" id="bodyInput" placeholder="Escribe un mensaje..."></textarea>
                                        <button type="submit" class="btn">Enviar</button>
                                    </form>
                                </div>
                            </div>
                        </div>

                        <!-- Variables JS globales -->
                        <script>
                            window.twilioFrom = "{{ $twilio }}";
                            window.csrfToken = "{{ csrf_token() }}";
                            window.routes = {
                                sync: "{{ route('sms.sync') }}",
                                send: "{{ route('sms.send') }}",
                                deleteOne: "{{ route('sms.deleteOne', ':contact') }}",
                                deleteMany: "{{ route('sms.deleteMany') }}"
                            };
                        </script>

                        <script src="{{ asset('js/sms-inbox.js') }}"></script>
                    </div>
                </div>
            </div>
        </section>
    </div>
</body>

</html>
