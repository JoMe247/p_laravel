<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8" />
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="base-url" content="{{ url('/') }}">
    <title>Payments</title>

    <link rel="stylesheet" href="{{ asset('css/variables.css') }}">
    <link rel="stylesheet" href="{{ asset('css/payments.css') }}">
</head>
<body>

    <div class="payments-wrapper">
        <a class="btn-invoices" href="{{ route('invoices', ['customerId' => $customerId]) }}">
            Invoices
        </a>

        <div class="payments-card">
            <h2>Payments</h2>
            <p>Esta vista queda lista como puente hacia Invoices.</p>
        </div>
    </div>

</body>
</html>
