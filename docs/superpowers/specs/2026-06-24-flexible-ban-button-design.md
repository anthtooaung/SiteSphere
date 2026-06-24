# Flexible Admin Actions Design — Unsecure/Secure + Delete
**Date:** 2026-06-24
**Status:** In Progress

---

## Implementation Progress

### Completed
- [x] Migration: `add_is_unsecure_to_content_tables` — `is_unsecure` on posts, user_posts, comments + `status`/`banned_by`/`banned_at`/`ban_reason` on users
- [x] Migration: Converts existing soft-deleted posts to `is_unsecure = true`
- [x] Models: Added `is_unsecure` cast to Posts, Comments, UserPosts
- [x] User model: Added `status`, `banned_by`, `banned_at`, `ban_reason` to fillable; added `isBanned()` and `isUnsecure()` methods
- [x] Auth: Added `isBanned()` check in `AuthenticatedSessionController::store()` and `SocialLoginController::callback()`
- [x] PostsController: Added `toggleUnsecure()`, URL blocking in `store()`, `deleteAudit()` (permanent)
- [x] CommentsController: Replaced `ban()`/`unban()` with permanent `delete()`
- [x] AdminUsersController: Added `toggleUnsecure()`, fixed `destroy()` to set `status='banned'`, fixed `restore()` to clear ban fields
- [x] Routes: Added `posts.toggle-unsecure`, `users.toggle-unsecure`, `comments.delete` (DELETE), `audits.delete` (DELETE); removed `comments.unban`, `audits.unban`
- [x] post-card.blade.php: Added `isUnsecure` prop, replaced Ban with Unsecure/Secure toggle
- [x] post-detail.blade.php: Added unsecure banner, replaced Ban Post with toggle, replaced Ban Description with Delete Description
- [x] comments.blade.php: Replaced Ban Comment with Delete Comment (permanent), removed Revert button
- [x] reports.blade.php: Added Mark Secure button for unsecure posts, removed Restore Comment, added Unsecure badge
- [x] profile-detail.blade.php: Added Mark Unsecure/Secure + Ban User buttons for admins
- [x] users.blade.php: Added Mark Unsecure/Secure toggle in actions, updated ban dialog text
- [x] hover-profile-card.blade.php: Added Unsecure badge

### Remaining
- [ ] URL blocking SweetAlert in post creation (currently throws ValidationException, spec wants a SweetAlert with "Visit Post" / "Cancel" buttons)
- [ ] Test migration on fresh database
- [ ] Verify all route names are correctly referenced in views

## Problem

The current admin actions are wrong:
- Posts use "Ban" (soft-delete) — should be "Unsecure/Secure" toggle
- Comments use "Ban" (soft-delete) — should be permanent delete
- Descriptions use "Ban" (soft-delete) — should be permanent delete
- Users have correct ban/unban but no unsecure/secure toggle
- Reports page uses generic text for delete/resolve dialogs

## New Action Model

| Content Type | Admin Action | Button Text | Effect |
|---|---|---|---|
| **Post** | Toggle unsecure | "Mark Unsecure" / "Mark Secure" | `is_unsecure` column. URL blocked from new posts when unsecure |
| **Comment** | Permanent delete | "Delete Comment" | Gone forever, no restore |
| **Description** | Permanent delete | "Delete Description" | Gone forever, no restore |
| **User** | Toggle unsecure | "Mark Unsecure" / "Mark Secure" | `status` column: `verified` ↔ `unsecure` |
| **User** | Ban/Unban | "Ban User" / "Unban User" | Soft-delete (`deleted_at`) + `status = 'banned'`. Login blocked for banned users (regular + OAuth/Gmail) |

## Database Changes

### New migration: `add_is_unsecure_to_content_tables`

```php
// posts table
$table->boolean('is_unsecure')->default(false)->after('report_count');

// user_posts table
$table->boolean('is_unsecure')->default(false)->after('report_count');

// comments table
$table->boolean('is_unsecure')->default(false)->after('report_count');
```

### Migration: Convert existing banned posts to unsecure

```php
// In the same migration or a separate one:
// Set is_unsecure = true on all soft-deleted posts
DB::table('posts')->whereNotNull('deleted_at')->update(['is_unsecure' => true]);
// Clear deleted_at so they're visible again as 'unsecure' posts
DB::table('posts')->whereNotNull('deleted_at')->update(['deleted_at' => null]);
```

