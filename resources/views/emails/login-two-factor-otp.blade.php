<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>SiteSphere Login Verification Code</title>
</head>
<body style="margin:0; padding:0; background:#f4f7fb; font-family:Arial, Helvetica, sans-serif;">

<table width="100%" cellpadding="0" cellspacing="0" style="background:#f4f7fb; padding:40px 15px;">
    <tr>
        <td align="center">
            <table width="100%" cellpadding="0" cellspacing="0" style="max-width:520px; background:#ffffff; border-radius:18px; overflow:hidden; box-shadow:0 12px 35px rgba(0,0,0,0.08);">
                <tr>
                    <td style="background:linear-gradient(135deg,#6c5ce7,#00cec9); padding:35px 25px; text-align:center;">
                        <div style="font-size:28px; font-weight:700; color:#ffffff;">
                            Site<span style="opacity:.85;">Sphere</span>
                        </div>
                        <div style="margin-top:10px; font-size:14px; color:#eef2ff;">
                            Secure login verification
                        </div>
                    </td>
                </tr>

                <tr>
                    <td style="padding:40px 30px; text-align:center;">
                        <h1 style="margin:0 0 15px; font-size:24px; color:#1f2937;">
                            Verify Your Login
                        </h1>

                        <p style="margin:0 0 28px; font-size:15px; line-height:1.7; color:#64748b;">
                            Use the code below to finish signing in to SiteSphere.
                            This code will expire in <strong>5 minutes</strong>.
                        </p>

                        <div style="display:inline-block; background:#f1f0ff; border:1px solid #d8d5ff; border-radius:14px; padding:18px 28px; font-size:34px; font-weight:700; letter-spacing:8px; color:#6c5ce7;">
                            {{ $otpCode }}
                        </div>

                        <p style="margin:30px 0 0; font-size:13px; line-height:1.6; color:#94a3b8;">
                            If you did not try to sign in, change your password as soon as possible.
                        </p>
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
