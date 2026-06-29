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

<style>
    @media (max-width: 767px) {
        .mobile-sidebar-z {
            z-index: 10000 !important;
        }
        .mobile-backdrop-z {
            z-index: 9999 !important;
        }
    }
</style>

<div x-data="{ 
    open: false, 
    showTrigger: true, 
    lastScrollY: window.scrollY 
}" 
@keydown.escape.window="open = false" 
@scroll.window="
    let currentScrollY = window.scrollY;
    if (currentScrollY > lastScrollY && currentScrollY > 50) {
        showTrigger = false;
    } else if (lastScrollY - currentScrollY > 5 || currentScrollY < 10) {
        showTrigger = true;
    }
    lastScrollY = currentScrollY;
"
class="{{ $isDropdownAside ? 'md:w-full md:flex md:justify-center' : 'md:h-full' }} home-aside-wrapper home-aside-wrapper--{{ $menuBarLocation }}">
    {{-- Mobile Filter Trigger Button --}}
    <button 
        class="fixed {{ $menuBarLocation === 'right' ? 'right-4' : 'left-4' }} top-[72px] z-40 p-2 rounded-full shadow-lg border border-gray-200 dark:border-gray-700 transition-transform duration-300 md:hidden"
        style="background-color: var(--background-color);"
        :class="showTrigger ? 'translate-x-0' : '{{ $menuBarLocation === 'right' ? 'translate-x-20' : '-translate-x-20' }}'"
        type="button" 
        @click="open = true" 
        aria-controls="sidebar" 
        aria-expanded="false" 
        aria-label="Open filters"
    >
        <x-fas-filter class="size-5 text-indigo-600 dark:text-indigo-400" aria-hidden="true" />
    </button>

    {{-- Backdrop --}}
    <div x-show="open" x-cloak class="fixed inset-0 bg-black/50 mobile-backdrop-z md:hidden" @click="open = false" x-transition.opacity></div>

    {{-- Mobile Sidebar (75% width) --}}
    <aside
        id="sidebar"
        x-show="open"
        x-cloak
        data-menu-bar-location="{{ $menuBarLocation }}"
        data-dropdown-aside="{{ $isDropdownAside ? 'true' : 'false' }}"
        {{ $attributes->class([
            'sidebar mobile-sidebar-z', 
            'home-aside', 
            'home-aside--'.$menuBarLocation, 
            'home-aside--dropdown' => $isDropdownAside,
            'layout-menu--topbar' => $isDropdownAside,
            'layout-menu--horizontal' => $isDropdownAside,
            'layout-menu--'.$menuBarLocation => $isDropdownAside,
            'fixed inset-y-0 w-[75%] max-w-[300px] shadow-2xl p-4 overflow-y-auto transform transition-transform duration-300 ease-in-out md:static md:!block md:z-0 md:shadow-none md:transform-none md:transition-none md:rounded-none',
            'right-0' => $menuBarLocation === 'right',
            'left-0' => $menuBarLocation !== 'right',
            'md:h-full md:w-[280px]' => ! $isDropdownAside,
            'md:h-auto md:w-full md:py-2 md:px-6' => $isDropdownAside,
            'md:max-w-none' => $isDropdownAside,
            'md:overflow-visible' => $isDropdownAside,
        ]) }}
        x-transition:enter="transition ease-out duration-300 transform"
        x-transition:enter-start="{{ $menuBarLocation === 'right' ? 'translate-x-full' : '-translate-x-full' }}"
        x-transition:enter-end="translate-x-0"
        x-transition:leave="transition ease-in duration-300 transform"
        x-transition:leave-start="translate-x-0"
        x-transition:leave-end="{{ $menuBarLocation === 'right' ? 'translate-x-full' : '-translate-x-full' }}"
        @click.outside="if(window.innerWidth < 768) open = false"
        style="background-color: var(--background-color); color: var(--text-color); font-family: var(--font-family);"
    >
        {{-- Close Button --}}
        <button class="absolute top-0 right-0 p-4 z-10 md:hidden" @click="open = false">
            <x-fas-xmark class="size-6 text-gray-500 dark:text-gray-400" />
        </button>

        @if ($isDropdownAside)
            <nav class="layout-menu-topbar-nav" aria-label="Filters menu">
                <ul class="layout-menu-topbar-list">
                    <li class="layout-menu-topbar-item layout-menu-topbar-settings home-aside-dropdown" x-data="{ openSection: false }" @click.outside="openSection = false">
                        <button type="button" class="layout-menu-topbar-link" :class="{ 'active': openSection }" id="ratingHeader" @click="openSection = !openSection">
                            <x-fas-star class="icon" aria-hidden="true" />
                            <span>Rating</span>
                            <x-fas-chevron-down class="layout-menu-topbar-chevron" aria-hidden="true" />
                        </button>

                        <div class="layout-menu-topbar-dropdown section-content" id="ratingContent" x-show="openSection" x-cloak>
                            <div class="rating-options">
                                <label class="rating-check"><input type="checkbox" value="all" :checked="filters.rating.length === 0" @change="filters.rating = []; updateResults()"><span>All</span></label>
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
                    </li>

                    <li class="layout-menu-topbar-item layout-menu-topbar-settings home-aside-dropdown" x-data="{ openSection: false }" @click.outside="openSection = false">
                        <button type="button" class="layout-menu-topbar-link" :class="{ 'active': openSection }" id="categoryHeader" @click="openSection = !openSection">
                            <x-fas-layer-group class="icon" aria-hidden="true" />
                            <span>Categories</span>
                            <x-fas-chevron-down class="layout-menu-topbar-chevron" aria-hidden="true" />
                        </button>

                        <div class="layout-menu-topbar-dropdown section-content" id="categoryContent" x-show="openSection" x-cloak>
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
                    </li>

                    <li class="layout-menu-topbar-item layout-menu-topbar-settings home-aside-dropdown" x-data="{ openSection: false }" @click.outside="openSection = false">
                        <button type="button" class="layout-menu-topbar-link" :class="{ 'active': openSection }" id="tagsHeader" @click="openSection = !openSection">
                            <x-fas-tags class="icon" aria-hidden="true" />
                            <span>Tags</span>
                            <x-fas-chevron-down class="layout-menu-topbar-chevron" aria-hidden="true" />
                        </button>

                        <div class="layout-menu-topbar-dropdown section-content" id="tagsContent" x-show="openSection" x-cloak>
                            <div class="tag-tools">
                                <div class="tag-search-box">
                                    <x-fas-search class="search-icon" aria-hidden="true" />
                                    <input type="text" id="tagSearch" placeholder="Search tags..." x-model="search.tag">
                                </div>
                            </div>

                            <div class="tags-container" id="tagsContainer">
                                <label class="tag-check" data-filter-component="tag">
                                    <input type="checkbox" value="All" :checked="filters.tags.length === 0" @change="clearFilters()">
                                    <span>All</span>
                                </label>
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
                    </li>
                </ul>
            </nav>
        @else
            <div class="sidebar-header">
                <h2>Filters</h2>
                <p>Refine websites by rating, category, and tags.</p>
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
                        <label class="rating-check"><input type="checkbox" value="all" :checked="filters.rating.length === 0" @change="filters.rating = []; updateResults()"><span>All</span></label>
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
                        <label class="tag-check" data-filter-component="tag">
                            <input type="checkbox" value="All" :checked="filters.tags.length === 0" @change="clearFilters()">
                            <span>All</span>
                        </label>
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
        @endif
    </aside>
</div>
