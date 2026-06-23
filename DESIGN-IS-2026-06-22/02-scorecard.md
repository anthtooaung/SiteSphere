# Scorecard — SiteSphere Mobile Homepage

## 1. Good design is innovative — Score: 1/3
   Evidence: The mobile homepage uses standard patterns (bottom nav, slide-in sidebar, post card grid) with no novel interaction design. The `@mobile`/`@desktop` server-side detection is an anti-pattern, not an innovation. The filter system with rating/category/tag checkboxes is conventional. No unique affordance distinguishes this from 5+ peer products (Product Hunt, G2, Capterra).
   Justification: Imitates competitor flows (filter sidebar, card grid, bottom nav) with minor variation; no pattern introduced that advances the form.

## 2. Good design makes a product useful — Score: 1/3
   Evidence: ~333 interactive elements on a single page (01-evidence.md: Structural). The primary task (browse filtered websites) requires opening a 75%-width sidebar overlay, checking filters, then scrolling past a large "Selected Filters" section to see results. Dead buttons exist when `$postId` is null (post-card.blade.php:170-184). Footer links "Latest Reviews" and "Top Rated Software" both link to the same URL with no sort params (footer.blade.php:40,46). 9 touch targets under 44x44px make primary actions difficult to tap.
   Justification: Primary task completes but requires unnecessary detours (sidebar overlay, scrolling past filter display, encountering dead buttons and undersized touch targets).

## 3. Good design is aesthetic — Score: 1/3
   Evidence: Spacing scale has 32 unique values with no consistent grid (01-evidence.md: Visual). Type scale has 25+ sizes, many just 1-2px apart (0.66rem, 0.72rem, 0.76rem, 0.78rem). 9 hue families used. Global CSS reset at homepage.css:2266-2294 suppresses hover/focus/active states on all buttons, creating a flat, undifferentiated feel. The `color-mix()` system is technically sophisticated but produces near-identical opacity variants.
   Justification: 3-5 spacing inconsistencies, orphan font sizes, and a massive state-suppression block constitute active visual noise rather than a coherent system.

## 4. Good design makes a product understandable — Score: 1/3
   Evidence: 7 label→behavior mismatches found (01-evidence.md: Copy & Honesty). "Create Post" vs "Write review" for the same action. "Setting" (singular) for a 4-item section. "Alerts" vs "Notifications" for the same data. "Best match" sort with no explanation. Jargon in report modal ("Intellectual Property", "Nudity / Obscenity", "Legal & Integrity"). 17 repeated patterns create cognitive overhead. Sidebar section headers use `<div @click>` — not keyboard operable.
   Justification: 2-3 controls unclear; jargon present in primary flows; label inconsistencies across viewports.

## 5. Good design is unobtrusive — Score: 2/3
   Evidence: The main content area is clean — post cards present content without decorative chrome. The filter sidebar is contextual (hidden until triggered). The "Selected Filters" section (home.blade.php:39-106) takes significant vertical space before results but is functional. Footer decorative animations are hidden on mobile (`hidden md:block`). Bottom nav is unobtrusive with frosted glass effect.
   Justification: Chrome visible but quiet — the filter display section is slightly heavy but content remains the figure.

## 6. Good design is honest — Score: 1/3
   Evidence: Footer tagline claims "global community of trusted website and honest tool reviewers" with no verification system (footer.blade.php:20). "simple, clear filters" is a subjective UI quality claim (home.blade.php:23). `authAlert()` uses `icon: 'warning'` for normal auth gates — confirmshaming-adjacent (post-card.blade.php:82). Dead "Save Post" and "Report" buttons appear functional but do nothing (post-card.blade.php:170-184). Footer links promise "Latest Reviews" and "Top Rated Software" but deliver default-sorted homepage.
   Justification: 2+ inflations AND dead buttons that promise actions they don't deliver constitute dishonest design.

## 7. Good design is long-lasting — Score: 2/3
   Evidence: CSS custom properties (`--background-color`, `--text-color`, `--accent-color`) provide theming longevity. The `color-mix()` approach is modern CSS. However, the `@mobile`/`@desktop` server-side detection is already an outdated pattern. Flowbite dependency ties the project to a specific UI library version. The massive CSS file (3184 lines homepage.css, 2835 lines nav.css) with duplicated media queries is fragile.
   Justification: 1 dated marker (server-side device detection) but the CSS custom property system would read as current in 3 years.

## 8. Good design is thorough down to the last detail — Score: 0/3
   Evidence: Error state: MISSING. Success state: MISSING. Focus rings suppressed on checkboxes and most buttons (homepage.css:2231-2294). 9 touch targets under 44x44px (01-evidence.md: Accessibility). No skip-link. Remove filter buttons are 12x12px `<span>` elements. Sidebar section headers are non-focusable `<div>` elements. Contrast failures on timestamps (~3.7:1), footer copyright (~3.3:1), and brand accent (~4.0:1). 2 dead props/variables. Skeleton loading state exists but error/success states are completely absent.
   Justification: 4+ states missing or default-browser (error, success states MISSING; focus state actively suppressed; touch targets severely undersized).

## 9. Good design is environmentally friendly — Score: 2/3
   Evidence: ~50KB gzipped JS (under 100KB threshold). SweetAlert2 lazy-loaded from CDN. No auto-playing animations visible on mobile idle (footer decorative animations hidden). Google Fonts loaded with `display=swap`. However: 31 hidden overlay/modal elements in DOM for 10 cards, 5 inline `<script>` blocks, 7-9 network requests minimum, ~3184-line CSS file with significant duplication.
   Justification: Under 500KB JS, motion gated (no idle animations on mobile), but DOM bloat from hidden elements and massive CSS duplication waste resources.

## 10. Good design is as little design as possible — Score: 1/3
   Evidence: 17 repeated patterns (01-evidence.md: Structural). Post card has 3+ variants each of Save, Report, and Review buttons (auth/guest/no-postId). Filter UI is duplicated entirely for dropdown vs sidebar modes. 53 distinct Blade components. The `@mobile`/`@desktop` split renders separate HTML trees. Each post card embeds a full report modal with 10 radio options in the DOM even when hidden. Global CSS reset suppresses states rather than designing them intentionally.
   Justification: 3-5 removable elements (duplicated filter UIs, dead button variants, hidden report modals per card, decorative footer SVGs).

---

## Total: 13/30
