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
        <button class="menu-icon" id="sidebarToggle" type="button" aria-controls="sidebar" aria-expanded="false" aria-label="Open sidebar">
            <x-fas-bars aria-hidden="true" />
        </button>

        <aside class="sidebar" id="sidebar">
            <div class="sidebar-header">
                <h2>Filters</h2>
                <p>Refine websites by rating, category, and tags.</p>
            </div>

            <div class="sidebar-section">
                <div class="section-header" id="ratingHeader">
                    <div class="section-left">
                        <x-fas-star class="section-icon" aria-hidden="true" />
                        <h3>Rating</h3>
                    </div>
                    <x-fas-chevron-down class="arrow-icon" aria-hidden="true" />
                </div>

                <div class="section-content" id="ratingContent">
                    <div class="rating-options">
                        <label class="rating-check"><input type="checkbox" value="all"><span>All</span></label>
                        @foreach ([5, 4, 3, 2, 1] as $rating)
                            <label class="rating-check"><input type="checkbox" value="{{ $rating }}"><span>{{ $rating }}+ Rating</span></label>
                        @endforeach
                    </div>
                </div>
            </div>

            <div class="sidebar-section">
                <div class="section-header" id="categoryHeader">
                    <div class="section-left">
                        <x-fas-layer-group class="section-icon" aria-hidden="true" />
                        <h3>Categories</h3>
                    </div>
                    <x-fas-chevron-down class="arrow-icon" aria-hidden="true" />
                </div>

                <div class="section-content" id="categoryContent">
                    <div class="category-search-box">
                        <x-fas-search class="search-icon" aria-hidden="true" />
                        <input type="text" id="categorySearch" placeholder="Search categories...">
                    </div>

                    <label class="category-check"><input type="checkbox" value="All"><span>All</span></label>

                    @foreach ($categories->take(5) as $category)
                        <label class="category-check">
                            <input type="checkbox" value="{{ $category->slug }}">
                            <span>{{ $category->name }}</span>
                        </label>
                    @endforeach

                    <div class="extra-categories" id="extraCategories">
                        @foreach ($categories->skip(5) as $category)
                            <label class="category-check">
                                <input type="checkbox" value="{{ $category->slug }}">
                                <span>{{ $category->name }}</span>
                            </label>
                        @endforeach
                    </div>

                    <button class="show-category-btn" id="showCategoryBtn" type="button">Show More Categories</button>
                </div>
            </div>

            <div class="sidebar-section">
                <div class="section-header" id="tagsHeader">
                    <div class="section-left">
                        <x-fas-tags class="section-icon" aria-hidden="true" />
                        <h3>Tags</h3>
                    </div>
                    <x-fas-chevron-down class="arrow-icon" aria-hidden="true" />
                </div>

                <div class="section-content" id="tagsContent">
                    <div class="tag-tools">
                        <div class="tag-search-box">
                            <x-fas-search class="search-icon" aria-hidden="true" />
                            <input type="text" id="tagSearch" placeholder="Search tags...">
                        </div>
                    </div>

                    <div class="tags-container" id="tagsContainer"></div>
                    <button class="show-tags-btn" id="showTagsBtn" type="button">Show More Tags</button>
                </div>
            </div>
        </aside>

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
    </script>
    @vite('resources/js/homepage.js')
@endpush
