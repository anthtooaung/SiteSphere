<form method="post" id="searchForm" class="w-auto">
    @csrf
    <div class="desktop-search-container">
        <x-fas-search class="icon text-[var(--accent-color)]"/>
        <input type="search" id="search" placeholder="Search..." aria-label="Search" />
    </div>
</form>
