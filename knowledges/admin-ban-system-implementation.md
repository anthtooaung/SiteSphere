# Admin Ban System Implementation Plan

**Date**: June 23, 2026
**Method**: Brainstorm Method
**Status**: Planning

---

## Overview

This document outlines the implementation plan for improving the admin ban system in SiteSphere. The system handles banning of posts, comments, descriptions (UserPosts), and users through a two-stage process: soft delete followed by optional permanent delete.

---

## Current State Analysis

### What Exists

| Feature | Posts | Comments | UserPosts (Descriptions) | Users |
|---------|-------|----------|--------------------------|-------|
| Soft Delete (Ban) | Yes | Yes | Yes | Yes |
| Permanent Delete | Yes | Yes | Yes | Yes |
| Restore/Revert | Yes | Yes | Yes | Yes |
| Admin sees banned content | Yes | Yes | Yes | Yes |
| Normal users blocked | Yes (404) | Hidden | Hidden | Yes (404) |
| Ban reason stored | Yes (audit log) | Yes (audit log) | Yes (audit log) | Yes (audit log) |

### How Ban System Works

**First Ban Click** → Soft Delete
- Sets `deleted_at` timestamp
- Creates audit log entry with reason
- Hides content from normal users
- Admin can still view and manage

**Permanent Delete** → Force Delete
- Only available after soft delete
- Removes all related database records
- Cascades to related data (comments, ratings, bookmarks, etc.)

---

## Identified Gaps

### Gap 1: Profile "My Uploads" Section

**Current Behavior**: Banned UserPosts disappear completely from user's upload list

**Required Behavior**: Show banned posts with "Banned" label

**Implementation**:
```php
// ProfileDetailController.php
// Change from:
$userPosts = $user->userPosts()->with('post')->latest()->get();

// To:
$userPosts = $user->userPosts()->with('post')->withTrashed()->latest()->get();
```

**View Update** (`profile-detail.blade.php`):
```blade
@foreach($userPosts as $userPost)
<div class="post-card">
    <!-- Post content -->

    @if($userPost->trashed())
    <div class="banned-badge">
        <span class="text-red-600 font-bold">
            <i class="fas fa-ban mr-1"></i> BANNED
        </span>
        @if($userPost->getBanReason())
        <p class="text-sm text-red-500">Reason: {{ $userPost->getBanReason() }}</p>
        @endif
    </div>
    @endif
</div>
@endforeach
```

---

### Gap 2: Forced Entry via Link

**Current Behavior**: Non-admins receive 404 error when accessing banned content

**Required Behavior**: Show ban information page with full details and back button

**New View**: `resources/views/banned-content.blade.php`

```blade
@extends('layouts.app')

@section('content')
<div class="min-h-screen flex items-center justify-center bg-gray-50">
    <div class="max-w-md w-full bg-white rounded-lg shadow-lg p-8 text-center">
        <div class="text-6xl mb-4 text-red-500">
            <i class="fas fa-ban"></i>
        </div>

        <h1 class="text-2xl font-bold text-gray-900 mb-2">
            This {{ $contentType }} Has Been Banned
        </h1>

        <div class="bg-red-50 rounded-lg p-4 mb-6 text-left">
            <div class="space-y-2">
                <p class="text-sm">
                    <span class="font-semibold text-gray-700">Banned by:</span>
                    <span class="text-gray-600">{{ $bannedBy }}</span>
                </p>
                <p class="text-sm">
                    <span class="font-semibold text-gray-700">Banned on:</span>
                    <span class="text-gray-600">{{ $bannedAt }}</span>
                </p>
                <p class="text-sm">
                    <span class="font-semibold text-gray-700">Reason:</span>
                    <span class="text-red-600">{{ $reason }}</span>
                </p>
            </div>
        </div>

        <a href="{{ $backUrl }}"
           class="inline-flex items-center px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700 transition">
            <i class="fas fa-arrow-left mr-2"></i> Go Back
        </a>
    </div>
</div>
@endsection
```

**Controller Changes**:

