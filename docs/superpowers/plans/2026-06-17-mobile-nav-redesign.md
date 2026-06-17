# Mobile Navigation and Filter UI Redesign Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Redesign mobile navigation (top/bottom bars), unify profile menu popup style, and implement a scroll-triggered filter system.

**Architecture:** Refactor existing Blade components to use AlpineJS for unified popup logic and scroll triggers. Use Tailwind CSS for responsive visibility and positioning.

**Tech Stack:** Laravel Blade, AlpineJS, Tailwind CSS.

---

### Task 1: Refactor Mobile Navigation Components

**Files:**
- Modify: `resources/views/components/layout/nav.blade.php`

- [ ] **Step 1: Update Top Navigation (Mobile)**
  Modify the `@mobile` section to include logo/title on the left and a permanent login/profile button on the right.

```html
<header class="mobile-header flex items-center justify-between px-4 py-3 bg-white dark:bg-gray-900 border-b border-gray-200 dark:border-gray-800 sticky top-0 z-50">
    <a href="{{ route('welcome') }}" class="flex items-center gap-2">
        <x-app-logo class="size-6"></x-app-logo>
        <span class="font-bold text-lg dark:text-white">SiteSphere</span>
    </a>

    @auth
        <x-profile-menu-btn trigger="top" />
    @else
        <x-login-out-menu-btn />
    @endauth
</header>
```

- [ ] **Step 2: Update Bottom Navigation (Mobile)**
  Rebuild the `mobile-bottom-nav` with Home, Categories, Upload, Alert, and Profile links.

```html
<nav class="mobile-bottom-nav fixed bottom-0 inset-x-0 bg-white dark:bg-gray-900 border-t border-gray-200 dark:border-gray-800 flex justify-around items-center py-2 z-50 md:hidden">
    <x-home-btn />
    <x-category-btn mobile-mode="trigger" />
    <x-create-post-btn />
    <x-noti-btn />
    @auth
        <x-profile-menu-btn trigger="bottom" />
    @else
        <x-login-out-menu-btn />
    @endauth
</nav>
```

- [ ] **Step 3: Commit**
```bash
git add resources/views/components/layout/nav.blade.php
git commit -m "feat(mobile): refactor top and bottom navigation bars"
```

---

### Task 2: Unified Profile Menu Popup

**Files:**
- Modify: `resources/views/components/profile-menu-btn.blade.php`

- [ ] **Step 1: Remove existing mobile bottom-sheet design**
  Strip out the existing `@mobile` drawer/sheet logic in `profile-menu-btn.blade.php`.

- [ ] **Step 2: Implement unified desktop-style popup for mobile**
  Wrap the component in AlpineJS state that can be triggered from both top and bottom nav buttons.

```html
<div x-data="{ open: false }" @click.away="open = false" class="relative">
    <button @click="open = !open" {{ $attributes->merge(['class' => 'profile-trigger']) }}>
        <!-- Avatar/Icon logic -->
    </button>

    <div x-show="open" x-cloak 
         class="absolute right-0 mt-2 w-64 bg-white dark:bg-gray-800 rounded-lg shadow-xl border border-gray-200 dark:border-gray-700 z-[100]"
         :class="{ 'bottom-full mb-2': trigger === 'bottom', 'top-full mt-2': trigger === 'top' }">
        <!-- Desktop menu items here -->
    </div>
</div>
```

- [ ] **Step 3: Commit**
```bash
git add resources/views/components/profile-menu-btn.blade.php
git commit -m "feat(mobile): unify profile menu popup with desktop style"
```

---

### Task 3: Scroll-Triggered Filter System

**Files:**
- Modify: `resources/views/components/layout/home-aside.blade.php`

- [ ] **Step 1: Implement Scroll-Trigger logic for Filter Icon**
  Add AlpineJS logic to track scroll direction and show/hide the filter icon.

```html
<div x-data="{ 
    showTrigger: true, 
    lastScrollY: window.scrollY,
    open: false,
    handleScroll() {
        let currentScrollY = window.scrollY;
        if (currentScrollY < this.lastScrollY - 5) {
            this.showTrigger = true;
        } else if (currentScrollY > this.lastScrollY + 5) {
            this.showTrigger = false;
        }
        this.lastScrollY = currentScrollY;
    }
}" @scroll.window="handleScroll">
    <button x-show="showTrigger" @click="open = true" 
            class="fixed top-16 left-4 z-40 bg-accent p-2 rounded-full shadow-lg md:hidden">
        <x-fas-filter class="size-5" />
    </button>
    
    <aside x-show="open" @click.away="open = false"
           class="fixed inset-y-0 left-0 w-3/4 bg-white dark:bg-gray-800 z-[60] shadow-2xl transform transition-transform"
           :class="open ? 'translate-x-0' : '-translate-x-full'">
        <!-- Filter content -->
    </aside>
</div>
```

- [ ] **Step 2: Commit**
```bash
git add resources/views/components/layout/home-aside.blade.php
git commit -m "feat(mobile): implement scroll-triggered filter system"
```

---

### Task 4: Responsive Layout Constraints

**Files:**
- Modify: `resources/views/components/layout/menu.blade.php`
- Modify: `resources/views/layout/menu/*.blade.php` (All dashboard pages)

- [ ] **Step 1: Hide sidebar menu on mobile**
  Ensure `layout-menu` is hidden on mobile screens.

```html
<aside id="layoutMenu" class="layout-menu hidden md:flex ...">
```

- [ ] **Step 2: Verify all dashboard pages hide the menu**
  Check `appearance.blade.php`, `edit-profile.blade.php`, etc., to ensure the `<x-layout.menu />` is only visible on desktop via its internal classes.

- [ ] **Step 3: Commit**
```bash
git add resources/views/components/layout/menu.blade.php
git commit -m "style(mobile): hide sidebar menu on small screens"
```

---

**Plan complete and saved to `docs/superpowers/plans/2026-06-17-mobile-nav-redesign.md`. Two execution options:**

**1. Subagent-Driven (recommended)** - I dispatch a fresh subagent per task, review between tasks, fast iteration

**2. Inline Execution** - Execute tasks in this session using executing-plans, batch execution with checkpoints

**Which approach?**
