@props(['content'])

<div x-data="{ show: false }" class="relative inline-flex" @mouseenter="show = true" @mouseleave="show = false" @focusin="show = true" @focusout="show = false">
    <div x-show="show" 
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 translate-y-1"
         x-transition:enter-end="opacity-100 translate-y-0"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100 translate-y-0"
         x-transition:leave-end="opacity-0 translate-y-1"
         role="tooltip"
         x-cloak
         class="absolute z-50 px-2 py-1 text-xs text-white bg-gray-800 rounded shadow-lg whitespace-nowrap -top-8 left-1/2 -translate-x-1/2 pointer-events-none">
        {{ $content }}
    </div>
    {{ $slot }}
</div>
