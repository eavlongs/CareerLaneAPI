<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Your Password</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333333;
            margin: 0;
            padding: 0;
            background-color: #f0f7ff;
        }

        .container {
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }

        .header {
            background-color: rgb(59 130 246);
            color: #ffffff;
            text-align: center;
            padding: 20px;
            border-radius: 5px 5px 0 0;
        }

        .content {
            background-color: #ffffff;
            padding: 30px;
            border-radius: 0 0 5px 5px;
        }

        .button {
            display: inline-block;
            background-color: rgb(59 130 246);
            color: #ffffff;
            text-decoration: none;
            padding: 12px 20px;
            border-radius: 5px;
            margin-top: 20px;
            font-weight: bold;
        }

        .footer {
            text-align: center;
            margin-top: 20px;
            font-size: 12px;
            color: #666666;
        }

    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Reset Your Password</h1>
        </div>
        <div class="content">
            <p>Hello,</p>
            <p>We received a request to reset your password for your account at {{ $appName }}. If you didn't make this request, you can safely ignore this email.</p>
            <p>To reset your password, please click the button below:</p>
            <p style="text-align: center;">
                <a href="{{ $forgotPasswordUrl }}" class="button">Reset Password</a>
            </p>
            <p>This link will expire in 1 hour for security reasons. If you need a new password reset link, please visit our website and request it again.</p>
            <p>If you didn't request a password reset, please ignore this email<. /p>
                    <p>Best regards,<br>The {{ $appName }} Team</p>
        </div>
        <div class="footer">
            <p>This is an automated message, please do not reply to this email. If you need assistance, please contact our support team.</p>
        </div>
    </div>
</body>
</html>
