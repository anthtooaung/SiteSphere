@props([
    'postId' => null,
    'title',
    'url',
    'category' => 'Uncategorized',
    'tags' => [],
    'profiles' => [],
    'averageRating' => 0,
    'ratingsCount' => 0,
    'commentsCount' => 0,
    'saved' => false,
    'slug' => null,
    'isUnsecure' => false,
])

<article
    {{ $attributes->merge(['class' => 'review-card !p-0 flex flex-col h-full w-full max-w-md overflow-hidden rounded-[8px] border [border-color:color-mix(in_srgb,var(--text-color,#182230)_10%,transparent)] [background:var(--background-color,#ffffff)] [color:var(--text-color,#0d1b2a)] [box-shadow:0_4px_14px_rgba(15,23,42,0.06)]']) }}
    x-data="{
        profiles: {{ \Illuminate\Support\Js::from(collect($profiles)->sortBy(fn($p) => $p['username'] !== 'Test User')->values()->toArray()) }},
        activeProfile: 0,
        saved: @js((bool) $saved),
        actionsOpen: false,
        reportOpen: false,
        reportReason: '',
        reportDetails: '',
        reviewOpen: false,
        selectedRating: {{ (int) round((float) $averageRating) }},
        configuredAverageRating: {{ \Illuminate\Support\Js::from((float) $averageRating) }},
        configuredRatingsCount: {{ \Illuminate\Support\Js::from((int) $ratingsCount) }},
        configuredCommentsCount: {{ \Illuminate\Support\Js::from((int) $commentsCount) }},
        get currentProfile() {
            return this.profiles[this.activeProfile] || this.profiles[0] || {
                username: '',
                initial: '?',
                time: '',
                description: '',
                avatar: ''
            };
        },
        averageRating() {
            return Number(this.configuredAverageRating || 0).toFixed(1);
        },
        ratingsTotal() {
            return this.configuredRatingsCount;
        },
        commentsTotal() {
            return this.configuredCommentsCount;
        },
        switchProfile(index) {
            this.activeProfile = index;
        },
        scrollProfiles(direction) {
            this.$refs.profileTabs?.scrollBy({
                left: direction * 120,
                behavior: 'smooth'
            });
        },
        scrollTags(direction) {
            this.$refs.tagScroller?.scrollBy({
                left: direction * 120,
                behavior: 'smooth'
            });
        },
        setRating(rating) {
            this.selectedRating = rating;
        },
        openReportModal() {
            this.actionsOpen = false;
            this.reportOpen = true;
        },
        closeReportModal() {
            this.reportOpen = false;
        },
        reportDetailsCount() {
            return this.reportDetails.length;
        },
        authAlert(action) {
            if (!window.sitesphereSwal) {
                window.location.href = '{{ route('login') }}';
                return;
            }

            window.sitesphereSwal.fire({
                title: 'Authentication Required',
                text: `Please log in to ${action} this content.`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Log In',
                cancelButtonText: 'Cancel',
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = '{{ route('login') }}';
                }
            });
        }
    }" x-on:click.outside="if (! reportOpen) { reviewOpen = false; actionsOpen = false }">
    <header class="space-y-3 px-4 pb-3 pt-4 sm:px-5">
        <div class="flex items-start justify-between gap-3">
            <div class="min-w-0 flex-1">
                <h2 class="line-clamp-2 min-h-[44px] overflow-hidden break-words text-[16px] font-extrabold leading-[1.35] tracking-normal [color:var(--text-color,#0d1b2a)]"
                    title="{{ $title }}" data-post-card-title>
                    @if($slug)
                        <a href="{{ route('posts.show', $slug) }}" class="hover:underline hover:[color:var(--accent-color)] transition-colors">
                            {{ $title }}
                        </a>
                    @else
                        {{ $title }}
                    @endif
                </h2>
            </div>

            @if ($isUnsecure)
                <div class="shrink-0 flex items-center pt-0.5">
                    <span class="unsecure-badge" title="Unsecure Post" style="display: inline-flex; align-items: center; gap: 4px; padding: 2px 6px; background: color-mix(in srgb, #d97706 15%, transparent); color: #d97706; border: 1px solid color-mix(in srgb, #d97706 30%, transparent); border-radius: 10px; font-size: 10px; font-weight: 600; vertical-align: middle;">
                        <x-fas-shield-halved style="width: 10px; height: 10px;" />
                        Unsecure
                    </span>
                </div>
            @endif

            <div class="relative shrink-0">
                <button type="button"
                    class="flex size-8 shrink-0 items-center justify-center [color:color-mix(in_srgb,var(--text-color,#0d1b2a)_62%,transparent)] transition-all hover:[background:color-mix(in_srgb,var(--background-color,#ffffff)_84%,var(--accent-color,#6c5ce7)_16%)] hover:[color:var(--accent-color,#6c5ce7)]"
                    aria-label="Post actions" aria-haspopup="menu" x-on:click.stop="actionsOpen = ! actionsOpen"
                    x-on:keydown.escape.window="actionsOpen = false" x-bind:aria-expanded="actionsOpen.toString()"
                    data-post-card-actions-button>
                    <x-fas-ellipsis class="size-4" aria-hidden="true" />
                </button>

                <div x-cloak x-show="actionsOpen" x-transition:enter="transition ease-out duration-200 origin-top-right"
                    x-transition:enter-start="opacity-0 -translate-y-1 scale-95"
                    x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                    x-transition:leave="transition ease-in duration-150 origin-top-right"
                    x-transition:leave-start="opacity-100 translate-y-0 scale-100"
                    x-transition:leave-end="opacity-0 -translate-y-1 scale-95"
                    class="absolute right-0 top-10 z-20 w-40 overflow-hidden rounded-lg border p-2 text-sm font-bold [border-color:color-mix(in_srgb,var(--accent-color,#6c5ce7)_20%,var(--background-color,#ffffff))] [background:var(--background-color,#ffffff)] [box-shadow:0_16px_36px_color-mix(in_srgb,var(--text-color,#0d1b2a)_18%,transparent)]"
                    role="menu" data-post-card-actions-menu>
                    @auth
                        @if ($postId)
                            <form method="POST" action="{{ route('posts.bookmark', $postId) }}">
                                @csrf
                                <button type="submit"
                                    class="flex min-h-9 w-full items-center gap-2 rounded-lg px-2.5 py-1.5 text-left transition-all duration-[180ms] [color:color-mix(in_srgb,var(--text-color,#0d1b2a)_78%,transparent)] hover:translate-x-0.5 hover:[background:color-mix(in_srgb,var(--accent-color,#6c5ce7)_12%,transparent)] hover:[color:var(--accent-color,#6c5ce7)] focus:outline-none focus-visible:translate-x-0.5 focus-visible:ring-2 focus-visible:[--tw-ring-color:color-mix(in_srgb,var(--accent-color,#6c5ce7)_35%,transparent)]"
                                    role="menuitem" data-post-card-action="bookmark">
                                    <x-fas-bookmark x-show="saved" class="size-3 [color:var(--accent-color,#6c5ce7)]" />
                                    <x-far-bookmark x-show="! saved" class="size-3" />
                                    <span
                                        x-text="saved ? 'Unsave Post' : 'Save Post'">{{ $saved ? 'Unsave Post' : 'Save Post' }}</span>
                                </button>
                            </form>

                            @if (Auth::user()?->role !== 'admin' && !collect($profiles)->contains('user_id', Auth::id()))
                                <div
                                    class="mt-1 border-t pt-1 [border-color:color-mix(in_srgb,var(--text-color,#0d1b2a)_10%,transparent)]">
                                    <button type="button"
                                        class="flex min-h-9 w-full items-center gap-2 rounded-lg px-2.5 py-1.5 text-left transition-all duration-[180ms] [color:color-mix(in_srgb,var(--text-color,#0d1b2a)_78%,transparent)] hover:translate-x-0.5 hover:[background:color-mix(in_srgb,var(--accent-color,#6c5ce7)_12%,transparent)] hover:[color:var(--accent-color,#6c5ce7)] focus:outline-none focus-visible:translate-x-0.5 focus-visible:ring-2 focus-visible:[--tw-ring-color:color-mix(in_srgb,var(--accent-color,#6c5ce7)_35%,transparent)]"
                                        role="menuitem" data-post-card-action="report" data-post-card-report-trigger
                                        x-on:click="openReportModal()">
                                        <x-far-flag class="size-3" aria-hidden="true" />
                                        <span>Report</span>
                                    </button>
                                </div>
                            @endif

                            @if (Auth::user()?->role === 'admin')
                                <form method="POST" action="{{ route('posts.toggle-unsecure', $postId) }}"
                                    class="mt-1 border-t pt-1 [border-color:color-mix(in_srgb,var(--text-color,#0d1b2a)_10%,transparent)]">
                                    @csrf
                                    <button type="submit"
                                        class="flex min-h-9 w-full items-center gap-2 rounded-lg px-2.5 py-1.5 text-left transition-all duration-[180ms] {{ $isUnsecure ? 'text-green-600 hover:[background:color-mix(in_srgb,#16a34a_12%,transparent)]' : '[color:#d97706] hover:[background:color-mix(in_srgb,#d97706_12%,transparent)]' }} focus:outline-none focus-visible:translate-x-0.5 focus-visible:ring-2"
                                        role="menuitem" data-post-card-action="toggle-unsecure">
                                        <x-fas-shield-halved class="size-3" aria-hidden="true" />
                                        <span>{{ $isUnsecure ? 'Mark Secure' : 'Mark Unsecure' }}</span>
                                    </button>
                                </form>
                            @endif
                        @else
                            <button type="button"
                                class="flex min-h-9 w-full items-center gap-2 rounded-lg px-2.5 py-1.5 text-left transition-all duration-[180ms] [color:color-mix(in_srgb,var(--text-color,#0d1b2a)_78%,transparent)]"
                                role="menuitem" data-post-card-action="bookmark">
                                <x-far-bookmark class="size-3" />
                                <span>Save Post</span>
                            </button>
                            @if (Auth::user()?->role !== 'admin' && !collect($profiles)->contains('user_id', Auth::id()))
                                <button type="button"
                                    class="mt-1 flex min-h-9 w-full items-center gap-2 rounded-lg border-t px-2.5 py-1.5 pt-2 text-left transition-all duration-[180ms] [border-color:color-mix(in_srgb,var(--text-color,#0d1b2a)_10%,transparent)] [color:color-mix(in_srgb,var(--text-color,#0d1b2a)_78%,transparent)]"
                                    role="menuitem" data-post-card-action="report">
                                    <x-fas-flag class="size-3" aria-hidden="true" />
                                    <span>Report</span>
                                </button>
                            @endif>
                        @endif
                    @endauth

                    @guest
                        <button type="button"
                            class="flex min-h-9 w-full items-center gap-2 rounded-lg px-2.5 py-1.5 text-left transition-all duration-[180ms] [color:color-mix(in_srgb,var(--text-color,#0d1b2a)_78%,transparent)] hover:translate-x-0.5 hover:[background:color-mix(in_srgb,var(--accent-color,#6c5ce7)_12%,transparent)] hover:[color:var(--accent-color,#6c5ce7)] focus:outline-none focus-visible:translate-x-0.5 focus-visible:ring-2 focus-visible:[--tw-ring-color:color-mix(in_srgb,var(--accent-color,#6c5ce7)_35%,transparent)]"
                            role="menuitem" data-auth-required="bookmark" data-post-card-action="bookmark"
                            x-on:click.stop="authAlert('save')">
                            <x-far-bookmark class="size-3" />
                            <span>Save Post</span>
                        </button>
                        <button type="button"
                            class="mt-1 flex min-h-9 w-full items-center gap-2 rounded-lg border-t px-2.5 py-1.5 pt-2 text-left transition-all duration-[180ms] [border-color:color-mix(in_srgb,var(--text-color,#0d1b2a)_10%,transparent)] [color:color-mix(in_srgb,var(--text-color,#0d1b2a)_78%,transparent)] hover:translate-x-0.5 hover:[background:color-mix(in_srgb,var(--accent-color,#6c5ce7)_12%,transparent)] hover:[color:var(--accent-color,#6c5ce7)] focus:outline-none focus-visible:translate-x-0.5 focus-visible:ring-2 focus-visible:[--tw-ring-color:color-mix(in_srgb,var(--accent-color,#6c5ce7)_35%,transparent)]"
                            role="menuitem" data-auth-required="report" data-post-card-action="report"
                            x-on:click.stop="authAlert('report')">
                            <x-fas-flag class="size-3" aria-hidden="true" />
                            <span>Report</span>
                        </button>
                    @endguest
                </div>
            </div>
        </div>

        {{-- stars section --}}
        <div
            class="inline-flex items-center gap-1.5 rounded-lg border px-2.5 py-1 [border-color:color-mix(in_srgb,var(--accent-color,#6c5ce7)_24%,var(--background-color,#ffffff))] [background:color-mix(in_srgb,var(--background-color,#ffffff)_88%,var(--accent-color,#6c5ce7)_12%)]"
            role="img" x-bind:aria-label="averageRating() + ' out of 5 stars'">
            <span class="text-[13px] font-extrabold leading-none [color:var(--accent-color,#6c5ce7)]"
                x-text="averageRating()">{{ number_format((float) $averageRating, 1) }}</span>
            <div class="flex items-center gap-0.5 [color:var(--accent-color,#6c5ce7)]" aria-hidden="true">
                @foreach (range(1, 5) as $star)
                    <x-fas-star class="size-2.5"
                        x-bind:class="Number(averageRating()) >= {{ $star }} ? '[color:var(--accent-color,#6c5ce7)]' :
                            '[color:color-mix(in_srgb,var(--text-color,#0d1b2a)_24%,transparent)]'" />
                @endforeach
            </div>
            <span class="text-[10px] font-semibold [color:color-mix(in_srgb,var(--text-color,#0d1b2a)_58%,transparent)]">(<span x-text="ratingsTotal()">{{ $ratingsCount }}</span> ratings)</span>
        </div>

        {{-- link section --}}
        <a href="{{ $url }}" target="_blank" rel="noopener noreferrer"
            class="group flex items-center gap-2 rounded-xl border px-3 py-2 transition-all [border-color:color-mix(in_srgb,var(--accent-color,#6c5ce7)_22%,var(--background-color,#ffffff))] [background:color-mix(in_srgb,var(--background-color,#ffffff)_90%,var(--accent-color,#6c5ce7)_10%)] hover:[border-color:var(--accent-color,#6c5ce7)] hover:[background:color-mix(in_srgb,var(--background-color,#ffffff)_82%,var(--accent-color,#6c5ce7)_18%)]"
            data-post-card-link>
            <span
                class="flex size-6 shrink-0 items-center justify-center rounded-lg [background:color-mix(in_srgb,var(--background-color,#ffffff)_78%,var(--accent-color,#6c5ce7)_22%)] [color:var(--accent-color,#6c5ce7)] shadow-sm">
                <x-fas-link class="size-2.5" />
            </span>
            <span
                class="min-w-0 flex-1 truncate text-xs font-bold [color:color-mix(in_srgb,var(--text-color,#0d1b2a)_82%,transparent)] group-hover:[color:var(--accent-color,#6c5ce7)]"
                data-post-card-url>{{ $url }}</span>
            <x-fas-arrow-up-right-from-square class="size-2.5 opacity-70 [color:var(--accent-color,#6c5ce7)]" />
        </a>

        {{-- tag section --}}
        <div class="grid grid-cols-[auto_minmax(0,1fr)_auto] items-center gap-1">
            <button type="button"
                class="flex size-7 shrink-0 items-center justify-center rounded-md [color:color-mix(in_srgb,var(--text-color,#0d1b2a)_58%,transparent)] focus:outline-none focus:ring-2 focus:[--tw-ring-color:color-mix(in_srgb,var(--accent-color,#6c5ce7)_22%,transparent)]"
                aria-label="Scroll tags left" data-tag-scroll="left" x-on:click="scrollTags(-1)">
                <x-fas-chevron-left class="size-2.5" aria-hidden="true" />
            </button>
            <div class="flex min-w-0 items-center gap-2 overflow-x-auto scroll-smooth pb-1 [-ms-overflow-style:none] [scrollbar-width:none] [&::-webkit-scrollbar]:hidden"
                x-ref="tagScroller" data-post-card-tags>
                @forelse ($tags as $tag)
                    <x-tag :tag="$tag" class="inline-flex shrink-0 items-center gap-1.5 whitespace-nowrap rounded-[7px] px-2.5 py-1 text-xs font-black transition-all" />
                @empty
                    <span
                        class="inline-flex shrink-0 items-center gap-1.5 whitespace-nowrap rounded-[7px] px-2.5 py-1 text-xs font-black transition-all"
                        style="background-color: color-mix(in srgb, var(--text-color, #0d1b2a) 8%, transparent); color: color-mix(in srgb, var(--text-color, #0d1b2a) 55%, transparent);">
                        No tags selected
                    </span>
                @endforelse
            </div>
            <button type="button"
                class="flex size-7 shrink-0 items-center justify-center rounded-md [color:color-mix(in_srgb,var(--text-color,#0d1b2a)_58%,transparent)] focus:outline-none focus:ring-2 focus:[--tw-ring-color:color-mix(in_srgb,var(--accent-color,#6c5ce7)_22%,transparent)]"
                aria-label="Scroll tags right" data-tag-scroll="right" x-on:click="scrollTags(1)">
                <x-fas-chevron-right class="size-2.5" aria-hidden="true" />
            </button>
        </div>
    </header>

    {{-- profile section --}}
    <section class="px-4 pb-3 sm:px-5">
        <div
            class="overflow-hidden rounded-xl border [border-color:color-mix(in_srgb,var(--accent-color,#6c5ce7)_18%,var(--background-color,#ffffff))] [background:color-mix(in_srgb,var(--background-color,#ffffff)_94%,var(--accent-color,#6c5ce7)_6%)]">
            <div
                class="border-b px-3 py-2 [border-color:color-mix(in_srgb,var(--accent-color,#6c5ce7)_16%,transparent)]">
                <div class="grid grid-cols-[auto_minmax(0,1fr)_auto] items-center gap-1">
                    <button type="button"
                        class="flex size-7 shrink-0 items-center justify-center rounded-md [color:color-mix(in_srgb,var(--text-color,#0d1b2a)_58%,transparent)] focus:outline-none focus:ring-2 focus:[--tw-ring-color:color-mix(in_srgb,var(--accent-color,#6c5ce7)_22%,transparent)]"
                        aria-label="Scroll profiles left" data-profile-scroll="left" x-on:click="scrollProfiles(-1)">
                        <x-fas-chevron-left class="size-2.5" aria-hidden="true" />
                    </button>
                    <div class="flex min-w-0 snap-x items-center gap-2 overflow-x-auto scroll-smooth [-ms-overflow-style:none] [scrollbar-width:none] [&::-webkit-scrollbar]:hidden"
                        x-ref="profileTabs" data-profile-tabs>
                        <template x-for="(profile, index) in profiles" x-bind:key="profile.username + index">
                            <button type="button"
                                class="flex shrink-0 snap-start items-center gap-1.5 border-b-2 px-3.5 py-2.5 text-[10px] font-semibold uppercase tracking-wider"
                                x-bind:class="activeProfile === index ?
                                    '[border-color:var(--accent-color,#6c5ce7)] [color:var(--accent-color,#6c5ce7)] font-bold' :
                                    'border-transparent [color:color-mix(in_srgb,var(--text-color,#0d1b2a)_48%,transparent)]'"
                                x-bind:data-active="activeProfile === index ? 'true' : 'false'"
                                x-bind:data-hover-profile="profile.user_id"
                                x-on:click="switchProfile(index)" data-profile-tab>
                                <img x-show="profile.avatar" x-bind:src="profile.avatar"
                                    x-bind:alt="profile.username + ' profile'" class="size-6 rounded-full object-cover">
                                <span x-show="! profile.avatar"
                                    class="flex size-6 items-center justify-center rounded-full text-[10px] font-extrabold [background:color-mix(in_srgb,var(--background-color,#ffffff)_80%,var(--accent-color,#6c5ce7)_20%)] [color:var(--accent-color,#6c5ce7)]"
                                    x-text="profile.initial"></span>
                                <span x-text="profile.username"></span>
                            </button>
                        </template>
                    </div>
                    <button type="button"
                        class="flex size-7 shrink-0 items-center justify-center rounded-md [color:color-mix(in_srgb,var(--text-color,#0d1b2a)_58%,transparent)] focus:outline-none focus:ring-2 focus:[--tw-ring-color:color-mix(in_srgb,var(--accent-color,#6c5ce7)_22%,transparent)]"
                        aria-label="Scroll profiles right" data-profile-scroll="right"
                        x-on:click="scrollProfiles(1)">
                        <x-fas-chevron-right class="size-2.5" aria-hidden="true" />
                    </button>
                </div>
            </div>

            <a x-bind:href="'{{ $slug ? route('posts.show', $slug) : '#' }}' + '#panel-user-' + (currentProfile.user_id ? currentProfile.user_id : 'anonymous-' + activeProfile)"
                class="block space-y-1.5 px-3 py-2.5 rounded-b-xl transition-all duration-200 hover:[background:color-mix(in_srgb,var(--accent-color,#6c5ce7)_6%,transparent)] cursor-pointer no-underline">
                <div class="flex items-center gap-2 text-xs">
                    <span class="font-bold [color:color-mix(in_srgb,var(--text-color,#0d1b2a)_52%,transparent)]"
                        x-text="currentProfile.time">{{ $profiles[0]['time'] ?? '' }}</span>
                </div>
                <p class="line-clamp-3 min-h-[60px] whitespace-pre-wrap break-words text-[13px] leading-5 [color:color-mix(in_srgb,var(--text-color,#0d1b2a)_78%,transparent)]"
                    data-post-card-description x-text="currentProfile.description">
                    {{ $profiles[0]['description'] ?? '' }}</p>
            </a>
        </div>
    </section>

    <footer
        class="mt-auto relative flex items-center justify-between gap-3 border-t px-4 py-3 sm:px-5 [border-color:color-mix(in_srgb,var(--accent-color,#6c5ce7)_18%,transparent)] [background:color-mix(in_srgb,var(--background-color,#ffffff)_96%,var(--accent-color,#6c5ce7)_4%)]">
        @if($slug)
            <a href="{{ route('posts.show', $slug) }}#reviewLedger"
                class="inline-flex items-center gap-2 rounded-xl px-3 py-2 text-sm font-bold transition-all [color:color-mix(in_srgb,var(--text-color,#0d1b2a)_72%,transparent)] hover:[background:color-mix(in_srgb,var(--background-color,#ffffff)_86%,var(--accent-color,#6c5ce7)_14%)] hover:[color:var(--accent-color,#6c5ce7)]">
                <x-far-comment class="size-5" />
                <span>Comments</span>
                <span
                    class="rounded-full px-2 py-0.5 text-[11px] font-bold [background:color-mix(in_srgb,var(--background-color,#ffffff)_82%,var(--accent-color,#6c5ce7)_18%)] [color:var(--accent-color,#6c5ce7)]"
                    x-text="commentsTotal()">{{ $commentsCount }}</span>
            </a>
        @else
            <button type="button"
                class="inline-flex items-center gap-2 rounded-xl px-3 py-2 text-sm font-bold transition-all [color:color-mix(in_srgb,var(--text-color,#0d1b2a)_72%,transparent)] hover:[background:color-mix(in_srgb,var(--background-color,#ffffff)_86%,var(--accent-color,#6c5ce7)_14%)] hover:[color:var(--accent-color,#6c5ce7)]">
                <x-far-comment class="size-5" />
                <span>Comments</span>
                <span
                    class="rounded-full px-2 py-0.5 text-[11px] font-bold [background:color-mix(in_srgb,var(--background-color,#ffffff)_82%,var(--accent-color,#6c5ce7)_18%)] [color:var(--accent-color,#6c5ce7)]"
                    x-text="commentsTotal()">{{ $commentsCount }}</span>
            </button>
        @endif
        <div class="relative">
            @auth
                @if($slug)
                    <a href="{{ route('posts.show', $slug) }}#reviewForm"
                        class="group inline-flex items-center gap-2 rounded-md px-2 py-0.5 text-sm font-bold transition-all [color:color-mix(in_srgb,var(--text-color,#0d1b2a)_76%,transparent)] [box-shadow:0_1px_3px_color-mix(in_srgb,var(--text-color,#0d1b2a)_18%,transparent)] hover:[background:color-mix(in_srgb,var(--background-color,#ffffff)_84%,var(--accent-color,#6c5ce7)_16%)] hover:[color:var(--accent-color,#6c5ce7)] hover:[box-shadow:0_8px_18px_color-mix(in_srgb,var(--accent-color,#6c5ce7)_18%,transparent)]">
                        <x-far-star class="size-3 group-hover:[color:var(--accent-color,#6c5ce7)]" />
                        <span class="group-hover:[color:var(--accent-color,#6c5ce7)]">Review</span>
                    </a>
                @else
                    <button type="button"
                        class="group inline-flex items-center gap-2 rounded-md px-2 py-0.5 text-sm font-bold transition-all [color:color-mix(in_srgb,var(--text-color,#0d1b2a)_76%,transparent)] [box-shadow:0_1px_3px_color-mix(in_srgb,var(--text-color,#0d1b2a)_18%,transparent)] hover:[background:color-mix(in_srgb,var(--background-color,#ffffff)_84%,var(--accent-color,#6c5ce7)_16%)] hover:[color:var(--accent-color,#6c5ce7)] hover:[box-shadow:0_8px_18px_color-mix(in_srgb,var(--accent-color,#6c5ce7)_18%,transparent)]"
                        x-on:click.stop="reviewOpen = ! reviewOpen" x-bind:aria-expanded="reviewOpen.toString()">
                        <x-far-star class="size-3 group-hover:[color:var(--accent-color,#6c5ce7)]" />
                        <span class="group-hover:[color:var(--accent-color,#6c5ce7)]">Review</span>
                    </button>
                @endif
            @endauth
            @guest
                <button type="button"
                    class="group inline-flex items-center gap-2 rounded-md px-2 py-0.5 text-sm font-bold transition-all [color:color-mix(in_srgb,var(--text-color,#0d1b2a)_76%,transparent)] [box-shadow:0_1px_3px_color-mix(in_srgb,var(--text-color,#0d1b2a)_18%,transparent)] hover:[background:color-mix(in_srgb,var(--background-color,#ffffff)_84%,var(--accent-color,#6c5ce7)_16%)] hover:[color:var(--accent-color,#6c5ce7)] hover:[box-shadow:0_8px_18px_color-mix(in_srgb,var(--accent-color,#6c5ce7)_18%,transparent)]"
                    data-auth-required="review"
                    x-on:click.stop="authAlert('review')">
                    <x-far-star class="size-3 group-hover:[color:var(--accent-color,#6c5ce7)]" />
                    <span class="group-hover:[color:var(--accent-color,#6c5ce7)]">Review</span>
                </button>
            @endguest
        </div>
    </footer>

    @auth
        @if ($postId)
            <template x-teleport="body">
                <div x-cloak x-show="reportOpen" x-transition.opacity.duration.200ms
                    class="fixed inset-0 z-[100000] flex items-center justify-center bg-black/45 p-4 backdrop-blur-md"
                    role="presentation" x-on:click.self="closeReportModal()"
                    x-on:keydown.escape.window="closeReportModal()" data-post-card-report-modal>
                    <form method="POST" action="{{ route('posts.report', $postId) }}"
                        class="flex max-h-[85vh] w-full max-w-xl flex-col overflow-hidden rounded-3xl border [border-color:color-mix(in_srgb,var(--text-color,#0d1b2a)_8%,transparent)] [background:var(--background-color,#ffffff)] [color:var(--text-color,#0d1b2a)] [box-shadow:0_30px_60px_-15px_color-mix(in_srgb,var(--text-color,#0d1b2a)_28%,transparent)]"
                        aria-labelledby="report-modal-title-{{ $postId }}" data-post-card-report-form
                        x-on:click.stop>
                        @csrf

                        <div class="flex items-start justify-between gap-4 px-7 pb-2 pt-7">
                            <div class="min-w-0">
                                <h3 id="report-modal-title-{{ $postId }}"
                                    class="text-[22px] font-bold leading-tight tracking-normal [color:var(--text-color,#0d1b2a)]">
                                    Report Content
                                </h3>
                                <p
                                    class="mt-2 text-sm leading-6 [color:color-mix(in_srgb,var(--text-color,#0d1b2a)_64%,transparent)]">
                                    Select the reason that best matches the issue with this post.
                                </p>
                            </div>
                            <button type="button"
                                class="flex size-9 shrink-0 items-center justify-center rounded-full transition [color:color-mix(in_srgb,var(--text-color,#0d1b2a)_62%,transparent)] hover:[background:color-mix(in_srgb,var(--background-color,#ffffff)_86%,var(--accent-color,#6c5ce7)_14%)] hover:[color:var(--text-color,#0d1b2a)] focus:outline-none focus-visible:ring-2 focus-visible:[--tw-ring-color:color-mix(in_srgb,var(--accent-color,#6c5ce7)_32%,transparent)]"
                                aria-label="Close report dialog" x-on:click="closeReportModal()">
                                <x-fas-xmark class="size-4" aria-hidden="true" />
                            </button>
                        </div>

                        <div class="flex-1 space-y-6 overflow-y-auto px-7 py-4">
                            <div class="space-y-3">
                                <span
                                    class="block text-[11px] font-bold uppercase tracking-[0.08em] [color:color-mix(in_srgb,var(--text-color,#0d1b2a)_42%,transparent)]">Content
                                    Quality</span>
                                <div class="grid gap-3 sm:grid-cols-2">
                                    @foreach ([
                                        ['label' => 'Spam / Misleading', 'icon' => 'triangle-exclamation'],
                                        ['label' => 'Fake / False Info', 'icon' => 'shield-halved'],
                                        ['label' => 'Intellectual Property', 'icon' => 'copyright'],
                                        ['label' => 'Nudity / Obscenity', 'icon' => 'eye-slash'],
                                    ] as $option)
                                        <label
                                            class="flex cursor-pointer items-center justify-between gap-3 rounded-2xl border p-4 transition active:scale-[0.98] [border-color:color-mix(in_srgb,var(--text-color,#0d1b2a)_12%,transparent)] [background:var(--background-color,#ffffff)] hover:[background:color-mix(in_srgb,var(--background-color,#ffffff)_92%,var(--accent-color,#6c5ce7)_8%)]"
                                            x-bind:class="reportReason === @js($option['label']) ? '[border-color:var(--accent-color,#6c5ce7)] [background:color-mix(in_srgb,var(--background-color,#ffffff)_84%,var(--accent-color,#6c5ce7)_16%)] [box-shadow:0_0_0_1px_var(--accent-color,#6c5ce7)]' : ''">
                                            <span
                                                class="flex min-w-0 items-center gap-3 text-sm font-semibold [color:color-mix(in_srgb,var(--text-color,#0d1b2a)_72%,transparent)]"
                                                x-bind:class="reportReason === @js($option['label']) ? '[color:var(--accent-color,#6c5ce7)]' : ''">
                                                @if ($option['icon'] === 'triangle-exclamation')
                                                    <x-fas-triangle-exclamation class="size-4 shrink-0" aria-hidden="true" />
                                                @elseif ($option['icon'] === 'shield-halved')
                                                    <x-fas-shield-halved class="size-4 shrink-0" aria-hidden="true" />
                                                @elseif ($option['icon'] === 'copyright')
                                                    <x-fas-copyright class="size-4 shrink-0" aria-hidden="true" />
                                                @else
                                                    <x-fas-eye-slash class="size-4 shrink-0" aria-hidden="true" />
                                                @endif
                                                <span>{{ $option['label'] }}</span>
                                            </span>
                                            <input type="radio" name="reason" value="{{ $option['label'] }}"
                                                class="size-4 shrink-0 accent-[var(--accent-color,#6c5ce7)]"
                                                x-model="reportReason" data-post-card-report-reason>
                                        </label>
                                    @endforeach
                                </div>
                            </div>

                            <div class="space-y-3">
                                <span
                                    class="block text-[11px] font-bold uppercase tracking-[0.08em] [color:color-mix(in_srgb,var(--text-color,#0d1b2a)_42%,transparent)]">Safety
                                    & Conduct</span>
                                <div class="grid gap-3 sm:grid-cols-2">
                                    @foreach ([
                                        ['label' => 'Hate Speech', 'icon' => 'face-frown'],
                                        ['label' => 'Harassment / Abuse', 'icon' => 'bolt'],
                                        ['label' => 'Violence / Threats', 'icon' => 'hand-fist'],
                                        ['label' => 'Self-Harm Risk', 'icon' => 'heart-crack'],
                                    ] as $option)
                                        <label
                                            class="flex cursor-pointer items-center justify-between gap-3 rounded-2xl border p-4 transition active:scale-[0.98] [border-color:color-mix(in_srgb,var(--text-color,#0d1b2a)_12%,transparent)] [background:var(--background-color,#ffffff)] hover:[background:color-mix(in_srgb,var(--background-color,#ffffff)_92%,var(--accent-color,#6c5ce7)_8%)]"
                                            x-bind:class="reportReason === @js($option['label']) ? '[border-color:var(--accent-color,#6c5ce7)] [background:color-mix(in_srgb,var(--background-color,#ffffff)_84%,var(--accent-color,#6c5ce7)_16%)] [box-shadow:0_0_0_1px_var(--accent-color,#6c5ce7)]' : ''">
                                            <span
                                                class="flex min-w-0 items-center gap-3 text-sm font-semibold [color:color-mix(in_srgb,var(--text-color,#0d1b2a)_72%,transparent)]"
                                                x-bind:class="reportReason === @js($option['label']) ? '[color:var(--accent-color,#6c5ce7)]' : ''">
                                                @if ($option['icon'] === 'face-frown')
                                                    <x-fas-face-frown class="size-4 shrink-0" aria-hidden="true" />
                                                @elseif ($option['icon'] === 'bolt')
                                                    <x-fas-bolt class="size-4 shrink-0" aria-hidden="true" />
                                                @elseif ($option['icon'] === 'hand-fist')
                                                    <x-fas-hand-fist class="size-4 shrink-0" aria-hidden="true" />
                                                @else
                                                    <x-fas-heart-crack class="size-4 shrink-0" aria-hidden="true" />
                                                @endif
                                                <span>{{ $option['label'] }}</span>
                                            </span>
                                            <input type="radio" name="reason" value="{{ $option['label'] }}"
                                                class="size-4 shrink-0 accent-[var(--accent-color,#6c5ce7)]"
                                                x-model="reportReason" data-post-card-report-reason>
                                        </label>
                                    @endforeach
                                </div>
                            </div>

                            <div class="space-y-3">
                                <span
                                    class="block text-[11px] font-bold uppercase tracking-[0.08em] [color:color-mix(in_srgb,var(--text-color,#0d1b2a)_42%,transparent)]">Legal
                                    & Integrity</span>
                                <div class="grid gap-3 sm:grid-cols-2">
                                    @foreach ([
                                        ['label' => 'Illegal Activities', 'icon' => 'gavel'],
                                        ['label' => 'Scams / Fraud', 'icon' => 'ban'],
                                    ] as $option)
                                        <label
                                            class="flex cursor-pointer items-center justify-between gap-3 rounded-2xl border p-4 transition active:scale-[0.98] [border-color:color-mix(in_srgb,var(--text-color,#0d1b2a)_12%,transparent)] [background:var(--background-color,#ffffff)] hover:[background:color-mix(in_srgb,var(--background-color,#ffffff)_92%,var(--accent-color,#6c5ce7)_8%)]"
                                            x-bind:class="reportReason === @js($option['label']) ? '[border-color:var(--accent-color,#6c5ce7)] [background:color-mix(in_srgb,var(--background-color,#ffffff)_84%,var(--accent-color,#6c5ce7)_16%)] [box-shadow:0_0_0_1px_var(--accent-color,#6c5ce7)]' : ''">
                                            <span
                                                class="flex min-w-0 items-center gap-3 text-sm font-semibold [color:color-mix(in_srgb,var(--text-color,#0d1b2a)_72%,transparent)]"
                                                x-bind:class="reportReason === @js($option['label']) ? '[color:var(--accent-color,#6c5ce7)]' : ''">
                                                @if ($option['icon'] === 'gavel')
                                                    <x-fas-gavel class="size-4 shrink-0" aria-hidden="true" />
                                                @else
                                                    <x-fas-ban class="size-4 shrink-0" aria-hidden="true" />
                                                @endif
                                                <span>{{ $option['label'] }}</span>
                                            </span>
                                            <input type="radio" name="reason" value="{{ $option['label'] }}"
                                                class="size-4 shrink-0 accent-[var(--accent-color,#6c5ce7)]"
                                                x-model="reportReason" data-post-card-report-reason>
                                        </label>
                                    @endforeach
                                </div>
                            </div>

                            <div class="space-y-2">
                                <div class="flex items-center justify-between gap-3">
                                    <label for="report-details-{{ $postId }}"
                                        class="text-sm font-semibold [color:var(--text-color,#0d1b2a)]">Provide
                                        Additional Details (Optional)</label>
                                    <span class="text-xs font-semibold [color:color-mix(in_srgb,var(--text-color,#0d1b2a)_45%,transparent)]"
                                        x-bind:class="reportDetailsCount() >= 560 ? '[color:var(--accent-color,#6c5ce7)]' : ''"
                                        x-text="`${reportDetailsCount()} / 600`">0 / 600</span>
                                </div>
                                <textarea id="report-details-{{ $postId }}" name="details" maxlength="600" rows="4"
                                    x-model="reportDetails" data-post-card-report-details
                                    class="w-full resize-none rounded-xl border px-3.5 py-3 text-sm leading-6 [border-color:color-mix(in_srgb,var(--text-color,#0d1b2a)_14%,transparent)] [background:var(--background-color,#ffffff)] [color:var(--text-color,#0d1b2a)] placeholder:[color:color-mix(in_srgb,var(--text-color,#0d1b2a)_42%,transparent)] focus:outline-none focus:ring-4 focus:[border-color:var(--accent-color,#6c5ce7)] focus:[--tw-ring-color:color-mix(in_srgb,var(--accent-color,#6c5ce7)_20%,transparent)]"
                                    placeholder="Describe context or reasons to help us review this report faster."></textarea>
                            </div>
                        </div>

                        <div
                            class="flex justify-end gap-3 border-t px-7 py-5 [border-color:color-mix(in_srgb,var(--text-color,#0d1b2a)_10%,transparent)] [background:color-mix(in_srgb,var(--background-color,#ffffff)_92%,var(--text-color,#0d1b2a)_8%)]">
                            <button type="button"
                                class="inline-flex min-h-11 items-center justify-center rounded-xl border px-5 text-sm font-bold transition [border-color:color-mix(in_srgb,var(--text-color,#0d1b2a)_14%,transparent)] [background:var(--background-color,#ffffff)] [color:color-mix(in_srgb,var(--text-color,#0d1b2a)_68%,transparent)] hover:[background:color-mix(in_srgb,var(--background-color,#ffffff)_88%,var(--text-color,#0d1b2a)_12%)]"
                                x-on:click="closeReportModal()">
                                Cancel
                            </button>
                            <button type="submit"
                                class="inline-flex min-h-11 items-center justify-center gap-2 rounded-xl px-5 text-sm font-bold text-white transition [background:var(--accent-color,#6c5ce7)] [box-shadow:0_4px_12px_color-mix(in_srgb,var(--accent-color,#6c5ce7)_20%,transparent)] disabled:cursor-not-allowed disabled:opacity-55"
                                x-bind:disabled="! reportReason" data-post-card-report-submit disabled>
                                <x-fas-paper-plane class="size-3" aria-hidden="true" />
                                <span>Submit Report</span>
                            </button>
                        </div>
                    </form>
                </div>
            </template>
        @endif
    @endauth
</article>
