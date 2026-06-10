@extends('dashboard')

@section('title')
    Dashboard
@endsection

@section('content')
    @php
        $dashboardMenuLocation = in_array($menuBarLocation ?? 'left', ['top', 'right', 'bottom', 'left'], true)
            ? $menuBarLocation
            : 'left';
    @endphp

    <x-layout.nav />

    <div class="dashboard-page dashboard-page--{{ $dashboardMenuLocation }}">
        <x-layout.menu :menu-bar-location="$dashboardMenuLocation" />

        <main class="dashboard-content dashboard-home-content" aria-labelledby="dashboardTitle">
            <section class="dashboard-panel">
                <p class="dashboard-kicker">Dashboard</p>
                <h1 id="dashboardTitle">Welcome back, {{ auth()->user()->name }}</h1>
                <p>
                    Your workspace is ready with your latest review activity.
                </p>
            </section>

            <section class="dashboard-stat-grid" aria-label="Dashboard statistics">
                <article class="dashboard-stat-card">
                    <span>My Reviews</span>
                    <strong>{{ number_format($stats['visibleReviews']) }}</strong>
                </article>

                <article class="dashboard-stat-card">
                    <span>Saved Posts</span>
                    <strong>{{ number_format($stats['savedPosts']) }}</strong>
                </article>

                <article class="dashboard-stat-card">
                    <span>Ratings Given</span>
                    <strong>{{ number_format($stats['ratingsGiven']) }}</strong>
                </article>

                <article class="dashboard-stat-card">
                    <span>Reviewed Websites</span>
                    <strong>{{ number_format($stats['reviewedWebsites']) }}</strong>
                </article>
            </section>

            <section class="dashboard-panel dashboard-activity-panel" aria-labelledby="dashboardRecentReviewsTitle">
                <div class="dashboard-section-heading">
                    <p class="dashboard-kicker">Recent Activity</p>
                    <h2 id="dashboardRecentReviewsTitle">Recent reviews</h2>
                </div>

                @forelse ($recentReviews as $review)
                    <a class="dashboard-activity-link" href="{{ route('posts.show', $review->post->slug) }}">
                        <span>{{ $review->post->title }}</span>
                        <small>{{ $review->created_at->diffForHumans() }}</small>
                    </a>
                @empty
                    <p class="dashboard-empty-state">No visible reviews yet.</p>
                @endforelse
            </section>
        </main>
    </div>
@endsection
