@props(['compact' => false])

<form method="get" action="{{ route('home') }}" id="searchForm" class="w-auto" x-data="{ expanded: false }" @click.outside="expanded = false">
    {{-- Preserve existing query params (category, tags, etc.) --}}
    @foreach(request()->except('search') as $key => $value)
        @if(!is_array($value))
            <input type="hidden" name="{{ $key }}" value="{{ $value }}" />
        @else
            @foreach($value as $v)
                <input type="hidden" name="{{ $key }}[]" value="{{ $v }}" />
            @endforeach
        @endif
    @endforeach

    @if($compact)
        <div class="mobile-search-expand" :class="{ 'is-expanded': expanded }" style="font-family: var(--font-family);">
            <button type="button" class="mobile-search-icon-btn" @click="expanded = !expanded; if(expanded) $nextTick(() => $refs.searchInput.focus())" aria-label="Search">
                <x-fas-search class="icon" style="color: var(--accent-color);"/>
            </button>
            <div class="mobile-search-input-wrap" x-show="expanded" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 w-0" x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0 w-0">
                <input type="search" name="search" x-ref="searchInput" placeholder="Search by title, URL, category or tag..." aria-label="Search" value="{{ request('search') }}" @keydown.escape="expanded = false" style="font-family: var(--font-family); color: var(--text-color);" />
            </div>
        </div>
    @else
        <div class="desktop-search-container" style="font-family: var(--font-family);">
            <x-fas-search class="icon" style="color: var(--accent-color);"/>
            <input type="search" name="search" id="search" placeholder="Search by title, URL, category or tag..." aria-label="Search" value="{{ request('search') }}" style="font-family: var(--font-family); color: var(--text-color);" />
        </div>
    @endif
</form>
