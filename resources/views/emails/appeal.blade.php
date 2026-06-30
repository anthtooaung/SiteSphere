<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Ban Appeal - SiteSphere</title>
</head>
<body style="margin:0; padding:0; background:#f1f5f9; font-family:Arial, Helvetica, sans-serif;">

<table width="100%" cellpadding="0" cellspacing="0" style="background:#f1f5f9; padding:40px 15px;">
    <tr>
        <td align="center">
            <table width="100%" cellpadding="0" cellspacing="0" style="max-width:600px; background:#ffffff; border-radius:16px; overflow:hidden; box-shadow:0 4px 24px rgba(0,0,0,0.06);">

                {{-- Header --}}
                <tr>
                    <td style="background:linear-gradient(135deg,#dc2626,#b91c1c); padding:28px 30px;">
                        <table width="100%" cellpadding="0" cellspacing="0">
                            <tr>
                                <td style="vertical-align:middle;">
                                    <div style="font-size:22px; font-weight:700; color:#ffffff; letter-spacing:-0.3px;">
                                        Site<span style="opacity:.8;">Sphere</span>
                                    </div>
                                </td>
                                <td align="right" style="vertical-align:middle;">
                                    <div style="display:inline-block; background:rgba(255,255,255,0.18); padding:5px 14px; border-radius:20px; font-size:12px; font-weight:600; color:#fecaca; letter-spacing:0.5px; text-transform:uppercase;">
                                        ⚑ Appeal
                                    </div>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>

                {{-- Urgency Bar --}}
                <tr>
                    <td style="background:#fef2f2; padding:10px 30px; border-bottom:1px solid #fee2e2;">
                        <p style="margin:0; font-size:12px; color:#dc2626; font-weight:600; letter-spacing:0.3px;">
                            ⏱ Submitted {{ $user->appeal_submitted_at?->diffForHumans() ?? 'just now' }} — awaiting review
                        </p>
                    </td>
                </tr>

                <tr>
                    <td style="padding:30px 30px 10px;">

                        {{-- Title --}}
                        <h1 style="margin:0 0 6px; font-size:22px; color:#0f172a; font-weight:700;">
                            Ban Appeal Received
                        </h1>
                        <p style="margin:0 0 24px; font-size:14px; color:#64748b; line-height:1.5;">
                            A banned user has submitted an appeal for your review.
                        </p>

                        {{-- User Info Card --}}
                        <table width="100%" cellpadding="0" cellspacing="0" style="background:#f8fafc; border:1px solid #e2e8f0; border-radius:12px; overflow:hidden;">
                            <tr>
                                <td style="padding:20px;">
                                    <table width="100%" cellpadding="0" cellspacing="0">
                                        <tr>
                                            {{-- Avatar Placeholder --}}
                                            <td width="52" style="vertical-align:top; padding-right:16px;">
                                                <div style="width:48px; height:48px; border-radius:50%; background:linear-gradient(135deg,#6366f1,#8b5cf6); text-align:center; line-height:48px; font-size:18px; font-weight:700; color:#ffffff;">
                                                    {{ strtoupper(substr($user->name, 0, 1)) }}
                                                </div>
                                            </td>
                                            {{-- User Details --}}
                                            <td style="vertical-align:middle;">
                                                <div style="font-size:16px; font-weight:700; color:#0f172a; margin-bottom:4px;">
                                                    {{ $user->name }}
                                                </div>
                                                <div style="font-size:13px; color:#64748b;">
                                                    {{ $user->email }}
                                                </div>
                                            </td>
                                        </tr>
                                    </table>

                                    {{-- Meta Grid --}}
                                    <table width="100%" cellpadding="0" cellspacing="0" style="margin-top:16px; padding-top:16px; border-top:1px solid #e2e8f0;">
                                        <tr>
                                            <td width="50%" style="vertical-align:top; padding-right:8px;">
                                                <div style="font-size:11px; color:#94a3b8; text-transform:uppercase; letter-spacing:0.5px; font-weight:600; margin-bottom:4px;">Banned On</div>
                                                <div style="font-size:13px; color:#334155; font-weight:500;">{{ $user->banned_at?->format('M d, Y \a\t h:i A') ?? 'Unknown' }}</div>
                                            </td>
                                            <td width="50%" style="vertical-align:top; padding-left:8px;">
                                                <div style="font-size:11px; color:#94a3b8; text-transform:uppercase; letter-spacing:0.5px; font-weight:600; margin-bottom:4px;">User ID</div>
                                                <div style="font-size:13px; color:#334155; font-weight:500;">#{{ $user->id }}</div>
                                            </td>
                                        </tr>
                                    </table>
                                </td>
                            </tr>
                        </table>

                        {{-- Appeal Statement --}}
                        <div style="margin-top:24px;">
                            <div style="font-size:11px; color:#94a3b8; text-transform:uppercase; letter-spacing:0.5px; font-weight:600; margin-bottom:10px;">Appeal Statement</div>
                            <div style="border-left:3px solid #dc2626; padding:16px 20px; background:#fafafa; border-radius:0 8px 8px 0; color:#334155; font-size:14px; line-height:1.75; white-space:pre-line;">{{ $reason }}</div>
                        </div>

                        {{-- Ban Reason (if any) --}}
                        @if($user->ban_reason)
                        <div style="margin-top:20px;">
                            <div style="font-size:11px; color:#94a3b8; text-transform:uppercase; letter-spacing:0.5px; font-weight:600; margin-bottom:10px;">Original Ban Reason</div>
                            <div style="padding:14px 18px; background:#fff7ed; border:1px solid #fed7aa; border-radius:8px; color:#9a3412; font-size:13px; line-height:1.6;">
                                {{ $user->ban_reason }}
                            </div>
                        </div>
                        @endif

                        {{-- Action Buttons --}}
                        <table width="100%" cellpadding="0" cellspacing="0" style="margin-top:28px;">
                            <tr>
                                <td align="center" style="padding-bottom:12px;">
                                    <a href="{{ route('users') }}?search={{ urlencode($user->email) }}" style="display:inline-block; padding:14px 36px; background:linear-gradient(135deg,#dc2626,#b91c1c); color:#ffffff; text-decoration:none; border-radius:10px; font-size:14px; font-weight:600; letter-spacing:0.2px;">
                                        Review in Admin Panel
                                    </a>
                                </td>
                            </tr>
                            <tr>
                                <td align="center">
                                    <a href="{{ route('profile-detail', $user->slug) }}" style="display:inline-block; padding:10px 24px; background:transparent; color:#6366f1; text-decoration:none; border-radius:8px; font-size:13px; font-weight:600; border:1px solid #c7d2fe;">
                                        View User Profile
                                    </a>
                                </td>
                            </tr>
                        </table>

                    </td>
                </tr>

                {{-- Footer --}}
                <tr>
                    <td style="background:#f8fafc; padding:20px 30px; border-top:1px solid #e2e8f0;">
                        <table width="100%" cellpadding="0" cellspacing="0">
                            <tr>
                                <td style="font-size:11px; color:#94a3b8; line-height:1.5;">
                                    This is an automated notification from SiteSphere.<br>
                                    Please review appeals within 24-48 hours for the best user experience.
                                </td>
                            </tr>
                            <tr>
                                <td style="padding-top:10px; font-size:11px; color:#94a3b8;">
                                    &copy; {{ date('Y') }} SiteSphere. All rights reserved.
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>

            </table>
        </td>
    </tr>
</table>

</body>
</html>
