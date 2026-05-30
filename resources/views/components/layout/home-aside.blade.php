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
                <label class="rating-check"><input type="checkbox" value="all"><span>All</span></label>
                @foreach ([5, 4, 3, 2, 1] as $rating)
                    <label class="rating-check"><input type="checkbox" value="{{ $rating }}"><span>{{ $rating }}+ Rating</span></label>
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

            @if ($categories->count() > 5)
                <button class="show-category-btn" id="showCategoryBtn" type="button">Show More Categories</button>
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
                    <input type="text" id="tagSearch" placeholder="Search tags...">
                </div>
            </div>

            <div class="tags-container" id="tagsContainer"></div>
            <button class="show-tags-btn" id="showTagsBtn" type="button">Show More Tags</button>
        </div>
    </div>
</aside>
