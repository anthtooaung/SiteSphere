# Report System Brainstorming & Upgrade Plan
**Date:** 2026-06-24
**Status:** Planning Phase

---

## 1. Current Problems

### Problem 1: No Duplicate Report Prevention
- A user can report the same post/comment/user unlimited times
- Creates noise in the admin report table
- No way to track if a report was already submitted

### Problem 2: No Status Tracking
- `admin_read` boolean is too simple
- No way to mark reports as resolved/dismissed
- No audit trail of admin actions on reports

### Problem 3: Silent Bans
- When admin bans a post/comment, the owner is NOT notified
- No feedback loop to content creators

### Problem 4: No Cascade Effect
- Banning a user doesn't affect their content
- No way to mark a user as "unsecure" before banning

### Problem 5: No URL Security
- A banned post's URL can be reused without warning
- No way to flag "unsecure" URLs

---

## 2. New Requirements (Finalized)

### 2.1 One Report Per User Per Target
**Rule:** A user can report a specific post/comment/description/user **only once**.

**Implementation:**
- Add unique constraint on `(user_id, target_name, target_id)` in `reports` table
- If user already reported → show "You have already reported this" toast
- **Self-reporting blocked:** Users cannot report their own content

### 2.2 Status-Based Target Tracking
**New Status Enum:**

| Target | Status Values | Default |
|--------|---------------|---------|
| `posts` | `active`, `unsecure` | `active` |
| `user_posts` (descriptions) | `active`, `unsecure`, `banned` | `active` |
| `comments` | `active`, `unsecure`, `banned` | `active` |
| `users` | `verified`, `unsecure`, `banned` | `verified` |

### 2.3 Threshold-Based "Unsecure" Status
**Rule:** When a target accumulates **3 or more reports** → auto-set status to `unsecure`.

**What happens when target becomes unsecure:**
- Target gets `status = 'unsecure'`
- `report_count` updated on the target
- **Admin notification sent** (only at threshold, not on every report)

### 2.4 Owner Notifications (Every Report)
**Rule:** Owner gets notified on **every single report**, regardless of count.

| Report Target | Who Gets Notified |
|---------------|-------------------|
| Post | Post creator (via `user_posts.user_id`) |
| Description | Description author (`user_posts.user_id`) |
| Comment | Comment author (`comments.user_id`) |
| User | The reported user themselves |

**Message format:** "Your [post/description/comment] has been reported."

### 2.5 Admin Notifications (Threshold Only)
**Rule:** Admin only gets notified when a target hits **3+ reports** (unsecure status).

**NOT notified on:**
- 1st report
- 2nd report
- Reports after target is already unsecure

**Notified on:**
- Target crossing 3-report threshold
- Target crossing threshold again after admin reset

### 2.6 Admin Notification Routing
**When admin clicks notification:**
1. Mark notification as read
2. Mark ALL reports for that target as `admin_read = true`
3. Redirect DIRECTLY to the content page:
   - Post → `/posts/{slug}`
   - Comment → `/posts/{slug}#comment-{id}`
   - Description → `/posts/{slug}#description-{id}`
   - User → `/profile/{name}`

### 2.7 Post Unsecure Logic
**Admin can:**
- Mark post as unsecure (sets `status = 'unsecure'`)
- Remove unsecure status (sets `status = 'active'`, resets `report_count = 0`)

**URL Security:**
- When post becomes unsecure → its URL is flagged
- When any user creates a new post with that URL → show warning:
  - "This URL has been flagged as unsecure"
  - Link to the unsecure post
- User can still proceed OR cancel

**Admin CANNOT ban posts** — only toggle unsecure status.

### 2.8 User Unsecure/Ban Logic

**Cascade Effect (Bidirectional):**

```
User hits 3+ reports → status = 'unsecure'
    │
    ├─ All user's user_posts → status = 'unsecure'
    ├─ All user's comments → status = 'unsecure'
    └─ All posts where user is sole author → status = 'unsecure'

Admin restores user to 'verified'
    │
    ├─ User → status = 'verified', report_count = 0
    ├─ All user_posts → status = 'active', report_count = 0
    ├─ All comments → status = 'active', report_count = 0
    └─ All posts → status = 'active', report_count = 0

Admin bans user
    │
    ├─ User → status = 'banned', banned_by, banned_at, ban_reason
    ├─ Soft-delete user (deleted_at = now)
    ├─ All user_posts → status = 'banned', soft-delete
    ├─ All comments → status = 'banned', soft-delete
    └─ All posts → status = 'banned', soft-delete

Admin restores banned user
    │
    ├─ User → status = 'verified', clear ban fields, restore deleted_at
    ├─ All user_posts → status = 'active', restore deleted_at
    ├─ All comments → status = 'active', restore deleted_at
    └─ All posts → status = 'active', restore deleted_at
```

