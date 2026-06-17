# Mobile Redesign Next Steps

This document outlines the remaining tasks for the mobile navigation and filter UI redesign.

## Current Progress
- **Branch:** `feature/mobile-nav-redesign`
- **Task 1 (Navigation Refactor):** COMPLETED. `nav.blade.php` updated with top/bottom bars.
- **Task 2 (Profile Menu Popup):** COMPLETED. `profile-menu-btn.blade.php` and `ProfileMenuBtn.php` refactored to use a unified desktop-style popup with AlpineJS.

## Pending Tasks

### 1. Scroll-Triggered Filter System (Task 3)
- **File:** `resources/views/components/layout/home-aside.blade.php`
- **Logic:**
    - Implement AlpineJS scroll listener to hide the filter icon on scroll down and show on scroll up (> 5px).
    - Position filter icon at `top-20`, `left-4`.
    - Set mobile width to 75% (`w-3/4`).
    - Ensure `z-index` is higher than the bottom navigation bar (`z-[70]`).
- **Status:** Subagent attempted but failed to finalize due to turn limits and environment errors.

### 2. Debug View/Test Errors
- **Issue:** Widespread 500 errors in feature tests (e.g., `HomePageTest.php`) reporting syntax errors in compiled views.
- **Hypothesis:** Potential conflict or mismatch in `@desktop` and `@mobile` Blade directives, or a syntax error introduced in `nav.blade.php`.
- **Action:** Inspect `storage/framework/views` compiled files and verify directive registration in `AppServiceProvider.php`.

### 3. Responsive Layout Constraints (Task 4)
- **File:** `resources/views/components/layout/menu.blade.php`
- **Action:** Ensure the sidebar menu is hidden on mobile screens (`hidden md:flex`).
- **Dashboard Pages:** Verify `layout/menu/*.blade.php` pages correctly hide the sidebar and only provide navigation via the profile menu popup.

### 4. Logic Sync for Profile Menu
- **Requirement:** Ensure that clicking the profile button in the top nav and bottom nav provides a consistent "unified" experience as specified in the design doc.

## How to Resume
1. Switch to `feature/mobile-nav-redesign` branch.
2. Run `php artisan view:clear` to rule out stale cache.
3. Start with debugging the 500 errors in `nav.blade.php`.
4. Proceed to Task 3 (Filters).
