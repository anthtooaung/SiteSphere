# SiteSphere External Fixes - Suggestions Document

## Overview
16 external (user-facing) issues identified from `mobile design.md`. Each fix includes the problem, file(s) affected, and the exact code change.

---

## Fix 1: Home Filter Removal Sync

**Problem:** Removing a filter from the home page filter boxes (Selected Filters section) doesn't update the sidebar checkboxes or sync properly.

**File:** `resources/views/layout/home.blade.php`

**Root Cause:** The `toggleFilter` method uses `splice()` to remove items from the filters array. While Alpine.js 3 should handle this reactively, the sidebar checkboxes use `:checked` binding which may not re-evaluate when the array is mutated via splice.

**Suggested Fix:** Replace `splice` with array reassignment in `toggleFilter`:

```javascript
// CURRENT (line ~260):
toggleFilter(type, value) {
    if (value === 'all' || value === 'All') {
        this.filters[type] = [];
    } else {
        const index = this.filters[type].indexOf(value);
        if (index > -1) {
            this.filters[type].splice(index, 1);  // <-- may not trigger reactivity
        } else {
            this.filters[type].push(value);
        }
    }
    this.updateResults();
},

// SUGGESTED:
toggleFilter(type, value) {
    if (value === 'all' || value === 'All') {
        this.filters[type] = [];
    } else {
        const index = this.filters[type].indexOf(value);
        if (index > -1) {
            this.filters[type] = this.filters[type].filter((_, i) => i !== index);
        } else {
            this.filters[type] = [...this.filters[type], value];
        }
    }
    this.updateResults();
},
```

---

## Fix 2: Search Bar Placeholder

**Problem:** Search bar just says "Search..." — users don't know what they can search for.

**File:** `resources/views/components/search-btn.blade.php` (line 14)

**Suggested Fix:**
```html
<!-- CURRENT -->
<input type="search" name="search" id="search" placeholder="Search..." .../>

<!-- SUGGESTED -->
<input type="search" name="search" id="search" placeholder="Search by title, URL, category or tag..." .../>
```

---

## Fix 3: Profile Stats Detail

**Problem:** Profile page stats lack detail. Each section should show URL (instead of title), ratings, dates, and link to posts.

**Files:**
- `resources/views/layout/profile-detail.blade.php` (lines 272-385)
- `app/Http/Controllers/ProfileDetailController.php`

### 3a: My Reviews Section (lines 330-350)

**Current:** Shows title, comment snippet, date, rating
**Suggested:** Show URL instead of title, keep rating, link to post

```blade
<!-- CURRENT -->
<a href="{{ $review->post ? route('posts.show', $review->post->slug) . '#comment-' . $review->id : '#' }}" class="list-title">{{ $review->post?->title ?? 'Deleted Post' }}</a>
<span class="list-subtitle">{{ Str::limit($review->content, 60) }}</span>

<!-- SUGGESTED -->
<a href="{{ $review->post ? route('posts.show', $review->post->slug) . '#comment-' . $review->id : '#' }}" class="list-title">{{ $review->post ? parse_url($review->post->url, PHP_URL_HOST) : 'Deleted Post' }}</a>
<span class="list-subtitle">{{ Str::limit($review->content, 60) }}</span>
```

### 3b: My Uploads Section (lines 362-383)

**Current:** Shows title, "Contributed Resource", date
**Suggested:** Show URL, add rating received

```blade
<!-- CURRENT -->
<a href="..." class="list-title">{{ $upload->post?->title ?? 'Deleted Post' }}</a>
<span class="list-subtitle">Contributed Resource</span>

<!-- SUGGESTED -->
<a href="..." class="list-title">{{ $upload->post ? parse_url($upload->post->url, PHP_URL_HOST) : 'Deleted Post' }}</a>
<span class="list-subtitle">{{ $upload->post ? number_format($upload->post->average_rating ?? 0, 1) . ' ★ rating' : 'Contributed Resource' }}</span>
```

### 3c: Rating Received Card (line 310-318)

**Current:** Shows average rating number and "Lifetime Average" text
**Suggested:** Make it clickable to expand and show per-post received ratings