### 2.9 Banned User Login Flow
**When banned user tries to login:**
1. Check `users.status = 'banned'`
2. Show error: "Your account has been banned by [admin name]. Reason: [reason]"
3. Block login entirely

**OAuth login also blocked:**
- Check ban status on Google OAuth callback
- If banned email tries OAuth → show same banned message

### 2.10 Description/Comment Unsecure/Ban Logic

**Same pattern as user:**

```
Description/Comment hits 3+ reports → status = 'unsecure'
    │
    └─ Owner notified: "Your [description/comment] has been marked as unsecure"

Admin bans description/comment
    │
    ├─ status = 'banned', banned_by, banned_at
    ├─ Soft-delete
    └─ Notify owner: "Your [description/comment] has been banned"

Admin restores description/comment
    │
    ├─ status = 'active', clear ban fields, report_count = 0
    ├─ Restore deleted_at
    └─ Notify owner: "Your [description/comment] has been restored"
```

---

## 3. Database Schema Changes

### 3.1 Migration: `add_status_columns_to_content_tables`

#### `posts` table
```php
$table->enum('status', ['active', 'unsecure'])->default('active')->after('url');
$table->foreignId('status_by')->nullable()->after('status');   // admin who set status
$table->timestamp('status_at')->nullable()->after('status_by'); // when status changed
$table->integer('report_count')->default(0)->after('status_at'); // denormalized count
```

#### `user_posts` table
```php
$table->enum('status', ['active', 'unsecure', 'banned'])->default('active')->after('user_hidden');
$table->foreignId('banned_by')->nullable()->after('status');
$table->timestamp('banned_at')->nullable()->after('banned_by');
$table->integer('report_count')->default(0)->after('banned_at'); // denormalized count
```

#### `comments` table
```php
$table->enum('status', ['active', 'unsecure', 'banned'])->default('active')->after('content');
$table->foreignId('banned_by')->nullable()->after('status');
$table->timestamp('banned_at')->nullable()->after('banned_by');
$table->integer('report_count')->default(0)->after('banned_at'); // denormalized count
```

#### `users` table
```php
// Replace is_verified boolean with status enum
$table->enum('status', ['verified', 'unsecure', 'banned'])->default('verified')->after('role');
$table->foreignId('banned_by')->nullable()->after('status');
$table->timestamp('banned_at')->nullable()->after('banned_by');
$table->string('ban_reason')->nullable()->after('banned_at');
```

**Decision on `is_verified`:** Keep it for backward compatibility. Map:
- `status = 'verified'` → `is_verified = true`
- `status != 'verified'` → `is_verified = false`

### 3.2 Migration: `update_reports_table`

```php
// Add unique constraint (one report per user per target)
$table->unique(['user_id', 'target_name', 'target_id'], 'unique_user_target_report');

// Add indexes for performance
$table->index(['target_name', 'target_id']);
$table->index('admin_read');
$table->index('created_at');
```

### 3.3 Final Schema Summary

```
reports
├── id
├── user_id (FK → users)          -- reporter
├── target_name (enum: posts, comments, user_posts, users)
├── target_id
├── reason
├── admin_read (boolean)
├── timestamps
└── UNIQUE(user_id, target_name, target_id)

posts
├── id, title, slug, url (unique)
├── status (enum: active, unsecure)      ← NEW
├── status_by (FK → users, nullable)     ← NEW
├── status_at (timestamp, nullable)      ← NEW
├── report_count (integer, default 0)    ← NEW
├── timestamps, deleted_at

user_posts
├── id, post_id, user_id, description, user_hidden
├── status (enum: active, unsecure, banned) ← NEW
├── banned_by (FK → users, nullable)        ← NEW
├── banned_at (timestamp, nullable)          ← NEW
├── report_count (integer, default 0)       ← NEW
├── timestamps, deleted_at

comments
├── id, user_id, post_id, content
├── status (enum: active, unsecure, banned) ← NEW
├── banned_by (FK → users, nullable)        ← NEW
├── banned_at (timestamp, nullable)          ← NEW
├── report_count (integer, default 0)       ← NEW
├── timestamps, deleted_at

users
├── id, name, slug, role, email, ...
├── status (enum: verified, unsecure, banned) ← NEW (replaces is_verified)
├── banned_by (FK → users, nullable)           ← NEW
├── banned_at (timestamp, nullable)             ← NEW
├── ban_reason (string, nullable)               ← NEW
├── report_count (integer)                      ← EXISTS
├── timestamps, deleted_at
```

