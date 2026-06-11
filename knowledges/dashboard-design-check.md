# UI/UX Audit Report (Desktop Viewport)

## Testing Credentials
- **Admin Email:** `anthtooaung2792005@outlook.com`
- **Admin Password:** `123!@#123`

## Audit Progress
- **Status:** `COMPLETED`
- **Completion Percentage:** `100%`
- **Last Audited Path:** `Administrative / Global Elements`
- **Next Path to Check:** `Implementation phase`
- **Resumption Notes:** `Desktop source audit completed from Blade/CSS/routes. No app code was modified. Use the findings below as the implementation backlog.`

---

## Audit Guidelines & Constraints
1. **Desktop Only:** This audit focuses exclusively on 1280px+ viewports.
2. **Sequential Workflow:** Audit follows the user journey from entry (Auth) to deep application paths.
3. **No Code Modification:** All findings are logged here. Implementation happens in a separate phase.
4. **Visual Specifications:** Findings include colors, spacing, typography, and interaction states.
5. **Token Management:** If remaining tokens fall below 10%, stop the audit immediately and document progress/remaining tasks in this file.

---

## 1. Authentication Flows

### 1.1 Login Page
- **Required Changes:**
  - Keep the split-panel concept, but reduce visual weight so it matches the quieter dashboard surfaces. Current shell uses a large `0 28px 70px rgba(17, 24, 39, 0.18)` shadow and strong blue background gradients.
  - Add clearer keyboard focus states to social buttons, the password visibility button, and the primary submit button. Hover exists, but focus should be equally visible.
  - Keep the fixed desktop shell, but avoid page-level `html/body { overflow: hidden; }` for any auth error state that can increase vertical height.
  - Ensure validation errors reserve vertical space below inputs so the layout does not jump after failed login.
- **UI/UX Specifications:**
  - Shell: `max-width: 1080px`, desktop height `min(660px, calc(100dvh - 48px))`, border radius reduce from `14px` to `8px`, border `1px solid color-mix(in srgb, var(--accent-color) 14%, transparent)`.
  - Shadow: replace large shadow with `0 12px 32px rgba(15, 23, 42, 0.10)`.
  - Form padding: keep `78px 50px 44px` for 1280px+, but cap inner form width at `360px`.
  - Typography: `h1` `32px/1.1` weight `800`; section label `11px`, uppercase, letter spacing `0.08em`; helper text `14px/1.5` in `#64748b`.
  - Inputs: height `46px`, radius `8px`, background `#f8fafc`, border `#dbe7ff`; focus ring `0 0 0 3px color-mix(in srgb, var(--accent-color) 18%, transparent)`.
  - Primary button: height `46px`, radius `8px`, background `var(--accent-color, #6c5ce7)`, hover `filter: brightness(0.96)`, active `transform: translateY(1px)`.

### 1.2 Registration Page
- **Required Changes:**
  - Registration is denser than login and competes with the timeline panel. Reduce secondary copy and make the timeline a compact progress rail on desktop.
  - Avoid a modal-within-panel feeling for the registration continuation flow. The current registration modal has multiple radii and heavy shadows that can feel separate from the auth shell.
  - Align password requirements with inline helper text rather than relying on `title` text only.
  - Make the OTP/profile/confirm steps visually consistent with the first registration form.
- **UI/UX Specifications:**
  - Registration form column: `max-width: 390px`, vertical gap `14px`, input height `44px`.
  - Timeline panel: use a `1px` vertical line in `rgba(255,255,255,0.28)`, step circles `28px`, active circle `#ffffff`, active text `#ffffff`, inactive text `rgba(255,255,255,0.72)`.
  - Registration modal: radius `8px`, shadow `0 18px 42px rgba(5, 8, 22, 0.18)`, header padding `24px`, body gap `16px`.
  - Error text: `13px/1.4`, color `#dc2626`, icon or left border optional, spacing `6px` below the affected field.

---

## 2. Core Application Paths

### 2.1 Dashboard / Home
- **Required Changes:**
  - The home page starts with filters before content. Move the results summary and sort control above the selected-filter panel so desktop users see content state first.
  - The sidebar and main content use `height: 100vh` with nested scrolling. This works, but the fixed nav is `85px`; ensure page content consistently offsets below it and does not hide top content.
  - Reduce overuse of rounded panels: home filter panel currently reaches `16px`, post cards use `20px`, and sidebar/category buttons use `14px`. Desktop dashboard tools should feel denser and more operational.
  - Improve empty selected-filter state. The current selected-filter panel can look like a blank card when no filters are active.
