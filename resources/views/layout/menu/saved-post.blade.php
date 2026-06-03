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
                        <p>
                            {{ $totalSavedCount }} {{ \Illuminate\Support\Str::plural('website', $totalSavedCount) }} saved to your workspace.
                        </p>
                    </div>

                    <form method="GET" action="{{ route('saved-post') }}" class="saved-post-toolbar"
                        data-saved-post-filter-form>
                        <label class="saved-post-search">
                            <x-fas-search class="saved-post-search-icon" aria-hidden="true" />
                            <span class="sr-only">Search saved posts</span>
                            <input type="search" name="search" value="{{ $savedPostFilters['search'] }}"
                                placeholder="Search by title or URL..." data-saved-post-search>
                        </label>

                        <label class="saved-post-control">
                            <span class="sr-only">Sort saved posts</span>
                            <select name="sort" data-saved-post-sort>
                                <option value="recent" @selected($savedPostFilters['sort'] === 'recent')>Recently saved</option>
                                <option value="az" @selected($savedPostFilters['sort'] === 'az')>A-Z</option>
                            </select>
                            <x-fas-chevron-down class="saved-post-control-icon" aria-hidden="true" />
                        </label>

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
                </header>

                <div class="saved-post-meta-row">
                    <span data-saved-post-count>
                        Showing {{ $visibleSavedCount }} of {{ $totalSavedCount }}
                    </span>
                    <span>
                        Sorted by {{ $savedPostFilters['sort'] === 'az' ? 'A-Z' : 'recently saved' }}
                    </span>
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
                                :comments-count="$post['comments_count']" :saved="true"
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
