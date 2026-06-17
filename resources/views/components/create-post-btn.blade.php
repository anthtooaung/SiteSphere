@php
    $isLanding = request()->routeIs(['welcome', 'about-us']);
@endphp

@desktop
@if($isLanding)
    <a href="{{ route('posts.create') }}" class="desktop-link">
        <div class="md:flex gap-2">
            <x-fas-plus class="icon"/>
            <span>Create</span>
        </div>
    </a>
@else
    <a href="{{ route('posts.create') }}"
       {{ $attributes->merge(['class' => 'write-button transition-transform duration-300 hover:rotate-90']) }}
       data-tooltip-placement="bottom"
       data-tooltip-target="create-post"
       style="background-color: var(--accent-color); font-family: var(--font-family);"
    >
        <x-fas-plus class="icon text-white"/>
    </a>
    <div id="create-post" role="tooltip" class="absolute z-10 invisible inline-block px-3 py-2 text-md rounded-xl shadow-xs opacity-0 tooltip" style="font-family: var(--font-family); background-color: var(--text-color, #1f2937); color: var(--background-color, #ffffff);">
        Create Post
    </div>
@endif
@enddesktop

@mobile
@if($isLanding)
    <a href="{{ route('posts.create') }}" 
       {{ $attributes->class([
           'mobile-nav-item flex-row gap-2 px-3 py-2 font-bold text-sm'
       ]) }}>
        <x-fas-plus class="icon"/>
        <span>Create</span>
    </a>
@else
    <a href="{{ route('posts.create') }}" {{ $attributes->merge(['class' => 'mobile-add-button transition-transform duration-300 hover:rotate-90']) }} aria-label="Write review" style="background-color: var(--accent-color); font-family: var(--font-family);">
        <x-fas-plus class="icon" style="font-size: 1.2rem; color: #ffffff;"/>
    </a>
@endif
@endmobile
