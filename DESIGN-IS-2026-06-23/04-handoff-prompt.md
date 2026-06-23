# Handoff — /make-plan Prompt

```
/make-plan Redesign SiteSphere post detail page and post card component. Current design failed audit at 15/30 with critical gaps in principles #3 (aesthetic), #4 (understandable), #8 (thorough), #10 (as little design as possible).

Verdict paragraph (quoted from 03-verdict.md):
> The SiteSphere post detail page and post card component scored 15/30 with critical gaps across multiple principles. Error and success states are completely missing, focus styles are actively suppressed on multiple controls (outline removed without replacement), and contrast fails on helper text tokens (var(--faint) ~2.8:1, var(--muted) ~4.0:1). The design token system in post-detail.css (32 custom properties) is contradicted by 26 ad-hoc spacing values, 24 font sizes, and 38 inline styles. Report modals are duplicated across 3 components with inconsistent options. Save/Report buttons have 3 variants each creating unnecessary complexity.

Why redesign and not refine: Total score is 15/30 (below 20 threshold). Multiple load-bearing principles scored 1 — thoroughness (missing error/success states, suppressed focus rings, contrast failures), aesthetics (26 ad-hoc spacing values contradicting the token system), and understandability (7 jargon instances, dead buttons, inconsistent terminology). The structural duplication (report modals x3, button variants x3) requires architectural changes, not surface-level fixes.

Preserve from current design (MUST be non-empty):
- CSS custom property system (32 tokens: --ink, --paper, --accent, --muted, --faint, etc.) — post-detail.css:12-50
- Deposition tab pattern with per-user panels — post-detail.blade.php:305-615
- Expandable text with ResizeObserver — post-detail.js:68-95, post-detail.css:1193-1220
- Alpine.js + vanilla JS architecture (post-detail.js has 6 clean modules)
- Star rating SVG component pattern (reusable, but needs deduplication)
- CSS color-mix() opacity pattern for consistent muted variants

Discard (MUST be non-empty — name the structural patterns causing the failures):
- Triplicated Save/Report button variants (auth+postId / auth no postId / guest) — post-card.blade.php:131-203. Caused failure on #10 (as little design as possible) and #6 (honest — dead buttons).
- Duplicated report modals with inconsistent options — report-modal.blade.php vs comments.blade.php:368-476. Caused failure on #10 and #4 (understandable — different options for same action).
- 38 inline styles bypassing the token system — post-detail.blade.php (17), comments.blade.php (20). Caused failure on #3 (aesthetic) and #9 (environmentally friendly).
- 26 ad-hoc spacing values and 24 font sizes — post-detail.css. Caused failure on #3 (aesthetic).
- Inconsistent focus:outline-none without replacement — comments.blade.php:181, 203, 218, 239, 276, 288, 392. Caused failure on #8 (thorough).

Top 5 moves from the audit (verbatim):
1. Principle #8 — Thorough: Add error, success, and loading states to post-detail.css. Fix inconsistent focus styles — buttons at comments.blade.php:181, 203, 218, 239, 276, 288, 392 suppress outline without replacement; add focus-visible:ring-2 consistently. Fix contrast on var(--faint) (~2.8:1) and var(--muted) (~4.0:1) for metadata text. Add skip-link. Label 3 unlabeled textareas (comments:51, post-detail:594, comments:311). Use <time> elements for dates.
2. Principle #10 — As little design as possible: Consolidate report modals into a single shared component with configurable options. Consolidate Save/Report button variants using Alpine.js conditionals instead of 3 separate Blade blocks. Remove dead category prop (post-card:5), unused selectedRating and setRating() (post-card:26, :63). Reduce repeated patterns from 12/15 to under 5.
3. Principle #3 — Aesthetic: Adopt a consistent spacing scale (4px or 8px grid) — replace 26 ad-hoc values. Reduce type scale from 24 to 5-6 sizes. Remove 38 inline styles and use CSS classes. Consolidate ~54 colors into a smaller palette aligned with the 32 custom properties.
4. Principle #4 — Understandable: Replace jargon: "audits" → "reviews" (post-detail:277, :310), "Linked records" → "Related posts" (post-detail:649), "Intellectual Property" → "Copyright Violation" (post-card:409), aria-label "User Reports" → "User Comments" (comments:9). Fix dead "Save Post" button (post-card:175). Standardize report modal options between post and comment.
5. Principle #6 — Honest: Remove inflation "Expert audits and technical reviews from verified contributors." (post-detail:310) — replace with factual description. Fix or remove dead buttons that claim functionality they don't have (post-card:175, :182 when postId is null).

Redesign principles in priority order:
1. Thorough (#8) — Every state (empty, loading, error, success, focus, disabled) present and considered. All focus rings visible. All contrast ≥4.5:1. Skip-link present. All form controls labeled. <time> elements for dates.
2. As little design as possible (#10) — Every element earns its place. Single shared report modal. Single button pattern with Alpine conditionals. No unused props or vars. Under 5 repeated patterns.
3. Aesthetic (#3) — Spacing on 4px grid. Type scale of 5-6 sizes. Zero inline styles. Color palette ≤20 tokens. Single CSS file for post-detail under 1500 lines.
4. Understandable (#4) — A first-time user names every primary control correctly. No jargon. Consistent terminology across viewports. Dead buttons eliminated. Report modal options consistent.
5. Honest (#6) — Every label maps 1:1 to behavior. No inflated claims. No dead buttons. Footer and metadata text is factual.

Deliverables for the plan:
- New component architecture: single shared report modal, single button pattern with Alpine conditionals
- Token consolidation: spacing scale (4/8/12/16/24/32/48), type scale (12/14/16/20/24/32px), color palette (≤20 tokens)
- States checklist: empty, loading, error, success, focus, disabled — all present in post-detail.css
- Accessibility fixes: skip-link, labeled textareas, <time> elements, consistent focus rings, contrast ≥4.5:1
- Copy fixes: jargon replacement list, inflation removal, dead button elimination
- Migration path: consolidate post-detail.css first, then post-card.blade.php button variants, then shared report modal
- Cutover criteria: all contrast ≥4.5:1, skip-link present, 0 dead buttons, error/success states rendered, 0 inline styles

Anti-patterns to guard against (specific to REDESIGN):
- Porting old inline styles under new class names without consolidating the token system
- Keeping both old and new report modals behind a feature flag indefinitely
- Redesigning to follow a trend (glassmorphism, neumorphism) rather than the principles above
- Treating the Preserve list as optional — the CSS custom property system and Alpine.js architecture must survive the redesign
- Adding new abstractions where a direct change suffices (e.g., a report modal factory instead of a single configurable component)
```