- **UI/UX Specifications:**
  - Desktop content offset: `padding-top: 96px` when fixed nav is present.
  - Main content padding: `24px` at 1280px+, not `12px`.
  - Sidebar: width `280px`, padding `16px`, section gap `16px`, border `1px solid color-mix(in srgb, var(--text-color) 10%, transparent)`.
  - Selected filters: radius `8px`, padding `16px`, no large shadow; use border-only surface with background `color-mix(in srgb, var(--background-color) 96%, var(--accent-color) 4%)`.
  - Results toolbar: height `48px`, display as a plain row, sort select width `180px`, focus ring matching accent.
  - Review grid: `grid-template-columns: repeat(auto-fill, minmax(320px, 1fr))`, gap `20px`, align cards to stretch.

### 2.2 Resource Discovery / Listings
- **Required Changes:**
  - Post cards are visually rich but too tall and rounded for dense desktop browsing. The content hierarchy should emphasize title, URL, rating, and latest review first.
  - The actions button has no rounded class while surrounding elements use strong radii. Normalize icon buttons to a consistent `8px` or circular treatment.
  - Tag scrollers use left/right chevrons even when the tag list is short; hide disabled arrows when scrolling is unnecessary.
  - Rating filter, category filter, and tag filter controls need identical active/hover/focus behavior.
- **UI/UX Specifications:**
  - Card radius: reduce from `rounded-[20px]` to `8px`; border `color-mix(in srgb, var(--text-color) 10%, transparent)`.
  - Card shadow: default none or `0 4px 14px rgba(15, 23, 42, 0.06)`; hover shadow `0 10px 24px rgba(15, 23, 42, 0.10)`.
  - Card padding: header `16px`, internal gap `12px`.
  - Title: `16px/1.35`, weight `800`, clamp to two lines with stable min-height `44px`.
  - Metadata pills: radius `6px`, font `12px`, padding `4px 8px`.
  - Action icon buttons: `32px x 32px`, radius `8px`, hover background `color-mix(in srgb, var(--accent-color) 10%, transparent)`, focus-visible ring `2px`.

### 2.3 Individual Resource / Post View
- **Required Changes:**
  - The post detail page introduces a separate editorial visual system with `--radius-lg: 20px`, serif/mono tokens, blur backgrounds, and heavier shadows. Bring it closer to the dashboard system.
  - The post detail page does not render the account side menu while still using `dashboard-page--{location}`. Decide whether detail view is full-width by design; if so, use a clear max-width and page title rhythm.
  - Report modals use `rounded-3xl` and deep shadows; align these with the rest of the dashboard modal system.
  - Inline styles for avatar dimensions and SVG sizes should be normalized in CSS in the implementation phase.
- **UI/UX Specifications:**
  - Detail content max width: `1180px`, centered, desktop padding `24px`.
  - Main cards: radius `8px`, border `1px solid var(--line)`, shadow `0 8px 24px rgba(18, 26, 41, 0.08)`.
  - Masthead title: `34px/1.15`, weight `850`, margin-bottom `16px`; URL/meta row `13px/1.5`.
  - Report modal: `max-width: 560px`, radius `8px`, header padding `24px 24px 8px`, body padding `16px 24px`, footer padding `16px 24px 24px`.
  - Report options: two-column grid, gap `10px`, option radius `8px`, selected border `var(--accent-color)`, selected background `color-mix(in srgb, var(--accent-color) 10%, var(--background-color) 90%)`.

---

## 3. User Features

### 3.1 User Profile & Preferences
- **Required Changes:**
  - Profile detail uses background blur decoration and a card radius of `16px`; reduce decoration and make it feel more like an account workspace.
  - The phone field displays `+95Not specified` when the value is missing. This is a content/UI bug and should show `Not specified` without the prefix.
  - "Last Login: Today" uses `now()` rather than a real last-login value. This creates false status information.
  - Profile settings form is well structured, but save/error feedback should be visible near the submit button and not rely only on toast behavior.
- **UI/UX Specifications:**
  - Profile content padding: `24px`, container max width `1200px`, gap `20px`.
  - Profile card radius: `8px`, border `1px solid var(--border)`, shadow `0 8px 24px rgba(15, 23, 42, 0.06)`.
  - Avatar: `112px x 112px`; edit button `32px` high, radius `6px`, font `13px`.
  - Info grid: two columns, gap `16px 24px`; labels `12px`, value text `14px/1.4`, long email values wrap with `overflow-wrap: anywhere`.
  - Stats cards: four columns at 1280px+, radius `8px`, padding `16px`, icon `40px`.

### 3.2 Bookmarks & Interactions (Comments/Ratings)
- **Required Changes:**
  - Saved posts page has strong filtering controls, but the toolbar can become visually heavy because search, sort, two dates, and actions sit in one row. Use a clear grid with predictable widths.
  - AJAX loading state exists but should include a non-layout-shifting overlay or skeleton row/card state.
  - Bookmark/report interactions are hidden behind ellipsis menus; saved state should be more scannable on cards in the saved page.
  - Rating and comment interactions need consistent success/error feedback in the same toast location and style.
