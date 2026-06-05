@extends('dashboard')

@section('title')
    Profile Detail
@endsection

@push('styles')
    @vite('resources/css/profile-detail.css')
@endpush

@section('content')
    @php
        $dashboardMenuLocation = in_array($menuBarLocation ?? 'left', ['top', 'right', 'bottom', 'left'], true)
            ? $menuBarLocation
            : 'left';
        
        $user = auth()->user();
        $reviewsCount = \App\Models\UserPosts::where('user_id', $user->id)->count();
        $ratingsCount = \App\Models\Ratings::where('user_id', $user->id)->count();
        
        // Average rating received on posts reviewed by this user
        $postIds = \App\Models\UserPosts::where('user_id', $user->id)->pluck('post_id');
        $averageRating = \App\Models\Ratings::whereIn('post_id', $postIds)->avg('rating') ?: 0;
        
        // User's recent reviews/posts
        $recentReviews = \App\Models\UserPosts::where('user_id', $user->id)
            ->with(['post.tags'])
            ->latest()
            ->take(4)
            ->get();
    @endphp

    <x-layout.nav />

    <div class="dashboard-page dashboard-page--{{ $dashboardMenuLocation }} profile-detail-page">
        <x-layout.menu :menu-bar-location="$dashboardMenuLocation" />

        <main class="dashboard-content profile-detail-content">
            <!-- Background Blur -->
            <div class="bg-blur blur1"></div>
            <div class="bg-blur blur2"></div>

            <div class="profile-container">

                <!-- Main Profile Card -->
                <div class="profile-card">
                    <a href="{{ route('edit-profile') }}" class="edit-btn" style="text-decoration: none;">
                        <i class="fa-regular fa-pen-to-square"></i>
                        Edit
                    </a>

                    <div class="profile-content">
                        <!-- Left -->
                        <div class="left-section">
                            @if($user->user_image)
                                <img class="profile-img" src="{{ $user->getAvatarUrl() }}" alt="{{ $user->name }}">
                            @else
                                <div class="profile-img-placeholder">
                                    <span>{{ Str::of($user->name)->substr(0, 1)->upper() }}</span>
                                </div>
                            @endif

                            <div class="user-info">
                                <div class="name-row">
                                    <h2>{{ $user->name }}</h2>
                                    @if($user->is_verified)
                                        <i class="fa-solid fa-circle-check verified" title="Verified Account"></i>
                                    @endif
                                </div>

                                <p>
                                    {{ $user->user_bio ?? 'Passionate reviewer and community member of SiteSphere.' }}
                                </p>

                                <div class="social-icons">
                                    <a href="mailto:{{ $user->email }}" aria-label="Email">
                                        <i class="fa-regular fa-envelope"></i>
                                    </a>
                                    @if($user->user_phone)
                                        <a href="tel:{{ $user->user_phone }}" aria-label="Phone">
                                            <i class="fa-solid fa-phone"></i>
                                        </a>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <!-- Right -->
                        <div class="right-section">
                            <div class="info-grid">
                                <div class="info-item">
                                    <i class="fa-regular fa-envelope"></i>
                                    <div>
                                        <span>Email</span>
                                        <h4>{{ $user->email }}</h4>
                                    </div>
                                </div>

                                <div class="info-item">
                                    <i class="fa-solid fa-phone"></i>
                                    <div>
                                        <span>Phone</span>
                                        <h4>{{ $user->user_phone ?? 'Not specified' }}</h4>
                                    </div>
                                </div>

                                <div class="info-item">
                                    <i class="fa-regular fa-calendar"></i>
                                    <div>
                                        <span>Date of Birth</span>
                                        <h4>{{ $user->user_dob ? \Carbon\Carbon::parse($user->user_dob)->format('d F Y') : 'Not specified' }}</h4>
                                    </div>
                                </div>

                                <div class="info-item">
                                    <i class="fa-regular fa-clock"></i>
                                    <div>
                                        <span>Joined</span>
                                        <h4>{{ $user->created_at ? $user->created_at->format('d F Y') : 'Not specified' }}</h4>
                                    </div>
                                </div>
                            </div>

                            <div class="login-status">
                                Last Login: Today, {{ now()->format('h:i A') }}
                                <span class="online-dot"></span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Stats Grid -->
                <div class="stats-grid">
                    <div class="stat-card">
                        <i class="fa-solid fa-comment-dots stat-icon blue"></i>
                        <div>
                            <h2>{{ $reviewsCount }}</h2>
                            <p>My Reviews</p>
                            <a href="{{ route('home') }}" class="bottom-link">View all reviews &rarr;</a>
                        </div>
                    </div>

                    <div class="stat-card">
                        <i class="fa-solid fa-star stat-icon gold"></i>
                        <div>
                            <h2>{{ $ratingsCount }}</h2>
                            <p>Rate Items</p>
                            <a href="{{ route('home') }}" class="bottom-link">Give rating &rarr;</a>
                        </div>
                    </div>

                    <div class="stat-card">
                        <i class="fa-solid fa-upload stat-icon green"></i>
                        <div>
                            <h2>{{ $reviewsCount }}</h2>
                            <p>My Uploads</p>
                            <a href="{{ route('home') }}" class="bottom-link">View uploads &rarr;</a>
                        </div>
                    </div>

                    <div class="stat-card">
                        <i class="fa-solid fa-ranking-star stat-icon purple"></i>
                        <div>
                            <h2>{{ number_format($averageRating, 1) }}</h2>
                            <p>Rating Received</p>
                            <a href="{{ route('home') }}" class="bottom-link">View all ratings &rarr;</a>
                        </div>
                    </div>
                </div>

                <!-- Reviews Box -->
                <div class="review-box">
                    <div class="review-top">
                        <h2>Recent Reviews</h2>
                    </div>

                    <div class="review-grid">
                        @forelse($recentReviews as $userPost)
                            <div class="review-card">
                                <div class="review-card-top">
                                    <a href="{{ $userPost->post->url }}" target="_blank">
                                        {{ $userPost->post->title }}
                                    </a>
                                    @php
                                        $postRating = \App\Models\Ratings::where('post_id', $userPost->post_id)
                                            ->where('user_id', $user->id)
                                            ->first()?->rating;
                                    @endphp
                                    <span>★ {{ $postRating ? number_format($postRating, 1) : 'N/A' }}</span>
                                </div>
                                <p>{{ Str::limit($userPost->description, 120) }}</p>
                                <div class="review-meta">
                                    @php
                                        $dotClass = match(strtolower($userPost->post->tags->first()?->name ?? '')) {
                                            'html' => 'html',
                                            'blade' => 'blade',
                                            default => 'java'
                                        };
                                    @endphp
                                    <span class="language-dot {{ $dotClass }}"></span>
                                    <span>{{ $userPost->post->tags->first()?->name ?? 'Web' }}</span>
                                </div>
                            </div>
                        @empty
                            <div class="review-card-empty">
                                <p>No posts reviewed yet.</p>
                            </div>
                        @endforelse
                    </div>

                    <a href="{{ route('home') }}" class="bottom-link">View all posts &rarr;</a>
                </div>

            </div>
        </main>
    </div>
@endsection

@push('scripts')
    @vite('resources/js/profile-detail.js')
@endpush
