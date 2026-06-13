# Admin Dashboard & Activity Log Design Specification

**Goal:** Implement a high-fidelity administrative interface for system metrics and activity tracking, utilizing interactive charts, timelines, and a calendar-based feed.

**Architecture:**
- **Unified Dashboard:** The existing `/menu/dashboard` will dynamically switch between a "Personal Stats" view for members and the new "Admin Overview" for administrators.
- **Activity Log:** A dedicated page at `/menu/dashboard/activity-log` featuring a dual-pane calendar and feed interface.
- **Data Model:** Extending the `AuditLogs` model to support categories and UI metadata (colors, icons).
- **Interactivity:** Alpine.js for chart filtering and AJAX for server-driven date switching in the activity feed.

---

## 1. Data Architecture

### Audit Log Extensions
- **Migration:** Add a `category` column (string) to the `audit_logs` table.
- **Categories:**
    - `moderation`: Red (Bans, Deletions)
    - `success`: Green (Resolutions, Approvals)
    - `announcement`: Blue (System-wide alerts)
    - `system`: Yellow (Settings changes, internal updates)
- **Model Methods (`AuditLogs`):**
    - `getIcon()`: Returns FontAwesome class based on category.
    - `getColor()`: Returns semantic hex or CSS variable based on category.

---

## 2. Admin Dashboard (`/menu/dashboard`)

### Admin Overview Panel
- **SVG Donut Chart:** A purely SVG-based "User Activity Distribution" chart.
- **KPI Cards:** Three cards (Total Users, Reviews, Reports) with embedded SVG sparklines showing trends.
- **Interactivity:** Clicking a KPI card dims unrelated slices in the donut chart (Alpine.js state).
- **Month/Year Picker:** Custom dropdown to filter dashboard data.

### Widgets
- **Recent Activity Timeline:** Displays the 4 most recent `AuditLogs` in a vertical stack.
- **Top Rated Posts:** Ranked list of the top 5 posts by average rating, with Gold/Silver/Bronze styling for the top 3.

---

## 3. Activity Log & Calendar (`/menu/dashboard/activity-log`)

### Interactive Calendar
- **Grid Layout:** Monthly calendar grid where dates with activities display a small indicator dot (color based on the dominant category for that day).
- **Navigation:** Month/Year switching (AJAX-enhanced or full reload depending on performance).

### Activity Feed Card
- **Logic:** Clicking a date on the calendar triggers an AJAX request to `GET /api/admin/activity/{date}`.
- **Response:** A Blade partial containing the detailed feed for that day (up to 3 items + "See All" button).
- **Modal:** A detailed log modal for viewing all actions on a specific date or all actions globally.

---

## 4. UI/UX Standards
- **Standardized Radius:** 8px for all cards and widgets.
- **Color Palette:** Slate-based surfaces (`#f8fafc`) with Indigo accents.
- **Empty States:** Centered "No data" UI using flexbox/grid.
- **Actionability:** Icons (eye, trash) allow navigation to actual resources (Post detail, Profile, etc.).

---

## 5. Verification Strategy
- **Feature Tests:**
    - `DashboardRoleAccessTest`: Verify that non-admins cannot see the Admin Overview.
    - `AuditLogCategoryTest`: Ensure categories are correctly stored and retrieved.
    - `ActivityLogAjaxTest`: Verify that clicking a date on the calendar returns the correct Blade partial.
- **Data Integrity:** Verify that SVG chart percentages correctly reflect the database totals.
