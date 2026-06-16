<form method="get" action="{{ route('home') }}" id="searchForm" class="w-auto">
    <div class="desktop-search-container" style="font-family: var(--font-family);">
        <x-fas-search class="icon" style="color: var(--accent-color);"/>
        <input type="search" name="search" id="search" placeholder="Search..." aria-label="Search" value="{{ request('search') }}" style="font-family: var(--font-family); color: var(--text-color);" />
    </div>
</form>
