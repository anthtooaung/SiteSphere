# SiteSphere Button Actions & Database Interactions

## Overview
This document maps every button click action and its corresponding database interaction across the SiteSphere platform.

## Page-by-Page Actions

### Public & Auth Pages

#### Global Navigation & Footer
- **[Login / Register] / [Profile Menu]** - Navigation Bar
  - **Type:** Navigation / Dropdown Toggle
  - **Action:** Opens the login/register modals or the authenticated user profile dropdown menu.
  - **DB Interaction:** None (UI action).

- **Mobile Bottom Nav Items** - Mobile Navigation Bar
  - **Type:** Navigation
  - **Action:** Navigates to Home, Create Post, Notifications, or Profile.
  - **DB Interaction:** None directly (triggers page load).

- **Footer Links (Create Post, About, Social Links)** - Footer
  - **Type:** Navigation
  - **Action:** Opens new pages or external social media profiles (LinkedIn, Telegram, GitHub).
  - **DB Interaction:** None.

#### Welcome Page (`/`)
- **[Explore]** - Hero Section
  - **Type:** Navigation
  - **Action:** Scrolls down to the Most Reviewed Websites section.
  - **DB Interaction:** None

- **[See More!]** - Most Reviewed Websites Section
  - **Type:** Navigation
  - **Action:** Redirects to the Home (`/home`) page.
  - **DB Interaction:** None

- **[Submit]** - Contact Form Section
  - **Type:** Mutation
  - **Action:** Submits the contact form.
  - **DB Interaction:** Send email via `ContactMessageController@store` (No database mutation).

#### Auth Pages (`/login`, `/register`, `/forgot-password`, `/reset-password`)
- **[Login]** - Login Form
  - **Type:** Mutation
  - **Action:** Authenticates user.
  - **DB Interaction:** Read from `Users`.

- **[Register]** - Registration Form
  - **Type:** Mutation
  - **Action:** Creates a new user account.
  - **DB Interaction:** Write to `Users`.

- **[Continue with Google / GitHub]** - Social Login
  - **Type:** Mutation
  - **Action:** Authenticates or registers the user via Socialite.
  - **DB Interaction:** Read/Write `SocialAccounts` and `Users`.

- **[Continue Profile / Skip Profile]** - Registration Flow
  - **Type:** Navigation / Mutation
  - **Action:** Updates profile details or skips to the next step.
  - **DB Interaction:** Write to `Users` (Profile details).

- **[Verify OTP]** - Registration / 2FA / Password Reset
  - **Type:** Mutation
  - **Action:** Verifies the one-time password.
  - **DB Interaction:** Read/Write to `OtpVerifications`.

- **[Resend OTP]** - OTP Verification Step
  - **Type:** Mutation
  - **Action:** Resends the OTP email.
  - **DB Interaction:** Write to `OtpVerifications`.

- **[Send OTP / Reset Password]** - Forgot Password Flow
  - **Type:** Mutation
  - **Action:** Initiates password reset process or finalizes new password.
  - **DB Interaction:** Write to `OtpVerifications`, Update `Users`.

- **[showRegister] / [showLogin]** - Auth Modals/Toggles
  - **Type:** Navigation
  - **Action:** Toggles between login and registration views (often via JS).
  - **DB Interaction:** None.

- **[confirmRegisterBtn]** - Final Registration Step
  - **Type:** Mutation
  - **Action:** Submits the complete registration form.
  - **DB Interaction:** Write to `Users`.

- **[backToProfileBtn] / [closeRegistrationFlow]** - Registration UI
  - **Type:** Navigation
  - **Action:** Navigates back a step or dismisses the registration flow overlay.
  - **DB Interaction:** None.

- **[Show password]** - Auth Inputs
  - **Type:** UI Toggle
  - **Action:** Toggles visibility of the password input field.
  - **DB Interaction:** None.

#### Home Page (`/home`)
- **[Menu] / [sidebarToggle]** - Sidebar Toggles
  - **Type:** Navigation
  - **Action:** Toggles the sidebar menu via JS.
  - **DB Interaction:** None.

- **[showCategoryBtn]** - Categories Toggle
  - **Type:** UI Toggle
  - **Action:** Expands or collapses the category filter list.
  - **DB Interaction:** None.