```php
// PostsController@show
public function show($slug)
{
    $post = Posts::where('slug', $slug)->firstOrFail();

    if ($post->trashed()) {
        if (auth()->user()?->role !== 'admin') {
            return $this->showBannedPage($post, 'post');
        }
        // Admin continues to view with red border
    }

    // ... rest of logic
}

private function showBannedPage($model, $contentType)
{
    $banLog = AuditLogs::where('action', 'like', '%ban%')
        ->where('target_type', $contentType)
        ->where('target_id', $model->id)
        ->latest()
        ->first();

    return view('banned-content', [
        'contentType' => $contentType,
        'bannedBy' => $banLog?->user?->name ?? 'Admin',
        'bannedAt' => $banLog?->created_at?->format('F j, Y') ?? 'Unknown',
        'reason' => $banLog?->reason ?? 'No reason provided',
        'backUrl' => url()->previous() ?? route('home'),
    ]);
}
```

---

### Gap 3: Admin View - Red Border + Actions

**Post Detail Page** (Admin viewing banned post):

```
+---------------------------------------+  <- Light red outer border (border-2 border-red-200)
| +-----------------------------------+ |
| | [!] BANNED CONTENT                | |  <- Dark red inner banner (bg-red-100)
| | Banned by: Admin | Date | Reason  | |
| | [Revert]  [Delete Permanently]    | |
| +-----------------------------------+ |
|                                       |
| Post content visible here...          |
|                                       |
+---------------------------------------+
```

**View Update** (`post-detail.blade.php`):

```blade
@if($post->trashed() && auth()->user()?->role === 'admin')
<div class="banned-post-container border-2 border-red-200 rounded-lg">
    <!-- Ban Banner -->
    <div class="bg-red-100 border-b border-red-200 p-4 rounded-t-lg">
        <div class="flex items-center justify-between">
            <div>
                <h3 class="text-red-800 font-bold">
                    <i class="fas fa-ban mr-1"></i> BANNED CONTENT
                </h3>
                <p class="text-sm text-red-600">
                    Banned by: {{ $banLog?->user?->name ?? 'Admin' }} |
                    {{ $banLog?->created_at?->format('M j, Y') }} |
                    Reason: {{ $banLog?->reason ?? 'No reason provided' }}
                </p>
            </div>
            <div class="flex space-x-2">
                <form action="{{ route('posts.unban', $post) }}" method="POST">
                    @csrf
                    <button type="submit"
                            class="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700">
                        <i class="fas fa-undo mr-1"></i> Revert
                    </button>
                </form>
                <form action="{{ route('posts.force-delete', $post) }}" method="POST"
                      onsubmit="return confirm('Permanently delete this post? This cannot be undone.')">
                    @csrf
                    @method('DELETE')
                    <button type="submit"
                            class="px-4 py-2 bg-red-600 text-white rounded hover:bg-red-700">
                        <i class="fas fa-trash-alt mr-1"></i> Delete Permanently
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- Normal Post Content -->
    <div class="p-6">
        {{-- existing post detail content --}}
    </div>
</div>
@else
    {{-- normal post detail content --}}
@endif
```

**Description/UserPost Section** (Admin viewing banned description):

```blade
@foreach($post->userPosts as $userPost)
<div class="description-card {{ $userPost->trashed() ? 'border-2 border-red-200 bg-red-50' : '' }}">
    <div class="p-4">
        <p>{{ $userPost->description }}</p>

        @if($userPost->trashed())
        <div class="mt-3 pt-3 border-t border-red-200">
            <p class="text-sm text-red-600 mb-2">
                <i class="fas fa-ban mr-1"></i> BANNED - {{ $userPost->getBanReason() ?? 'No reason' }}
            </p>
            <div class="flex space-x-2">
                <form action="{{ route('audits.unban', $userPost) }}" method="POST">
                    @csrf
                    <button class="text-sm px-3 py-1 bg-green-600 text-white rounded">
                        <i class="fas fa-undo mr-1"></i> Revert
                    </button>
                </form>
                <form action="{{ route('audits.force-delete', $userPost) }}" method="POST">
                    @csrf
                    @method('DELETE')
                    <button class="text-sm px-3 py-1 bg-red-600 text-white rounded">
                        <i class="fas fa-trash-alt mr-1"></i> Delete
                    </button>
                </form>
            </div>
        </div>
        @endif
    </div>
</div>
@endforeach
```

