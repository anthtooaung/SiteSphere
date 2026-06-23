# Handoff — /make-plan Prompt

```
/make-plan Redesign SiteSphere mobile homepage. Current design failed audit at 13/30 with critical gaps in principles #2 (useful), #4 (understandable), #6 (honest), #8 (thorough), #10 (as little design as possible).

Verdict paragraph (quoted from 03-verdict.md):
> The SiteSphere mobile homepage scored 13/30 with a critical failure on principle #8 (thorough — 0/3): error and success states are completely missing, focus rings are actively suppressed, 9 touch targets are undersized (worst: 12px remove buttons), and contrast fails on timestamps and footer text. The `@mobile`/`@desktop` server-side detection architecture forces duplicated HTML trees and makes responsive behavior brittle. 17 repeated patterns, dead buttons, and label→behavior mismatches erode trust and usability.

Why redesign and not refine: Principle #8 (thorough) scored 0 — error and success states are completely missing, focus rings are actively suppressed across all checkboxes and most buttons, 9 touch targets are under 44x44px (worst: 12px remove filter buttons), and contrast fails on 3 text tokens. The `@mobile`/`@desktop` server-side detection is an architectural anti-pattern that forces all responsive work into duplicated Blade templates rather than CSS.

Preserve from current design (MUST be non-empty — at minimum, name the brand tokens):
- CSS custom property system (`--background-color`, `--text-color`, `--accent-color`, `--font-family`) — index.blade.php:19-21
- `color-mix()` opacity pattern for consistent muted variants — homepage.css:8
- Post card component structure (title → rating → link → tags → profile → footer) — post-card.blade.php:99-367
- Bottom navigation pattern (frosted glass, 5-item layout) — nav.css:2441-2565
- Alpine.js + Blade component architecture

Discard (MUST be non-empty — name the structural patterns causing the failures):
- `@mobile`/`@desktop` server-side detection — nav.blade.php:1-145. Caused failure on principles #8 (thorough) and #10 (as little design as possible) by forcing duplicated HTML trees.
- Duplicated filter UI (dropdown mode + sidebar mode in same component) — home-aside.blade.php:83-304. Caused failure on #10 (as little design as possible).
- 3x Save/Report/Review button variants per card (auth with postId / auth without postId / guest) — post-card.blade.php:131-203. Caused failure on #10 and #6 (honest — dead buttons).
- Global hover/focus/active state suppression block — homepage.css:2266-2294. Caused failure on #8 (thorough — focus rings removed).
- Server-side `@mobile` script block for overlay management — nav.blade.php:95-144. Caused failure on #8 (inline script not accessible).

Top 5 moves from the audit (verbatim):
1. Principle #8 — Thorough: Replace `@mobile`/`@desktop` Blade directives with CSS-only responsive patterns (`hidden md:flex`, `flex md:hidden`). Add error and success states. Fix all 9 undersized touch targets to 44x44px minimum. Restore focus rings on checkboxes and buttons. Fix contrast on timestamps (≥4.5:1), footer copyright, and brand accent. Evidence: nav.blade.php:1-145, homepage.css:2231-2294, homepage.css:1452-1453, post-card.blade.php:116.
2. Principle #6 — Honest: Remove inflated footer tagline claims ("global community of trusted reviewers"). Replace `authAlert()` warning icon with neutral icon. Fix dead "Save Post" and "Report" buttons (post-card.blade.php:170-184). Make footer links sort correctly or remove the misleading labels. Evidence: footer.blade.php:20, post-card.blade.php:82, post-card.blade.php:170-184, footer.blade.php:40-46.
3. Principle #4 — Understandable: Unify "Create Post" / "Write review" labels. Fix "Setting" → "Settings". Unify "Alerts" / "Notifications". Replace jargon in report modal ("Intellectual Property" → "Copyright / Plagiarism", etc.). Make sidebar section headers keyboard-operable (`<button>` instead of `<div @click>`). Convert `<span class="remove-btn">` to `<button>`. Evidence: create-post-btn.blade.php:12-17, menu.blade.php:143, noti-btn.blade.php:70, post-card.blade.php:409, home-aside.blade.php:197, home.blade.php:59.
4. Principle #3 — Aesthetic: Adopt a consistent spacing scale (4px or 8px grid). Reduce type scale to 5-6 sizes. Remove the massive hover/focus/active state suppression block (homepage.css:2266-2294) and design intentional states. Consolidate the 3184-line homepage.css with duplicated media queries. Evidence: homepage.css:2266-2294, homepage.css (32 unique spacing values, 25+ font sizes).
5. Principle #10 — As little design as possible: Eliminate duplicated filter UI (dropdown vs sidebar). Consolidate Save/Report/Review button variants using Alpine.js conditionals instead of separate Blade blocks. Remove hidden report modals from DOM (render on demand). Reduce repeated patterns from 17 to under 5. Evidence: home-aside.blade.php:83-189 vs 190-304, post-card.blade.php:131-203 (3 variants each).

Redesign principles in priority order:
1. Thorough (#8) — Every state (empty, loading, error, success, focus, disabled) present and considered. All touch targets ≥44px. All contrast ≥4.5:1. Skip-link present. ARIA landmarks labeled.
2. Honest (#6) — Every label maps 1:1 to behavior. No dead buttons. No inflated claims. No confirmshaming. Footer links sort correctly.
3. Understandable (#4) — A first-time user names every primary control correctly. No jargon. Consistent terminology across viewports. Sidebar section headers keyboard-operable.
4. As little design as possible (#10) — Every element earns its place. No duplicated UI. No hidden modals per card. CSS-only responsive. Under 5 repeated patterns.
5. Aesthetic (#3) — Spacing on 4px grid. Type scale of 5-6 sizes. Intentional hover/focus/active states. Single CSS file under 1500 lines.

Deliverables for the plan:
- New information architecture (remove `@mobile`/`@desktop`, single responsive HTML tree)
- New primary flow (filter → browse → evaluate → click through, with all states)
- Token decisions (spacing scale: 4/8/12/16/24/32/48. Type scale: 12/14/16/20/24/32px. Color count cap: 5 hue families)
- States checklist (empty, loading, error, success, focus, disabled — all present)
- Migration path: convert nav.blade.php first, then home-aside.blade.php, then post-card.blade.php
- Cutover criteria: all touch targets ≥44px, all contrast ≥4.5:1, skip-link present, 0 dead buttons, error/success states rendered

Anti-patterns to guard against (specific to REDESIGN):
- Porting old `@mobile`/`@desktop` structure under new styling
- Keeping both old and new nav behind a feature flag indefinitely
- Redesigning to follow a trend (frosted glass, neumorphism) rather than the principles above
- Treating the Preserve list as optional — the CSS custom property system and Alpine.js architecture must survive the redesign
```
