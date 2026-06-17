@php
    $isLanding = request()->routeIs(['welcome', 'about-us']);
@endphp

@desktop
<button data-dropdown-toggle="mega-menu-icons-dropdown" class="desktop-link">
    <x-fas-layer-group class="icon"/>
    <span>Categories</span>
    <svg class="w-4 h-4 ms-1.5 -me-0.5" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24"><path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m19 9-7 7-7-7"/></svg>
</button>

<div
    id="mega-menu-icons-dropdown"
    class="category-menu-dropdown absolute z-10 hidden text-sm rounded-xl shadow-md"
>
    <div class="category-menu-panel p-4 text-heading md:pb-4">
        @if ($categories->isEmpty())
            <p class="text-body">No categories available.</p>
        @else
            <ul class="category-menu-grid grid gap-3 font-normal sm:grid-cols-2 md:grid-cols-3" aria-labelledby="mega-menu-icons-dropdown-button">
                @foreach ($categories as $category)
                    <li>
                        <a href="{{ route('home', ['category' => $category->slug]) }}" class="inline-flex items-center text-body hover:text-fg-brand">
                            {{ $category->name }}
                        </a>
                    </li>
                @endforeach
            </ul>
        @endif
    </div>
</div>
@enddesktop

@mobile
@if (in_array($mobileMode, ['both', 'trigger'], true))
    <button
        type="button"
        {{ $attributes->class([
            'mobile-nav-item',
            'flex-row gap-2 px-3 py-2 font-bold text-sm' => $isLanding,
            'flex-col' => !$isLanding
        ]) }}
        aria-label="Open categories"
    >
        <x-fas-layer-group class="icon"/>
        <span>Categories</span>
    </button>
@endif

@if (in_array($mobileMode, ['both', 'overlay'], true))
    <div {{ $attributes->merge(['class' => 'mobile-menu-overlay category-mobile-overlay']) }} id="mobileCategoryOverlay">
        <button
            type="button"
            class="mobile-close-button category-mobile-close"
            @click="$dispatch('close-category')"
            aria-label="Close categories"
        >
            <x-fas-times class="size-8"/>
        </button>



        @forelse ($categories as $category)
            <a href="{{ route('home', ['category' => $category->slug]) }}" class="mobile-overlay-link">
                <x-fas-layer-group class="icon size-8"/>
                {{ $category->name }}
            </a>
        @empty
            <span class="mobile-overlay-link">No categories available.</span>
        @endforelse
    </div>
@endif
@endmobile
