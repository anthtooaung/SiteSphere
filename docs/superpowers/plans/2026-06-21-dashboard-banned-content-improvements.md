# Dashboard Activity Feed & Banned Content Handling

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Enhance the admin dashboard's recent activity feed with better UX, implement banned content visibility for admins, and add permanent delete capabilities.

**Architecture:**
- **Backend:** Extend `DashboardController` to fetch slugs for linking. Modify `PostsController::show` and `ProfileDetailController` to handle soft-deleted records for admins.
- **Frontend:** Update `admin-dashboard.js` timeline template. Add banned banner components. Create custom 404 page.
- **UX:** Color-only timeline indicators, clickable targets, "See more" button, banned content banners with revert/delete actions.

**Tech Stack:** Laravel 13, Tailwind CSS, Alpine.js, Blade, FontAwesome 6.

---

## Summary of Changes

### Dashboard Activity Feed
- Show 6-7 recent items (currently 4)
- Remove `tl-icon` — use only `tl-stone` color dot
- Make `tl-target` clickable with proper routing
- Add "See more" button inside `act-timeline-wrap`

### Banned Content Visibility
- Admins can view banned (soft-deleted) posts, comments, and user profiles
- Regular users see 404 for banned content
- Banned banner shows: who banned it, when, reason
- Admin actions: Revert (unban) or Delete Permanently

### Custom 404 Page
- Clean error page with "Go to Home" button

---

## Data Model Reference

| Entity | Ban Mechanism | Soft Deletes | Audit Target Type |
|--------|---------------|--------------|-------------------|
| Posts | `$post->delete()` + `user_hidden` | ✅ Yes | `App\Models\Post` |
| Comments | Soft delete | ✅ Yes | `App\Models\Comment` |
| Users | `deleted_at` set | ✅ Yes | `App\Models\User` |

### Audit Log Actions
- `ban_post` / `unban_post` — Post moderation
- `ban_comment` / `unban_comment` — Comment moderation
- `delete_user` / `restore_user` — User moderation
- `ban_audit` — Individual audit description hiding

---

## Task 1: Dashboard Controller — Fetch Slugs for Linking

**Files:**
- Modify: `app/Http/Controllers/DashboardController.php`

**Goal:** Query post slugs, user slugs, and comment→post relationships so the view can generate proper links.

- [ ] **Step 1: Change `take(4)` to `take(7)`**

```php
$recentAuditLogs = AuditLogs::query()
    ->with('user')
    ->latest()
    ->take(7)  // was take(4)
    ->get();
```

- [ ] **Step 2: Build slug mappings for linking**

```php
// After fetching $recentAuditLogs, build mappings:
$postIds = $recentAuditLogs
    ->where('target_type', Posts::class)
    ->pluck('target_id')
    ->unique();

$userIds = $recentAuditLogs
    ->where('target_type', User::class)
    ->pluck('target_id')
    ->unique();

// For comments, we need the post slug (comments belong to posts)
$commentIds = $recentAuditLogs
    ->where('target_type', Comments::class)
    ->pluck('target_id')
    ->unique();

// Fetch post slugs
$postSlugs = Posts::withTrashed()
    ->whereIn('id', $postIds)
    ->pluck('slug', 'id');

// Fetch user slugs
$userSlugs = User::withTrashed()
    ->whereIn('id', $userIds)
    ->pluck('slug', 'id');

// Fetch comment → post slug mapping
$commentPostSlugs = Comments::withTrashed()
    ->whereIn('id', $commentIds)
    ->with(['post' => fn($q) => $q->select('id', 'slug')])
    ->get()
    ->mapWithKeys(fn($c) => [$c->id => $c->post->slug ?? null])
    ->filter();

// Pass to view
return view('layout.menu.dashboard', [
    // ... existing params
    'postSlugs' => $postSlugs,
    'userSlugs' => $userSlugs,
    'commentPostSlugs' => $commentPostSlugs,
]);
```

---

## Task 2: Dashboard JS — Update Timeline Template

**Files:**
- Modify: `resources/js/admin-dashboard.js`

**Goal:** Remove `tl-icon`, make `tl-target` a link, add "See more" button.

- [ ] **Step 1: Update activity feed rendering**

In the `activityList` section, update the template:

