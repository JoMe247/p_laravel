<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#a60853">
    <title>Recuperar Contraseña</title>

     <!-- Icon -->
    <link rel="icon" href="{{ asset('img/favicon.png') }}">

    <!-- Styles -->
    <link rel="stylesheet" href="{{ asset('css/login.css?v0.1') }}">

    <!-- Icons -->
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>

    <!-- Jquery -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
</head>
<body>
    <div class="wrapper">
        @if(session('status'))
            <div class="alert success" style="color: green; text-align:center;">
                {{ session('status') }}
            </div>
        @endif
        @if(session('status_error'))
            <div class="alert error" style="color: red; text-align:center;">
                {{ session('status_error') }}
            </div>
        @endif

        <form action="{{ route('password.email') }}" method="POST">
            @csrf
            <h1><img src="{{ asset('img/logo-white.png') }}" alt="Logo"></h1>

            <p style="text-align:center; color:#fff; margin-bottom:10px;">
                Ingresa tu correo para restablecer tu contraseña.
            </p>

            <div class="input-box">
                <input type="email" name="email" placeholder="Correo electrónico" value="{{ old('email') }}" required>
                <i class='bx bxs-envelope'></i>
            </div>

            @if ($errors->any())
                <div class="alert" style="color: red; text-align:center;">
                    {{ $errors->first() }}
                </div>
            @endif

            <button type="submit" class="btn">Enviar enlace</button>
            <a href="{{ url('/login') }}" style="display:block;text-align:center;margin-top:10px;color:#fff;">Volver al login</a>
        </form>
    </div>
</body>
</html>
