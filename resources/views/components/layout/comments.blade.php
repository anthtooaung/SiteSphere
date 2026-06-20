@props([
    'post',
    'comments',
    'commentUserRatings' => collect(),
    'userRating' => 0,
])

<section class="aud-reviews" aria-label="User Reports">
    <div class="aud-depo-head">
        <div>
            <h2 class="aud-h2">User Comments</h2>
            <p class="aud-sub">Community experiences and ratings posted by users.</p>
        </div>
    </div>

    <!-- Review composer -->
    @auth
        <form class="aud-composer" id="reviewForm" method="POST" action="{{ route('posts.comments.store', $post->slug) }}">
            @csrf
            <input type="hidden" name="rating" id="ratingInput" value="{{ $userRating }}">

            <div class="aud-composer-top">
                @if(auth()->user()->getAvatarUrl())
                    <img src="{{ auth()->user()->getAvatarUrl() }}" alt="{{ auth()->user()->name }} profile" class="ss-avatar" style="width: 32px; height: 32px; border-radius: 50%; object-fit: cover; flex-shrink: 0;">
                    <span class="aud-composer-label">{{auth()->user()->name}}</span>
                @else
                    <span
                        class="ss-avatar is-initial"
                        aria-hidden="true"
                        style="width: 32px; height: 32px; border-radius: 50%; --ph-hue: {{ (auth()->id() * 47) % 360 }};"
                    >
                        <span class="ss-avatar-initials" style="font-size: 10.9px">
                            {{ collect(explode(' ', auth()->user()->name))->map(fn($n) => Str::substr($n, 0, 1))->join('') }}
                        </span>
                    </span>
                @endif


                <!-- Star rating picker -->
                <div
                    class="ss-rating-picker"
                    id="ratingPicker"
                    role="radiogroup"
                    aria-label="Your rating"
                    style="margin-left: auto"
                ></div>
            </div>

            <textarea
                class="aud-composer-input"
                id="reviewTextarea"
                name="content"
                placeholder="Describe what you experienced — attempts, charges, timing…"
                rows="3"
            ></textarea>

            <div class="aud-composer-foot">
                <span class="aud-composer-hint ss-mono"></span>
                <button
                    type="submit"
                    class="aud-submit"
                    id="reviewSubmit"
                    style="background-color: var(--accent-color); color: var(--background-color);"
                    disabled
                >
                    Submit
                </button>
            </div>
        </form>
    @else
        <div class="aud-composer" style="text-align: center; padding: 24px; border: 1px solid var(--line); border-radius: var(--radius); background: var(--paper);">
            <p class="aud-sub" style="margin: 0 auto 12px;">You must be logged in to contribute your experience.</p>
            <a href="{{ route('login') }}" class="aud-submit" style="display: inline-block; background-color: var(--accent-color); color: var(--background-color);">Login to Review</a>
        </div>
    @endauth

    <!-- Review ledger -->
    <div class="aud-ledger" id="reviewLedger">
        @forelse($comments as $comment)
            @php
                $cInitials = collect(explode(' ', $comment->user->name))
                    ->map(fn($n) => Str::substr($n, 0, 1))
                    ->join('');
                $cHue = ($comment->user->id * 47) % 360;
                $cRating = $commentUserRatings[$comment->user_id] ?? 0;
                $voted = auth()->check() && $comment->commentReactions->contains('user_id', auth()->id());
            @endphp
            <article
                class="aud-row"
                id="comment-{{ $comment->id }}"
                data-review-id="comment-{{ $comment->id }}"
                x-data="{
                    actionsOpen: false,
                    editOpen: false,
                    editContent: @js($comment->content),
                    reportOpen: false,
                    reportReason: '',
                    reportDetails: '',
                    openReportModal() {
                        this.actionsOpen = false;
                        this.reportOpen = true;
                    },
                    closeReportModal() {
                        this.reportOpen = false;
                        this.reportReason = '';
                        this.reportDetails = '';
                    },
                    reportDetailsCount() { return this.reportDetails.length; }
                }"
                x-on:click.outside="actionsOpen = false"
            >
                @if($comment->user->getAvatarUrl())
                    <img src="{{ $comment->user->getAvatarUrl() }}" alt="{{ $comment->user->name }} profile" class="ss-avatar" data-hover-profile="{{ $comment->user_id }}" style="width: 34px; height: 34px; border-radius: 50%; object-fit: cover; flex-shrink: 0;">
                @else
                    <span
                        class="ss-avatar is-initial"
                        aria-hidden="true"
                        data-hover-profile="{{ $comment->user_id }}"
                        style="width: 34px; height: 34px; border-radius: 50%; --ph-hue: {{ $cHue }};"
                    >
                        <span class="ss-avatar-initials" style="font-size: 11.6px">{{ $cInitials }}</span>
                    </span>
                @endif
                <div class="aud-row-body">
                    <div class="aud-row-head">
                        <span class="aud-row-head-left">
                            <span class="aud-row-name" data-hover-profile="{{ $comment->user_id }}">{{ $comment->user->name }}</span>
                            @if($cRating > 0)
                                <span
                                    class="ss-stars"
                                    role="img"
                                    aria-label="{{ $cRating }} out of 5"
                                    style="--sz: 13px; --gap: 2px; --pct: {{ ($cRating / 5) * 100 }}%"
                                >
                                    <span class="ss-stars-track">
                                        @for($i = 0; $i < 5; $i++)
                                            <svg viewBox="0 0 24 24" width="13" height="13">
                                                <path d="M12 2.4l2.95 5.98 6.6.96-4.78 4.66 1.13 6.57L12 17.5l-5.9 3.07 1.13-6.57L2.45 9.34l6.6-.96L12 2.4z" />
                                            </svg>
                                        @endfor
                                    </span>
                                    <span class="ss-stars-fill">
                                        @for($i = 0; $i < 5; $i++)
                                            <svg viewBox="0 0 24 24" width="13" height="13">
                                                <path d="M12 2.4l2.95 5.98 6.6.96-4.78 4.66 1.13 6.57L12 17.5l-5.9 3.07 1.13-6.57L2.45 9.34l6.6-.96L12 2.4z" />
                                            </svg>
                                        @endfor
                                    </span>
                                </span>
                            @endif
                        </span>

                        {{-- Three-dot menu --}}
                        @auth
                            <div class="relative shrink-0" style="margin-left: auto;">
                                <button type="button"
                                    class="flex size-8 shrink-0 items-center justify-center rounded-full border [border-color:var(--line)] [background:var(--paper)] [color:color-mix(in_srgb,var(--text-color,#0d1b2a)_62%,transparent)] transition-all hover:[border-color:var(--accent-line)] hover:[background:var(--accent-wash)] hover:[color:var(--accent-ink)]"
                                    aria-label="More options" aria-haspopup="menu"
                                    x-on:click.stop="actionsOpen = ! actionsOpen"
                                    x-on:keydown.escape.window="actionsOpen = false"
                                    x-bind:aria-expanded="actionsOpen.toString()">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-more-horizontal"><circle cx="12" cy="12" r="1"/><circle cx="19" cy="12" r="1"/><circle cx="5" cy="12" r="1"/></svg>
                                </button>

                                <div x-cloak x-show="actionsOpen"
                                    x-transition:enter="transition ease-out duration-200 origin-top-right"
                                    x-transition:enter-start="opacity-0 -translate-y-1 scale-95"
                                    x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                                    x-transition:leave="transition ease-in duration-150 origin-top-right"
                                    x-transition:leave-start="opacity-100 translate-y-0 scale-100"
                                    x-transition:leave-end="opacity-0 -translate-y-1 scale-95"
                                    class="absolute right-0 top-10 z-20 w-40 overflow-hidden rounded-lg border p-2 text-sm font-bold [border-color:color-mix(in_srgb,var(--accent-color,#6c5ce7)_20%,var(--background-color,#ffffff))] [background:var(--background-color,#ffffff)] [box-shadow:0_16px_36px_color-mix(in_srgb,var(--text-color,#0d1b2a)_18%,transparent)]"
                                    role="menu">
                                    @if(auth()->id() === $comment->user_id)
                                        {{-- Edit --}}
                                        <button type="button"
                                            class="flex min-h-9 w-full items-center gap-2 rounded-lg px-2.5 py-1.5 text-left transition-all duration-[180ms] [color:color-mix(in_srgb,var(--text-color,#0d1b2a)_78%,transparent)] hover:translate-x-0.5 hover:[background:color-mix(in_srgb,var(--accent-color,#6c5ce7)_12%,transparent)] hover:[color:var(--accent-color,#6c5ce7)] focus:outline-none"
                                            role="menuitem"
                                            x-on:click="actionsOpen = false; editOpen = ! editOpen">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 3a2.85 2.83 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5Z"/><path d="m15 5 4 4"/></svg>
                                            <span>Edit</span>
                                        </button>
                                        {{-- Delete --}}
                                        <div class="mt-1 border-t pt-1 [border-color:color-mix(in_srgb,var(--text-color,#0d1b2a)_10%,transparent)]">
                                            <form method="POST" action="{{ route('comments.destroy', $comment->id) }}"
                                                x-on:submit.prevent="window.sitesphereSwal.confirm({
                                                    title: 'Delete this comment?',
                                                    text: 'This action cannot be undone.',
                                                    icon: 'warning',
                                                    confirmButtonColor: '#b91c1c',
                                                    cancelButtonColor: '#6c757d',
                                                    confirmButtonText: 'Yes, delete it!'
                                                }).then((result) => {
                                                    if (result.isConfirmed) { $el.submit(); }
                                                })">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit"
                                                    class="flex min-h-9 w-full items-center gap-2 rounded-lg px-2.5 py-1.5 text-left transition-all duration-[180ms] [color:#b91c1c] hover:translate-x-0.5 hover:[background:color-mix(in_srgb,#b91c1c_12%,transparent)] focus:outline-none"
                                                    role="menuitem">
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14H6L5 6"/><path d="M10 11v6"/><path d="M14 11v6"/><path d="M9 6V4h6v2"/></svg>
                                                    <span>Delete</span>
                                                </button>
                                            </form>
                                        </div>
                                    @else
                                        @if(auth()->user()->role === 'admin')
                                            {{-- Ban --}}
                                            <div class="mt-1 border-t pt-1 [border-color:color-mix(in_srgb,var(--text-color,#0d1b2a)_10%,transparent)]">
                                                <form method="POST" action="{{ route('comments.ban', $comment->id) }}"
                                                    x-on:submit.prevent="window.sitesphereSwal.confirm({
                                                        title: 'Ban this comment?',
                                                        text: 'Please specify the reason for banning this comment:',
                                                        icon: 'warning',
                                                        input: 'textarea',
                                                        inputPlaceholder: 'Enter the ban reason here...',
                                                        inputValidator: (value) => {
                                                            if (!value) {
                                                                return 'A ban reason is required!';
                                                            }
                                                        },
                                                        confirmButtonColor: '#ef4444',
                                                        cancelButtonColor: '#6c757d',
                                                        confirmButtonText: 'Yes, ban it!'
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
                                                    <button type="submit"
                                                        class="flex min-h-9 w-full items-center gap-2 rounded-lg px-2.5 py-1.5 text-left transition-all duration-[180ms] [color:#ef4444] hover:translate-x-0.5 hover:[background:color-mix(in_srgb,#ef4444_12%,transparent)] focus:outline-none"
                                                        role="menuitem">
                                                        <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-ban"><circle cx="12" cy="12" r="10"/><path d="m4.9 4.9 14.2 14.2"/></svg>
                                                        <span>Ban</span>
                                                    </button>
                                                </form>
                                            </div>
                                        @else
                                            {{-- Report --}}
                                            <div class="mt-1 border-t pt-1 [border-color:color-mix(in_srgb,var(--text-color,#0d1b2a)_10%,transparent)]">
                                                <button type="button"
                                                    class="flex min-h-9 w-full items-center gap-2 rounded-lg px-2.5 py-1.5 text-left transition-all duration-[180ms] [color:color-mix(in_srgb,var(--text-color,#0d1b2a)_78%,transparent)] hover:translate-x-0.5 hover:[background:color-mix(in_srgb,var(--accent-color,#6c5ce7)_12%,transparent)] hover:[color:var(--accent-color,#6c5ce7)] focus:outline-none"
                                                    role="menuitem"
                                                    x-on:click="openReportModal()">
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-flag"><path d="M4 15s1-1 4-1 5 2 8 2 4-1 4-1V3s-1 1-4 1-5-2-8-2-4 1-4 1z"/><line x1="4" x2="4" y1="22" y2="15"/></svg>
                                                    <span>Report</span>
                                                </button>
                                            </div>
                                        @endif
                                    @endif
                                </div>
                            </div>
                        @endauth
                    </div>

                    <span class="aud-row-date ss-mono">{{ $comment->created_at->diffForHumans() }}</span>

                    {{-- Inline edit form --}}
                    @auth
                        @if(auth()->id() === $comment->user_id)
                            <div x-show="editOpen" x-collapse style="margin-top: 8px;">
                                <form method="POST" action="{{ route('comments.update', $comment->id) }}">
                                    @csrf
                                    @method('PATCH')
                                    <textarea
                                        name="content"
                                        x-model="editContent"
                                        rows="3"
                                        class="aud-composer-input"
                                        style="width: 100%; margin-bottom: 8px;"
                                        maxlength="2000"
                                    ></textarea>
                                    <div style="display: flex; gap: 8px; justify-content: flex-end;">
                                        <button type="button" @click="editOpen = false"
                                            class="inline-flex min-h-9 items-center justify-center rounded-xl border px-4 text-sm font-bold transition [border-color:color-mix(in_srgb,var(--text-color,#0d1b2a)_14%,transparent)] [background:var(--background-color,#ffffff)] [color:color-mix(in_srgb,var(--text-color,#0d1b2a)_68%,transparent)] hover:[background:color-mix(in_srgb,var(--background-color,#ffffff)_88%,var(--text-color,#0d1b2a)_12%)]">Cancel</button>
                                        <button type="submit"
                                            class="inline-flex min-h-9 items-center justify-center gap-2 rounded-xl px-4 text-sm font-bold text-white transition [background:var(--accent-color,#6c5ce7)] [box-shadow:0_4px_12px_color-mix(in_srgb,var(--accent-color,#6c5ce7)_20%,transparent)]">Save</button>
                                    </div>
                                </form>
                            </div>
                        @endif
                    @endauth

                    <div class="ss-expandable" data-clamp="3" x-show="!editOpen">
                        <p class="ss-expandable-text" style="--clamp-lines: 3">
                            {!! nl2br(e($comment->content)) !!}
                        </p>
                        <p class="ss-expandable-ghost" aria-hidden="true">
                            {!! nl2br(e($comment->content)) !!}
                        </p>
                        <button type="button" class="ss-expand-btn" hidden>See more</button>
                    </div>
                    <div class="ss-helpful" x-show="!editOpen">
                        @php
                            $noun = $comment->helpful_count == 1 ? 'person' : 'people';
                        @endphp
                        <span class="ss-helpful-count helpful-count">{{ $comment->helpful_count }} {{ $noun }} found this helpful</span>
                        <span class="ss-helpful-pair">
                            <span class="ss-helpful-ask">Was this useful?</span>
                            <span class="ss-helpful-btns">
                                <button
                                    type="button"
                                    class="ss-helpful-btn js-helpful-btn @if($voted) is-active @endif"
                                    data-comment-id="{{ $comment->id }}"
                                    aria-pressed="{{ $voted ? 'true' : 'false' }}"
                                >
                                    <span aria-hidden="true">
                                        <svg viewBox="0 0 24 24" width="14" height="14" fill="currentColor" style="display: block; flex-shrink: 0">
                                            <path d="M14 9V5a3 3 0 0 0-3-3l-4 9v11h11.28a2 2 0 0 0 2-1.7l1.38-9a2 2 0 0 0-2-2.3H14z" />
                                            <path d="M7 22H4a2 2 0 0 1-2-2v-7a2 2 0 0 1 2-2h3" />
                                        </svg>
                                    </span>
                                    <span class="helpful-label">Helpful</span>
                                </button>
                            </span>
                        </span>
                    </div>

                    {{-- Report modal (teleported to body, same style as post-detail) --}}
                    @auth
                        @if(auth()->id() !== $comment->user_id)
                            <template x-teleport="body">
                                <div x-cloak x-show="reportOpen" x-transition.opacity.duration.200ms
                                    class="fixed inset-0 z-[100000] flex items-center justify-content: center; justify-content bg-black/45 p-4 backdrop-blur-md"
                                    style="justify-content: center;"
                                    role="presentation"
                                    x-on:click.self="closeReportModal()"
                                    x-on:keydown.escape.window="closeReportModal()">
                                    <form method="POST" action="{{ route('comments.report', $comment->id) }}"
                                        class="flex max-h-[85vh] w-full max-w-xl flex-col overflow-hidden rounded-3xl border [border-color:color-mix(in_srgb,var(--text-color,#0d1b2a)_8%,transparent)] [background:var(--background-color,#ffffff)] [color:var(--text-color,#0d1b2a)] [box-shadow:0_30px_60px_-15px_color-mix(in_srgb,var(--text-color,#0d1b2a)_28%,transparent)]"
                                        aria-labelledby="comment-report-modal-title-{{ $comment->id }}"
                                        x-on:click.stop>
                                        @csrf

                                        <div class="flex items-start justify-between gap-4 px-7 pb-2 pt-7">
                                            <div class="min-w-0">
                                                <h3 id="comment-report-modal-title-{{ $comment->id }}"
                                                    class="text-[22px] font-bold leading-tight tracking-normal [color:var(--text-color,#0d1b2a)]">
                                                    Report Comment
                                                </h3>
                                                <p class="mt-2 text-sm leading-6 [color:color-mix(in_srgb,var(--text-color,#0d1b2a)_64%,transparent)]">
                                                    Select the reason that best describes the issue with this comment.
                                                </p>
                                            </div>
                                            <button type="button"
                                                class="flex size-9 shrink-0 items-center justify-center rounded-full transition [color:color-mix(in_srgb,var(--text-color,#0d1b2a)_62%,transparent)] hover:[background:color-mix(in_srgb,var(--background-color,#ffffff)_86%,var(--accent-color,#6c5ce7)_14%)] hover:[color:var(--text-color,#0d1b2a)] focus:outline-none"
                                                aria-label="Close report dialog"
                                                x-on:click="closeReportModal()">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-x"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>
                                            </button>
                                        </div>

                                        <div class="flex-1 space-y-6 overflow-y-auto px-7 py-4">
                                            <div class="space-y-3">
                                                <span class="block text-[11px] font-bold uppercase tracking-[0.08em] [color:color-mix(in_srgb,var(--text-color,#0d1b2a)_42%,transparent)]">Content Quality</span>
                                                <div class="grid gap-3 sm:grid-cols-2">
                                                    @foreach ([
                                                        ['label' => 'Spam / Misleading'],
                                                        ['label' => 'Fake / False Info'],
                                                        ['label' => 'Misinformation'],
                                                        ['label' => 'Nudity / Obscenity'],
                                                    ] as $option)
                                                        <label class="flex cursor-pointer items-center justify-between gap-3 rounded-2xl border p-4 transition active:scale-[0.98] [border-color:color-mix(in_srgb,var(--text-color,#0d1b2a)_12%,transparent)] [background:var(--background-color,#ffffff)] hover:[background:color-mix(in_srgb,var(--background-color,#ffffff)_92%,var(--accent-color,#6c5ce7)_8%)]"
                                                            x-bind:class="reportReason === @js($option['label']) ? '[border-color:var(--accent-color,#6c5ce7)] [background:color-mix(in_srgb,var(--background-color,#ffffff)_84%,var(--accent-color,#6c5ce7)_16%)] [box-shadow:0_0_0_1px_var(--accent-color,#6c5ce7)]' : ''">
                                                            <span class="flex min-w-0 items-center gap-3 text-sm font-semibold [color:color-mix(in_srgb,var(--text-color,#0d1b2a)_72%,transparent)]"
                                                                x-bind:class="reportReason === @js($option['label']) ? '[color:var(--accent-color,#6c5ce7)]' : ''">
                                                                <span>{{ $option['label'] }}</span>
                                                            </span>
                                                            <input type="radio" name="reason" value="{{ $option['label'] }}"
                                                                class="size-4 shrink-0 accent-[var(--accent-color,#6c5ce7)]"
                                                                x-model="reportReason">
                                                        </label>
                                                    @endforeach
                                                </div>
                                            </div>

                                            <div class="space-y-3">
                                                <span class="block text-[11px] font-bold uppercase tracking-[0.08em] [color:color-mix(in_srgb,var(--text-color,#0d1b2a)_42%,transparent)]">Safety &amp; Conduct</span>
                                                <div class="grid gap-3 sm:grid-cols-2">
                                                    @foreach ([
                                                        ['label' => 'Hate Speech'],
                                                        ['label' => 'Harassment / Abuse'],
                                                        ['label' => 'Violence / Threats'],
                                                        ['label' => 'Other'],
                                                    ] as $option)
                                                        <label class="flex cursor-pointer items-center justify-between gap-3 rounded-2xl border p-4 transition active:scale-[0.98] [border-color:color-mix(in_srgb,var(--text-color,#0d1b2a)_12%,transparent)] [background:var(--background-color,#ffffff)] hover:[background:color-mix(in_srgb,var(--background-color,#ffffff)_92%,var(--accent-color,#6c5ce7)_8%)]"
                                                            x-bind:class="reportReason === @js($option['label']) ? '[border-color:var(--accent-color,#6c5ce7)] [background:color-mix(in_srgb,var(--background-color,#ffffff)_84%,var(--accent-color,#6c5ce7)_16%)] [box-shadow:0_0_0_1px_var(--accent-color,#6c5ce7)]' : ''">
                                                            <span class="flex min-w-0 items-center gap-3 text-sm font-semibold [color:color-mix(in_srgb,var(--text-color,#0d1b2a)_72%,transparent)]"
                                                                x-bind:class="reportReason === @js($option['label']) ? '[color:var(--accent-color,#6c5ce7)]' : ''">
                                                                <span>{{ $option['label'] }}</span>
                                                            </span>
                                                            <input type="radio" name="reason" value="{{ $option['label'] }}"
                                                                class="size-4 shrink-0 accent-[var(--accent-color,#6c5ce7)]"
                                                                x-model="reportReason">
                                                        </label>
                                                    @endforeach
                                                </div>
                                            </div>

                                            <div class="space-y-2">
                                                <div class="flex items-center justify-between gap-3">
                                                    <label for="comment-report-details-{{ $comment->id }}"
                                                        class="text-sm font-semibold [color:var(--text-color,#0d1b2a)]">Provide Additional Details (Optional)</label>
                                                    <span class="text-xs font-semibold [color:color-mix(in_srgb,var(--text-color,#0d1b2a)_45%,transparent)]"
                                                        x-bind:class="reportDetailsCount() >= 560 ? '[color:var(--accent-color,#6c5ce7)]' : ''"
                                                        x-text="`${reportDetailsCount()} / 600`">0 / 600</span>
                                                </div>
                                                <textarea id="comment-report-details-{{ $comment->id }}" name="details" maxlength="600" rows="4"
                                                    x-model="reportDetails"
                                                    class="w-full resize-none rounded-xl border px-3.5 py-3 text-sm leading-6 [border-color:color-mix(in_srgb,var(--text-color,#0d1b2a)_14%,transparent)] [background:var(--background-color,#ffffff)] [color:var(--text-color,#0d1b2a)] placeholder:[color:color-mix(in_srgb,var(--text-color,#0d1b2a)_42%,transparent)] focus:outline-none focus:ring-4 focus:[border-color:var(--accent-color,#6c5ce7)] focus:[--tw-ring-color:color-mix(in_srgb,var(--accent-color,#6c5ce7)_20%,transparent)]"
                                                    placeholder="Describe context or reasons to help us review this report faster."></textarea>
                                            </div>
                                        </div>

                                        <div class="flex justify-end gap-3 border-t px-7 py-5 [border-color:color-mix(in_srgb,var(--text-color,#0d1b2a)_10%,transparent)] [background:color-mix(in_srgb,var(--background-color,#ffffff)_92%,var(--text-color,#0d1b2a)_8%)]">
                                            <button type="button"
                                                class="inline-flex min-h-11 items-center justify-center rounded-xl border px-5 text-sm font-bold transition [border-color:color-mix(in_srgb,var(--text-color,#0d1b2a)_14%,transparent)] [background:var(--background-color,#ffffff)] [color:color-mix(in_srgb,var(--text-color,#0d1b2a)_68%,transparent)] hover:[background:color-mix(in_srgb,var(--background-color,#ffffff)_88%,var(--text-color,#0d1b2a)_12%)]"
                                                x-on:click="closeReportModal()">
                                                Cancel
                                            </button>
                                            <button type="submit"
                                                class="inline-flex min-h-11 items-center justify-center gap-2 rounded-xl px-5 text-sm font-bold text-white transition [background:var(--accent-color,#6c5ce7)] [box-shadow:0_4px_12px_color-mix(in_srgb,var(--accent-color,#6c5ce7)_20%,transparent)] disabled:cursor-not-allowed disabled:opacity-55"
                                                x-bind:disabled="! reportReason" disabled>
                                                <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-send"><line x1="22" x2="11" y1="2" y2="13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/></svg>
                                                <span>Submit Report</span>
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </template>
                        @endif
                    @endauth
                </div>
            </article>
        @empty
            <div style="padding: 24px; text-align: center; border-top: 1px solid var(--line-2);">
                <p class="aud-sub">No user Comments yet. Be the first to share your experience!</p>
            </div>
        @endforelse
    </div>
</section>
