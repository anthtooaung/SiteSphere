@props([
    'post',
    'comments',
    'commentUserRatings' => collect(),
    'userRating' => 0,
])

<section class="aud-reviews" aria-label="User Reports">
    <div class="aud-depo-head">
        <div>
            <h2 class="aud-h2">User Reports</h2>
            <p class="aud-sub">Community experiences and ratings posted by users.</p>
        </div>
    </div>

    <!-- Review composer -->
    @auth
        <form class="aud-composer" id="reviewForm" method="POST" action="{{ route('posts.comments.store', $post->slug) }}">
            @csrf
            <input type="hidden" name="rating" id="ratingInput" value="{{ $userRating }}">

            <div class="aud-composer-top">
                <span
                    class="ss-avatar is-initial"
                    aria-hidden="true"
                    style="width: 32px; height: 32px; border-radius: 50%; --ph-hue: {{ (auth()->id() * 47) % 360 }};"
                >
                    <span class="ss-avatar-initials" style="font-size: 10.9px">
                        {{ collect(explode(' ', auth()->user()->name))->map(fn($n) => Str::substr($n, 0, 1))->join('') }}
                    </span>
                </span>

                <span class="aud-composer-label">Contribute your experience</span>

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
                    disabled
                >
                    Submit
                </button>
            </div>
        </form>
    @else
        <div class="aud-composer" style="text-align: center; padding: 24px; border: 1px solid var(--line); border-radius: var(--radius); background: var(--paper);">
            <p class="aud-sub" style="margin: 0 auto 12px;">You must be logged in to contribute your experience.</p>
            <a href="{{ route('login') }}" class="aud-submit" style="display: inline-block;">Login to Review</a>
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
            <article class="aud-row" data-review-id="comment-{{ $comment->id }}">
                <span
                    class="ss-avatar is-initial"
                    aria-hidden="true"
                    data-hover-profile="{{ $comment->user_id }}"
                    style="width: 34px; height: 34px; border-radius: 50%; --ph-hue: {{ $cHue }};"
                >
                    <span class="ss-avatar-initials" style="font-size: 11.6px">{{ $cInitials }}</span>
                </span>
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
                    </div>
                    <span class="aud-row-date ss-mono">{{ $comment->created_at->diffForHumans() }}</span>
                    <div class="ss-expandable" data-clamp="3">
                        <p class="ss-expandable-text" style="--clamp-lines: 3">
                            {!! nl2br(e($comment->content)) !!}
                        </p>
                        <p class="ss-expandable-ghost" aria-hidden="true">
                            {!! nl2br(e($comment->content)) !!}
                        </p>
                        <button type="button" class="ss-expand-btn" hidden>See more</button>
                    </div>
                    <div class="ss-helpful">
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
                                        <svg
                                            viewBox="0 0 24 24"
                                            width="14"
                                            height="14"
                                            fill="currentColor"
                                            style="display: block; flex-shrink: 0"
                                        >
                                            <path d="M14 9V5a3 3 0 0 0-3-3l-4 9v11h11.28a2 2 0 0 0 2-1.7l1.38-9a2 2 0 0 0-2-2.3H14z" />
                                            <path d="M7 22H4a2 2 0 0 1-2-2v-7a2 2 0 0 1 2-2h3" />
                                        </svg>
                                    </span>
                                    <span class="helpful-label">Helpful</span>
                                </button>
                            </span>
                        </span>
                    </div>
                </div>
            </article>
        @empty
            <div style="padding: 24px; text-align: center; border-top: 1px solid var(--line-2);">
                <p class="aud-sub">No user reports yet. Be the first to share your experience!</p>
            </div>
        @endforelse
    </div>
</section>
