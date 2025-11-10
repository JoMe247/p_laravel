<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#a60853">
    <title>Nueva Contraseña</title>

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

        <form action="{{ route('password.update') }}" method="POST">
            @csrf
            <h1><img src="{{ asset('img/logo-white.png') }}" alt="Logo"></h1>

            <input type="hidden" name="token" value="{{ $token }}">

            <p style="text-align:center; color:#fff; margin-bottom:10px;">
                Ingresa tu nueva contraseña.
            </p>

            <div class="input-box">
                <input type="password" name="password" placeholder="Nueva contraseña" required>
                <i class='bx bxs-lock'></i>
            </div>

            <div class="input-box">
                <input type="password" name="password_confirmation" placeholder="Confirmar contraseña" required>
                <i class='bx bxs-lock-alt'></i>
            </div>

            @if ($errors->any())
                <div class="alert" style="color: red; text-align:center;">
                    {{ $errors->first() }}
                </div>
            @endif

            <button type="submit" class="btn">Guardar nueva contraseña</button>
        </form>
    </div>
</body>
</html>
