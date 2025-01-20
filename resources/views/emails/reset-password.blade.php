<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Password Reset</title>
</head>
<body>
    <div style="text-align: center;">
        <img src="{{ $logo }}" alt="Logo" style="width: 150px; height: 75px;">
       
        <h1>Reset Your Password</h1>
        <p>You are receiving this email because we received a password reset request for your account.</p>
        <a href="{{ $url }}" style="display: inline-block; padding: 10px 20px; border-radius:4px; background-color: #4CAF50; color: white; text-decoration: none; margin-top: 20px;">Reset Password</a>
        <p>If you did not request a password reset, no further action is required.</p>
    </div>
</body>
</html>
