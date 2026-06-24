# Report System Brainstorming & Upgrade Plan
**Date:** 2026-06-24
**Status:** Revised — Simplified for School Assignment

---

## 1. Current Problems (Real Bugs)

### Bug 1: `report_count` is Dead Code
- The column exists on `users` table but is **never incremented** when reports are filed
- `AdminUsersController` reads it for filtering, but it's always 0

### Bug 2: Enum Mismatch on Notifications
- `notificatioins.target_type` enum is `['posts', 'comments']`
- But `ReportsController::notifyAdminsAboutUserReport()` writes `'users'`
- Fails on MySQL strict mode or silently stores invalid value

### Bug 3: Posts Model `reports()` Relation Bug
- Filters by `target_name = 'post'` (singular) instead of `'posts'` (plural)
- The relation never matches anything

### Bug 4: No Duplicate Report Prevention
- A user can report the same post/comment/user unlimited times
- Creates noise in the admin report table

### Bug 5: `user_posts` Report Type is Incomplete
- The enum includes `user_posts` but no submission or display code exists

### Bug 6: Inconsistent Ban Notifications
- Comment bans notify the author, but post bans are silent

---

## 2. Revised Requirements (Simplified)

### 2.1 One Report Per User Per Target
**Rule:** A user can report a specific post/comment/user **only once**.

**Implementation:**
- Add unique constraint on `(user_id, target_name, target_id)` in `reports` table
- If user already reported → show "You have already reported this" toast
- **Self-reporting blocked:** Users cannot report their own content

**When admin resolves a report:**
- Delete the old report records for that target
- This frees up the same users to report again if the problem comes back
- No need for a `status` column on reports (resolved/unresolved)

### 2.2 Report Count (Actually Working)
**Rule:** When a report is filed, increment `report_count` on the target.

| Target | Column to Increment |
|--------|-------------------|
| `posts` | `posts.report_count` (need to add column) |
| `comments` | `comments.report_count` (need to add column) |
| `user_posts` | `user_posts.report_count` (need to add column) |
| `users` | `users.report_count` (already exists, just not used) |

### 2.3 Notifications — Owner + Admin on Every Report
**Rule:** Both the content owner AND admin get notified on **every single report**.

| Report Target | Who Gets Notified |
|---------------|-------------------|
| Post | Post creator + Admin |
| Comment | Comment author + Admin |
| User | The reported user + Admin |

**Message format:** "Your [post/comment] has been reported." (owner) / "[Target] has been reported." (admin)

### 2.4 Unsecure Tag on Notifications (3+ Reports)
**Rule:** When a target's `report_count >= 3`, the notification gets a distinct visual tag.

**Implementation:**
- Check `report_count` on the target when creating the notification
- If `report_count >= 3`, add a flag/tag to the notification (e.g., `is_unsecure = true` or a distinct message format)
- In the notification dropdown, show a red badge or "⚠️ Unsecure" label on those notifications

### 2.5 Mark All as Read
**Rule:** Add a "Mark all as read" button in the notification box.

**Implementation:**
- New route: `POST /notifications/mark-all-read`
- Sets `is_read = true` for all notifications belonging to the current user
- Button appears in the notification dropdown

### 2.6 User Profile Unsecure Tag
**Rule:** If a user's `report_count >= 3`, show an "Unsecure" badge on their profile.

**Implementation:**
- Check `users.report_count` when rendering the profile
- Show a visual badge (e.g., yellow/red warning) if `report_count >= 3`
- This is display-only — no status change, no cascade

### 2.7 User Tab Unsecure Tag (on Posts)
**Rule:** On a post's user tab (the list of contributors/descriptions), show "Unsecure" next to users whose `report_count >= 3`.

**Implementation:**
- When rendering the user tab, check each user's `report_count`
- Show a small badge next to unsecure users
- This is display-only — no content status change

### 2.8 Banned User Login Block
**Rule:** If a user is banned (`status = 'banned'`), block login and show a message.

**Implementation:**
- Check `users.status` on login (both regular and OAuth)
- If banned → show: "Your account has been banned. Reason: [reason]"
- Block login entirely

---

## 3. Database Schema Changes

### 3.1 Migration: `add_report_count_to_content_tables`

#### `posts` table
```php
$table->integer('report_count')->default(0)->after('url');
```

#### `user_posts` table
```php
$table->integer('report_count')->default(0)->after('user_hidden');
```

#### `comments` table
```php
$table->integer('report_count')->default(0)->after('content');
```

*(No changes to `users` — `report_count` already exists)*