---

## 4. Logic Flow (Step by Step)

### 4.1 User Submits a Report

```
User clicks "Report" on a post/comment/description/user
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
    ├─ Increment `report_count` on target (denormalized column):
    │   ├─ posts.report_count += 1
    │   ├─ user_posts.report_count += 1
    │   ├─ comments.report_count += 1
    │   └─ users.report_count += 1 (already exists)
    │
    ├─ Notify OWNER (every report):
    │   ├─ Post report → notify post creator (via user_posts.user_id)
    │   ├─ Description report → notify description author (user_posts.user_id)
    │   ├─ Comment report → notify comment author (comments.user_id)
    │   └─ User report → notify the reported user
    │   Message: "Your [post/description/comment] has been reported."
    │
    ├─ Check report_count for this target:
    │   ├─ Count < 3 → Show in admin report table only (no admin noti)
    │   └─ Count >= 3 AND target not already 'unsecure':
    │       ├─ Set target status = 'unsecure'
    │       ├─ If target is USER → cascade to their content
    │       ├─ Notify ALL admins: "[Target] has reached 3+ reports and is now unsecure"
    │       └─ Admin noti contains target_type + target_id for direct redirect to content
    │
    └─ Show success toast to reporter
```

### 4.2 Admin Opens a Report Notification (Direct to Content)

```
Admin clicks notification in notification box
    │
    ├─ NotificationOpenController:
    │   ├─ Mark notification as read (is_read = true)
    │   ├─ Mark ALL reports for this target as admin_read = true
    │   ├─ Log in AuditLogs
    │   └─ Redirect DIRECTLY to the content:
    │       ├─ target_type = 'posts' → /posts/{slug}
    │       ├─ target_type = 'comments' → /posts/{slug}#comment-{id}
    │       ├─ target_type = 'user_posts' → /posts/{slug}#description-{id}
    │       └─ target_type = 'users' → /profile/{name}
    │
    └─ Done — admin lands on the actual content page
       (no intermediate report page visit)
```

### 4.3 Admin Actions

#### Post (toggle unsecure only)
```
Admin sees unsecure post → can "Mark as Safe"
    │
    ├─ Set posts.status = 'active'
    ├─ Set posts.status_by = admin.id, status_at = now()
    ├─ Reset posts.report_count = 0 (allows re-triggering at 3 new reports)
    ├─ AuditLogs: action = 'mark_post_safe'
    └─ Post no longer shows unsecure warning

Admin sees active post with reports → can "Mark as Unsecure"
    │
    ├─ Set posts.status = 'unsecure'
    ├─ Set posts.status_by = admin.id, status_at = now()
    ├─ AuditLogs: action = 'mark_post_unsecure'
    └─ Post shows unsecure badge
```

#### User (ban / restore)
```
Admin bans user:
    │
    ├─ Set users.status = 'banned'
    ├─ Set users.banned_by = admin.id, banned_at = now(), ban_reason = reason
    ├─ Soft-delete user (deleted_at = now)
    ├─ Cascade: all user's user_posts, comments → status = 'banned', banned_by = admin
    ├─ AuditLogs: action = 'ban_user'
    ├─ Send email: "Your account has been banned. Reason: {reason}"
    └─ On next login attempt: "Your account has been banned by {admin_name}"

Admin restores user (from banned OR unsecure → verified):
    │
    ├─ Set users.status = 'verified'
    ├─ Clear banned_by, banned_at, ban_reason
    ├─ Reset users.report_count = 0
    ├─ Restore user (deleted_at = null)
    ├─ CASCADE: All user's content auto-restores:
    │   ├─ user_posts: status = 'active', clear banned_by/banned_at, report_count = 0, restore deleted_at
    │   ├─ comments: status = 'active', clear banned_by/banned_at, report_count = 0, restore deleted_at
    │   └─ posts (where user is author): status = 'active', clear status_by/status_at, report_count = 0
    ├─ AuditLogs: action = 'restore_user'
    └─ Send email: "Your account has been restored"

**Important:** When admin changes user from unsecure to verified, ALL of the user's content (posts, descriptions, comments) automatically changes back to normal (active) status.
```