Add expansion panel for "Rating Received" similar to reviews/uploads:
```blade
<div class="stat-card" :class="expandedSection === 'received' ? 'active' : ''">
    <span class="stat-icon purple">...</span>
    <div>
        <h2>{{ number_format($averageRating, 1) }}</h2>
        <p>Rating Received</p>
        <button @click="expandedSection = expandedSection === 'received' ? null : 'received'" class="bottom-link">
            <span x-text="expandedSection === 'received' ? 'Collapse ↑' : 'View details →'"></span>
        </button>
    </div>
</div>
```

And add a new expansion panel:
```blade
<!-- Rating Received Panel -->
<div x-show="expandedSection === 'received'" class="expansion-panel">
    <div class="panel-header">
        <h3>Ratings Received</h3>
        <span class="count-pill">{{ $ratingsCount }} Items</span>
    </div>
    <div class="dense-list">
        @forelse($allRatings as $rating)
            <div class="list-row">
                <div class="list-left">
                    <div class="list-icon-bg purple-bg">
                        <svg ...star icon...></svg>
                    </div>
                    <div class="list-info">
                        <a href="{{ $rating->post ? route('posts.show', $rating->post->slug) : '#' }}" class="list-title">
                            {{ $rating->post ? parse_url($rating->post->url, PHP_URL_HOST) : 'Deleted Post' }}
                        </a>
                        <span class="list-subtitle">Rated {{ number_format($rating->rating, 1) }} ★</span>
                    </div>
                </div>
                <div class="list-right">
                    <span class="list-meta">{{ $rating->created_at->format('d M Y') }}</span>
                </div>
            </div>
        @empty
            <div class="empty-state">No ratings given yet.</div>
        @endforelse
    </div>
</div>
```

### 3d: Rate Items Card (line 286-295)

**Current:** Shows count and "Total Rated" text (not clickable)
**Suggested:** Make it clickable to show per-post ratings given

```blade
<!-- CURRENT -->
<div class="stat-card">
    ...
    <span class="bottom-link" style="cursor: default; opacity: 0.7;">Total Rated</span>
</div>

<!-- SUGGESTED -->
<div class="stat-card" :class="expandedSection === 'rated' ? 'active' : ''">
    ...
    <button @click="expandedSection = expandedSection === 'rated' ? null : 'rated'" class="bottom-link">
        <span x-text="expandedSection === 'rated' ? 'Collapse ↑' : 'View all rated →'"></span>
    </button>
</div>
```

### 3e: Controller Changes

**File:** `app/Http/Controllers/ProfileDetailController.php`

Need to pass `$allRatings` with post relationship loaded (already done), and add `average_rating` to posts:
```php
$allRatings = Ratings::query()
    ->where('user_id', $user->id)
    ->with(['post'])  // already loaded
    ->latest()
    ->get();
```

Also need to add `average_rating` to uploads query so we can show rating on uploads:
```php
$allUploads = (clone $userPostsQuery)
    ->with(['post.tags', 'post.ratings'])  // add ratings
    ->latest()
    ->get();
```

---

## Fix 4: Activity Log Detail

**Problem:** Activity logs don't show enough context — admin can't understand what each log entry means.

**Files:**
- `resources/views/partials/admin-activity-card.blade.php`
- `app/Http/Controllers/AdminActivityLogController.php`

**Suggested Fix:** Add more detail columns to the activity log display:
- Show action type in human-readable format (e.g., "Banned User" instead of "ban_user")
- Show target name/title
- Show who performed the action
- Show reason if available

Need to read the current activity card template first to determine exact changes.

---

## Fix 5: Banned Description Rollback (Admin Restore)

**Problem:** When admin bans (soft-deletes) a description, there's no way to restore it — only "Delete Permanently" exists.

**Files:**
- `resources/views/layout/post-detail.blade.php` (around line 560-580)
- `routes/web.php`
- `app/Http/Controllers/PostsController.php`

**Suggested Fix:**

### 5a: Add restore route in `routes/web.php`
```php
Route::patch('/audits/{userPost}/restore', [PostsController::class, 'restoreAudit'])
    ->withTrashed()
    ->name('audits.restore');
```

### 5b: Add restore method in `PostsController.php`
```php
public function restoreAudit(UserPosts $userPost): RedirectResponse
{
    $userPost->restore();
    return back()->with('success', 'Description restored.');
}
```

