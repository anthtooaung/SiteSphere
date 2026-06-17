# Mobile Responsiveness Improvements Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Fix layout and overflow issues on mobile screens (< 640px) in `reports`, `users`, `appearance`, `edit-profile`, and `edit-tag` pages.

**Architecture:** Use CSS media queries to stack grid layouts and wrap tables in scrollable containers.

**Tech Stack:** Vanilla CSS, Blade Templates, TailwindCSS (for utility helpers if available).

---

### Task 1: Fix Table Overflow in Reports and Users Pages

**Files:**
- Modify: `resources/css/reports.css`
- Modify: `resources/css/admin-users.css` (or equivalent file)
- Modify: `resources/views/layout/menu/reports.blade.php`
- Modify: `resources/views/layout/menu/users.blade.php`

- [ ] **Step 1: Wrap tables in a scrollable container**

Modify `reports.blade.php` and `users.blade.php` to wrap the `table` elements:

```html
<div class="reports-table-wrap" style="overflow-x: auto;">
    <table class="reports-table">
        ...
    </table>
</div>
```

- [ ] **Step 2: Add CSS to ensure container handles overflow**

In `resources/css/reports.css` (and equivalent for users):

```css
.reports-table-wrap {
    width: 100%;
    overflow-x: auto;
}
.reports-table {
    min-width: 600px; /* Adjust based on actual content width */
}
```

- [ ] **Step 3: Commit**

```bash
git add resources/views/layout/menu/reports.blade.php resources/views/layout/menu/users.blade.php resources/css/reports.css
git commit -m "fix(responsive): add scrollable container for tables"
```

### Task 2: Fix Grid Layouts (Appearance, Profile, Tags)

**Files:**
- Modify: `resources/css/appearance.css`
- Modify: `resources/css/edit-profile.css`
- Modify: `resources/css/edit-tag.css`

- [ ] **Step 1: Apply media queries to stack grid items**

For each CSS file, identify the grid container (e.g., `.appearance-choice-grid`, `.profile-card-grid`, `.edit-tag-grid`).

Add:

```css
@media (max-width: 640px) {
    .grid-container-class {
        grid-template-columns: 1fr !important;
    }
}
```

- [ ] **Step 2: Commit**

```bash
git add resources/css/appearance.css resources/css/edit-profile.css resources/css/edit-tag.css
git commit -m "fix(responsive): stack grid layouts on small screens"
```

---

**Plan complete and saved to `docs/superpowers/plans/2026-06-17-responsive-design-fixes.md`. Two execution options:**

**1. Subagent-Driven (recommended)** - I dispatch a fresh subagent per task, review between tasks, fast iteration

**2. Inline Execution** - Execute tasks in this session using executing-plans, batch execution with checkpoints

**Which approach?**
