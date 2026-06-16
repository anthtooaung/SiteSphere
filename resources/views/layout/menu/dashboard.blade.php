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
            @if($isAdmin)
                @php
                    $jsStats = [
                        [
                            "name" => "Site Reviews",
                            "value" => $stats['totalReviews'],
                            "logVal" => log10(max(1, $stats['totalReviews'])),
                            "color" => "#8b5cf6",
                            "icon" => "magnifying-glass",
                            "trendHtml" => "",
                            "trend" => $stats['reviewTrend']
                        ],
                        [
                            "name" => "Total Users",
                            "value" => $stats['totalUsers'],
                            "logVal" => log10(max(1, $stats['totalUsers'])),
                            "color" => "#6366f1",
                            "icon" => "users",
                            "trendHtml" => "",
                            "trend" => $stats['userTrend']
                        ],
                        [
                            "name" => "Open Reports",
                            "value" => $stats['totalReports'],
                            "logVal" => log10(max(1, $stats['totalReports'])),
                            "color" => "#ef4444",
                            "icon" => "flag",
                            "trendHtml" => "",
                            "trend" => $stats['reportTrend']
                        ]
                    ];
                    
                    $jsActs = $recentActivity->map(function($log) {
                        $colorStr = $log->getColor();
                        $iconStr = $log->getIcon();
                        $iconStr = str_replace('fa-', '', $iconStr);
                        return [
                            "color" => $colorStr,
                            "icon" => $iconStr,
                            "txt" => $log->action,
                            "time" => $log->created_at->diffForHumans()
                        ];
                    })->toArray();
                    
                    $jsPosts = $topPosts->map(function($post) {
                        return [
                            "title" => $post->title,
                            "rating" => round($post->ratings_avg_rating ?? 0),
                            "comments" => $post->comments_count
                        ];
                    })->toArray();
                @endphp
                @push('styles')
                    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" />
                    @vite('resources/css/admin-dashboard.css')
                @endpush
                @push('scripts')
                    <script>
                        window.AdminDashboardData = {
                            stats: @json($jsStats),
                            recentActivity: @json($jsActs),
                            topPosts: @json($jsPosts)
                        };
                    </script>
                    @vite('resources/js/admin-dashboard.js')
                @endpush

                <div class="admin-shell">
                  <header class="page-header">
                    <div class="page-header-label">
                      <i class="fa-solid fa-shield-halved"></i> Admin Panel
                    </div>
                    <h1>Admin Dashboard</h1>
                    <p>Overview of platform activity, user growth, and content moderation status.</p>
                  </header>

                  <section class="infographic-section">
                    <div class="infographic-card">
                      <div class="infographic-head">
                        <div class="infographic-head-left">
                          <div class="infographic-head-icon">
                            <i class="fa-solid fa-chart-pie"></i>
                          </div>
                          <div class="infographic-head-title">Admin Overview</div>
                        </div>
                        <div class="overview-month-wrap">
                          <button class="overview-month-btn" id="overview-month-btn">
                            <span id="overview-month-label">{{ now()->format('M Y') }}</span>
                            <i class="fa-solid fa-chevron-down omc"></i>
                          </button>
                          <div class="overview-month-picker" id="overview-month-picker">
                            <div class="cmp-year-row">
                              <button class="cmp-ynav" type="button"><i class="fa-solid fa-chevron-left"></i></button>
                              <span class="cmp-year-label" id="overview-picker-year">{{ now()->year }}</span>
                              <button class="cmp-ynav" type="button"><i class="fa-solid fa-chevron-right"></i></button>
                            </div>
                            <div class="cmp-month-grid" id="overview-picker-month-grid"></div>
                          </div>
                        </div>
                      </div>

                      <div class="infographic-body">
                        <div class="ov9-row" id="ov-layout-9">
                          <div class="ov9-pie-tile">
                            <div class="ov9-pie-head">
                              <span class="ov9-pie-head-title">Activity Distribution</span>
                            </div>
                            <div class="ov9-pie-body">
                              <div style="position: relative; width: 180px; height: 180px">
                                <svg id="cat-svg-9" viewBox="0 0 400 400" xmlns="http://www.w3.org/2000/svg" style="display: block; width: 180px; height: 180px"></svg>
                              </div>
                            </div>
                            <div class="ov9-pie-legend">
                              <span class="ov9-leg-item"><span class="ov9-leg-dot" style="background: #8b5cf6"></span><span class="ov9-leg-txt">Reviews</span></span>
                              <span class="ov9-leg-item"><span class="ov9-leg-dot" style="background: #6366f1"></span><span class="ov9-leg-txt">Users</span></span>
                              <span class="ov9-leg-item"><span class="ov9-leg-dot" style="background: #ef4444"></span><span class="ov9-leg-txt">Reports</span></span>
                            </div>
                          </div>
                          <div class="ov9-kpi-col">
                            <div class="ov9-kpi ov9-kpi--users" data-ov9-idx="1" role="button" tabindex="0" aria-label="Filter by Users">
                              <div class="ov9-kpi-top-row">
                                <div class="ov9-kpi-top">
                                  <span class="ov9-kpi-icon" style="background: rgba(99, 102, 241, 0.12); color: #6366f1;"><i class="fa-solid fa-users"></i></span>
                                  <span class="kpi-lbl">Users</span>
                                </div>
                                <div class="ov9-kpi-bottom">
                                  <div class="kpi-val">{{ number_format($stats['totalUsers']) }}</div>
                                </div>
                              </div>
                              <div class="ov9-kpi-spark-row">
                                <div class="ov9-spark-wrap" data-trend="@json($stats['userTrend'])" data-color="#6366f1">
                                  <svg class="ov9-spark" viewBox="0 0 100 48" preserveAspectRatio="none" xmlns="http://www.w3.org/2000/svg">
                                    <defs><linearGradient id="sg9u" x1="0" y1="0" x2="0" y2="1"><stop offset="0%" stop-color="#6366f1" stop-opacity="0.18"/><stop offset="100%" stop-color="#6366f1" stop-opacity="0"/></linearGradient></defs>
                                    <path class="spark-fill" d="" fill="url(#sg9u)"/><path class="spark-line" d="" fill="none" stroke="#6366f1" stroke-width="1.8" stroke-linecap="round"/>
                                  </svg>
                                </div>
                              </div>
                            </div>
                            <div class="ov9-kpi ov9-kpi--audits" data-ov9-idx="0" role="button" tabindex="0" aria-label="Filter by Reviews">
                              <div class="ov9-kpi-top-row">
                                <div class="ov9-kpi-top">
                                  <span class="ov9-kpi-icon" style="background: rgba(139, 92, 246, 0.12); color: #8b5cf6;"><i class="fa-solid fa-magnifying-glass"></i></span>
                                  <span class="kpi-lbl">Reviews</span>
                                </div>
                                <div class="ov9-kpi-bottom">
                                  <div class="kpi-val">{{ number_format($stats['totalReviews']) }}</div>
                                </div>
                              </div>
                              <div class="ov9-kpi-spark-row">
                                <div class="ov9-spark-wrap" data-trend="@json($stats['reviewTrend'])" data-color="#8b5cf6">
                                  <svg class="ov9-spark" viewBox="0 0 100 48" preserveAspectRatio="none" xmlns="http://www.w3.org/2000/svg">
                                    <defs><linearGradient id="sg9a" x1="0" y1="0" x2="0" y2="1"><stop offset="0%" stop-color="#8b5cf6" stop-opacity="0.18"/><stop offset="100%" stop-color="#8b5cf6" stop-opacity="0"/></linearGradient></defs>
                                    <path class="spark-fill" d="" fill="url(#sg9a)"/><path class="spark-line" d="" fill="none" stroke="#8b5cf6" stroke-width="1.8" stroke-linecap="round"/>
                                  </svg>
                                </div>
                              </div>
                            </div>
                            <div class="ov9-kpi ov9-kpi--reports" data-ov9-idx="2" role="button" tabindex="0" aria-label="Filter by Reports">
                              <div class="ov9-kpi-top-row">
                                <div class="ov9-kpi-top">
                                  <span class="ov9-kpi-icon" style="background: rgba(239, 68, 68, 0.1); color: #ef4444;"><i class="fa-solid fa-flag"></i></span>
                                  <span class="kpi-lbl">Reports</span>
                                </div>
                                <div class="ov9-kpi-bottom">
                                  <div class="kpi-val kpi-val--danger">{{ number_format($stats['totalReports']) }}</div>
                                </div>
                              </div>
                              <div class="ov9-kpi-spark-row">
                                <div class="ov9-spark-wrap" data-trend="@json($stats['reportTrend'])" data-color="#ef4444">
                                  <svg class="ov9-spark" viewBox="0 0 100 48" preserveAspectRatio="none" xmlns="http://www.w3.org/2000/svg">
                                    <defs><linearGradient id="sg9r" x1="0" y1="0" x2="0" y2="1"><stop offset="0%" stop-color="#ef4444" stop-opacity="0.18"/><stop offset="100%" stop-color="#ef4444" stop-opacity="0"/></linearGradient></defs>
                                    <path class="spark-fill" d="" fill="url(#sg9r)"/><path class="spark-line" d="" fill="none" stroke="#ef4444" stroke-width="1.8" stroke-linecap="round"/>
                                  </svg>
                                </div>
                              </div>
                            </div>
                          </div>
                        </div>
                      </div>
                      <div id="cat-popup" style="display: none; position: absolute; z-index: -1; pointer-events: none;"></div>
                    </div>
                  </section>

                  <section class="row row-lower">
                    <div class="card">
                      <div class="card-head">
                        <div class="card-head-left">
                          <div class="card-icon" style="background: color-mix(in srgb, var(--accent-color) 8%, transparent); color: var(--accent-color)">
                            <i class="fa-solid fa-clock-rotate-left"></i>
                          </div>
                          <span class="card-title">Recent Activity</span>
                        </div>
                      </div>
                      <div class="act-body">
                        <div style="flex: 1; min-width: 0">
                          <div class="timeline" id="activity-list"></div>
                        </div>
                        <div class="act-legend">
                          <span class="act-legend-item"><span class="act-legend-dot" style="background: #ef4444"></span> Ban / Delete</span>
                          <span class="act-legend-item"><span class="act-legend-dot" style="background: #10b981"></span> Resolved / Approved</span>
                          <span class="act-legend-item"><span class="act-legend-dot" style="background: #7c3aed"></span> Announcement / Bulk</span>
                          <span class="act-legend-item"><span class="act-legend-dot" style="background: var(--accent-color)"></span> Warning / Settings</span>
                        </div>
                      </div>
                      <a href="{{ route('admin.activity-log') }}" class="see-more-link">See More</a>
                    </div>

                    <div class="card">
                      <div class="card-head">
                        <div class="card-head-left">
                          <div class="card-icon" style="background: #fefce8; color: #ca8a04">
                            <i class="fa-solid fa-trophy"></i>
                          </div>
                          <span class="card-title">Top Rated Posts</span>
                        </div>
                      </div>
                      <div id="top-posts"></div>
                    </div>
                  </section>
                </div>
            @else
                <section class="dashboard-panel">
                    <p class="dashboard-kicker">Dashboard</p>
                    <h1 id="dashboardTitle">
                        @auth
                            Welcome back, {{ auth()->user()->name }}
                        @else
                            Welcome to SiteSphere
                        @endauth
                    </h1>
                    <p>
                        Your workspace is ready with your latest review activity.
                    </p>
                </section>

                <section class="dashboard-stat-grid" aria-label="Dashboard statistics">
                    <article class="dashboard-stat-card">
                        <div class="stat-icon-wrap" style="background: rgba(139, 92, 246, 0.12); color: #8b5cf6;">
                            <i class="fa-solid fa-magnifying-glass"></i>
                        </div>
                        <div class="stat-content">
                            <span>Total Reviews</span>
                            <strong>{{ number_format($stats['totalReviews']) }}</strong>
                        </div>
                    </article>

                    <article class="dashboard-stat-card">
                        <div class="stat-icon-wrap" style="background: rgba(99, 102, 241, 0.12); color: #6366f1;">
                            <i class="fa-solid fa-bookmark"></i>
                        </div>
                        <div class="stat-content">
                            <span>Saved Posts</span>
                            <strong>{{ number_format($stats['savedPosts']) }}</strong>
                        </div>
                    </article>

                    <article class="dashboard-stat-card">
                        <div class="stat-icon-wrap" style="background: rgba(245, 158, 11, 0.12); color: #f59e0b;">
                            <i class="fa-solid fa-star"></i>
                        </div>
                        <div class="stat-content">
                            <span>Ratings Given</span>
                            <strong>{{ number_format($stats['ratingsGiven']) }}</strong>
                        </div>
                    </article>

                    <article class="dashboard-stat-card">
                        <div class="stat-icon-wrap" style="background: rgba(16, 185, 129, 0.12); color: #10b981;">
                            <i class="fa-solid fa-globe"></i>
                        </div>
                        <div class="stat-content">
                            <span>Reviewed Websites</span>
                            <strong>{{ number_format($stats['reviewedWebsites']) }}</strong>
                        </div>
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
                        <p class="dashboard-empty-state">No reviews yet.</p>
                    @endforelse
                </section>
            @endif
        </main>
    </div>
@endsection
