@extends('dashboard')

@section('title')
    Notifications
@endsection

@section('content')
    @php
        $dashboardMenuLocation = in_array($menuBarLocation ?? 'left', ['top', 'right', 'bottom', 'left'], true)
            ? $menuBarLocation
            : 'left';
    @endphp

    <x-layout.nav />

    <div class="dashboard-page dashboard-page--{{ $dashboardMenuLocation }} notifications-page">
        <x-layout.menu :menu-bar-location="$dashboardMenuLocation" />

        <main class="dashboard-content notifications-content" aria-labelledby="notificationsTitle">
            <section class="notifications-main-app" data-notifications-page>
                <header class="notifications-header">
                    <div class="notifications-title-group">
                        <p class="dashboard-kicker">Activity</p>
                        <h1 id="notificationsTitle">Notifications</h1>
                        <p>View all your notifications including read and unread ones.</p>
                    </div>
                    @if ($unreadCount > 0)
                        <form method="POST" action="{{ route('notifications.mark-all-read') }}" class="m-0">
                            @csrf
                            <button type="submit" class="notifications-mark-all-btn">
                                <x-fas-check-double class="size-4" />
                                Mark all read
                            </button>
                        </form>
                    @endif
                </header>

                <form method="GET" action="{{ route('notifications.index') }}" class="notifications-toolbar" data-notifications-filter-form>
                    <label class="notifications-search">
                        <x-fas-search class="notifications-search-icon" aria-hidden="true" />
                        <span class="sr-only">Search notifications</span>
                        <input type="search" name="search" value="{{ $search }}"
                            placeholder="Search notifications..." data-notifications-search>
                    </label>

                    <div class="notifications-filter-buttons">
                        <a href="{{ route('notifications.index', ['filter' => 'all', 'search' => $search]) }}"
                            class="notifications-filter-btn {{ $filter === 'all' ? 'active' : '' }}">
                            All
                        </a>
                        <a href="{{ route('notifications.index', ['filter' => 'unread', 'search' => $search]) }}"
                            class="notifications-filter-btn {{ $filter === 'unread' ? 'active' : '' }}">
                            Unread
                        </a>
                        <a href="{{ route('notifications.index', ['filter' => 'read', 'search' => $search]) }}"
                            class="notifications-filter-btn {{ $filter === 'read' ? 'active' : '' }}">
                            Read
                        </a>
                    </div>
                </form>

                <div class="notifications-meta-row">
                    <span>
                        Showing {{ $notifications->firstItem() ?? 0 }}-{{ $notifications->lastItem() ?? 0 }} of {{ $notifications->total() }} notifications
                    </span>
                    <span>
                        {{ $unreadCount }} unread
                    </span>
                </div>

                @if ($notifications->isEmpty())
                    <section class="notifications-empty">
                        <x-far-bell-slash class="notifications-empty-icon" aria-hidden="true" />
                        @if ($search !== '')
                            <h2>No notifications found</h2>
                            <p>No notifications match your search "{{ $search }}".</p>
                            <a href="{{ route('notifications.index', ['filter' => $filter]) }}" class="notifications-filter-btn">Clear search</a>
                        @elseif ($filter === 'unread')
                            <h2>No unread notifications</h2>
                            <p>You're all caught up!</p>
                            <a href="{{ route('notifications.index') }}" class="notifications-filter-btn">View all</a>
                        @elseif ($filter === 'read')
                            <h2>No read notifications</h2>
                            <p>You haven't read any notifications yet.</p>
                            <a href="{{ route('notifications.index') }}" class="notifications-filter-btn">View all</a>
                        @else
                            <h2>No notifications yet</h2>
                            <p>When you receive notifications, they will appear here.</p>
                        @endif
                    </section>
                @else
                    <div class="notifications-list" data-notifications-list>
                        @foreach ($notifications as $notification)
                            @php
                                $isUnread = ! $notification->is_read;
                            @endphp
                            <form method="POST" action="{{ route('notifications.open', $notification) }}" class="notifications-item-form">
                                @csrf
                                <button type="submit" class="notifications-item {{ $isUnread ? 'notifications-item--unread' : 'notifications-item--read' }}">
                                    <div class="notifications-item-icon">
                                        @if ($isUnread)
                                            <span class="notifications-unread-dot" aria-label="Unread"></span>
                                        @else
                                            <x-fas-check class="size-4 opacity-40" aria-hidden="true" />
                                        @endif
                                    </div>
                                    <div class="notifications-item-body">
                                        <span class="notifications-item-message">{{ $notification->message }}</span>
                                        @if ($notification->created_at)
                                            <span class="notifications-item-time">{{ $notification->created_at->diffForHumans() }}</span>
                                        @endif
                                    </div>
                                    <div class="notifications-item-action">
                                        <x-fas-chevron-right class="size-4 opacity-40" aria-hidden="true" />
                                    </div>
                                </button>
                            </form>
                        @endforeach
                    </div>

                    @if ($notifications->hasPages())
                        <div class="notifications-pagination">
                            <span>Page {{ $notifications->currentPage() }} of {{ $notifications->lastPage() }}</span>
                            <div style="display: flex; gap: 8px;">
                                @if ($notifications->onFirstPage())
                                    <button disabled class="notifications-pagination-btn" style="opacity: 0.5; cursor: not-allowed;">Previous</button>
                                @else
                                    <a href="{{ $notifications->previousPageUrl() }}" class="notifications-pagination-btn">Previous</a>
                                @endif

                                @if ($notifications->hasMorePages())
                                    <a href="{{ $notifications->nextPageUrl() }}" class="notifications-pagination-btn">Next</a>
                                @else
                                    <button disabled class="notifications-pagination-btn" style="opacity: 0.5; cursor: not-allowed;">Next</button>
                                @endif
                            </div>
                        </div>
                    @endif
                @endif
            </section>
        </main>
    </div>
