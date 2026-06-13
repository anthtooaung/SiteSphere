# Admin Dashboard Logic & Role-based View Design

## 1. Overview
Refactor the existing dashboard to provide a data-rich experience for administrators while maintaining the personal dashboard for regular members. This involves dual-path controller logic and a highly interactive Blade view utilizing SVG and Alpine.js.

## 2. Technical Strategy

### 2.1 Model Enhancements (`User.php`)
- Add `isAdmin()` helper method to check if the user's role is 'admin'.

### 2.2 Controller Logic (`DashboardController.php`)
- **Admin Path:**
    - `totalUsers`: `User::count()`
    - `totalReviews`: `UserPosts::count()`
    - `totalReports`: `Reports::count()`
    - `recentAuditLogs`: `AuditLogs::latest()->take(4)->get()`
    - `topPosts`: `Posts::withAvg('ratings', 'rating')->withCount('comments')->orderBy('ratings_avg_rating', 'desc')->take(5)->get()`
- **User Path:**
    - Retain current logic for personal stats and recent reviews.

### 2.3 UI Components (`dashboard.blade.php`)
- **KPI Grid:**
    - 3-column layout for Admin.
    - Each card displays a total count and a decorative SVG sparkline.
    - Alpine.js `x-data` will track the "active" metric for donut chart interaction.
- **Interactive Donut Chart:**
    - Pure SVG implementation.
    - Percentage calculation: `(value / total) * circumference`.
    - Dynamic dimming effect: `opacity` changes based on Alpine.js state.
- **Activity Timeline:**
    - Vertical list of `AuditLogs`.
    - Icons and colors pulled directly from `AuditLogs` model methods (`getIcon`, `getColor`).
- **Top Posts List:**
    - Ranking icons: Rank 1 (Gold), Rank 2 (Silver), Rank 3 (Bronze).
    - Display average rating and comment count.

## 3. Implementation Plan

1. **Step 1:** Modify `app/Models/User.php` to include `isAdmin()`.
2. **Step 2:** Refactor `app/Http/Controllers/DashboardController.php` to fetch admin data.
3. **Step 3:** Update `resources/views/layout/menu/dashboard.blade.php` with the new admin layout and Alpine.js logic.
4. **Step 4:** Verify styles and interactivity (SVG donut, card clicks).

## 4. Design Decisions
- **SVG for Charts:** Avoids heavy JS libraries, keeping the dashboard lightweight and consistent with the project's minimalist aesthetic.
- **Alpine.js for Interactivity:** Minimal footprint, perfect for simple state management like highlighting chart segments.
- **Slate/Indigo Palette:** Aligns with the existing design system mentioned in the requirements.
