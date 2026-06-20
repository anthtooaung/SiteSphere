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

                                            @if (Auth::user()?->role === 'admin')
                                                <form method="POST" action="{{ route('posts.ban', $post->id) }}"
                                                    class="mt-1 border-t pt-1 [border-color:color-mix(in_srgb,var(--text-color,#0d1b2a)_10%,transparent)]"
                                                    x-on:submit.prevent="window.sitesphereSwal.confirm({
                                                        title: 'Are you sure?',
                                                        text: 'You want to ban and soft delete this post? This action will also hide all audit descriptions.',
                                                        icon: 'warning',
                                                        confirmButtonColor: '#b91c1c',
                                                        cancelButtonColor: '#6c757d',
                                                        confirmButtonText: 'Yes, ban it!'
                                                    }).then((result) => {
                                                        if (result.isConfirmed) {
                                                            $el.submit();
                                                        }
                                                    })">
                                                    @csrf
                                                    <button type="submit"
                                                        class="flex min-h-9 w-full items-center gap-2 rounded-lg px-2.5 py-1.5 text-left transition-all duration-[180ms] [color:#b91c1c] hover:translate-x-0.5 hover:[background:color-mix(in_srgb,#b91c1c_12%,transparent)] focus:outline-none focus-visible:translate-x-0.5 focus-visible:ring-2 focus-visible:[--tw-ring-color:color-mix(in_srgb,#b91c1c_28%,transparent)]"
                                                        role="menuitem">
                                                        <x-fas-ban class="size-3" aria-hidden="true" />
                                                        <span>Ban Post</span>
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
                            </div>

                            <x-layout.report-modal :post-id="$post->id" />
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
                                                <span class="aud-depo-tab-name">{{ $displayName }}</span>
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
                                            class="aud-depo-panel"
                                            id="panel-user-{{ $isProfileVisible ? $userPost->user->id : 'anonymous-' . $loop->index }}"
                                            data-panel="user-{{ $isProfileVisible ? $userPost->user->id : 'anonymous-' . $loop->index }}"
                                            @if(!$loop->first) hidden @endif
                                            x-data="{
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
                                            }" x-on:click.outside="actionsOpen = false"
                                        >
                                            <header class="aud-depo-panel-head">
                                                @if($avatarUrl)
                                                    <img src="{{ $avatarUrl }}" alt="{{ $displayName }} profile" class="ss-avatar" style="width: 62px; height: 62px; border-radius: 50%; object-fit: cover; flex-shrink: 0;">
                                                @else
                                                    <span
                                                        class="ss-avatar is-initial"
                                                        aria-hidden="true"
                                                        style="width: 62px; height: 62px; border-radius: 50%; --ph-hue: {{ $hue }};"
                                                    >
                                                        <span class="ss-avatar-initials" style="font-size: 21.1px">{{ $initials }}</span>
                                                    </span>
                                                @endif
                                                <div class="aud-depo-id">
                                                    <div class="aud-depo-name-row">
                                                        <h3>{{ $displayName }}</h3>
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

                                                                @if (Auth::id() === $userPost->user_id)
                                                                    <a href="{{ route('user-posts.edit', $userPost->id) }}"
                                                                        class="mt-1 border-t pt-1 flex min-h-9 w-full items-center gap-2 rounded-lg px-2.5 py-1.5 text-left transition-all duration-[180ms] [border-color:color-mix(in_srgb,var(--text-color,#0d1b2a)_10%,transparent)] [color:color-mix(in_srgb,var(--text-color,#0d1b2a)_78%,transparent)] hover:translate-x-0.5 hover:[background:color-mix(in_srgb,var(--accent-color,#6c5ce7)_12%,transparent)] hover:[color:var(--accent-color,#6c5ce7)] focus:outline-none focus-visible:translate-x-0.5 focus-visible:ring-2 focus-visible:[--tw-ring-color:color-mix(in_srgb,var(--accent-color,#6c5ce7)_35%,transparent)]"
                                                                        role="menuitem">
                                                                        <x-fas-pen class="size-3" aria-hidden="true" />
                                                                        <span>Edit Description</span>
                                                                    </a>

                                                                    <form method="POST" action="{{ route('user-posts.destroy', $userPost->id) }}"
                                                                        class="mt-1 border-t pt-1 [border-color:color-mix(in_srgb,var(--text-color,#0d1b2a)_10%,transparent)]"
                                                                        x-on:submit.prevent="window.sitesphereSwal.confirm({
                                                                            title: 'Are you sure?',
                                                                            text: 'You want to delete your description?',
                                                                            icon: 'warning',
                                                                            confirmButtonColor: '#d33',
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
                                                                            class="flex min-h-9 w-full items-center gap-2 rounded-lg px-2.5 py-1.5 text-left transition-all duration-[180ms] [color:#b91c1c] hover:translate-x-0.5 hover:[background:color-mix(in_srgb,#b91c1c_12%,transparent)] focus:outline-none focus-visible:translate-x-0.5 focus-visible:ring-2 focus-visible:[--tw-ring-color:color-mix(in_srgb,#b91c1c_28%,transparent)]"
                                                                            role="menuitem">
                                                                            <x-fas-trash class="size-3" aria-hidden="true" />
                                                                            <span>Delete Description</span>
                                                                        </button>
                                                                    </form>
                                                                @endif
                                                                @if (Auth::user()?->role === 'admin')
                                                                    <form method="POST" action="{{ route('audits.ban', $userPost->id) }}"
                                                                        class="mt-1 border-t pt-1 [border-color:color-mix(in_srgb,var(--text-color,#0d1b2a)_10%,transparent)]"
                                                                        x-on:submit.prevent="window.sitesphereSwal.confirm({
                                                                            title: 'Are you sure?',
                                                                            text: 'You want to hide this audit description?',
                                                                            icon: 'warning',
                                                                            confirmButtonColor: '#b91c1c',
                                                                            cancelButtonColor: '#6c757d',
                                                                            confirmButtonText: 'Yes, hide it!'
                                                                        }).then((result) => {
                                                                            if (result.isConfirmed) {
                                                                                $el.submit();
                                                                            }
                                                                        })">
                                                                        @csrf
                                                                        <button type="submit"
                                                                            class="flex min-h-9 w-full items-center gap-2 rounded-lg px-2.5 py-1.5 text-left transition-all duration-[180ms] [color:#b91c1c] hover:translate-x-0.5 hover:[background:color-mix(in_srgb,#b91c1c_12%,transparent)] focus:outline-none focus-visible:translate-x-0.5 focus-visible:ring-2 focus-visible:[--tw-ring-color:color-mix(in_srgb,#b91c1c_28%,transparent)]"
                                                                            role="menuitem">
                                                                            <x-fas-ban class="size-3" aria-hidden="true" />
                                                                            <span>Ban Audit</span>
                                                                        </button>
                                                                    </form>
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

                                            <x-layout.report-modal :post-id="$post->id" :modal-id="'audit-' . $userPost->id" />

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
