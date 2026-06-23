# Verdict — SiteSphere Post Detail & Post Card

## REDESIGN

The SiteSphere post detail page and post card component scored 15/30 with critical gaps across multiple principles. Error and success states are completely missing, focus styles are actively suppressed on multiple controls (outline removed without replacement), and contrast fails on helper text tokens (`var(--faint)` ~2.8:1, `var(--muted)` ~4.0:1). The design token system in post-detail.css (32 custom properties) is contradicted by 26 ad-hoc spacing values, 24 font sizes, and 38 inline styles. Report modals are duplicated across 3 components with inconsistent options. Save/Report buttons have 3 variants each creating unnecessary complexity.

### Top 5 Highest-Leverage Moves

1. **Principle #8 — Thorough**: Add error, success, and loading states to post-detail.css. Fix inconsistent focus styles — buttons at comments.blade.php:181, 203, 218, 239, 276, 288, 392 suppress outline without replacement; add `focus-visible:ring-2` consistently. Fix contrast on `var(--faint)` (~2.8:1) and `var(--muted)` (~4.0:1) for metadata text. Add skip-link. Label 3 unlabeled textareas (comments:51, post-detail:594, comments:311). Use `<time>` elements for dates.
   Evidence: post-detail.css states checklist, comments.blade.php focus:outline-none instances, post-detail.css var(--faint)/var(--muted) contrast ratios

2. **Principle #10 — As little design as possible**: Consolidate report modals into a single shared component with configurable options (post vs comment currently have different radio groups). Consolidate Save/Report button variants using Alpine.js conditionals instead of 3 separate Blade blocks. Remove dead `category` prop (post-card:5), unused `selectedRating` and `setRating()` (post-card:26, :63). Reduce repeated patterns from 12/15 to under 5.
   Evidence: post-card.blade.php:131-203 (3 button variants), report-modal.blade.php vs comments.blade.php report modal duplication

3. **Principle #3 — Aesthetic**: Adopt a consistent spacing scale (4px or 8px grid) — replace 26 ad-hoc values. Reduce type scale from 24 to 5-6 sizes. Remove 38 inline styles and use CSS classes. Consolidate ~54 colors into a smaller palette aligned with the 32 custom properties.
   Evidence: post-detail.css spacing array (26 values), type scale (24 values), inline style count (38)

4. **Principle #4 — Understandable**: Replace jargon: "audits" → "reviews" (post-detail:277, :310), "Linked records" → "Related posts" (post-detail:649), "Intellectual Property" → "Copyright Violation" (post-card:409), aria-label "User Reports" → "User Comments" (comments:9). Fix dead "Save Post" button (post-card:175). Standardize report modal options between post and comment.
   Evidence: 7 jargon instances, 2 label-behavior mismatches, report modal inconsistency

5. **Principle #6 — Honest**: Remove inflation "Expert audits and technical reviews from verified contributors." (post-detail:310) — replace with factual description. Fix or remove dead buttons that claim functionality they don't have (post-card:175, :182 when postId is null).
   Evidence: post-detail.blade.php:310, post-card.blade.php:171-183
