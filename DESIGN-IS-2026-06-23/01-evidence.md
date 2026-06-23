# Evidence — SiteSphere Post Detail & Post Card (Mobile)

## 1. Structural Evidence

### Post Detail Page (`post-detail.blade.php`)
- **Interactive elements:** 27 (buttons, links, textareas, scroll controls)
- **Max nesting depth:** ~10 levels (masthead actions menu path)
- **Repeated patterns:** 12 (three-dot menus x2, save/report buttons x2-3, ban buttons x2, star SVGs x6, report modals x2, avatar fallbacks x4, guest login links x2)
- **Dead props:** 0
- **Unused Alpine vars:** 2 (`reportOpen`, `closeReportModal()`, `reportDetailsCount()` in masthead x-data scope — report modal is in separate component)
- **Conditional blocks:** 22 (`@if`, `@auth`, `@guest`, `x-show`)

### Post Card Component (`post-card.blade.php`)
- **Interactive elements:** ~27 unique (~35+ with loops)
- **Max nesting depth:** ~10 levels (report modal path)
- **Repeated patterns:** 10 (save/report buttons x3 variants each, scroll chevrons x2 pairs, comments/review link+button variants, report modal)
- **Dead props:** 1 (`category` declared at line 5, never used)
- **Unused Alpine vars:** 2 (`selectedRating` at :26, `setRating()` at :63)
- **Conditional blocks:** 19

### Post Card Skeleton (`post-card-skeleton.blade.php`)
- **Interactive elements:** 0
- **Max nesting depth:** 4
- **Repeated patterns:** 2 (skeleton bars x7, tag pills x3)
- **Dead props:** 0
- **Unused Alpine vars:** 0
- **Conditional blocks:** 1

### Comments Section (`comments.blade.php`)
- **Interactive elements:** 23 per comment (multiplied by comment count)
- **Max nesting depth:** ~11 levels (report modal path)
- **Repeated patterns:** 15 (three-dot menu, dropdown, report/edit/delete/ban/revert buttons, inline edit form, star SVGs, report modal, helpful button per comment)
- **Dead props:** 0
- **Unused Alpine vars:** 0
- **Conditional blocks:** 23

---

## 2. Visual Evidence (INFERRED — no running instance)

### `post-detail.css`
- **Spacing scale:** 26 distinct values `[1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16, 18, 20, 22, 26, 28, 40, 50, 54, 78, 96]px`
- **Type scale:** 24 distinct values `[0.62rem–2.85rem, 12px]`
- **Distinct colors:** ~54 (22 hex literals, 5 rgba, 10 hsl patterns, 24 CSS custom property references)
- **Lowest contrast:** `var(--faint)` on `var(--paper)` ≈ 2.8:1 FAIL; `var(--muted)` on `var(--paper)` ≈ 4.0:1 FAIL AA normal text
- **States:** :hover ✓, :focus ✓, :active ✗, :disabled ✓, :empty ✓, :error ✗, :success ✗, focus-visible ✗ (in CSS), focus-within ✗
- **Breakpoints:** 2 (`767px`, `768px`)
- **Animations:** 1 @keyframes (`aud-fade`), 3 animation properties, 13 transitions
- **CSS custom properties:** 32 defined in `.post-detail-page` scope

### `homepage.css` (post card ranges)
- **Spacing scale:** 14 distinct values `[2, 4, 5, 6, 7, 8, 10, 14, 15, 16, 20, 22, 28, 40]px`
- **Type scale:** 11 distinct values
- **Distinct colors:** ~20 hex + ~10 color-mix + ~5 var references
- **States:** :hover ✓, :focus ✓, :active ✓, :disabled ✗, :empty ✗, :error ✗, :success ✗, .loading ✓ (skeleton), focus-visible ✓ (Tailwind)
- **Animations:** 4 @keyframes, 4 animation properties, 30+ transitions

---

## 3. Copy & Honesty Evidence

### Inflations (1)
- `post-detail.blade.php:310` — "Expert audits and technical reviews from verified contributors." (no verification mechanism visible)

### Dark Patterns (0)
None found.

