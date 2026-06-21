# Render Deployment — Email (SMTP) Setup

## Overview

- **Local dev** → Uses **Resend** (API-based, no SMTP ports needed, free tier)
- **Production (Render)** → Uses **Gmail SMTP** (SMTP ports work fine on Render)

Your local ISP blocks outbound SMTP ports (465, 587), so Gmail SMTP times out locally.
Render servers don't have this restriction — Gmail SMTP works perfectly there.

---

## Required Environment Variables on Render

Set these in your **Render Dashboard → Service → Environment**:

| Variable | Value | Notes |
|---|---|---|
| `MAIL_MAILER` | `smtp` | Use SMTP driver |
| `MAIL_SCHEME` | `smtps` | SSL encryption (required by Laravel) |
| `MAIL_HOST` | `smtp.gmail.com` | Gmail SMTP server |
| `MAIL_PORT` | `465` | SMTPS port |
| `MAIL_USERNAME` | `your-gmail@gmail.com` | Your Gmail address |
| `MAIL_PASSWORD` | `xxxx xxxx xxxx xxxx` | **Gmail App Password** (16 chars, with spaces) |
| `MAIL_FROM_ADDRESS` | `your-gmail@gmail.com` | Must match MAIL_USERNAME for Gmail |
| `MAIL_FROM_NAME` | `SiteSphere` | Display name in emails |
| `QUEUE_CONNECTION` | `sync` | Process mail immediately (no Redis needed) |

> **Important:** Do NOT use your regular Gmail password. You must generate an **App Password**.

---

## How to Generate a Gmail App Password

1. Go to [Google Account Security](https://myaccount.google.com/security)
2. Enable **2-Step Verification** (required for App Passwords)
3. Go to [App Passwords](https://myaccount.google.com/apppasswords)
4. Select app: **Mail**, device: **Other (Custom name)** → name it `SiteSphere`
5. Click **Generate** → copy the 16-character password
6. Use this password as `MAIL_PASSWORD` in your `.env` / Render environment

---

## Alternative: Resend (Recommended for Production Long-Term)

Resend is simpler (no SMTP complexity), has a generous free tier (3,000 emails/month),
and works reliably on any hosting platform.

### Setup Steps

1. Sign up at [resend.com](https://resend.com)
2. Get your API key from the dashboard
3. (Optional) Verify your domain for custom sender addresses

### Environment Variables for Resend

| Variable | Value |
|---|---|
| `MAIL_MAILER` | `resend` |
| `RESEND_API_KEY` | `re_xxxxxxxxxxxx` |
| `MAIL_FROM_ADDRESS` | `noreply@yourdomain.com` |
| `MAIL_FROM_NAME` | `SiteSphere` |

> On Resend's free tier, unverified domains can only send to the email
> you signed up with. Verify your domain to send to anyone.

---

## Queue Configuration

All OTP mailables implement `ShouldQueue`. With `QUEUE_CONNECTION=sync`,
emails are sent immediately during the request. This is fine for low traffic.

If you later want queued processing:
1. Set `QUEUE_CONNECTION=database`
2. Run `php artisan queue:table && php artisan migrate`
3. Start a worker: `php artisan queue:work`
4. On Render, add a **Background Worker** service running `php artisan queue:work`

---

## Render-Specific Notes

### Free Tier Limitations
- Render free tier spins down after 15 min of inactivity
- First request after spin-down takes ~30s — OTP emails may feel slow
- Consider Render's paid tier ($7/mo) for production

### Build Command
```bash
composer install --no-dev && npm ci && npm run build
```

### Start Command
```bash
php artisan serve --host 0.0.0.0 --port $PORT
```

### Health Check
Add a health check route to verify mail config:
```php
Route::get('/health/mail', function () {
    return response()->json([
        'mailer' => config('mail.default'),
        'configured' => !empty(config('mail.mailers.smtp.password')),
    ]);
});
```

---

## Troubleshooting

| Problem | Solution |
|---|---|
| "Connection timed out" | Render may block port 587. Try port 465 with `MAIL_SCHEME=ssl` |
| "Authentication failed" | Regenerate Gmail App Password; ensure 2FA is enabled |
| "Emails not arriving" | Check spam folder; verify `MAIL_FROM_ADDRESS` matches `MAIL_USERNAME` |
| "535 5.7.8 Bad credentials" | App Password is wrong or 2FA isn't enabled |
| Emails work locally but not on Render | Check Render env vars match your `.env` exactly |

---

## Security Notes

- **Never commit `.env` to git** — it contains secrets
- Use Render's **Environment Groups** to share vars across services
- Rotate your Gmail App Password if it's ever exposed
- Consider using a dedicated email account (not your personal Gmail)
