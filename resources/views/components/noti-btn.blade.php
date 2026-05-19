@desktop
<button
    data-tooltip-placement="bottom"
    type="button"
    data-tooltip-target="notification"
    {{ $attributes->merge(['class' => 'noti-button']) }}
>
    <x-far-bell class="icon"/>
    <span class="absolute top-0.5 right-0.5 flex items-center justify-center bg-red-500 text-white text-[10px] font-black rounded-full w-4 h-4 border border-white shadow-sm">3</span>
</button>
<div id="notification" role="tooltip" class=" text-white absolute z-10 invisible inline-block px-3 py-2 text-md bg-gray-600 rounded-xl shadow-xs opacity-0 tooltip">
    Notification
</div>
@enddesktop

@mobile
<a href="#" {{ $attributes->merge(['class' => 'mobile-nav-item relative']) }}>
    <x-far-bell class="icon"/>
    <span class="mobile-badge">3</span>
    <span>Alerts</span>
</a>
@endmobile
