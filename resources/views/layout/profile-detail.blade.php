@extends('dashboard')

@section('title')
    Profile Detail
@endsection

@push('styles')
    @vite('resources/css/profile-detail.css')
@endpush

@section('content')
    @php
        $dashboardMenuLocation = in_array($menuBarLocation ?? 'left', ['top', 'right', 'bottom', 'left'], true)
            ? $menuBarLocation
            : 'left';
    @endphp

    <x-layout.nav />

    <div @class([
        'dashboard-page',
        'dashboard-page--'.$dashboardMenuLocation,
        'dashboard-page--no-menu' => ! $isOwnProfile,
        'profile-detail-page',
    ])>
        @if ($isOwnProfile)
            <x-layout.menu :menu-bar-location="$dashboardMenuLocation" />
        @endif

        <main class="dashboard-content profile-detail-content" x-data="{
            expandedSection: null,
            reportOpen: false,
            reportReason: '',
            reportDetails: '',
            openReportModal() {
                this.reportOpen = true;
            },
            closeReportModal() {
                this.reportOpen = false;
                this.reportReason = '';
                this.reportDetails = '';
            },
            reportDetailsCount() { return this.reportDetails.length; }
        }">
            @if ($isBanned)
                <div class="banned-banner" style="position: sticky; top: -24px; margin: -24px -24px 24px -24px; z-index: 40;">
                    <div class="banned-banner-inner">
                        <div class="banned-banner-icon">
                            <x-fas-ban class="size-5" aria-hidden="true" />
                        </div>
                        <div class="banned-banner-content">
                            <div class="banned-banner-title">This user has been banned</div>
                            <div class="banned-banner-meta">
                                @if ($banLog && $banLog->user)
                                    Banned by <strong>{{ $banLog->user->name }}</strong>
                                    on <strong>{{ $banLog->created_at->format('M d, Y \a\t h:i A') }}</strong>
                                @endif
                            </div>
                            @if ($banLog && $banLog->reason)
                                <div class="banned-banner-reason">Reason: {{ $banLog->reason }}</div>
                            @endif
                        </div>
                        <div class="banned-banner-actions">
                            <form method="POST" action="{{ route('users.restore', $user->id) }}" style="display:inline;">
                                @csrf
                                @method('PATCH')
                                <button type="submit" class="banned-btn banned-btn-revert">
                                    <x-fas-rotate-left class="size-4" /> Revert
                                </button>
                            </form>
                            <form method="POST" action="{{ route('users.force-delete', $user->id) }}" style="display:inline;"
                                x-data x-on:submit.prevent="window.sitesphereSwal.confirm({
                                    title: 'Delete Permanently?',
                                    text: 'This action cannot be undone. The user and all their data will be permanently removed.',
                                    icon: 'warning',
                                    confirmButtonColor: 'var(--ui-danger)',
                                    cancelButtonColor: '#6c757d',
                                    confirmButtonText: 'Yes, delete forever!'
                                }).then((result) => { if (result.isConfirmed) $el.submit(); })">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="banned-btn banned-btn-delete">
                                    <x-fas-trash class="size-4" /> Delete Permanently
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Background Blur -->
            <div class="bg-blur blur1"></div>
            <div class="bg-blur blur2"></div>

            <div class="profile-container">

                <!-- Main Profile Card -->
                <div class="profile-card">
                    @if ($user->id === auth()->id())
                        <a href="{{ route('edit-profile') }}" class="edit-btn" style="text-decoration: none;">
                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-pencil"><path d="M17 3a2.85 2.83 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5Z"/><path d="m15 5 4 4"/></svg>
                            Edit
                        </a>
                    @else
                        @auth
                            <div class="relative shrink-0" x-data="{ actionsOpen: false }" x-on:click.outside="actionsOpen = false" style="position: absolute; right: 20px; top: 20px; z-index: 20;">
                                <button type="button"
                                    class="flex size-9 shrink-0 items-center justify-center rounded-full border [border-color:var(--border)] [background:var(--card)] [color:var(--muted)] transition-all hover:[border-color:var(--accent-1)] hover:[background:color-mix(in_srgb,var(--accent-1)_10%,transparent)] hover:[color:var(--accent-1)]"
                                    aria-label="More options" aria-haspopup="menu" x-on:click.stop="actionsOpen = ! actionsOpen"
                                    x-on:keydown.escape.window="actionsOpen = false" x-bind:aria-expanded="actionsOpen.toString()">
                                    <x-fas-ellipsis class="size-4" aria-hidden="true" />
                                </button>

                                <div x-cloak x-show="actionsOpen" x-transition:enter="transition ease-out duration-200 origin-top-right"
                                    x-transition:enter-start="opacity-0 -translate-y-1 scale-95"
                                    x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                                    x-transition:leave="transition ease-in duration-150 origin-top-right"
                                    x-transition:leave-start="opacity-100 translate-y-0 scale-100"
                                    x-transition:leave-end="opacity-0 -translate-y-1 scale-95"
                                    class="absolute right-0 top-11 w-48 overflow-hidden rounded-lg border p-2 text-sm font-bold [border-color:color-mix(in_srgb,var(--accent-color,#6c5ce7)_20%,var(--background-color,#ffffff))] [background:var(--background-color,#ffffff)] [box-shadow:0_16px_36px_color-mix(in_srgb,var(--text-color,#0d1b2a)_18%,transparent)]"
                                    role="menu">
                                    
                                    @if (Auth::user()?->role === 'admin')
                                        <form method="POST" action="{{ route('users.toggle-unsecure', $user->id) }}">
                                            @csrf
                                            <button type="submit"
                                                class="flex min-h-9 w-full items-center gap-2 rounded-lg px-2.5 py-1.5 text-left transition-all duration-[180ms] {{ $user->isUnsecure() ? 'text-green-600 hover:[background:color-mix(in_srgb,#16a34a_12%,transparent)]' : '[color:#d97706] hover:[background:color-mix(in_srgb,#d97706_12%,transparent)]' }} focus:outline-none focus-visible:translate-x-0.5 focus-visible:ring-2"
                                                role="menuitem">
                                                <x-fas-shield-halved class="size-3" aria-hidden="true" />
                                                <span>{{ $user->isUnsecure() ? 'Mark Secure' : 'Mark Unsecure' }}</span>
                                            </button>
                                        </form>

                                        <form method="POST" action="{{ route('users.destroy', $user) }}"
                                            class="mt-1 border-t pt-1 [border-color:color-mix(in_srgb,var(--text-color,#0d1b2a)_10%,transparent)]"
                                            x-data x-on:submit.prevent="window.sitesphereSwal.confirm({
                                                title: 'Ban this user?',
                                                text: 'This will ban and soft-delete the user account.',
                                                icon: 'warning',
                                                input: 'text',
                                                inputPlaceholder: 'Enter reason for banning...',
                                                confirmButtonColor: 'var(--ui-danger)',
                                                cancelButtonColor: '#6c757d',
                                                confirmButtonText: 'Yes, ban user!',
                                                inputValidator: (value) => {
                                                    if (!value) {
                                                        return 'You need to provide a reason!'
                                                    }
                                                }
                                            }).then((result) => {
                                                if (result.isConfirmed) {
                                                    let input = document.createElement('input');
                                                    input.type = 'hidden';
                                                    input.name = 'reason';
                                                    input.value = result.value;
                                                    $el.appendChild(input);
                                                    $el.submit();
                                                }
                                            })">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                class="flex min-h-9 w-full items-center gap-2 rounded-lg px-2.5 py-1.5 text-left transition-all duration-[180ms] [color:var(--ui-danger)] hover:translate-x-0.5 hover:[background:color-mix(in_srgb,var(--ui-danger)_12%,transparent)] focus:outline-none"
                                                role="menuitem">
                                                <x-fas-ban class="size-3" aria-hidden="true" />
                                                <span>Ban User</span>
                                            </button>
                                        </form>
                                    @else
                                        <button type="button"
                                            class="flex min-h-9 w-full items-center gap-2 rounded-lg px-2.5 py-1.5 text-left transition-all duration-[180ms] [color:color-mix(in_srgb,var(--text-color,#0d1b2a)_78%,transparent)] hover:translate-x-0.5 hover:[background:color-mix(in_srgb,var(--accent-color,#6c5ce7)_12%,transparent)] hover:[color:var(--accent-color,#6c5ce7)] focus:outline-none"
                                            role="menuitem"
                                            x-on:click="actionsOpen = false; openReportModal()">
                                            <x-fas-flag class="size-3" aria-hidden="true" />
                                            <span>Report</span>
                                        </button>
                                    @endif
                                </div>
                            </div>
                        @endauth
                    @endif

                    <div class="profile-content">
                        <!-- Left -->
                        <div class="left-section">
                            @if($user->user_image)
                                <img class="profile-img" src="{{ $user->getAvatarUrl() }}" alt="{{ $user->name }}">
                            @else
                                <div class="profile-img-placeholder">
                                    <span>{{ Str::of($user->name)->substr(0, 1)->upper() }}</span>
                                </div>
                            @endif

                            <div class="user-info">
                                <div class="name-row">
                                    <h2>{{ $user->name }}</h2>
                                    @if($user->is_verified && !$user->isUnsecure())
                                        <span class="verified" title="Verified Account">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-badge-check"><path d="M3.85 8.62a4 4 0 0 1 4.78-4.77 4 4 0 0 1 6.74 0 4 4 0 0 1 4.78 4.78 4 4 0 0 1 0 6.74 4 4 0 0 1-4.77 4.78 4 4 0 0 1-6.75 0 4 4 0 0 1-4.78-4.77 4 4 0 0 1 0-6.76Z"/><path d="m9 12 2 2 4-4"/></svg>
                                        </span>
                                    @endif
                                    @if($user->isUnsecure())
                                        <span class="unsecure-badge" title="Unsecure Account (Reported {{ $user->report_count }} times)" style="display: inline-flex; align-items: center; gap: 4px; padding: 2px 8px; background: color-mix(in srgb, #ffc107 20%, transparent); color: #ffc107; border: 1px solid color-mix(in srgb, #ffc107 30%, transparent); border-radius: 12px; font-size: 12px; font-weight: 500;">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-alert-triangle"><path d="m21.73 18-8-14a2 2 0 0 0-3.48 0l-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.73-3Z"/><path d="M12 9v4"/><path d="M12 17h.01"/></svg>
                                            Unsecure
                                        </span>
                                    @endif
                                </div>

                                <p>
                                    {{ $user->user_bio ?? 'Passionate reviewer and community member of SiteSphere.' }}
                                </p>

                                <div class="social-icons">
                                    <a href="mailto:{{ $user->email }}" aria-label="Email">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-mail"><rect width="20" height="16" x="2" y="4" rx="2"/><path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"/></svg>
                                    </a>
                                    @if($user->user_phone)
                                        <a href="tel:{{ $user->user_phone }}" aria-label="Phone">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-phone"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
                                        </a>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <!-- Right -->
                        <div class="right-section">
                            <div class="info-grid">
                                <div class="info-item">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-mail"><rect width="20" height="16" x="2" y="4" rx="2"/><path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"/></svg>
                                    <div>
                                        <span>Email</span>
                                        <h4>{{ $user->email }}</h4>
                                    </div>
                                </div>

                                <div class="info-item">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-phone"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
                                    <div>
                                        <span>Phone</span>
                                        <h4>{{ $user->user_phone ? '+95'.$user->user_phone : 'Not specified' }}</h4>
                                    </div>
                                </div>

                                <div class="info-item">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-calendar"><path d="M8 2v4"/><path d="M16 2v4"/><rect width="18" height="18" x="3" y="4" rx="2"/><path d="M3 10h18"/></svg>
                                    <div>
                                        <span>Date of Birth</span>
                                        <h4>{{ $user->user_dob ? \Carbon\Carbon::parse($user->user_dob)->format('d F Y') : 'Not specified' }}</h4>
                                    </div>
                                </div>

                                <div class="info-item">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-clock"><circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/></svg>
                                    <div>
                                        <span>Joined</span>
                                        <h4>{{ $user->created_at ? $user->created_at->format('d F Y') : 'Not specified' }}</h4>
                                    </div>
                                </div>
                            </div>

                            <div class="login-status">
                                Last Login: Today, {{ now()->format('h:i A') }}
                                <span class="online-dot"></span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Stats Grid -->
                <div class="stats-grid">
                    <div class="stat-card" :class="expandedSection === 'reviews' ? 'active' : ''">
                        <span class="stat-icon blue">
                            <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-message-square-text"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/><path d="M13 8H7"/><path d="M17 12H7"/></svg>
                        </span>
                        <div>
                            <h2>{{ $reviewsCount }}</h2>
                            <p>My Reviews</p>
                            <button @click="expandedSection = expandedSection === 'reviews' ? null : 'reviews'" class="bottom-link">
                                <span x-text="expandedSection === 'reviews' ? 'Collapse ↑' : 'View all reviews &rarr;'"></span>
                            </button>
                        </div>
                    </div>

                    <div class="stat-card">
                        <span class="stat-icon gold">
                            <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-star"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
                        </span>
                        <div>
                            <h2>{{ $ratingsCount }}</h2>
                            <p>Rate Items</p>
                            <span class="bottom-link" style="cursor: default; opacity: 0.7;">Total Rated</span>
                        </div>
                    </div>

                    <div class="stat-card" :class="expandedSection === 'uploads' ? 'active' : ''">
                        <span class="stat-icon green">
                            <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-cloud-upload"><path d="M4 14.899A7 7 0 1 1 15.71 8h1.79a4.5 4.5 0 0 1 2.5 8.242"/><path d="M12 13v8"/><path d="m8 17 4-4 4 4"/></svg>
                        </span>
                        <div>
                            <h2>{{ $uploadsCount }}</h2>
                            <p>My Uploads</p>
                            <button @click="expandedSection = expandedSection === 'uploads' ? null : 'uploads'" class="bottom-link">
                                <span x-text="expandedSection === 'uploads' ? 'Collapse ↑' : 'View all uploads &rarr;'"></span>
                            </button>
                        </div>
                    </div>

                    <div class="stat-card">
                        <span class="stat-icon purple">
                            <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-award"><circle cx="12" cy="8" r="6"/><path d="M15.477 12.89 17 21.416a.5.5 0 0 1-.81.47l-3.58-2.687a1 1 0 0 0-1.197 0l-3.586 2.686a.5.5 0 0 1-.81-.469l1.514-8.526"/></svg>
                        </span>
                        <div>
                            <h2>{{ number_format($averageRating, 1) }}</h2>
                            <p>Rating Received</p>
                            <span class="bottom-link" style="cursor: default; opacity: 0.7;">Lifetime Average</span>
                        </div>
                    </div>
                </div>

                <!-- Expansion Panels -->
                <div class="expansion-container" x-show="expandedSection" x-collapse x-cloak>
                    <!-- Reviews Panel -->
                    <div x-show="expandedSection === 'reviews'" class="expansion-panel">
                        <div class="panel-header">
                            <h3>My Reviews</h3>
                            <span class="count-pill">{{ $reviewsCount }} Items</span>
                        </div>
                        <div class="dense-list">
                            @forelse($allReviews as $review)
                                <div class="list-row">
                                    <div class="list-left">
                                        <div class="list-icon-bg">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-message-square-text"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/><path d="M13 8H7"/><path d="M17 12H7"/></svg>
                                        </div>
                                        <div class="list-info">
                                            <a href="{{ $review->post ? route('posts.show', $review->post->slug) . '#comment-' . $review->id : '#' }}" class="list-title">{{ $review->post?->title ?? 'Deleted Post' }}</a>
                                            <span class="list-subtitle">{{ Str::limit($review->content, 60) }}</span>
                                        </div>
                                    </div>
                                    <div class="list-right">
                                        <span class="list-meta">{{ $review->created_at->format('d M Y') }}</span>
                                        <div class="list-rating">★ {{ number_format($recentReviewRatings->get($review->post_id) ?? 0, 1) }}</div>
                                    </div>
                                </div>
                            @empty
                                <div class="empty-state">No reviews yet.</div>
                            @endforelse
                        </div>
                    </div>



                    <!-- Uploads Panel -->
                    <div x-show="expandedSection === 'uploads'" class="expansion-panel">
                        <div class="panel-header">
                            <h3>My Uploads</h3>
                            <span class="count-pill">{{ $uploadsCount }} Items</span>
                        </div>
                        <div class="dense-list">
                            @forelse($allUploads as $upload)
                                                <div class="list-row">
                                                    <div class="list-left">
                                                        <div class="list-icon-bg green-bg">
                                                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-cloud-upload"><path d="M4 14.899A7 7 0 1 1 15.71 8h1.79a4.5 4.5 0 0 1 2.5 8.242"/><path d="M12 13v8"/><path d="m8 17 4-4 4 4"/></svg>
                                                        </div>
                                                        <div class="list-info">
                                                            <a href="{{ $upload->post ? route('posts.show', $upload->post->slug) . '#panel-user-' . $user->id : '#' }}" class="list-title">{{ $upload->post?->title ?? 'Deleted Post' }}</a>
                                                            <span class="list-subtitle">Contributed Resource</span>
                                                        </div>
                                                    </div>
                                                    <div class="list-right">
                                                        <span class="list-meta">{{ $upload->created_at->format('d M Y') }}</span>
                                                        @if($upload->post)
                                                            <a href="{{ route('posts.show', $upload->post->slug) }}#panel-user-{{ $user->id }}" class="view-btn">View</a>
                                                        @endif
                                                    </div>
                                                </div>
                            @empty
                                <div class="empty-state">No uploads yet.</div>
                            @endforelse
                        </div>
                    </div>
                </div>

            </div>

            {{-- Report modal (teleported to body, same style as post-detail) --}}
            @auth
                @if(auth()->id() !== $user->id)
                    <template x-teleport="body">
                        <div x-cloak x-show="reportOpen" x-transition.opacity.duration.200ms
                            class="fixed inset-0 z-[100000] flex items-center justify-center bg-black/45 p-4 backdrop-blur-md"
                            role="presentation"
                            x-on:click.self="closeReportModal()"
                            x-on:keydown.escape.window="closeReportModal()">
                            <form method="POST" action="{{ route('users.report', $user->id) }}"
                                class="flex max-h-[85vh] w-full max-w-xl flex-col overflow-hidden rounded-3xl border [border-color:color-mix(in_srgb,var(--text-color,#0d1b2a)_8%,transparent)] [background:var(--background-color,#ffffff)] [color:var(--text-color,#0d1b2a)] [box-shadow:0_30px_60px_-15px_color-mix(in_srgb,var(--text-color,#0d1b2a)_28%,transparent)]"
                                aria-labelledby="user-report-modal-title-{{ $user->id }}"
                                x-on:click.stop>
                                @csrf

                                <div class="flex items-start justify-between gap-4 px-7 pb-2 pt-7">
                                    <div class="min-w-0">
                                        <h3 id="user-report-modal-title-{{ $user->id }}"
                                            class="text-[22px] font-bold leading-tight tracking-normal [color:var(--text-color,#0d1b2a)]">
                                            Report User
                                        </h3>
                                        <p class="mt-2 text-sm leading-6 [color:color-mix(in_srgb,var(--text-color,#0d1b2a)_64%,transparent)]">
                                            Select the reason that best describes the issue with this user.
                                        </p>
                                    </div>
                                    <button type="button"
                                        class="flex size-9 shrink-0 items-center justify-center rounded-full transition [color:color-mix(in_srgb,var(--text-color,#0d1b2a)_62%,transparent)] hover:[background:color-mix(in_srgb,var(--background-color,#ffffff)_86%,var(--accent-color,#6c5ce7)_14%)] hover:[color:var(--text-color,#0d1b2a)] focus:outline-none"
                                        aria-label="Close report dialog"
                                        x-on:click="closeReportModal()">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-x"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>
                                    </button>
                                </div>

                                <div class="flex-1 space-y-6 overflow-y-auto px-7 py-4">
                                    <div class="space-y-3">
                                        <span class="block text-[11px] font-bold uppercase tracking-[0.08em] [color:color-mix(in_srgb,var(--text-color,#0d1b2a)_42%,transparent)]">Content Quality</span>
                                        <div class="grid gap-3 sm:grid-cols-2">
                                            @foreach ([
                                                ['label' => 'Spam / Misleading'],
                                                ['label' => 'Fake / False Info'],
                                                ['label' => 'Misinformation'],
                                                ['label' => 'Nudity / Obscenity'],
                                            ] as $option)
                                                <label class="flex cursor-pointer items-center justify-between gap-3 rounded-2xl border p-4 transition active:scale-[0.98] [border-color:color-mix(in_srgb,var(--text-color,#0d1b2a)_12%,transparent)] [background:var(--background-color,#ffffff)] hover:[background:color-mix(in_srgb,var(--background-color,#ffffff)_92%,var(--accent-color,#6c5ce7)_8%)]"
                                                    x-bind:class="reportReason === @js($option['label']) ? '[border-color:var(--accent-color,#6c5ce7)] [background:color-mix(in_srgb,var(--background-color,#ffffff)_84%,var(--accent-color,#6c5ce7)_16%)] [box-shadow:0_0_0_1px_var(--accent-color,#6c5ce7)]' : ''">
                                                    <span class="flex min-w-0 items-center gap-3 text-sm font-semibold [color:color-mix(in_srgb,var(--text-color,#0d1b2a)_72%,transparent)]"
                                                        x-bind:class="reportReason === @js($option['label']) ? '[color:var(--accent-color,#6c5ce7)]' : ''">
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
                                        <span class="block text-[11px] font-bold uppercase tracking-[0.08em] [color:color-mix(in_srgb,var(--text-color,#0d1b2a)_42%,transparent)]">Safety &amp; Conduct</span>
                                        <div class="grid gap-3 sm:grid-cols-2">
                                            @foreach ([
                                                ['label' => 'Hate Speech'],
                                                ['label' => 'Harassment / Abuse'],
                                                ['label' => 'Violence / Threats'],
                                                ['label' => 'Other'],
                                            ] as $option)
                                                <label class="flex cursor-pointer items-center justify-between gap-3 rounded-2xl border p-4 transition active:scale-[0.98] [border-color:color-mix(in_srgb,var(--text-color,#0d1b2a)_12%,transparent)] [background:var(--background-color,#ffffff)] hover:[background:color-mix(in_srgb,var(--background-color,#ffffff)_92%,var(--accent-color,#6c5ce7)_8%)]"
                                                    x-bind:class="reportReason === @js($option['label']) ? '[border-color:var(--accent-color,#6c5ce7)] [background:color-mix(in_srgb,var(--background-color,#ffffff)_84%,var(--accent-color,#6c5ce7)_16%)] [box-shadow:0_0_0_1px_var(--accent-color,#6c5ce7)]' : ''">
                                                    <span class="flex min-w-0 items-center gap-3 text-sm font-semibold [color:color-mix(in_srgb,var(--text-color,#0d1b2a)_72%,transparent)]"
                                                        x-bind:class="reportReason === @js($option['label']) ? '[color:var(--accent-color,#6c5ce7)]' : ''">
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
                                        <div class="flex items-center justify-between">
                                            <label for="user-report-details-{{ $user->id }}"
                                                class="block text-[11px] font-bold uppercase tracking-[0.08em] [color:color-mix(in_srgb,var(--text-color,#0d1b2a)_42%,transparent)]">Additional Details <span class="ml-1 font-medium normal-case tracking-normal opacity-70">(Optional)</span></label>
                                            <span class="text-xs font-semibold [color:color-mix(in_srgb,var(--text-color,#0d1b2a)_42%,transparent)]"
                                                x-bind:class="reportDetailsCount() >= 560 ? '[color:var(--accent-color,#6c5ce7)]' : ''"
                                                x-text="`${reportDetailsCount()} / 600`">0 / 600</span>
                                        </div>
                                        <textarea id="user-report-details-{{ $user->id }}" name="details" maxlength="600" rows="4"
                                            x-model="reportDetails"
                                            class="w-full resize-y rounded-2xl border px-4 py-3 text-[15px] leading-relaxed transition-colors placeholder:[color:color-mix(in_srgb,var(--text-color,#0d1b2a)_38%,transparent)] focus:outline-none focus:ring-4 [border-color:color-mix(in_srgb,var(--text-color,#0d1b2a)_16%,transparent)] [background:color-mix(in_srgb,var(--text-color,#0d1b2a)_2%,transparent)] [color:var(--text-color,#0d1b2a)] focus:[border-color:var(--accent-color,#6c5ce7)] focus:[--tw-ring-color:color-mix(in_srgb,var(--accent-color,#6c5ce7)_16%,transparent)]"
                                            placeholder="Describe context or reasons to help us review this report faster."></textarea>
                                    </div>
                                </div>

                                <div class="flex items-center justify-end gap-3 border-t px-7 py-5 [border-color:color-mix(in_srgb,var(--text-color,#0d1b2a)_8%,transparent)] [background:color-mix(in_srgb,var(--text-color,#0d1b2a)_2%,transparent)]">
                                    <button type="button"
                                        class="rounded-xl px-5 py-2.5 text-[15px] font-bold transition hover:[background:color-mix(in_srgb,var(--text-color,#0d1b2a)_6%,transparent)] [color:color-mix(in_srgb,var(--text-color,#0d1b2a)_72%,transparent)] focus:outline-none"
                                        x-on:click="closeReportModal()">
                                        Cancel
                                    </button>
                                    <button type="submit"
                                        class="inline-flex items-center justify-center gap-2 rounded-xl px-6 py-2.5 text-[15px] font-bold shadow-sm transition disabled:cursor-not-allowed disabled:opacity-50 [background:var(--accent-color,#6c5ce7)] [color:var(--background-color,#ffffff)] hover:![background:color-mix(in_srgb,var(--accent-color,#6c5ce7)_85%,#000_15%)] focus:outline-none focus:ring-4 focus:[--tw-ring-color:color-mix(in_srgb,var(--accent-color,#6c5ce7)_24%,transparent)]"
                                        x-bind:disabled="! reportReason" disabled>
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-flag"><path d="M4 15s1-1 4-1 5 2 8 2 4-1 4-1V3s-1 1-4 1-5-2-8-2-4 1-4 1z"/><line x1="4" x2="4" y1="22" y2="15"/></svg>
                                        <span>Submit Report</span>
                                    </button>
                                </div>
                            </form>
                        </div>
                    </template>
                @endif
            @endauth
        </main>
    </div>
@endsection

@push('scripts')
    @vite('resources/js/profile-detail.js')
@endpush
