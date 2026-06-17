# Remaining Tasks: Mobile Layout Redesign

## Progress Summary (2026-06-17)

### 1. Mobile Bottom-Sheet Filter Drawer (`home-aside.blade.php`)
- **Status:** COMPLETED
- **Changes:**
    - Transformed the sidebar filter into a fixed bottom-sheet drawer for mobile viewports.
    - Implemented AlpineJS for visibility toggling (`open` state) and transitions (slide-up).
    - Added backdrop and mobile-only close button.
    - Preserved static sidebar layout for desktop (768px+).
    - Refactored internal dropdown sections (Rating, Categories, Tags) from legacy JS to AlpineJS.
    - Cleaned up legacy mobile sidebar CSS and JS from `homepage.css` and `homepage.js`.

### 2. Mobile Bottom-Sheet Account/Admin Menu (`profile-menu-btn.blade.php`)
- **Status:** COMPLETED
- **Changes:**
    - Transformed the mobile profile dropdown into a fixed bottom-sheet drawer.
    - Organised menu items into logical groups: Profile, Admin, and Settings.
    - Added user summary (avatar, name, verified status) to the drawer header.
    - Implemented AlpineJS for smooth slide-up transitions and backdrop management.
    - Synchronized `menu.blade.php` to hide the redundant vertical sidebar on mobile (`hidden md:flex`).

### 3. Mobile Navigation Refinement & Split Navigation (`nav.blade.php`)
- **Status:** COMPLETED
- **Changes:**
    - Implemented **Split Navigation** for Welcome and About Us pages on mobile.
    - Authentication triggers (Login/Profile) moved to the top header for landing pages.
    - Primary links (Home, Category, About, etc.) remain in the bottom bar with a new **horizontal laptop-style** design.
    - Updated `home-btn`, `category-btn`, `about-btn`, `create-post-btn`, and `noti-btn` to support conditional horizontal layout on landing pages.
    - Improved component flexibility with `$attributes->merge()`.

## Remaining Tasks

### 4. Final Responsive Verification
- **Goal:** Comprehensive testing across all breakpoints.
- **Checklist:**
    - Verify no layout overlaps between 768px (Tailwind `md`) and any remaining legacy breakpoints.
    - Test dark mode consistency in new drawer components.
    - Confirm all AlpineJS event listeners work without conflict across different pages.

### 5. Debugging Regressions
- **Issue:** `test_opening_report_notification_marks_notification_read_but_not_report_read` in `AdminReportsPageTest.php` fails with 403.
- **Context:** Likely related to unrequested changes in `NotificationOpenController` and middleware interactions in Laravel 13.
- **Task:** Fix authorization logic or test setup to resolve the 403 error.

---
*Note: This task list was updated after completing the Split Navigation implementation.*
