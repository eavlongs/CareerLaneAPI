<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Document</title>
</head>
<body>
    <p>Click the link below to verify your email</p>
    <a href="{{$verificationUrl}}">{{$verificationUrl}}</a>
</body>
</html>

<script>

document.addEventListener('DOMContentLoaded', () => {
            // Extract the token from the URL
            const urlParams = new URLSearchParams(window.location.search);
            const token = urlParams.get('token');

            // Check if the token exists in the URL
            if (token) {
                // Send a request to verify the token
                verifyToken(token);
            } else {
                alert('No token found in the URL');
            }
        });
        async function verifyToken(token) {
            try {
                // Send a request to your backend to verify the token
                const response = await fetch(`/verify-token/token=${token}`);
                const data = await response.json();

                if (response.ok) {
                    
                    alert(data.message);
                } else {
                   
                    alert(data.error || 'An error occurred');
                }
            } catch (error) {
            
                alert('An error occurred while verifying the token');
            }
        }
}
</script>