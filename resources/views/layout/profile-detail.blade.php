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
    @endphp

    <x-layout.nav />

    <div @class([
        'dashboard-page',
        'dashboard-page--'.$dashboardMenuLocation,
        'dashboard-page--no-menu' => ! $isOwnProfile,
        'profile-detail-page',
    ])>
        @if ($isOwnProfile)
            <x-layout.menu :menu-bar-location="$dashboardMenuLocation" />
        @endif

        <main class="dashboard-content profile-detail-content" x-data="{ expandedSection: null }">
            <!-- Background Blur -->
            <div class="bg-blur blur1"></div>
            <div class="bg-blur blur2"></div>

            <div class="profile-container">

                <!-- Main Profile Card -->
                <div class="profile-card">
                    @if ($user->id === auth()->id())
                        <a href="{{ route('edit-profile') }}" class="edit-btn" style="text-decoration: none;">
                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-pencil"><path d="M17 3a2.85 2.83 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5Z"/><path d="m15 5 4 4"/></svg>
                            Edit
                        </a>
                    @endif

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
                                        <span class="verified" title="Verified Account">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-badge-check"><path d="M3.85 8.62a4 4 0 0 1 4.78-4.77 4 4 0 0 1 6.74 0 4 4 0 0 1 4.78 4.78 4 4 0 0 1 0 6.74 4 4 0 0 1-4.77 4.78 4 4 0 0 1-6.75 0 4 4 0 0 1-4.78-4.77 4 4 0 0 1 0-6.76Z"/><path d="m9 12 2 2 4-4"/></svg>
                                        </span>
                                    @endif
                                </div>

                                <p>
                                    {{ $user->user_bio ?? 'Passionate reviewer and community member of SiteSphere.' }}
                                </p>

                                <div class="social-icons">
                                    <a href="mailto:{{ $user->email }}" aria-label="Email">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-mail"><rect width="20" height="16" x="2" y="4" rx="2"/><path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"/></svg>
                                    </a>
                                    @if($user->user_phone)
                                        <a href="tel:{{ $user->user_phone }}" aria-label="Phone">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-phone"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
                                        </a>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <!-- Right -->
                        <div class="right-section">
                            <div class="info-grid">
                                <div class="info-item">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-mail"><rect width="20" height="16" x="2" y="4" rx="2"/><path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"/></svg>
                                    <div>
                                        <span>Email</span>
                                        <h4>{{ $user->email }}</h4>
                                    </div>
                                </div>

                                <div class="info-item">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-phone"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
                                    <div>
                                        <span>Phone</span>
                                        <h4>{{ $user->user_phone ? '+95'.$user->user_phone : 'Not specified' }}</h4>
                                    </div>
                                </div>

                                <div class="info-item">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-calendar"><path d="M8 2v4"/><path d="M16 2v4"/><rect width="18" height="18" x="3" y="4" rx="2"/><path d="M3 10h18"/></svg>
                                    <div>
                                        <span>Date of Birth</span>
                                        <h4>{{ $user->user_dob ? \Carbon\Carbon::parse($user->user_dob)->format('d F Y') : 'Not specified' }}</h4>
                                    </div>
                                </div>

                                <div class="info-item">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-clock"><circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/></svg>
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
                    <div class="stat-card" :class="expandedSection === 'reviews' ? 'active' : ''">
                        <span class="stat-icon blue">
                            <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-message-square-text"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/><path d="M13 8H7"/><path d="M17 12H7"/></svg>
                        </span>
                        <div>
                            <h2>{{ $reviewsCount }}</h2>
                            <p>My Reviews</p>
                            <button @click="expandedSection = expandedSection === 'reviews' ? null : 'reviews'" class="bottom-link">
                                <span x-text="expandedSection === 'reviews' ? 'Collapse ↑' : 'View all reviews &rarr;'"></span>
                            </button>
                        </div>
                    </div>

                    <div class="stat-card">
                        <span class="stat-icon gold">
                            <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-star"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
                        </span>
                        <div>
                            <h2>{{ $ratingsCount }}</h2>
                            <p>Rate Items</p>
                            <span class="bottom-link" style="cursor: default; opacity: 0.7;">Total Rated</span>
                        </div>
                    </div>

                    <div class="stat-card" :class="expandedSection === 'uploads' ? 'active' : ''">
                        <span class="stat-icon green">
                            <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-cloud-upload"><path d="M4 14.899A7 7 0 1 1 15.71 8h1.79a4.5 4.5 0 0 1 2.5 8.242"/><path d="M12 13v8"/><path d="m8 17 4-4 4 4"/></svg>
                        </span>
                        <div>
                            <h2>{{ $uploadsCount }}</h2>
                            <p>My Uploads</p>
                            <button @click="expandedSection = expandedSection === 'uploads' ? null : 'uploads'" class="bottom-link">
                                <span x-text="expandedSection === 'uploads' ? 'Collapse ↑' : 'View all uploads &rarr;'"></span>
                            </button>
                        </div>
                    </div>

                    <div class="stat-card">
                        <span class="stat-icon purple">
                            <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-award"><circle cx="12" cy="8" r="6"/><path d="M15.477 12.89 17 21.416a.5.5 0 0 1-.81.47l-3.58-2.687a1 1 0 0 0-1.197 0l-3.586 2.686a.5.5 0 0 1-.81-.469l1.514-8.526"/></svg>
                        </span>
                        <div>
                            <h2>{{ number_format($averageRating, 1) }}</h2>
                            <p>Rating Received</p>
                            <span class="bottom-link" style="cursor: default; opacity: 0.7;">Lifetime Average</span>
                        </div>
                    </div>
                </div>

                <!-- Expansion Panels -->
                <div class="expansion-container" x-show="expandedSection" x-collapse x-cloak>
                    <!-- Reviews Panel -->
                    <div x-show="expandedSection === 'reviews'" class="expansion-panel">
                        <div class="panel-header">
                            <h3>My Reviews</h3>
                            <span class="count-pill">{{ $reviewsCount }} Items</span>
                        </div>
                        <div class="dense-list">
                            @forelse($allReviews as $review)
                                <div class="list-row">
                                    <div class="list-left">
                                        <div class="list-icon-bg">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-message-square-text"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/><path d="M13 8H7"/><path d="M17 12H7"/></svg>
                                        </div>
                                        <div class="list-info">
                                            <a href="{{ route('posts.show', $review->post->slug) }}#comment-{{ $review->id }}" class="list-title">{{ $review->post->title }}</a>
                                            <span class="list-subtitle">{{ Str::limit($review->content, 60) }}</span>
                                        </div>
                                    </div>
                                    <div class="list-right">
                                        <span class="list-meta">{{ $review->created_at->format('d M Y') }}</span>
                                        <div class="list-rating">★ {{ number_format($recentReviewRatings->get($review->post_id) ?? 0, 1) }}</div>
                                    </div>
                                </div>
                            @empty
                                <div class="empty-state">No reviews yet.</div>
                            @endforelse
                        </div>
                    </div>



                    <!-- Uploads Panel -->
                    <div x-show="expandedSection === 'uploads'" class="expansion-panel">
                        <div class="panel-header">
                            <h3>My Uploads</h3>
                            <span class="count-pill">{{ $uploadsCount }} Items</span>
                        </div>
                        <div class="dense-list">
                            @forelse($allUploads as $upload)
                                                <div class="list-row">
                                                    <div class="list-left">
                                                        <div class="list-icon-bg green-bg">
                                                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-cloud-upload"><path d="M4 14.899A7 7 0 1 1 15.71 8h1.79a4.5 4.5 0 0 1 2.5 8.242"/><path d="M12 13v8"/><path d="m8 17 4-4 4 4"/></svg>
                                                        </div>
                                                        <div class="list-info">
                                                            <a href="{{ route('posts.show', $upload->post->slug) }}#panel-user-{{ $user->id }}" class="list-title">{{ $upload->post->title }}</a>
                                                            <span class="list-subtitle">Contributed Resource</span>
                                                        </div>
                                                    </div>
                                                    <div class="list-right">
                                                        <span class="list-meta">{{ $upload->created_at->format('d M Y') }}</span>
                                                        <a href="{{ route('posts.show', $upload->post->slug) }}#panel-user-{{ $user->id }}" class="view-btn">View</a>
                                                    </div>
                                                </div>
                            @empty
                                <div class="empty-state">No uploads yet.</div>
                            @endforelse
                        </div>
                    </div>
                </div>

            </div>
        </main>
    </div>
@endsection

@push('scripts')
    @vite('resources/js/profile-detail.js')
@endpush
