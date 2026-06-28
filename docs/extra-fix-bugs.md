# Extra Fix — Bug Tracker

> Source: `/home/pc/Documents/Extra fix.md`

---

## Both Platforms

### 1. Post Detail & Post Card — Title/URL Restructure
- **Status:** 🔵 Finalized — Ready to Implement
- **Priority:** High
- **Description:** Remove `title` from `posts` table. Add `title` and `slug` to `user_posts` table. Post card shows domain instead of title. User's title appears in the description box under user tabs.

#### Schema Changes
- **`posts` table:**
  - Remove `title` column
  - `url` remains unique (no duplicate URLs — reuse existing `posts` row if URL already exists)
  - `slug` stays, derived from URL domain (e.g., `google` from `google.com`)
- **`user_posts` table:**
  - Add `title` column (user-written title)
  - Add `slug` column (derived from title)
- Run `php artisan migrate:fresh --seed` (no data migration needed)

#### Post Card Layout (top → bottom)
1. **Domain** (replaces title) — clickable, shows domain name
2. **Tags / Ratings**
3. **Description box** — contains: user tabs, title (from `user_posts`), description text
4. **Footer** — comments, save, report, etc.

#### Click Behavior
- **Click domain** → shows a popover/dropdown with 2 options:
  - "Go to post detail"
  - "Go to URL" (opens external link)
- **Click anywhere else** on the card (except reviews, comments, user tabs) → route to `posts.show`

