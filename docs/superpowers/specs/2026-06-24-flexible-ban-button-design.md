# Flexible Ban Button Design
**Date:** 2026-06-24
**Status:** Approved

---

## Problem

Ban buttons across the admin UI use inconsistent text:
- Post card dropdown says "Ban" (should say "Ban Post")
- Comment dropdown says "Ban" (should say "Ban Comment")
- Description/audit dropdown says "Ban" (should say "Ban Description")
- Reports page delete/resolve dialogs use generic text across all tabs

## Approach

Hardcode specific text in each blade file. No abstraction, no state checking, no new components.

## Changes

### 1. `resources/views/components/layout/post-card.blade.php`

Line 166 — Change button text:
```
- <span>Ban</span>
+ <span>Ban Post</span>
```

### 2. `resources/views/layout/post-detail.blade.php`

Line 553 — Change description ban button text:
```
- <span>Ban</span>
+ <span>Ban Description</span>
```

### 3. `resources/views/components/layout/comments.blade.php`

Line 279 — Change comment ban button text:
```
- <span>Ban</span>
+ <span>Ban Comment</span>
```

### 4. `resources/views/layout/menu/reports.blade.php`

**Posts tab (line ~357):**
- Delete dialog: `confirmAction($event, 'Delete Report?', ...)` → `confirmAction($event, 'Delete Post Report?', 'This action will remove the post report record from the queue.', 'Delete Post Report')`
- Resolve dialog: `confirmAction($event, 'Resolve Report?', ...)` → `confirmAction($event, 'Resolve All Post Reports?', 'This will delete ALL reports for this post and reset the report count.', 'Resolve All Post Reports')`
- Resolve tooltip: `"Resolve All Reports"` → `"Resolve All Post Reports"`

**Comments tab (line ~521):**
- Delete dialog: → `confirmAction($event, 'Delete Comment Report?', 'This action will remove the comment report record from the queue.', 'Delete Comment Report')`
- Resolve dialog: → `confirmAction($event, 'Resolve All Comment Reports?', 'This will delete ALL reports for this comment and reset the report count.', 'Resolve All Comment Reports')`
- Resolve tooltip: → `"Resolve All Comment Reports"`

**Users tab (line ~688):**
- Delete dialog: → `confirmAction($event, 'Delete User Report?', 'This action will remove the user report record from the queue.', 'Delete User Report')`
- Resolve dialog: → `confirmAction($event, 'Resolve All User Reports?', 'This will delete ALL reports for this user and reset the report count.', 'Resolve All User Reports')`
- Resolve tooltip: → `"Resolve All User Reports"`

## No Changes Needed

These are already correct:
- Post detail ban button — already says "Ban Post" ✅
- Reports restore buttons — already say "Restore Post" / "Restore Comment" ✅
- Confirmation dialog titles for ban actions — already specific ("Ban this comment?", "Ban this description?") ✅

## Scope

- 4 files changed
- ~10 text replacements
- No logic changes, no new routes, no database changes
