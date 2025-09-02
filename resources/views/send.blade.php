<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Enviar WhatsApp</title>
    <link rel="icon" href="img/favicon.png">

    <!-- Styles compartidos -->
    <link rel="stylesheet" href="{{ asset('css/variables.css') }}">
    <link rel="stylesheet" href="{{ asset('css/dash.css') }}">
    <link rel="stylesheet" href="{{ asset('css/main.css') }}">
    <link rel="stylesheet" href="{{ asset('css/dropdown.css') }}">
    <link rel="stylesheet" href="{{ asset('css/graph.css') }}">
    <link rel="stylesheet" href="{{ asset('css/editCustomer.css') }}">
    <link rel="stylesheet" href="{{ asset('css/ui_elements.css') }}">
    <link rel="stylesheet" href="{{ asset('css/send.css') }}"> <!-- Nuevo -->

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

                <div class="send-container">
                    <div class="send-card">
                        <h1>📤 Enviar WhatsApp</h1>

                        <div class="send-actions">
                            <a href="{{ route('inbox') }}">📥 Ir al Inbox</a>
                        </div>

                        <!-- Mensajes de éxito -->
                        @if(session('ok'))
                            <div class="alert-success">
                                {{ session('ok') }}
                            </div>
                        @endif

                        <!-- Mensajes de error -->
                        @if($errors->any())
                            <div class="alert-error">
                                <ul>
                                    @foreach($errors->all() as $e)
                                        <li>{{ $e }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <!-- Formulario -->
                        <form method="POST" action="{{ route('send.action') }}" class="send-form">
                            @csrf
                            <div class="form-group">
                                <label for="to">Destino</label>
                                <input type="text" id="to" name="to" placeholder="+52XXXXXXXXXX" value="{{ old('to') }}">
                            </div>

                            <div class="form-group">
                                <label for="body">Mensaje</label>
                                <textarea id="body" name="body" rows="4">{{ old('body') }}</textarea>
                            </div>

                            <button type="submit" class="btn-send">Enviar</button>
                        </form>
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
</body>
</html>
