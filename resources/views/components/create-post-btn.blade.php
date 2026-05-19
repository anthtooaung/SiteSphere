@desktop
<a href="#"
   {{ $attributes->merge(['class' => 'write-button']) }}
   data-tooltip-placement="bottom"
   data-tooltip-target="create-post"
>
    <x-fas-plus class="icon text-white"/>
</a>
<div id="create-post" role="tooltip" class=" text-white absolute z-10 invisible inline-block px-3 py-2 text-md bg-gray-600 rounded-xl shadow-xs opacity-0 tooltip">
    Create Post
</div>
@enddesktop

@mobile
<a href="#" {{ $attributes->merge(['class' => 'mobile-add-button']) }} aria-label="Write review">
    <x-fas-plus class="icon" style="font-size: 1.2rem;"/>
</a>
@endmobile
