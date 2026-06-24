# Report Box UI Upgrade
**Date:** 2026-06-24
**Status:** Ready for Implementation

---

## 1. Goal

Simplify the reports table UI by removing unnecessary actions and adding a clear read/unread toggle. The Description tab will be added later after other features are complete.

---

## 2. Changes Summary

### 2.1 Remove from All Tables

| Remove | Reason |
|--------|--------|
| Delete button (trash icon) | Reports should not be deleted manually; resolve handles cleanup |
| Restore button (undo icon) | Restore should happen on the content detail page, not from reports table |

### 2.2 Keep/Modify

| Action | Behavior |
|--------|----------|
| View button (eye icon) | Opens report detail page + auto-marks as read |
| Read/Unread toggle | Single button with icon + text |

### 2.3 New Toggle Design

```
If unread → Show "Mark Read" button (green, check icon)
If read   → Show "Mark Unread" button (gray, check-double icon)
```

**Style:** Icon + text (Option B)

---

## 3. Behavior Rules

### 3.1 Opening a Report

| Trigger | What Happens |
|---------|--------------|
| Click row | Opens report detail page + marks that report as read |
| Click View button | Opens report detail page + marks that report as read |

**Note:** Only the clicked report is marked as read, not all reports for the same target.

### 3.2 Manual Toggle

| Action | Result |
|--------|--------|
| Click "Mark Read" | Marks as read without opening detail page |
| Click "Mark Unread" | Marks as unread |

### 3.3 Resolve Action (on Detail Page)

When admin resolves a report from the detail page:

| Action | Result |
|--------|--------|
| Resolve | Resets `report_count` to 0 on the target |
| | Reports stay in the table (not deleted) |
| | Reports remain visible with their read status |

---

## 4. UI Layout

### 4.1 Actions Column (Before)

```
┌─────────────────────────────────────────────────────────┐
│ 👁️ View │ ✓✓ Mark Unread │ ↩️ Restore │ 🗑️ Delete      │
└─────────────────────────────────────────────────────────┘
```

### 4.2 Actions Column (After)

```
┌──────────────────────────────────────┐
│ 👁️ View  │  ✓ Mark Read (green)     │
└──────────────────────────────────────┘
     or
┌──────────────────────────────────────┐
│ 👁️ View  │  ✓✓ Mark Unread (gray)   │
└──────────────────────────────────────┘
```

---

## 5. Files to Change

### 5.1 Blade Template

**File:** `resources/views/layout/menu/reports.blade.php`

Changes:
- Remove delete button from POST table (lines ~357-365)
- Remove delete button from COMMENT table (lines ~512-520)
- Remove delete button from USER table (lines ~670-678)
- Remove restore button from POST table (lines ~346-355)
- Remove restore button from COMMENT table (lines ~501-510)
- Add "Mark Read" button for unread reports (currently only "Mark Unread" exists)

### 5.2 Controller

**File:** `app/Http/Controllers/AdminReportsController.php`

Changes:
- Update `open()` method to auto-mark report as read when viewed
- Ensure `read()` and `unread()` methods work correctly for toggle

### 5.3 CSS (if needed)

**File:** `resources/css/reports.css`

Changes:
- Style the "Mark Read" button (green variant)
- Ensure toggle buttons look consistent

---

## 6. Table Column Headers

### Before
```
| No | Post Content | Reason | Reported By | Reported Date | Actions |
```

### After
```
| No | Post Content | Reason | Reported By | Reported Date | Actions |
```

No column changes needed — just fewer buttons in the Actions column.

---

## 7. Tabs

### Current
```
[POST] [COMMENT] [USER]
```

### Future (after other features complete)
```
[POST] [COMMENT] [USER] [DESCRIPTION]
```

The Description tab will be added when `user_posts` reporting is implemented.

---

## 8. Edge Cases

| Scenario | Handling |
|----------|----------|
| Report for deleted content | View button disabled with "unavailable" tooltip |
| Multiple reports for same target | Each report has independent read status |
| Admin marks as read then resolves | Report stays in table, shows as read |
| Report already read | "Mark Read" button not shown, only "Mark Unread" |

---

## 9. Implementation Order

### Step 1: Blade Template
1. Remove delete buttons from all 3 tables
2. Remove restore buttons from POST and COMMENT tables
3. Add "Mark Read" button for unread reports
4. Keep "Mark Unread" button for read reports

### Step 2: Controller
5. Update `open()` to auto-mark as read
6. Verify `read()` and `unread()` toggle works

### Step 3: Testing
7. Test View auto-marks as read
8. Test toggle between read/unread
9. Test resolve from detail page

---

**Next Step:** Implement Step 1 — Update blade template
