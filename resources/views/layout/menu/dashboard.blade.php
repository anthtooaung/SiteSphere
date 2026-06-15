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

    <div class="dashboard-page dashboard-page--{{ $dashboardMenuLocation }}"
         @if($isAdmin) x-data="{ activeKpi: null, hoveredKpi: null }" @endif>
        <x-layout.menu :menu-bar-location="$dashboardMenuLocation" />

        <main class="dashboard-content dashboard-home-content" aria-labelledby="dashboardTitle">
            @if($isAdmin)
                <section class="dashboard-panel mb-4">
                    <p class="dashboard-kicker">Admin Dashboard</p>
                    <h1 id="dashboardTitle" class="text-2xl font-black text-slate-900">System Overview</h1>
                    <p class="text-slate-500">Real-time metrics and platform activity at a glance.</p>
                </section>

                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
                    @php
                        $total = max(1, $stats['totalUsers'] + $stats['totalReviews'] + $stats['totalReports']);
                        $uP = ($stats['totalUsers'] / $total) * 100;
                        $rvP = ($stats['totalReviews'] / $total) * 100;
                        $rpP = ($stats['totalReports'] / $total) * 100;
                    @endphp

                    <div class="lg:col-span-2 grid grid-cols-1 md:grid-cols-3 gap-4">
                        {{-- Users Card --}}
                        <article
                            @click="activeKpi = activeKpi === 'users' ? null : 'users'"
                            @mouseenter="hoveredKpi = 'users'"
                            @mouseleave="hoveredKpi = null"
                            :class="{ 'ring-2 ring-indigo-500 shadow-lg scale-[1.02]': activeKpi === 'users', 'opacity-50 grayscale-[0.5]': (activeKpi && activeKpi !== 'users') || (hoveredKpi && hoveredKpi !== 'users') }"
                            class="dashboard-stat-card cursor-pointer transition-all duration-300 bg-white">
                            <div class="flex flex-col">
                                <span class="text-slate-500 text-xs font-bold uppercase tracking-wider">Total Users</span>
                                <strong class="text-3xl text-slate-900 mt-1">{{ number_format($stats['totalUsers']) }}</strong>
                            </div>
                            <svg class="w-full h-12 mt-4 text-indigo-500 opacity-20" viewBox="0 0 100 20">
                                <path d="M0 15 Q 10 5, 20 12 T 40 8 T 60 15 T 80 5 T 100 12" fill="none" stroke="currentColor" stroke-width="2" />
                            </svg>
                        </article>

                        {{-- Reviews Card --}}
                        <article
                            @click="activeKpi = activeKpi === 'reviews' ? null : 'reviews'"
                            @mouseenter="hoveredKpi = 'reviews'"
                            @mouseleave="hoveredKpi = null"
                            :class="{ 'ring-2 ring-emerald-500 shadow-lg scale-[1.02]': activeKpi === 'reviews', 'opacity-50 grayscale-[0.5]': (activeKpi && activeKpi !== 'reviews') || (hoveredKpi && hoveredKpi !== 'reviews') }"
                            class="dashboard-stat-card cursor-pointer transition-all duration-300 bg-white">
                            <div class="flex flex-col">
                                <span class="text-slate-500 text-xs font-bold uppercase tracking-wider">Total Reviews</span>
                                <strong class="text-3xl text-slate-900 mt-1">{{ number_format($stats['totalReviews']) }}</strong>
                            </div>
                            <svg class="w-full h-12 mt-4 text-emerald-500 opacity-20" viewBox="0 0 100 20">
                                <path d="M0 10 Q 15 18, 30 10 T 60 5 T 100 15" fill="none" stroke="currentColor" stroke-width="2" />
                            </svg>
                        </article>

                        {{-- Reports Card --}}
                        <article
                            @click="activeKpi = activeKpi === 'reports' ? null : 'reports'"
                            @mouseenter="hoveredKpi = 'reports'"
                            @mouseleave="hoveredKpi = null"
                            :class="{ 'ring-2 ring-rose-500 shadow-lg scale-[1.02]': activeKpi === 'reports', 'opacity-50 grayscale-[0.5]': (activeKpi && activeKpi !== 'reports') || (hoveredKpi && hoveredKpi !== 'reports') }"
                            class="dashboard-stat-card cursor-pointer transition-all duration-300 bg-white">
                            <div class="flex flex-col">
                                <span class="text-slate-500 text-xs font-bold uppercase tracking-wider">Total Reports</span>
                                <strong class="text-3xl text-slate-900 mt-1">{{ number_format($stats['totalReports']) }}</strong>
                            </div>
                            <svg class="w-full h-12 mt-4 text-rose-500 opacity-20" viewBox="0 0 100 20">
                                <path d="M0 5 Q 20 15, 40 5 T 80 15 T 100 5" fill="none" stroke="currentColor" stroke-width="2" />
                            </svg>
                        </article>
                    </div>

                    {{-- Donut Chart --}}
                    <div class="dashboard-panel flex items-center justify-center p-6 bg-white rounded-lg border border-slate-200">
                        <svg class="w-48 h-48 transform -rotate-90" viewBox="0 0 42 42">
                            <circle class="text-slate-100" stroke-width="4" stroke="currentColor" fill="transparent" r="15.91549430918954" cx="21" cy="21" />

                            {{-- Users Segment --}}
                            <circle class="text-indigo-500 transition-all duration-500"
                                    stroke-width="4"
                                    :class="{ 'opacity-20': (activeKpi && activeKpi !== 'users') || (hoveredKpi && hoveredKpi !== 'users') }"
                                    stroke-dasharray="{{ $uP }} {{ 100 - $uP }}"
                                    stroke-dashoffset="0"
                                    stroke="currentColor" fill="transparent" r="15.91549430918954" cx="21" cy="21" />

                            {{-- Reviews Segment --}}
                            <circle class="text-emerald-500 transition-all duration-500"
                                    stroke-width="4"
                                    :class="{ 'opacity-20': (activeKpi && activeKpi !== 'reviews') || (hoveredKpi && hoveredKpi !== 'reviews') }"
                                    stroke-dasharray="{{ $rvP }} {{ 100 - $rvP }}"
                                    stroke-dashoffset="{{ -$uP }}"
                                    stroke="currentColor" fill="transparent" r="15.91549430918954" cx="21" cy="21" />

                            {{-- Reports Segment --}}
                            <circle class="text-rose-500 transition-all duration-500"
                                    stroke-width="4"
                                    :class="{ 'opacity-20': (activeKpi && activeKpi !== 'reports') || (hoveredKpi && hoveredKpi !== 'reports') }"
                                    stroke-dasharray="{{ $rpP }} {{ 100 - $rpP }}"
                                    stroke-dashoffset="{{ -($uP + $rvP) }}"
                                    stroke="currentColor" fill="transparent" r="15.91549430918954" cx="21" cy="21" />

                            <g class="transform rotate-90 origin-center">
                                <text x="50%" y="50%" dominant-baseline="middle" text-anchor="middle" class="fill-slate-900 font-black text-[3px] uppercase tracking-tighter">
                                    Metrics
                                </text>
                            </g>
                        </svg>
                    </div>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                    {{-- Recent Activity Timeline --}}
                    <section class="dashboard-panel bg-white p-6 rounded-lg border border-slate-200 h-full" aria-labelledby="activityTitle">
                        <div class="mb-8 flex items-center justify-between">
                            <div>
                                <p class="dashboard-kicker">System Logs</p>
                                <h2 id="activityTitle" class="text-lg font-black text-slate-900 uppercase tracking-tight">Recent Activity</h2>
                            </div>
                            <div class="h-10 w-10 bg-indigo-50 rounded-lg flex items-center justify-center text-indigo-600">
                                <i class="fa-solid fa-clock-rotate-left"></i>
                            </div>
                        </div>
                        <div class="space-y-8 relative before:absolute before:inset-0 before:ml-5 before:w-0.5 before:-translate-x-px before:bg-slate-100">
                            @foreach($recentActivity as $log)
                                <div class="relative flex items-start gap-6 group">
                                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-white ring-4 ring-white shadow-sm transition-transform group-hover:scale-110" style="color: {{ $log->getColor() }}; border: 1px solid {{ $log->getColor() }}20">
                                        <i class="fa-solid {{ $log->getIcon() }}"></i>
                                    </div>
                                    <div class="flex flex-col pt-1">
                                        <p class="text-sm font-bold text-slate-900 leading-tight">{{ $log->action }}</p>
                                        <span class="text-[11px] font-bold text-slate-400 uppercase tracking-widest mt-1">{{ $log->created_at->diffForHumans() }}</span>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </section>

                    {{-- Top Posts Leaderboard --}}
                    <section class="dashboard-panel bg-white p-6 rounded-lg border border-slate-200 h-full" aria-labelledby="leaderboardTitle">
                        <div class="mb-8 flex justify-between items-center">
                            <div>
                                <p class="dashboard-kicker">Engagement</p>
                                <h2 id="leaderboardTitle" class="text-lg font-black text-slate-900 uppercase tracking-tight">Top Posts</h2>
                            </div>
                            <div class="h-10 w-10 bg-amber-50 rounded-lg flex items-center justify-center text-amber-500">
                                <i class="fa-solid fa-trophy"></i>
                            </div>
                        </div>
                        <div class="space-y-3">
                            @foreach($topPosts as $index => $post)
                                <div class="group flex items-center justify-between p-4 rounded-lg bg-slate-50 border border-slate-100 transition-all hover:bg-white hover:shadow-md hover:border-indigo-100">
                                    <div class="flex items-center gap-4">
                                        <span @class([
                                            'flex h-10 w-10 items-center justify-center rounded-lg text-sm font-black shadow-sm',
                                            'bg-amber-400 text-white' => $index === 0,
                                            'bg-slate-300 text-white' => $index === 1,
                                            'bg-orange-400 text-white' => $index === 2,
                                            'bg-white text-slate-400 border border-slate-200' => $index > 2,
                                        ])>
                                            #{{ $index + 1 }}
                                        </span>
                                        <div class="flex flex-col">
                                            <a href="{{ route('posts.show', $post->slug) }}" class="text-sm font-black text-slate-900 group-hover:text-indigo-600 transition-colors truncate max-w-[180px]">
                                                {{ $post->title }}
                                            </a>
                                            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">{{ $post->tags->first()?->categories->first()?->name ?? 'Uncategorized' }}</span>
                                        </div>
                                    </div>
                                    <div class="flex items-center gap-4">
                                        <div class="flex flex-col items-end">
                                            <span class="flex items-center gap-1.5 text-amber-500 text-sm font-black">
                                                <i class="fa-solid fa-star text-[10px]"></i>
                                                {{ number_format($post->ratings_avg_rating ?? 0, 1) }}
                                            </span>
                                            <span class="flex items-center gap-1.5 text-slate-400 text-[10px] font-bold">
                                                <i class="fa-solid fa-comment text-[9px]"></i>
                                                {{ $post->comments_count }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </section>
                </div>
            @else
                <section class="dashboard-panel">
                    <p class="dashboard-kicker">Dashboard</p>
                    <h1 id="dashboardTitle">Welcome back, {{ auth()->user()->name }}</h1>
                    <p>
                        Your workspace is ready with your latest review activity.
                    </p>
                </section>

                <section class="dashboard-stat-grid" aria-label="Dashboard statistics">
                    <article class="dashboard-stat-card">
                        <span>Total Reviews</span>
                        <strong>{{ number_format($stats['totalReviews']) }}</strong>
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
                        <p class="dashboard-empty-state">No reviews yet.</p>
                    @endforelse
                </section>
            @endif
        </main>
    </div>
@endsection
