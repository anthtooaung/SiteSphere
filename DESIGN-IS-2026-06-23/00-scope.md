# Scope — Dieter Rams Design Audit

## Target
**SiteSphere — Post Detail Page & Post Card Component (Mobile Design)**

### Surfaces audited
1. **Post Detail Page** — full post view with masthead, verdict strip, depositions tabs, comments/reviews, related posts carousel (`post-detail.blade.php`)
2. **Post Card Component** — reusable card used on homepage, saved-posts, upload preview (`post-card.blade.php`)
3. **Post Card Skeleton** — loading state placeholder (`post-card-skeleton.blade.php`)
4. **Comments Section** — embedded review/comment area with composer and helpful votes (`comments.blade.php`)

### Key files
- `resources/views/layout/post-detail.blade.php` — post detail page
- `resources/views/components/layout/post-card.blade.php` — post card component
- `resources/views/components/layout/post-card-skeleton.blade.php` — loading skeleton
- `resources/views/components/layout/comments.blade.php` — comments section
- `resources/views/partials/home-posts.blade.php` — renders post cards in loops
- `resources/css/post-detail.css` — post detail styles
- `resources/css/homepage.css` — post card styles (lines 1616+, 2372+, 2565+, 3125+)
- `resources/css/nav.css` — post detail scrollbar overrides (lines 2682-2730)
- `resources/js/post-detail.js` — post detail interactivity (6 modules)
- `app/Http/Controllers/PostsController.php` — show() at line 290

### Primary user
A visitor or registered user evaluating a specific website/tool recommendation on a mobile device (≤480px viewport).

### Primary task
Read the full post review, evaluate credibility via depositions and ratings, read/write comments, and navigate to the external site or related posts.

### Constraints
- Laravel 11 + Blade templates + Tailwind CSS + Alpine.js
- Uses `jenssegers/agent` for server-side `@mobile`/`@desktop` detection (known anti-pattern from previous audit)
- CSS custom properties for theming
- Dark mode support via CSS custom properties
- Post card is reused across 4+ surfaces (homepage, saved-posts, upload preview, post detail related)

### Known architectural issue (carried from homepage audit)
The project uses `@mobile`/`@desktop` Blade directives to render completely different HTML for mobile vs desktop. This is a server-side User-Agent sniffing approach that breaks on resize, tablets, and cached pages. This is documented in `memory/project-mobile-anti-pattern.md`.
