# Flexible Admin Actions Design — Unsecure/Secure + Delete
**Date:** 2026-06-24
**Status:** Revised

---

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
| **User** | Ban/Unban | "Ban User" / "Unban User" | `status` column: → `banned` / back to `verified` |

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
- "Ban User" / "Unban User" — sets `status` to `banned` or back to `verified`

These already exist in the reports page and profile. Just need flexible text.

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
- `AdminUsersController.php` — add `toggleUnsecure()` if not exists
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
