# Mobile Bottom-Sheet Account Menu Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Transform the mobile "Profile" button dropdown into a mobile-friendly fixed bottom-sheet drawer with clearly grouped navigation links.

**Architecture:** Replace the current hidden dropdown div in `profile-menu-btn.blade.php` with a bottom-sheet container managed by AlpineJS. Group the existing menu items into Profile, Admin, and Settings sections.

**Tech Stack:** Laravel (Blade), Tailwind CSS, AlpineJS.

---

### Task 1: Create Bottom-Sheet Account Menu and Backdrop

**Files:**
- Modify: `resources/views/components/profile-menu-btn.blade.php`

- [ ] **Step 1: Replace mobile dropdown with bottom-sheet drawer**

Modify the `@mobile` section of `profile-menu-btn.blade.php`. Wrap the trigger and menu in an AlpineJS component. Replace the hidden dropdown div with a fixed bottom-sheet container and a semi-transparent backdrop.

```blade
{{-- In profile-menu-btn.blade.php @mobile section --}}
<div x-data="{ open: false }" @keydown.escape.window="open = false" class="mobile-account-menu-wrap">
    <button
        type="button"
        class="mobile-nav-item"
        @click="open = true"
        aria-label="Account menu"
        :aria-expanded="open.toString()"
    >
        @if($user->user_image)
            <img src="{{ $user->getAvatarUrl() }}" alt="{{ $user->name }}" class="size-6 rounded-full object-cover" />
        @else
            <x-far-user class="icon"/>
        @endif
        <span>Profile</span>
    </button>

    {{-- Backdrop --}}
    <div x-show="open" x-cloak class="fixed inset-0 bg-black/50 z-[60]" @click="open = false" x-transition.opacity></div>

    {{-- Bottom Sheet --}}
    <div
        id="mobileAccountMenu"
        x-show="open"
        x-cloak
        class="fixed inset-x-0 bottom-0 z-[70] h-[75vh] bg-white dark:bg-gray-800 rounded-t-[2.5rem] shadow-2xl p-6 overflow-y-auto transform transition-transform duration-300 ease-in-out"
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="translate-y-full"
        x-transition:enter-end="translate-y-0"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="translate-y-0"
        x-transition:leave-end="translate-y-full"
    >
        {{-- Drawer Handle --}}
        <div class="w-10 h-1 bg-gray-300 dark:bg-gray-600 rounded-full mx-auto mb-6"></div>

        <div class="flex justify-between items-center mb-6">
            <h3 class="text-xl font-bold text-gray-900 dark:text-white">Account & Settings</h3>
            <button @click="open = false" class="p-2 bg-gray-100 dark:bg-gray-700 rounded-full text-gray-500 dark:text-gray-400">
                <x-fas-xmark class="size-4" />
            </button>
        </div>

        {{-- User Summary --}}
        <div class="flex items-center gap-4 mb-8 pb-4 border-b border-gray-100 dark:border-gray-700">
            @if($user->user_image)
                <img src="{{ $user->getAvatarUrl() }}" alt="{{ $user->name }}" class="size-12 rounded-full object-cover" />
            @else
                <div class="size-12 bg-gray-100 dark:bg-gray-700 rounded-full flex items-center justify-center">
                    <x-far-user class="size-6 text-gray-400" />
                </div>
            @endif
            <div>
                <div class="font-bold text-lg text-gray-900 dark:text-white">{{ $user->name }}</div>
                <div class="text-sm text-gray-500 dark:text-gray-400 flex items-center gap-1">
                    Verified <x-fas-check-circle class="size-3 text-indigo-500" />
                </div>
            </div>
        </div>

        {{-- Content Grouping --}}
        <div class="space-y-8 pb-8">
            {{-- Profile Section --}}
            <div>
                <p class="text-xs font-semibold text-gray-400 dark:text-gray-500 uppercase tracking-wider mb-4">Profile</p>
                <ul class="space-y-4">
                    @foreach($profileMenuItems as $menuItem)
                        <li>
                            <a href="{{ $menuItem['href'] }}" class="flex items-center gap-3 text-gray-700 dark:text-gray-300 font-medium">
                                <span class="w-5 text-center">
                                    @if($menuItem['label'] === 'View Profile') 👤 @else 🔖 @endif
                                </span>
                                {{ $menuItem['label'] }}
                            </a>
                        </li>
                    @endforeach
                </ul>
            </div>

            {{-- Admin Section --}}
            @if($isAdmin)
                <div>
                    <p class="text-xs font-semibold text-gray-400 dark:text-gray-500 uppercase tracking-wider mb-4">Admin</p>
                    <ul class="space-y-4">
                        @foreach($adminMenuItems as $menuItem)
                            <li>
                                <a href="{{ $menuItem['href'] }}" class="flex items-center gap-3 text-gray-700 dark:text-gray-300 font-medium">
                                    <span class="w-5 text-center">
                                        @if($menuItem['label'] === 'Dashboard') 📊 @elseif($menuItem['label'] === 'Users') 👥 @else 🚩 @endif
                                    </span>
                                    {{ $menuItem['label'] }}
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endif

            {{-- Settings Section --}}
            <div>
                <p class="text-xs font-semibold text-gray-400 dark:text-gray-500 uppercase tracking-wider mb-4">Settings</p>
                <ul class="space-y-4">
                    @foreach($settingMenuItems as $menuItem)
                        <li>
                            <a href="{{ $menuItem['href'] }}" class="flex items-center gap-3 text-gray-700 dark:text-gray-300 font-medium">
                                <span class="w-5 text-center">
                                    @if($menuItem['label'] === 'Edit Profile') ✏️ @elseif($menuItem['label'] === 'Appearance') 🎨 @elseif($menuItem['label'] === 'Edit Tag') 🏷️ @else 🛡️ @endif
                                </span>
                                {{ $menuItem['label'] }}
                            </a>
                        </li>
                    @endforeach
                </ul>
            </div>

            {{-- Logout Section --}}
            <div class="pt-4 border-t border-gray-100 dark:border-gray-700">
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="flex items-center gap-3 text-red-500 font-semibold">
                        <span class="w-5 text-center">🚪</span> Logout
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
```

