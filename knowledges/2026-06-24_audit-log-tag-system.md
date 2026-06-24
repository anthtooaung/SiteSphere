# Audit Log Tag System Redesign
**Date:** 2026-06-24
**Status:** ✅ Implemented

---

## 1. Goal

Replace the current inconsistent `category` system with a clear, semantic tag system where each tag tells the admin *what kind of action* was taken and *what it means*.

**Visual style:** Color-coded circle boxes (no icons).

---

## 2. Tag Definitions

### 2.1 Announcement (Purple `#7c3aed`)
**Meaning:** Admin changed something about the site's structure or taxonomy.

| Action | Trigger |
|--------|---------|
| `add_category` | Admin creates a new category |
| `update_category` | Admin renames/edits a category |
| `delete_category` | Admin removes a category |
| `update_tag_taxonomy` | Admin publishes global tag defaults |

---

### 2.2 Check (Blue `#3b82f6`)
**Meaning:** Admin deliberately reviewed/inspected something. Not automatic — only logged when admin actively opens or reads.

| Action | Trigger |
|--------|---------|
| `read_report` | Admin opens/views a report |
| `unread_report` | Admin marks a report as unread |
| `view_notification` | Admin opens a notification |
| `review_flagged_content` | Admin opens a flagged post/comment/user for inspection |

---

### 2.3 Moderation (Red `#ef4444`)
**Meaning:** Admin took action to restrict, remove, or punish content/users.

| Action | Trigger |
|--------|---------|
| `ban_post` | Admin soft-deletes a post |
| `ban_audit` | Admin soft-deletes a user description |
| `ban_comment` | Admin soft-deletes a comment |
| `ban_user` | Admin bans a user account |
| `set_unsecure` | Admin marks user/content as unsecure |
| `force_delete_post` | Admin permanently deletes a post |
| `force_delete_audit` | Admin permanently deletes a user description |
| `force_delete_comment` | Admin permanently deletes a comment |
| `force_delete_user` | Admin permanently deletes a user |
| `delete_user` | Admin soft-deletes (restricts) a user |
| `change_user_role` | Admin changes a user's role |
| `delete_report` | Admin deletes a report |

---

### 2.4 Resolved (Green `#10b981`)
**Meaning:** Admin fixed something — restored content, unbanned, or resolved a report.

| Action | Trigger |
|--------|---------|
| `unban_post` | Admin restores a soft-deleted post |
| `unban_audit` | Admin restores a soft-deleted description |
| `unban_comment` | Admin restores a soft-deleted comment |
| `restore_user` | Admin restores a soft-deleted user |
| `set_verified` | Admin changes user status back to verified |
| `resolve_report` | Admin resolves a report (deletes records + resets count) |

---

## 3. Mapping: Old Categories → New Tags

| Old Action | Old Category | New Tag | Reason |
|------------|-------------|---------|--------|
| `ban_post` | system ❌ | **moderation** | Admin restricting content |
| `unban_post` | system ❌ | **resolved** | Admin restoring content |
| `force_delete_post` | moderation | **moderation** | Keep |
| `ban_audit` | moderation | **moderation** | Keep |
| `unban_audit` | moderation ❌ | **resolved** | Admin restoring content |
| `force_delete_audit` | moderation | **moderation** | Keep |
| `ban_comment` | moderation | **moderation** | Keep |
| `unban_comment` | moderation ❌ | **resolved** | Admin restoring content |
| `force_delete_comment` | moderation | **moderation** | Keep |
| `change_user_role` | moderation | **moderation** | Keep |
| `delete_user` | moderation | **moderation** | Keep |
| `restore_user` | moderation ❌ | **resolved** | Admin restoring user |
| `force_delete_user` | moderation | **moderation** | Keep |
| `read_report` | system ❌ | **check** | Admin reviewing |
| `unread_report` | system ❌ | **check** | Admin reviewing |
| `delete_report` | system ❌ | **moderation** | Admin removing data |
| `update_tag_taxonomy` | system ❌ | **announcement** | Taxonomy change |

---

## 4. New Actions to Add (From Report System)

These actions are new from the report system brainstorming and need audit log entries:

| New Action | Tag | Context |
|------------|-----|---------|
| `add_category` | **announcement** | Admin creates a new category |
| `update_category` | **announcement** | Admin edits a category |
| `delete_category` | **announcement** | Admin removes a category |
| `resolve_report` | **resolved** | Admin resolves a report (deletes records + resets count) |
| `set_unsecure` | **moderation** | Admin marks user/content as unsecure |
| `set_verified` | **resolved** | Admin changes status back to verified |
| `ban_user` | **moderation** | Admin bans a user (from new status field) |
| `review_flagged_content` | **check** | Admin opens flagged content for inspection |

---

## 5. Database Changes

### 5.1 Migration: Fix Existing Categories

Retroactively update all existing `audit_logs` rows:

```php
// From 'system' to correct category
DB::table('audit_logs')->where('action', 'ban_post')->where('category', 'system')->update(['category' => 'moderation']);
DB::table('audit_logs')->where('action', 'unban_post')->where('category', 'system')->update(['category' => 'resolved']);
DB::table('audit_logs')->where('action', 'ban_audit')->where('category', 'system')->update(['category' => 'moderation']);
DB::table('audit_logs')->where('action', 'unban_audit')->where('category', 'system')->update(['category' => 'resolved']);
DB::table('audit_logs')->where('action', 'read_report')->where('category', 'system')->update(['category' => 'check']);
DB::table('audit_logs')->where('action', 'unread_report')->where('category', 'system')->update(['category' => 'check']);
DB::table('audit_logs')->where('action', 'delete_report')->where('category', 'system')->update(['category' => 'moderation']);
DB::table('audit_logs')->where('action', 'update_tag_taxonomy')->where('category', 'system')->update(['category' => 'announcement']);

// Fix unban_audit that was set to 'moderation' (should be 'resolved')
DB::table('audit_logs')->where('action', 'unban_audit')->where('category', 'moderation')->update(['category' => 'resolved']);
DB::table('audit_logs')->where('action', 'unban_comment')->where('category', 'moderation')->update(['category' => 'resolved']);
DB::table('audit_logs')->where('action', 'restore_user')->where('category', 'moderation')->update(['category' => 'resolved']);
```