```javascript
// Add slug mappings from window data
const postSlugs = data.postSlugs || {};
const userSlugs = data.userSlugs || {};
const commentPostSlugs = data.commentPostSlugs || {};

// Build target URL based on target type
function getTargetUrl(a) {
    if (a.targetType === 'App\\Models\\Post') {
        const slug = postSlugs[a.targetId];
        return slug ? `/posts/${slug}` : null;
    }
    if (a.targetType === 'App\\Models\\User') {
        const slug = userSlugs[a.targetId];
        return slug ? `/profile/${slug}` : null;
    }
    if (a.targetType === 'App\\Models\\Comment') {
        const postSlug = commentPostSlugs[a.targetId];
        return postSlug ? `/posts/${postSlug}#comment-${a.targetId}` : null;
    }
    return null;
}

activityList.innerHTML = acts.map(a => {
    const catLabel = categoryLabels[a.category] || defaultCategoryLabel;
    const targetUrl = getTargetUrl(a);
    const targetInfo = a.target
        ? targetUrl
            ? `<a href="${targetUrl}" class="tl-target">${a.target} #${a.targetId}</a>`
            : `<span class="tl-target">${a.target} #${a.targetId}</span>`
        : '';

    return `<div class="tl-item">
        <div class="tl-stone-col">
            <div class="tl-stone" style="background:${a.color};"></div>
            <div class="tl-line"></div>
        </div>
        <div class="tl-content">
            <div class="tl-meta">
                <span class="tl-badge" style="background:${a.color};">${catLabel}</span>
                <span class="tl-user">${a.user}</span>
            </div>
            <div class="tl-txt">${a.txt} ${targetInfo}</div>
            ${a.reason ? `<div class="tl-reason">${a.reason}</div>` : ''}
            <div class="tl-time">${a.time}</div>
        </div>
    </div>`;
}).join("");

// Add "See more" button if there are more items
if (acts.length >= 7) {
    const seeMoreHtml = `
        <div class="tl-see-more">
            <a href="/menu/dashboard/activity-log" class="tl-see-more-btn">
                See more activity <i class="fa-solid fa-arrow-right"></i>
            </a>
        </div>
    `;
    activityList.insertAdjacentHTML('beforeend', seeMoreHtml);
}
```

- [ ] **Step 2: Pass slug data from Blade to JS**

In `dashboard.blade.php`, update the `AdminDashboardData` object:

```php
<script>
    window.AdminDashboardData = {
        stats: @json($stats),
        recentActivity: @json($recentActivity->map(fn($log) => [
            'user' => $log->user->name ?? 'System',
            'txt' => $log->action_text ?? $log->action,
            'target' => class_basename($log->target_type),
            'targetId' => $log->target_id,
            'targetType' => $log->target_type,
            'category' => $log->category,
            'color' => $log->getColor(),
            'icon' => $log->getIcon(),
            'reason' => $log->reason,
            'time' => $log->created_at->diffForHumans(),
        ])),
        topPosts: @json($topPosts),
        postSlugs: @json($postSlugs ?? []),
        userSlugs: @json($userSlugs ?? []),
        commentPostSlugs: @json($commentPostSlugs ?? []),
    };
</script>
```

---

## Task 3: Dashboard CSS — See More Button & Cleanup

**Files:**
- Modify: `resources/css/admin-dashboard.css`

- [ ] **Step 1: Add "See more" button styles**

```css
/* ── SEE MORE BUTTON ── */
.tl-see-more {
    padding-top: 12px;
    border-top: 1px solid var(--ui-border);
    margin-top: 4px;
    text-align: center;
}
.tl-see-more-btn {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 8px 20px;
    font-size: 12px;
    font-weight: 700;
    color: var(--accent-color);
    background: color-mix(in srgb, var(--accent-color) 8%, transparent);
    border: 1px solid color-mix(in srgb, var(--accent-color) 20%, transparent);
    border-radius: 8px;
    text-decoration: none;
    transition: all 0.2s ease;
    cursor: pointer;
}
.tl-see-more-btn:hover {
    background: color-mix(in srgb, var(--accent-color) 15%, transparent);
    transform: translateY(-1px);
    box-shadow: 0 2px 8px color-mix(in srgb, var(--accent-color) 15%, transparent);
}
.tl-see-more-btn i {
    font-size: 10px;
    transition: transform 0.2s ease;
}
.tl-see-more-btn:hover i {
    transform: translateX(2px);
}

