# Integrated Desktop Search Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Integrate the existing `search-btn.blade.php` component into the desktop navigation bar.

**Architecture:** Add the existing search component to the `@desktop` nav block, specifically within the center navigation links container.

**Tech Stack:** Laravel Blade, Tailwind CSS.

---

### Task 1: Modify Navigation to Include Search Bar

**Files:**
- Modify: `resources/views/components/layout/nav.blade.php`

- [ ] **Step 1: Locate center nav container in `nav.blade.php`**

Find the section within the `@desktop` nav block:
```blade
{{--        center path--}}
        <div class="items-center justify-between w-full md:flex md:w-auto md:order-1" id="navbar-sticky">
            <ul class="flex p-4 md:p-0 mt-4 md:space-x-8 rtl:space-x-reverse md:mt-0 ">
                <li>
                    <x-home-btn/>
                </li>
                <li>
                    <x-category-btn />
                </li>
```

- [ ] **Step 2: Add Search Component**

Modify to add the search component:
```blade
{{--        center path--}}
        <div class="items-center justify-between w-full md:flex md:w-auto md:order-1" id="navbar-sticky">
            <ul class="flex p-4 md:p-0 mt-4 md:space-x-8 rtl:space-x-reverse md:mt-0 ">
                <li>
                    <x-home-btn/>
                </li>
                <li>
                    <x-category-btn />
                </li>
                <li class="hidden md:block">
                    <x-search-btn />
                </li>
                @if(request()->routeIs('welcome'))
                    <li>
                        <x-about-btn />
                    </li>
                @endif
            </ul>
        </div>
```

- [ ] **Step 3: Update CSS for responsiveness (Optional but Recommended)**

Check `resources/views/components/search-btn.blade.php` and verify container widths.
If necessary, modify `resources/css/app.css` to add/adjust:
```css
.desktop-search-container {
    width: 240px; /* or desired width */
}
```

- [ ] **Step 4: Verify in Browser**

Refresh the page, inspect the navigation bar, and confirm the search bar is rendered correctly in the desktop view.

- [ ] **Step 5: Commit**

```bash
git add resources/views/components/layout/nav.blade.php
git commit -m "feat: integrate search bar into desktop navigation"
```