### 3.2 Migration: `add_unique_constraint_and_indexes_to_reports`

```php
// Unique constraint (one report per user per target)
$table->unique(['user_id', 'target_name', 'target_id'], 'unique_user_target_report');

// Indexes for performance
$table->index(['target_name', 'target_id']);
$table->index('admin_read');
$table->index('created_at');
```

### 3.3 Migration: `add_status_and_ban_fields_to_users`

```php
$table->enum('status', ['verified', 'unsecure', 'banned'])->default('verified')->after('role');
$table->foreignId('banned_by')->nullable()->after('status');
$table->timestamp('banned_at')->nullable()->after('banned_by');
$table->string('ban_reason')->nullable()->after('banned_at');
```

**Decision on `is_verified`:** Keep it for backward compatibility. Map:
- `status = 'verified'` → `is_verified = true`
- `status != 'verified'` → `is_verified = false`

### 3.4 Migration: `fix_notification_target_type_enum`

```php
// Update enum to include 'users'
$table->enum('target_type', ['posts', 'comments', 'users'])->change();
```

### 3.5 Final Schema Summary

```
reports (no new columns, just constraint)
├── id
├── user_id (FK → users)
├── target_name (enum: posts, comments, user_posts, users)
├── target_id
├── reason
├── admin_read (boolean)
├── timestamps
└── UNIQUE(user_id, target_name, target_id)  ← NEW

posts
├── id, title, slug, url
├── report_count (integer, default 0)    ← NEW
├── timestamps, deleted_at

user_posts
├── id, post_id, user_id, description, user_hidden
├── report_count (integer, default 0)    ← NEW
├── timestamps, deleted_at

comments
├── id, user_id, post_id, content
├── report_count (integer, default 0)    ← NEW
├── timestamps, deleted_at

users
├── id, name, slug, role, email, ...
├── status (enum: verified, unsecure, banned) ← NEW
├── banned_by (FK → users, nullable)          ← NEW
├── banned_at (timestamp, nullable)            ← NEW
├── ban_reason (string, nullable)              ← NEW
├── report_count (integer, default 0)          ← EXISTS (but never used)
├── timestamps, deleted_at

notificatioins
├── id, to_user_id, from_user_id
├── target_type (enum: posts, comments, users) ← FIX enum
├── target_id, message, is_read
├── timestamps
```

---

## 4. Logic Flow (Step by Step)

### 4.1 User Submits a Report

```
User clicks "Report" on a post/comment/user
    │
    ├─ Check: Is user reporting their own content?
    │   ├─ YES → Show "You cannot report your own content" toast → STOP
    │   └─ NO → Continue
    │
    ├─ Check: Has this user already reported this target?
    │   ├─ YES → Show "You have already reported this" toast → STOP
    │   └─ NO → Continue
    │
    ├─ Create report in `reports` table
    │
    ├─ Increment `report_count` on target:
    │   ├─ posts.report_count += 1
    │   ├─ comments.report_count += 1
    │   ├─ user_posts.report_count += 1
    │   └─ users.report_count += 1
    │
    ├─ Notify OWNER (every report):
    │   ├─ Post report → notify post creator
    │   ├─ Comment report → notify comment author
    │   └─ User report → notify the reported user
    │   Message: "Your [post/comment] has been reported."
    │
    ├─ Notify ADMIN (every report):
    │   ├─ Check target's report_count
    │   ├─ If report_count >= 3 → notification with "⚠️ Unsecure" tag
    │   └─ If report_count < 3 → normal notification
    │
    └─ Show success toast to reporter
```

### 4.2 Admin Resolves a Report

```
Admin opens report → takes action on content → resolves
    │
    ├─ Delete all report records for that target
    ├─ Reset report_count on the target to 0
    ├─ Log in AuditLogs
    └─ Content is now "clean" — users can re-report if needed
```

### 4.3 Mark All as Read

```
User clicks "Mark all as read" in notification box
    │
    ├─ Update all notifications for current user: is_read = true
    └─ Refresh notification dropdown
```

### 4.4 Banned User Login

```
Banned user tries to login (regular or OAuth)
    │
    ├─ Check users.status = 'banned'
    ├─ Show error: "Your account has been banned. Reason: [reason]"
    └─ Block login entirely
```

---

## 5. Files to Change

### 5.1 New Migrations
- `add_report_count_to_content_tables.php` — posts, user_posts, comments report_count
- `add_unique_constraint_and_indexes_to_reports.php` — unique constraint + indexes
- `add_status_and_ban_fields_to_users.php` — users status, banned_by, banned_at, ban_reason
- `fix_notification_target_type_enum.php` — add 'users' to enum

