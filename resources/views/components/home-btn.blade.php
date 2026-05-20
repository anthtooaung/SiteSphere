@desktop
<a href="#" class="desktop-link" aria-current="page">
    <div class="md:flex gap-2">
        <x-fas-home class="icon"/>
        <span>Home</span>
    </div>
</a>
@enddesktop

@mobile
<a href="#" {{ $attributes->merge(['class' => 'mobile-nav-item']) }}>
    <x-fas-home class="icon"/>
    <span>Home</span>
</a>
@endmobile
