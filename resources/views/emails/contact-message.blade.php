<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>New SiteSphere contact message</title>
</head>
<body style="margin:0; padding:0; background:#f4f7fb; font-family:Arial, Helvetica, sans-serif;">

<table width="100%" cellpadding="0" cellspacing="0" style="background:#f4f7fb; padding:40px 15px;">
    <tr>
        <td align="center">
            <table width="100%" cellpadding="0" cellspacing="0" style="max-width:560px; background:#ffffff; border-radius:18px; overflow:hidden; box-shadow:0 12px 35px rgba(0,0,0,0.08);">
                <tr>
                    <td style="background:linear-gradient(135deg,#6c5ce7,#00cec9); padding:32px 25px; text-align:center;">
                        <div style="font-size:28px; font-weight:700; color:#ffffff;">
                            Site<span style="opacity:.85;">Sphere</span>
                        </div>
                        <div style="margin-top:10px; font-size:14px; color:#eef2ff;">
                            New contact message
                        </div>
                    </td>
                </tr>

                <tr>
                    <td style="padding:34px 30px;">
                        <h1 style="margin:0 0 20px; font-size:23px; color:#1f2937;">
                            Message Details
                        </h1>

                        <p style="margin:0 0 8px; font-size:14px; line-height:1.7; color:#475569;">
                            <strong>Name:</strong> {{ $messageData['first_name'] }} {{ $messageData['last_name'] }}
                        </p>

                        <p style="margin:0 0 24px; font-size:14px; line-height:1.7; color:#475569;">
                            <strong>Email:</strong> {{ $messageData['email'] }}
                        </p>

                        <div style="padding:20px; border-radius:14px; background:#f8fafc; border:1px solid #e2e8f0; color:#334155; font-size:15px; line-height:1.8; white-space:pre-line;">{{ $messageData['message'] }}</div>
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
