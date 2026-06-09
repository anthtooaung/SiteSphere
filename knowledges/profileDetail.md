
## Limit token remain Rule
### 20% remaining token
- If token reach 20% then stop the current process and fill in this **finishedInUsers.md** (what finished, what remain)

# login account
- Email : **thegrowingtreeclan@gmail.com** | password : **123!@#123**

## Profile Detail Backend Logic Updates
### Required Changes
To ensure we only show and count `UserPosts` where `user_hidden` is `true` for the profile owner themselves, we need to conditionally apply a filter in `resources/views/layout/profile-detail.blade.php`. 
1. Determine if the authenticated user is viewing their own profile (`$isOwnProfile`).
2. Create a base query for `UserPosts` that filters out `user_hidden = true` if `$isOwnProfile` is false.
3. Clone and use this base query for `$reviewsCount`, `$postIds` (used for `$averageRating`), and `$recentReviews`.

### Required Function Code
```php
@php
    $dashboardMenuLocation = in_array($menuBarLocation ?? 'left', ['top', 'right', 'bottom', 'left'], true)
        ? $menuBarLocation
        : 'left';
    
    $user = $user ?? auth()->user();
    $isOwnProfile = auth()->check() && $user->id === auth()->id();

    // Base query for UserPosts, filter out hidden posts if not the owner
    $userPostsQuery = \App\Models\UserPosts::where('user_id', $user->id);
    if (!$isOwnProfile) {
        $userPostsQuery->where('user_hidden', false);
    }

    $reviewsCount = (clone $userPostsQuery)->count();
    $ratingsCount = \App\Models\Ratings::where('user_id', $user->id)->count();
    
    // Average rating received on posts reviewed by this user
    $postIds = (clone $userPostsQuery)->pluck('post_id');
    $averageRating = \App\Models\Ratings::whereIn('post_id', $postIds)->avg('rating') ?: 0;
    
    // User's recent reviews/posts
    $recentReviews = (clone $userPostsQuery)
        ->with(['post.tags'])
        ->latest()
        ->take(4)
        ->get();
@endphp
```
