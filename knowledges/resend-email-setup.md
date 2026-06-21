# Resend Email Setup — SiteSphere

## What is Resend?
Resend is a modern transactional email service used to send OTP codes, password reset emails,
and other system emails in SiteSphere. It replaces the previous Gmail SMTP configuration.

Laravel 13 has **built-in support** for Resend — no extra package config needed beyond
installing `resend/resend-laravel`.

---

## Package Installation

Run once when you have internet access:

```bash
composer require resend/resend-laravel
```

---

## Where the API Key Goes

### In your `.env` file (root of the project):

```dotenv
MAIL_MAILER=resend
MAIL_FROM_ADDRESS="onboarding@resend.dev"   # Use this ONLY while testing (no domain verified)
MAIL_FROM_NAME="SiteSphere"

RESEND_KEY=re_your_api_key_here             # <-- paste your key HERE
```

> The `RESEND_KEY` variable is read by Laravel's built-in Resend transport via `config/services.php`.
> You do NOT need to touch `config/mail.php` — the `resend` mailer is already defined there.

---

## Local Development Setup (No Domain Yet)

Use these settings while testing locally before your domain is verified:

| Key | Value |
|-----|-------|
| `MAIL_MAILER` | `resend` |
| `MAIL_FROM_ADDRESS` | `onboarding@resend.dev` |
| `RESEND_KEY` | Your `re_...` API key |

**Limitation:** You can only send emails to the **same email address** you registered
with on Resend (your own inbox). This is enough to test OTP flows.

---

## Production Setup (Domain Verified)

Once your domain (e.g., `sitesphere.com`) is verified in the Resend dashboard:

```dotenv
MAIL_MAILER=resend
MAIL_FROM_ADDRESS="noreply@yourdomain.com"
MAIL_FROM_NAME="SiteSphere"

RESEND_KEY=re_your_production_api_key_here

# Also set these for production:
APP_ENV=production
APP_DEBUG=false
APP_URL=https://yourdomain.com
QUEUE_CONNECTION=database
```

---

## How to Verify a Domain in Resend

1. Go to [resend.com](https://resend.com) → **Domains** → **Add Domain**
2. Enter your domain (e.g., `sitesphere.com`)
3. Add the DNS records they provide:
   - **SPF** record (TXT)
   - **DKIM** records (TXT)
   - **DMARC** record (TXT) — optional but recommended
4. Click **Verify** — usually takes a few minutes

---

## How OTP Emails Work in This Project

OTPs are sent from these controllers:

| Controller | Purpose |
|-----------|---------|
| `AuthenticatedSessionController` | Login 2FA OTP |
| `LoginTwoFactorChallengeController` | Resend 2FA OTP |
| `PasswordResetLinkController` | Password reset OTP |
| `RegisteredUserController` | Registration verification OTP |

All of them call `Mail::to($user->email)->send(new SomeMail($otpCode))`.

> **Important:** The `createAndSendTwoFactorOtp()` method has a safety guard —
> if `RESEND_KEY` is empty, it will skip sending the email silently
> (OTP code is still logged to `storage/logs/laravel.log` for local debugging).

---

## Free Tier Limits (Resend)

| Limit | Amount |
|-------|--------|
| Emails per month | 3,000 |
| Emails per day | 100 |
| Domains | 1 |
| API keys | Unlimited |

Upgrade plans are available if needed. For a small community platform like SiteSphere,
the free tier is more than enough to start.

---

## Quick Checklist

- [ ] `composer require resend/resend-laravel` installed
- [ ] `RESEND_KEY=re_...` added to `.env`
- [ ] `MAIL_MAILER=resend` set in `.env`
- [ ] Domain verified in Resend dashboard (for production)
- [ ] `MAIL_FROM_ADDRESS` updated to use verified domain (for production)
- [ ] `QUEUE_CONNECTION=database` set on production server
- [ ] Google & GitHub OAuth redirect URIs updated to production URL
