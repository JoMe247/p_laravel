<!-- resources/views/register.blade.php -->
<!DOCTYPE html>
<html lang="en">
<head>
    <link rel="icon" href="{{ asset('img/favicon.png') }}">
    <meta name="theme-color" content="#a60853">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register</title>

    <link rel="stylesheet" href="{{ asset('css/login.css?v0.2') }}">
    <link rel="stylesheet" href="{{ asset('css/variables.css') }}">

    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
</head>
<body>

    <div class="wrapper">
        <form id="registrationForm" method="POST" action="{{ route('register') }}">
            @csrf
            <h1><img src="{{ asset('img/logo-white.png') }}" alt=""></h1>
            <div class="input-box">
                <input type="text" name="username" placeholder="Username" required autocomplete="off">
                <i class='bx bxs-user'></i>
            </div>
            <div class="input-box">
                <input type="email" name="email" placeholder="Email" required autocomplete="off">
                <i class='bx bxs-envelope'></i>
            </div>
            <div class="input-box" id="password-box">
                <input type="password" name="password" placeholder="Password" required autocomplete="new-password">
                <i class='bx bxs-lock-alt'></i>
            </div>

            <div id="responseMessage" class="response-message"></div>
            <button type="submit" class="btn">Register</button>
            <div class="register-link">
                <p>Already have an account? <a href="{{ route('login') }}">Login here</a></p>
            </div>
        </form>
    </div>

    <script>
        document.getElementById("registrationForm").addEventListener("submit", async function(event) {
            event.preventDefault();

            const formData = new FormData(this);
            const responseMessage = document.getElementById("responseMessage");

            try {
                const response = await fetch("{{ route('register') }}", {
                    method: "POST",
                    headers: {
                        "X-CSRF-TOKEN": document.querySelector('input[name="_token"]').value,
                        "Accept": "application/json"
                    },
                    body: formData
                });

                const data = await response.json();

                if (data.success) {
                    responseMessage.style.color = "green";
                    responseMessage.textContent = data.message;
                } else {
                    responseMessage.style.color = "white";
                    responseMessage.textContent = data.message || "Error al registrar";
                    responseMessage.setAttribute("shake","on");
                    setTimeout(() => responseMessage.removeAttribute("shake"), 800);
                }

            } catch (error) {
                console.error(error);
                responseMessage.textContent = "Error interno, intente de nuevo.";
            }
        });
    </script>

</body>
</html>
