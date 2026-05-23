<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SiteSphere Verification Code</title>
    <style>
        body {
            font-family: 'Outfit', 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background-color: #080f18;
            color: #ffffff;
            margin: 0;
            padding: 0;
            -webkit-text-size-adjust: none;
            -ms-text-size-adjust: none;
        }
        .wrapper {
            width: 100%;
            background-color: #080f18;
            padding: 40px 0;
        }
        .container {
            max-width: 500px;
            margin: 0 auto;
            background: linear-gradient(145deg, #0d1b2a, #152538);
            border: 1px solid rgba(108, 92, 231, 0.2);
            border-radius: 20px;
            padding: 40px;
            text-align: center;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.5);
        }
        .logo {
            font-size: 24px;
            font-weight: 700;
            color: #6c5ce7;
            margin-bottom: 30px;
            letter-spacing: 0.5px;
        }
        .logo span {
            color: #ffffff;
        }
        h1 {
            font-size: 22px;
            font-weight: 600;
            margin: 0 0 16px;
            color: #ffffff;
        }
        p {
            font-size: 15px;
            line-height: 1.6;
            color: #b0c4de;
            margin: 0 0 30px;
        }
        .code-box {
            background-color: rgba(108, 92, 231, 0.1);
            border: 2px dashed #6c5ce7;
            border-radius: 12px;
            padding: 16px;
            font-size: 32px;
            font-weight: 700;
            letter-spacing: 6px;
            color: #6c5ce7;
            display: inline-block;
            margin-bottom: 30px;
            text-indent: 6px; /* offset letter-spacing */
        }
        .footer {
            margin-top: 40px;
            font-size: 12px;
            color: #6272a4;
            border-top: 1px solid rgba(255, 255, 255, 0.08);
            padding-top: 20px;
        }
    </style>
</head>
<body>
    <div class="wrapper">
        <div class="container">
            <div class="logo"><span>Site</span>Sphere</div>
            <h1>Verification Code</h1>
            <p>Please use the following 6-digit verification code to complete your registration. This code will expire in 5 minutes.</p>
            <div class="code-box">{{ $otpCode }}</div>
            <p style="margin-bottom: 0; font-size: 13px; color: #a29bfe;">If you did not request this code, you can safely ignore this email.</p>
            <div class="footer">
                &copy; {{ date('Y') }} SiteSphere. All rights reserved.
            </div>
        </div>
    </div>
</body>
</html>
