
<button id="dropdownDividerButton" data-dropdown-toggle="dropdownDivider" class="inline-flex items-center justify-center bg-brand box-border  hover:bg-brand-strong focus:ring-4 focus:ring-brand-medium shadow-xl  leading-5 rounded-xl text-sm px-4 py-2.5 focus:outline-none" type="button">
    Login / Register
    <svg class="w-4 h-4 ms-1.5 -me-0.5" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24"><path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m19 9-7 7-7-7"/></svg>
</button>

<!-- Dropdown menu -->
<div id="dropdownDivider" class="z-10 hidden bg-neutral-primary-medium border border-default-medium rounded-base divide-y divide-default-medium shadow-lg w-44">
    <ul class="p-2 text-sm text-body font-medium" aria-labelledby="dropdownDividerButton">
        <li>
            <a href="{{route('login')}}" class="inline-flex items-center w-full p-2 hover:bg-neutral-tertiary-medium hover:text-heading rounded">Login</a>
        </li>
    </ul>
    <div class="p-2 text-sm text-body font-medium">
        <a href="{{route('register')}}" class="inline-flex items-center w-full p-2 hover:bg-neutral-tertiary-medium hover:text-heading rounded">Register</a>
    </div>
</div>
