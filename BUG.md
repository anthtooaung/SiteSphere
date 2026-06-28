# Bug Report — SiteSphere Project

**Generated:** 2026-06-28  
**Total:** 16 bugs + 8 code quality issues

---

## 🔴 HIGH Severity — Runtime Crashes

### BUG-01: `Alpine.$data()` removed in Alpine.js v3
- **File:** `resources/js/homepage.js:17`
- **Code:** `const alpineData = Alpine.$data(homePage);`
- **Impact:** Home page search form JavaScript completely broken. `Alpine.$data()` was removed from Alpine.js v3 public API — this call throws a `TypeError` at runtime.
- **Fix:** Rewrite to use Alpine's public API or access component data via `Alpine.$data()` alternative.

---

### BUG-02: `abort(redirect(...))` is invalid usage
- **File:** `app/Http/Controllers/AdminActivityLogController.php:115`
- **Code:** `abort(redirect()->route('login'));`
- **Impact:** `abort()` expects an integer HTTP status code, not a `RedirectResponse`. This throws an exception. The route is already behind `auth` middleware so this check is redundant.
- **Fix:** Remove the check entirely (middleware handles it), or use `abort(401)`.

---

### BUG-03: Selects non-existent `title` column from `posts` table
- **File:** `app/Http/Controllers/WelcomeController.php:16`
- **Code:** `->select(['id', 'title', 'slug', 'url'])`
- **Impact:** Migration `2026_06_28_120000_restructure_post_titles.php` dropped the `title` column from `posts`. This query fails with `SQLSTATE[42S22]: Column not found: 1054 Unknown column 'title'`.
- **Fix:** Remove `'title'` from the select array. Titles are now on `user_posts`.

---

### BUG-04: Accesses `$post->title` after column was removed
- **File:** `app/Http/Controllers/ReportsController.php:427`
- **Code:** `$postTitle = $userPost->post?->title ?? 'Unknown Post';`
- **Impact:** `title` column no longer exists on `posts` table. This always returns `'Unknown Post'`.
- **Fix:** Change to `$userPost->title ?? 'Unknown Post'` (title is now on `user_posts`).

---

### BUG-05: Wrong table name `otpVerifications` (camelCase)
- **File:** `app/Models/OtpVerifications.php:9`
- **Code:** `protected $table = 'otpVerifications';`
- **Impact:** Migration creates table `otp_verifications` (snake_case). This model override causes `Table 'otpVerifications' doesn't exist` error.
- **Fix:** Remove the `$table` property entirely (Laravel infers `otp_verifications` by default).

---

### BUG-06: Wrong table name `socialAccounts` (camelCase)
- **File:** `app/Models/SocialAccount.php:12`
- **Code:** `protected $table = 'socialAccounts';`
- **Impact:** Migration creates table `social_accounts` (snake_case). Same issue as BUG-05.
- **Fix:** Remove the `$table` property entirely.

---

### BUG-07: XSS via unescaped Blade output in JavaScript
- **File:** `resources/views/layout/menu/edit-tag.blade.php:669`
- **Code:** `` title: `{!! session('success') ?? $errors->first() !!}`, ``
- **Impact:** Raw Blade output (`{!! !!}`) injects session data directly into a JS template literal. If an attacker controls session flash data, they can inject arbitrary JavaScript.
- **Fix:** Use `@json(session('success') ?? $errors->first())` or escape with `e()`.

---

### BUG-08: OTP codes logged in plaintext
- **Files:**
  - `app/Http/Controllers/Auth/RegisteredUserController.php:85, 148`
  - `app/Http/Controllers/Auth/AuthenticatedSessionController.php:139`
  - `app/Http/Controllers/Auth/PasswordResetLinkController.php:55`
  - `app/Http/Controllers/Auth/LoginTwoFactorChallengeController.php:141`
- **Code:** `Log::info("OTP verification code for {$email}: {$otpCode}");`
- **Impact:** OTP codes written to application logs. Anyone with log access can bypass 2FA and password resets.
- **Fix:** Remove or redact OTP logging in production.

---

## 🟡 MEDIUM Severity — Functional Bugs

### BUG-09: Wrong route `/post/` instead of `/posts/`
- **File:** `resources/views/components/layout/upload-post.blade.php:166`
- **Code:** `` window.location.href = `/post/${unsecurePost.slug}`; ``
- **Impact:** Should be `/posts/` (with `s`). Clicking "Visit Post" on unsecure URL warning produces a 404.
- **Fix:** Change to `/posts/${unsecurePost.slug}`.

---

### BUG-10: `selectDate()` not globally accessible from inline onclick
- **File:** `resources/views/partials/admin-activity-card.blade.php:29`
- **Code:** `onclick="selectDate('{{ $date }}', true)"`
- **Impact:** `selectDate()` is defined inside a `DOMContentLoaded` closure in `admin-activity.js`, making it inaccessible from inline handlers. Throws `ReferenceError: selectDate is not defined`.
- **Fix:** Expose `selectDate` on `window` or use `addEventListener` instead of inline `onclick`.

