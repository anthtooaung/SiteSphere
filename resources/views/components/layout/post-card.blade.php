@props([
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
        setRating(rating) {
            this.selectedRating = rating;
        }
    }"
    x-on:click.outside="reviewOpen = false"
>
    <header class="space-y-3 px-4 pb-3 pt-4 sm:px-5">
        <div class="flex items-start justify-between gap-3">
            <div class="min-w-0 flex-1">
                <h2 class="break-words text-base font-extrabold leading-snug tracking-normal [color:var(--text-color,#0d1b2a)]" data-post-card-title>
                    {{ $title }}
                </h2>
            </div>
            <button
                type="button"
                class="flex size-8 shrink-0 items-center justify-center rounded-lg border [border-color:color-mix(in_srgb,var(--accent-color,#6c5ce7)_22%,var(--background-color,#ffffff))] [background:color-mix(in_srgb,var(--background-color,#ffffff)_92%,var(--accent-color,#6c5ce7)_8%)] [color:color-mix(in_srgb,var(--text-color,#0d1b2a)_62%,transparent)] transition-all hover:[border-color:var(--accent-color,#6c5ce7)] hover:[background:color-mix(in_srgb,var(--background-color,#ffffff)_84%,var(--accent-color,#6c5ce7)_16%)] hover:[color:var(--accent-color,#6c5ce7)] focus:outline-none focus:ring-2 focus:[--tw-ring-color:color-mix(in_srgb,var(--accent-color,#6c5ce7)_22%,transparent)]"
                aria-label="Save post"
                x-on:click="saved = ! saved"
                x-bind:aria-pressed="saved.toString()"
            >
                <x-fas-bookmark x-show="saved" class="size-3.5 [color:var(--accent-color,#6c5ce7)]" />
                <x-far-bookmark x-show="! saved" class="size-3.5" />
            </button>
        </div>

        <div class="inline-flex items-center gap-1.5 rounded-lg border px-2.5 py-1 [border-color:color-mix(in_srgb,var(--accent-color,#6c5ce7)_24%,var(--background-color,#ffffff))] [background:color-mix(in_srgb,var(--background-color,#ffffff)_88%,var(--accent-color,#6c5ce7)_12%)]">
            <span class="text-[13px] font-extrabold leading-none [color:var(--accent-color,#6c5ce7)]" x-text="averageRating()">{{ number_format((float) $averageRating, 1) }}</span>
            <div class="flex items-center gap-0.5 [color:var(--accent-color,#6c5ce7)]">
                @foreach (range(1, 5) as $star)
                    <x-fas-star
                        class="size-2.5"
                        x-bind:class="Number(averageRating()) >= {{ $star }} ? '[color:var(--accent-color,#6c5ce7)]' : '[color:color-mix(in_srgb,var(--text-color,#0d1b2a)_24%,transparent)]'"
                    />
                @endforeach
            </div>
            <span class="text-[10px] font-semibold [color:color-mix(in_srgb,var(--text-color,#0d1b2a)_58%,transparent)]">(<span x-text="ratingsTotal()">{{ $ratingsCount }}</span> ratings)</span>
        </div>

        <a
            href="{{ $url }}"
            target="_blank"
            rel="noopener noreferrer"
            class="group flex items-center gap-2 rounded-xl border px-3 py-2 transition-all [border-color:color-mix(in_srgb,var(--accent-color,#6c5ce7)_22%,var(--background-color,#ffffff))] [background:color-mix(in_srgb,var(--background-color,#ffffff)_90%,var(--accent-color,#6c5ce7)_10%)] hover:[border-color:var(--accent-color,#6c5ce7)] hover:[background:color-mix(in_srgb,var(--background-color,#ffffff)_82%,var(--accent-color,#6c5ce7)_18%)]"
            data-post-card-link
        >
            <span class="flex size-6 shrink-0 items-center justify-center rounded-lg [background:color-mix(in_srgb,var(--background-color,#ffffff)_78%,var(--accent-color,#6c5ce7)_22%)] [color:var(--accent-color,#6c5ce7)] shadow-sm">
                <x-fas-link class="size-2.5" />
            </span>
            <span class="min-w-0 flex-1 truncate text-xs font-bold [color:color-mix(in_srgb,var(--text-color,#0d1b2a)_82%,transparent)] group-hover:[color:var(--accent-color,#6c5ce7)]" data-post-card-url>{{ $url }}</span>
            <x-fas-arrow-up-right-from-square class="size-2.5 opacity-70 [color:var(--accent-color,#6c5ce7)]" />
        </a>

        <div class="flex flex-wrap items-center gap-2" data-post-card-tags>
            @forelse ($tags as $tag)
                <span class="inline-flex items-center gap-1 rounded-md border px-2 py-0.5 text-[11px] font-bold [border-color:color-mix(in_srgb,var(--accent-color,#6c5ce7)_24%,var(--background-color,#ffffff))] [background:color-mix(in_srgb,var(--background-color,#ffffff)_90%,var(--accent-color,#6c5ce7)_10%)] [color:var(--accent-color,#6c5ce7)]">
                    <span class="size-1.5 rounded-full [background:var(--accent-color,#6c5ce7)]"></span>
                    {{ $tag }}
                </span>
            @empty
                <span class="inline-flex items-center gap-1 rounded-md border px-2 py-0.5 text-[11px] font-bold [border-color:color-mix(in_srgb,var(--text-color,#0d1b2a)_16%,transparent)] [background:color-mix(in_srgb,var(--background-color,#ffffff)_94%,var(--text-color,#0d1b2a)_6%)] [color:color-mix(in_srgb,var(--text-color,#0d1b2a)_62%,transparent)]">
                    <span class="size-1.5 rounded-full [background:color-mix(in_srgb,var(--text-color,#0d1b2a)_42%,transparent)]"></span>
                    No tags selected
                </span>
            @endforelse
        </div>
    </header>

    <section class="px-4 pb-3 sm:px-5">
        <div class="overflow-hidden rounded-xl border [border-color:color-mix(in_srgb,var(--accent-color,#6c5ce7)_18%,var(--background-color,#ffffff))] [background:color-mix(in_srgb,var(--background-color,#ffffff)_94%,var(--accent-color,#6c5ce7)_6%)]">
            <div class="border-b px-3 py-2 [border-color:color-mix(in_srgb,var(--accent-color,#6c5ce7)_16%,transparent)]">
                <div class="relative">
                    <div class="flex snap-x items-center gap-2 overflow-x-auto scroll-smooth pb-1 [-ms-overflow-style:none] [scrollbar-width:none] [&::-webkit-scrollbar]:hidden">
                        <template x-for="(profile, index) in profiles" x-bind:key="profile.username + index">
                            <button
                                type="button"
                                class="flex shrink-0 snap-start items-center gap-1.5 border-b-2 px-3.5 py-2.5 text-[10px] font-semibold uppercase tracking-wider transition-all"
                                x-bind:class="activeProfile === index ? '[border-color:var(--accent-color,#6c5ce7)] [color:var(--accent-color,#6c5ce7)] font-bold' : 'border-transparent [color:color-mix(in_srgb,var(--text-color,#0d1b2a)_48%,transparent)] hover:[color:color-mix(in_srgb,var(--text-color,#0d1b2a)_72%,transparent)]'"
                                x-on:click="switchProfile(index)"
                            >
                                <img
                                    x-show="profile.avatar"
                                    x-bind:src="profile.avatar"
                                    x-bind:alt="profile.username + ' profile'"
                                    class="size-6 rounded-full object-cover"
                                >
                                <span
                                    x-show="! profile.avatar"
                                    class="flex size-6 items-center justify-center rounded-full text-[10px] font-extrabold [background:color-mix(in_srgb,var(--background-color,#ffffff)_80%,var(--accent-color,#6c5ce7)_20%)] [color:var(--accent-color,#6c5ce7)]"
                                    x-text="profile.initial"
                                ></span>
                                <span x-text="profile.username"></span>
                            </button>
                        </template>
                    </div>
                </div>
            </div>

            <div class="space-y-1.5 px-3 py-2.5">
                <div class="flex items-center gap-2 text-xs">
                    <span class="font-bold [color:color-mix(in_srgb,var(--text-color,#0d1b2a)_52%,transparent)]" x-text="currentProfile.time">{{ $profiles[0]['time'] ?? '' }}</span>
                </div>
                <p
                    class="line-clamp-3 whitespace-pre-wrap break-words text-[13px] leading-5 [color:color-mix(in_srgb,var(--text-color,#0d1b2a)_78%,transparent)]"
                    data-post-card-description
                    x-text="currentProfile.description"
                >{{ $profiles[0]['description'] ?? '' }}</p>
            </div>
        </div>
    </section>

    <footer class="relative flex items-center justify-between gap-3 border-t px-4 py-3 sm:px-5 [border-color:color-mix(in_srgb,var(--accent-color,#6c5ce7)_18%,transparent)] [background:color-mix(in_srgb,var(--background-color,#ffffff)_96%,var(--accent-color,#6c5ce7)_4%)]">
        <button
            type="button"
            class="inline-flex items-center gap-2 rounded-xl px-3 py-2 text-sm font-bold transition-all [color:color-mix(in_srgb,var(--text-color,#0d1b2a)_72%,transparent)] hover:[background:color-mix(in_srgb,var(--background-color,#ffffff)_86%,var(--accent-color,#6c5ce7)_14%)] hover:[color:var(--accent-color,#6c5ce7)]"
        >
            <x-far-comment class="size-5" />
            <span>Comments</span>
            <span class="rounded-full px-2 py-0.5 text-[11px] font-bold [background:color-mix(in_srgb,var(--background-color,#ffffff)_82%,var(--accent-color,#6c5ce7)_18%)] [color:var(--accent-color,#6c5ce7)]" x-text="commentsTotal()">{{ $commentsCount }}</span>
        </button>
        <div class="relative">
            <button
                type="button"
                class="group inline-flex  items-center gap-2 rounded-md  px-2 py-0.5 text-sm font-bold transition-all  [color:color-mix(in_srgb,var(--text-color,#0d1b2a)_76%,transparent)] [box-shadow:0_1px_3px_color-mix(in_srgb,var(--text-color,#0d1b2a)_18%,transparent)] hover:[background:color-mix(in_srgb,var(--background-color,#ffffff)_84%,var(--accent-color,#6c5ce7)_16%)] hover:[color:var(--accent-color,#6c5ce7)] hover:[box-shadow:0_8px_18px_color-mix(in_srgb,var(--accent-color,#6c5ce7)_18%,transparent)]"
                x-on:click.stop="reviewOpen = ! reviewOpen"
                x-bind:aria-expanded="reviewOpen.toString()"
            >
                <x-far-star class="size-3 group-hover:[color:var(--accent-color,#6c5ce7)]" />
                <span class="group-hover:[color:var(--accent-color,#6c5ce7)]">Review</span>
            </button>
        </div>
    </footer>
</article>