This ensures existing "banned" posts become "unsecure" posts in the new system.

## Route Changes

### Remove / Replace
```php
// REMOVE: posts.ban route (soft-delete no longer used for posts)
// Route::post('/posts/{post}/ban', ...) → DELETE

// ADD: posts unsecure toggle
Route::post('/posts/{post}/toggle-unsecure', [PostsController::class, 'toggleUnsecure'])->name('posts.toggle-unsecure');

// KEEP: posts.unban (may still be needed for restore from existing bans)
// Route::post('/posts/{post}/unban', ...) → KEEP for now

// CHANGE: comments.ban → comments.delete (permanent)
Route::delete('/comments/{comment}/delete', [CommentsController::class, 'delete'])->name('comments.delete');
// REMOVE: comments.unban (no more restore)
// Route::post('/comments/{comment}/unban', ...) → DELETE

// CHANGE: audits.ban → audits.delete (permanent)
Route::delete('/audits/{userPost}/delete', [PostsController::class, 'deleteAudit'])->name('audits.delete');
// REMOVE: audits.unban (no more restore)
// Route::post('/audits/{userPost}/unban', ...) → DELETE

// ADD: user unsecure toggle (if not exists)
Route::post('/users/{user}/toggle-unsecure', [AdminUsersController::class, 'toggleUnsecure'])->name('users.toggle-unsecure');
```

## View Changes

### 1. Post Card — `post-card.blade.php`

Replace the "Ban" form with "Mark Unsecure" / "Mark Secure" toggle:

```blade
@if (Auth::user()?->role === 'admin')
    <form method="POST" action="{{ route('posts.toggle-unsecure', $postId) }}">
        @csrf
        <button type="submit" ...>
            <x-fas-shield-halved class="size-3" />
            <span>{{ $isUnsecure ? 'Mark Secure' : 'Mark Unsecure' }}</span>
        </button>
    </form>
@endif
```

- Icon: `fas-shield-halved` (🛡️) instead of `fas-ban`
- Color: yellow/amber for unsecure, green for secure
- Text changes based on current `$isUnsecure` state

### 2. Post Detail — `post-detail.blade.php`

Same change as post card — replace "Ban Post" form with unsecure toggle.

Remove the "banned" banner for posts. Replace with "unsecure" banner:
- Change title from "This post has been banned" to "This post is unsecure"
- Change icon from `fa-ban` to `fa-shield-halved`
- Change color from red to amber/yellow
- Remove restore/delete buttons (admin uses the dropdown toggle instead)
- Show URL block notice: "This URL cannot be used for new posts"

### 3. Comments — `comments.blade.php`

Replace "Ban Comment" (soft-delete) with "Delete Comment" (permanent):

```blade
<form method="POST" action="{{ route('comments.delete', $comment->id) }}"
    x-on:submit.prevent="window.sitesphereSwal.confirm({
        title: 'Delete this comment?',
        text: 'This will permanently delete this comment. This action cannot be undone.',
        icon: 'warning',
        confirmButtonColor: '#ef4444',
        confirmButtonText: 'Yes, delete it!'
    })...">
    @csrf
    @method('DELETE')
    <button type="submit">
        <x-fas-trash class="size-3" />
        <span>Delete Comment</span>
    </button>
</form>
```

