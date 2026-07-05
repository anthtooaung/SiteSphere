<div class="hover-profile-card">
    <div class="top-section">
        <div class="profile-img">
            @if($cardUser->user_image)
                <img src="{{ $cardUser->getAvatarUrl() }}" alt="{{ $cardUser->name }}">
            @else
                <div class="profile-img-placeholder" style="width: 100%; height: 100%; display: flex; align-items: center; justify-content: center; background: color-mix(in srgb, var(--background-color, #ffffff) 80%, var(--accent-color, #6c5ce7) 20%); color: var(--accent-color, #6c5ce7); font-size: 20px; font-weight: bold;">
                    <span>{{ Str::of($cardUser->name)->substr(0, 1)->upper() }}</span>
                </div>
            @endif
        </div>

        <div class="user-info">
            <h2 title="{{ $cardUser->name }}">
                {{ $cardUser->name }}
                @if ($cardUser->isUnsecure())
                    <span style="display: inline-block; padding: 2px 6px; border-radius: 4px; font-size: 10px; font-weight: 600; background: color-mix(in srgb, #d97706 15%, transparent); color: #d97706; vertical-align: middle;">Unsecure</span>
                @endif
            </h2>
            <p>{{ $role }}</p>
        </div>
    </div>

    <div class="info-list">
        <div class="info-item">
            <div class="left">
                <x-fas-envelope class="size-4" />
                <span>Email</span>
            </div>
            <div class="right email">{{ $maskedEmail ?? $cardUser->email }}</div>
        </div>

        @if($cardUser->user_phone)
            <div class="info-item">
                <div class="left">
                    <x-fas-phone class="size-4" />
                    <span>Phone</span>
                </div>
                <div class="right">{{ $cardUser->user_phone }}</div>
            </div>
        @endif
    </div>

    <div class="contribution-stats">
        <div class="stats-list">
            <div class="stat-item">
                <div>
                    <strong>
                        <x-fas-cloud-arrow-up class="size-4" />
                        {{ $uploadsCount }}
                    </strong>
                    <span>Uploads</span>
                </div>
            </div>

            <div class="stat-item">
                <div>
                    <strong>
                        <x-fas-star class="size-4" />
                        {{ $averageRating }}
                    </strong>
                    <span>Rating</span>
                </div>
            </div>
        </div>
    </div>

    <a href="{{ route('profile-detail', ['slug' => $cardUser->slug]) }}" class="message-btn" id="messageBtn" style="text-decoration: none; display: inline-block;">View Profile</a>
</div>