/* Make tl-target a link */
a.tl-target {
    text-decoration: none;
    cursor: pointer;
    transition: all 0.15s ease;
}
a.tl-target:hover {
    background: color-mix(in srgb, var(--accent-color) 20%, transparent);
    text-decoration: underline;
}
```

- [ ] **Step 2: Remove or comment out `.tl-icon` styles (optional cleanup)**

The `.tl-icon` styles can remain (they won't be used) or be removed for cleanliness.

---

## Task 4: PostsController — Handle Banned Post Viewing

**Files:**
- Modify: `app/Http/Controllers/PostsController.php`

**Goal:** Allow admins to view soft-deleted (banned) posts.

- [ ] **Step 1: Modify `show` method**

```php
public function show(Posts $posts): View
{
    // Handle banned (soft-deleted) posts
    if ($posts->trashed()) {
        abort_unless(auth()->user()?->role === 'admin', 404);

        // Load ban audit log
        $banLog = AuditLogs::query()
            ->with('user')
            ->where('target_type', Posts::class)
            ->where('target_id', $posts->id)
            ->where('action', 'ban_post')
            ->latest()
            ->first();

        return view('layout.post-detail', [
            'post' => $posts,
            'isBanned' => true,
            'banLog' => $banLog,
            'saved' => false,
            // ... other required vars with defaults
        ]);
    }

    // Existing show logic...
    $posts->load(['tags.categories', 'userPosts' => fn ($q) => $q->with('user.settings')->latest()]);
    // ...

    return view('layout.post-detail', [
        'post' => $posts,
        'isBanned' => false,
        'banLog' => null,
        'saved' => auth()->user()?->bookmarks()->where('post_id', $posts->id)->exists() ?? false,
        // ... other existing vars
    ]);
}
```

- [ ] **Step 2: Add `forceDelete` method for permanent deletion**

```php
public function forceDelete(Request $request, Posts $post): RedirectResponse
{
    $user = $request->user();
    abort_unless($user?->role === 'admin', 403);
    abort_unless($post->trashed(), 404);

    DB::transaction(function () use ($post, $user): void {
        // Delete related records
        $post->comments()->forceDelete();
        $post->ratings()->delete();
        $post->bookmarks()->delete();
        UserPosts::where('post_id', $post->id)->forceDelete();

        $this->audit($user, 'force_delete_post', $post, 'Post permanently deleted by admin.');

        $post->forceDelete();
    });

    return redirect()->route('dashboard')->with('success', 'Post permanently deleted.');
}
```

- [ ] **Step 3: Add route for force delete**

In `routes/web.php`:
```php
Route::delete('/posts/{post}/force-delete', [PostsController::class, 'forceDelete'])
    ->withTrashed()
    ->name('posts.force-delete');
```

---

## Task 5: Post Detail — Banned Banner Component

**Files:**
- Modify: `resources/views/layout/post-detail.blade.php`

**Goal:** Show banned banner at top of page when admin views a banned post.

- [ ] **Step 1: Add banned banner after `<x-layout.nav />`**

```blade
@if ($isBanned)
    <div class="banned-banner">
        <div class="banned-banner-inner">
            <div class="banned-banner-icon">
                <i class="fa-solid fa-ban"></i>
            </div>
            <div class="banned-banner-content">
                <div class="banned-banner-title">This post has been banned</div>
                <div class="banned-banner-meta">
                    @if ($banLog && $banLog->user)
                        Banned by <strong>{{ $banLog->user->name }}</strong>
                        on <strong>{{ $banLog->created_at->format('M d, Y \a\t h:i A') }}</strong>
                    @endif
                </div>
                @if ($banLog && $banLog->reason)
                    <div class="banned-banner-reason">Reason: {{ $banLog->reason }}</div>
                @endif
            </div>
            <div class="banned-banner-actions">
                <form method="POST" action="{{ route('posts.unban', $post->id) }}" style="display:inline;">
                    @csrf
                    <button type="submit" class="banned-btn banned-btn-revert">
                        <i class="fa-solid fa-rotate-left"></i> Revert
                    </button>
                </form>
                <form method="POST" action="{{ route('posts.force-delete', $post->id) }}" style="display:inline;"
                    x-data x-on:submit.prevent="window.sitesphereSwal.confirm({
                        title: 'Delete Permanently?',
                        text: 'This action cannot be undone. The post and all its data will be permanently removed.',
                        icon: 'warning',
                        confirmButtonColor: 'var(--ui-danger)',
                        cancelButtonColor: '#6c757d',
                        confirmButtonText: 'Yes, delete forever!'
                    }).then((result) => { if (result.isConfirmed) $el.submit(); })">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="banned-btn banned-btn-delete">
                        <i class="fa-solid fa-trash"></i> Delete Permanently
                    </button>
                </form>
                <a href="{{ route('home') }}" class="banned-btn banned-btn-home">
                    <i class="fa-solid fa-house"></i> Home
                </a>
            </div>
        </div>
    </div>
