
@desktop
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
@enddesktop

@mobile
    <a href="{{ route('posts.create') }}" {{ $attributes->merge(['class' => 'mobile-add-button flex items-center justify-center transition-transform duration-300 hover:rotate-90']) }} aria-label="Write review" style="background-color: var(--accent-color); font-family: var(--font-family);">
        <x-fas-plus class="icon" style="font-size: 1.2rem; color: #ffffff;"/>
    </a>
@endmobile
