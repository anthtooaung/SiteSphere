@php
    $isHome = request()->routeIs('home');
    $isLanding = request()->routeIs(['welcome', 'about-us']);
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
<a href="{{ route('home') }}" 
   {{ $attributes->class([
       'mobile-nav-item', 
       'active' => $isHome,
       'flex-row gap-2 px-3 py-2 font-bold text-sm' => $isLanding,
       'flex-col' => !$isLanding
   ]) }} 
   @if($isHome) aria-current="page" @endif>
    <x-fas-home class="icon"/>
    <span>Home</span>
</a>
@endmobile
