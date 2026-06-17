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

<div x-data="{ 
    showTrigger: true, 
    lastScrollY: window.scrollY,
    open: false,
    handleScroll() {
        let currentScrollY = window.scrollY;
        if (Math.abs(currentScrollY - this.lastScrollY) < 5) return;
        this.showTrigger = currentScrollY < this.lastScrollY;
        this.lastScrollY = currentScrollY;
    }
}" @scroll.window="handleScroll" class="md:h-full">
    {{-- Trigger --}}
    <button x-show="showTrigger" x-transition @click="open = true" 
            class="fixed top-20 left-4 z-40 bg-accent text-white p-3 rounded-full shadow-lg md:hidden"
            aria-label="Open filters">
        <x-fas-filter class="size-5" />
    </button>

    {{-- Backdrop --}}
    <div x-show="open" x-cloak class="fixed inset-0 bg-black/50 z-[60] md:hidden" @click="open = false" x-transition.opacity></div>

    {{-- Bottom Sheet / Sidebar --}}
    <aside
        id="sidebar"
        x-show="open"
        x-cloak
        data-menu-bar-location="{{ $menuBarLocation }}"
        data-dropdown-aside="{{ $isDropdownAside ? 'true' : 'false' }}"
        {{ $attributes->class([
            'sidebar', 
            'home-aside', 
            'home-aside--'.$menuBarLocation, 
            'fixed inset-y-0 left-0 z-[70] w-3/4 bg-white dark:bg-gray-800 shadow-2xl p-6 overflow-y-auto transform transition-transform duration-300 md:static md:block md:w-[280px] md:shadow-none'
        ]) }}
        :class="open ? 'translate-x-0' : '-translate-x-full'"
        @click.outside="if(window.innerWidth < 768) open = false"
    >
        {{-- Close Button --}}
        <button class="absolute top-2 right-2 p-2 md:hidden" @click="open = false">
            <x-fas-xmark class="size-6 text-gray-500 dark:text-gray-400" />
        </button>

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

    <div class="sidebar-section home-aside-dropdown" x-data="{ openSection: true }">
        <div class="section-header" id="ratingHeader" @click="openSection = !openSection" :class="{ 'active': openSection }">
            <div class="section-left">
                <x-fas-star class="section-icon" aria-hidden="true" />
                <h3>Rating</h3>
            </div>
            <x-fas-chevron-down class="arrow-icon" aria-hidden="true" />
        </div>

        <div class="section-content" id="ratingContent" x-show="openSection">
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

    <div class="sidebar-section home-aside-dropdown" x-data="{ openSection: true }">
        <div class="section-header" id="categoryHeader" @click="openSection = !openSection" :class="{ 'active': openSection }">
            <div class="section-left">
                <x-fas-layer-group class="section-icon" aria-hidden="true" />
                <h3>Categories</h3>
            </div>
            <x-fas-chevron-down class="arrow-icon" aria-hidden="true" />
        </div>

        <div class="section-content" id="categoryContent" x-show="openSection">
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

    <div class="sidebar-section home-aside-dropdown" x-data="{ openSection: true }">
        <div class="section-header" id="tagsHeader" @click="openSection = !openSection" :class="{ 'active': openSection }">
            <div class="section-left">
                <x-fas-tags class="section-icon" aria-hidden="true" />
                <h3>Tags</h3>
            </div>
            <x-fas-chevron-down class="arrow-icon" aria-hidden="true" />
        </div>

        <div class="section-content" id="tagsContent" x-show="openSection">
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
</div>