### 5.2 No Schema Changes Needed

The `category` column already exists on `audit_logs`. We just need to:
1. Fix existing data (migration above)
2. Update all `AuditLogs::create()` calls to use correct categories
3. Remove `'system'` as a valid/default category

---

## 6. Model Changes

### `AuditLogs.php`

Update `getIcon()` and `getColor()` — remove `system`, add `check`:

```php
// No icons — color circle only
// Just update getColor():

public function getColor(): string
{
    return match ($this->category) {
        'moderation' => '#ef4444',   // Red
        'check'      => '#3b82f6',   // Blue
        'announcement' => '#7c3aed', // Purple
        'resolved'   => '#10b981',   // Green
        default      => '#6b7280',   // Gray (fallback)
    };
}
```

### Remove `getIcon()` method
Since we're using color circle boxes instead of icons, remove the `getIcon()` method entirely.

---

## 7. Controller Changes

### 7.1 PostsController.php

| Method | Current | New |
|--------|---------|-----|
| `ban()` | category: system | category: **moderation** |
| `unban()` | category: system | category: **resolved** |
| `forceDelete()` | category: moderation | Keep |
| `banAudit()` | category: moderation | Keep |
| `unbanAudit()` | category: moderation | category: **resolved** |
| `forceDeleteAudit()` | category: moderation | Keep |

### 7.2 CommentsController.php

| Method | Current | New |
|--------|---------|-----|
| `ban()` | category: moderation | Keep |
| `unban()` | category: moderation | category: **resolved** |
| `forceDelete()` | category: moderation | Keep |

### 7.3 AdminUsersController.php

| Method | Current | New |
|--------|---------|-----|
| `changeRole()` | category: moderation | Keep |
| `delete()` | category: moderation | Keep |
| `restore()` | category: moderation | category: **resolved** |
| `forceDelete()` | category: moderation | Keep |

### 7.4 AdminReportsController.php

| Method | Current | New |
|--------|---------|-----|
| `open()` | category: system | category: **check** |
| `read()` | category: system | category: **check** |
| `unread()` | category: system | category: **check** |
| `delete()` | category: system | category: **moderation** |
| `resolve()` (NEW) | — | category: **resolved** |

### 7.5 EditTagsController.php

| Method | Current | New |
|--------|---------|-----|
| `update()` | category: system | category: **announcement** |

### 7.6 New Actions (from Report System)

| Controller | Method | Action | Category |
|------------|--------|--------|----------|
| CategoriesController | `store()` | `add_category` | **announcement** |
| CategoriesController | `update()` | `update_category` | **announcement** |
| CategoriesController | `destroy()` | `delete_category` | **announcement** |
| AdminReportsController | `resolve()` | `resolve_report` | **resolved** |
| AdminUsersController | `setUnsecure()` | `set_unsecure` | **moderation** |
| AdminUsersController | `setVerified()` | `set_verified` | **resolved** |

---

## 8. UI Changes

### 8.1 Activity Log Page — Filter Bar

Add a filter bar above the calendar to filter by tag type:

```
[All] [Announcement] [Check] [Moderation] [Resolved]
```

- Clicking a tag filters the calendar to show only that type
- "All" shows everything (default)
- Filter persists when navigating months

### 8.2 Dashboard — Recent Activity

Update the timeline to use color circle boxes instead of icons:
- Small colored circle (8-10px) next to each entry
- Color matches the tag category
- Legend row already shows the four colors — keep it

### 8.3 Color Legend

```
● Announcement (Purple)
● Check (Blue)
● Moderation (Red)
● Resolved (Green)
```

---

## 9. Edge Cases

| Scenario | Handling |
|----------|----------|
| Old logs with `system` category | Migration fixes them to correct category |
| Unknown action type | Falls back to gray `#6b7280` |
| Admin action not yet categorized | Must explicitly set category on every `AuditLogs::create()` |
| Multiple actions in one flow | Each gets its own audit log entry with correct tag |

---

## 10. Implementation Order

### Phase 1: Fix Existing Data ✅
1. ✅ Write migration to fix all existing `audit_logs` categories — `database/migrations/2026_06_24_000000_fix_audit_log_categories.php`
2. ✅ Update `AuditLogs` model — remove `system`, add `check`, remove `getIcon()`

### Phase 2: Update Controllers ✅
3. ✅ Update `PostsController` — fix `ban_post`, `unban_post`, `unban_audit` categories
4. ✅ Update `CommentsController` — fix `unban_comment` category
5. ✅ Update `AdminUsersController` — fix `restore_user` category
6. ✅ Update `AdminReportsController` — fix `read_report`, `unread_report`, `delete_report` categories
7. ✅ Update `EditTagsController` — fix `update_tag_taxonomy` category

### Phase 3: Add New Actions (from Report System) ✅
8. ✅ Add `resolve_report` audit log to `AdminReportsController`
9. ✅ Add `add_category`, `update_category`, `delete_category` audit logs
10. ✅ Add `set_unsecure`, `set_verified` audit logs

### Phase 4: UI ✅
11. ✅ Update dashboard timeline to use color circles
12. ✅ Update activity log page to use color circles
13. ✅ Add filter bar to activity log page

---

**Next Step:** Review this design, then start implementation with Phase 1.
