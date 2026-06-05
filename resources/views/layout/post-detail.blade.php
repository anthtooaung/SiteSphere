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

                            <div class="aud-menu-wrap">
                                <button
                                    type="button"
                                    class="aud-menu-btn"
                                    id="menuBtn"
                                    aria-label="More options"
                                    aria-expanded="false"
                                    aria-controls="menuDropdown"
                                >
                                    <svg
                                        viewBox="0 0 24 24"
                                        width="20"
                                        height="20"
                                        fill="currentColor"
                                        aria-hidden="true"
                                    >
                                        <circle cx="12" cy="5" r="1.5" />
                                        <circle cx="12" cy="12" r="1.5" />
                                        <circle cx="12" cy="19" r="1.5" />
                                    </svg>
                                </button>

                                <div class="aud-menu-dropdown" id="menuDropdown" role="menu">
                                    <button type="button" class="aud-menu-item" role="menuitem">
                                        <svg
                                            viewBox="0 0 24 24"
                                            width="15"
                                            height="15"
                                            fill="none"
                                            stroke="currentColor"
                                            stroke-width="2"
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            aria-hidden="true"
                                        >
                                            <path d="M19 21l-7-5-7 5V5a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2z" />
                                        </svg>
                                        Bookmark
                                    </button>
                                    <button
                                        type="button"
                                        class="aud-menu-item is-danger"
                                        role="menuitem"
                                    >
                                        <svg
                                            viewBox="0 0 24 24"
                                            width="15"
                                            height="15"
                                            fill="none"
                                            stroke="currentColor"
                                            stroke-width="2"
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            aria-hidden="true"
                                        >
                                            <circle cx="12" cy="12" r="10" />
                                            <line x1="12" y1="8" x2="12" y2="12" />
                                            <line x1="12" y1="16" x2="12.01" y2="16" />
                                        </svg>
                                        Report
                                    </button>
                                </div>
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
                                    <svg
                                        viewBox="0 0 24 24"
                                        width="15"
                                        height="15"
                                        fill="none"
                                        stroke="currentColor"
                                        stroke-width="2.2"
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                    >
                                        <path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71" />
                                        <path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71" />
                                    </svg>
                                </span>
                                <span class="aud-domain-text">{{ $host }}</span>
                                <span class="aud-domain-ext" aria-hidden="true">
                                    <svg
                                        viewBox="0 0 24 24"
                                        width="13"
                                        height="13"
                                        fill="none"
                                        stroke="currentColor"
                                        stroke-width="2.2"
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                    >
                                        <path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6" />
                                        <polyline points="15 3 21 3 21 9" />
                                        <line x1="10" y1="14" x2="21" y2="3" />
                                    </svg>
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
                                    <h2 class="aud-h2">Detailed Reports</h2>
                                    <p class="aud-sub">Expert audits and technical reviews from verified contributors.</p>
                                </div>
                            </div>

                            <div class="aud-depo-grid">
                                <!-- ---- Sidebar tab nav ---- -->
                                <nav class="aud-depo-index" aria-label="Auditors" id="depoNav">
                                    @foreach($post->userPosts as $userPost)
                                        @php
                                            $isProfileVisible = (bool) ($userPost->user->settings?->user_post_visible);
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
                                            $isProfileVisible = (bool) ($userPost->user->settings?->user_post_visible);
                                            $displayName = $isProfileVisible ? $userPost->user->name : 'Anonymous';
                                            $initials = $isProfileVisible 
                                                ? collect(explode(' ', $userPost->user->name))->map(fn($n) => Str::substr($n, 0, 1))->join('')
                                                : '?';
                                            $hue = $isProfileVisible ? (($userPost->user->id * 47) % 360) : 222;
                                            $avatarUrl = $isProfileVisible ? $userPost->user->getAvatarUrl() : '';
                                            $bio = $isProfileVisible ? ($userPost->user->user_bio ?? 'Expert Contributor') : 'Expert Contributor';
                                        @endphp
                                        <article
                                            class="aud-depo-panel"
                                            id="panel-user-{{ $userPost->user->id }}"
                                            data-panel="user-{{ $userPost->user->id }}"
                                            @if(!$loop->first) hidden @endif
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
                                                    <p class="aud-depo-role">{{ $bio }}</p>
                                                    <p class="aud-depo-date ss-mono">{{ $userPost->created_at->diffForHumans() }}</p>
                                                </div>
                                            </header>

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
                                            <svg
                                                viewBox="0 0 24 24"
                                                width="16"
                                                height="16"
                                                fill="none"
                                                stroke="currentColor"
                                                stroke-width="2.5"
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                aria-hidden="true"
                                            >
                                                <polyline points="15 18 9 12 15 6" />
                                            </svg>
                                        </button>
                                        <button
                                            type="button"
                                            class="aud-related-arrow"
                                            id="relNext"
                                            aria-label="Scroll right"
                                        >
                                            <svg
                                                viewBox="0 0 24 24"
                                                width="16"
                                                height="16"
                                                fill="none"
                                                stroke="currentColor"
                                                stroke-width="2.5"
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                aria-hidden="true"
                                            >
                                                <polyline points="9 18 15 12 9 6" />
                                            </svg>
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
