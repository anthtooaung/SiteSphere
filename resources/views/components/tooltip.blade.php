@props(['content'])

<div x-data="{ show: false, tooltipId: 'tooltip-' + Math.random().toString(36).substr(2, 9) }" {{ $attributes->merge(['class' => 'relative inline-flex']) }} @mouseenter="show = true" @mouseleave="show = false" @focusin="show = true" @focusout="show = false">
    <div x-show="show" 
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 translate-y-1"
         x-transition:enter-end="opacity-100 translate-y-0"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100 translate-y-0"
         x-transition:leave-end="opacity-0 translate-y-1"
         :id="tooltipId"
         role="tooltip"
         x-cloak
         class="absolute z-50 px-2 py-1 text-xs rounded shadow-lg whitespace-nowrap -top-8 left-1/2 -translate-x-1/2 pointer-events-none ui-tooltip">
        {{ $content }}
    </div>
    <div x-bind:aria-describedby="tooltipId" class="inline-flex">
        {{ $slot }}
    </div>
</div>
