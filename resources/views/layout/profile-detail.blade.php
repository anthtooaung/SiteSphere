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
                            <x-far-pen-to-square />
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
                                        <x-fas-circle-check class="verified" title="Verified Account" />
                                    @endif
                                </div>

                                <p>
                                    {{ $user->user_bio ?? 'Passionate reviewer and community member of SiteSphere.' }}
                                </p>

                                <div class="social-icons">
                                    <a href="mailto:{{ $user->email }}" aria-label="Email">
                                        <x-far-envelope />
                                    </a>
                                    @if($user->user_phone)
                                        <a href="tel:{{ $user->user_phone }}" aria-label="Phone">
                                            <x-fas-phone />
                                        </a>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <!-- Right -->
                        <div class="right-section">
                            <div class="info-grid">
                                <div class="info-item">
                                    <x-far-envelope />
                                    <div>
                                        <span>Email</span>
                                        <h4>{{ $user->email }}</h4>
                                    </div>
                                </div>

                                <div class="info-item">
                                    <x-fas-phone />
                                    <div>
                                        <span>Phone</span>
                                        <h4>{{ $user->user_phone ? '+95'.$user->user_phone : 'Not specified' }}</h4>
                                    </div>
                                </div>

                                <div class="info-item">
                                    <x-far-calendar />
                                    <div>
                                        <span>Date of Birth</span>
                                        <h4>{{ $user->user_dob ? \Carbon\Carbon::parse($user->user_dob)->format('d F Y') : 'Not specified' }}</h4>
                                    </div>
                                </div>

                                <div class="info-item">
                                    <x-far-clock />
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
                            <x-fas-comment-dots />
                        </span>
                        <div>
                            <h2>{{ $reviewsCount }}</h2>
                            <p>My Reviews</p>
                            <button @click="expandedSection = expandedSection === 'reviews' ? null : 'reviews'" class="bottom-link">
                                <span x-text="expandedSection === 'reviews' ? 'Collapse ↑' : 'View all reviews &rarr;'"></span>
                            </button>
                        </div>
                    </div>

                    <div class="stat-card" :class="expandedSection === 'ratings' ? 'active' : ''">
                        <span class="stat-icon gold">
                            <x-fas-star />
                        </span>
                        <div>
                            <h2>{{ $ratingsCount }}</h2>
                            <p>Rate Items</p>
                            <button @click="expandedSection = expandedSection === 'ratings' ? null : 'ratings'" class="bottom-link">
                                <span x-text="expandedSection === 'ratings' ? 'Collapse ↑' : 'View all ratings &rarr;'"></span>
                            </button>
                        </div>
                    </div>

                    <div class="stat-card" :class="expandedSection === 'uploads' ? 'active' : ''">
                        <span class="stat-icon green">
                            <x-fas-upload />
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
                            <x-fas-ranking-star />
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
                                        <div class="list-icon-bg"><x-fas-comment-dots /></div>
                                        <div class="list-info">
                                            <a href="{{ route('posts.show', $review->post->slug) }}" class="list-title">{{ $review->post->title }}</a>
                                            <span class="list-subtitle">{{ Str::limit($review->description, 60) }}</span>
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

                    <!-- Ratings Panel -->
                    <div x-show="expandedSection === 'ratings'" class="expansion-panel">
                        <div class="panel-header">
                            <h3>Rate Items</h3>
                            <span class="count-pill">{{ $ratingsCount }} Items</span>
                        </div>
                        <div class="dense-list">
                            @forelse($allRatings as $rating)
                                <div class="list-row">
                                    <div class="list-left">
                                        <div class="list-icon-bg gold-bg"><x-fas-star /></div>
                                        <div class="list-info">
                                            <a href="{{ route('posts.show', $rating->post->slug) }}" class="list-title">{{ $rating->post->title }}</a>
                                            <span class="list-subtitle">Given Rating</span>
                                        </div>
                                    </div>
                                    <div class="list-right">
                                        <span class="list-meta">{{ $rating->created_at->format('d M Y') }}</span>
                                        <div class="list-rating gold-text">★ {{ number_format($rating->rating, 1) }}</div>
                                    </div>
                                </div>
                            @empty
                                <div class="empty-state">No ratings given yet.</div>
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
                            @forelse($allReviews as $upload) {{-- Using reviews as uploads for now --}}
                                <div class="list-row">
                                    <div class="list-left">
                                        <div class="list-icon-bg green-bg"><x-fas-upload /></div>
                                        <div class="list-info">
                                            <a href="{{ route('posts.show', $upload->post->slug) }}" class="list-title">{{ $upload->post->title }}</a>
                                            <span class="list-subtitle">Contributed Resource</span>
                                        </div>
                                    </div>
                                    <div class="list-right">
                                        <span class="list-meta">{{ $upload->created_at->format('d M Y') }}</span>
                                        <a href="{{ route('posts.show', $upload->post->slug) }}" class="view-btn">View</a>
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
