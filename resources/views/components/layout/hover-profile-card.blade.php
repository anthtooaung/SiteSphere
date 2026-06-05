<div class="profile-card">
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
            <h2>{{ $cardUser->name }}</h2>
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

    <a href="{{ auth()->check() && auth()->id() === $cardUser->id ? route('profile-detail') : '#' }}" class="message-btn" id="messageBtn" style="text-decoration: none; display: inline-block;">View Profile</a>
</div>