- **Author Profile Link** - Post Card
  - **Type:** Navigation
  - **Action:** Navigates to the author's public profile (`/profile/{slug}`).
  - **DB Interaction:** None.

- **[Read Reviews] / [Visit Website]** - Post Card
  - **Type:** Navigation
  - **Action:** Redirects to the Post Detail page or opens the external website link in a new tab.
  - **DB Interaction:** None.

- **[Clear All Filters]** - Filters Section
  - **Type:** Filter
  - **Action:** Clears active category, tag, or rating filters via AlpineJS.
  - **DB Interaction:** None (Client-side trigger, may result in AJAX Read for `Posts`).

- **[Load More / Infinite Scroll]** - Posts Feed
  - **Type:** Navigation / Filter
  - **Action:** Loads the next page of posts.
  - **DB Interaction:** Read from `Posts`, `Users`, `Bookmarks`, `Categories`.

#### Post Detail Page (`/posts/{slug}`)
- **[Bookmark]** - Post Actions
  - **Type:** Mutation
  - **Action:** Saves or removes a post from user's bookmarks.
  - **DB Interaction:** Write to `Bookmarks`.

- **[Report]** - Post Actions
  - **Type:** Mutation
  - **Action:** Submits a report for moderation.
  - **DB Interaction:** Write to `Reports`.

- **[Ban]** - Post Actions (Admin)
  - **Type:** Mutation
  - **Action:** Bans the post/user.
  - **DB Interaction:** Update `Posts` or `Users`.

- **[Post Comment]** - Comments Section
  - **Type:** Mutation
  - **Action:** Adds a comment (and optionally a rating) to the post.
  - **DB Interaction:** Write to `Comments`, `Ratings`.

- **[React / Like Comment]** - Comment Actions
  - **Type:** Mutation
  - **Action:** Toggles a reaction (like) on a comment.
  - **DB Interaction:** Write/Delete from `CommentReactions`.

- **[See more]** - Comment Toggles
  - **Type:** UI Toggle
  - **Action:** Expands truncated long comments to show full text.
  - **DB Interaction:** None.

#### Profile Detail Page (`/profile/{slug?}`)
- **[Edit Profile]** - Profile Header
  - **Type:** Navigation
  - **Action:** Redirects to Edit Profile page.
  - **DB Interaction:** None.

- **[View Profile (Message)]** - Profile Card
  - **Type:** Navigation
  - **Action:** Redirects to the specific user's profile detail page.
  - **DB Interaction:** None.

- Profile Links (Email, Phone, "View all reviews") - Profile Card
  - **Type:** Navigation
  - **Action:** Opens the email client, initiates a phone call, or filters the profile to show all user reviews.
  - **DB Interaction:** None (Client-side triggers or mailto/tel links).

### User Dashboard Pages

#### Dashboard Home (`/menu/dashboard`)
- **[KPI Card (Users/Reviews/Reports)]** - Admin Statistics
  - **Type:** UI Interaction
  - **Action:** Highlights segments in the donut chart and scales the card for visual feedback.
  - **DB Interaction:** None.

- **[Recent Review Link]** - Activity Section (User)
  - **Type:** Navigation
  - **Action:** Redirects to the Post Detail page of the specific review.
  - **DB Interaction:** None.

- **[Top Post Link]** - Engagement Section (Admin)
  - **Type:** Navigation
  - **Action:** Redirects to the Post Detail page of the top-rated post.
  - **DB Interaction:** None.

#### Appearance Settings (`/menu/appearance`)
- **[Save Changes]** - Appearance Form
  - **Type:** Mutation
  - **Action:** Updates user workspace preferences (dark mode, menu/notification location, theme, fonts).
  - **DB Interaction:** Update `Settings`, `CustomThemes`, and `User` (fonts sync).

- **[Theme Swatch / Preset Theme]** - Theme Grid
  - **Type:** UI Interaction
  - **Action:** Selects a preset theme and updates the live preview.
  - **DB Interaction:** None.

- **[Font search option]** - Font Search
  - **Type:** UI Interaction
  - **Action:** Selects a font from the curated catalog for preview and update.
  - **DB Interaction:** None.

