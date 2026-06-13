# Home Page Server-Side Pagination & Infinite Scroll Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Transition the home page from client-side pagination (limited to 24 posts) to full server-side pagination with "Load More" / Infinite Scroll functionality, moving filtering and sorting to the backend for scalability.

**Architecture:**
- **Backend:** `HomeController` will handle query parameters (`page`, `category`, `tags`, `rating`, `sort`) and use Laravel's `paginate()` method.
- **Frontend:** Refactor `homepage.js` to move state management to Alpine.js. Use AJAX to fetch next batches of posts and append them to the grid.
- **UX:** Maintain existing design while adding skeleton loaders and a persistent "Load More" trigger.

**Tech Stack:** Laravel 13, Tailwind CSS, Alpine.js, Blade.

---

### Task 1: Backend Refactor - HomeController

**Files:**
- Modify: `app/Http/Controllers/HomeController.php`

- [x] **Step 1: Update `HomeController` to handle filters and pagination**

- [x] **Step 2: Update `HomeController` to return JSON for AJAX requests**

- [x] **Step 3: Commit backend changes**

### Task 2: Frontend Refactor - Blade Template

**Files:**
- Modify: `resources/views/layout/home.blade.php`
- Create: `resources/views/partials/home-posts.blade.php`

- [x] **Step 1: Create partial for post cards**

- [x] **Step 2: Update home.blade.php with Alpine.js structure and skeleton container**

- [x] **Step 3: Commit view changes**

### Task 3: JavaScript Refactor - homepage.js to Alpine.js

**Files:**
- Modify: `resources/js/homepage.js`

- [x] **Step 1: Implement `homeController` Alpine.js logic**

- [x] **Step 2: Commit JS changes**

### Task 4: Final UX Polish - Skeleton Loaders & Styles

**Files:**
- Modify: `resources/css/homepage.css`
- Create: `resources/views/components/layout/post-card-skeleton.blade.php`

- [x] **Step 1: Create skeleton component**

- [x] **Step 2: Add styles for load-more button and skeletons**

- [x] **Step 3: Commit UX improvements**

### Task 5: Verification & Tests

**Files:**
- Modify: `tests/Feature/HomePageTest.php`

- [x] **Step 1: Update tests for AJAX pagination**

- [x] **Step 2: Run all tests**

- [x] **Step 3: Final commit**
