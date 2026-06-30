<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Account Restored - SiteSphere</title>
</head>
<body style="margin:0; padding:0; background:#f4f7fb; font-family:Arial, Helvetica, sans-serif;">

<table width="100%" cellpadding="0" cellspacing="0" style="background:#f4f7fb; padding:40px 15px;">
    <tr>
        <td align="center">
            <table width="100%" cellpadding="0" cellspacing="0" style="max-width:560px; background:#ffffff; border-radius:18px; overflow:hidden; box-shadow:0 12px 35px rgba(0,0,0,0.08);">
                <tr>
                    <td style="background:linear-gradient(135deg,#22c55e,#16a34a); padding:32px 25px; text-align:center;">
                        <div style="font-size:28px; font-weight:700; color:#ffffff;">
                            Site<span style="opacity:.85;">Sphere</span>
                        </div>
                        <div style="margin-top:10px; font-size:14px; color:#dcfce7;">
                            Account Restored
                        </div>
                    </td>
                </tr>

                <tr>
                    <td style="padding:34px 30px;">
                        <h1 style="margin:0 0 20px; font-size:23px; color:#1f2937;">
                            Good news, {{ $user->name }}!
                        </h1>

                        <p style="margin:0 0 20px; font-size:15px; color:#475569; line-height:1.7;">
                            Your SiteSphere account has been reviewed and restored by our team. You can now log in and continue using the platform.
                        </p>

                        <div style="margin:24px 0; padding:16px; border-radius:10px; background:#f0fdf4; border:1px solid #bbf7d0;">
                            <p style="margin:0; font-size:14px; color:#166534; line-height:1.6;">
                                <strong>What's next?</strong><br>
                                Log in with your existing credentials to access your account. All your data and content remain intact.
                            </p>
                        </div>

                        <div style="margin-top:28px; text-align:center;">
                            <a href="{{ route('login') }}" style="display:inline-block; padding:14px 32px; background:linear-gradient(135deg,#22c55e,#16a34a); color:#ffffff; text-decoration:none; border-radius:10px; font-size:15px; font-weight:600;">
                                Log In Now
                            </a>
                        </div>
                    </td>
                </tr>

                <tr>
                    <td style="background:#f8fafc; padding:22px 30px; text-align:center; font-size:12px; color:#94a3b8;">
                        &copy; {{ date('Y') }} SiteSphere. All rights reserved.
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>

</body>
</html>
