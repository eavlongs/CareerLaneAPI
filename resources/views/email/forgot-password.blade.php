<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Forgot Your Password</title>
</head>
<body>
    <p>Click the link below to reset your password:</p>
    <p>If you cannot click the link, copy and paste the following URL into your browser:</p>
    <a href="{{ $forgotPasswordUrl }}">{{ $forgotPasswordUrl }}</a> <!-- Show the plain text URL -->
    <p>If you did not request this, please ignore this email.</p>
</body>
</html>
