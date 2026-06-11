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
                                    <x-fas-ellipsis class="size-4" aria-hidden="true" style="width: 16px; height: 16px;" />
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
                                                    <x-fas-bookmark x-show="saved" class="size-3 [color:var(--accent-color,#6c5ce7)]" style="width: 12px; height: 12px;" />
                                                    <x-far-bookmark x-show="! saved" class="size-3" style="width: 12px; height: 12px;" />
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
                                                    <x-far-flag class="size-3" aria-hidden="true" style="width: 12px; height: 12px;" />
                                                    <span>Report</span>
                                                </button>
                                            </div>

                                            @if (Auth::user()?->role === 'admin')
                                                <form method="POST" action="{{ route('posts.ban', $post->id) }}"
                                                    class="mt-1 border-t pt-1 [border-color:color-mix(in_srgb,var(--text-color,#0d1b2a)_10%,transparent)]"
                                                    x-on:submit.prevent="Swal.fire({
                                                        title: 'Are you sure?',
                                                        text: 'You want to ban and soft delete this post? This action will also hide all audit descriptions.',
                                                        icon: 'warning',
                                                        showCancelButton: true,
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
                                                        <x-fas-ban class="size-3" aria-hidden="true" style="width: 12px; height: 12px;" />
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
                                            <x-far-bookmark class="size-3" style="width: 12px; height: 12px;" />
                                            <span>Save Post</span>
                                        </a>
                                        <a href="{{ route('login') }}"
                                            class="mt-1 flex min-h-9 w-full items-center gap-2 rounded-lg border-t px-2.5 py-1.5 pt-2 text-left transition-all duration-[180ms] [border-color:color-mix(in_srgb,var(--text-color,#0d1b2a)_10%,transparent)] [color:color-mix(in_srgb,var(--text-color,#0d1b2a)_78%,transparent)] hover:translate-x-0.5 hover:[background:color-mix(in_srgb,var(--accent-color,#6c5ce7)_12%,transparent)] hover:[color:var(--accent-color,#6c5ce7)] focus:outline-none focus-visible:translate-x-0.5 focus-visible:ring-2 focus-visible:[--tw-ring-color:color-mix(in_srgb,var(--accent-color,#6c5ce7)_35%,transparent)]"
                                            role="menuitem">
                                            <x-fas-flag class="size-3" aria-hidden="true" style="width: 12px; height: 12px;" />
                                            <span>Report</span>
                                        </a>
                                    @endguest
                                </div>
                            </div>

                            @auth
                                @if ($post->id)
                                    <template x-teleport="body">
                                        <div x-cloak x-show="reportOpen" x-transition.opacity.duration.200ms
                                            class="fixed inset-0 z-[100000] flex items-center justify-center bg-black/45 p-4 backdrop-blur-md"
                                            role="presentation" x-on:click.self="closeReportModal()"
                                            x-on:keydown.escape.window="closeReportModal()">
                                            <form method="POST" action="{{ route('posts.report', $post->id) }}"
                                                class="flex max-h-[85vh] w-full max-w-xl flex-col overflow-hidden rounded-3xl border [border-color:color-mix(in_srgb,var(--text-color,#0d1b2a)_8%,transparent)] [background:var(--background-color,#ffffff)] [color:var(--text-color,#0d1b2a)] [box-shadow:0_30px_60px_-15px_color-mix(in_srgb,var(--text-color,#0d1b2a)_28%,transparent)]"
                                                aria-labelledby="report-modal-title-{{ $post->id }}"
                                                x-on:click.stop>
                                                @csrf

                                                <div class="flex items-start justify-between gap-4 px-7 pb-2 pt-7">
                                                    <div class="min-w-0">
                                                        <h3 id="report-modal-title-{{ $post->id }}"
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
                                                        <x-fas-xmark class="size-4" aria-hidden="true" style="width: 16px; height: 16px;" />
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
                                                                            <x-fas-triangle-exclamation class="size-4 shrink-0" aria-hidden="true" style="width: 16px; height: 16px;" />
                                                                        @elseif ($option['icon'] === 'shield-halved')
                                                                            <x-fas-shield-halved class="size-4 shrink-0" aria-hidden="true" style="width: 16px; height: 16px;" />
                                                                        @elseif ($option['icon'] === 'copyright')
                                                                            <x-fas-copyright class="size-4 shrink-0" aria-hidden="true" style="width: 16px; height: 16px;" />
                                                                        @else
                                                                            <x-fas-eye-slash class="size-4 shrink-0" aria-hidden="true" style="width: 16px; height: 16px;" />
                                                                        @endif
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
                                                                            <x-fas-face-frown class="size-4 shrink-0" aria-hidden="true" style="width: 16px; height: 16px;" />
                                                                        @elseif ($option['icon'] === 'bolt')
                                                                            <x-fas-bolt class="size-4 shrink-0" aria-hidden="true" style="width: 16px; height: 16px;" />
                                                                        @elseif ($option['icon'] === 'hand-fist')
                                                                            <x-fas-hand-fist class="size-4 shrink-0" aria-hidden="true" style="width: 16px; height: 16px;" />
                                                                        @else
                                                                            <x-fas-heart-crack class="size-4 shrink-0" aria-hidden="true" style="width: 16px; height: 16px;" />
                                                                        @endif
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
                                                                            <x-fas-gavel class="size-4 shrink-0" aria-hidden="true" style="width: 16px; height: 16px;" />
                                                                        @else
                                                                            <x-fas-ban class="size-4 shrink-0" aria-hidden="true" style="width: 16px; height: 16px;" />
                                                                        @endif
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
                                                            <label for="report-details-{{ $post->id }}"
                                                                class="text-sm font-semibold [color:var(--text-color,#0d1b2a)]">Provide
                                                                Additional Details (Optional)</label>
                                                            <span class="text-xs font-semibold [color:color-mix(in_srgb,var(--text-color,#0d1b2a)_45%,transparent)]"
                                                                x-bind:class="reportDetailsCount() >= 560 ? '[color:var(--accent-color,#6c5ce7)]' : ''"
                                                                x-text="`${reportDetailsCount()} / 600`">0 / 600</span>
                                                        </div>
                                                        <textarea id="report-details-{{ $post->id }}" name="details" maxlength="600" rows="4"
                                                            x-model="reportDetails"
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
                                                        x-bind:disabled="! reportReason" disabled>
                                                        <x-fas-paper-plane class="size-3" aria-hidden="true" style="width: 12px; height: 12px;" />
                                                        <span>Submit Report</span>
                                                    </button>
                                                </div>
                                            </form>
                                        </div>
                                    </template>
                                @endif
                            @endauth
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
                                    <h2 class="aud-h2">Detailed Description</h2>
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
                                        @endphp
                                        <article
                                            class="aud-depo-panel"
                                            id="panel-user-{{ $userPost->user->id }}"
                                            data-panel="user-{{ $userPost->user->id }}"
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
                                                        <x-fas-ellipsis class="size-4" aria-hidden="true" style="width: 16px; height: 16px;" />
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
                                                                        <x-far-flag class="size-3" aria-hidden="true" style="width: 12px; height: 12px;" />
                                                                        <span>Report</span>
                                                                    </button>
                                                                </div>

                                                                @if (Auth::user()?->role === 'admin')
                                                                    <form method="POST" action="{{ route('audits.ban', $userPost->id) }}"
                                                                        class="mt-1 border-t pt-1 [border-color:color-mix(in_srgb,var(--text-color,#0d1b2a)_10%,transparent)]"
                                                                        x-on:submit.prevent="Swal.fire({
                                                                            title: 'Are you sure?',
                                                                            text: 'You want to hide this audit description?',
                                                                            icon: 'warning',
                                                                            showCancelButton: true,
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
                                                                            <x-fas-ban class="size-3" aria-hidden="true" style="width: 12px; height: 12px;" />
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
                                                                <x-fas-flag class="size-3" aria-hidden="true" style="width: 12px; height: 12px;" />
                                                                <span>Report</span>
                                                            </a>
                                                        @endguest
                                                    </div>
                                                </div>
                                            </header>

                                            @auth
                                                @if ($post->id)
                                                    <template x-teleport="body">
                                                        <div x-cloak x-show="reportOpen" x-transition.opacity.duration.200ms
                                                            class="fixed inset-0 z-[100000] flex items-center justify-center bg-black/45 p-4 backdrop-blur-md"
                                                            role="presentation" x-on:click.self="closeReportModal()"
                                                            x-on:keydown.escape.window="closeReportModal()">
                                                            <form method="POST" action="{{ route('posts.report', $post->id) }}"
                                                                class="flex max-h-[85vh] w-full max-w-xl flex-col overflow-hidden rounded-3xl border [border-color:color-mix(in_srgb,var(--text-color,#0d1b2a)_8%,transparent)] [background:var(--background-color,#ffffff)] [color:var(--text-color,#0d1b2a)] [box-shadow:0_30px_60px_-15px_color-mix(in_srgb,var(--text-color,#0d1b2a)_28%,transparent)]"
                                                                aria-labelledby="report-modal-title-{{ $post->id }}-audit-{{ $userPost->id }}"
                                                                x-on:click.stop>
                                                                @csrf

                                                                <div class="flex items-start justify-between gap-4 px-7 pb-2 pt-7">
                                                                    <div class="min-w-0">
                                                                        <h3 id="report-modal-title-{{ $post->id }}-audit-{{ $userPost->id }}"
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
                                                                        <x-fas-xmark class="size-4" aria-hidden="true" style="width: 16px; height: 16px;" />
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
                                                                                            <x-fas-triangle-exclamation class="size-4 shrink-0" aria-hidden="true" style="width: 16px; height: 16px;" />
                                                                                        @elseif ($option['icon'] === 'shield-halved')
                                                                                            <x-fas-shield-halved class="size-4 shrink-0" aria-hidden="true" style="width: 16px; height: 16px;" />
                                                                                        @elseif ($option['icon'] === 'copyright')
                                                                                            <x-fas-copyright class="size-4 shrink-0" aria-hidden="true" style="width: 16px; height: 16px;" />
                                                                                        @else
                                                                                            <x-fas-eye-slash class="size-4 shrink-0" aria-hidden="true" style="width: 16px; height: 16px;" />
                                                                                        @endif
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
                                                                                            <x-fas-face-frown class="size-4 shrink-0" aria-hidden="true" style="width: 16px; height: 16px;" />
                                                                                        @elseif ($option['icon'] === 'bolt')
                                                                                            <x-fas-bolt class="size-4 shrink-0" aria-hidden="true" style="width: 16px; height: 16px;" />
                                                                                        @elseif ($option['icon'] === 'hand-fist')
                                                                                            <x-fas-hand-fist class="size-4 shrink-0" aria-hidden="true" style="width: 16px; height: 16px;" />
                                                                                        @else
                                                                                            <x-fas-heart-crack class="size-4 shrink-0" aria-hidden="true" style="width: 16px; height: 16px;" />
                                                                                        @endif
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
                                                                                            <x-fas-gavel class="size-4 shrink-0" aria-hidden="true" style="width: 16px; height: 16px;" />
                                                                                        @else
                                                                                            <x-fas-ban class="size-4 shrink-0" aria-hidden="true" style="width: 16px; height: 16px;" />
                                                                                        @endif
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
                                                                            <label for="report-details-{{ $post->id }}-audit-{{ $userPost->id }}"
                                                                                class="text-sm font-semibold [color:var(--text-color,#0d1b2a)]">Provide
                                                                                Additional Details (Optional)</label>
                                                                            <span class="text-xs font-semibold [color:color-mix(in_srgb,var(--text-color,#0d1b2a)_45%,transparent)]"
                                                                                x-bind:class="reportDetailsCount() >= 560 ? '[color:var(--accent-color,#6c5ce7)]' : ''"
                                                                                x-text="`${reportDetailsCount()} / 600`">0 / 600</span>
                                                                        </div>
                                                                        <textarea id="report-details-{{ $post->id }}-audit-{{ $userPost->id }}" name="details" maxlength="600" rows="4"
                                                                            x-model="reportDetails"
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
                                                                        x-bind:disabled="! reportReason" disabled>
                                                                        <x-fas-paper-plane class="size-3" aria-hidden="true" style="width: 12px; height: 12px;" />
                                                                        <span>Submit Report</span>
                                                                    </button>
                                                                </div>
                                                            </form>
                                                        </div>
                                                    </template>
                                                @endif
                                            @endauth

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