#### Post Detail Page
- Uses `posts.show` route (by post ID, not slug — avoids conflicts since URLs are unique)
- Shows all `user_posts` as tabs (each tab = one user's title + description)
- Domain displayed prominently at top

#### Upload Post Flow
- User inputs: title, URL, description
- If URL already exists in `posts` → reuse that row, create new `user_posts` row
- If URL is new → create new `posts` row + new `user_posts` row
- `posts.slug` = domain name from URL
- `user_posts.slug` = derived from title

#### Files to Change
- Database migrations: `posts` (drop title), `user_posts` (add title, slug)
- `PostController@store` — update creation logic
- `post-card.blade.php` — restructure layout
- `post-detail.blade.php` — add user tabs with title/description
- `upload-post.blade.php` — update preview
- Related CSS files

---

### 2. Report Page — Click Row Navigates to Reported User
- **Status:** 🟢 Ready
- **Priority:** Medium
- **Description:** When a user clicks a report card/row (in Post, Comment, or Description tabs), it should route to the related user's profile — not just the report detail.
- **Current Behavior:** Only the User tab links to the reported user's profile. Post/Comment/Description tabs only link to `route('reports.open', $report)`.
- **Fix:** Add a clickable link to the reported user's profile in Post, Comment, and Description report rows. Eager-load the author relationship. Use `route('profile-detail', ['slug' => $author->slug])`.
- **Files:** `resources/views/layout/menu/reports.blade.php`

---

### 3. Notification Page — Delete Redirects to Forbidden Page
- **Status:** 🟢 Ready
- **Priority:** Medium
- **Description:** When a user deletes a notification, they should be redirected to the notifications index page — not a forbidden/403 page.
- **Current Behavior:** `NotificatioinsController@destroy` calls `redirect()->back()`, which may resolve to an invalid or protected URL depending on the referrer.
- **Fix:** Change `redirect()->back()` to `redirect()->route('notifications.index')`.
- **Files:** `app/Http/Controllers/NotificatioinsController.php`

---

### 4. Activity Log — Show Banned Action Details
- **Status:** 🟢 Ready
- **Priority:** Medium
- **Description:** Banned actions in the activity log should show more detail: the reason for the ban and who was banned (target user/post).
- **Current Behavior:** `reason` is passed from PHP to JS but never rendered. Target info is used only for URL generation, not displayed as text. Action text shows raw DB value (e.g., `ban_user`) instead of human-readable label.
- **Fix:**
  - Render `a.reason` in the activity log entries and detail modal
  - Display target name as visible text (e.g., "banned user: John Doe")
  - Use human-readable action label instead of raw action string
- **Files:** `resources/js/admin-activity.js`, `resources/views/layout/menu/activity-log.blade.php`

---

## Mobile

### 5. Edit Tag & Appearance — Shared Color Picker Component
- **Status:** 🔵 Finalized — Ready to Implement
- **Priority:** Medium
- **Description:** Create a shared color picker blade component that works on both mobile and desktop. The current `<input type="color">` is unusable on mobile — users can't pick colors freely or input hex codes.

#### Component Requirements
- Visual color swatch (colored box) — clickable to open native picker
- Text input field for typing hex codes (e.g., `#FF5733`)
- Swatch and text input stay in sync (changing one updates the other)
- Works on both mobile and desktop
- Used on both Edit Tag and Appearance pages

#### Files to Change
- Create: `resources/views/components/color-picker.blade.php` (or similar)
- Update: `resources/views/layout/menu/edit-tag.blade.php` — replace `<input type="color">` with new component
- Update: `resources/views/layout/menu/appearance.blade.php` — replace `<input type="color">` with new component
- Update: `resources/css/edit-tag.css` — style the new component
- Update: `resources/css/appearance.css` — style the new component

---

### 6. Profile Detail — Flexible Inner Box Sizing
- **Status:** 🟢 Ready
- **Priority:** Low
- **Description:** Inner data boxes on the profile detail page should be flexible/sized properly on mobile.
- **Current Behavior:** `.profile-detail-content` padding stays at 24px on all screens. `.review-box` padding only reduces to 25px at 768px. `.expansion-container` padding (30px) never changes.
- **Fix:**
  - Reduce `.profile-detail-content` padding to 16px at 768px
  - Reduce `.expansion-container` padding on mobile
  - Ensure `box-sizing: border-box` is consistent
- **Files:** `resources/css/profile-detail.css`

---

### 7. Post Detail — Page Not Centered
- **Status:** 🟢 Ready
- **Priority:** Low
- **Description:** The post detail page should be centered on mobile. Currently it may overflow to the left.
- **Current Behavior:** `.ss-page` uses `margin: 0 auto` which should center it, but `.aud-domain` has `min-width: 320px` causing horizontal overflow on narrow viewports.
- **Fix:** Change `.aud-domain` `min-width` from `320px` to `min(320px, 100%)` or `auto`.
- **Files:** `resources/css/post-detail.css`

---

### 8. Noti Btn — Duplicate Empty State on Mobile
- **Status:** 🔵 Finalized — Ready to Implement
- **Priority:** Medium
- **Description:** On mobile, the "No unread notifications" empty state (with SVG) appears **twice** simultaneously in the notification panel.

#### Current Behavior
- Two copies of the empty state message + SVG are rendered and both visible at the same time on mobile
- This only happens in the "no notifications" state

#### Fix
- Find and remove the duplicate rendering so only one instance shows on mobile
- Likely one instance is from the desktop dropdown and one from the mobile overlay — need to hide one at the appropriate breakpoint

#### Files to Change
- `resources/views/components/noti-btn.blade.php`
- Related CSS (nav.css or noti-btn styles)

---

### 9. Notification Page — Bottom Scroll for Mobile Nav
- **Status:** 🟢 Ready
- **Priority:** Low
- **Description:** The notification page needs a bottom scroll margin to avoid being hidden by the mobile bottom navigation bar.
- **Current Behavior:** Missing `margin-bottom: 96px` that other pages (appearance, edit-tag, profile-detail) have at the 900px breakpoint.
- **Fix:** Add `margin-bottom: 96px` to the notification page content at `max-width: 900px`.
- **Files:** `resources/css/` (notification-specific CSS or inline in blade)

---

## Desktop

*(No items listed in the source document)*

---

## Legend

| Symbol | Meaning |
|--------|---------|
| 🔵 Finalized | Requirements confirmed, ready to implement |
| 🟢 Ready | Clear requirements, can be implemented |
| 🟡 Needs Discussion | Ambiguous or requires design decision |
| 🔴 Blocked | Cannot proceed until dependency is resolved |
