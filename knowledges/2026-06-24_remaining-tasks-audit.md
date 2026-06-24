# Remaining Tasks — Post-Audit Fix List
> Generated: 2026-06-24
> Sources: report-system-brainstorming, audit-log-tag-system, report-box-upgrade, flexible-ban-button-design

---

## 🚨 Priority 1 — Will Break at Runtime

### 1. Stale `comments.unban` route → 500 error
- **File:** `routes/web.php:56`
- **Problem:** Route `comments.unban` points to `CommentsController::unban` which no longer exists. Visiting this route throws a 500.
- **Fix:** Remove the route line entirely.

### 2. Duplicate `deleteAudit()` in PostsController
- **File:** `app/Http/Controllers/PostsController.php` (lines ~184 and ~226)
- **Problem:** Two `deleteAudit()` methods exist. PHP silently uses the second, overriding the first.
- **Fix:** Remove the first (dead) definition, keep the second which includes full logic.

### 3. Duplicate `delete()` in CommentsController
- **File:** `app/Http/Controllers/CommentsController.php` (lines ~97 and ~129)
- **Problem:** Two `delete()` methods exist. Second overrides the first.
- **Fix:** Remove the first (dead) definition, keep the second which includes notification to comment author.

### 4. `resetReportCount()` missing `user_posts` case
- **File:** `app/Http/Controllers/AdminReportsController.php` (~line 316)
- **Problem:** When resolving a `user_posts` report, `report_count` on the user_posts record is NOT reset.
- **Fix:** Add a `case 'user_posts':` block that resets `UserPosts::where('id', $targetId)->update(['report_count' => 0])`.

---

## ⚠️ Priority 2 — Not Working As Specified

### 5. Reports model missing `userPost()` relation
- **File:** `app/Models/Reports.php`
- **Problem:** Has `post()`, `comment()`, `targetUser()` but no `userPost()` relation for `user_posts` target type.
- **Fix:** Add `public function userPost() { return $this->belongsTo(UserPosts::class, 'target_id'); }`

### 6. "Mark all as read" button missing from notification dropdown
- **File:** `resources/views/components/noti-btn.blade.php`
- **Problem:** Route `POST /notifications/mark-all-read` and controller method exist, but no UI button triggers them.
- **Fix:** Add a "Mark all as read" button/link in the notification dropdown header.

### 7. `restore_user` audit log category is `moderation` instead of `resolved`
- **File:** `app/Http/Controllers/AdminUsersController.php` (~line 93)
- **Problem:** `restore()` calls `$this->audit(...)` without specifying category, so it defaults to `'moderation'`.
- **Fix:** Pass `category: 'resolved'` explicitly in the audit call.

---

## ⚠️ Priority 3 — Report Box Upgrade Missing Removals

### 8. Delete button still visible on POST report table
- **File:** `resources/views/admin/reports.blade.php` (~lines 380-388)
- **Problem:** Spec says remove delete buttons from POST and COMMENT tables. They're still there.
- **Fix:** Remove the "Delete Report" button/form from the POST reports table row.

### 9. Delete button still visible on COMMENT report table
- **File:** `resources/views/admin/reports.blade.php` (~lines 546-554)
- **Problem:** Same as above for COMMENT tab.
- **Fix:** Remove the "Delete Report" button/form from the COMMENT reports table row.

### 10. Restore button still visible on POST report table
- **File:** `resources/views/admin/reports.blade.php` (~lines 360-368)
- **Problem:** Spec says remove restore button. Still present for trashed posts.
- **Fix:** Remove the "Restore Post" button/form from the POST reports table row.

---

## 📝 Priority 4 — Audit Log Naming / Missing Actions

### 11. Audit action names differ from spec
- **Problem:** Spec uses `set_unsecure` / `set_verified`. Code uses `mark_unsecure_post` / `mark_secure_post` / `toggle_unsecure_user`.
- **Decision needed:** Rename to match spec, or update spec to match code? (Low impact — functional either way.)

### 12. `set_verified` audit action not implemented
- **Problem:** When admin marks a user as verified (removes unsecure), no separate `set_verified` audit log is created. `toggleUnsecure()` handles both directions with the same action name.
- **Fix:** Log distinct actions (`set_unsecure` / `set_verified`) depending on the toggle direction.

### 13. `review_flagged_content` audit action not implemented
- **Problem:** No audit log entry when admin reviews flagged content.
- **Fix:** Add audit logging in the flagged content review flow.

### 14. Stale `audits.unban` route
- **File:** `routes/web.php:46`
- **Problem:** Route still exists but spec says remove it. The controller method `unbanAudit()` still exists too, so it won't error — but it's dead code per the new design.
- **Decision needed:** Keep for backward compatibility, or remove?

---

## Summary Count

| Priority | Count | Impact |
|---|---|---|
| 🚨 P1 — Runtime errors | 4 | Broken pages / silent bugs |
| ⚠️ P2 — Missing features | 3 | Incomplete user experience |
| ⚠️ P3 — UI not cleaned up | 3 | Spec mismatch, extra buttons |
| 📝 P4 — Naming / dead code | 4 | Low impact, cosmetic |

**Total remaining: 14 items**
