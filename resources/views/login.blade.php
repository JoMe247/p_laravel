<?php
    session_start(); // Start the session

    // Check if the user session is active by verifying if 'user_id' is set
    if (!isset($_SESSION['user_id'])) {
        // If not set, redirect to login page
        
    }else{
        header("Location: dash.php");
        exit(); // Exit to prevent the rest of the page from loading
    }

    // If session is active, continue displaying the dashboard content
?>

<?php

// require 'db_connection.php';

// Check if user session is already active
if (!isset($_SESSION['user_id'])) {
    // If there's no session, check for a "remember me" cookie
    if (isset($_COOKIE['rememberme_token'])) {
        $token = $_COOKIE['rememberme_token'];

        // Retrieve the token from the database
        $stmt = $pdo->prepare('SELECT user_id FROM user_tokens WHERE token = :token AND expires_at > NOW()');
        $stmt->execute(['token' => $token]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($row) {
            // Token is valid, log the user in
            $stmt = $pdo->prepare('SELECT id, username FROM users WHERE id = :user_id');
            $stmt->execute(['user_id' => $row['user_id']]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($user) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                header("Location: dash.php");
                exit();
            }
        } else {
            // If the token is invalid, clear the cookie
            setcookie("rememberme_token", "", time() - 3600, "/"); // Expire the cookie
        }
    }

    // If no session and no valid token, redirect to login page
    if (!isset($_SESSION['user_id'])) {
        // header("Location: login.php");
        // exit;
    }
}
// If session is active, continue displaying the dashboard
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <!-- Icon -->
    <link rel="icon" href="img/favicon.png">

    <meta name="theme-color" content="#a60853">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>

    <!-- Styles -->
    <link rel="stylesheet" href="css/login.css?v0.1">

    <!-- Icons -->
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>

    <!-- Jquery -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
</head>
<body>

    <div class="wrapper">
        <form action="operations/sess/login.php" method="POST">
            <h1><img src="img/logo-white.png" alt=""></h1>
            <div class="input-box">
                <input type="text" name="username_or_email" placeholder="Username or Email" required autocomplete="one-time-code">
                <i class='bx bxs-user'></i>
            </div>
            <div class="input-box">
                <input type="password" name="password" placeholder="Password" required autocomplete="one-time-code">
                <i class='bx bxs-lock-alt'></i>
            </div>
            <div class="remember-forgot">
                <label><input type="checkbox" name="remember_me"><e>Remember me</e></label>
                <a href="operations/sess/request_reset.php">Forgot password?</a>
            </div>
            <button type="submit" class="btn">Login</button>
        </form>        
    </div>
    
</body>
</html>