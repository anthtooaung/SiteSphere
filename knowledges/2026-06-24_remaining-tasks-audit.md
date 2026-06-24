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
- **Fix:** Removed the "Delete Report" button/form from the POST reports table row.

### ~~9. Delete button still visible on COMMENT report table~~ ✅
- **Fix:** Removed the "Delete Report" button/form from the COMMENT reports table row.

### ~~10. Restore button still visible on POST report table~~ ✅
- **Fix:** Removed the "Restore Post" button/form. Kept the "Mark Secure" button for unsecure posts.

---

## ~~📝 Priority 4 — Audit Log Naming / Missing Actions~~ ✅ FIXED

### ~~11. Audit action names differ from spec~~ ✅
- **Fix:** Renamed `mark_unsecure_post`/`mark_secure_post` to `set_unsecure`/`set_verified` in `PostsController::toggleUnsecure()`. Added explicit `category` based on direction (`moderation` for unsecure, `resolved` for verified).

### ~~12. `set_verified` audit action not implemented~~ ✅
- **Fix:** `PostsController::toggleUnsecure()` and `AdminUsersController::toggleUnsecure()` now log distinct actions (`set_unsecure` / `set_verified`) with appropriate categories depending on the toggle direction.

### ~~13. `review_flagged_content` audit action not implemented~~ ✅ N/A
- **Status:** Feature does not exist in the codebase. No "flagged content review" UI or controller logic was ever built. This spec item is not applicable.

### ~~14. Stale `audits.unban` route~~ ✅
- **Fix:** Removed `audits.unban` route from `web.php` and removed `unbanAudit()` method from `PostsController`. No views referenced this route.

---

## Summary Count

| Priority | Count | Status |
|---|---|---|
| 🚨 P1 — Runtime errors | 4 | ✅ All fixed (2026-06-24) |
| ⚠️ P2 — Missing features | 3 | ✅ All fixed (2026-06-24) |
| ⚠️ P3 — UI not cleaned up | 3 | ✅ All fixed (2026-06-24) |
| 📝 P4 — Naming / dead code | 4 | ✅ All fixed (2026-06-24) |

**All 14 items resolved. Zero remaining.**