#### Description / Comment (ban / restore)
```
Admin bans description/comment:
    │
    ├─ Set status = 'banned', banned_by = admin.id, banned_at = now()
    ├─ Soft-delete the record
    ├─ Notify owner: "Your [description/comment] has been banned. Reason: {reason}"
    ├─ AuditLogs: action = 'ban_description' / 'ban_comment'
    └─ If it was a comment → also delete associated rating (existing logic)

Admin restores description/comment:
    │
    ├─ Set status = 'active', clear banned_by, banned_at
    ├─ Reset report_count = 0
    ├─ Restore the record (deleted_at = null)
    ├─ Notify owner: "Your [description/comment] has been restored"
    └─ AuditLogs: action = 'restore_description' / 'restore_comment'
```

### 4.4 URL Security Check (Post Creation)

```
User submits new post with URL
    │
    ├─ Check: Does any post exist with this URL AND status = 'unsecure'?
    │   (Include soft-deleted posts via withTrashed())
    │
    ├─ YES → Show warning:
    │   "This URL has been flagged as unsecure. [Link to the unsecure post]"
    │   User can still proceed OR cancel
    │
    └─ NO → Proceed with normal creation
```

### 4.5 User Cascade (Both Directions)

```
User hits 3+ reports → status changes to 'unsecure'
    │
    ├─ All user's user_posts → status = 'unsecure'
    ├─ All user's comments → status = 'unsecure'
    ├─ All posts where user is the sole author → status = 'unsecure'
    │
    └─ These show "unsecure" badge in the UI
       Other users see warning when interacting with this content

Admin restores user to 'verified'
    │
    ├─ All user's user_posts → status = 'active', report_count = 0
    ├─ All user's comments → status = 'active', report_count = 0
    ├─ All posts where user is the sole author → status = 'active', report_count = 0
    │
    └─ All unsecure badges removed, content fully restored
```

---

## 5. Files to Change

### 5.1 New Migrations
- `add_status_columns_to_content_tables.php` — posts, user_posts, comments status columns
- `add_status_and_ban_fields_to_users.php` — users status, banned_by, banned_at, ban_reason
- `add_unique_constraint_and_indexes_to_reports.php` — unique constraint + indexes

### 5.2 Models to Update
- `Reports.php` — add unique validation rule
- `Posts.php` — add status field, scopes (active, unsecure)
- `UserPosts.php` — add status field, scopes
- `Comments.php` — add status field, scopes
- `User.php` — replace is_verified with status, add ban methods, login check

### 5.3 Controllers to Update
- `ReportsController.php` — add duplicate check, owner notifications, threshold logic
- `AdminReportsController.php` — add mark-safe/mark-unsecure actions, update open() routing
- `PostsController.php` — remove ban/unban, add toggleUnsecure; add URL security check in store()
- `CommentsController.php` — update ban to use status field
- `AdminUsersController.php` — update ban to use status field + email blocking
- `NotificationOpenController.php` — redirect directly to content (not report page)
- `AuthenticatedSessionController.php` — add banned-user login block
- `DashboardController.php` — update activity log for new status values

### 5.4 Views to Update
- Report modal — show "already reported" state
- Admin reports page — add mark-safe/unsecure buttons per tab
- Post detail page — show unsecure badge if post is unsecure
- Post creation form — show URL security warning
- User profile — show unsecure/banned badge
- Login page — show banned message with admin name and reason
- Notification dropdown — route correctly for all target types

