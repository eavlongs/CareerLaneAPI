<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Verify Your Email</title>
</head>
<body>
    <p>Click the link below to verify your email:</p>
    <a href="{{ $verificationUrl }}" style="color: blue; text-decoration: underline;">Verify Email</a>
    <p>If you cannot click the link, copy and paste the following URL into your browser:</p>
    <p>{{ $verificationUrl }}</p> <!-- Show the plain text URL -->
    <p>If you did not request this verification, please ignore this email.</p>
</body>
</html>
