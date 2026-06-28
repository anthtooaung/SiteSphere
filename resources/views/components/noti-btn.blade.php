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
                <form method="POST" action="{{ route('notifications.open', $notification) }}" class="noti-form">
                    @csrf
                    <button type="submit" class="noti-item noti-item-action" style="font-family: var(--font-family);">
                        <span class="noti-message" style="color: var(--text-color);">{{ $notification->message }}</span>
                        @if ($notification->created_at)
                            <span class="noti-time">{{ $notification->created_at->diffForHumans() }}</span>
                        @endif
                    </button>
                </form>
            @endforeach
        </div>
    @endif
    <a href="{{ route('notifications.index') }}" class="noti-see-all" style="font-family: var(--font-family); color: var(--text-color);">
        See all notifications
    </a>
</div>
@enddesktop

@mobile
@php
    $btnClass = $trigger === 'top' ? 'auth-menu-button relative !bg-transparent !border-transparent' : 'mobile-nav-item relative';
    $showTrigger = in_array($mobileMode, ['bottom', 'trigger', 'both'], true);
    $showOverlay = in_array($mobileMode, ['overlay', 'both'], true);
@endphp
@if ($showTrigger)
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

@if ($showOverlay)
    <div class="mobile-menu-overlay category-mobile-overlay" id="mobileNotiOverlay"
        x-data="{
            search: '',
            notifications: @js($unreadNotifications->map(fn($n) => ['message' => $n->message, 'time' => $n->created_at?->diffForHumans()])->values()),
            matches(msg) {
                return msg.toLowerCase().includes(this.search.toLowerCase());
            },
            hasResults() {
                if (this.search === '') return this.notifications.length > 0;
                return this.notifications.some(n => this.matches(n.message));
            }
        }"
        style="background-color: var(--background-color); color: var(--text-color); font-family: var(--font-family);"
    >
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

        <div class="mobile-search">
            <div class="mobile-search-inner">
                <x-fas-search class="icon w-4 h-4"/>
                <input type="text" x-model="search" placeholder="Search notifications..." autocomplete="off" style="outline: none;">
            </div>
        </div>

        @foreach ($unreadNotifications as $notification)
            <form method="POST" action="{{ route('notifications.open', $notification) }}" class="m-0 w-full" x-show="search === '' || matches('{{ addslashes($notification->message) }}')">
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
        @endforeach

        <div x-show="!hasResults()" x-cloak class="p-12 text-center opacity-60 flex flex-col items-center justify-center gap-4 w-full h-full">
            <x-far-bell-slash class="size-16" />
            <p class="text-xl font-black" x-text="search === '' ? 'No unread notifications' : 'No notifications found'"></p>
            <p class="text-sm" x-text="search === '' ? 'You\'re all caught up!' : 'Try a different search term'"></p>
        </div>

        <div style="padding: 0.75rem 1rem;">
            <a href="{{ route('notifications.index') }}" class="mobile-overlay-link w-full text-center !justify-center" style="text-decoration: none;">
                <x-fas-eye class="icon size-5"/>
                <span class="text-sm font-bold">See all notifications</span>
            </a>
        </div>
    </div>
@endif
@endmobile
