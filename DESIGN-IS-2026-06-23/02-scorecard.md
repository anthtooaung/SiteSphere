# Scorecard — SiteSphere Post Detail & Post Card

## 1. Good design is innovative — Score: 1/3
**Evidence:** Post detail uses standard patterns: tabbed depositions, expandable text (ResizeObserver), star rating system, horizontal carousel, report modals. None of these are novel in the review/recommendation space. The deposition tab pattern with per-user panels is a minor variation on standard tabs.
**Justification:** Score 1 (not 0) because the deposition-per-contributor layout with expandable descriptions is a reasonable adaptation, though not innovative. Score 1 (not 2) because no pattern advances beyond what peer products (G2, Capterra, Trustpilot) already ship.

## 2. Good design makes a product useful — Score: 2/3
**Evidence:** Primary task (read post → evaluate credibility → comment → navigate to URL) completes. Verdict strip with rating breakdown, deposition tabs, comments composer, related posts carousel all support the task. Dead "Save Post" button (post-card.blade.php:175) when `$postId` is null adds a decoy action. 3-variant button pattern (auth+postId / auth no postId / guest) adds cognitive load.
**Justification:** Score 2 (not 3) because dead buttons and triplicated action patterns add unnecessary steps/complexity. Score 2 (not 1) because the primary flow is functional and the core content is accessible.

## 3. Good design is aesthetic — Score: 1/3
**Evidence:** post-detail.css defines 32 custom properties (a design token system), but 26 distinct spacing values, 24 font sizes, and ~54 distinct colors undermine it. No consistent spacing scale (values include 1, 3, 5, 7, 9, 11, 13 px — odd numbers suggest ad-hoc choices). 38 inline styles bypass the token system entirely.
**Justification:** Score 1 because the token system exists but is contradicted by 26 ad-hoc spacing values, 24 font sizes, and 38 inline styles — 3+ inconsistencies across the surface.

## 4. Good design makes a product understandable — Score: 1/3
**Evidence:** 7 jargon instances ("audits", "verified contributors", "Linked records", "Intellectual Property"). 2 label-behavior mismatches (dead Save Post button, aria-label "User Reports" on comments section). Inconsistent terminology: "audits" vs "contributions" vs "reports" across surfaces. Report modal options differ between post and comment without explanation.
**Justification:** Score 1 because 2–3 controls are unclear (jargon labels) and terminology is inconsistent across the audited surfaces.

## 5. Good design is unobtrusive — Score: 2/3
**Evidence:** Post detail page is content-focused: verdict strip, deposition panels, and comments section prioritize content over chrome. Three-dot menus are compact. The deposition tab navigation is clean. However, 38 inline styles and duplicated report modals (same structure in 3 components) add structural noise.
**Justification:** Score 2 because chrome is visible but quiet — content dominates the page. The inline styles and modal duplication are noise but don't compete with content for attention.

## 6. Good design is honest — Score: 2/3
**Evidence:** 1 inflation: "Expert audits and technical reviews from verified contributors." (post-detail.blade.php:310) — no verification mechanism is visible. Dead "Save Post" button (post-card.blade.php:175) claims functionality it doesn't have. No dark patterns found. Report modal headings are accurate.
**Justification:** Score 2 because there is 1 minor inflation and 1 dead button, but no deceptive flows or forced continuity.

## 7. Good design is long-lasting — Score: 2/3
**Evidence:** CSS custom property system (32 tokens) is forward-thinking. Alpine.js + vanilla JS stack is modern. `color-mix()` usage is contemporary CSS. No skeuomorphism, fad gradients, or trend typography. However, 38 inline styles and duplicated HTML blocks suggest structural debt that will age poorly.
**Justification:** Score 2 because the visual language has no dated trend markers (1 minor concern: inline styles as structural debt), but would read as current 3 years from now.

## 8. Good design is thorough down to the last detail — Score: 1/3
**Evidence:** Missing states: :active, :error, :success, [data-loading], [data-error], [data-empty], .error, .empty, .success. Focus styles inconsistent: some buttons get `focus-visible:ring-2`, others get `focus:outline-none` with no replacement (comments.blade.php:181, 203, 218, 239, 276, 288, 392). 3 unlabeled textareas. No skip-link. No `<time>` elements for dates. Contrast fails on `var(--faint)` (~2.8:1) and `var(--muted)` (~4.0:1) for metadata text.
**Justification:** Score 1 because 2–3 core states are missing (error, success, active), focus is actively suppressed on multiple controls, and contrast fails on helper text tokens.

## 9. Good design is environmentally friendly — Score: 2/3
**Evidence:** ~20.3 KB JS is well under 500KB. ~11-12 network requests is reasonable. Dark mode supported via CSS custom properties. However, 50+ CSS transition properties and 7 Alpine transition groups run on the page. No `prefers-reduced-motion` check found. 38 inline styles increase HTML payload. No lazy loading on 5 `<img>` tags.
**Justification:** Score 2 because JS is under 500KB and motion is mostly gated on hover/focus, but no prefers-reduced-motion respect and no lazy loading.

## 10. Good design is as little design as possible — Score: 1/3
**Evidence:** Report modals duplicated in 3 components (post-detail x2, post-card, comments). Save/Report buttons have 3 variants each (auth+postId, auth no postId, guest). Star SVG block repeated 6+ times. 12 repeated patterns in post detail alone. 15 repeated patterns in comments. Dead `category` prop in post-card. Unused `selectedRating` and `setRating()` in post-card.
**Justification:** Score 1 because 3–5 removable elements exist (duplicated modals, triplicated buttons, unused props/vars) — clear duplication of affordances.

---

## Total: 15/30
