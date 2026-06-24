# Remaining Tasks — Post-Audit Fix List
> Generated: 2026-06-24
> Sources: report-system-brainstorming, audit-log-tag-system, report-box-upgrade, flexible-ban-button-design

---

## ~~🚨 Priority 1 — Will Break at Runtime~~ ✅ FIXED

### ~~1. Stale `comments.unban` route → 500 error~~ ✅
- **File:** `routes/web.php`
- **Fix:** Removed the `comments.unban` route line.

### ~~2. Duplicate `deleteAudit()` in PostsController~~ ✅
- **File:** `app/Http/Controllers/PostsController.php`
- **Fix:** Removed the first (dead) definition, kept the second with full logic.

### ~~3. Duplicate `delete()` in CommentsController~~ ✅
- **File:** `app/Http/Controllers/CommentsController.php`
- **Fix:** Removed the first (dead) definition, kept the second which includes notification to comment author.

### ~~4. `resetReportCount()` missing `user_posts` case~~ ✅
- **File:** `app/Http/Controllers/AdminReportsController.php`
- **Fix:** Added `'user_posts' => UserPosts::where('id', $targetId)->update(['report_count' => 0])` case and imported `UserPosts` model.

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

| Priority | Count | Status |
|---|---|---|
| 🚨 P1 — Runtime errors | 4 | ✅ All fixed (2026-06-24) |
| ⚠️ P2 — Missing features | 3 | Pending |
| ⚠️ P3 — UI not cleaned up | 3 | Pending |
| 📝 P4 — Naming / dead code | 4 | Pending |

**Total remaining: 10 items**