### 5c: Add restore button in `post-detail.blade.php`
In the admin section for trashed descriptions (around line 560), add a "Restore" button before "Delete Permanently":

```blade
@if($userPost->trashed())
    {{-- Restore button --}}
    <form method="POST" action="{{ route('audits.restore', $userPost->id) }}" class="mt-1 border-t pt-1 ...">
        @csrf
        @method('PATCH')
        <button type="submit" class="flex min-h-9 w-full items-center gap-2 ... text-green-600 ...">
            <x-fas-rotate-left class="size-3" />
            <span>Restore</span>
        </button>
    </form>
    {{-- Delete Permanently (existing) --}}
    ...
@endif
```

---

## Fix 6: Preview Card Menu Disable (View-Only Mode)

**Problem:** In the upload post preview, the card's menu button (three-dot) is clickable and opens actions. It should be view-only.

**Files:**
- `resources/views/components/layout/post-card.blade.php`
- Wherever the post card is rendered in preview mode (upload post page)

**Suggested Fix:** Add a `previewMode` prop to the post card component:

### 6a: Add prop
```blade
@props([
    ...existing props...
    'previewMode' => false,
])
```

### 6b: Conditionally disable actions button
```blade
@if(!$previewMode)
    <div class="relative shrink-0">
        <button type="button" ... x-on:click.stop="actionsOpen = ! actionsOpen">
            <x-fas-ellipsis class="size-4" />
        </button>
        ...dropdown menu...
    </div>
@endif
```

### 6c: Conditionally disable footer links
```blade
@if(!$previewMode)
    <footer ...>
        ...existing footer with comments and review buttons...
    </footer>
@else
    <footer ...>
        <span>Comments: {{ $commentsCount }}</span>
        <span>Rating: {{ number_format($averageRating, 1) }} ★</span>
    </footer>
@endif
```

---

## Fix 7: Upload Post URL Trimming

**Problem:** URLs displayed on post cards show the full URL. Should show just the domain name (e.g., `example.com`).

**Files:**
- `resources/views/components/layout/post-card.blade.php` (line 241)

**Suggested Fix:** Use `parse_url()` to extract domain:

```blade
<!-- CURRENT -->
<span ... data-post-card-url>{{ $url }}</span>

<!-- SUGGESTED -->
<span ... data-post-card-url>{{ parse_url($url, PHP_URL_HOST) ?? $url }}</span>
```

Also apply the same fix in:
- `resources/views/layout/post-detail.blade.php` (already uses `$host = parse_url($post->url, PHP_URL_HOST)`)
- `resources/views/partials/home-posts.blade.php` (if it renders URLs)

---

## Fix 8: WebSocket Real-time Notifications

**Problem:** Real-time notifications are not arriving. Echo is configured but notifications aren't being broadcast.

**Files:**
- `app/Models/Notificatioins.php` (add ShouldBroadcast)
- `app/Events/` (create notification event)
- `resources/js/echo.js` (listen for events)
- `routes/channels.php` (define private channel)

**Suggested Fix:**

### 8a: Create NotificationEvent
```php
// app/Events/NotificationCreated.php
namespace App\Events;

use App\Models\Notificatioins;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class NotificationCreated implements ShouldBroadcast
{
    public function __construct(public Notificatioins $notification) {}

    public function broadcastOn(): array
    {
        return [new PrivateChannel('notifications.' . $this->notification->to_user_id)];
    }

    public function broadcastAs(): string
    {
        return 'notification.created';
    }
}
```

### 8b: Dispatch event when creating notifications
In `ReportsController.php` and anywhere notifications are created:
```php
use App\Events\NotificationCreated;

// After creating notification:
event(new NotificationCreated($notification));
```

### 8c: Define channel in `routes/channels.php`
```php
Broadcast::channel('notifications.{userId}', function ($user, $userId) {
    return (int) $user->id === (int) $userId;
});
```