### 5.2 Models to Update
- `Reports.php` — add unique validation, add `userPost()` relation
- `Posts.php` — fix `reports()` relation (`'post'` → `'posts'`), add report_count fillable
- `Comments.php` — add report_count fillable
- `UserPosts.php` — add report_count fillable
- `User.php` — add status field, ban methods, login check

### 5.3 Controllers to Update
- `ReportsController.php` — add duplicate check, self-report block, increment report_count, notify owner + admin, add `user_posts` submission
- `AdminReportsController.php` — add resolve action (delete reports + reset count)
- `NotificationOpenController.php` — fix enum routing, add `markAllAsRead` method
- `AuthenticatedSessionController.php` — add banned-user login block
- `CommentsController.php` — fix ban to also notify owner consistently

### 5.4 Views to Update
- Report modal — show "already reported" state
- Notification dropdown — show unsecure tag on 3+ report notifications, add "Mark all as read" button
- User profile — show unsecure badge if report_count >= 3
- Post user tab — show unsecure tag next to users with report_count >= 3
- Login page — show banned message

### 5.5 New/Updated Routes
```php
// Mark all notifications as read
Route::post('/notifications/mark-all-read', [NotificationOpenController::class, 'markAllAsRead'])->name('notifications.mark-all-read');

// Report resolve (admin)
Route::delete('/menu/reports/{report}/resolve', [AdminReportsController::class, 'resolve'])->name('reports.resolve');
```

---

## 6. Implementation Order

### Phase 1: Bug Fixes (Start Here)
1. Fix `report_count` — actually increment it in `ReportsController`
2. Fix `notificatioins.target_type` enum to include `'users'`
3. Fix Posts model `reports()` relation (`'post'` → `'posts'`)
4. Add `user_posts` report submission endpoint

### Phase 2: Duplicate Prevention
5. Add unique constraint migration
6. Add duplicate check in `ReportsController`
7. Add self-reporting block
8. Show "already reported" toast in UI

### Phase 3: Notifications
9. Update `ReportsController` to notify both owner AND admin on every report
10. Add unsecure tag logic (check report_count >= 3 when creating notification)
11. Add "Mark all as read" button + route
12. Update notification dropdown UI

### Phase 4: User Status & Bans
13. Add `status`, `banned_by`, `banned_at`, `ban_reason` to users
14. Update `AuthenticatedSessionController` to block banned users
15. Add unsecure badge on user profile (display only)
16. Add unsecure tag on user tab in posts (display only)

### Phase 5: Admin Resolve
17. Add resolve action to `AdminReportsController` (delete reports + reset count)
18. Update admin reports UI with resolve button

---

## 7. Edge Cases

| Scenario | Handling |
|----------|----------|
| User reports their own content | Blocked → "You cannot report your own content" |
| User reports same target twice | Blocked by unique constraint → "Already reported" |
| Admin resolves, user re-reports | Old reports deleted, user can report again |
| Report target was deleted | Show "Content no longer available" in report table |
| User with report_count >= 3 gets reported again | Count increments, notification still shows unsecure tag |
| Banned user tries OAuth | Check status on OAuth callback, block login |

---

## 8. Testing Checklist

### Phase 1: Bug Fixes ✅
- [x] `report_count` increments on target when reported
- [x] Notification enum includes 'users' target type
- [x] Posts model `reports()` relation fixed (`'post'` → `'posts'`)
- [x] `user_posts` report submission endpoint added

### Phase 2: Duplicate Prevention ✅
- [x] User can report a post only once
- [x] User cannot report their own content
- [x] Unique constraint on `(user_id, target_name, target_id)`

### Phase 3: Notifications ✅
- [x] Owner gets notified on every report
- [x] Admin gets notified on every report
- [x] Notifications with report_count >= 3 show unsecure tag
- [x] "Mark all as read" button works
- [x] Notification routing redirects to content page

### Phase 4: User Status & Bans ✅
- [x] User profile shows unsecure badge when report_count >= 3
- [x] User tab shows unsecure tag for unsecure users
- [x] Banned user cannot login (regular + OAuth)
- [x] Hover profile card shows unsecure badge

### Phase 5: Admin Resolve (Pending)
- [ ] Admin can resolve a report (deletes records, resets count)
- [ ] All audit logs created correctly

---

**Next Step:** Start with Phase 5 — Admin Resolve.
