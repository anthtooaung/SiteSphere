<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Account Permanently Banned - SiteSphere</title>
</head>
<body style="margin:0; padding:0; background:#f4f7fb; font-family:Arial, Helvetica, sans-serif;">

<table width="100%" cellpadding="0" cellspacing="0" style="background:#f4f7fb; padding:40px 15px;">
    <tr>
        <td align="center">
            <table width="100%" cellpadding="0" cellspacing="0" style="max-width:560px; background:#ffffff; border-radius:18px; overflow:hidden; box-shadow:0 12px 35px rgba(0,0,0,0.08);">

                {{-- Header --}}
                <tr>
                    <td style="background:linear-gradient(135deg,#dc2626,#991b1b); padding:32px 25px; text-align:center;">
                        <div style="font-size:28px; font-weight:700; color:#ffffff;">
                            Site<span style="opacity:.85;">Sphere</span>
                        </div>
                        <div style="margin-top:10px; font-size:14px; color:#fecaca;">
                            Account Permanently Banned
                        </div>
                    </td>
                </tr>

                {{-- Content --}}
                <tr>
                    <td style="padding:34px 30px;">
                        <h1 style="margin:0 0 20px; font-size:23px; color:#1f2937;">
                            Hello {{ $user->name }},
                        </h1>

                        <p style="margin:0 0 20px; font-size:15px; color:#475569; line-height:1.7;">
                            We regret to inform you that your SiteSphere account has been <strong style="color:#dc2626;">permanently banned</strong> by an administrator. This action is final and your account cannot be restored.
                        </p>

                        {{-- Ban Details Card --}}
                        <div style="margin:24px 0; padding:18px; border-radius:10px; background:#fef2f2; border:1px solid #fecaca;">
                            <table width="100%" cellpadding="0" cellspacing="0">
                                <tr>
                                    <td style="padding-bottom:12px;">
                                        <div style="font-size:11px; color:#dc2626; text-transform:uppercase; letter-spacing:0.5px; font-weight:600; margin-bottom:4px;">Ban Reason</div>
                                        <div style="font-size:14px; color:#991b1b; line-height:1.6;">{{ $reason }}</div>
                                    </td>
                                </tr>
                                <tr>
                                    <td style="padding-top:12px; border-top:1px solid #fecaca;">
                                        <table width="100%" cellpadding="0" cellspacing="0">
                                            <tr>
                                                <td width="50%">
                                                    <div style="font-size:11px; color:#dc2626; text-transform:uppercase; letter-spacing:0.5px; font-weight:600; margin-bottom:4px;">Action By</div>
                                                    <div style="font-size:13px; color:#991b1b; font-weight:500;">{{ $admin->name }}</div>
                                                </td>
                                                <td width="50%">
                                                    <div style="font-size:11px; color:#dc2626; text-transform:uppercase; letter-spacing:0.5px; font-weight:600; margin-bottom:4px;">Date</div>
                                                    <div style="font-size:13px; color:#991b1b; font-weight:500;">{{ now()->format('M d, Y \a\t h:i A') }}</div>
                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                            </table>
                        </div>

                        {{-- What This Means --}}
                        <div style="margin:24px 0; padding:16px; border-radius:10px; background:#fff7ed; border:1px solid #fed7aa;">
                            <p style="margin:0; font-size:14px; color:#9a3412; line-height:1.6;">
                                <strong>What this means:</strong><br>
                                Your account data and all associated content have been permanently removed. You will not be able to create a new account using the same email address or phone number.
                            </p>
                        </div>

                        <p style="margin:20px 0 0; font-size:14px; color:#64748b; line-height:1.7;">
                            If you believe this action was made in error, you may contact our support team for further assistance.
                        </p>
                    </td>
                </tr>

                {{-- Footer --}}
                <tr>
                    <td style="background:#f8fafc; padding:22px 30px; text-align:center; border-top:1px solid #e2e8f0;">
                        <p style="margin:0 0 8px; font-size:13px; color:#475569;">
                            Thank you, {{ $user->name }}
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
