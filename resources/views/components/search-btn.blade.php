<form method="post" id="searchForm" class="w-5xl">
    @csrf
    {{--                <label for="search" class="block mb-2.5 text-sm font-medium text-heading ">Search</label>--}}
    <div class="relative group">
        <label for="search" class="absolute inset-y-0 start-0 flex items-center ps-2 pointer-events-none ">
            <x-fas-search class="icon text-[var(--accent-color)]"/>
        </label>
        <input type="search" id="search" class="block p-3 ps-9 bg-[var(--background-color)]/20 border border-1 text-heading text-sm rounded-xl focus:border-[var(--accent)] shadow-xs placeholder:text-[var(--text-color)]" placeholder="Search" />
{{--        <button type="button" class="absolute end-1.5 bottom-1.5 text-white bg-brand hover:bg-brand-strong box-border border border-transparent focus:ring-4 focus:ring-brand-medium shadow-xs font-medium leading-5 rounded text-xs px-3 py-1.5 focus:outline-none">Search</button>--}}
    </div>
</form>
