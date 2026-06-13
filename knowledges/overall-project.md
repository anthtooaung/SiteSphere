# SiteSphere: Master Project Documentation

## 1. Project Overview & Identity
SiteSphere is a modern development resource discovery platform built with **Laravel 13**, **Tailwind CSS**, and **Alpine.js**. It focuses on providing a premium, customizable user experience for reviewing and managing web resources.

### Core Technology Stack
- **Backend:** PHP 8.3+, Laravel 13, Socialite (OAuth)
- **Frontend:** Tailwind CSS (v3/v4), Alpine.js, Flowbite, Blade Components
- **Persistence:** SQLite (Local) / MySQL (Production), Redis (Caching/Sessions)
- **Theming:** Dynamic CSS Variables (`--accent-color`, etc.) stored in the database.

---

## 2. Design System & UI/UX Standards
All UI development must adhere to the standardized "Enterprise-Grade" design language established in recent audits.

### Core Primitives
- **Border Radii:** Standardized to `8px` for cards, buttons, and inputs. Circular treatment for avatars and pills only.
- **Typography:** Primary: `Figtree/Inter`. Desktop Titles: `800-850` weight. Small labels: `11px` uppercase with `0.08em` letter spacing.
- **Shadows:** Default: None or subtle (`0 4px 14px rgba(15, 23, 42, 0.06)`). Hover: `0 10px 24px rgba(15, 23, 42, 0.10)`.
- **Interaction:** Hover/Focus states must be high-visibility. Primary buttons use accent color with brightness filters on hover.

### Layout Rules
- **Sidebar Flexibility:** Must support `left`, `right`, `top`, and `bottom` positions via `$menuBarLocation`.
- **Responsive Breakpoints:** 
    - **Desktop (>1024px):** Fixed navigation (72px-85px), stable sidebar, 24px padding.
    - **Tablet (640px - 1024px):** Mobile card layout for tables to avoid horizontal scrolling.
    - **Mobile (<640px):** Bottom navigation bar + search strip.

---

## 3. Implementation Status & Roadmap

### ✅ Completed Milestones
- **Authentication:** Login/Registration flows refined with consistent shell design.
- **User Management:** High-fidelity filter system, AJAX table loading, and skeleton states.
- **Reports System:** Soft-deletes, togglable read/unread status, and integrated resource navigation.
- **Performance:** N+1 query fixes in Profile Detail, Home, and Post Detail; View Composer caching.
- **Theming:** Full propagation of custom accent colors and sidebar positions across all dashboard views.

### ⏳ Pending & Continuous Tasks
- **Home Page:** Transitioning to full server-side pagination/infinite scroll beyond initial 24 posts.
- **Dashboard:** Implementation of more functional widgets and stats cards.
- **Monitoring:** Deployment-ready error tracking (Sentry) and S3 storage configuration for production.

---

## 4. Performance & Backend Mandates
- **N+1 Prevention:** Always use `with()`, `loadCount()`, and `withAvg()` to consolidate queries.
- **AJAX Interactions:** All filter/search operations on dashboard pages must use Alpine.js `fetch()` with visible skeleton loaders (`pulse` animation).
- **Caching:** Per-user notification counts and global view data must be cached to prevent redundant DB hits on every render.
- **Soft Deletes:** Critical for `Posts` and `Comments` to maintain audit trails.

---

## 5. Deployment & Persistence
- **Docker-Ready:** Multi-stage Dockerfile builds assets and triggers migrations.
- **Persistence:** Production requires S3-compatible storage for avatars/uploads to prevent data loss in ephemeral containers.
- **Database:** Optimized for remote managed MySQL (Aiven) with SSL/TLS enforcement.

---

## 6. Developer Operational Rules
- **Token Limit:** If token usage reaches **20%**, immediately stop work and update `knowledges/remainTask.md`.
- **UI Consistency:** NEVER hardcode colors or positions. Always reference `$menuBarLocation`, `$toastPosition`, and `--accent-color`.
- **Testing:** New features MUST include Feature/Unit tests (standard: `php artisan test`).
- **Pint:** Run `vendor/bin/pint --dirty` before every submission to maintain clean code standards.

---
*Refer to individual files in `knowledges/` for deep-dive technical specs on specific modules.*
