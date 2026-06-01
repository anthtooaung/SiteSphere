@props([
    'title',
    'url',
    'category' => 'Uncategorized',
    'profiles' => [],
    'averageRating' => 0,
    'ratingsCount' => 0,
    'commentsCount' => 0,
    'saved' => false,
])

<article
    {{ $attributes->merge(['class' => 'review-card w-full max-w-md overflow-hidden rounded-[20px] border border-slate-100 bg-white shadow-[0_18px_50px_rgba(15,23,42,0.08)]']) }}
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
                <h2 class="break-words text-base font-extrabold leading-snug tracking-normal text-slate-950" data-post-card-title>
                    {{ $title }}
                </h2>
            </div>
            <button
                type="button"
                class="flex size-8 shrink-0 items-center justify-center rounded-lg border border-slate-200 bg-white text-slate-400 transition-all hover:border-blue-200 hover:bg-blue-50 hover:text-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-100"
                aria-label="Save post"
                x-on:click="saved = ! saved"
                x-bind:aria-pressed="saved.toString()"
            >
                <x-fas-bookmark x-show="saved" class="size-3.5 text-blue-600" />
                <x-far-bookmark x-show="! saved" class="size-3.5" />
            </button>
        </div>

        <div class="inline-flex items-center gap-1.5 rounded-lg border border-amber-100 bg-amber-50 px-2.5 py-1">
            <span class="text-[13px] font-extrabold leading-none text-amber-600" x-text="averageRating()">{{ number_format((float) $averageRating, 1) }}</span>
            <div class="flex items-center gap-0.5 text-amber-500">
                @foreach (range(1, 5) as $star)
                    <x-fas-star
                        class="size-2.5"
                        x-bind:class="Number(averageRating()) >= {{ $star }} ? 'text-amber-500' : 'text-slate-300'"
                    />
                @endforeach
            </div>
            <span class="text-[10px] font-semibold text-slate-400">(<span x-text="ratingsTotal()">{{ $ratingsCount }}</span> ratings)</span>
        </div>

        <a
            href="{{ $url }}"
            target="_blank"
            rel="noopener noreferrer"
            class="group flex items-center gap-2 rounded-xl border border-blue-100 bg-blue-50 px-3 py-2 transition-all hover:border-blue-200 hover:bg-blue-100/70"
            data-post-card-link
        >
            <span class="flex size-6 shrink-0 items-center justify-center rounded-lg bg-white text-blue-600 shadow-sm">
                <x-fas-link class="size-2.5" />
            </span>
            <span class="min-w-0 flex-1 truncate text-xs font-bold text-slate-700 group-hover:text-blue-700" data-post-card-url>{{ $url }}</span>
            <x-fas-arrow-up-right-from-square class="size-2.5 text-blue-400 opacity-70" />
        </a>

        <div class="flex flex-wrap items-center gap-2">
            <span class="inline-flex items-center gap-1 rounded-md border border-emerald-100 bg-emerald-50 px-2 py-0.5 text-[11px] font-bold text-emerald-700" data-post-card-category>
                <span class="size-1.5 rounded-full bg-emerald-500"></span>
                {{ $category }}
            </span>
        </div>
    </header>

    <section class="px-4 pb-3 sm:px-5">
        <div class="overflow-hidden rounded-xl border border-slate-100 bg-slate-50">
            <div class="border-b border-slate-200/60 px-3 py-2">
                <div class="relative">
                    <div class="flex snap-x items-center gap-2 overflow-x-auto scroll-smooth pb-1 [-ms-overflow-style:none] [scrollbar-width:none] [&::-webkit-scrollbar]:hidden">
                        <template x-for="(profile, index) in profiles" x-bind:key="profile.username + index">
                            <button
                                type="button"
                                class="flex shrink-0 snap-start items-center gap-1.5 border-b-2 px-3.5 py-2.5 text-[10px] font-semibold uppercase tracking-wider transition-all"
                                x-bind:class="activeProfile === index ? 'border-blue-600 text-blue-600 font-bold' : 'border-transparent text-gray-400 hover:text-gray-600'"
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
                                    class="flex size-6 items-center justify-center rounded-full bg-blue-100 text-[10px] font-extrabold text-blue-700"
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
                    <span class="font-bold text-slate-800" x-text="currentProfile.username">{{ $profiles[0]['username'] ?? '' }}</span>
                    <span class="size-1 rounded-full bg-slate-300"></span>
                    <span class="font-medium text-slate-400" x-text="currentProfile.time">{{ $profiles[0]['time'] ?? '' }}</span>
                </div>
                <p
                    class="line-clamp-3 whitespace-pre-wrap break-words text-[13px] leading-5 text-slate-700"
                    data-post-card-description
                    x-text="currentProfile.description"
                >{{ $profiles[0]['description'] ?? '' }}</p>
            </div>
        </div>
    </section>

    <footer class="relative flex items-center justify-between gap-3 border-t border-slate-100 bg-white px-4 py-3 sm:px-5">
        <button
            type="button"
            class="inline-flex items-center gap-2 rounded-xl px-3 py-2 text-sm font-bold text-slate-600 transition-all hover:bg-slate-50 hover:text-blue-600"
        >
            <x-far-comment class="size-5" />
            <span>Comments</span>
            <span class="rounded-full bg-slate-100 px-2 py-0.5 text-[11px] font-bold text-slate-600" x-text="commentsTotal()">{{ $commentsCount }}</span>
        </button>

        <div class="relative">
            <button
                type="button"
                class="inline-flex items-center gap-2 rounded-xl bg-slate-900 px-4 py-2.5 text-sm font-bold text-white shadow-sm transition-all hover:bg-blue-600 focus:outline-none focus:ring-4 focus:ring-blue-100"
                x-on:click.stop="reviewOpen = ! reviewOpen"
                x-bind:aria-expanded="reviewOpen.toString()"
            >
                <x-far-star class="size-3" />
                <span>Review</span>
            </button>

            <div
                x-cloak
                x-show="reviewOpen"
                x-transition
                class="absolute bottom-full right-0 z-30 mb-3 w-48 space-y-2.5 rounded-2xl border border-slate-200/80 bg-white p-4 shadow-xl"
            >
                <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Your rating</p>
                <div class="flex items-center gap-1.5 text-base" aria-label="Star rating">
                    @foreach (range(1, 5) as $rating)
                        <button
                            type="button"
                            class="transition-all hover:-translate-y-0.5 hover:scale-105"
                            x-on:click="setRating({{ $rating }})"
                            aria-label="Rate {{ $rating }} star"
                        >
                            <x-fas-star
                                class="size-4"
                                x-bind:class="selectedRating >= {{ $rating }} ? 'text-amber-500' : 'text-slate-300'"
                            />
                        </button>
                    @endforeach
                </div>
            </div>
        </div>
    </footer>
</article>
