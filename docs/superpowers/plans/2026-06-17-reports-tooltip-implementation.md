# Report Section Tooltip Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Implement a reusable, custom Alpine.js tooltip component and integrate it into the Report section action buttons to replace native `title` attributes.

**Architecture:** Use an Alpine.js-powered Blade component that renders an absolute-positioned tooltip on hover/focus, styled via CSS to match the enterprise design system.

**Tech Stack:** Laravel, Blade, Alpine.js, CSS.

---

### Task 1: Create Tooltip Blade Component

**Files:**
- Create: `resources/views/components/tooltip.blade.php`

- [ ] **Step 1: Write the tooltip component**

```html
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
```

- [ ] **Step 2: Commit**

```bash
git add resources/views/components/tooltip.blade.php
git commit -m "feat: add reusable tooltip component"
```

### Task 2: Add Tooltip Styles to `reports.css`

**Files:**
- Modify: `resources/css/reports.css`

- [ ] **Step 1: Add necessary styles**

Append to `resources/css/reports.css`:
```css
/* Tooltip styles */
[x-cloak] {
    display: none !important;
}

.reports-tooltip-content {
    background: var(--text-color, #0d1b2a);
    color: var(--background-color, #ffffff);
    padding: 4px 8px;
    border-radius: 6px;
    font-size: 0.72rem;
    font-weight: 700;
}
```

- [ ] **Step 2: Commit**

```bash
git add resources/css/reports.css
git commit -m "feat: add tooltip styles to reports.css"
```

### Task 3: Integrate Tooltip into `reports.blade.php`

**Files:**
- Modify: `resources/views/layout/menu/reports.blade.php`

- [ ] **Step 1: Refactor Action Buttons**

Locate action buttons and wrap them:

*Example (Delete Action):*
```html
<x-tooltip content="Delete Report">
    <button type="submit" class="reports-icon-btn delete-action" aria-label="Delete Report">
        <x-fas-trash aria-hidden="true" />
    </button>
</x-tooltip>
```
*Note: Ensure `title` attributes are removed from the buttons.*

- [ ] **Step 2: Verify functionality**

Check the "View", "Mark Unread", and "Delete" buttons in the "Reports" table. Tooltips should appear on hover.

- [ ] **Step 3: Commit**

```bash
git add resources/views/layout/menu/reports.blade.php
git commit -m "feat: integrate tooltips into report action buttons"
```