- **UI/UX Specifications:**
  - Saved toolbar grid: search `minmax(320px, 1fr)`, sort `180px`, dates `150px` each, actions `auto`; gap `12px`.
  - Toolbar controls: height `42px`, radius `8px`, border `color-mix(in srgb, var(--text-color) 12%, transparent)`.
  - Loading: skeleton cards with `height: 260px`, radius `8px`, background shimmer from `rgba(148,163,184,0.12)` to `rgba(148,163,184,0.22)`.
  - Saved card indicator: add a visible bookmark pill in the card header, `height: 26px`, text `Saved`, color `var(--accent-color)`, background `color-mix(in srgb, var(--accent-color) 12%, transparent)`.

---

## 4. Administrative / Global Elements

### 4.1 Navigation & Footer
- **Required Changes:**
  - Desktop nav is fixed at `85px` high and visually heavier than the dashboard content. Reduce height and shadow for a denser app feel.
  - Search expands from `240px` to `320px`, which can shift nearby nav items on desktop. Reserve the expanded width or position the expansion without layout movement.
  - Hover animations rotate/scale some icons strongly, especially the create button. Use restrained motion for repeated dashboard workflows.
  - The menu component supports left/right/top/bottom positions. Each page should verify content offset and scroll behavior for all four positions before implementation is considered complete.
- **UI/UX Specifications:**
  - Nav height: `72px`; horizontal padding `24px`; border bottom `1px solid color-mix(in srgb, var(--text-color) 10%, transparent)`.
  - Nav shadow: default none; scrolled `0 6px 18px rgba(15, 23, 42, 0.06)`.
  - Search: reserve `320px` width at desktop; collapsed visual can still show `240px` inner input, but layout width should not change.
  - Nav links: font `14px`, weight `700`, icon `18px`, underline height `2px`, hover translate max `-2px`.
  - Account side menu: width `240px`, active item radius `8px`, item height `40px`, section heading `11px` uppercase with `0.08em` letter spacing.

### 4.2 Feedback & Notifications (Toast/Modals)
- **Required Changes:**
  - SweetAlert2 is loaded in both the base layout and auth view. Avoid duplicate script loading in the implementation phase.
  - Auth toast timer is `1000ms`, too short for error messages. Increase for errors and keep success shorter.
  - Notification dropdown needs a clear max height and empty/read states. Current markup focuses on unread notifications only.
  - Report modals, crop modal, registration modal, and SweetAlert confirmations should share one modal visual language.
- **UI/UX Specifications:**
  - Toast success: `2200ms`; toast error/warning: `4500ms`; width `min(360px, calc(100vw - 32px))`; radius `8px`.
  - Toast colors: success `#16a34a`, warning `#f59e0b`, error `#dc2626`; text `var(--text-color)`, background `var(--background-color)`.
  - Dropdowns: radius `8px`, border `color-mix(in srgb, var(--text-color) 12%, transparent)`, shadow `0 12px 28px rgba(15, 23, 42, 0.12)`, max height `420px`, overflow-y `auto`.
  - Modal overlay: `rgba(15, 23, 42, 0.45)` with `backdrop-filter: blur(6px)`.
  - Modal panel: radius `8px`, max width by context, shadow `0 24px 56px rgba(15, 23, 42, 0.18)`.

---

## Miscellaneous Observations
- The desktop app currently mixes utility classes, large custom CSS files, inline styles, Flowbite dropdowns, Alpine state, and SweetAlert2. Implementation should first define shared dashboard primitives for `card`, `button`, `input`, `dropdown`, `modal`, `toast`, and `table`.
- The UI is dominated by `var(--accent-color, #6c5ce7)` and blue/purple color mixes. Keep the brand accent, but use more neutral borders/surfaces so repeated dashboard pages feel less one-note.
- Many desktop surfaces use border radii above `8px` (`14px`, `16px`, `18px`, `20px`, `30px`, `999px`). Keep pills circular only where semantically a pill/avatar is expected; use `8px` for most cards and controls.
- Several pages use decorative blur blobs. For operational dashboard views, remove or reduce them because they compete with tables, forms, and review content.
- The base dashboard layout sets desktop `html, body { overflow: hidden; }`. This requires every page to manage its own scroll perfectly. Audit implementation should test all desktop menu positions at `1280x800`, `1440x900`, and `1920x1080`.
- Source audit only: no browser screenshot pass was performed in this phase. Implementation verification should include Playwright or browser screenshots for login, register, home, post detail, profile, saved posts, and reports.
