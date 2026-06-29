@extends('index')

@section('title')
    Home
@endsection

@push('styles')
    @vite('resources/css/homepage.css')
@endpush

@section('content')
    <x-layout.nav />

    <div class="home-page page-layout"
         x-data="homeController()">
        
        <x-layout.home-aside :categories="$categories" :menu-bar-location="$menuBarLocation" />

        <main class="main-content">
            <section class="content-intro" aria-labelledby="contentTitle">
                <div>
                    <h1 id="contentTitle">Discover Useful Websites</h1>
                    <p>Browse reviewed websites and narrow your results with simple, clear filters.</p>
                </div>
            </section>

            <div class="results-toolbar">
                <p><strong id="resultsCount" x-text="totalResults">{{ $posts->total() }}</strong> websites found</p>
                <label class="sort-control" for="sortSelect">
                    <select id="sortSelect" x-model="filters.sort" @change="updateResults()" aria-label="Sort websites">
                        <option value="best">Best match</option>
                        <option value="rating">Highest rating</option>
                        <option value="newest">Newest</option>
                    </select>
                </label>
            </div>

            <header class="hero">
                <div class="active-filters-wrapper" :class="{'has-filters': hasActiveFilters()}">
                    <div class="active-filters-heading">
                        <div class="active-filters-title">
                            <span>Selected Filters</span>
                        </div>

                        <button class="clear-all-filters" id="clearAllFilters" type="button" @click="clearFilters()">Clear All Filters</button>
                    </div>

                    <div class="active-filter-grid" aria-live="polite">
                        <div class="filter-section">
                            <div class="filter-title">
                                <x-fas-star class="filter-icon" aria-hidden="true" />
                                <h3>Rating</h3>
                            </div>
                            <div class="filter-items" id="selectedRatings">
                                <template x-for="rating in filters.rating" :key="rating">
                                    <div class="selected-box">
                                        <x-fas-star class="selected-icon" aria-hidden="true" />
                                        <span class="selected-label" x-text="rating === 'all' ? 'All' : rating + '+ Rating'"></span>
                                        <span class="remove-btn" @click="toggleFilter('rating', rating)">×</span>
                                    </div>
                                </template>
                                <div class="selected-box" x-show="filters.rating.length === 0">
                                    <x-fas-star class="selected-icon" aria-hidden="true" />
                                    <span class="selected-label">All</span>
                                </div>
                            </div>
                        </div>

                        <div class="filter-section">
                            <div class="filter-title">
                                <x-fas-layer-group class="filter-icon" aria-hidden="true" />
                                <h3>Categories</h3>
                            </div>
                            <div class="filter-items" id="selectedCategories">
                                <template x-for="category in filters.category" :key="category">
                                    <div class="selected-box">
                                        <span class="selected-label" x-text="getCategoryLabel(category)"></span>
                                        <span class="remove-btn" @click="toggleFilter('category', category)">×</span>
                                    </div>
                                </template>
                                <div class="selected-box" x-show="filters.category.length === 0">
                                    <span class="selected-label">All</span>
                                </div>
                            </div>
                        </div>

                        <div class="filter-section">
                            <div class="filter-title">
                                <x-fas-tags class="filter-icon" aria-hidden="true" />
                                <h3>Tags</h3>
                            </div>
                            <div class="filter-items" id="selectedTags">
                                <template x-for="tag in filters.tags" :key="tag">
                                    <div class="selected-box">
                                        <span class="selected-label" x-text="tag"></span>
                                        <span class="remove-btn" @click="toggleFilter('tags', tag)">×</span>
                                    </div>
                                </template>
                                <div class="selected-box" x-show="filters.tags.length === 0">
                                    <span class="selected-label">All</span>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </header>

            <div class="pagination-container pagination-top" @click.prevent="handlePaginationClick($event)" x-show="totalResults > 0" x-cloak>
                {!! $posts->withQueryString()->links() !!}
            </div>

            <section class="reviews-grid" id="reviewsGrid" x-ref="postsGrid">
                @include('partials.home-posts', ['posts' => $posts])
            </section>

            <section class="reviews-grid" style="margin-top: 20px;" x-show="isLoading && page === 1" x-cloak>
                <x-layout.post-card-skeleton :count="3" />
            </section>

            <section class="home-empty-state" id="homeEmptyState" x-show="totalResults === 0" x-cloak>
                <div id="emptyStateSearchIcon">
                    <x-fas-search class="home-empty-icon" aria-hidden="true" />
                </div>
                <h2 id="emptyStateTitle">No websites found</h2>
                <p id="emptyStateText">Try adjusting your filters to find what you're looking for.</p>
                <button type="button" class="clear-filters-btn" id="emptyStateClearBtn" @click="clearFilters()">Clear All Filters</button>
            </section>

            <div class="pagination-container pagination-bottom" @click.prevent="handlePaginationClick($event)" x-show="totalResults > 0" x-cloak>
                {!! $posts->withQueryString()->links() !!}
            </div>
        </main>
    </div>
@endsection

