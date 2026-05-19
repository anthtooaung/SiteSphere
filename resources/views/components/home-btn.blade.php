@desktop
<a href="#" class="desktop-link" aria-current="page">
    <x-fas-home class="icon"/>
    <span>Home</span>
</a>
@enddesktop

@mobile
<a href="#" {{ $attributes->merge(['class' => 'mobile-nav-item']) }}>
    <x-fas-home class="icon"/>
    <span>Home</span>
</a>
@endmobile
