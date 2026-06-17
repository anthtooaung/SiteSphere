# Mobile Bottom-Sheet Filter Drawer Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Transform the desktop sidebar filter component into a mobile-friendly fixed bottom-sheet drawer.

**Architecture:** Wrap the existing content of `home-aside.blade.php` in a new container that handles fixed positioning, transitions, and backdrop rendering for mobile viewports.

**Tech Stack:** Laravel (Blade), Tailwind CSS, AlpineJS.

---

### Task 1: Create Bottom-Sheet Container and Backdrop

**Files:**
- Modify: `resources/views/components/layout/home-aside.blade.php`

- [ ] **Step 1: Wrap existing aside content in a new container**

Modify `home-aside.blade.php` to add a wrapper div that will act as the bottom-sheet container. Add Tailwind classes for fixed positioning, sliding animation, and rounded top corners.

```blade
{{-- In home-aside.blade.php --}}
<div x-data="{ open: false }" @keydown.escape.window="open = false">
    {{-- Trigger --}}
    <button class="menu-icon" id="sidebarToggle" type="button" @click="open = true" aria-controls="sidebar" aria-expanded="false" aria-label="Open sidebar">
        <x-fas-bars aria-hidden="true" />
    </button>

    {{-- Backdrop --}}
    <div x-show="open" x-cloak class="fixed inset-0 bg-black/50 z-40" @click="open = false" x-transition.opacity></div>

    {{-- Bottom Sheet --}}
    <aside
        id="sidebar"
        x-show="open"
        x-cloak
        class="fixed inset-x-0 bottom-0 z-50 h-[75vh] bg-white dark:bg-gray-800 rounded-t-2xl shadow-xl p-4 overflow-y-auto transform transition-transform duration-300 ease-in-out"
        x-transition:enter="translate-y-full"
        x-transition:enter-end="translate-y-0"
        x-transition:leave="translate-y-0"
        x-transition:leave-end="translate-y-full"
        @click.outside="open = false"
    >
        {{-- Close Button --}}
        <button class="absolute top-2 right-2 p-2" @click="open = false">
            <x-fas-xmark class="size-6" />
        </button>

        {{-- Original Content --}}
        {{-- ... (The rest of the existing sidebar content) ... --}}
    </aside>
</div>
```

- [ ] **Step 2: Commit**

```bash
git add resources/views/components/layout/home-aside.blade.php
git commit -m "feat: implement mobile bottom-sheet filter drawer"
```

### Task 2: Refine Desktop Layout for Bottom-Sheet Transition

**Files:**
- Modify: `resources/views/components/layout/home-aside.blade.php`

- [ ] **Step 1: Ensure desktop behavior is preserved**

Adjust the `aside` classes to ensure the bottom-sheet styling only applies on mobile, reverting to the original sidebar behavior on desktop.

```blade
{{-- In home-aside.blade.php --}}
{{-- Use responsive variants for classes --}}
<aside
    id="sidebar"
    x-show="open"
    x-cloak
    class="fixed inset-x-0 bottom-0 z-50 h-[75vh] md:h-auto md:static md:block md:w-auto bg-white dark:bg-gray-800 rounded-t-2xl md:rounded-none shadow-xl md:shadow-none p-4 overflow-y-auto md:overflow-visible transform transition-transform duration-300 ease-in-out md:transform-none"
    {{-- ... (transition classes) ... --}}
>
    {{-- ... --}}
</aside>
```

- [ ] **Step 2: Commit**

```bash
git add resources/views/components/layout/home-aside.blade.php
git commit -m "feat: ensure desktop sidebar functionality in home-aside"
```

### Task 3: Cleanup Legacy Sidebar CSS and JS

**Files:**
- Modify: `resources/css/homepage.css`
- Modify: `resources/js/homepage.js`

- [ ] **Step 1: Remove legacy sidebar styles**

Identify and remove mobile-specific sidebar styles in `homepage.css` (typically under `@media (max-width: 900px)`) that conflict with the new Tailwind implementation.

- [ ] **Step 2: Remove legacy sidebar JS logic**

Remove the native JavaScript event listeners and class toggling for the sidebar in `homepage.js` to avoid conflicts with AlpineJS state management.

- [ ] **Step 3: Commit**

```bash
git add resources/css/homepage.css resources/js/homepage.js
git commit -m "refactor: remove legacy sidebar CSS and JS to prevent conflicts"
```

### Task 4: Refactor Sidebar Dropdowns to AlpineJS

**Files:**
- Modify: `resources/views/components/layout/home-aside.blade.php`

- [ ] **Step 1: Implement AlpineJS state for dropdown sections**

Update each filter section (Rating, Categories, Tags) in `home-aside.blade.php` to use AlpineJS for toggling visibility instead of relying on the removed legacy JS.

```blade
{{-- Example for one section --}}
<div class="sidebar-section home-aside-dropdown" x-data="{ openSection: true }">
    <div class="section-header" @click="openSection = !openSection">
        {{-- ... --}}
    </div>
    <div class="section-content" x-show="openSection">
        {{-- ... --}}
    </div>
</div>
```

- [ ] **Step 2: Commit**

```bash
git add resources/views/components/layout/home-aside.blade.php
git commit -m "feat: refactor sidebar dropdowns to use AlpineJS"
```

### Task 5: Verification

- [ ] **Step 1: Test Mobile View**

Open the application on a mobile emulator or resize the browser window to mobile width. Click the filter button and verify:
1. The backdrop appears.
2. The drawer slides up from the bottom, covering 75% of the screen.
3. Content scrolls inside the drawer.
4. Tapping the backdrop or the close button dismisses the drawer.

- [ ] **Step 2: Test Desktop View**

Resize the browser window to desktop width and verify that the sidebar behaves as expected (static, not fixed, no backdrop).
