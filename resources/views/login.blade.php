<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#a60853">
    <title>Login</title>

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

        {{-- Mensajes de verificación de correo --}}
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

        <form action="{{ url('/login') }}" method="POST">
            @csrf
            
            <h1><img src="{{ asset('img/logo-white.png') }}" alt=""></h1>

            {{-- Mensajes de error --}}
            @if ($errors->any())
                <div class="alert" style="color: red; text-align:center;">
                    {{ $errors->first() }}
                </div>
            @endif

            <div class="input-box">
                <input type="text" name="username_or_email" placeholder="Username or Email" value="{{ old('username_or_email') }}" required autocomplete="username">
                <i class='bx bxs-user'></i>
            </div>

            <div class="input-box">
                <input type="password" name="password" placeholder="Password" required autocomplete="current-password">
                <i class='bx bxs-lock-alt'></i>
            </div>

            <div class="remember-forgot">
                <label><input type="checkbox" name="remember_me"><e>Remember me</e></label>
                <a href="{{ route('password.request') }}">Forgot password?</a>
            </div>

            <button type="submit" class="btn">Login</button>
        </form>        
    </div>

</body>
</html>