---

### BUG-11: Malformed CSS class containing raw CSS syntax
- **File:** `resources/views/components/layout/comments.blade.php:394`
- **Code:** `class="fixed inset-0 z-[100000] flex items-center justify-content: center; justify-content bg-black/45 p-4 backdrop-blur-md"`
- **Impact:** `justify-content: center;` is raw CSS inside a Tailwind class attribute. Parsed as invalid class names. Report modal overlay not properly centered.
- **Fix:** Remove `justify-content: center; justify-content` from the class (inline `style` on line 395 already handles it).

---

### BUG-12: `@push` inside `@section` — styles/scripts may not load
- **File:** `resources/views/layout/menu/activity-log.blade.php:38-57`
- **Impact:** `@push('styles')` and `@push('scripts')` are placed inside `@section('content')`. They render after `@yield('styles')` and `@yield('scripts')` in the parent layout, so CSS/JS may load incorrectly or appear inline in the page body.
- **Fix:** Move `@push` directives outside the `@section` block, or use `@prepend`.

---

### BUG-13: OTP generated with insecure `rand()`
- **Files:** Same 5 auth controllers as BUG-08
- **Code:** `$otpCode = rand(100000, 999999);`
- **Impact:** `rand()` is not cryptographically secure and is predictable. OTP codes for auth/2FA/password reset should use `random_int()`.
- **Fix:** Change to `$otpCode = random_int(100000, 999999);`

---

### BUG-14: Missing rate limiting on auth routes
- **File:** `routes/auth.php:22-29, 37, 61-74`
- **Impact:** No `throttle` middleware on login POST, registration OTP, and password reset OTP endpoints. Allows brute-force attacks on OTP codes and email flooding.
- **Fix:** Add `->middleware('throttle:5,1')` to sensitive auth routes.

---

### BUG-15: Hardcoded admin credentials in seeder
- **File:** `database/seeders/AdminUserSeeder.php:17-28`
- **Code:** Email: `anthtooaung2792005@outlook.com`, Password: `123!@#123`
- **Impact:** Weak password hardcoded in source. Seeder runs on every Docker start (`docker/start.sh:47`). Developer's real email exposed.
- **Fix:** Use environment variables for admin credentials. Use a strong password.

---

### BUG-16: Duplicate `isUnsecure` array key
- **File:** `app/Http/Controllers/PostsController.php:419-422`
- **Code:**
  ```php
  'isUnsecure' => false,              // line 419 — dead code
  'isUnsecure' => $posts->is_unsecure, // line 422 — overwrites
  ```
- **Impact:** First value is silently overwritten. Dead code masks intent.
- **Fix:** Remove the first `'isUnsecure' => false` entry.

---

## 🟢 LOW Severity — Code Quality

### BUG-17: Invalid Tailwind class `overflow-none`
- **File:** `resources/views/dashboard.blade.php:53`
- **Impact:** `overflow-none` is not a valid Tailwind utility. Should be `overflow-hidden`.
- **Fix:** Change to `overflow-hidden`.

---

### BUG-18: Empty CSS class attribute
- **File:** `resources/views/layout/post-detail.blade.php:442`
- **Code:** `<div class=" ">`
- **Impact:** Empty class suggests a missing style definition.

---

### BUG-19: Empty `if(updateUrl){}` dead code block
- **File:** `resources/js/auth.js:411-413`
- **Impact:** Empty if-statement does nothing. Leftover from refactoring.

---

### BUG-20: Redundant `$isAdmin` assigned 3 times
- **File:** `app/Http/Controllers/PostsController.php:282, 322, 364`
- **Impact:** `auth()->user()?->role === 'admin'` called 3 times in the same method. First assignment suffices.

---

### BUG-21: Model name typo "Notificatioins"
- **Files:** `app/Models/Notificatioins.php`, related migrations, controllers, requests, factory
- **Impact:** Typo propagates throughout codebase. Makes searching and maintenance harder.

---

### BUG-22: Hardcoded contact email in config
- **File:** `config/mail.php:119`
- **Code:** `'recipient' => env('MAIL_CONTACT_RECIPIENT', 'anthtooaung2792005@outlook.com')`
- **Impact:** Personal email as default fallback. Should use env without personal default.

---

### BUG-23: Wildcard composer version constraints
- **File:** `composer.json:19, 25, 26`
- **Code:** `"resend/resend-laravel": "*"` and `"laravel/breeze": "*"`
- **Impact:** `*` allows any major version, potentially breaking on update.

---

### BUG-24: Missing null-check on carousel buttons
- **File:** `resources/js/about-us.js:32-33, 60, 65`
- **Impact:** `prevBtn`/`nextBtn` accessed without null-check. Throws `TypeError` if elements are missing from DOM.

---

## Security Summary

| Priority | Issue | Count |
|----------|-------|-------|
| 🔴 Critical | Runtime crash bugs | 8 |
| 🟡 Medium | Functional/security bugs | 8 |
| 🟢 Low | Code quality issues | 8 |

**Total: 24 issues identified**
