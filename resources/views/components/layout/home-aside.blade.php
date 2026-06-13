@props([
    'categories' => collect(),
    'menuBarLocation' => 'left',
])

@php
    $menuBarLocation = in_array($menuBarLocation, ['top', 'right', 'bottom', 'left'], true)
        ? $menuBarLocation
        : 'left';
    $isDropdownAside = in_array($menuBarLocation, ['top', 'bottom'], true);
@endphp

<button class="menu-icon" id="sidebarToggle" type="button" aria-controls="sidebar" aria-expanded="false" aria-label="Open sidebar">
    <x-fas-bars aria-hidden="true" />
</button>

<aside
    id="sidebar"
    data-menu-bar-location="{{ $menuBarLocation }}"
    data-dropdown-aside="{{ $isDropdownAside ? 'true' : 'false' }}"
    {{ $attributes->class(['sidebar', 'home-aside', 'home-aside--'.$menuBarLocation, 'home-aside--dropdown' => $isDropdownAside]) }}
>
    <div class="sidebar-header">
        <h2>Filters</h2>
        @if ($isDropdownAside)
            <p class="home-aside-header-copy">
                <span class="home-aside-header-primary">Refine Website</span>
                <span class="home-aside-header-secondary">by rating, category, and tags.</span>
            </p>
        @else
            <p>Refine websites by rating, category, and tags.</p>
        @endif
    </div>

    <div class="sidebar-section home-aside-dropdown">
        <div class="section-header" id="ratingHeader">
            <div class="section-left">
                <x-fas-star class="section-icon" aria-hidden="true" />
                <h3>Rating</h3>
            </div>
            <x-fas-chevron-down class="arrow-icon" aria-hidden="true" />
        </div>

        <div class="section-content" id="ratingContent">
            <div class="rating-options">
                <label class="rating-check"><input type="checkbox" value="all" :checked="filters.rating.length === 0" @change="clearFilters()"><span>All</span></label>
                @foreach ([5, 4, 3, 2, 1] as $rating)
                    <label class="rating-check">
                        <input type="checkbox" value="{{ $rating }}" 
                               :checked="filters.rating.includes('{{ $rating }}')"
                               @change="toggleFilter('rating', '{{ $rating }}')">
                        <span>{{ $rating }}+ Rating</span>
                    </label>
                @endforeach
            </div>
        </div>
    </div>

    <div class="sidebar-section home-aside-dropdown">
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
                <input type="text" id="categorySearch" placeholder="Search categories..." x-model="search.category">
            </div>

            <div class="category-list-container">
                <label class="category-check" data-filter-component="category">
                    <input type="checkbox" value="All" :checked="filters.category.length === 0" @change="clearFilters()">
                    <span>All</span>
                </label>

                @foreach ($categories as $index => $category)
                    <label class="category-check" data-filter-component="category"
                           x-show="'{{ strtolower($category->name) }}'.includes(search.category.toLowerCase()) && ({{ $index }} < 5 || search.showMoreCategories || search.category.length > 0)">
                        <input type="checkbox" value="{{ $category->slug }}"
                               :checked="filters.category.includes('{{ $category->slug }}')"
                               @change="toggleFilter('category', '{{ $category->slug }}')">
                        <span>{{ $category->name }}</span>
                    </label>
                @endforeach
            </div>

            @if ($categories->count() > 5)
                <button class="show-category-btn" id="showCategoryBtn" type="button" 
                        x-show="search.category.length === 0"
                        @click="search.showMoreCategories = !search.showMoreCategories"
                        x-text="search.showMoreCategories ? 'Show Less Categories' : 'Show More Categories'">
                    Show More Categories
                </button>
            @endif
        </div>
    </div>

    <div class="sidebar-section home-aside-dropdown">
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
                    <input type="text" id="tagSearch" placeholder="Search tags..." x-model="search.tag">
                </div>
            </div>

            <div class="tags-container" id="tagsContainer">
                <template x-for="tag in visibleTags" :key="tag">
                    <label class="tag-check" data-filter-component="tag">
                        <input type="checkbox" 
                               :value="tag"
                               :checked="filters.tags.includes(tag)"
                               @change="toggleFilter('tags', tag)">
                        <span x-text="tag"></span>
                    </label>
                </template>
            </div>
            
            <button class="show-tags-btn" id="showTagsBtn" type="button" 
                    x-show="filteredTags.length > 10"
                    @click="search.showMoreTags = !search.showMoreTags"
                    x-text="search.showMoreTags ? 'Show Less Tags' : 'Show More Tags'">
                Show More Tags
            </button>
        </div>
    </div>
</aside>
