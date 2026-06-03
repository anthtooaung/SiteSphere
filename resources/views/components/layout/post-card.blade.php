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
])

<article
    {{ $attributes->merge(['class' => 'review-card w-full max-w-md overflow-hidden rounded-[20px] border [border-color:color-mix(in_srgb,var(--accent-color,#6c5ce7)_24%,var(--background-color,#ffffff))] [background:var(--background-color,#ffffff)] [color:var(--text-color,#0d1b2a)] [box-shadow:0_18px_50px_color-mix(in_srgb,var(--accent-color,#6c5ce7)_14%,transparent)]']) }}
    x-data="{
        profiles: {{ \Illuminate\Support\Js::from($profiles) }},
        activeProfile: 0,
        saved: @js((bool) $saved),
        actionsOpen: false,
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
        }
    }" x-on:click.outside="reviewOpen = false; actionsOpen = false">
    <header class="space-y-3 px-4 pb-3 pt-4 sm:px-5">
        <div class="flex items-start justify-between gap-3">
            <div class="min-w-0 flex-1">
                <h2 class="break-words text-base font-extrabold leading-snug tracking-normal [color:var(--text-color,#0d1b2a)]"
                    data-post-card-title>
                    {{ $title }}
                </h2>
            </div>

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

                            <form method="POST" action="{{ route('posts.report', $postId) }}"
                                class="mt-1 border-t pt-1 [border-color:color-mix(in_srgb,var(--text-color,#0d1b2a)_10%,transparent)]">
                                @csrf
                                <input type="hidden" name="reason" value="Reported from the post card action menu.">
                                <button type="submit"
                                    class="flex min-h-9 w-full items-center gap-2 rounded-lg px-2.5 py-1.5 text-left transition-all duration-[180ms] [color:color-mix(in_srgb,var(--text-color,#0d1b2a)_78%,transparent)] hover:translate-x-0.5 hover:[background:color-mix(in_srgb,var(--accent-color,#6c5ce7)_12%,transparent)] hover:[color:var(--accent-color,#6c5ce7)] focus:outline-none focus-visible:translate-x-0.5 focus-visible:ring-2 focus-visible:[--tw-ring-color:color-mix(in_srgb,var(--accent-color,#6c5ce7)_35%,transparent)]"
                                    role="menuitem" data-post-card-action="report">
                                    <x-far-flag class="size-3" aria-hidden="true" />
                                    <span>Report</span>
                                </button>
                            </form>

                            @if (Auth::user()?->role === 'admin')
                                <form method="POST" action="{{ route('posts.ban', $postId) }}"
                                    class="mt-1 border-t pt-1 [border-color:color-mix(in_srgb,var(--text-color,#0d1b2a)_10%,transparent)]">
                                    @csrf
                                    <button type="submit"
                                        class="flex min-h-9 w-full items-center gap-2 rounded-lg px-2.5 py-1.5 text-left transition-all duration-[180ms] [color:#b91c1c] hover:translate-x-0.5 hover:[background:color-mix(in_srgb,#fee2e2_78%,var(--background-color,#ffffff)_22%)] focus:outline-none focus-visible:translate-x-0.5 focus-visible:ring-2 focus-visible:[--tw-ring-color:color-mix(in_srgb,#b91c1c_28%,transparent)]"
                                        role="menuitem" data-post-card-action="ban">
                                        <x-fas-ban class="size-3" aria-hidden="true" />
                                        <span>Ban</span>
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
                            <button type="button"
                                class="mt-1 flex min-h-9 w-full items-center gap-2 rounded-lg border-t px-2.5 py-1.5 pt-2 text-left transition-all duration-[180ms] [border-color:color-mix(in_srgb,var(--text-color,#0d1b2a)_10%,transparent)] [color:color-mix(in_srgb,var(--text-color,#0d1b2a)_78%,transparent)]"
                                role="menuitem" data-post-card-action="report">
                                <x-fas-flag class="size-3" aria-hidden="true" />
                                <span>Report</span>
                            </button>
                        @endif
                    @endauth

                    @guest
                        <a href="{{ route('login') }}"
                            class="flex min-h-9 w-full items-center gap-2 rounded-lg px-2.5 py-1.5 text-left transition-all duration-[180ms] [color:color-mix(in_srgb,var(--text-color,#0d1b2a)_78%,transparent)] hover:translate-x-0.5 hover:[background:color-mix(in_srgb,var(--accent-color,#6c5ce7)_12%,transparent)] hover:[color:var(--accent-color,#6c5ce7)] focus:outline-none focus-visible:translate-x-0.5 focus-visible:ring-2 focus-visible:[--tw-ring-color:color-mix(in_srgb,var(--accent-color,#6c5ce7)_35%,transparent)]"
                            role="menuitem" data-auth-required="bookmark" data-post-card-action="bookmark">
                            <x-far-bookmark class="size-3" />
                            <span>Save Post</span>
                        </a>
                        <a href="{{ route('login') }}"
                            class="mt-1 flex min-h-9 w-full items-center gap-2 rounded-lg border-t px-2.5 py-1.5 pt-2 text-left transition-all duration-[180ms] [border-color:color-mix(in_srgb,var(--text-color,#0d1b2a)_10%,transparent)] [color:color-mix(in_srgb,var(--text-color,#0d1b2a)_78%,transparent)] hover:translate-x-0.5 hover:[background:color-mix(in_srgb,var(--accent-color,#6c5ce7)_12%,transparent)] hover:[color:var(--accent-color,#6c5ce7)] focus:outline-none focus-visible:translate-x-0.5 focus-visible:ring-2 focus-visible:[--tw-ring-color:color-mix(in_srgb,var(--accent-color,#6c5ce7)_35%,transparent)]"
                            role="menuitem" data-auth-required="report" data-post-card-action="report">
                            <x-fas-flag class="size-3" aria-hidden="true" />
                            <span>Report</span>
                        </a>
                    @endguest
                </div>
            </div>
        </div>

        <div
            class="inline-flex items-center gap-1.5 rounded-lg border px-2.5 py-1 [border-color:color-mix(in_srgb,var(--accent-color,#6c5ce7)_24%,var(--background-color,#ffffff))] [background:color-mix(in_srgb,var(--background-color,#ffffff)_88%,var(--accent-color,#6c5ce7)_12%)]">
            <span class="text-[13px] font-extrabold leading-none [color:var(--accent-color,#6c5ce7)]"
                x-text="averageRating()">{{ number_format((float) $averageRating, 1) }}</span>
            <div class="flex items-center gap-0.5 [color:var(--accent-color,#6c5ce7)]">
                @foreach (range(1, 5) as $star)
                    <x-fas-star class="size-2.5"
                        x-bind:class="Number(averageRating()) >= {{ $star }} ? '[color:var(--accent-color,#6c5ce7)]' :
                            '[color:color-mix(in_srgb,var(--text-color,#0d1b2a)_24%,transparent)]'" />
                @endforeach
            </div>
            <span class="text-[10px] font-semibold [color:color-mix(in_srgb,var(--text-color,#0d1b2a)_58%,transparent)]">(<span x-text="ratingsTotal()">{{ $ratingsCount }}</span> ratings)</span>
        </div>

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

        <div class="grid grid-cols-[auto_minmax(0,1fr)_auto] items-center gap-1">
            <button type="button"
                class="flex size-7 shrink-0 items-center justify-center rounded-md [color:color-mix(in_srgb,var(--text-color,#0d1b2a)_58%,transparent)] focus:outline-none focus:ring-2 focus:[--tw-ring-color:color-mix(in_srgb,var(--accent-color,#6c5ce7)_22%,transparent)]"
                aria-label="Scroll tags left" data-tag-scroll="left" x-on:click="scrollTags(-1)">
                <x-fas-chevron-left class="size-2.5" aria-hidden="true" />
            </button>
            <div class="flex min-w-0 items-center gap-2 overflow-x-auto scroll-smooth pb-1 [-ms-overflow-style:none] [scrollbar-width:none] [&::-webkit-scrollbar]:hidden"
                x-ref="tagScroller" data-post-card-tags>
                @forelse ($tags as $tag)
                    <span
                        class="inline-flex shrink-0 items-center gap-1 whitespace-nowrap rounded-md border px-2 py-0.5 text-[11px] font-bold [border-color:color-mix(in_srgb,var(--accent-color,#6c5ce7)_24%,var(--background-color,#ffffff))] [background:color-mix(in_srgb,var(--background-color,#ffffff)_90%,var(--accent-color,#6c5ce7)_10%)] [color:var(--accent-color,#6c5ce7)]">
                        <span class="size-1.5 rounded-full [background:var(--accent-color,#6c5ce7)]"></span>
                        {{ $tag }}
                    </span>
                @empty
                    <span
                        class="inline-flex shrink-0 items-center gap-1 whitespace-nowrap rounded-md border px-2 py-0.5 text-[11px] font-bold [border-color:color-mix(in_srgb,var(--text-color,#0d1b2a)_16%,transparent)] [background:color-mix(in_srgb,var(--background-color,#ffffff)_94%,var(--text-color,#0d1b2a)_6%)] [color:color-mix(in_srgb,var(--text-color,#0d1b2a)_62%,transparent)]">
                        <span
                            class="size-1.5 rounded-full [background:color-mix(in_srgb,var(--text-color,#0d1b2a)_42%,transparent)]"></span>
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

            <div class="space-y-1.5 px-3 py-2.5">
                <div class="flex items-center gap-2 text-xs">
                    <span class="font-bold [color:color-mix(in_srgb,var(--text-color,#0d1b2a)_52%,transparent)]"
                        x-text="currentProfile.time">{{ $profiles[0]['time'] ?? '' }}</span>
                </div>
                <p class="line-clamp-3 whitespace-pre-wrap break-words text-[13px] leading-5 [color:color-mix(in_srgb,var(--text-color,#0d1b2a)_78%,transparent)]"
                    data-post-card-description x-text="currentProfile.description">
                    {{ $profiles[0]['description'] ?? '' }}</p>
            </div>
        </div>
    </section>

    <footer
        class="relative flex items-center justify-between gap-3 border-t px-4 py-3 sm:px-5 [border-color:color-mix(in_srgb,var(--accent-color,#6c5ce7)_18%,transparent)] [background:color-mix(in_srgb,var(--background-color,#ffffff)_96%,var(--accent-color,#6c5ce7)_4%)]">
        <button type="button"
            class="inline-flex items-center gap-2 rounded-xl px-3 py-2 text-sm font-bold transition-all [color:color-mix(in_srgb,var(--text-color,#0d1b2a)_72%,transparent)] hover:[background:color-mix(in_srgb,var(--background-color,#ffffff)_86%,var(--accent-color,#6c5ce7)_14%)] hover:[color:var(--accent-color,#6c5ce7)]">
            <x-far-comment class="size-5" />
            <span>Comments</span>
            <span
                class="rounded-full px-2 py-0.5 text-[11px] font-bold [background:color-mix(in_srgb,var(--background-color,#ffffff)_82%,var(--accent-color,#6c5ce7)_18%)] [color:var(--accent-color,#6c5ce7)]"
                x-text="commentsTotal()">{{ $commentsCount }}</span>
        </button>
        <div class="relative">
            @auth
                <button type="button"
                    class="group inline-flex items-center gap-2 rounded-md px-2 py-0.5 text-sm font-bold transition-all [color:color-mix(in_srgb,var(--text-color,#0d1b2a)_76%,transparent)] [box-shadow:0_1px_3px_color-mix(in_srgb,var(--text-color,#0d1b2a)_18%,transparent)] hover:[background:color-mix(in_srgb,var(--background-color,#ffffff)_84%,var(--accent-color,#6c5ce7)_16%)] hover:[color:var(--accent-color,#6c5ce7)] hover:[box-shadow:0_8px_18px_color-mix(in_srgb,var(--accent-color,#6c5ce7)_18%,transparent)]"
                    x-on:click.stop="reviewOpen = ! reviewOpen" x-bind:aria-expanded="reviewOpen.toString()">
                    <x-far-star class="size-3 group-hover:[color:var(--accent-color,#6c5ce7)]" />
                    <span class="group-hover:[color:var(--accent-color,#6c5ce7)]">Review</span>
                </button>
            @endauth
            @guest
                <a href="{{ route('login') }}"
                    class="group inline-flex items-center gap-2 rounded-md px-2 py-0.5 text-sm font-bold transition-all [color:color-mix(in_srgb,var(--text-color,#0d1b2a)_76%,transparent)] [box-shadow:0_1px_3px_color-mix(in_srgb,var(--text-color,#0d1b2a)_18%,transparent)] hover:[background:color-mix(in_srgb,var(--background-color,#ffffff)_84%,var(--accent-color,#6c5ce7)_16%)] hover:[color:var(--accent-color,#6c5ce7)] hover:[box-shadow:0_8px_18px_color-mix(in_srgb,var(--accent-color,#6c5ce7)_18%,transparent)]"
                    data-auth-required="review">
                    <x-far-star class="size-3 group-hover:[color:var(--accent-color,#6c5ce7)]" />
                    <span class="group-hover:[color:var(--accent-color,#6c5ce7)]">Review</span>
                </a>
            @endguest
        </div>
    </footer>
</article>
