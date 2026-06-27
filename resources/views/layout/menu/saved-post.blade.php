@extends('dashboard')

@section('title')
    Saved Post
@endsection

@section('content')
    @php
        $dashboardMenuLocation = in_array($menuBarLocation ?? 'left', ['top', 'right', 'bottom', 'left'], true)
            ? $menuBarLocation
            : 'left';
        $savedPostFilters = $savedPostFilters ?? [
            'search' => '',
            'sort' => 'recent',
            'start_date' => '',
            'end_date' => '',
        ];
        $visibleSavedCount = $savedPosts->count();
        $totalSavedCount = $totalSavedCount ?? $visibleSavedCount;
    @endphp

    <x-layout.nav />

    <div class="dashboard-page dashboard-page--{{ $dashboardMenuLocation }} saved-post-page">
        <x-layout.menu :menu-bar-location="$dashboardMenuLocation" />

        <main class="dashboard-content saved-post-content" aria-labelledby="savedPostTitle">
            <section class="saved-post-main-app" data-saved-post-page>
                <header class="saved-post-header">
                    <div class="saved-post-title-group">
                        <p class="dashboard-kicker">Bookmarks</p>
                        <h1 id="savedPostTitle">Saved Post</h1>
                        <p>Manage and view your bookmarked website reviews and saved resources.</p>
                    </div>
                </header>

                <form method="GET" action="{{ route('saved-post') }}" class="saved-post-toolbar"
                    data-saved-post-filter-form>
                    <label class="saved-post-search">
                        <x-fas-search class="saved-post-search-icon" aria-hidden="true" />
                        <span class="sr-only">Search saved posts</span>
                        <input type="search" name="search" value="{{ $savedPostFilters['search'] }}"
                            placeholder="Search by title or URL..." data-saved-post-search>
                    </label>

                    <div class="saved-post-control-wrapper relative">
                        <span class="sr-only">Sort saved posts</span>
                        <button type="button" class="saved-post-control" id="savedPostSortButton"
                            data-dropdown-toggle="savedPostSortDropdown" data-dropdown-placement="bottom-start"
                            aria-expanded="false">
                            <span class="saved-post-control-label">
                                {{ $savedPostFilters['sort'] === 'az' ? 'A-Z' : 'Recently saved' }}
                            </span>
                            <x-fas-chevron-down class="saved-post-control-icon" aria-hidden="true" />
                        </button>

                        <div id="savedPostSortDropdown" class="account-menu-dropdown hidden"
                            aria-labelledby="savedPostSortButton">
                            <ul class="account-menu-list">
                                <li>
                                    <button type="button" class="account-menu-link {{ $savedPostFilters['sort'] === 'recent' ? 'active' : '' }}"
                                        data-sort-val="recent">
                                        Recently saved
                                    </button>
                                </li>
                                <li>
                                    <button type="button" class="account-menu-link {{ $savedPostFilters['sort'] === 'az' ? 'active' : '' }}"
                                        data-sort-val="az">
                                        A-Z
                                    </button>
                                </li>
                            </ul>
                        </div>
                        <input type="hidden" name="sort" value="{{ $savedPostFilters['sort'] }}" data-saved-post-sort>
                    </div>

                    <label class="saved-post-date">
                        <span>Start date</span>
                        <input type="date" name="start_date" value="{{ $savedPostFilters['start_date'] }}"
                            data-saved-post-start-date>
                    </label>

                    <label class="saved-post-date">
                        <span>End date</span>
                        <input type="date" name="end_date" value="{{ $savedPostFilters['end_date'] }}"
                            data-saved-post-end-date>
                    </label>

                    <div class="saved-post-toolbar-actions">
                        <button type="submit" class="saved-post-filter-button">
                            Apply
                        </button>
                        <a href="{{ route('saved-post') }}" class="saved-post-clear-button">
                            Clear
                        </a>
                    </div>
                </form>

                <div class="saved-post-meta-row">
                    <span data-saved-post-count>
                        Showing {{ $visibleSavedCount }} of {{ $totalSavedCount }}
                    </span>
                    <span>
                        Sorted by {{ $savedPostFilters['sort'] === 'az' ? 'A-Z' : 'recently saved' }}
                    </span>
                </div>

                <div class="saved-post-loading" data-saved-post-loading hidden aria-live="polite">
                    <span class="saved-post-loading-spinner" aria-hidden="true"></span>
                    <span>Loading saved posts...</span>
                </div>

                @if ($savedPosts->isEmpty())
                    <section class="saved-post-empty" data-saved-post-empty>
                        <x-fas-bookmark class="saved-post-empty-icon" aria-hidden="true" />
                        @if ($totalSavedCount === 0)
                            <h2>No saved posts yet</h2>
                            <p>Save posts from the card action menu and they will appear here.</p>
                            <a href="{{ route('home') }}" class="saved-post-filter-button">Browse posts</a>
                        @else
                            <h2>No saved posts match your filters</h2>
                            <p>Try changing the search text, date range, or sort option.</p>
                            <a href="{{ route('saved-post') }}" class="saved-post-filter-button">Reset filters</a>
                        @endif
                    </section>
                @else
                    <div class="saved-post-grid" data-saved-post-grid>
                        @foreach ($savedPosts as $post)
                            <x-layout.post-card :post-id="$post['id']" :title="$post['title']" :url="$post['url']"
                                :category="$post['category']" :tags="$post['tags']" :profiles="$post['profiles']"
                                :average-rating="$post['average_rating']" :ratings-count="$post['ratings_count']"
                                :comments-count="$post['comments_count']" :saved="true" :slug="$post['slug']"
                                :is-unsecure="$post['is_unsecure']"
                                :has-reported="$post['has_reported']"
                                class="saved-post-card"
                                data-saved-post-card
                                data-saved-post-title="{{ $post['title'] }}"
                                data-saved-post-url="{{ $post['url'] }}"
                                data-saved-post-date="{{ $post['saved_at'] }}" />
                        @endforeach
                    </div>
                @endif
            </section>
        </main>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const mainApp = document.querySelector('[data-saved-post-page]');
            if (!mainApp) return;

            async function fetchSavedPosts(url) {
                setSavedPostLoading(true);

                try {
                    const response = await fetch(url, {
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    });
                    const html = await response.text();
                    const parser = new DOMParser();
                    const doc = parser.parseFromString(html, 'text/html');

                    const newMain = doc.querySelector('.saved-post-main-app');
                    const currentMain = document.querySelector('.saved-post-main-app');
                    if (newMain && currentMain) {
                        currentMain.innerHTML = newMain.innerHTML;
                        window.history.pushState({}, '', url);
                        bindSavedPostEvents();
                        if (typeof initFlowbite === 'function') {
                            initFlowbite();
                        }
                        setSavedPostLoading(false);
                    } else {
                        setSavedPostLoading(false);
                    }
                } catch (error) {
                    console.error('Failed to fetch saved posts:', error);
                    setSavedPostLoading(false);
                }
            }

            function setSavedPostLoading(isLoading) {
                const currentMain = document.querySelector('.saved-post-main-app');
                const form = currentMain?.querySelector('[data-saved-post-filter-form]');
                const loading = currentMain?.querySelector('[data-saved-post-loading]');
                const controls = form?.querySelectorAll('input, button, select, textarea') || [];

                currentMain?.classList.toggle('is-loading', isLoading);

                if (loading) {
                    loading.hidden = !isLoading;
                }

                controls.forEach(control => {
                    control.disabled = isLoading;
                });
            }

            function bindSavedPostEvents() {
                const form = mainApp.querySelector('[data-saved-post-filter-form]');
                if (form) {
                    form.addEventListener('submit', (e) => {
                        e.preventDefault();
                        const formData = new FormData(form);
                        const params = new URLSearchParams();
                        for (const [key, val] of formData.entries()) {
                            if (val) {
                                params.append(key, val);
                            }
                        }
                        const url = form.action + '?' + params.toString();
                        fetchSavedPosts(url);
                    });

                    // Handle sort dropdown option selection
                    const sortButtons = form.querySelectorAll('[data-sort-val]');
                    const sortInput = form.querySelector('[data-saved-post-sort]');
                    const sortLabel = form.querySelector('.saved-post-control-label');
                    sortButtons.forEach(btn => {
                        btn.addEventListener('click', () => {
                            const val = btn.getAttribute('data-sort-val');
                            if (sortInput && sortInput.value !== val) {
                                sortInput.value = val;
                                if (sortLabel) {
                                    sortLabel.textContent = btn.textContent.trim();
                                }
                                sortButtons.forEach(b => b.classList.remove('active'));
                                btn.classList.add('active');
                                sortInput.dispatchEvent(new Event('change'));
                            }
                        });
                    });

                    // Auto-submit form when sort dropdown or date pickers change
                    const autoFields = form.querySelectorAll('[data-saved-post-sort], [data-saved-post-start-date], [data-saved-post-end-date]');
                    autoFields.forEach(field => {
                        field.addEventListener('change', () => {
                            if (typeof form.requestSubmit === 'function') {
                                form.requestSubmit();
                            } else {
                                form.dispatchEvent(new Event('submit', { cancelable: true }));
                            }
                        });
                    });
                }
                const links = mainApp.querySelectorAll('a');
                links.forEach(link => {
                    try {
                        const urlObj = new URL(link.href, window.location.origin);
                        if (urlObj.pathname === window.location.pathname) {
                            link.addEventListener('click', (e) => {
                                e.preventDefault();
                                fetchSavedPosts(link.href);
                            });
                        }
                    } catch (e) {
                        // Ignore invalid URLs
                    }
                });
            }

            bindSavedPostEvents();
        });
    </script>
@endpush