#### Security (`/menu/security`)
- **[Save Changes]** - Security Form
  - **Type:** Mutation
  - **Action:** Updates account protection (2FA), posting visibility, and password.
  - **DB Interaction:** Update `Users`, `Settings`.

- **[Update Password]** - Password Toggle
  - **Type:** UI Toggle
  - **Action:** Toggles visibility of the password change input fields.
  - **DB Interaction:** None.

#### Edit Profile (`/menu/edit-profile`)
- **[Save Changes]** - Profile Form
  - **Type:** Mutation
  - **Action:** Updates user information and profile picture.
  - **DB Interaction:** Update `Users`, Disk storage for avatar.

- **[Camera Button / Upload Photo]** - Avatar Section
  - **Type:** UI Interaction
  - **Action:** Triggers file input for profile photo selection.
  - **DB Interaction:** None.

- **[Apply Crop / Use Original]** - Crop Modal
  - **Type:** UI Interaction
  - **Action:** Finalizes the profile photo preview (cropping or original GIF).
  - **DB Interaction:** None.

#### Edit Tag (`/menu/edit-tag`)
- **[Save Changes / Publish to users]** - Tag Form
  - **Type:** Mutation
  - **Action:** Updates personal tag styles (User) or global taxonomy (Admin).
  - **DB Interaction:** Write/Delete `CustomTags` (User); Update `Categories`, `Tags`, `AuditLogs` (Admin).

- **[Reset to Defaults]** - Tag Styles
  - **Type:** Mutation
  - **Action:** Clears all personal tag overrides.
  - **DB Interaction:** Delete from `CustomTags`.

- **[Delete Category / Remove Tag]** - Taxonomy Actions (Admin)
  - **Type:** Mutation
  - **Action:** Permanently removes a category or tag from the global system.
  - **DB Interaction:** Delete `Categories`, `Tags`, `PostTags` (indirectly).

- **[Add Category / Add Tag]** - Taxonomy Actions (Admin)
  - **Type:** Mutation (UI)
  - **Action:** Adds a new category or tag entry to the management list.
  - **DB Interaction:** None (UI only until form submit).

#### Saved Posts (`/menu/saved-post`)
- **[Apply]** - Filter Toolbar
  - **Type:** Filter
  - **Action:** Filters bookmarked posts by search, sort order, and date range.
  - **DB Interaction:** Read from `Bookmarks`, `Posts`.

- **[Clear]** - Filter Toolbar
  - **Type:** Filter
  - **Action:** Resets all active filters for saved posts.
  - **DB Interaction:** Read from `Bookmarks`, `Posts`.

#### Admin Reports (`/menu/reports`)
- **[Apply / Clear]** - Report Filters
  - **Type:** Filter
  - **Action:** Filters the audit queue by search, status (read/unread), and date.
  - **DB Interaction:** Read from `Reports`.

- **[Mark Read / Mark Unread]** - Audit Actions
  - **Type:** Mutation
  - **Action:** Toggles the read/unread status of a report.
  - **DB Interaction:** Update `Reports`.

- **[Delete Post / Delete Comment]** - Moderation Actions
  - **Type:** Mutation
  - **Action:** Soft-deletes reported content from the platform.
  - **DB Interaction:** Delete `Posts` or `Comments`.

- **[Suspend Account]** - Moderation Actions
  - **Type:** Mutation (UI trigger)
  - **Action:** Initiates user account suspension process.
  - **DB Interaction:** Likely Delete/Update `Users`.

#### Admin Users (`/menu/users`)
- **[Apply / Clear]** - User Filters
  - **Type:** Filter
  - **Action:** Filters the user directory by search, role, status, and join date.
  - **DB Interaction:** Read from `Users`.

- **[Restore User / Restrict User]** - User Directory Actions
  - **Type:** Mutation
  - **Action:** Soft-deletes (restricts) or restores a user account.
  - **DB Interaction:** Delete/Restore `Users`.

- **[Change Role]** - User Directory Actions
  - **Type:** Mutation
  - **Action:** Toggles a user's role between 'admin' and 'user'.
  - **DB Interaction:** Update `Users`.