@endsection

@push('styles')
    <style>
        .notifications-page {
            --noti-accent: var(--accent-color, #6c5ce7);
        }

        .notifications-content {
            padding: 1.5rem;
            overflow-y: auto;
            height: calc(100vh - 60px);
        }

        .notifications-main-app {
            max-width: 800px;
            margin: 0 auto;
        }

        .notifications-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 1rem;
            margin-bottom: 1.5rem;
        }

        .notifications-title-group p:first-of-type {
            color: color-mix(in srgb, var(--text-color) 60%, transparent);
            font-size: 0.75rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            margin: 0 0 0.25rem;
        }

        .notifications-title-group h1 {
            font-size: 1.5rem;
            font-weight: 900;
            margin: 0 0 0.25rem;
            color: var(--text-color);
        }

        .notifications-title-group p:last-of-type {
            color: color-mix(in srgb, var(--text-color) 60%, transparent);
            font-size: 0.85rem;
            margin: 0;
        }

        .notifications-mark-all-btn {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 8px 16px;
            border: 1px solid color-mix(in srgb, var(--text-color) 15%, transparent);
            border-radius: 8px;
            background: transparent;
            color: var(--text-color);
            font-size: 0.8rem;
            font-weight: 700;
            cursor: pointer;
            white-space: nowrap;
            transition: all 180ms ease;
        }

        .notifications-mark-all-btn:hover {
            background: color-mix(in srgb, var(--noti-accent) 10%, var(--background-color));
            border-color: var(--noti-accent);
            color: var(--noti-accent);
        }

        .notifications-toolbar {
            display: flex;
            gap: 1rem;
            align-items: center;
            margin-bottom: 1rem;
            flex-wrap: wrap;
        }

        .notifications-search {
            display: flex;
            align-items: center;
            gap: 8px;
            flex: 1;
            min-width: 200px;
            padding: 8px 12px;
            border: 1px solid color-mix(in srgb, var(--text-color) 15%, transparent);
            border-radius: 8px;
            background: var(--background-color);
        }

        .notifications-search-icon {
            width: 16px;
            height: 16px;
            opacity: 0.5;
        }

        .notifications-search input {
            flex: 1;
            border: none;
            background: transparent;
            color: var(--text-color);
            font-size: 0.85rem;
            outline: none;
        }

        .notifications-search input::placeholder {
            color: color-mix(in srgb, var(--text-color) 40%, transparent);
        }

        .notifications-filter-buttons {
            display: flex;
            gap: 4px;
        }

        .notifications-filter-btn {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 8px 14px;
            border: 1px solid color-mix(in srgb, var(--text-color) 15%, transparent);
            border-radius: 8px;
            background: transparent;
            color: color-mix(in srgb, var(--text-color) 70%, transparent);
            font-size: 0.8rem;
            font-weight: 700;
            text-decoration: none;
            cursor: pointer;
            transition: all 180ms ease;
        }

        .notifications-filter-btn:hover {
            background: color-mix(in srgb, var(--noti-accent) 10%, var(--background-color));
            color: var(--text-color);
        }

        .notifications-filter-btn.active {
            background: color-mix(in srgb, var(--noti-accent) 12%, var(--background-color));
            border-color: var(--noti-accent);
            color: var(--noti-accent);
        }

        .notifications-meta-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1rem;
            padding: 0 2px;
            color: color-mix(in srgb, var(--text-color) 55%, transparent);
            font-size: 0.78rem;
            font-weight: 600;
        }

        .notifications-empty {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 1rem;
            padding: 4rem 2rem;
            text-align: center;
            color: color-mix(in srgb, var(--text-color) 50%, transparent);
        }

        .notifications-empty-icon {
            width: 64px;
            height: 64px;
            opacity: 0.4;
        }

        .notifications-empty h2 {
            font-size: 1.1rem;
            font-weight: 900;
            margin: 0;
            color: var(--text-color);
        }

        .notifications-empty p {
            font-size: 0.85rem;
            margin: 0;
        }

        .notifications-list {
            display: flex;
            flex-direction: column;
            gap: 6px;
        }

        .notifications-item-form {
            margin: 0;
        }

        .notifications-item {
            display: flex;
            align-items: center;
            gap: 12px;
            width: 100%;
            padding: 12px 14px;
            border: 1px solid color-mix(in srgb, var(--text-color) 8%, transparent);
            border-radius: 10px;
            background: var(--background-color);
            color: var(--text-color);
            font: inherit;
            text-align: left;
            cursor: pointer;
            transition: all 180ms ease;
        }

        .notifications-item:hover {
            background: color-mix(in srgb, var(--noti-accent) 6%, var(--background-color));
            border-color: color-mix(in srgb, var(--noti-accent) 20%, var(--background-color));
            transform: translateX(2px);
        }

        .notifications-item--unread {
            border-left: 3px solid var(--noti-accent);
            background: color-mix(in srgb, var(--noti-accent) 4%, var(--background-color));
        }

        .notifications-item-icon {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 32px;
            height: 32px;
            flex-shrink: 0;
        }

        .notifications-unread-dot {
            width: 10px;
            height: 10px;
            border-radius: 50%;
            background: var(--noti-accent);
            box-shadow: 0 0 0 3px color-mix(in srgb, var(--noti-accent) 20%, transparent);
        }

        .notifications-item-body {
            flex: 1;
            min-width: 0;
            display: flex;
            flex-direction: column;
            gap: 2px;
        }

        .notifications-item-message {
            font-size: 0.85rem;
            font-weight: 700;
            line-height: 1.4;
            color: var(--text-color);
        }

        .notifications-item--read .notifications-item-message {
            font-weight: 600;
            opacity: 0.75;
        }

        .notifications-item-time {
            font-size: 0.72rem;
            font-weight: 600;
            color: color-mix(in srgb, var(--text-color) 50%, transparent);
        }

        .notifications-item-action {
            flex-shrink: 0;
            opacity: 0;
            transition: opacity 180ms ease;
        }

        .notifications-item:hover .notifications-item-action {
            opacity: 1;
        }

        .notifications-pagination {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 1.5rem;
            padding-top: 1rem;
            border-top: 1px solid color-mix(in srgb, var(--text-color) 10%, transparent);
            color: color-mix(in srgb, var(--text-color) 55%, transparent);
            font-size: 0.78rem;
            font-weight: 600;
        }

        .notifications-pagination-btn {
            display: inline-flex;
            align-items: center;
            padding: 6px 14px;
            border: 1px solid color-mix(in srgb, var(--text-color) 15%, transparent);
            border-radius: 6px;
            background: transparent;
            color: var(--text-color);
            font-size: 0.78rem;
            font-weight: 700;
            text-decoration: none;
            cursor: pointer;
            transition: all 180ms ease;
        }

        .notifications-pagination-btn:hover {
            background: color-mix(in srgb, var(--noti-accent) 10%, var(--background-color));
            border-color: var(--noti-accent);
            color: var(--noti-accent);
        }

        /* Mobile responsive */
        @media (max-width: 900px) {
            .notifications-content {
                padding: 1rem;
                height: auto;
            }

            .notifications-header {
                flex-direction: column;
            }

            .notifications-toolbar {
                flex-direction: column;
                align-items: stretch;
            }

            .notifications-search {
                min-width: unset;
            }

            .notifications-filter-buttons {
                justify-content: stretch;
            }

            .notifications-filter-btn {
                flex: 1;
                justify-content: center;
            }
        }
    </style>
@endpush