### 8d: Listen in frontend `resources/js/echo.js`
```javascript
// Listen for real-time notifications
const userId = document.querySelector('meta[name="user-id"]')?.content;
if (userId) {
    window.Echo.private(`notifications.${userId}`)
        .listen('.notification.created', (e) => {
            // Update notification badge count
            const badge = document.querySelector('.noti-badge, .mobile-badge');
            if (badge) {
                const current = parseInt(badge.textContent) || 0;
                badge.textContent = current + 1;
                badge.style.display = 'flex';
            }
            // Show toast notification
            if (window.sitesphereSwal) {
                window.sitesphereSwal.fire({
                    toast: true,
                    position: 'top-end',
                    showConfirmButton: false,
                    timer: 3000,
                    timerProgressBar: true,
                    icon: 'info',
                    title: e.notification.message,
                });
            }
        });
}
```

### 8e: Add user ID meta tag in `dashboard.blade.php`
```html
@auth
    <meta name="user-id" content="{{ auth()->id() }}">
@endauth
```

---

## Fix 9: Reports Page - Banned User Link

**Problem:** In the reports page users tab, banned users' names are not clickable links to their profile. Should work like the users page.

**File:** `resources/views/layout/menu/reports.blade.php` (users-view section, around line 720-740)

**Current:**
```blade
@if ($targetUser?->slug)
    <a href="{{ route('reports.open', $report) }}" class="reports-reporter hover:underline" style="color: var(--text-color);">
        {{ $targetUser->name }}
    </a>
@else
    <span class="reports-reporter">Unknown User</span>
@endif
```

**Suggested:** Link to profile page (with trashed users accessible to admins):
```blade
@if ($targetUser)
    <a href="{{ route('profile-detail', ['slug' => $targetUser->slug]) }}" class="reports-reporter hover:underline" style="color: var(--text-color);">
        {{ $targetUser->name }}
        @if ($targetUser->trashed())
            <span class="reports-banned-badge">Banned</span>
        @endif
    </a>
@else
    <span class="reports-reporter">Unknown User</span>
@endif
```

---

## Fix 10: Banned User Tab Name in Post Detail/Card

**Problem:** When a user who contributed a description is banned/trashed, their name still shows in the tab. Should show "Banned" instead.

**Files:**
- `resources/views/layout/post-detail.blade.php` (around line 330-350, depo-tab)
- `resources/views/components/layout/post-card.blade.php` (around line 286-300, profile tabs)

### 10a: Post Detail (depo tabs)
```blade
@php
    $isProfileVisible = ! $userPost->user_hidden;
    $isUserBanned = $userPost->user->trashed();
    $displayName = $isUserBanned ? 'Banned' : ($isProfileVisible ? $userPost->user->name : 'Anonymous');
    // ...
@endphp
```

### 10b: Post Card (profile tabs)
The post card uses Alpine.js template for profile tabs. Need to pass `isBanned` flag in the profiles array from the controller/component, then:

```blade
<span x-text="profile.isBanned ? 'Banned' : profile.username"></span>
```

**Controller change:** In `post-card.php` component or `HomeController.php`, add `isBanned` to the profiles array:
```php
'isBanned' => $userPost->user->trashed(),
```

---

## Fix 11: Email Privacy Masking

**Problem:** Full email addresses are shown on profile detail and hover profile cards. Should be masked.

**Files:**
- `resources/views/layout/profile-detail.blade.php` (lines 215, 234)
- `resources/views/components/layout/hover-profile-card.blade.php`

**Suggested Fix:** Create a helper function or use inline PHP:

### Helper function (in `app/Helpers/` or inline):
```php
function maskEmail(string $email): string {
    $parts = explode('@', $email);
    if (count($parts) !== 2) return $email;
    
    $local = $parts[0];
    $domain = $parts[1];
    
    if (strlen($local) <= 4) {
        return str_repeat('*', strlen($local) - 1) . substr($local, -1) . '@' . $domain;
    }
    
    return substr($local, 0, 2) . str_repeat('*', strlen($local) - 4) . substr($local, -2) . '@' . $domain;
}
```

### Usage in templates:
```blade
<!-- CURRENT -->
<h4>{{ $user->email }}</h4>

<!-- SUGGESTED -->
<h4>{{ maskEmail($user->email) }}</h4>
```

Examples:
- `anthony@gmail.com` → `an***ny@gmail.com`
- `ab@gmail.com` → `*b@gmail.com`
- `test12345@example.com` → `te***45@example.com`

---

## Fix 12: Notification Mark-All-Read Cache Clear

**Problem:** After clicking "Mark all read", the notification badge still shows unread count for 30 seconds (cache TTL).

