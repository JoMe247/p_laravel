<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Verificación de correo</title>
    <link rel="stylesheet" href="{{ asset('css/login.css') }}">
</head>
<body>
    <div class="wrapper">
        <h2 style="color: {{ $success ? 'green' : 'red' }}">{{ $message }}</h2>
        @if($success)
            <a href="{{ route('login') }}" class="btn">Iniciar sesión</a>
        @endif
    </div>
</body>
</html>
