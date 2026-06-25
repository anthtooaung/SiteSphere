@props(['postId', 'modalId' => null, 'action' => null])

@php
    $uid = $modalId ?? $postId;
@endphp

@auth
    @if ($postId)
        <template x-teleport="body">
            <div x-cloak x-show="reportOpen" x-transition.opacity.duration.200ms
                class="fixed inset-0 z-[100000] flex items-center justify-center bg-black/45 p-4 backdrop-blur-md"
                role="presentation" x-on:click.self="closeReportModal()"
                x-on:keydown.escape.window="closeReportModal()">
                <form method="POST" action="{{ $action ?? route('posts.report', $postId) }}"
                    class="flex max-h-[85vh] w-full max-w-xl flex-col overflow-hidden rounded-3xl border [border-color:color-mix(in_srgb,var(--text-color,#0d1b2a)_8%,transparent)] [background:var(--background-color,#ffffff)] [color:var(--text-color,#0d1b2a)] [box-shadow:0_30px_60px_-15px_color-mix(in_srgb,var(--text-color,#0d1b2a)_28%,transparent)]"
                    aria-labelledby="report-modal-title-{{ $uid }}"
                    x-on:click.stop>
                    @csrf

                    <div class="flex items-start justify-between gap-4 px-7 pb-2 pt-7">
                        <div class="min-w-0">
                            <h3 id="report-modal-title-{{ $uid }}"
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
                                    ['label' => 'Spam / Misleading', 'icon' => 'fas-triangle-exclamation'],
                                    ['label' => 'Fake / False Info', 'icon' => 'fas-shield-halved'],
                                    ['label' => 'Intellectual Property', 'icon' => 'fas-copyright'],
                                    ['label' => 'Nudity / Obscenity', 'icon' => 'fas-eye-slash'],
                                ] as $option)
                                    <label
                                        class="flex cursor-pointer items-center justify-between gap-3 rounded-2xl border p-4 transition active:scale-[0.98] [border-color:color-mix(in_srgb,var(--text-color,#0d1b2a)_12%,transparent)] [background:var(--background-color,#ffffff)] hover:[background:color-mix(in_srgb,var(--background-color,#ffffff)_92%,var(--accent-color,#6c5ce7)_8%)]"
                                        x-bind:class="reportReason === @js($option['label']) ? '[border-color:var(--accent-color,#6c5ce7)] [background:color-mix(in_srgb,var(--background-color,#ffffff)_84%,var(--accent-color,#6c5ce7)_16%)] [box-shadow:0_0_0_1px_var(--accent-color,#6c5ce7)]' : ''">
                                        <span
                                            class="flex min-w-0 items-center gap-3 text-sm font-semibold [color:color-mix(in_srgb,var(--text-color,#0d1b2a)_72%,transparent)]"
                                            x-bind:class="reportReason === @js($option['label']) ? '[color:var(--accent-color,#6c5ce7)]' : ''">
                                            <x-dynamic-component :component="$option['icon']" class="size-4 shrink-0" aria-hidden="true" />
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
                                    ['label' => 'Hate Speech', 'icon' => 'fas-face-frown'],
                                    ['label' => 'Harassment / Abuse', 'icon' => 'fas-bolt'],
                                    ['label' => 'Violence / Threats', 'icon' => 'fas-hand-fist'],
                                    ['label' => 'Self-Harm Risk', 'icon' => 'fas-heart-crack'],
                                ] as $option)
                                    <label
                                        class="flex cursor-pointer items-center justify-between gap-3 rounded-2xl border p-4 transition active:scale-[0.98] [border-color:color-mix(in_srgb,var(--text-color,#0d1b2a)_12%,transparent)] [background:var(--background-color,#ffffff)] hover:[background:color-mix(in_srgb,var(--background-color,#ffffff)_92%,var(--accent-color,#6c5ce7)_8%)]"
                                        x-bind:class="reportReason === @js($option['label']) ? '[border-color:var(--accent-color,#6c5ce7)] [background:color-mix(in_srgb,var(--background-color,#ffffff)_84%,var(--accent-color,#6c5ce7)_16%)] [box-shadow:0_0_0_1px_var(--accent-color,#6c5ce7)]' : ''">
                                        <span
                                            class="flex min-w-0 items-center gap-3 text-sm font-semibold [color:color-mix(in_srgb,var(--text-color,#0d1b2a)_72%,transparent)]"
                                            x-bind:class="reportReason === @js($option['label']) ? '[color:var(--accent-color,#6c5ce7)]' : ''">
                                            <x-dynamic-component :component="$option['icon']" class="size-4 shrink-0" aria-hidden="true" />
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
                                    ['label' => 'Illegal Activities', 'icon' => 'fas-gavel'],
                                    ['label' => 'Scams / Fraud', 'icon' => 'fas-ban'],
                                ] as $option)
                                    <label
                                        class="flex cursor-pointer items-center justify-between gap-3 rounded-2xl border p-4 transition active:scale-[0.98] [border-color:color-mix(in_srgb,var(--text-color,#0d1b2a)_12%,transparent)] [background:var(--background-color,#ffffff)] hover:[background:color-mix(in_srgb,var(--background-color,#ffffff)_92%,var(--accent-color,#6c5ce7)_8%)]"
                                        x-bind:class="reportReason === @js($option['label']) ? '[border-color:var(--accent-color,#6c5ce7)] [background:color-mix(in_srgb,var(--background-color,#ffffff)_84%,var(--accent-color,#6c5ce7)_16%)] [box-shadow:0_0_0_1px_var(--accent-color,#6c5ce7)]' : ''">
                                        <span
                                            class="flex min-w-0 items-center gap-3 text-sm font-semibold [color:color-mix(in_srgb,var(--text-color,#0d1b2a)_72%,transparent)]"
                                            x-bind:class="reportReason === @js($option['label']) ? '[color:var(--accent-color,#6c5ce7)]' : ''">
                                            <x-dynamic-component :component="$option['icon']" class="size-4 shrink-0" aria-hidden="true" />
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
                                <label for="report-details-{{ $uid }}"
                                    class="text-sm font-semibold [color:var(--text-color,#0d1b2a)]">Provide
                                    Additional Details (Optional)</label>
                                <span class="text-xs font-semibold [color:color-mix(in_srgb,var(--text-color,#0d1b2a)_45%,transparent)]"
                                    x-bind:class="reportDetailsCount() >= 560 ? '[color:var(--accent-color,#6c5ce7)]' : ''"
                                    x-text="`${reportDetailsCount()} / 600`">0 / 600</span>
                            </div>
                            <textarea id="report-details-{{ $uid }}" name="details" maxlength="600" rows="4"
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
                            <x-fas-paper-plane class="size-3" aria-hidden="true" />
                            <span>Submit Report</span>
                        </button>
                    </div>
                </form>
            </div>
        </template>
    @endif
@endauth
