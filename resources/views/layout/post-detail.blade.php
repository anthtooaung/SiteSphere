@extends('dashboard')

@section('title')
    {{ $post->title }}
@endsection

@push('styles')
    @vite('resources/css/post-detail.css')
@endpush

@section('content')
    @php
        $dashboardMenuLocation = in_array($menuBarLocation ?? 'left', ['top', 'right', 'bottom', 'left'], true)
            ? $menuBarLocation
            : 'left';
        
        $host = parse_url($post->url, PHP_URL_HOST) ?? $post->url;
    @endphp

    <x-layout.nav />

    <div class="dashboard-page dashboard-page--{{ $dashboardMenuLocation }} post-detail-page">
        <main class="dashboard-content post-detail-content">
            @if ($isBanned)
                <div class="banned-banner" style="position: sticky; top: -24px; margin: -24px -24px 24px -24px; z-index: 40;">
                    <div class="banned-banner-inner">
                        <div class="banned-banner-icon">
                            <x-fas-ban class="size-5" aria-hidden="true" />
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
                        
                    </div>
                </div>
            @elseif ($isUnsecure)
                <div class="banned-banner" style="background: linear-gradient(135deg, #f59e0b, #d97706); color: #fff; position: sticky; top: -24px; margin: -24px -24px 24px -24px; z-index: 40;">
                    <div class="banned-banner-inner">
                        <div class="banned-banner-icon">
                            <x-fas-shield-halved class="size-5" aria-hidden="true" />
                        </div>
                        <div class="banned-banner-content">
                            <div class="banned-banner-title">This post is unsecure</div>
                            <div class="banned-banner-reason" style="color: rgba(255,255,255,0.9);">This URL cannot be used for new posts.</div>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Background Blur -->
            <div class="bg-blur blur1"></div>
            <div class="bg-blur blur2"></div>

            <div class="aud">
                <div class="ss-page">
                    <!-- =================================================================
                         MASTHEAD
                    ================================================================= -->
                    <header class="aud-mast">
                        <div class="aud-mast-top">
                            <span class="ss-eyebrow"></span>
                            <span class="aud-ref ss-mono"></span>
                        </div>

                        <!-- Title + three-dot menu -->
                        <div class="aud-title-row">
                            <h1 class="aud-title">
                                {{ $post->title }}
                            </h1>

                            <div class="relative shrink-0" x-data="{
                                actionsOpen: false,
                                reportOpen: false,
                                reportReason: '',
                                reportDetails: '',
                                saved: @js((bool) $saved),
                                openReportModal() {
                                    this.actionsOpen = false;
                                    this.reportOpen = true;
                                },
                                closeReportModal() {
                                    this.reportOpen = false;
                                },
                                reportDetailsCount() {
                                    return this.reportDetails.length;
                                }
                            }" x-on:click.outside="actionsOpen = false">
                                <button type="button"
                                    class="flex size-9 shrink-0 items-center justify-center rounded-full border [border-color:var(--line)] [background:var(--paper)] [color:color-mix(in_srgb,var(--text-color,#0d1b2a)_62%,transparent)] transition-all hover:[border-color:var(--accent-line)] hover:[background:var(--accent-wash)] hover:[color:var(--accent-ink)]"
                                    aria-label="More options" aria-haspopup="menu" x-on:click.stop="actionsOpen = ! actionsOpen"
                                    x-on:keydown.escape.window="actionsOpen = false" x-bind:aria-expanded="actionsOpen.toString()">
                                    <x-fas-ellipsis class="size-4" aria-hidden="true" />
                                </button>

                                <div x-cloak x-show="actionsOpen" x-transition:enter="transition ease-out duration-200 origin-top-right"
                                    x-transition:enter-start="opacity-0 -translate-y-1 scale-95"
                                    x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                                    x-transition:leave="transition ease-in duration-150 origin-top-right"
                                    x-transition:leave-start="opacity-100 translate-y-0 scale-100"
                                    x-transition:leave-end="opacity-0 -translate-y-1 scale-95"
                                    class="absolute right-0 top-11 z-20 w-40 overflow-hidden rounded-lg border p-2 text-sm font-bold [border-color:color-mix(in_srgb,var(--accent-color,#6c5ce7)_20%,var(--background-color,#ffffff))] [background:var(--background-color,#ffffff)] [box-shadow:0_16px_36px_color-mix(in_srgb,var(--text-color,#0d1b2a)_18%,transparent)]"
                                    role="menu">
                                    @auth
                                        @if ($post->id)
                                            <form method="POST" action="{{ route('posts.bookmark', $post->id) }}">
                                                @csrf
                                                <button type="submit"
                                                    class="flex min-h-9 w-full items-center gap-2 rounded-lg px-2.5 py-1.5 text-left transition-all duration-[180ms] [color:color-mix(in_srgb,var(--text-color,#0d1b2a)_78%,transparent)] hover:translate-x-0.5 hover:[background:color-mix(in_srgb,var(--accent-color,#6c5ce7)_12%,transparent)] hover:[color:var(--accent-color,#6c5ce7)] focus:outline-none focus-visible:translate-x-0.5 focus-visible:ring-2 focus-visible:[--tw-ring-color:color-mix(in_srgb,var(--accent-color,#6c5ce7)_35%,transparent)]"
                                                    role="menuitem">
                                                    <x-fas-bookmark class="size-3" aria-hidden="true" x-show="saved" style="color: var(--accent-color, #6c5ce7);" />
                                                    <x-far-bookmark class="size-3" aria-hidden="true" x-show="!saved" />
                                                    <span
                                                        x-text="saved ? 'Unsave Post' : 'Save Post'">{{ $saved ? 'Unsave Post' : 'Save Post' }}</span>
                                                </button>
                                            </form>

                                            @if (Auth::user()?->role !== 'admin' && Auth::id() !== $post->user_id && !$post->userPosts->contains('user_id', Auth::id()))
                                                <div
                                                    class="mt-1 border-t pt-1 [border-color:color-mix(in_srgb,var(--text-color,#0d1b2a)_10%,transparent)]">
                                                    <button type="button"
                                                        class="flex min-h-9 w-full items-center gap-2 rounded-lg px-2.5 py-1.5 text-left transition-all duration-[180ms] [color:color-mix(in_srgb,var(--text-color,#0d1b2a)_78%,transparent)] hover:translate-x-0.5 hover:[background:color-mix(in_srgb,var(--accent-color,#6c5ce7)_12%,transparent)] hover:[color:var(--accent-color,#6c5ce7)] focus:outline-none focus-visible:translate-x-0.5 focus-visible:ring-2 focus-visible:[--tw-ring-color:color-mix(in_srgb,var(--accent-color,#6c5ce7)_35%,transparent)]"
                                                        role="menuitem"
                                                        x-on:click="openReportModal()">
                                                        <x-fas-flag class="size-3" aria-hidden="true" />
                                                        <span>Report</span>
                                                    </button>
                                                </div>
                                            @endif

                                            @if (Auth::user()?->role === 'admin')
                                                <form method="POST" action="{{ route('posts.toggle-unsecure', $post->id) }}"
                                                    class="mt-1 border-t pt-1 [border-color:color-mix(in_srgb,var(--text-color,#0d1b2a)_10%,transparent)]">
                                                    @csrf
                                                    <button type="submit"
                                                        class="flex min-h-9 w-full items-center gap-2 rounded-lg px-2.5 py-1.5 text-left transition-all duration-[180ms] {{ $post->is_unsecure ? 'text-green-600 hover:[background:color-mix(in_srgb,#16a34a_12%,transparent)]' : '[color:#d97706] hover:[background:color-mix(in_srgb,#d97706_12%,transparent)]' }} focus:outline-none focus-visible:translate-x-0.5 focus-visible:ring-2"
                                                        role="menuitem">
                                                        <x-fas-shield-halved class="size-3" aria-hidden="true" />
                                                        <span>{{ $post->is_unsecure ? 'Mark Secure' : 'Mark Unsecure' }}</span>
                                                    </button>
                                                </form>
                                            @endif
                                        @endif
                                    @endauth

                                    @guest
                                        <a href="{{ route('login') }}"
                                            class="flex min-h-9 w-full items-center gap-2 rounded-lg px-2.5 py-1.5 text-left transition-all duration-[180ms] [color:color-mix(in_srgb,var(--text-color,#0d1b2a)_78%,transparent)] hover:translate-x-0.5 hover:[background:color-mix(in_srgb,var(--accent-color,#6c5ce7)_12%,transparent)] hover:[color:var(--accent-color,#6c5ce7)] focus:outline-none focus-visible:translate-x-0.5 focus-visible:ring-2 focus-visible:[--tw-ring-color:color-mix(in_srgb,var(--accent-color,#6c5ce7)_35%,transparent)]"
                                            role="menuitem">
                                            <x-far-bookmark class="size-3" aria-hidden="true" />
                                            <span>Save Post</span>
                                        </a>
                                        <a href="{{ route('login') }}"
                                            class="mt-1 flex min-h-9 w-full items-center gap-2 rounded-lg border-t px-2.5 py-1.5 pt-2 text-left transition-all duration-[180ms] [border-color:color-mix(in_srgb,var(--text-color,#0d1b2a)_10%,transparent)] [color:color-mix(in_srgb,var(--text-color,#0d1b2a)_78%,transparent)] hover:translate-x-0.5 hover:[background:color-mix(in_srgb,var(--accent-color,#6c5ce7)_12%,transparent)] hover:[color:var(--accent-color,#6c5ce7)] focus:outline-none focus-visible:translate-x-0.5 focus-visible:ring-2 focus-visible:[--tw-ring-color:color-mix(in_srgb,var(--accent-color,#6c5ce7)_35%,transparent)]"
                                            role="menuitem">
                                            <x-fas-flag class="size-3" aria-hidden="true" />
                                            <span>Report</span>
                                        </a>
                                    @endguest
                                </div>
                                <x-layout.report-modal :post-id="$post->id" />
                            </div>
                        </div>

                        <!-- Domain pill -->
                        <div class="aud-mast-row">
                            <a
                                class="aud-domain"
                                href="{{ $post->url }}"
                                target="_blank"
                                rel="noreferrer"
                            >
                                <span class="aud-domain-chain" aria-hidden="true">
                                    <x-fas-link class="size-3.5" />
                                </span>
                                <span class="aud-domain-text">{{ $host }}</span>
                                <span class="aud-domain-ext" aria-hidden="true">
                                    <x-fas-arrow-up-right-from-square class="size-3" />
                                </span>
                            </a>
                        </div>

                        <!-- Taxonomy tags -->
                        <div class="aud-tags-container" style="display: flex; flex-direction: column; gap: 8px; margin-top: 18px;">
                            @if($post->tags->flatMap->categories->unique('id')->isNotEmpty())
                                <div class="aud-categories" style="display: flex; flex-wrap: wrap; gap: 8px;">
                                    @foreach($post->tags->flatMap->categories->unique('id') as $category)
                                        @php
                                            $catColor = $category->category_color ?? '#6c5ce7';
                                        @endphp
                                        <span class="ss-tag is-cat" style="background-color: color-mix(in srgb, {{ $catColor }} 14%, transparent); color: {{ $catColor }};">
                                            {{ $category->name }}
                                        </span>
                                    @endforeach
                                </div>
                            @endif
                            @if($post->tags->isNotEmpty())
                                <div class="aud-tags" style="display: flex; flex-wrap: wrap; gap: 8px; margin-top: 0;">
                                    @foreach($post->tags as $tag)
                                        <x-tag :tag="$tag" class="ss-tag is-accent" />
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    </header>

                    <!-- =================================================================
                         VERDICT STRIP  — score + rating breakdown
                    ================================================================= -->
                    <section class="aud-verdict ss-score-block" aria-label="Overall verdict">
                        <!-- Score column -->
                        <div class="aud-verdict-score">
                            <span class="ss-eyebrow">Overall Rating</span>

                            <div class="aud-score-num">
                                <strong class="ss-tnum">{{ number_format($averageRating, 1) }}</strong>
                                <span>/ 5</span>
                            </div>

                            <!-- Stars -->
                            <span
                                class="ss-stars"
                                role="img"
                                aria-label="{{ $averageRating }} out of 5"
                                style="--sz: 18px; --gap: 2px; --pct: {{ ($averageRating / 5) * 100 }}%"
                            >
                                <span class="ss-stars-track">
                                    @for($i = 0; $i < 5; $i++)
                                        <svg viewBox="0 0 24 24" width="18" height="18">
                                            <path d="M12 2.4l2.95 5.98 6.6.96-4.78 4.66 1.13 6.57L12 17.5l-5.9 3.07 1.13-6.57L2.45 9.34l6.6-.96L12 2.4z" />
                                        </svg>
                                    @endfor
                                </span>
                                <span class="ss-stars-fill">
                                    @for($i = 0; $i < 5; $i++)
                                        <svg viewBox="0 0 24 24" width="18" height="18">
                                            <path d="M12 2.4l2.95 5.98 6.6.96-4.78 4.66 1.13 6.57L12 17.5l-5.9 3.07 1.13-6.57L2.45 9.34l6.6-.96L12 2.4z" />
                                        </svg>
                                    @endfor
                                </span>
                            </span>

                            <span class="aud-score-meta ss-mono">{{ $ratingsCount }} reviews · {{ $auditsCount }} audits</span>
                        </div>

                        <!-- Distribution column -->
                        <div class="aud-verdict-dist">
                            <span class="ss-eyebrow">Rating Breakdown</span>

                            <div class="ss-bars is-compact">
                                @foreach([5, 4, 3, 2, 1] as $star)
                                    @php
                                        $count = $ratingDistribution[$star] ?? 0;
                                        $pct = $ratingsCount > 0 ? round(($count / $ratingsCount) * 100) : 0;
                                    @endphp
                                    <div class="ss-bar-row" data-stars="{{ $star }}">
                                        <span class="ss-bar-label">{{ $star }}</span>
                                        <span class="ss-bar-track">
                                            <span class="ss-bar-fill" style="width: {{ $pct }}%"></span>
                                        </span>
                                        <span class="ss-bar-count">{{ $count }}</span>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </section>

                    <!-- =================================================================
                         DETAILED REPORTS  — tabbed expert depositions
                    ================================================================= -->
                    @if($post->userPosts->isNotEmpty())
                        <section class="aud-depo" aria-label="Detailed Reports">
                            <div class="aud-depo-head">
                                <div>
                                    <h2 class="aud-h2">Detailed Description</h2>
                                    <p class="aud-sub">Expert audits and technical reviews from verified contributors.</p>
                                </div>
                            </div>

                            <div class="aud-depo-grid">
                                <!-- ---- Sidebar tab nav ---- -->
                                <nav class="aud-depo-index" aria-label="Auditors" id="depoNav">
                                    @foreach($post->userPosts as $userPost)
                                        @php
                                            $isProfileVisible = ! $userPost->user_hidden;
                                            $displayName = $isProfileVisible ? $userPost->user->name : 'Anonymous';
                                            $initials = $isProfileVisible 
                                                ? collect(explode(' ', $userPost->user->name))->map(fn($n) => Str::substr($n, 0, 1))->join('')
                                                : '?';
                                            $hue = $isProfileVisible ? (($userPost->user->id * 47) % 360) : 222;
                                            $avatarUrl = $isProfileVisible ? $userPost->user->getAvatarUrl() : '';
                                        @endphp
                                        <button
                                            type="button"
                                            class="aud-depo-tab @if($loop->first) is-active @endif"
                                            data-contributor="user-{{ $userPost->user->id }}"
                                            @if($isProfileVisible) data-hover-profile="{{ $userPost->user->id }}" @endif
                                            aria-selected="{{ $loop->first ? 'true' : 'false' }}"
                                        >
                                            @if($avatarUrl)
                                                <img src="{{ $avatarUrl }}" alt="{{ $displayName }} profile" class="ss-avatar" style="width: 40px; height: 40px; border-radius: 50%; object-fit: cover; flex-shrink: 0;">
                                            @else
                                                <span
                                                    class="ss-avatar is-initial"
                                                    aria-hidden="true"
                                                    style="width: 40px; height: 40px; border-radius: 50%; --ph-hue: {{ $hue }};"
                                                >
                                                    <span class="ss-avatar-initials" style="font-size: 13.6px">{{ $initials }}</span>
                                                </span>
                                            @endif
                                            <span class="aud-depo-tab-body">
                                                <span class="aud-depo-tab-name">
                                                    {{ $displayName }}
                                                    @if($userPost->user->isUnsecure())
                                                        <span class="unsecure-badge" title="Unsecure Account" style="display: inline-flex; align-items: center; gap: 4px; padding: 2px 6px; background: color-mix(in srgb, #d97706 15%, transparent); color: #d97706; border: 1px solid color-mix(in srgb, #d97706 30%, transparent); border-radius: 10px; font-size: 10px; font-weight: 600; vertical-align: middle; margin-left: 6px;">
                                                            <x-fas-shield-halved style="width: 10px; height: 10px;" />
                                                            Unsecure
                                                        </span>
                                                    @else
                                                        <svg style="color: var(--accent-color); margin-left: 6px;" class="inline-block size-3" fill="currentColor" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512"><!--! Font Awesome Free 7.2.0 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free (Icons: CC BY 4.0, Fonts: SIL OFL 1.1, Code: MIT License) Copyright 2026 Fonticons, Inc. --><path fill="currentColor" d="M256 512a256 256 0 1 1 0-512 256 256 0 1 1 0 512zM374 145.7c-10.7-7.8-25.7-5.4-33.5 5.3L221.1 315.2 169 263.1c-9.4-9.4-24.6-9.4-33.9 0s-9.4 24.6 0 33.9l72 72c5 5 11.8 7.5 18.8 7s13.4-4.1 17.5-9.8L379.3 179.2c7.8-10.7 5.4-25.7-5.3-33.5z"></path></svg>
                                                    @endif
                                                </span>
                                            </span>
                                        </button>
                                    @endforeach
                                </nav>

                                <!-- ---- Panel slot: one panel per contributor ---- -->
                                <div id="depoPanels">
                                    @foreach($post->userPosts as $userPost)
                                        @php
                                            $isProfileVisible = ! $userPost->user_hidden;
                                            $displayName = $isProfileVisible ? $userPost->user->name : 'Anonymous';
                                            $initials = $isProfileVisible 
                                                ? collect(explode(' ', $userPost->user->name))->map(fn($n) => Str::substr($n, 0, 1))->join('')
                                                : '?';
                                            $hue = $isProfileVisible ? (($userPost->user->id * 47) % 360) : 222;
                                            $avatarUrl = $isProfileVisible ? $userPost->user->getAvatarUrl() : '';
                                        @endphp
                                        <article
                                            class="aud-depo-panel @if($userPost->trashed()) banned-border banned-tooltip @endif"
                                            id="panel-user-{{ $isProfileVisible ? $userPost->user->id : 'anonymous-' . $loop->index }}"
                                            data-panel="user-{{ $isProfileVisible ? $userPost->user->id : 'anonymous-' . $loop->index }}"
                                            @if($userPost->trashed()) data-ban-reason="{{ $userPost->getBanReason() ?? 'No reason provided' }}" @endif
                                            @if(!$loop->first) hidden @endif
                                            x-data="{
                                                actionsOpen: false,
                                                reportOpen: false,
                                                editOpen: false,
                                                reportReason: '',
                                                reportDetails: '',
                                                saved: @js((bool) $saved),
                                                openReportModal() {
                                                    this.actionsOpen = false;
                                                    this.reportOpen = true;
                                                },
                                                closeReportModal() {
                                                    this.reportOpen = false;
                                                },
                                                openEditModal() {
                                                    this.actionsOpen = false;
                                                    this.editOpen = true;
                                                },
                                                closeEditModal() {
                                                    this.editOpen = false;
                                                },
                                                reportDetailsCount() {
                                                    return this.reportDetails.length;
                                                }
                                            }" x-on:click.outside="actionsOpen = false"
                                        >
                                            <header class="aud-depo-panel-head">
                                                @if($avatarUrl)
                                                    <img src="{{ $avatarUrl }}" alt="{{ $displayName }} profile" class="ss-avatar" style="width: 40px; height: 40px; border-radius: 50%; object-fit: cover; flex-shrink: 0;">
                                                @else
                                                    <span
                                                        class="ss-avatar is-initial"
                                                        aria-hidden="true"
                                                        style="width: 40px; height: 40px; border-radius: 50%; --ph-hue: {{ $hue }};"
                                                    >
                                                        <span class="ss-avatar-initials" style="font-size: 13.6px">{{ $initials }}</span>
                                                    </span>
                                                @endif
                                                <div class="aud-depo-id">
                                                    <div class="aud-depo-name-row" style="display: flex; align-items: center; gap: 8px;">
                                                        <h3>{{ $displayName }}</h3>
                                                        @if($userPost->user->isUnsecure())
                                                            <span class="unsecure-badge" title="Unsecure Account" style="display: inline-flex; align-items: center; gap: 4px; padding: 2px 8px; background: color-mix(in srgb, #d97706 15%, transparent); color: #d97706; border: 1px solid color-mix(in srgb, #d97706 30%, transparent); border-radius: 12px; font-size: 11px; font-weight: 600;">
                                                                <x-fas-shield-halved style="width: 12px; height: 12px;" />
                                                                Unsecure
                                                            </span>
                                                        @else
                                                            <svg style="color: var(--accent-color);" class="inline-block size-3" fill="currentColor" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512"><!--! Font Awesome Free 7.2.0 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free (Icons: CC BY 4.0, Fonts: SIL OFL 1.1, Code: MIT License) Copyright 2026 Fonticons, Inc. --><path fill="currentColor" d="M256 512a256 256 0 1 1 0-512 256 256 0 1 1 0 512zM374 145.7c-10.7-7.8-25.7-5.4-33.5 5.3L221.1 315.2 169 263.1c-9.4-9.4-24.6-9.4-33.9 0s-9.4 24.6 0 33.9l72 72c5 5 11.8 7.5 18.8 7s13.4-4.1 17.5-9.8L379.3 179.2c7.8-10.7 5.4-25.7-5.3-33.5z"></path></svg>
                                                        @endif
                                                    </div>
                                                    <p class="aud-depo-date ss-mono">{{ $userPost->created_at->diffForHumans() }}</p>
                                                </div>

                                                 <div class="relative shrink-0">
                                                    <button type="button"
                                                        class="flex size-9 shrink-0 items-center justify-center rounded-full border [border-color:var(--line)] [background:var(--paper)] [color:color-mix(in_srgb,var(--text-color,#0d1b2a)_62%,transparent)] transition-all hover:[border-color:var(--accent-line)] hover:[background:var(--accent-wash)] hover:[color:var(--accent-ink)]"
                                                        aria-label="More options" aria-haspopup="menu" x-on:click.stop="actionsOpen = ! actionsOpen"
                                                        x-on:keydown.escape.window="actionsOpen = false" x-bind:aria-expanded="actionsOpen.toString()">
                                                        <x-fas-ellipsis class="size-4" aria-hidden="true" />
                                                    </button>

                                                    <div x-cloak x-show="actionsOpen" x-transition:enter="transition ease-out duration-200 origin-top-right"
                                                        x-transition:enter-start="opacity-0 -translate-y-1 scale-95"
                                                        x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                                                        x-transition:leave="transition ease-in duration-150 origin-top-right"
                                                        x-transition:leave-start="opacity-100 translate-y-0 scale-100"
                                                        x-transition:leave-end="opacity-0 -translate-y-1 scale-95"
                                                        class="absolute right-0 top-11 z-20 w-40 overflow-hidden rounded-lg border p-2 text-sm font-bold [border-color:color-mix(in_srgb,var(--accent-color,#6c5ce7)_20%,var(--background-color,#ffffff))] [background:var(--background-color,#ffffff)] [box-shadow:0_16px_36px_color-mix(in_srgb,var(--text-color,#0d1b2a)_18%,transparent)]"
                                                        role="menu">
                                                        @auth
                                                            @if ($post->id)
                                                                @if (Auth::user()?->role !== 'admin' && Auth::id() !== $userPost->user_id)
                                                                    <div
                                                                        class=" ">
                                                                        <button type="button"
                                                                            class="flex min-h-9 w-full items-center gap-2 rounded-lg px-2.5 py-1.5 text-left transition-all duration-[180ms] [color:color-mix(in_srgb,var(--text-color,#0d1b2a)_78%,transparent)] hover:translate-x-0.5 hover:[background:color-mix(in_srgb,var(--accent-color,#6c5ce7)_12%,transparent)] hover:[color:var(--accent-color,#6c5ce7)] focus:outline-none focus-visible:translate-x-0.5 focus-visible:ring-2 focus-visible:[--tw-ring-color:color-mix(in_srgb,var(--accent-color,#6c5ce7)_35%,transparent)]"
                                                                            role="menuitem"
                                                                            x-on:click="openReportModal()">
                                                                            <x-fas-flag class="size-3" aria-hidden="true" />
                                                                            <span>Report</span>
                                                                        </button>
                                                                    </div>
                                                                @endif

                                                                @if (Auth::id() === $userPost->user_id)
                                                                    <button type="button"
                                                                        class="mt-1 border-t pt-1 flex min-h-9 w-full items-center gap-2 rounded-lg px-2.5 py-1.5 text-left transition-all duration-[180ms] [border-color:color-mix(in_srgb,var(--text-color,#0d1b2a)_10%,transparent)] [color:color-mix(in_srgb,var(--text-color,#0d1b2a)_78%,transparent)] hover:translate-x-0.5 hover:[background:color-mix(in_srgb,var(--accent-color,#6c5ce7)_12%,transparent)] hover:[color:var(--accent-color,#6c5ce7)] focus:outline-none focus-visible:translate-x-0.5 focus-visible:ring-2 focus-visible:[--tw-ring-color:color-mix(in_srgb,var(--accent-color,#6c5ce7)_35%,transparent)]"
                                                                        role="menuitem"
                                                                        x-on:click="openEditModal()">
                                                                        <x-fas-pen class="size-3" aria-hidden="true" />
                                                                        <span>Edit</span>
                                                                    </button>

                                                                    <form method="POST" action="{{ route('posts.description.destroy', $post->id) }}"
                                                                        class="mt-1 border-t pt-1 [border-color:color-mix(in_srgb,var(--text-color,#0d1b2a)_10%,transparent)]"
                                                                        x-on:submit.prevent="window.sitesphereSwal.confirm({
                                                                            title: 'Are you sure?',
                                                                            text: 'You want to delete your description?',
                                                                            icon: 'warning',
                                                                            confirmButtonColor: 'var(--ui-danger)',
                                                                            cancelButtonColor: '#6c757d',
                                                                            confirmButtonText: 'Yes, delete it!'
                                                                        }).then((result) => {
                                                                            if (result.isConfirmed) {
                                                                                $el.submit();
                                                                            }
                                                                        })">
                                                                        @csrf
                                                                        @method('DELETE')
                                                                        <button type="submit"
                                                                            class="flex min-h-9 w-full items-center gap-2 rounded-lg px-2.5 py-1.5 text-left transition-all duration-[180ms] [color:var(--ui-danger)] hover:translate-x-0.5 hover:[background:color-mix(in_srgb,var(--ui-danger)_12%,transparent)] focus:outline-none focus-visible:translate-x-0.5 focus-visible:ring-2 focus-visible:[--tw-ring-color:color-mix(in_srgb,var(--ui-danger)_28%,transparent)]"
                                                                            role="menuitem">
                                                                            <x-fas-trash class="size-3" aria-hidden="true" />
                                                                            <span>Delete</span>
                                                                        </button>
                                                                    </form>
                                                                @endif
                                                                @if (Auth::user()?->role === 'admin')
                                                                    @if($userPost->trashed())
                                                                        {{-- Delete Permanently (legacy trashed descriptions) --}}
                                                                        <form method="POST" action="{{ route('audits.force-delete', $userPost->id) }}"
                                                                            class="mt-1 border-t pt-1 [border-color:color-mix(in_srgb,var(--text-color,#0d1b2a)_10%,transparent)]"
                                                                            x-on:submit.prevent="window.sitesphereSwal.confirm({
                                                                                title: 'Delete Permanently?',
                                                                                text: 'This action cannot be undone. The description will be permanently removed.',
                                                                                icon: 'warning',
                                                                                input: 'text',
                                                                                inputPlaceholder: 'Enter reason for deletion...',
                                                                                confirmButtonColor: '#ef4444',
                                                                                cancelButtonColor: '#6c757d',
                                                                                confirmButtonText: 'Yes, delete forever!',
                                                                                inputValidator: (value) => {
                                                                                    if (!value) return 'You need to provide a reason!'
                                                                                }
                                                                            }).then((result) => {
                                                                                if (result.isConfirmed) {
                                                                                    const input = document.createElement('input');
                                                                                    input.type = 'hidden';
                                                                                    input.name = 'reason';
                                                                                    input.value = result.value;
                                                                                    $el.appendChild(input);
                                                                                    $el.submit();
                                                                                }
                                                                            })">
                                                                            @csrf
                                                                            @method('DELETE')
                                                                            <button type="submit"
                                                                                class="flex min-h-9 w-full items-center gap-2 rounded-lg px-2.5 py-1.5 text-left transition-all duration-[180ms] [color:#ef4444] hover:translate-x-0.5 hover:[background:color-mix(in_srgb,#ef4444_12%,transparent)] focus:outline-none"
                                                                                role="menuitem">
                                                                                <x-fas-trash class="size-3" aria-hidden="true" />
                                                                                <span>Delete Permanently</span>
                                                                            </button>
                                                                        </form>
                                                                    @else
                                                                        {{-- Delete Description (permanent) --}}
                                                                        <form method="POST" action="{{ route('audits.delete', $userPost->id) }}"
                                                                            class="mt-1 border-t pt-1 [border-color:color-mix(in_srgb,var(--text-color,#0d1b2a)_10%,transparent)]"
                                                                            x-on:submit.prevent="window.sitesphereSwal.confirm({
                                                                                title: 'Delete this description?',
                                                                                text: 'This will permanently delete this description. This action cannot be undone.',
                                                                                icon: 'warning',
                                                                                input: 'text',
                                                                                inputPlaceholder: 'Enter reason for deletion...',
                                                                                confirmButtonColor: '#ef4444',
                                                                                cancelButtonColor: '#6c757d',
                                                                                confirmButtonText: 'Yes, delete it!',
                                                                                inputValidator: (value) => {
                                                                                    if (!value) return 'You need to provide a reason!'
                                                                                }
                                                                            }).then((result) => {
                                                                                if (result.isConfirmed) {
                                                                                    const input = document.createElement('input');
                                                                                    input.type = 'hidden';
                                                                                    input.name = 'reason';
                                                                                    input.value = result.value;
                                                                                    $el.appendChild(input);
                                                                                    $el.submit();
                                                                                }
                                                                            })">
                                                                            @csrf
                                                                            @method('DELETE')
                                                                            <button type="submit"
                                                                                class="flex min-h-9 w-full items-center gap-2 rounded-lg px-2.5 py-1.5 text-left transition-all duration-[180ms] [color:#ef4444] hover:translate-x-0.5 hover:[background:color-mix(in_srgb,#ef4444_12%,transparent)] focus:outline-none"
                                                                                role="menuitem">
                                                                                <x-fas-trash class="size-3" aria-hidden="true" />
                                                                                <span>Delete</span>
                                                                            </button>
                                                                        </form>
                                                                    @endif
                                                                @endif
                                                            @endif
                                                        @endauth

                                                        @guest
                                                            <a href="{{ route('login') }}"
                                                                class="mt-1 flex min-h-9 w-full items-center gap-2 rounded-lg border-t px-2.5 py-1.5 pt-2 text-left transition-all duration-[180ms] [border-color:color-mix(in_srgb,var(--text-color,#0d1b2a)_10%,transparent)] [color:color-mix(in_srgb,var(--text-color,#0d1b2a)_78%,transparent)] hover:translate-x-0.5 hover:[background:color-mix(in_srgb,var(--accent-color,#6c5ce7)_12%,transparent)] hover:[color:var(--accent-color,#6c5ce7)] focus:outline-none focus-visible:translate-x-0.5 focus-visible:ring-2 focus-visible:[--tw-ring-color:color-mix(in_srgb,var(--accent-color,#6c5ce7)_35%,transparent)]"
                                                                role="menuitem">
                                                                <x-fas-flag class="size-3" aria-hidden="true" />
                                                                <span>Report</span>
                                                            </a>
                                                        @endguest
                                                    </div>
                                                </div>
                                            </header>

                                            <x-layout.report-modal :post-id="$post->id" :modal-id="'audit-' . $userPost->id" :action="route('user-posts.report', $userPost->id)" />

                                            @if (Auth::id() === $userPost->user_id)
                                                <template x-teleport="body">
                                                    <div x-cloak x-show="editOpen" x-transition.opacity.duration.200ms
                                                        class="fixed inset-0 z-[100000] flex items-center justify-center bg-black/45 p-4 backdrop-blur-md"
                                                        role="presentation" x-on:click.self="closeEditModal()"
                                                        x-on:keydown.escape.window="closeEditModal()">
                                                        <form method="POST" action="{{ route('posts.description.update', $post->id) }}"
                                                            class="flex max-h-[85vh] w-full max-w-xl flex-col overflow-hidden rounded-3xl border [border-color:color-mix(in_srgb,var(--text-color,#0d1b2a)_8%,transparent)] [background:var(--background-color,#ffffff)] [color:var(--text-color,#0d1b2a)] [box-shadow:0_30px_60px_-15px_color-mix(in_srgb,var(--text-color,#0d1b2a)_28%,transparent)]"
                                                            x-on:click.stop>
                                                            @csrf
                                                            @method('PATCH')

                                                            <div class="flex items-start justify-between gap-4 px-7 pb-2 pt-7">
                                                                <div class="min-w-0">
                                                                    <h3 class="text-[22px] font-bold leading-tight tracking-normal [color:var(--text-color,#0d1b2a)]">
                                                                        Edit Description
                                                                    </h3>
                                                                </div>
                                                                <button type="button"
                                                                    class="flex size-9 shrink-0 items-center justify-center rounded-full transition [color:color-mix(in_srgb,var(--text-color,#0d1b2a)_62%,transparent)] hover:[background:color-mix(in_srgb,var(--background-color,#ffffff)_86%,var(--accent-color,#6c5ce7)_14%)] hover:[color:var(--text-color,#0d1b2a)] focus:outline-none focus-visible:ring-2 focus-visible:[--tw-ring-color:color-mix(in_srgb,var(--accent-color,#6c5ce7)_32%,transparent)]"
                                                                    aria-label="Close edit dialog" x-on:click="closeEditModal()">
                                                                    <x-fas-xmark class="size-4" aria-hidden="true" />
                                                                </button>
                                                            </div>

                                                            <div class="flex-1 space-y-6 overflow-y-auto px-7 py-4">
                                                                <div class="space-y-2">
                                                                    <textarea name="description" required rows="6" maxlength="5000"
                                                                        class="w-full resize-none rounded-xl border px-3.5 py-3 text-sm leading-6 [border-color:color-mix(in_srgb,var(--text-color,#0d1b2a)_14%,transparent)] [background:var(--background-color,#ffffff)] [color:var(--text-color,#0d1b2a)] placeholder:[color:color-mix(in_srgb,var(--text-color,#0d1b2a)_42%,transparent)] focus:outline-none focus:ring-4 focus:[border-color:var(--accent-color,#6c5ce7)] focus:[--tw-ring-color:color-mix(in_srgb,var(--accent-color,#6c5ce7)_20%,transparent)]"
                                                                        placeholder="Update your description">{{ $userPost->description }}</textarea>
                                                                </div>
                                                            </div>

                                                            <div class="flex justify-end gap-3 border-t px-7 py-5 [border-color:color-mix(in_srgb,var(--text-color,#0d1b2a)_10%,transparent)] [background:color-mix(in_srgb,var(--background-color,#ffffff)_92%,var(--text-color,#0d1b2a)_8%)]">
                                                                <button type="button"
                                                                    class="inline-flex min-h-11 items-center justify-center rounded-xl border px-5 text-sm font-bold transition [border-color:color-mix(in_srgb,var(--text-color,#0d1b2a)_14%,transparent)] [background:var(--background-color,#ffffff)] [color:color-mix(in_srgb,var(--text-color,#0d1b2a)_68%,transparent)] hover:[background:color-mix(in_srgb,var(--background-color,#ffffff)_88%,var(--text-color,#0d1b2a)_12%)]"
                                                                    x-on:click="closeEditModal()">
                                                                    Cancel
                                                                </button>
                                                                <button type="submit"
                                                                    class="inline-flex min-h-11 items-center justify-center gap-2 rounded-xl px-5 text-sm font-bold text-white transition [background:var(--accent-color,#6c5ce7)] [box-shadow:0_4px_12px_color-mix(in_srgb,var(--accent-color,#6c5ce7)_20%,transparent)]">
                                                                    <x-fas-save class="size-3" aria-hidden="true" />
                                                                    <span>Save Changes</span>
                                                                </button>
                                                            </div>
                                                        </form>
                                                    </div>
                                                </template>
                                            @endif

                                            <div class="ss-expandable aud-depo-body" data-clamp="5">
                                                <p class="ss-expandable-text" style="--clamp-lines: 5">
                                                    {!! nl2br(e($userPost->description)) !!}
                                                </p>
                                                <p class="ss-expandable-ghost" aria-hidden="true">
                                                    {!! nl2br(e($userPost->description)) !!}
                                                </p>
                                                <button type="button" class="ss-expand-btn" hidden>See more</button>
                                            </div>

                                            <div class="aud-depo-foot">
                                                <div class="aud-depo-tags"></div>
                                            </div>
                                        </article>
                                    @endforeach
                                </div>
                            </div>
                        </section>
                    @endif

                    <x-layout.comments
                        :post="$post"
                        :comments="$comments"
                        :comment-user-ratings="$commentUserRatings"
                        :user-rating="$userRating"
                        :user-has-commented="$userHasCommented"
                    />

                    <!-- =================================================================
                         LINKED RECORDS  — horizontal scroll carousel
                    ================================================================= -->
                    @if($relatedPosts->isNotEmpty())
                        <section class="aud-related" aria-label="Linked records">
                            <div class="aud-related-body">
                                <div class="aud-related-header">
                                    <h2 class="aud-h2">Linked records</h2>
                                    <div class="aud-related-arrows">
                                        <button
                                            type="button"
                                            class="aud-related-arrow is-disabled"
                                            id="relPrev"
                                            aria-label="Scroll left"
                                            disabled
                                        >
                                            <x-fas-chevron-left class="size-4" aria-hidden="true" />
                                        </button>
                                        <button
                                            type="button"
                                            class="aud-related-arrow"
                                            id="relNext"
                                            aria-label="Scroll right"
                                        >
                                            <x-fas-chevron-right class="size-4" aria-hidden="true" />
                                        </button>
                                    </div>
                                </div>

                                <div class="aud-related-grid" id="relatedGrid">
                                    @foreach($relatedPosts as $related)
                                        @php
                                            $rHost = parse_url($related->url, PHP_URL_HOST) ?? $related->url;
                                            $mark = Str::upper(Str::substr($related->title, 0, 1));
                                            $rAvg = round((float) ($related->average_rating ?? 0), 1);
                                            $rCount = (int) ($related->audits_count ?? 0);
                                        @endphp
                                        <a class="aud-related-card" href="{{ route('posts.show', $related->slug) }}">
                                            <span class="aud-related-mark">{{ $mark }}</span>
                                            <span class="aud-related-text">
                                                <span class="aud-related-domain ss-mono">{{ $rHost }}</span>
                                                <span class="aud-related-title">{{ $related->title }}</span>
                                                <span class="aud-related-meta">
                                                    @if($rAvg > 0)
                                                        <span
                                                            class="ss-stars"
                                                            role="img"
                                                            aria-label="{{ $rAvg }} out of 5"
                                                            style="--sz: 12px; --gap: 2px; --pct: {{ ($rAvg / 5) * 100 }}%"
                                                        >
                                                            <span class="ss-stars-track">
                                                                @for($i = 0; $i < 5; $i++)
                                                                    <svg viewBox="0 0 24 24" width="12" height="12">
                                                                        <path d="M12 2.4l2.95 5.98 6.6.96-4.78 4.66 1.13 6.57L12 17.5l-5.9 3.07 1.13-6.57L2.45 9.34l6.6-.96L12 2.4z" />
                                                                    </svg>
                                                                @endfor
                                                            </span>
                                                            <span class="ss-stars-fill">
                                                                @for($i = 0; $i < 5; $i++)
                                                                    <svg viewBox="0 0 24 24" width="12" height="12">
                                                                        <path d="M12 2.4l2.95 5.98 6.6.96-4.78 4.66 1.13 6.57L12 17.5l-5.9 3.07 1.13-6.57L2.45 9.34l6.6-.96L12 2.4z" />
                                                                    </svg>
                                                                @endfor
                                                            </span>
                                                        </span>
                                                        ·
                                                    @endif
                                                    {{ $rCount }} review{{ $rCount != 1 ? 's' : '' }}
                                                </span>
                                            </span>
                                        </a>
                                    @endforeach
                                </div>
                            </div>
                        </section>
                    @endif
                </div>
            </div>
        </main>
    </div>
@endsection

@push('scripts')
    @vite('resources/js/post-detail.js')
@endpush
