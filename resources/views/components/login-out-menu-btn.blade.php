{{--@desktop--}}
<button id="dropdownDividerButton" data-dropdown-toggle="dropdownDivider" class="inline-flex items-center justify-center bg-brand box-border focus:border  focus:border-[var(--accent-color)] shadow-xl  leading-5 rounded-xl text-sm px-4 py-2.5 focus:outline-none" type="button">
    Login / Register
    <svg class="w-4 h-4 ms-1.5 -me-0.5" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24"><path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m19 9-7 7-7-7"/></svg>
</button>

<!-- Dropdown menu -->
<div id="dropdownDivider" class="z-10 hidden bg-neutral-100 border rounded-xl divide-y divide-default-medium shadow-md w-44">
    <ul class="p-2 text-sm text-body font-medium" aria-labelledby="dropdownDividerButton">
        <li>
            <a href="{{route('login')}}" class="inline-flex items-center w-full p-2 hover:bg-neutral-200 hover:text-[var(--accent-color)] rounded">Login</a>
        </li>
    </ul>
    <div class="p-2 text-sm text-body font-medium">
        <a href="{{route('register')}}" class="inline-flex items-center w-full p-2 hover:bg-neutral-200 hover:text-[var(--accent-color)] rounded">Register</a>
    </div>
</div>
{{--@enddesktop--}}

{{--@mobile--}}
{{--<a href="{{ route('register') }}" {{ $attributes->merge(['class' => 'mobile-nav-item']) }}>--}}
{{--    <x-far-user class="icon"/>--}}
{{--    <span>Register</span>--}}
{{--</a>--}}
{{--@endmobile--}}