@endif
```

---

## Task 6: Profile Detail — Handle Banned Users

**Files:**
- Modify: `app/Http/Controllers/ProfileDetailController.php`
- Modify: `resources/views/layout/profile-detail.blade.php`

**Goal:** Allow admins to view banned user profiles with banner and actions.

- [ ] **Step 1: Modify `ProfileDetailController`**

```php
public function __invoke(Request $request, ?string $slug = null): View|RedirectResponse
{
    if ($slug) {
        $user = User::withTrashed()->where('slug', $slug)->firstOrFail();
    } else {
        $user = $request->user();
        return redirect()->route('profile-detail', ['slug' => $user->slug]);
    }

    // Handle banned users
    if ($user->trashed()) {
        abort_unless($request->user()?->role === 'admin', 404);

        $banLog = AuditLogs::query()
            ->with('user')
            ->where('target_type', User::class)
            ->where('target_id', $user->id)
            ->whereIn('action', ['delete_user', 'ban_user'])
            ->latest()
            ->first();

        return view('layout.profile-detail', [
            'user' => $user,
            'isBanned' => true,
            'banLog' => $banLog,
            'isOwnProfile' => false,
            // ... other vars with defaults/empty collections
        ]);
    }

    // Existing logic...
    $isOwnProfile = $request->user()?->is($user) ?? false;
    // ...

    return view('layout.profile-detail', [
        'user' => $user,
        'isBanned' => false,
        'banLog' => null,
        'isOwnProfile' => $isOwnProfile,
        // ... other existing vars
    ]);
}
```

- [ ] **Step 2: Add banned banner to `profile-detail.blade.php`**

Same pattern as post-detail — banned banner with revert/delete/home buttons.

- [ ] **Step 3: Add `forceDelete` method to `AdminUsersController`**

```php
public function forceDelete(Request $request, User $user): RedirectResponse
{
    $admin = $this->authorizeAdmin($request);
    $this->abortIfSelfAction($admin, $user);
    abort_unless($user->trashed(), 404);

    DB::transaction(function () use ($admin, $user): void {
        // Delete user's content
        $user->comments()->forceDelete();
        $user->ratings()->delete();
        $user->bookmarks()->delete();
        UserPosts::where('user_id', $user->id)->forceDelete();

        $this->audit($admin, 'force_delete_user', $user, 'User permanently deleted by admin.');

        $user->forceDelete();
    });

    return redirect()->route('admin.users')->with('success', "{$user->name} permanently deleted.");
}
```

- [ ] **Step 4: Add route**

```php
Route::delete('/menu/users/{user}/force-delete', [AdminUsersController::class, 'forceDelete'])
    ->withTrashed()
    ->name('users.force-delete');
```

---

## Task 7: Comments — Handle Banned Comments

**Files:**
- Modify: `app/Http/Controllers/CommentsController.php`

**Goal:** Add force delete for comments. Banned comments visible on post page for admins.

- [ ] **Step 1: Add `forceDelete` method**

```php
public function forceDelete(Request $request, Comments $comment): RedirectResponse
{
    $user = $request->user();
    abort_unless($user?->role === 'admin', 403);
    abort_unless($comment->trashed(), 404);

    $postId = $comment->post_id;

    $comment->reactions()->delete();
    $this->audit($user, 'force_delete_comment', $comment, 'Comment permanently deleted by admin.');
    $comment->forceDelete();

    return back()->with('success', 'Comment permanently deleted.');
}
```

- [ ] **Step 2: Add route**

```php
Route::delete('/comments/{comment}/force-delete', [CommentsController::class, 'forceDelete'])
    ->withTrashed()
    ->name('comments.force-delete');