### 5.5 New/Updated Routes
```php
// Post status toggle (replaces ban/unban)
Route::patch('/posts/{post}/toggle-unsecure', [PostsController::class, 'toggleUnsecure'])->name('posts.toggle-unsecure');

// Description ban/restore
Route::patch('/descriptions/{userPost}/ban', [PostsController::class, 'banAudit'])->name('descriptions.ban');
Route::patch('/descriptions/{userPost}/restore', [PostsController::class, 'restoreAudit'])->name('descriptions.restore');

// Comment ban/restore (already exist, update logic)
Route::patch('/comments/{comment}/ban', [CommentsController::class, 'ban'])->name('comments.ban');
Route::patch('/comments/{comment}/restore', [CommentsController::class, 'unban'])->name('comments.restore');

// Report open (already exists, update routing for descriptions)
Route::get('/menu/reports/{report}/open', [AdminReportsController::class, 'open'])->name('reports.open');
```

---

## 6. Edge Cases to Handle

| Scenario | Handling |
|----------|----------|
| User reports, admin resolves, user tries to re-report | Blocked by unique constraint → "Already reported" |
| User reports their own content | Blocked → "You cannot report your own content" |
| Post has 5 reports, admin marks safe, gets 6th report | Re-triggers unsecure when count reaches 3 again (after reset) |
| User banned → creates new account with same email | Email is already blocked in DB → show banned message |
| User banned → tries OAuth with same email | Check ban status on OAuth callback too |
| Description author != post author | Owner notification goes to description author |
| Post with no user_posts (edge case) | No owner to notify — only admin gets noti at threshold |
| Report target was deleted | Show "Content no longer available" in report table |
| Admin bans user who is also an admin | Block — cannot ban admin accounts (existing logic) |

---

## 7. Implementation Order

### Phase 1: Database Foundation
1. Create migration for status columns on all content tables
2. Create migration for users.status + ban fields
3. Create migration for reports unique constraint + indexes
4. Update all models with new fields and scopes

### Phase 2: Report Submission Logic
5. Add duplicate-report check in ReportsController
6. Add owner notifications (all report types)
7. Add threshold detection (3+ reports → set unsecure)
8. Add admin notifications (only at threshold)

### Phase 3: Admin Actions
9. Update PostsController: remove ban/unban, add toggleUnsecure
10. Update CommentsController: use status field for ban/restore
11. Update AdminUsersController: use status field for ban/restore
12. Update AdminReportsController: add new action methods

### Phase 4: URL Security & Cascade
13. Add URL security check in PostsController::store()
14. Implement user cascade on unsecure status change

### Phase 5: Auth & Login
15. Add banned-user login block in AuthenticatedSessionController
16. Show ban message with admin name and reason

### Phase 6: UI Updates
17. Update report modals with "already reported" state
18. Update admin reports page with new action buttons
19. Add unsecure badges across the UI
20. Add URL security warning on post creation
21. Update login page banned message
22. Update notification routing for all target types

---

## 8. Finalized Decisions

| # | Question | Decision |
|---|----------|----------|
| 1 | Self-reporting | **Blocked** — users cannot report their own content |
| 2 | Unsecure re-triggering | **Yes** — after admin marks safe, new reports can re-trigger unsecure at 3 |
| 3 | OAuth ban check | **Yes** — banned email blocks Google OAuth login too |
| 4 | Content restoration cascade | **Yes** — when admin restores user → all their content auto-restores to active |
| 5 | Admin unsecure→verified cascade | **Yes** — when admin changes user from unsecure to verified, all related data automatically changes to normal (active) |
| 6 | Report limit | **One report per user per target** — user can report a specific post/comment/description/user only once |

---

## 9. Testing Checklist

- [ ] User can report a post only once
- [ ] User cannot report their own content
- [ ] Owner gets notified on every report
- [ ] Admin only gets notified at 3+ reports
- [ ] Admin notification redirects to content
- [ ] Reports marked as read when admin opens notification
- [ ] Post can be marked/unmarked as unsecure
- [ ] URL security warning shows on post creation
- [ ] User becomes unsecure at 3+ reports
- [ ] User's content cascades to unsecure
- [ ] Admin can ban user (blocks email)
- [ ] Banned user sees ban message on login
- [ ] Banned user's OAuth login is blocked
- [ ] Admin can restore banned user
- [ ] Restored user's content cascades back to active
- [ ] Description/comment ban/restore works
- [ ] All audit logs created correctly

---

**Next Step:** Review this document, then begin Phase 1 implementation.
