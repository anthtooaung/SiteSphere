# Refactor Settings Pages Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Refactor settings pages to use the global `window.sitesphereSwal` helper for consistent UI notifications and confirmations.

**Architecture:** Replace direct `Swal.fire` calls with `window.sitesphereSwal.confirm` for user actions and `window.sitesphereSwal.toast` for session feedback.

**Tech Stack:** Laravel Blade, AlpineJS, SweetAlert2 (via custom helper).

---

### Task 1: Refactor Appearance Page

**Files:**
- Modify: `resources/views/layout/menu/appearance.blade.php`

- [ ] **Step 1: Update `submitForm` in AlpineJS data**
  Replace `Swal.fire` with `window.sitesphereSwal.confirm`.

- [ ] **Step 2: Update session success block**
  Replace `Swal.fire` toast with `window.sitesphereSwal.toast`.

- [ ] **Step 3: Commit**
```bash
git add resources/views/layout/menu/appearance.blade.php
git commit -m "refactor(appearance): use global sitesphereSwal helper"
```

### Task 2: Refactor Edit Profile Page

**Files:**
- Modify: `resources/views/layout/menu/edit-profile.blade.php`

- [ ] **Step 1: Update `showMessage` in AlpineJS data**
  Replace `Swal.fire` toast with `window.sitesphereSwal.toast`.

- [ ] **Step 2: Update `submitForm` in AlpineJS data**
  Replace `Swal.fire` confirmation with `window.sitesphereSwal.confirm`.

- [ ] **Step 3: Update session success and error blocks**
  Replace `Swal.fire` toasts with `window.sitesphereSwal.toast`.

- [ ] **Step 4: Commit**
```bash
git add resources/views/layout/menu/edit-profile.blade.php
git commit -m "refactor(profile): use global sitesphereSwal helper"
```

### Task 3: Refactor Security Page

**Files:**
- Modify: `resources/views/layout/menu/security.blade.php`

- [ ] **Step 1: Update `submitForm` in AlpineJS data**
  Replace `Swal.fire` confirmation with `window.sitesphereSwal.confirm`.

- [ ] **Step 2: Update session success and error blocks**
  Replace `Swal.fire` toasts with `window.sitesphereSwal.toast`.

- [ ] **Step 3: Commit**
```bash
git add resources/views/layout/menu/security.blade.php
git commit -m "refactor(security): use global sitesphereSwal helper"
```

### Task 4: Refactor Edit Tag Page

**Files:**
- Modify: `resources/views/layout/menu/edit-tag.blade.php`

- [ ] **Step 1: Update `confirmSubmit` and `confirmReset` in AlpineJS data**
  Replace `Swal.fire` with `window.sitesphereSwal.confirm`.

- [ ] **Step 2: Update `removeCategory` and `removeTag` in AlpineJS data**
  Replace `Swal.fire` with `window.sitesphereSwal.confirm`.

- [ ] **Step 3: Update session success/error block**
  Replace `Swal.fire` toast with `window.sitesphereSwal.toast`.

- [ ] **Step 4: Commit**
```bash
git add resources/views/layout/menu/edit-tag.blade.php
git commit -m "refactor(tag): use global sitesphereSwal helper"
```

### Task 5: Final Review and Combined Commit (Optional as per Task 2 instruction)

**Files:**
- Modify: all affected files

- [ ] **Step 1: Verify all settings pages use the helper**
- [ ] **Step 2: Final Commit (if not already committed)**
```bash
git add resources/views/layout/menu/*.blade.php
git commit -m "refactor: use global sitesphereSwal helper in settings pages"
```