#### Admin Activity Log (`/menu/dashboard/activity-log`)
- **[Calendar Day Cell]** - Activity Calendar
  - **Type:** Navigation / AJAX
  - **Action:** Fetches and displays audit logs for the selected date.
  - **DB Interaction:** Read from `AuditLogs`.

- **[Prev / Next Month]** - Activity Calendar
  - **Type:** Navigation
  - **Action:** Navigates the calendar to different months/years.
  - **DB Interaction:** None (triggers page reload with new data).

#### Global UI Actions
- **[Notification Item]** - Notification Dropdown
  - **Type:** Mutation (Redirect via Form)
  - **Action:** Marks a notification as read and redirects the user to the target content.
  - **DB Interaction:** Update `Notificatioins`.

## Database Mutation Summary

| Action | Route / Controller | Tables Affected | Operation |
| :--- | :--- | :--- | :--- |
| **Register** | `RegisteredUserController@store` | `Users` | INSERT |
| **Social Login** | `SocialiteController@callback` | `Users`, `SocialAccounts` | INSERT/UPDATE |
| **Complete Profile** | `ProfileController@update` | `Users` | UPDATE |
| **Verify OTP** | `VerifyOtpController@store` | `OtpVerifications` | INSERT/UPDATE |
| **Resend OTP** | `ResendOtpController@store` | `OtpVerifications` | INSERT |
| **Reset Password** | `NewPasswordController@store` | `Users`, `OtpVerifications` | UPDATE |
| **Bookmark Post** | `BookmarkController@toggle` | `Bookmarks` | INSERT/DELETE |
| **Report Content** | `ReportController@store` | `Reports` | INSERT |
| **Ban Post/User** | `Admin\ModerationController` | `Posts`, `Users` | UPDATE |
| **Post Comment / Rate** | `CommentController@store` | `Comments`, `Ratings` | INSERT |
| **React to Comment** | `CommentReactionController@toggle` | `CommentReactions` | INSERT/DELETE |
| **Update Appearance** | `SettingsController@updateAppearance` | `Settings`, `CustomThemes`, `Users` | UPDATE |
| **Update Security** | `SettingsController@updateSecurity` | `Users`, `Settings` | UPDATE |
| **Update Profile** | `ProfileController@update` | `Users` | UPDATE |
| **Update/Reset Tags** | `TagController@update` / `destroy` | `CustomTags` (User) | INSERT/UPDATE/DELETE |
| **Manage Taxonomy** | `Admin\TaxonomyController` | `Categories`, `Tags`, `AuditLogs` | INSERT/UPDATE/DELETE |
| **Manage Reports** | `Admin\ReportController` | `Reports` | UPDATE |
| **Moderation Delete** | `Admin\ModerationController` | `Posts`, `Comments` | DELETE (Soft) |
| **Manage Users** | `Admin\UserController` | `Users` | UPDATE/DELETE/RESTORE |
| **Notification Read** | `NotificationController@read` | `Notificatioins` | UPDATE |

> **Note:** Actions performed on `NotificationItem` and most Moderation/Taxonomy actions are Admin restricted. `Notificatioins` table is spelled as `Notificatioins` in the current schema.



---

# Mobile Layout Redesign Progress (2026-06-17)

## Completed Tasks

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

# Design Audit Tasks (2026-06-20)

## Completed

### C1. about-us.blade.php - UI tokens aligned
- **Status:** COMPLETED
- **Changes:** Replaced parallel `--border`, `--shadow`, `--radius-lg`, `--radius-md` with shared `--ui-*` tokens from `app.css`. Fixed top spacing (`md:mt-20` → `md:mt-24`). Replaced `<x-fas-s>` icon with `<x-app-logo />` component.

### C2. Remove Font Awesome CDN - dashboard.blade.php & activity-log.blade.php
- **Status:** COMPLETED
- **Changes:**
    - Removed `<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" />` from both files.
    - Replaced all `<i class="fa-solid ...">` icons with colored dots (`<span class="act-legend-dot">`) in both blade templates.
    - Replaced Font Awesome chevrons with Unicode characters (`&#9664;`, `&#9654;`, `&#9662;`).
    - Updated `admin-dashboard.js`: timeline items, category labels, star ratings, comment icons all use colored dots now.
    - Updated `admin-activity.js`: activity entries, modal rows, see-more button all use colored dots now.
    - Removed unused `icon` field from PHP data arrays in both controllers.
    - Redesigned `act-body` section: legend moved to top row with background/border, timeline wrapped in bordered scrollable container.

