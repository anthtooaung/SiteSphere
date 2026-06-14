# Settings Page Redesign (Approach 2) Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Implement a global SweetAlert2 helper for themed interactions and refactor settings pages to use standard form submissions.

**Architecture:** Centralized JS helper in `app.js` and Alpine.js refactors in Blade templates.

**Tech Stack:** Laravel, Alpine.js, SweetAlert2.

---

### Task 1: Create Global SweetAlert Helper

**Files:**
- Modify: `resources/js/app.js`

- [ ] **Step 1: Define `window.sitesphereSwal`**
  Add the helper logic to pull CSS variables and pre-configure Swal.

```javascript
window.sitesphereSwal = {
    getTheme() {
        const style = getComputedStyle(document.documentElement);
        return {
            background: style.getPropertyValue('--background-color').trim() || '#ffffff',
            color: style.getPropertyValue('--text-color').trim() || '#0d1b2a',
            confirmButtonColor: style.getPropertyValue('--accent-color').trim() || '#6c5ce7',
            fontFamily: style.getPropertyValue('--font-family').trim() || 'Figtree, sans-serif'
        };
    },
    async confirm(options = {}) {
        const theme = this.getTheme();
        return await window.Swal.fire({
            icon: options.icon || 'question',
            title: options.title || 'Are you sure?',
            text: options.text || '',
            showCancelButton: true,
            cancelButtonColor: '#d33',
            background: theme.background,
            color: theme.color,
            confirmButtonColor: theme.confirmButtonColor,
            didOpen: (popup) => {
                popup.style.fontFamily = theme.fontFamily;
            },
            ...options
        });
    },
    toast(options = {}) {
        const theme = this.getTheme();
        window.Swal.fire({
            toast: true,
            position: options.position || 'top-end',
            showConfirmButton: false,
            timer: options.timer || 3000,
            timerProgressBar: true,
            icon: options.icon || 'success',
            title: options.title || '',
            background: theme.background,
            color: theme.color,
            didOpen: (toast) => {
                toast.onmouseenter = window.Swal.stopTimer;
                toast.onmouseleave = window.Swal.resumeTimer;
                toast.style.fontFamily = theme.fontFamily;
            },
            ...options
        });
    }
};
```

- [ ] **Step 2: Commit**
```bash
git add resources/js/app.js
git commit -m "feat: add global sitesphereSwal helper"
```

---

### Task 2: Refactor Settings Pages

**Files:**
- Modify: `resources/views/layout/menu/appearance.blade.php`, `edit-profile.blade.php`, `security.blade.php`, `edit-tag.blade.php`

- [ ] **Step 1: Refactor Appearance Page**
  Update `submitForm` and the session success block to use `window.sitesphereSwal`.

- [ ] **Step 2: Refactor Edit Profile Page**
  Update `submitForm` and the session success block.

- [ ] **Step 3: Refactor Security Page**
  Update `submitForm` and the session success block.

- [ ] **Step 4: Refactor Edit Tag Page**
  Update `confirmSubmit`, `confirmReset`, and the session success block.

- [ ] **Step 5: Commit**
```bash
git add resources/views/layout/menu/*.blade.php
git commit -m "refactor: use global sitesphereSwal helper in settings pages"
```

---

### Task 3: Build Assets

- [ ] **Step 1: Run build**
  Run: `npm run build`
  Expected: Success.

- [ ] **Step 2: Commit build files**
```bash
git add public/build/
git commit -m "build: compile assets with new swal helper"
```
