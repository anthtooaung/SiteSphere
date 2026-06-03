@desktop
<button
    id="notificationDropdownButton"
    type="button"
    data-dropdown-toggle="notificationDropdown"
    {{ $attributes->merge(['class' => 'noti-button']) }}
    aria-label="{{ $unreadCount > 0 ? $unreadCount.' unread notifications' : 'Notifications' }}"
>
    <x-far-bell class="icon"/>
    @if ($unreadCount > 0)
        <span class="noti-badge">{{ $unreadCount }}</span>
    @endif
</button>
<div id="notificationDropdown" class="noti-dropdown hidden" aria-labelledby="notificationDropdownButton">
    <div class="noti-dropdown-header">Notifications</div>

    @if ($unreadNotifications->isEmpty())
        <p class="noti-empty">No unread notifications</p>
    @else
        <div class="noti-list">
            @foreach ($unreadNotifications as $notification)
                @if ($notification->target_type === 'posts')
                    <form method="POST" action="{{ route('notifications.open', $notification) }}" class="noti-form">
                        @csrf
                        <button type="submit" class="noti-item noti-item-action">
                            <span class="noti-message">{{ $notification->message }}</span>
                            @if ($notification->created_at)
                                <span class="noti-time">{{ $notification->created_at->diffForHumans() }}</span>
                            @endif
                        </button>
                    </form>
                @else
                    <div class="noti-item">
                        <span class="noti-message">{{ $notification->message }}</span>
                        @if ($notification->created_at)
                            <span class="noti-time">{{ $notification->created_at->diffForHumans() }}</span>
                        @endif
                    </div>
                @endif
            @endforeach
        </div>
    @endif
</div>
@enddesktop

@mobile
<a
    href="#"
    {{ $attributes->merge(['class' => 'mobile-nav-item relative']) }}
    aria-label="{{ $unreadCount > 0 ? $unreadCount.' unread notifications' : 'Notifications' }}"
>
    <x-far-bell class="icon"/>
    @if ($unreadCount > 0)
        <span class="mobile-badge">{{ $unreadCount }}</span>
    @endif
    <span>Alerts</span>
</a>
@endmobile