```

- [ ] **Step 3: Show banned comments in post detail for admins**

In `post-detail.blade.php`, when rendering comments, check if comment is trashed and show banned indicator.

---

## Task 8: Custom 404 Page

**Files:**
- Create: `resources/views/errors/404.blade.php`

**Goal:** Clean 404 page with "Go to Home" button.

- [ ] **Step 1: Create 404 view**

```blade
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>404 — Not Found</title>
    @vite('resources/css/app.css')
    <style>
        .error-page {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: var(--background-color, #f8fafc);
            font-family: system-ui, -apple-system, sans-serif;
        }
        .error-card {
            text-align: center;
            padding: 48px;
            max-width: 420px;
        }
        .error-code {
            font-size: 72px;
            font-weight: 900;
            color: var(--accent-color, #6c5ce7);
            line-height: 1;
            margin-bottom: 8px;
        }
        .error-title {
            font-size: 20px;
            font-weight: 700;
            color: var(--text-color, #0f172a);
            margin-bottom: 8px;
        }
        .error-desc {
            font-size: 14px;
            color: var(--muted, #64748b);
            margin-bottom: 24px;
            line-height: 1.5;
        }
        .error-btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 10px 24px;
            background: var(--accent-color, #6c5ce7);
            color: #fff;
            border: none;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.2s ease;
            cursor: pointer;
        }
        .error-btn:hover {
            opacity: 0.9;
            transform: translateY(-1px);
        }
    </style>
</head>
<body>
    <div class="error-page">
        <div class="error-card">
            <div class="error-code">404</div>
            <div class="error-title">Page Not Found</div>
            <p class="error-desc">The page you're looking for doesn't exist or has been removed.</p>
            <a href="{{ route('home') }}" class="error-btn">
                <i class="fa-solid fa-house"></i> Go to Home
            </a>
        </div>
    </div>
</body>
</html>
```

---

## Task 9: CSS — Banned Banner Styles

**Files:**
- Modify: `resources/css/post-detail.css` (or create shared CSS)

- [ ] **Step 1: Add banned banner styles**

```css
/* ── BANNED BANNER ── */
.banned-banner {
    background: linear-gradient(135deg, #fef2f2, #fee2e2);
    border-bottom: 2px solid #ef4444;
    padding: 16px 0;
}
.banned-banner-inner {
    max-width: 1200px;
    margin: 0 auto;
    padding: 0 24px;
    display: flex;
    align-items: center;
    gap: 16px;
    flex-wrap: wrap;
}
.banned-banner-icon {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background: #ef4444;
    color: #fff;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 16px;
    flex-shrink: 0;
}
.banned-banner-content {
    flex: 1;
    min-width: 200px;
}
.banned-banner-title {
    font-size: 15px;
    font-weight: 700;
    color: #991b1b;
}
.banned-banner-meta {
    font-size: 13px;
    color: #b91c1c;
    margin-top: 2px;
}
.banned-banner-meta strong {
    color: #991b1b;
}
.banned-banner-reason {
    font-size: 12px;
    color: #dc2626;
    margin-top: 4px;
    font-style: italic;
}
.banned-banner-actions {
    display: flex;
    gap: 8px;
    flex-shrink: 0;
}
.banned-btn {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 8px 16px;
    border-radius: 8px;
    font-size: 12px;
    font-weight: 600;
    text-decoration: none;
    border: none;
    cursor: pointer;
    transition: all 0.2s ease;
}
.banned-btn-revert {
    background: #10b981;
    color: #fff;
}
.banned-btn-revert:hover {
    background: #059669;
}
.banned-btn-delete {
    background: #ef4444;
    color: #fff;
}
.banned-btn-delete:hover {
    background: #dc2626;
}
.banned-btn-home {
    background: var(--ui-surface, #fff);
    color: var(--text-color, #0f172a);
    border: 1px solid var(--ui-border, #e2e8f0);
}
.banned-btn-home:hover {
    background: var(--background-color, #f8fafc);
}
```

---

## Task 10: Routes Summary

**Files:**
- Modify: `routes/web.php`

- [ ] **Step 1: Add new routes**

```php
// Banned content - force delete routes
Route::delete('/posts/{post}/force-delete', [PostsController::class, 'forceDelete'])
    ->withTrashed()
    ->name('posts.force-delete');

Route::delete('/comments/{comment}/force-delete', [CommentsController::class, 'forceDelete'])
    ->withTrashed()
    ->name('comments.force-delete');

Route::delete('/menu/users/{user}/force-delete', [AdminUsersController::class, 'forceDelete'])
    ->withTrashed()
    ->name('users.force-delete');
```

---

## Verification Checklist

- [ ] Dashboard shows 6-7 recent activities
- [ ] `tl-icon` removed from timeline
- [ ] `tl-target` links work for posts, users, and comments
- [ ] "See more" button appears and links to activity log
- [ ] Admin can view banned post with banner
- [ ] Admin can view banned user profile with banner
- [ ] Admin can revert (unban) from banner
- [ ] Admin can permanently delete from banner
- [ ] Regular users see 404 for banned content
- [ ] 404 page has "Go to Home" button
- [ ] All existing functionality still works
