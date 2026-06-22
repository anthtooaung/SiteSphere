# Report Backend Logic — Blame & Improvements

> **Date:** 2026-06-22
> **Purpose:** Compare SiteSphere's report system against industry best practices (Meta, Reddit, Twitter/X) and identify what's wrong, what's missing, and how to fix it.

---

## Table of Contents

1. [Architecture Blame](#1-architecture-blame)
2. [Database Schema Blame](#2-database-schema-blame)
3. [Workflow State Blame](#3-workflow-state-blame)
4. [Rate Limiting Blame](#4-rate-limiting-blame)
5. [Controller Logic Blame](#5-controller-logic-blame)
6. [Authorization Blame](#6-authorization-blame)
7. [Notification Blame](#7-notification-blame)
8. [Admin Dashboard Blame](#8-admin-dashboard-blame)
9. [Missing Features](#9-missing-features)
10. [Priority Fix List](#10-priority-fix-list)

---

## 1. Architecture Blame

### What Industry Does
Major platforms use a **pipeline architecture**:
```
Ingestion → Pre-processing → Classification → Triage/Queue → Human Review → Action → Appeals
```
- Event-driven ingestion (Kafka, SQS) decouples report submission from processing
- ML-based auto-classification handles 95% of cases (Meta)
- Confidence-based routing: high-confidence → auto-action, low-confidence → human review
- Dedicated service classes separate business logic from controllers

### What SiteSphere Does
All business logic lives **directly in controllers**. No service layer, no event system, no queue.

**Blame:**
- `ReportsController::store()` mixes validation, creation, and notification in one method
- `AdminReportsController::markRead()` mixes authorization, state change, and audit logging inline
- No separation of concerns — adding new report types means duplicating controller code
- No async processing — notification creation blocks the HTTP response

### How to Fix
```
app/
├── Services/
│   └── ReportService.php          # All report business logic
├── Events/
│   ├── ReportCreated.php          # Dispatched after report submission
│   └── ReportStatusChanged.php    # Dispatched on read/unread/resolve
├── Listeners/
│   ├── NotifyAdminsOfReport.php   # Async notification creation
│   └── LogReportAudit.php         # Async audit logging
└── Jobs/
    └── AutoClassifyReport.php     # Future: ML classification
```

---

## 2. Database Schema Blame

### What Industry Does
```sql
-- Proper polymorphic with morphMap
reportable_type VARCHAR  -- 'post', 'comment', 'user'
reportable_id   BIGINT

-- Multi-state status
status ENUM('new','pending_review','investigating','escalated',
            'resolved_action_taken','resolved_no_action','appealed','closed')

-- Rich metadata
severity_score DECIMAL(3,2)
reporter_trust_score DECIMAL(3,2)
resolved_at TIMESTAMP
closed_at TIMESTAMP
```

### What SiteSphere Does
```sql
-- Manual enum-based polymorphic
target_name ENUM('users','posts','comments','user_posts')
target_id   INT
reason      LONGTEXT
admin_read  BOOLEAN  -- THIS IS THE ENTIRE STATUS SYSTEM
```

**Blame:**
- **`admin_read` boolean is not a status system.** A two-state toggle (read/unread) cannot represent "investigating", "resolved", "escalated", or "dismissed". This is the single biggest design flaw.
- **No `resolved_at` or `closed_at` timestamp.** You cannot measure resolution time or track SLAs.
- **No severity score.** All reports are treated equally — a spam report and a CSAM report sit in the same queue.
- **`reason` is a LONGTEXT blob.** Industry uses structured categories (spam, abuse, harassment, hate_speech, copyright) as ENUM, with `reason` as optional free text. Your system concatenates category + details into one field, making filtering and analytics impossible.
- **No `report_evidence` table.** Users cannot attach screenshots or supporting files.
- **No `report_status_history` table.** You only have `audit_logs`, which is a generic log — not a structured status transition trail.
- **`target_name` enum extended to `user_posts` but never used.** Dead migration, technical debt.
- **`UpdateReportsRequest` always returns `false` for authorization.** Dead code.

### How to Fix (Migration)
```php
// Add proper status column
Schema::table('reports', function (Blueprint $table) {
    $table->string('status')->default('new')->after('reason');
    // new, pending_review, investigating, resolved_action_taken,
    // resolved_no_action, dismissed, closed
    $table->string('category')->nullable()->after('reason');
    // spam, abuse, harassment, hate_speech, illegal, other
    $table->decimal('severity_score', 3, 2)->default(0.5)->after('status');
    $table->timestamp('resolved_at')->nullable()->after('admin_read');
    $table->timestamp('closed_at')->nullable()->after('resolved_at');
    $table->dropColumn('admin_read'); // Replace with status
});

// Separate status history table
Schema::create('report_status_history', function (Blueprint $table) {
    $table->id();
    $table->foreignId('report_id')->constrained()->cascadeOnDelete();
    $table->string('old_status');
    $table->string('new_status');
    $table->foreignId('changed_by')->constrained('users');
    $table->string('reason')->nullable();
    $table->timestamps();
});
```

---

## 3. Workflow State Blame

### What Industry Does
```
[NEW] → [PENDING_REVIEW] → [INVESTIGATING] → [RESOLVED_ACTION_TAKEN]
  ↓            ↓                   ↓
[DISMISSED] ← ← ← ← ← ← ← ← [RESOLVED_NO_ACTION]
                                          ↓
                                    [APPEALED] → [CLOSED]
```
With auto-escalation rules:
- Reports pending > 48h → escalate
- Reports with > 10 community votes → priority queue
- Reports from trusted reporters → fast-track

### What SiteSphere Does
```
[UNREAD] ←→ [READ] → [DELETED]
```

**Blame:**
- **Two states is not a workflow.** You cannot answer: "How many reports were resolved this week?" or "What's the average time to resolution?" or "Which reports are still being investigated?"
- **No concept of "resolved".** Admin either reads or deletes. There's no way to mark a report as "reviewed, no action needed" vs "reviewed, content removed".
- **No auto-escalation.** Reports sit unread forever until an admin happens to check.
- **No appeal mechanism.** If a user's content is removed based on a report, they have no way to contest the decision.
- **Delete is the only terminal state.** Soft-deleting a report is not the same as resolving it. You're mixing "handled" with "removed from view".

### How to Fix
```php
// In Reports model
const STATUS_NEW = 'new';
const STATUS_PENDING = 'pending_review';
const STATUS_INVESTIGATING = 'investigating';
const STATUS_RESOLVED_ACTION = 'resolved_action_taken';
const STATUS_RESOLVED_NO_ACTION = 'resolved_no_action';
const STATUS_DISMISSED = 'dismissed';
const STATUS_CLOSED = 'closed';

// Valid transitions
const TRANSITIONS = [
    'new'                    => ['pending_review', 'dismissed'],
    'pending_review'         => ['investigating', 'resolved_no_action', 'dismissed'],
    'investigating'         => ['resolved_action_taken', 'resolved_no_action'],
    'resolved_action_taken' => ['closed'],
    'resolved_no_action'    => ['closed'],
    'dismissed'             => ['closed'],
];
```

---

## 4. Rate Limiting Blame

### What Industry Does
- **Per-user limits:** 10 reports/day, 30 reports/week
- **Per-IP limits:** 100 reports/hour
- **Per-content limits:** Max 50 reports before auto-queue
- **Duplicate detection:** Same reporter + same content = block
- **Reporter trust scoring:** Trusted users get higher limits

### What SiteSphere Does
**Nothing.** Zero rate limiting on report submission.

**Blame:**
- **A single user can spam unlimited reports.** No throttling whatsoever.
- **No duplicate report detection.** A user can report the same post 100 times.
- **No CAPTCHA or bot protection.** Automated report flooding is trivially easy.
- **No per-user report tracking.** You cannot identify serial false reporters.
- **Admin notifications are created for every single report.** A spam attack would flood every admin's notification inbox.

### How to Fix
```php
// In ReportsController::store()
// 1. Rate limit middleware
Route::middleware('throttle:10,1440')->group(function () { // 10 per day
    Route::post('/posts/{post}/report', [ReportsController::class, 'store']);
});

// 2. Duplicate check in store method
$existing = Reports::where('user_id', auth()->id())
    ->where('target_name', 'posts')
    ->where('target_id', $post->id)
    ->exists();

if ($existing) {
    return back()->with('error', 'You have already reported this content.');
}

// 3. Throttle notifications (batch, don't spam)
// Instead of creating N notifications for N admins,
// create one notification per admin max per hour
```

---

## 5. Controller Logic Blame

### ReportsController

**Blame:**
- `store()` concatenates `reason` and `details` into one string: `$reason = $request->reason; if ($details) { $reason .= " - Details: " . $details; }`. This destroys structured data. You can never query "all spam reports" because the reason field is freeform text with appended details.
- `storeForComment()` validates inline instead of using a FormRequest like `store()` does. Inconsistent validation patterns.
- `show()`, `edit()`, `update()`, `destroy()` are empty stubs. Dead code cluttering the controller.
- No check for reporting own content. A user can report their own post.
- No check for reporting soft-deleted content. A user can report content that's already been removed.

### AdminReportsController

**Blame:**
- `index()` runs **three separate paginated queries** (posts, comments, users) every page load. This is N+1 territory and will not scale. Should be a single query with `target_name` filter.
- `open()` method does too many things: marks as read, logs audit, resolves target type, redirects. Should be split.
- `applySearch()`, `applyCommentSearch()`, `applyUserSearch()` are three separate methods doing nearly identical things. Should be one method with conditional logic.
- `destroy()` soft-deletes the report but doesn't check if it's already been handled. You can delete a report that's still being investigated.
- No bulk actions. Admins can only process reports one at a time. Industry supports bulk resolve, bulk dismiss, bulk assign.

### How to Fix
```php
// Single unified query in index()
public function index(Request $request)
{
    $query = Reports::with(['reporter', 'post', 'comment', 'targetUser'])
        ->when($request->target_name, fn($q, $type) =>
            $q->where('target_name', $type)
        )
        ->when($request->status, fn($q, $status) =>
            $status === 'unread' ? $q->where('status', 'new')
                : $q->where('status', '!=', 'new')
        )
        ->when($request->search, fn($q, $search) =>
            $q->where('reason', 'like', "%{$search}%")
              ->orWhereHas('reporter', fn($r) =>
                  $r->where('name', 'like', "%{$search}%")
              )
        )
        ->latest();

    $reports = $query->paginate(12);
    return view('layout.menu.reports', compact('reports'));
}
```

---

## 6. Authorization Blame

### What Industry Does
- Formal policy classes registered in `AuthServiceProvider`
- Gate-based authorization: `Gate::allows('manage-reports')`
- Role-based access control (RBAC) with middleware
- Audit trail of who authorized what

### What SiteSphere Does
```php
// Inline authorization in every method
private function authorizeAdmin(Request $request): void
{
    abort_unless($request->user()->role === 'admin', 403);
}
```

**Blame:**
- **`ReportsPolicy` exists but all methods return `false`.** It's never registered anywhere. This is dead code that confuses anyone reading the codebase.
- **Inline `abort_unless()` in every controller method.** This is not reusable. If you add a "moderator" role, you have to change every method.
- **No policy-based authorization on routes.** The `authorizeAdmin()` call is manually duplicated in every controller method instead of using middleware.
- **No distinction between "can view reports" and "can take action on reports".** A moderator might need read-only access, but there's no granular permission system.

### How to Fix
```php
// 1. Create a proper Gate in AuthServiceProvider
Gate::define('manage-reports', fn(User $user) =>
    in_array($user->role, ['admin', 'moderator'])
);

// 2. Use middleware on routes
Route::middleware(['auth', 'can:manage-reports'])->group(function () {
    Route::get('/menu/reports', [AdminReportsController::class, 'index']);
    // ...
});

// 3. Delete the dead ReportsPolicy or implement it properly
```

---

## 7. Notification Blame

### What Industry Does
- Event-driven notifications (dispatched via queue)
- Channel selection (email, in-app, push, SMS)
- User notification preferences (opt-in/out)
- Notification batching (don't spam)
- Delivery tracking and retry logic

### What SiteSphere Does
```php
// In ReportsController::store()
$admins = User::where('role', 'admin')->get();
foreach ($admins as $admin) {
    Notificatioins::create([
        'user_id' => $admin->id,
        'message' => auth()->user()->name . ' reported ' . $targetType . ': ' . $title,
        'is_read' => false,
    ]);
}
```

**Blame:**
- **Synchronous notification creation blocks the HTTP response.** If there are 10 admins, the user waits for 10 database writes before seeing the success message.
- **No queue.** Notifications should be dispatched to a job queue.
- **No batching.** If 100 users report the same post, every admin gets 100 separate notifications. Industry would consolidate: "100 users reported Post X".
- **Typo in model name: `Notificatioins`.** This is a permanent code smell.
- **No notification preferences.** Admins cannot opt out of report notifications or set frequency.
- **No email fallback.** If an admin doesn't check the dashboard, they never see the report. Industry sends email digests.

### How to Fix
```php
// 1. Use events + listeners
event(new ReportCreated($report));

// 2. Listener dispatches to queue
class NotifyAdminsOfReport implements ShouldQueue
{
    public function handle(ReportCreated $event)
    {
        // Batch: check if same target already has recent notifications
        $recentNotification = Notificatioins::where('message', 'like', '%' . $event->report->target_id . '%')
            ->where('created_at', '>', now()->subHour())
            ->exists();

        if (!$recentNotification) {
            // Create one notification per admin
            User::where('role', 'admin')->each(fn($admin) =>
                Notificatioins::create([...])
            );
        }
    }
}
```

---

## 8. Admin Dashboard Blame

### What Industry Does
- Priority-sorted queue (severity score × reporter trust × recency)
- One-click bulk actions
- Rich content preview (inline, not redirect)
- SLA indicators (time-in-queue, escalation warnings)
- Per-moderator workload tracking
- Template-based responses

### What SiteSphere Does
Three separate tabs with three separate paginated queries, manual previous/next pagination, no priority sorting.

**Blame:**
- **No priority sorting.** A spam report from today sits next to a harassment report from last week. All reports are equal — they shouldn't be.
- **No SLA tracking.** You have no way to know if a report has been sitting unread for 2 hours or 2 weeks.
- **Manual pagination buttons.** Laravel has `->paginate()` with built-in previous/next — you're reimplementing what the framework provides.
- **No bulk actions.** Admins must click each report individually. At scale, this is unusable.
- **"View" redirects away from the dashboard.** Admin loses context. Industry opens content in a side panel or modal.
- **No inline content preview.** Admin has to navigate to the actual post/comment/user page to see what was reported. This breaks workflow.
- **No moderator assignment.** Any admin can act on any report. No ownership, no accountability.
- **No analytics.** No "reports per day" chart, no "average resolution time", no "top reporters" list.

### How to Fix
```php
// Priority query
Reports::with(['reporter', 'post', 'comment'])
    ->where('status', '!=', 'closed')
    ->orderByRaw('CASE
        WHEN status = "new" AND created_at < NOW() - INTERVAL 48 HOUR THEN 0
        WHEN status = "new" THEN 1
        WHEN status = "pending_review" THEN 2
        WHEN status = "investigating" THEN 3
        ELSE 4 END')
    ->orderBy('created_at', 'desc')
    ->paginate(20);
```

---

## 9. Missing Features

### Critical (Should Have)

| Feature | Status | Impact |
|---------|--------|--------|
| Proper status workflow | ❌ Missing | Cannot track report lifecycle |
| Rate limiting | ❌ Missing | Vulnerable to spam attacks |
| Duplicate report detection | ❌ Missing | Same content reported multiple times |
| Status history / audit trail | ⚠️ Partial | Generic audit_logs, not structured transitions |
| Reporter cannot report own content | ❌ Missing | Logical error |
| Category-based filtering | ❌ Missing | reason is freeform text |
| Bulk actions | ❌ Missing | Unusable at scale |
| Auto-escalation on timeout | ❌ Missing | Reports can sit forever |

### Important (Should Have Soon)

| Feature | Status | Impact |
|---------|--------|--------|
| Report evidence/attachments | ❌ Missing | Users can't provide proof |
| Reporter trust scoring | ❌ Missing | All reporters treated equally |
| In-app content preview for admins | ❌ Missing | Breaks review workflow |
| Email notifications to admins | ❌ Missing | Reports missed if dashboard unchecked |
| Report analytics dashboard | ❌ Missing | No operational visibility |
| Moderator assignment | ❌ Missing | No accountability |

### Nice to Have (Future)

| Feature | Status | Impact |
|---------|--------|--------|
| Appeals process | ❌ Missing | No recourse for reported users |
| Community voting on reports | ❌ Missing | No crowdsourced triage |
| ML auto-classification | ❌ Missing | All reports require human review |
| Reporter notification on resolution | ❌ Missing | Reporters never learn outcome |

---

## 10. Priority Fix List

### Phase 1: Critical Fixes (Do Now)

1. **Replace `admin_read` boolean with proper `status` column**
   - Add migration: `status` ENUM with `new`, `pending_review`, `investigating`, `resolved_action_taken`, `resolved_no_action`, `dismissed`, `closed`
   - Add `resolved_at`, `closed_at` timestamps
   - Update all controller methods to use status transitions instead of boolean toggle

2. **Add rate limiting**
   - `throttle:10,1440` middleware on report routes (10 per day per user)
   - Duplicate detection: block same user reporting same content twice

3. **Add report categories as ENUM column**
   - Stop concatenating category + details into one `reason` field
   - Add `category` column: `spam`, `abuse`, `harassment`, `hate_speech`, `illegal`, `other`
   - Keep `reason` as optional free text

4. **Fix authorization**
   - Delete dead `ReportsPolicy` or implement it properly
   - Use Gate/middleware instead of inline `abort_unless()`

### Phase 2: Architecture Improvements (Do Soon)

5. **Extract business logic to `ReportService`**
   - Move all report logic out of controllers
   - Use events + listeners for notifications and audit logging
   - Queue notification creation

6. **Add `report_status_history` table**
   - Track every status transition with who, when, why
   - Required for accountability and analytics

7. **Unify admin queries**
   - Replace three separate queries with one parameterized query
   - Add priority sorting (newest unread first, then by age)

8. **Add bulk actions**
   - Select multiple reports → bulk resolve, bulk dismiss
   - Reduces admin workload at scale

### Phase 3: Feature Additions (Do Later)

9. **Report evidence table** — allow file/screenshot attachments
10. **Reporter notifications** — inform users when their report is resolved
11. **Email notifications for admins** — daily digest of unread reports
12. **Analytics dashboard** — reports/day, resolution time, top categories
13. **Auto-escalation** — reports unread > 48h get bumped to top
14. **Appeals process** — allow reported users to contest decisions

---

## Quick Reference: What Good Looks Like

```
Your System          →  Industry Standard
─────────────────────────────────────────────
admin_read (boolean) →  status (8-state enum)
reason (freeform)    →  category (enum) + reason (optional text)
inline abort_unless  →  Gate + middleware policy
synchronous notif    →  queued event + listener
3 separate queries   →  1 parameterized query
no rate limiting     →  multi-layer throttling
no duplicate check   →  per-user per-content dedup
no status history    →  report_status_history table
no priority sorting  →  severity × trust × recency
delete = only action →  resolve, dismiss, escalate, assign
```

---

## References

- Meta Content Moderation: Automated enforcement at scale
- Reddit Moderator Tools: Community-based triage with AutoModerator
- Twitter/X Trust & Safety: Community Notes + automated classification
- Alex Xu, System Design Interview Vol. 2 — Content Moderation chapter
- Laravel Documentation: Authorization (Gates, Policies), Rate Limiting, Event System
