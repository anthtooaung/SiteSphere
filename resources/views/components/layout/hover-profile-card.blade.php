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
            <h2>
                {{ $cardUser->name }}
                @if($cardUser->isUnsecure())
                    <span class="unsecure-badge" title="Unsecure Account" style="display: inline-flex; align-items: center; gap: 4px; padding: 2px 8px; background: color-mix(in srgb, #ffc107 20%, transparent); color: #ffc107; border: 1px solid color-mix(in srgb, #ffc107 30%, transparent); border-radius: 12px; font-size: 11px; font-weight: 500; vertical-align: middle;">
                        <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-alert-triangle"><path d="m21.73 18-8-14a2 2 0 0 0-3.48 0l-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.73-3Z"/><path d="M12 9v4"/><path d="M12 17h.01"/></svg>
                        Unsecure
                    </span>
                @endif
            </h2>
            <p>{{ $role }}</p>
        </div>
    </div>

    <div class="info-list">
        <div class="info-item">
            <div class="left">
                <i class="fa-solid fa-envelope"></i>
                <span>Email</span>
            </div>
            <div class="right email">{{ $cardUser->email }}</div>
        </div>

        @if($cardUser->user_phone)
            <div class="info-item">
                <div class="left">
                    <i class="fa-solid fa-phone"></i>
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
                        <i class="fa-solid fa-cloud-arrow-up"></i>
                        {{ $uploadsCount }}
                    </strong>
                    <span>Uploads</span>
                </div>
            </div>

            <div class="stat-item">
                <div>
                    <strong>
                        <i class="fa-solid fa-star"></i>
                        {{ $averageRating }}
                    </strong>
                    <span>Rating</span>
                </div>
            </div>
        </div>
    </div>

    <a href="{{ route('profile-detail', ['slug' => $cardUser->slug]) }}" class="message-btn" id="messageBtn" style="text-decoration: none; display: inline-block;">View Profile</a>
</div>
