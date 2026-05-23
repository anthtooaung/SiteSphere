@php
    $isHome = request()->routeIs('home');
@endphp

@desktop
<a href="{{ route('home') }}" @class(['desktop-link', 'active' => $isHome]) @if($isHome) aria-current="page" @endif>
    <div class="md:flex gap-2">
        <x-fas-home class="icon"/>
        <span>Home</span>
    </div>
</a>
@enddesktop

@mobile
<a href="{{ route('home') }}" {{ $attributes->class(['mobile-nav-item', 'active' => $isHome]) }} @if($isHome) aria-current="page" @endif>
    <x-fas-home class="icon"/>
    <span>Home</span>
</a>
@endmobile
