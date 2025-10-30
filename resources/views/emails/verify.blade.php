<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Verify Your Email</title>
    <style>
        body { font-family: Arial, sans-serif; background:#f8f8f8; padding:20px; }
        .container { background:#fff; border-radius:8px; padding:30px; max-width:520px; margin:0 auto; box-shadow:0 2px 10px rgba(0,0,0,.1); }
        h2 { color:#a60853; text-align:center; }
        p { color:#333; font-size:15px; line-height:1.5; }
        .btn { display:inline-block; background:#a60853; color:#fff !important; text-decoration:none; padding:12px 20px; border-radius:6px; margin-top:16px; }
        .footer { font-size:12px; color:#777; margin-top:26px; text-align:center; }
    </style>
</head>
<body>
    <div class="container">
        <h2>Verify Your Email Address</h2>
        <p>Hello {{ $user->username }},</p>
        <p>Please verify your email address by clicking the button below:</p>
        <p style="text-align:center;">
            <a href="{{ $verificationUrl }}" class="btn">Verify Email</a>
        </p>
        <p>If the button doesn’t work, copy and paste this link into your browser:</p>
        <p><a href="{{ $verificationUrl }}">{{ $verificationUrl }}</a></p>
        <div class="footer">
            <p>© {{ date('Y') }} Mensajería Twilio. All rights reserved.</p>
        </div>
    </div>
</body>
</html>