- [ ] **Step 2: Commit**

```bash
git add resources/views/components/profile-menu-btn.blade.php
git commit -m "feat: implement mobile bottom-sheet account menu"
```

### Task 2: Synchronize Mobile Menu in Dashboard Layout

**Files:**
- Modify: `resources/views/components/layout/menu.blade.php`

- [ ] **Step 1: Ensure consistency in dashboard sidebar menu**

While `profile-menu-btn.blade.php` handles the global nav, `menu.blade.php` is used as a sidebar on dashboard pages. Ensure that on mobile, `menu.blade.php` either hides (if redundant with the bottom nav) or also behaves as a bottom-sheet. Given the design, the bottom nav "Profile" button is the primary access point, so the vertical sidebar in `menu.blade.php` should likely be hidden on mobile if it's not already.

```blade
{{-- In menu.blade.php --}}
{{-- Add md:flex to the sidebar and hidden to the base class if it's only for desktop --}}
<aside id="layoutMenu" ...
    {{ $attributes->class(['layout-menu', 'hidden md:flex', ...]) }}>
```

- [ ] **Step 2: Commit**

```bash
git add resources/views/components/layout/menu.blade.php
git commit -m "feat: ensure mobile consistency for account menu sidebar"
```

### Task 3: Verification

- [ ] **Step 1: Test Mobile View**

Open the application on a mobile emulator.
1. Click the "Profile" icon in the bottom navigation.
2. Verify the backdrop appears and the account menu slides up from the bottom.
3. Verify the menu items are correctly grouped (Profile, Admin, Settings).
4. Verify the close button and backdrop click dismiss the menu.
5. Verify clicking a link (e.g., "Edit Profile") navigates correctly.
6. Verify "Logout" works.

- [ ] **Step 2: Test Desktop View**

Verify that the desktop account button dropdown in the top nav still works as expected and hasn't been affected by the mobile changes.
Verify that the dashboard sidebar (`menu.blade.php`) still functions correctly on desktop.
