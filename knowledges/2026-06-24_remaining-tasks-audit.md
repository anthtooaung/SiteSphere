# Remaining Tasks — Post-Audit Fix List
> Generated: 2026-06-24
> Sources: report-system-brainstorming, audit-log-tag-system, report-box-upgrade, flexible-ban-button-design

---

## ~~🚨 Priority 1 — Will Break at Runtime~~ ✅ FIXED

### ~~1. Stale `comments.unban` route → 500 error~~ ✅
- **Fix:** Removed the `comments.unban` route line.

### ~~2. Duplicate `deleteAudit()` in PostsController~~ ✅
- **Fix:** Removed the first (dead) definition, kept the second with full logic.

### ~~3. Duplicate `delete()` in CommentsController~~ ✅
- **Fix:** Removed the first (dead) definition, kept the second which includes notification to comment author.

### ~~4. `resetReportCount()` missing `user_posts` case~~ ✅
- **Fix:** Added `user_posts` case and imported `UserPosts` model.

---

## ~~⚠️ Priority 2 — Not Working As Specified~~ ✅ FIXED

### ~~5. Reports model missing `userPost()` relation~~ ✅
- **Fix:** Added `userPost(): BelongsTo` relation.

### ~~6. "Mark all as read" button missing from notification dropdown~~ ✅
- **Fix:** Added "Mark all read" button in both desktop dropdown and mobile overlay.

### ~~7. `restore_user` audit log category is `moderation` instead of `resolved`~~ ✅
- **Fix:** Passed `category: 'resolved'` explicitly.

---

## ~~⚠️ Priority 3 — Report Box Upgrade Missing Removals~~ ✅ FIXED

### ~~8. Delete button still visible on POST report table~~ ✅
- **File:** `resources/views/layout/menu/reports.blade.php`
- **Fix:** Removed the "Delete Report" button/form from the POST reports table row.

### ~~9. Delete button still visible on COMMENT report table~~ ✅
- **File:** `resources/views/layout/menu/reports.blade.php`
- **Fix:** Removed the "Delete Report" button/form from the COMMENT reports table row.

### ~~10. Restore button still visible on POST report table~~ ✅
- **File:** `resources/views/layout/menu/reports.blade.php`
- **Fix:** Removed the "Restore Post" button/form. Kept the "Mark Secure" button for unsecure posts.

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
| ⚠️ P2 — Missing features | 3 | ✅ All fixed (2026-06-24) |
| ⚠️ P3 — UI not cleaned up | 3 | ✅ All fixed (2026-06-24) |
| 📝 P4 — Naming / dead code | 4 | Pending |

**Total remaining: 4 items**