**File:** `app/Http/Controllers/NotificationOpenController.php` (line 76-86)

**Current:**
```php
public function markAllAsRead(Request $request): RedirectResponse
{
    $user = $request->user();
    Notificatioins::query()
        ->where('to_user_id', $user->id)
        ->where('is_read', false)
        ->update(['is_read' => true]);
    return back()->with('success', 'All notifications marked as read.');
}
```

**Suggested:** Add cache clearing:
```php
use Illuminate\Support\Facades\Cache;

public function markAllAsRead(Request $request): RedirectResponse
{
    $user = $request->user();
    Notificatioins::query()
        ->where('to_user_id', $user->id)
        ->where('is_read', false)
        ->update(['is_read' => true]);
    
    Cache::forget('notifications.unread.' . $user->id);
    
    return back()->with('success', 'All notifications marked as read.');
}
```

---

## Fix 13: Report Button Hide After Reporting

**Problem:** After reporting a post/user, the report button still shows. Backend prevents duplicates but UI doesn't reflect it.

**Files:**
- `app/View/Components/layout/post-card.php` (pass hasReported)
- `resources/views/components/layout/post-card.blade.php` (hide button)
- `app/Http/Controllers/PostsController.php` (pass hasReported for post detail)
- `resources/views/layout/post-detail.blade.php` (hide button)

**Suggested Fix:**

### 13a: In post-card component class
```php
// Check if current user has reported this post
$hasReported = false;
if (Auth::check() && $postId) {
    $hasReported = \App\Models\Reports::query()
        ->where('user_id', Auth::id())
        ->where('target_name', 'posts')
        ->where('target_id', $postId)
        ->exists();
}
// Pass to view
```

### 13b: In post-card blade
```blade
@if (Auth::user()?->role !== 'admin' && !collect($profiles)->contains('user_id', Auth::id()) && !$hasReported)
    <button type="button" ... x-on:click="openReportModal()">
        <x-far-flag class="size-3" />
        <span>Report</span>
    </button>
@endif
```

### 13c: For comments and user_posts reports
Similar pattern — check if user has already reported and hide the button.

---

## Fix 14: Post Card Footer Spacing

**Problem:** Footer section of post card has too much bottom padding.

**File:** `resources/views/components/layout/post-card.blade.php` (line 327)

**Suggested Fix:**
```css
/* CURRENT */
class="... px-4 py-3 sm:px-5 ..."

/* SUGGESTED */
class="... px-4 py-2 sm:px-5 ..."
```

---

## Fix 15: Profile Stat Icons Too Big

**Problem:** Profile page stat icons are too large (32x32).

**File:** `resources/views/layout/profile-detail.blade.php` (lines 275, 288, 299, 312)

**Suggested Fix:** Change SVG dimensions from 32x32 to 24x24:

```html
<!-- CURRENT -->
<svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" ...>

<!-- SUGGESTED -->
<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" ...>
```

Apply to all 4 stat icon SVGs (reviews, ratings, uploads, average rating).

---

## Fix 16: Error Page Button Icon Size

**Problem:** Error page buttons have oversized house icon.

**Files:**
- `resources/views/errors/403.blade.php` (line 69)
- `resources/views/errors/404.blade.php` (line 69)
- `resources/views/errors/500.blade.php` (line 69)

**Suggested Fix:**
```html
<!-- CURRENT -->
<x-fas-house /> Go to Home

<!-- SUGGESTED -->
<x-fas-house class="size-4" /> Go to Home
```

---

## Implementation Order

| Phase | Fixes | Complexity |
|-------|-------|-----------|
| 1 | 14, 15, 16, 2 | Quick (CSS/template) |
| 2 | 12, 13, 1 | Medium (logic) |
| 3 | 3, 11 | Medium (profile overhaul) |
| 4 | 5, 6, 10 | Medium (post detail) |
| 5 | 4, 9 | Medium (admin) |
| 6 | 7, 8 | Complex (WebSocket + URL) |

---

## Notes
- Fix 8 (WebSocket) requires Pusher/Reverb credentials to be configured in `.env`
- Fix 3 (Profile stats) is the most extensive change — touches both controller and view
- Fix 5 (Restore) needs a new route and controller method
- Fix 11 (Email masking) should create a helper function for reuse
