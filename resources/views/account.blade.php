{{-- resources/views/account.blade.php --}}
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Account · Plan</title>

    <link rel="stylesheet" href="{{ asset('css/variables.css') }}">
    <link rel="stylesheet" href="{{ asset('css/dash.css') }}">
    <link rel="stylesheet" href="{{ asset('css/main.css') }}">
</head>

<body>

    <div class="account-wrapper">

        <h1>Agency {{ $agency->agency_code }}</h1>

        <h2>Plan: {{ $plan->account_type }}</h2>

        <hr>

        <h3>Mensajes SMS</h3>

        <p><b>Twilio Number:</b> {{ $twilioNumber }}</p>
        <p><b>Enviados HOY:</b> {{ $dailySmsCount }}</p>
        <p><b>Enviados este mes:</b> {{ $monthlySmsCount }} / {{ $smsLimit }}</p>

        @if ($isSmsOverLimit)
            <div style="color:red;font-weight:bold;">
                ⚠ Has excedido tu límite mensual de mensajes.
            </div>
        @endif

        <hr>

        <h3>Documentos</h3>
        <p><b>Subidos este mes:</b> {{ $monthlyDocCount }} / {{ $docLimit }}</p>

        @if ($isDocsOverLimit)
            <div style="color:red;font-weight:bold;">
                ⚠ Has excedido el límite mensual de documentos.
            </div>
        @endif

        <hr>

        <h3>Usuarios</h3>
        <p><b>Usuarios creados:</b> {{ $totalUsers }} / {{ $userLimit }}</p>

        @if ($isUserOverLimit)
            <div style="color:red;font-weight:bold;">
                ⚠ Límite de usuarios alcanzado.
            </div>
        @endif



    </div>

</body>

</html>
