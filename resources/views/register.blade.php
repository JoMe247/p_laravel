<!DOCTYPE html>
<html lang="en">
<head>
    <!-- Icon -->
    <link rel="icon" href="img/favicon.png">
    <meta name="theme-color" content="#a60853">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register</title>

    <!-- Styles -->
    <link rel="stylesheet" href="css/login.css?v0.2">
    <link rel="stylesheet" href="css/variables.css">

    <!-- Icons -->
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>

    <!-- Jquery -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
</head>
<body>

    <div class="wrapper">
        <form id="registrationForm">
            <h1><img src="img/logo-white.png" alt=""></h1>
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
            <div id="responseMessage" class="response-message" "></div>
            <button type="submit" class="btn">Register</button>
            <div class="register-link">
                <p>Already have an account? <a href="login.php">Login here</a></p>
            </div>
            
        </form>
    </div>

    <!-- JavaScript for AJAX Registration -->
    <script>
        document.getElementById("registrationForm").addEventListener("submit", function(event) {
            event.preventDefault(); // Prevent form from submitting traditionally

            // Gather form data
            const formData = new FormData(this);

            // AJAX request to register.php
            fetch("operations/sess/register.php", {
                method: "POST",
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                const responseMessage = document.getElementById("responseMessage");
                if (data.success) {
                    responseMessage.style.color = "green";
                    responseMessage.textContent = data.message;
                    // Optionally redirect after registration or show a success state
                } else {
                    responseMessage.style.color = "white";
                    responseMessage.textContent = data.message;
                    responseMessage.setAttribute("shake","on");
                    setTimeout(function(){responseMessage.removeAttribute("shake")},800)
                }
            })
            .catch(error => {
                console.error("Error:", error);
                document.getElementById("responseMessage").textContent = "An error occurred. Please try again.";
            });
        });
    </script>

</body>
</html>