@push('scripts')
    <script>
        window.homeCategoryTags = @json($categoryTags);
        window.homeCategoryLabels = @json($categoryLabels);
        window.homeInitialCategory = @json($initialCategory);

        function homeController() {
            return {
                totalResults: {{ $posts->total() }},
                hasMore: {{ $posts->hasMorePages() ? 'true' : 'false' }},
                lastPage: {{ $posts->lastPage() }},
                page: {{ $posts->currentPage() }},
                isLoading: false,
                filters: {
                    category: window.homeInitialCategory ? [window.homeInitialCategory] : [],
                    tags: [],
                    rating: [],
                    sort: 'best',
                    search: @json(request('search') ?? '')
                },
                search: {
                    category: '',
                    tag: '',
                    showMoreCategories: false,
                    showMoreTags: false
                },

                get availableTags() {
                    if (this.filters.category.length === 0) return window.homeCategoryTags.all;
                    const tags = new Set();
                    this.filters.category.forEach(cat => {
                        (window.homeCategoryTags[cat] || []).forEach(tag => tags.add(tag));
                    });
                    return Array.from(tags);
                },

                get filteredTags() {
                    const search = this.search.tag.toLowerCase();
                    return this.availableTags.filter(t => t.toLowerCase().includes(search));
                },

                get visibleTags() {
                    const tags = this.filteredTags;
                    if (this.search.showMoreTags) return tags;
                    return tags.slice(0, 10);
                },

                async updateResults(resetPage = true) {
                    if (resetPage) this.page = 1;
                    this.isLoading = true;
                    
                    try {
                        const url = this.getFilterUrl();
                        url.searchParams.set('page', this.page);
                        
                        const response = await fetch(url.toString(), {
                            headers: { 'X-Requested-With': 'XMLHttpRequest' }
                        });
                        const data = await response.json();
                        
                        this.$refs.postsGrid.innerHTML = data.html;
                        this.totalResults = data.total;
                        this.lastPage = data.lastPage;
                        if (data.currentPage) {
                            this.page = data.currentPage;
                        }
                        
                        const paginationContainers = document.querySelectorAll('.pagination-container');
                        if (paginationContainers.length > 0 && data.pagination !== undefined) {
                            paginationContainers.forEach(container => {
                                container.innerHTML = data.pagination;
                            });
                        }

                        // Update URL without reloading to reflect current filters
                        window.history.pushState({}, '', url);

                        // Also update the desktop search input if it exists
                        const searchInput = document.getElementById('search');
                        if (searchInput) {
                            searchInput.value = this.filters.search;
                        }
                    } catch (error) {
                        console.error('Error updating results:', error);
                    } finally {
                        this.isLoading = false;
                    }
                },

                handlePaginationClick(event) {
                    const link = event.target.closest('a');
                    if (!link) return;
                    
                    const url = new URL(link.href);
                    const pageStr = url.searchParams.get('page');
                    
                    this.page = pageStr ? parseInt(pageStr, 10) : 1;
                    this.updateResults(false);
                    
                    // Scroll up to the top of the grid
                    if (this.$refs.postsGrid) {
                        this.$refs.postsGrid.scrollIntoView({ behavior: 'smooth', block: 'start' });
                    }
                },

                getFilterUrl() {
                    const url = new URL(window.location.origin + window.location.pathname);
                    if (this.filters.category.length > 0) {
                        url.searchParams.set('category', this.filters.category.join(','));
                    }
                    if (this.filters.tags.length > 0) {
                        url.searchParams.set('tags', this.filters.tags.join(','));
                    }
                    if (this.filters.rating.length > 0) {
                        url.searchParams.set('rating', this.filters.rating[0]);
                    }
                    if (this.filters.search) {
                        url.searchParams.set('search', this.filters.search);
                    }
                    url.searchParams.set('sort', this.filters.sort);
                    return url;
                },

                toggleFilter(type, value) {
                    if (value === 'all' || value === 'All') {
                        this.filters[type] = [];
                    } else if (type === 'rating') {
                        // Rating is single-select: replace instead of append
                        this.filters.rating = this.filters.rating.includes(value) ? [] : [value];
                    } else {
                        const index = this.filters[type].indexOf(value);
                        if (index > -1) {
                            this.filters[type] = this.filters[type].filter((_, i) => i !== index);
                        } else {
                            this.filters[type] = [...this.filters[type], value];
                        }
                    }
                    this.updateResults();
                },

                hasActiveFilters() {
                    return this.filters.category.length > 0 || 
                           this.filters.tags.length > 0 || 
                           this.filters.rating.length > 0 ||
                           this.filters.search !== '';
                },

                clearFilters() {
                    this.filters.category = [];
                    this.filters.tags = [];
                    this.filters.rating = [];
                    this.filters.search = '';
                    this.filters.sort = 'best';
                    this.updateResults();
                },

                getCategoryLabel(slug) {
                    return window.homeCategoryLabels[slug] || slug;
                },

                init() {
                    const params = new URLSearchParams(window.location.search);

                    if (params.get('category')) {
                        this.filters.category = params.get('category').split(',');
                    }
                    if (params.get('tags')) {
                        this.filters.tags = params.get('tags').split(',');
                    }
                    if (params.get('rating')) {
                        this.filters.rating = [params.get('rating')];
                    }
                    if (params.get('search')) {
                        this.filters.search = params.get('search');
                    }
                    if (params.get('sort')) {
                        this.filters.sort = params.get('sort');
                    }
                }
            }
        }
    </script>
    @vite('resources/js/homepage.js')
@endpush
