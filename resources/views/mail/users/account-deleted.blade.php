<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Account Restricted - SiteSphere</title>
</head>
<body style="margin:0; padding:0; background:#f4f7fb; font-family:Arial, Helvetica, sans-serif;">

<table width="100%" cellpadding="0" cellspacing="0" style="background:#f4f7fb; padding:40px 15px;">
    <tr>
        <td align="center">
            <table width="100%" cellpadding="0" cellspacing="0" style="max-width:560px; background:#ffffff; border-radius:18px; overflow:hidden; box-shadow:0 12px 35px rgba(0,0,0,0.08);">

                {{-- Header --}}
                <tr>
                    <td style="background:linear-gradient(135deg,#f59e0b,#d97706); padding:32px 25px; text-align:center;">
                        <div style="font-size:28px; font-weight:700; color:#ffffff;">
                            Site<span style="opacity:.85;">Sphere</span>
                        </div>
                        <div style="margin-top:10px; font-size:14px; color:#fef3c7;">
                            Account Restricted
                        </div>
                    </td>
                </tr>

                {{-- Content --}}
                <tr>
                    <td style="padding:34px 30px;">
                        <h1 style="margin:0 0 20px; font-size:23px; color:#1f2937;">
                            Hello {{ $deletedUser->name }},
                        </h1>

                        <p style="margin:0 0 20px; font-size:15px; color:#475569; line-height:1.7;">
                            Your SiteSphere account has been restricted by an administrator and can no longer be used to sign in.
                        </p>

                        {{-- Ban Details Card --}}
                        <div style="margin:24px 0; padding:18px; border-radius:10px; background:#fffbeb; border:1px solid #fde68a;">
                            <table width="100%" cellpadding="0" cellspacing="0">
                                <tr>
                                    <td style="padding-bottom:12px;">
                                        <div style="font-size:11px; color:#b45309; text-transform:uppercase; letter-spacing:0.5px; font-weight:600; margin-bottom:4px;">Reason</div>
                                        <div style="font-size:14px; color:#92400e; line-height:1.6;">{{ $reason }}</div>
                                    </td>
                                </tr>
                                <tr>
                                    <td style="padding-top:12px; border-top:1px solid #fde68a;">
                                        <div style="font-size:11px; color:#b45309; text-transform:uppercase; letter-spacing:0.5px; font-weight:600; margin-bottom:4px;">Action By</div>
                                        <div style="font-size:13px; color:#92400e; font-weight:500;">{{ $admin->name }}</div>
                                    </td>
                                </tr>
                            </table>
                        </div>

                        {{-- What To Do --}}
                        <div style="margin:24px 0; padding:16px; border-radius:10px; background:#f0fdf4; border:1px solid #bbf7d0;">
                            <p style="margin:0; font-size:14px; color:#166534; line-height:1.6;">
                                <strong>What can I do?</strong><br>
                                If this was a mistake, an administrator can review and restore your account. You may also submit an appeal through the login page.
                            </p>
                        </div>
                    </td>
                </tr>

                {{-- Footer --}}
                <tr>
                    <td style="background:#f8fafc; padding:22px 30px; text-align:center; border-top:1px solid #e2e8f0;">
                        <p style="margin:0 0 8px; font-size:13px; color:#475569;">
                            Thank you, {{ $deletedUser->name }}
                        </p>
                        <p style="margin:0; font-size:12px; color:#94a3b8;">
                            &copy; {{ date('Y') }} SiteSphere. All rights reserved.
                        </p>
                    </td>
                </tr>

            </table>
        </td>
    </tr>
</table>

</body>
</html>
