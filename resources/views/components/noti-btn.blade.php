    <button
        data-tooltip-placement="bottom"
        type="button"
        data-tooltip-target="notification"
        class="relative shadow-md rounded-xl p-2"
    >
        <x-far-bell class="icon"/>
        <span class="absolute top-0 right-0 translate-x-1/2 text-sm text-[var(--accent-color)]">3</span>
    </button>
    <div id="notification" role="tooltip" class=" text-white absolute z-10 invisible inline-block px-3 py-2 text-md bg-gray-600 rounded-xl shadow-xs opacity-0 tooltip">
        Notification
    </div>