### Jargon / Unclear Labels (7)
| File:Line | String | Proposed Replacement |
|-----------|--------|---------------------|
| post-detail:310 | "Expert audits" | "Detailed reviews" |
| post-detail:310 | "verified contributors" | "community members" |
| post-detail:277 | "audits" in meta text | "contributions" |
| post-detail:306 | aria-label "Detailed Reports" | "Detailed reviews" |
| post-detail:649 | "Linked records" (x2) | "Related posts" |
| comments:9 | aria-label "User Reports" | "User Comments" |
| post-card:409 | "Intellectual Property" | "Copyright Violation" |

### Label→Behavior Mismatches (2)
- `post-card.blade.php:175` — "Save Post" button with no action handler when `$postId` is null
- `comments.blade.php:9` — aria-label "User Reports" on a comments section (heading says "User Comments")

### Auth-Gated Content
17 distinct auth-check blocks across all files. Guests see login prompts; admins see ban/revert/delete actions; comment owners see edit/delete.

### Report Modal Inconsistency
Post report modal and comment report modal have different options:
- Post: has "Intellectual Property", "Self-Harm Risk", "Illegal Activities", "Scams/Fraud"
- Comment: has "Misinformation", "Other"; missing "Legal & Integrity" section

---

## 4. Weight & Friction Evidence

- **Estimated JS:** ~20.3 KB (post-detail.js 259 lines + Alpine directives)
- **Network requests:** ~11-12 (CSS, JS, Google Fonts, favicon, SweetAlert2)
- **Animations on idle:** 50+ CSS transition properties, 7 Alpine transition groups, 1 @keyframes animation
- **Modals:** 4 modal overlays, 9 dropdown menus, 10 SweetAlert2 triggers
- **Inline styles:** 38 (17 post-detail, 1 post-card, 20 comments)
- **Images:** 5 `<img>` tags, 17+ `<svg>` tags, no lazy loading
- **External deps:** Alpine.js, Tailwind CSS, SweetAlert2, Google Fonts (Figtree + 5 alternates)

---

## 5. Accessibility Evidence

### Contrast
- `var(--faint)` on `var(--paper)`: ~2.8:1 **FAIL** AA
- `var(--muted)` on `var(--paper)`: ~4.0:1 **FAIL** AA normal text
- `.ss-helpful-count` color `var(--muted)`: ~4.0:1 **FAIL**
- `.ss-bar-label` color `var(--faint)`: ~2.8:1 **FAIL**
- `.ss-eyebrow` color `var(--faint)`: ~2.8:1 **FAIL**
- `.aud-depo-date` color `var(--faint)`: ~2.8:1 **FAIL**
- `.aud-related-meta` color `var(--faint)`: ~2.8:1 **FAIL**
- `.ss-tag.is-alert` color `var(--warn)` on `var(--warn-wash)`: ~3.1:1 **FAIL**
- Inline `color-mix(..., 42-58%)` on white: all estimated below 4.5:1 **FAIL**

### Focus
- Post-detail.css: 1 `:focus` rule (removes outline, adds border-color + box-shadow)
- Tailwind utilities: inconsistent — some buttons get `focus-visible:ring-2`, others only `focus:outline-none` with no replacement
- Dropdown menus: no arrow-key navigation (WCAG 2.1.1 Partial)

### Form Labels
3 unlabeled textareas:
- `reviewTextarea` — comments.blade.php:51
- `description` textarea — post-detail.blade.php:594
- `content` textarea — comments.blade.php:311

### Skip-Link
**Missing.** No skip-link in any audited file.

### Semantic HTML
- 109 `<div>` vs 14 semantic elements = 7.8:1 ratio
- No `<time>` elements despite date display
- No `<figure>`, `<figcaption>`, `<address>`, `<blockquote>`

### ARIA Landmarks
- 5 landmarks with aria-labels (nav "Auditors", sections "Overall verdict"/"Detailed Reports"/"Linked records", section "User Reports")
- 4 landmarks without aria-labels (main, 2 headers, footer, article root)

### Keyboard Reachability
All primary actions reachable via Tab + Enter/Space. Three-dot menus lack arrow-key navigation within the menu.
