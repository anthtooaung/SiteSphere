@extends('index')

@section('title')
    Home
@endsection

@push('styles')
    @vite('resources/css/homepage.css')
@endpush

@section('content')
    <x-layout.nav />

    <div class="home-page page-layout">
        
        <x-layout.home-aside :categories="$categories" :menu-bar-location="$menuBarLocation" />

        <main class="main-content">
            <section class="content-intro" aria-labelledby="contentTitle">
                <div>
                    <h1 id="contentTitle">Discover Useful Websites</h1>
                    <p>Browse reviewed websites and narrow your results with simple, clear filters.</p>
                </div>
            </section>

            <header class="hero">
                <div class="active-filters-wrapper">
                    <div class="active-filters-heading">
                        <div class="active-filters-title">
                            <span>Selected Filters</span>
                        </div>

                        <button class="clear-all-filters" id="clearAllFilters" type="button">Clear All Filters</button>
                    </div>

                    <div class="active-filter-grid" aria-live="polite">
                        <div class="filter-section">
                            <div class="filter-title">
                                <x-fas-star class="filter-icon" aria-hidden="true" />
                                <h3>Rating</h3>
                            </div>
                            <div class="filter-items" id="selectedRatings"></div>
                        </div>

                        <div class="filter-section">
                            <div class="filter-title">
                                <x-fas-layer-group class="filter-icon" aria-hidden="true" />
                                <h3>Categories</h3>
                            </div>
                            <div class="filter-items" id="selectedCategories"></div>
                        </div>

                        <div class="filter-section">
                            <div class="filter-title">
                                <x-fas-tags class="filter-icon" aria-hidden="true" />
                                <h3>Tags</h3>
                            </div>
                            <div class="filter-items" id="selectedTags"></div>
                        </div>
                    </div>
                </div>
            </header>

            <div class="results-toolbar">
                <p><strong id="resultsCount">{{ $posts->count() }}</strong> websites found</p>
                <label class="sort-control" for="sortSelect">
                    <select id="sortSelect" aria-label="Sort websites">
                        <option value="best">Best match</option>
                        <option value="rating">Highest rating</option>
                        <option value="newest">Newest</option>
                    </select>
                </label>
            </div>

            <section class="reviews-grid" id="reviewsGrid">
                @foreach ($posts as $post)
                    <x-layout.post-card
                        :title="$post['title']"
                        :url="$post['url']"
                        :category="$post['category']"
                        :profiles="$post['profiles']"
                        :average-rating="$post['average_rating']"
                        :ratings-count="$post['ratings_count']"
                        :comments-count="$post['comments_count']"
                        :saved="$post['is_bookmarked']"
                        data-category="{{ $post['category_slug'] }}"
                        data-rating="{{ (int) floor($post['average_rating']) }}"
                        data-tags="{{ implode(',', $post['tags']) }}"
                    />
                @endforeach
            </section>

            <div class="pagination" id="pagination"></div>
        </main>
    </div>
@endsection

@push('scripts')
    <script>
        window.homeCategoryTags = @json($categoryTags);
        window.homeCategoryLabels = @json($categoryLabels);
        window.homeInitialCategory = @json($initialCategory);
    </script>
    @vite('resources/js/homepage.js')
@endpush
