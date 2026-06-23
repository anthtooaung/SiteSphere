# Evidence — SiteSphere Mobile Homepage Design Audit

## 1. Structural Evidence

### Interactive elements
- ~73 unique interactive elements in mobile layout shell + ~26 per post card
- With 10 cards/page: ~333 total interactive elements
- 53 distinct Blade components used
- 17 repeated patterns (same affordance duplicated across viewports/contexts)

### Nesting depth
- Max 16 levels deep from `home-page` root (report modal radio labels → icon components)

### Dead code
- `category` prop in post-card.blade.php:5 — declared, passed, never used
- `$isDashboardRoute` in menu.blade.php:10 — declared, never used
- Dropdown filter UI (home-aside.blade.php:83-189) is dead code on mobile for default config

### Architecture
- `@mobile`/`@desktop` directives in nav.blade.php (2 pairs) — server-side User-Agent sniffing
- 2 inline `<script>` blocks (1 mobile-exclusive, 1 shared)
- 17 repeated patterns: "Clear All Filters" (2x), Profile Menu (2x), Login button (2x), all nav items (2-3x each), filter sections (2x each), pagination (2x), save/report/review buttons (3+ variants each)

---

## 2. Visual Evidence

### Spacing scale (mobile breakpoints)
Unique values: `0, 2px, 3px, 4px, 5px, 6px, 7px, 8px, 9px, 10px, 11px, 12px, 14px, 15px, 16px, 18px, 20px, 22px, 24px, 25px, 28px, 30px, 34px, 36px, 40px, 42px, 45px, 48px, 70px, 82px, 96px, 100px`
**Non-systematic. No consistent 4px/8px grid.** Values include 5px, 7px, 9px, 11px, 13px, 15px, 18px.

### Type scale (mobile breakpoints)
25+ unique font-size values: `7.68px, 10.56px, 10.88px, 11.52px, 12px, 12.16px, 12.48px, 13.6px, 13.76px, 14px, 14.08px, 14.4px, 14.72px, 15.2px, 15.68px, 16px, 17.6px, 18px, 19.2px, 20px, 20.8px, 22.4px, 23.2px, 28px, 28.8px, 48px`
**Non-systematic.** Fractional rem values like 0.66rem, 0.72rem, 0.76rem, 0.78rem — many just 1-2px apart.

### Colors
9 distinct hue families: Purple/Indigo, Dark navy, Light blue, Blue, Gray, Red, Green, Amber/Orange, White.

### Contrast
- Lowest on homepage: ~3.4:1 at `.home-empty-state p` (homepage.css:1593) — 64% opacity text
- Timestamps in post cards: ~3.7:1 (post-card.blade.php:309, 52% opacity)
- Footer copyright: ~3.3:1 (`text-slate-500` on `bg-slate-900`)
- Mobile brand accent: ~4.0:1 (`#6c5ce7` on frosted white)

### States checklist
| State | Status |
|-------|--------|
| Empty | YES — homepage.css:1565-1596 |
| Loading | YES — homepage.css:3125-3136 (skeleton pulse) |
| Error | **MISSING** |
| Success | **MISSING** |
| Focus | **PARTIAL** — suppressed on checkboxes (homepage.css:2231-2245), massive hover/focus reset block (homepage.css:2266-2294) |
| Disabled | YES — homepage.css:3106-3109, post-card.blade.php:528 |

---

## 3. Copy & Honesty Evidence

### Inflations
- footer.blade.php:20: "Empowering better resource choices. Join our global community of trusted website and honest tool reviewers." — "global community" (no i18n), "trusted/honest" (no verification system), "Empowering" (vague)
- home.blade.php:22: "Discover Useful Websites" — "Useful" is subjective, no usefulness metric
- home.blade.php:23: "simple, clear filters" — subjective quality claim

### Dark patterns
- post-card.blade.php:76-96: `authAlert()` uses SweetAlert2 with `icon: 'warning'` — confirmshaming-adjacent for normal auth gates
- Guest report button gated behind login warning — could discourage safety reporting

### Jargon
- "Intellectual Property" → "Copyright / Plagiarism"
- "Nudity / Obscenity" → "Adult / Explicit Content"
- "Legal & Integrity" → "Legal Issues"
- "Content Quality" → "Misleading or Inappropriate"
- "Setting" (singular) → "Settings"
- "Best match" → "Recommended" or "Relevance"
- "Edit Tag" → "Manage Tags"

### Label→behavior mismatches
1. "Create Post" (desktop tooltip) vs "Write review" (mobile aria-label) — same route, different semantics
2. "Save Post" / "Report" dead buttons when `$postId` is null — no form, no click handler
3. "Latest Reviews" and "Top Rated Software" footer links — both link to same homepage URL with no sort params
4. "Alerts" (mobile) vs "Notifications" (desktop) — same data, different labels
5. "Setting" (singular) for section with 4 items

---

## 4. Weight & Friction Evidence

### JS payload
- ~50 KB gzipped (Alpine.js ~15KB, Flowbite ~30KB, collapse ~1KB, inline scripts ~4KB)
- SweetAlert2 lazy-loaded from CDN on demand (not in initial payload)

### Network requests
- 7-9 minimum: 4 Vite bundles + 3-5 Google Fonts requests

### TTI estimate
- ~1,300ms on fast 3G / mid-range mobile
- ~400-600ms on fast broadband

### Animations on idle screen
- 0 auto-playing visible on mobile idle (footer decorative animations hidden via `hidden md:block`)
- Skeleton pulse only during loading states
- Loading spinner briefly during navigation

### Hidden elements on initial load
- 11 base overlays/modals/badges + 2 per post card (actions dropdown + report modal)
- For 10 cards: 31 hidden elements in DOM

### Inline scripts
- 5 inline `<script>` blocks across homepage views

---

## 5. Accessibility Evidence

### Contrast failures
- Timestamps: ~3.7:1 (FAIL)
- Footer copyright: ~3.3:1 (FAIL)
- Mobile brand accent: ~4.0:1 (FAIL)

### Keyboard failures
- Sidebar section headers: `<div @click>` — not focusable, not keyboard operable
- Remove filter buttons: `<span class="remove-btn">` — no tabindex, no role="button"

### Skip link
- **NOT PRESENT**

### Touch target failures (9 elements under 44x44px)
- Filter trigger: ~36x36px
- Sidebar close: ~40x40px
- Post card actions: 32x32px
- Tag/profile scroll buttons: 28x28px
- Remove filter buttons: 12x12px
- Sort select: 34px height
- Clear All Filters: ~30px height
- Post card menu items: 36px
- Category checkboxes: ~30px effective height

### ARIA landmarks
- 7 landmarks total; only 1 has aria-labelledby
- Bottom nav, header, main, aside, footer all lack aria-labels
