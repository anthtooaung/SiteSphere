# Dashboard Design Integration Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Integrate a new, custom HTML/CSS/JS design for the Admin Dashboard and Activity Log into the existing Laravel Blade views.

**Architecture:** We will extract the custom CSS and Vanilla JS into dedicated files (`resources/css/admin-*.css`, `resources/js/admin-*.js`) and compile them using Vite. The Blade templates will be updated to include the new HTML structure within the `<main>` tag, inject dynamic data via the `window` object, and load the Vite assets. CSS variables will map to the existing global theme variables.

**Tech Stack:** Laravel Blade, Vite, Vanilla JS, CSS

---

### Task 1: Setup Asset Pipeline

**Files:**
- Modify: `vite.config.js`

- [ ] **Step 1: Add new asset entries to Vite configuration**
Modify `vite.config.js` to include the new CSS and JS files in the `input` array. Make sure not to break existing entries.

```javascript
// snippet to add inside input array
'resources/css/admin-dashboard.css',
'resources/js/admin-dashboard.js',
'resources/css/admin-activity.css',
'resources/js/admin-activity.js',
```

- [ ] **Step 2: Commit changes**
```bash
git add vite.config.js
git commit -m "chore: add admin dashboard and activity assets to vite config"
```

### Task 2: Implement Admin Dashboard Styles and Scripts

**Files:**
- Create: `resources/css/admin-dashboard.css`
- Create: `resources/js/admin-dashboard.js`

- [ ] **Step 1: Create Admin Dashboard CSS**
Create `resources/css/admin-dashboard.css` with the provided CSS from the dashboard HTML. Update the `:root` variables to use the app's global variables and font-family.
```css
/* resources/css/admin-dashboard.css */
* { margin: 0; padding: 0; box-sizing: border-box; }
:root {
  --bg: var(--background-color, #f8fafc);
  --surface: #fff; 
  --border: #e2e8f0;
  --text: var(--text-color, #0f172a);
  --muted: #64748b;
  --subtle: #94a3b8;
  --accent: var(--accent-color, #4f46e5);
  --accent-light: #eef2ff;
  --danger: #ef4444;
  --danger-bg: #fef2f2;
  --success: #10b981;
  --success-bg: #ecfdf5;
  --info: #3b82f6;
  --info-bg: #eff6ff;
  --card-color: var(--accent-color, #4f46e5);
}
/* Remaining CSS copied from the dashboard HTML */
```

- [ ] **Step 2: Create Admin Dashboard JS**
Create `resources/js/admin-dashboard.js` with the provided JS logic. Map the hardcoded data to the `window.AdminDashboardData` object.
```javascript
// resources/js/admin-dashboard.js
document.addEventListener('DOMContentLoaded', () => {
    const data = window.AdminDashboardData || {};
    const categories = data.stats || []; 
    const acts = data.recentActivity || [];
    const posts = data.topPosts || [];
    
    // ... (rest of the vanilla JS code, using these variables) ...
});
```

- [ ] **Step 3: Commit changes**
```bash
git add resources/css/admin-dashboard.css resources/js/admin-dashboard.js
git commit -m "feat: add admin dashboard CSS and JS assets"
```

### Task 3: Update Admin Dashboard Blade Template

**Files:**
- Modify: `resources/views/layout/menu/dashboard.blade.php`

- [ ] **Step 1: Replace Admin Content and Inject Assets**
Update `dashboard.blade.php`. Push the new assets using `@vite`. Prepare the PHP data to match the expected JSON structure and assign it to `window.AdminDashboardData`.

```php
@push('styles')
    @if($isAdmin)
        @vite('resources/css/admin-dashboard.css')
    @endif
@endpush

@push('scripts')
    @if($isAdmin)
        <script>
            // Transform $stats, $recentActivity, $topPosts into required JSON shapes
            window.AdminDashboardData = {
                stats: @json($formattedStats),
                recentActivity: @json($formattedActivity),
                topPosts: @json($formattedPosts)
            };
        </script>
        @vite('resources/js/admin-dashboard.js')
    @endif
@endpush
```

Replace the main `@if($isAdmin)` content block with the `.shell` HTML from the provided design.

- [ ] **Step 2: Commit changes**
```bash
git add resources/views/layout/menu/dashboard.blade.php
git commit -m "feat: update admin dashboard layout and integrate vite assets"
```

### Task 4: Implement Activity Log Styles and Scripts

**Files:**
- Create: `resources/css/admin-activity.css`
- Create: `resources/js/admin-activity.js`

- [ ] **Step 1: Create Admin Activity CSS**
Create `resources/css/admin-activity.css` mapping `:root` to use `var(--background-color)` and `var(--accent-color)`.

- [ ] **Step 2: Create Admin Activity JS**
Create `resources/js/admin-activity.js` replacing the hardcoded `actsExpanded` with `window.AdminActivityData.actsExpanded`.

- [ ] **Step 3: Commit changes**
```bash
git add resources/css/admin-activity.css resources/js/admin-activity.js
git commit -m "feat: add admin activity log CSS and JS assets"
```

### Task 5: Update Activity Log Blade Template

**Files:**
- Modify: `resources/views/layout/menu/activity-log.blade.php`

- [ ] **Step 1: Replace Content and Inject Assets**
Update `activity-log.blade.php` replacing the AlpineJS structure with the `.shell` structure.
Push styles and scripts, formatting the Laravel `$auditLogs` variable to match the needed array structure.

- [ ] **Step 2: Commit changes**
```bash
git add resources/views/layout/menu/activity-log.blade.php
git commit -m "feat: update activity log layout and integrate vite assets"
```