- Remove the "Restore Comment" button (no more unban)
- Remove the ban reason textarea (permanent delete doesn't need a reason)

### 4. Descriptions (user_posts) — `post-detail.blade.php`

Replace "Ban" (soft-delete) with "Delete Description" (permanent):

```blade
<form method="POST" action="{{ route('audits.delete', $userPost->id) }}"
    x-on:submit.prevent="window.sitesphereSwal.confirm({
        title: 'Delete this description?',
        text: 'This will permanently delete this description. This action cannot be undone.',
        icon: 'warning',
        confirmButtonColor: '#ef4444',
        confirmButtonText: 'Yes, delete it!'
    })...">
    @csrf
    @method('DELETE')
    <button type="submit">
        <x-fas-trash class="size-3" />
        <span>Delete Description</span>
    </button>
</form>
```

- Remove the "Restore" / unban button for descriptions

### 5. Users — Profile Detail / Reports

Users keep TWO actions:
- "Mark Unsecure" / "Mark Secure" — toggles `status` between `verified` and `unsecure`
- "Ban User" / "Unban User" — soft-delete + sets `status` to `banned`

**Ban implementation** (in `AdminUsersController::destroy()`):
```php
$user->status = 'banned';
$user->banned_by = $admin->id;
$user->banned_at = now();
$user->ban_reason = $request->input('reason');
$user->save();
$user->delete(); // soft-delete (sets deleted_at)
```

**Unban implementation** (in `AdminUsersController::restore()`):
```php
$user->status = 'verified';
$user->banned_by = null;
$user->banned_at = null;
$user->ban_reason = null;
$user->save();
$user->restore(); // clears deleted_at
```

**Login blocking** (already implemented):
- `AuthenticatedSessionController::store()` — checks `$user->isBanned()` after authenticate
- `SocialLoginController` — checks `$user->isBanned()` for both existing social accounts and email-based users
- Both regular login and Google OAuth are blocked
- Shows error: "Your account has been banned. Reason: [reason]"

**Bug fix needed:** Current `destroy()` does `$user->delete()` without setting `status = 'banned'`. This means the login check (`isBanned()`) doesn't catch soft-deleted users. Must set `status = 'banned'` before soft-deleting.

### 6. Reports Page — `reports.blade.php`

**Posts tab:**
- Replace "Restore Post" / "Ban" buttons with "Mark Secure" / "Mark Unsecure" toggle
- Delete Report dialog: "Delete Post Report?"
- Resolve dialog: "Resolve All Post Reports?"
- After migration: existing soft-deleted posts become unsecure, so "Restore" becomes "Mark Secure"

**Comments tab:**
- Remove "Restore Comment" button (no more unban)
- Delete dialog: "Delete Comment Report?"
- Resolve dialog: "Resolve All Comment Reports?"

**Users tab:**
- Keep "Ban/Unban User" + "Mark Unsecure/Secure"
- Delete dialog: "Delete User Report?"
- Resolve dialog: "Resolve All User Reports?"

## URL Blocking on New Post Creation

When creating a new post:
1. Check if the submitted URL exists on any post where `is_unsecure = true`
2. If match found → show SweetAlert:
   - Title: "This URL has been flagged"
   - Text: "This URL is associated with an unsecure post. Would you like to visit that post or cancel?"
   - Buttons: "Visit Post" (redirect) / "Cancel" (go back)
3. If no match → proceed normally

**Implementation:** Add check in `PostsController::store()` before creating the post.

## Unsecure Badge Display

The unsecure badge shows in these places:
- Post card — if `posts.is_unsecure = true`
- Post detail — if post is unsecure
- Comment — if the **comment author** has `status = 'unsecure'` (not the comment itself)
- Description — if the **description author** has `status = 'unsecure'`
- User profile — if `users.status = 'unsecure'`
- Hover profile card — if user is unsecure

## Files to Change

### New Migrations
- `add_is_unsecure_to_content_tables.php` — `is_unsecure` on posts, user_posts, comments

### Models
- `Posts.php` — add `is_unsecure` to fillable
- `Comments.php` — add `is_unsecure` to fillable
- `UserPosts.php` — add `is_unsecure` to fillable

### Controllers
- `PostsController.php` — add `toggleUnsecure()`, add URL check in `store()`, remove `ban()` usage for posts
- `CommentsController.php` — replace `ban()` with permanent `delete()`, remove `unban()`
- `AdminUsersController.php` — add `toggleUnsecure()`, fix `destroy()` to set `status = 'banned'`, fix `restore()` to set `status = 'verified'`
- `AdminReportsController.php` — update resolve to handle new action types

### Views
- `post-card.blade.php` — replace Ban with Unsecure/Secure toggle
- `post-detail.blade.php` — replace Ban Post with toggle, replace Ban Description with Delete Description
- `comments.blade.php` — replace Ban Comment with Delete Comment, remove Restore
- `reports.blade.php` — update all 3 tabs with correct buttons and dialog text
- `profile-detail.blade.php` — add Mark Unsecure/Secure button alongside Ban/Unban

### Routes
- Add `posts.toggle-unsecure`
- Change `comments.ban` → `comments.delete` (DELETE method)
- Change `audits.ban` → `audits.delete` (DELETE method)
- Remove `comments.unban`, `audits.unban`
- Add `users.toggle-unsecure` if not exists

## Scope

- 4 new/modified migrations
- 4 controllers modified
- 6 blade files modified
- ~5 routes changed
- URL blocking logic in post creation