---

### Gap 4: Bug Fix - Posts Model Relationship

**File**: `app/Models/Posts.php`

**Current (broken)**:
```php
public function reports()
{
    return $this->hasMany(Reports::class, 'target_id')
                ->where('target_name', 'post');
}
```

**Fixed**:
```php
public function reports()
{
    return $this->hasMany(Reports::class, 'target_id')
                ->where('target_name', 'posts');
}
```

---

## Database Flow

### Soft Delete Flow (First Ban)

```
Admin Clicks Ban
       |
       v
+------------------+
| Validate Request |
| (check admin)    |
+--------+---------+
         |
         v
+------------------+
| Soft Delete      |
| Set deleted_at   |
+--------+---------+
         |
         v
+------------------+
| Create Audit Log |
| (action, reason) |
+--------+---------+
         |
         v
+------------------+
| Hide from Users  |
| (user_hidden=true|
|  for UserPosts)  |
+------------------+
```

### Permanent Delete Flow

```
Admin Clicks Delete Permanently
       |
       v
+------------------+
| Verify trashed() |
| (must be soft    |
|  deleted first)  |
+--------+---------+
         |
         v
+------------------+
| Delete Related   |
| - Comments       |
| - Ratings        |
| - Bookmarks      |
| - UserPosts      |
| - Reports?       |
+--------+---------+
         |
         v
+------------------+
| Force Delete     |
| Main Record      |
+--------+---------+
         |
         v
+------------------+
| Create Audit Log |
| (force_delete_*) |
+------------------+
```

---

## Files to Modify

| File | Type | Changes |
|------|------|---------|
| `app/Http/Controllers/ProfileDetailController.php` | Controller | Include trashed UserPosts for own profile |
| `resources/views/layout/profile-detail.blade.php` | View | Show banned badge on banned UserPosts |
| `app/Http/Controllers/PostsController.php` | Controller | Return ban info page instead of 404 |
| `resources/views/post-detail.blade.php` | View | Red border + action buttons for admin |
| `app/Models/Posts.php` | Model | Fix `reports()` relationship |
| `resources/views/banned-content.blade.php` | View | **NEW** - Shared ban detail view |

---

## Implementation Order

1. **Phase 1**: Fix bug in Posts model (`reports()` relationship)
2. **Phase 2**: Create `banned-content.blade.php` shared view
3. **Phase 3**: Update `PostsController@show` to show ban page instead of 404
4. **Phase 4**: Update `ProfileDetailController` to include trashed UserPosts
5. **Phase 5**: Update `profile-detail.blade.php` with banned badge
6. **Phase 6**: Update `post-detail.blade.php` with red border and action buttons
7. **Phase 7**: Test all scenarios

---

## Testing Scenarios

### Test 1: Soft Delete Post
- Admin bans a post
- Post gets `deleted_at` timestamp
- Audit log created with reason
- Normal users see banned page when accessing URL
- Admin sees post with red border

### Test 2: Revert Post
- Admin reverts banned post
- `deleted_at` cleared
- Post visible to all users again
- Audit log created for unban

### Test 3: Permanent Delete Post
- Admin permanently deletes banned post
- Post and all related data removed
- 404 for all users (including admin)
- Audit log created for force delete

### Test 4: Profile "My Uploads"
- User's post gets banned
- User sees their post with "Banned" label in profile
- Reason displayed if available

### Test 5: Description Ban
- Admin bans a description (UserPost)
- Description shows red border to admin
- Revert/Delete buttons visible
- Normal users don't see description

---

## FontAwesome Icons Used

| Icon | Class | Usage |
|------|-------|-------|
| Ban | `fas fa-ban` | Banned content indicator |
| Undo | `fas fa-undo` | Revert button |
| Trash | `fas fa-trash-alt` | Delete permanently button |
| Arrow Left | `fas fa-arrow-left` | Go back button |

---

## Notes

- All ban operations require admin role verification
- Audit logs are immutable - keep them even after permanent delete
- Consider adding "Banned by" and "Banned at" columns directly to tables for faster queries (optional optimization)
- The `getBanReason()` method on models already exists - reuse it
- Use FontAwesome icons instead of text symbols for consistency

---

**End of Implementation Plan**