### Pagination theme fix
- **Status:** COMPLETED
- **Changes:** Fixed CSS selector mismatch in `homepage.css` — changed `#pagination-container` (ID) to `.pagination-container` (class) so theme-aware pagination styles actually apply.

### Comment restriction (1 user = 1 comment)
- **Status:** COMPLETED
- **Changes:**
    - Added `userHasCommented` flag in `PostsController.php`.
    - Passed flag to `comments.blade.php` component.
    - Wrapped comment composer in `@unless($userHasCommented)` — form hidden when user already commented.

## Remaining

### M1. Remove redundant inline styles
- **Status:** COMPLETED
- **Pages:** `home.blade.php`, `upload-post.blade.php`
- **Changes:** Removed inline `style="font-family: var(--font-family); background-color: var(--background-color); color: var(--text-color);"` from both files — already set on `<body>` in `index.blade.php`.

### M2. Move inline pagination styles to CSS
- **Status:** COMPLETED
- **Page:** `home.blade.php`
- **Changes:** Added `.pagination-top` and `.pagination-bottom` classes to `homepage.css`. Replaced inline `style="margin-bottom: 20px;"` and `style="margin-top: 40px; padding-bottom: 40px;"` with these classes.

### M3. Deduplicate pagination DOM
- **Status:** COMPLETED
- **Page:** `home.blade.php`
- **Changes:** Merged mobile/desktop pagination from 4 containers to 2 (top + bottom). Removed `md:hidden` / `hidden md:block` classes — pagination is now responsive in a single container.

### M4. Replace inline SVGs with Blade components
- **Status:** COMPLETED
- **Page:** `post-detail.blade.php`
- **Changes:** Replaced 29 inline Lucide SVGs with Font Awesome Blade components:
  - `lucide-more-horizontal` → `<x-fas-ellipsis>`
  - `lucide-bookmark-check` / `lucide-bookmark` → `<x-fas-bookmark>` / `<x-far-bookmark>`
  - `lucide-flag` → `<x-fas-flag>`
  - `lucide-ban` → `<x-fas-ban>`
  - `lucide-x` → `<x-fas-xmark>`
  - `lucide-send` → `<x-fas-paper-plane>`
  - `lucide-pencil` → `<x-fas-pen>`
  - `lucide-trash-2` → `<x-fas-trash>`
  - Report reason icons: `lucide-alert-triangle` → `<x-fas-triangle-exclamation>`, `lucide-shield-alert` → `<x-fas-shield-halved>`, `lucide-copyright` → `<x-fas-copyright>`, `lucide-eye-off` → `<x-fas-eye-slash>`, `lucide-frown` → `<x-fas-face-frown>`, `lucide-zap` → `<x-fas-bolt>`, `lucide-swords` → `<x-fas-hand-fist>`, `lucide-heart-crack` → `<x-fas-heart-crack>`, `lucide-gavel` → `<x-fas-gavel>`
  - Link icons: `lucide-link` → `<x-fas-link>`, `lucide-external-link` → `<x-fas-arrow-up-right-from-square>`
  - Carousel arrows: chevron-left/right → `<x-fas-chevron-left>` / `<x-fas-chevron-right>`
  - Remaining 4 SVGs are star rating visualizations (custom SVGs for rating display) — kept as-is.

### M5. Extract report modal into Blade component
- **Page:** `post-detail.blade.php`
- **Issue:** Report modal duplicated across masthead and each audit panel.

### M6. Replace hardcoded danger color
- **Page:** `post-detail.blade.php`
- **Issue:** `#b91c1c` hardcoded for ban/delete buttons.

### M7. Replace hardcoded hex colors in dashboard
- **Page:** `dashboard.blade.php`
- **Issue:** Inline styles use `#8b5cf6`, `#6366f1`, `#ef4444`, etc.

### M8. Replace hardcoded colors in JS
- **Page:** `reports.blade.php`
- **Issue:** Inline HTML in JS uses hardcoded hex colors.

### L1-L10. Low priority items
- See full audit report for details.
