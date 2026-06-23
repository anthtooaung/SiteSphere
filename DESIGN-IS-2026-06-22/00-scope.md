# Scope — Dieter Rams Design Audit

## Target
**SiteSphere Homepage — Mobile Design (all surfaces)**

### Surfaces audited
1. **Mobile header** — sticky top bar with brand logo + profile/login button (`nav.blade.php:62-74`, `nav.css:2312-2434`)
2. **Mobile bottom navigation** — fixed bottom bar with Home / Category / Create / Notification / Profile (`nav.blade.php:77-89`, `nav.css:2441-2565`)
3. **Sidebar / filter panel** — slide-in overlay with Rating / Categories / Tags filters (`home-aside.blade.php`, `homepage.css:66-82` mobile rules)
4. **Main content** — content intro, results toolbar, active filters display, post cards grid, pagination (`home.blade.php:19-133`)
5. **Post cards** — title, rating badge, external link, tags scroller, profile tabs, comments/review footer, actions menu, report modal (`post-card.blade.php`)
6. **Footer** — SiteSphere footer with links and social icons (`footer.blade.php`)

### Key files
- `resources/views/layout/home.blade.php` — page layout
- `resources/views/components/layout/nav.blade.php` — navigation (uses `@mobile`/`@desktop`)
- `resources/views/components/layout/home-aside.blade.php` — filter sidebar
- `resources/views/components/layout/post-card.blade.php` — post card component
- `resources/views/components/layout/post-card-skeleton.blade.php` — loading skeleton
- `resources/views/components/layout/footer.blade.php` — footer
- `resources/css/homepage.css` — homepage styles (3184 lines)
- `resources/css/nav.css` — navigation styles (2835 lines)

### Primary user
A visitor or registered user browsing website/tool recommendations on a mobile device (≤480px viewport).

### Primary task
Discover and evaluate website recommendations by browsing filtered post cards, reading reviews, and navigating to external sites.

### Constraints
- Laravel 11 + Blade templates + Tailwind CSS + Alpine.js
- Uses `jenssegers/agent` for server-side `@mobile`/`@desktop` detection (known anti-pattern)
- CSS custom properties for theming (`--background-color`, `--text-color`, `--accent-color`, `--font-family`)
- Dark mode support via CSS custom properties

### Known architectural issue
The project uses `@mobile`/`@desktop` Blade directives to render completely different HTML for mobile vs desktop. This is a server-side User-Agent sniffing approach that breaks on resize, tablets, and cached pages. This is documented in `memory/project-mobile-anti-pattern.md`.
