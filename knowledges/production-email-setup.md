# Production Email Setup — Gmail SMTP (Path B)

## Strategy: Local = Resend, Production = Gmail SMTP

Your home ISP blocks outbound SMTP (ports 465 & 587), so Gmail doesn't work locally.
Production servers (Render, Railway, Fly.io, etc.) do NOT have this restriction.

| Environment | Driver | Works for |
|-------------|--------|-----------|
| Local dev   | Resend | Your own email only (no domain needed) |
| Production  | Gmail SMTP | Any user's email in the world |

---

## Current Local `.env` (Do NOT change this)

```dotenv
MAIL_MAILER=resend
MAIL_FROM_ADDRESS="onboarding@resend.dev"
MAIL_FROM_NAME="SiteSphere"
RESEND_API_KEY=re_Xh6aTTPy_AGcAMqH5KY85kK6EFJiSDc5s
```

---

## Production Server Environment Variables

Set these in your hosting dashboard (Render / Railway / Fly.io / Forge):

```dotenv
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=anthtooaung2792005@gmail.com
MAIL_PASSWORD=usuprliniilimcrl
MAIL_FROM_ADDRESS=anthtooaung2792005@gmail.com
MAIL_FROM_NAME=SiteSphere
```

> Do NOT wrap values in quotes in hosting dashboards — paste raw values only.

---

## How the Code Handles Both Drivers

A shared trait `App\Traits\ChecksMailConfiguration` makes the guard driver-aware:

```php
private function isMailConfigured(): bool
{
    return match (config('mail.default')) {
        'resend' => (string) config('services.resend.key') !== '',
        'smtp'   => (string) config('mail.mailers.smtp.password') !== '',
        default  => true,
    };
}
```

This trait is used in:
- `AuthenticatedSessionController`   — login 2FA OTP
- `LoginTwoFactorChallengeController` — resend 2FA OTP
- `RegisteredUserController`          — registration OTP (×2)

---

## Gmail App Password

The password `usuprliniilimcrl` is a **Gmail App Password** — not your real Gmail password.
It was generated at: https://myaccount.google.com/apppasswords

> If it stops working (Google revokes it), go back to that URL and generate a new one.
> Requires 2-Step Verification to be enabled on the Gmail account.

---

## Gmail Free Limits

| Limit | Amount |
|-------|--------|
| Emails per day | 500 |
| Emails per month | ~15,000 |
| Cost | Free |

For a new project, 500/day is more than enough.

---

## Other Production `.env` Values to Set

```dotenv
APP_ENV=production
APP_DEBUG=false
APP_URL=https://yourdomain.com
APP_KEY=base64:...     # generate with: php artisan key:generate --show

DB_CONNECTION=mysql
DB_HOST=your-db-host
DB_PORT=3306
DB_DATABASE=your_db
DB_USERNAME=your_user
DB_PASSWORD=your_pass

QUEUE_CONNECTION=database
SESSION_DRIVER=database
CACHE_STORE=database

# Google OAuth — update redirect URIs in Google Cloud Console too
GOOGLE_CLIENT_ID=your-client-id
GOOGLE_CLIENT_SECRET=your-client-secret
GOOGLE_REDIRECT_URI=https://yourdomain.com/auth/google/callback

# GitHub OAuth — update callback URL in GitHub Settings too
GITHUB_CLIENT_ID=your-client-id
GITHUB_CLIENT_SECRET=your-client-secret
GITHUB_REDIRECT_URI=https://yourdomain.com/auth/github/callback
```

---

## Deployment Checklist

- [ ] Set all env vars in hosting dashboard
- [ ] Update Google OAuth redirect URI to production URL
- [ ] Update GitHub OAuth callback URL to production URL
- [ ] Run `php artisan migrate --force` on production
- [ ] Run `php artisan config:cache` on production
- [ ] Run `npm run build` before deploying (or in Dockerfile)
- [ ] Test OTP email by registering with a real email on production
