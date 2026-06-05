<form method="post" id="searchForm" class="w-auto">
    @csrf
    <div class="desktop-search-container" style="font-family: var(--font-family);">
        <x-fas-search class="icon" style="color: var(--accent-color);"/>
        <input type="search" id="search" placeholder="Search..." aria-label="Search" style="font-family: var(--font-family); color: var(--text-color);" />
    </div>
</form>
