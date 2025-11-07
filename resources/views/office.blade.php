{{-- resources/views/office.blade.php --}}
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Office · Sub Users</title>
    <link rel="icon" href="{{ asset('img/favicon.png') }}">

    <link rel="stylesheet" href="{{ asset('css/variables.css') }}">
    <link rel="stylesheet" href="{{ asset('css/dash.css') }}">
    <link rel="stylesheet" href="{{ asset('css/main.css') }}">
    <link rel="stylesheet" href="{{ asset('css/dropdown.css') }}">
    <meta name="csrf-token" content="{{ csrf_token() }}">
</head>
<body class="dark">

<div class="container" style="max-width: 680px; margin:40px auto;">
    <h2 style="margin-bottom:16px;">Crear Sub-User</h2>

    @if(session('success'))
        <div class="alert success">{{ session('success') }}</div>
    @endif

    @if ($errors->any())
        <div class="alert error">
            <ul style="margin:0;padding-left:18px;">
                @foreach ($errors->all() as $e)
                    <li>{{ $e }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('office.store') }}" class="card" style="padding:18px;border-radius:12px;">
        @csrf

        <div class="form-row">
            <label>Username</label>
            <input type="text" name="username" value="{{ old('username') }}" required>
        </div>

        <div class="form-row">
            <label>Nombre</label>
            <input type="text" name="name" value="{{ old('name') }}" required>
        </div>

        <div class="form-row">
            <label>Email</label>
            <input type="email" name="email" value="{{ old('email') }}" required>
        </div>

        <div class="form-row">
            <label>Password</label>
            <input type="password" name="password" required>
        </div>

        {{-- Sólo informativos (heredados) --}}
        @isset($agency)
            <div class="form-row">
                <label>Agencia</label>
                <input type="text" value="{{ $agency }}" disabled>
            </div>
        @endisset

        @isset($twilio)
            <div class="form-row">
                <label>Twilio From</label>
                <input type="text" value="{{ $twilio }}" disabled>
            </div>
        @endisset

        <div style="margin-top:14px; display:flex; gap:10px;">
            <button type="submit" class="btn primary">Crear</button>
            <a href="{{ route('dashboard') }}" class="btn">Volver</a>
        </div>
    </form>
</div>

</body>
</html>
