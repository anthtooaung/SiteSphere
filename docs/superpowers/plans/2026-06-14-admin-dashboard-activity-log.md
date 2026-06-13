# Admin Dashboard & Activity Log Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Implement a high-fidelity administrative interface for system metrics and activity tracking, utilizing interactive charts, timelines, and a calendar-based feed.

**Architecture:**
- **Backend:** Role-based `DashboardController`, `AdminActivityLogController`, and extended `AuditLogs` model.
- **Frontend:** Alpine.js for interactive SVG charts and calendar date switching. Blade partials for AJAX-loaded activity feeds.
- **UX:** Slate-based design system with 8px radii and semantic category colors.

**Tech Stack:** Laravel 13, Tailwind CSS, Alpine.js, Blade, FontAwesome 6.

---

### Task 1: Data Architecture & Model Extensions

**Files:**
- Create: `database/migrations/2026_06_14_000000_add_category_to_audit_logs_table.php`
- Modify: `app/Models/AuditLogs.php`
- Modify: `database/factories/AuditLogsFactory.php`

- [ ] **Step 1: Create migration to add `category` to `audit_logs`**

```php
// database/migrations/...
public function up(): void
{
    Schema::table('audit_logs', function (Blueprint $table) {
        $table->string('category')->default('system')->after('action');
    });
}
```

- [ ] **Step 2: Add category logic to `AuditLogs` model**

```php
// app/Models/AuditLogs.php
public function getIcon(): string
{
    return match($this->category) {
        'moderation' => 'fa-hammer',
        'success' => 'fa-check-circle',
        'announcement' => 'fa-bullhorn',
        default => 'fa-cog',
    };
}

public function getColor(): string
{
    return match($this->category) {
        'moderation' => '#ef4444',
        'success' => '#10b981',
        'announcement' => '#3b82f6',
        default => '#f59e0b',
    };
}
```

- [ ] **Step 3: Update factory and run migration**

Run: `php artisan migrate`

---

### Task 2: Admin Dashboard Logic & Role-based View

**Files:**
- Modify: `app/Http/Controllers/DashboardController.php`
- Modify: `resources/views/layout/menu/dashboard.blade.php`

- [ ] **Step 1: Update `DashboardController` to fetch admin stats**

```php
// app/Http/Controllers/DashboardController.php
public function __invoke(Request $request): View
{
    $user = $request->user();
    if ($user->role === 'admin') {
        $stats = [
            'totalUsers' => User::count(),
            'totalReviews' => UserPosts::count(),
            'totalReports' => Reports::count(),
        ];
        $recentActivity = AuditLogs::latest()->take(4)->get();
        $topPosts = Posts::withAvg('ratings', 'rating')
            ->orderByDesc('ratings_avg_rating')
            ->take(5)->get();
            
        return view('layout.menu.dashboard-admin', compact('stats', 'recentActivity', 'topPosts'));
    }
    // ... existing user logic
}
```

- [ ] **Step 2: Implement Admin Dashboard View (Donut Chart & KPI Cards)**

Use SVG for the Donut Chart and Sparklines. Implement Alpine.js `x-data="{ activeKpi: null }"` for slice dimming.

---

### Task 3: Activity Log - Route & Controller

**Files:**
- Modify: `routes/web.php`
- Create: `app/Http/Controllers/AdminActivityLogController.php`

- [ ] **Step 1: Define routes**

```php
Route::get('/menu/dashboard/activity-log', [AdminActivityLogController::class, 'index'])->name('admin.activity-log');
Route::get('/api/admin/activity/{date}', [AdminActivityLogController::class, 'show'])->name('admin.activity-date');
```

- [ ] **Step 2: Implement `AdminActivityLogController`**

`index()` should return the monthly view. `show()` should return a Blade partial for a specific date.

---

### Task 4: Activity Log - Calendar & Feed Interactivity

**Files:**
- Create: `resources/views/layout/menu/activity-log.blade.php`
- Create: `resources/views/partials/admin-activity-card.blade.php`

- [ ] **Step 1: Implement Calendar Grid in Blade**

Loop through the days of the month. Check if a date has activities to render the indicator dot.

- [ ] **Step 2: Implement AJAX Date Switching with Alpine.js**

```javascript
// In activity-log.blade.php
function activityLogController() {
    return {
        selectedDate: '{{ now()->toDateString() }}',
        isLoading: false,
        async selectDate(date) {
            this.selectedDate = date;
            this.isLoading = true;
            const response = await fetch(`/api/admin/activity/${date}`);
            document.getElementById('activityFeed').innerHTML = await response.text();
            this.isLoading = false;
        }
    }
}
```

---

### Task 5: Verification & Tests

**Files:**
- Create: `tests/Feature/AdminDashboardTest.php`

- [ ] **Step 1: Write access control and data accuracy tests**

```php
public function test_non_admins_cannot_access_activity_log()
{
    $user = User::factory()->create(['role' => 'user']);
    $this->actingAs($user)->get('/menu/dashboard/activity-log')->assertStatus(403);
}
```

- [ ] **Step 2: Run all tests**

Run: `php artisan test`
Expected: ALL PASS
