@props([
    'trigger' => 'bottom',
    'mobileMode' => 'both',
])
@php
    $isLanding = request()->routeIs(['welcome', 'about-us']);
@endphp

@desktop
<button
    id="notificationDropdownButton"
    type="button"
    data-dropdown-toggle="notificationDropdown"
    {{ $attributes->merge(['class' => 'noti-button']) }}
    aria-label="{{ $unreadCount > 0 ? $unreadCount.' unread notifications' : 'Notifications' }}"
    style="font-family: var(--font-family);"
>
    <x-far-bell class="icon"/>
    @if ($unreadCount > 0)
        <span class="noti-badge">{{ $unreadCount }}</span>
    @endif
</button>
<div id="notificationDropdown" class="noti-dropdown hidden" aria-labelledby="notificationDropdownButton" style="font-family: var(--font-family); color: var(--text-color); background-color: var(--background-color);">
    <div class="noti-dropdown-header" style="font-family: var(--font-family); color: var(--text-color); display: flex; justify-content: space-between; align-items: center;">
        <span>Notifications</span>
        @if ($unreadNotifications->isNotEmpty())
            <form method="POST" action="{{ route('notifications.mark-all-read') }}" class="m-0">
                @csrf
                <button type="submit" class="text-xs font-medium opacity-60 hover:opacity-100 transition-opacity" style="color: var(--text-color);" title="Mark all as read">
                    Mark all read
                </button>
            </form>
        @endif
    </div>

    @if ($unreadNotifications->isEmpty())
        <p class="noti-empty" style="font-family: var(--font-family); color: var(--text-color);">No unread notifications</p>
    @else
        <div class="noti-list" style="font-family: var(--font-family);">
            @foreach ($unreadNotifications as $notification)
                @if ($notification->target_type === 'posts')
                    <form method="POST" action="{{ route('notifications.open', $notification) }}" class="noti-form">
                        @csrf
                        <button type="submit" class="noti-item noti-item-action" style="font-family: var(--font-family);">
                            <span class="noti-message" style="color: var(--text-color);">{{ $notification->message }}</span>
                            @if ($notification->created_at)
                                <span class="noti-time">{{ $notification->created_at->diffForHumans() }}</span>
                            @endif
                        </button>
                    </form>
                @else
                    <div class="noti-item" style="font-family: var(--font-family);">
                        <span class="noti-message" style="color: var(--text-color);">{{ $notification->message }}</span>
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
@php
    $btnClass = $trigger === 'top' ? 'auth-menu-button relative !bg-transparent !border-transparent' : 'mobile-nav-item relative';
@endphp
@if (in_array($mobileMode, ['bottom', 'trigger'], true))
    <button
        type="button"
        {{ $attributes->class([
            $btnClass,
            'flex-row gap-2 px-3 py-2 font-bold text-sm' => $isLanding && $trigger === 'bottom',
            'flex-col' => !$isLanding && $trigger === 'bottom'
        ]) }}
        data-mobile-noti-open
        aria-label="{{ $unreadCount > 0 ? $unreadCount.' unread notifications' : 'Notifications' }}"
        style="font-family: var(--font-family); color: var(--text-color);"
    >
        <x-far-bell class="icon" style="color: var(--text-color);"/>
        @if ($unreadCount > 0)
            <span class="mobile-badge">{{ $unreadCount }}</span>
        @endif
        @if ($trigger === 'bottom')
            <span>Alerts</span>
        @endif
    </button>
@endif

@if (in_array($mobileMode, ['both', 'overlay'], true))
    <div class="mobile-menu-overlay category-mobile-overlay" id="mobileNotiOverlay" style="background-color: var(--background-color); color: var(--text-color); font-family: var(--font-family);">
        <div style="display: flex; justify-content: space-between; align-items: center; padding: 1rem;">
            <button
                type="button"
                class="mobile-close-button category-mobile-close"
                data-mobile-noti-close
                aria-label="Close notifications"
            >
                <x-fas-times class="size-8"/>
            </button>
            @if ($unreadNotifications->isNotEmpty())
                <form method="POST" action="{{ route('notifications.mark-all-read') }}" class="m-0">
                    @csrf
                    <button type="submit" class="text-sm font-medium opacity-60 hover:opacity-100 transition-opacity" style="color: var(--text-color);">
                        Mark all read
                    </button>
                </form>
            @endif
        </div>

        @forelse ($unreadNotifications as $notification)
            @if ($notification->target_type === 'posts')
                <form method="POST" action="{{ route('notifications.open', $notification) }}" class="m-0 w-full">
                    @csrf
                    <button type="submit" class="mobile-overlay-link w-full text-left !justify-start">
                        <x-far-bell class="icon size-8"/>
                        <div class="flex flex-col gap-0.5">
                            <span class="text-sm font-bold">{{ $notification->message }}</span>
                            @if ($notification->created_at)
                                <span class="text-[10px] opacity-60">{{ $notification->created_at->diffForHumans() }}</span>
                            @endif
                        </div>
                    </button>
                </form>
            @else
                <div class="mobile-overlay-link !justify-start">
                    <x-far-bell class="icon size-8"/>
                    <div class="flex flex-col gap-0.5">
                        <span class="text-sm font-bold">{{ $notification->message }}</span>
                        @if ($notification->created_at)
                            <span class="text-[10px] opacity-60">{{ $notification->created_at->diffForHumans() }}</span>
                        @endif
                    </div>
                </div>
            @endif
        @empty
            <div class="p-12 text-center opacity-60 flex flex-col items-center justify-center gap-4 w-full h-full">
                <x-far-bell-slash class="size-16" />
                <p class="text-xl font-black">No unread notifications</p>
                <p class="text-sm">You're all caught up!</p>
            </div>
        @endforelse
    </div>
@endif
@endmobile
