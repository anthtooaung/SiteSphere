# Button Actions & Database Interactions Documentation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Create a comprehensive markdown document (`knowledges/remainTask.md`) mapping every UI button click action to its corresponding backend database interaction across the SiteSphere platform.

**Architecture:** Research-based documentation tasks grouped by logical areas of the application (Public, User Dashboard, Admin).

**Tech Stack:** Markdown, grep/rg for codebase analysis.

---

### Task 1: Research and Document Public & Auth Pages

**Files:**
- Modify: `knowledges/remainTask.md`

- [ ] **Step 1: Write Introduction**
  Open or initialize the file with the document title and introduction.

```markdown
# SiteSphere Button Actions & Database Interactions

## Overview
This document maps every button click action and its corresponding database interaction across the SiteSphere platform.

## Page-by-Page Actions
```

- [ ] **Step 2: Research Welcome & Auth Pages**
  Analyze `resources/views/welcome.blade.php`, `resources/views/auth/*.blade.php` and their routes/controllers.
  Run: `grep -rnE "<button|<a.*btn|wire:click" resources/views/welcome.blade.php resources/views/auth/`
  Identify Login, Register, Forgot Password, and any interactions on the welcome page.

- [ ] **Step 3: Research Home & Detail Pages**
  Analyze `resources/views/index.blade.php`, `resources/views/layout/public/*.blade.php`, `resources/views/layout/menu/post-detail.blade.php`, `resources/views/layout/menu/profile-detail.blade.php`.
  Identify actions like Load More, Like Post, Bookmark, Rate, follow links.

- [ ] **Step 4: Document Findings**
  Append the findings to `knowledges/remainTask.md` under a `### Public & Auth Pages` header. Use the format specified in the design.

- [ ] **Step 5: Commit**
```bash
git add knowledges/remainTask.md
git commit -m "docs: map button actions for public and auth pages"
```

---

### Task 2: Research and Document User Dashboard Pages

**Files:**
- Modify: `knowledges/remainTask.md`

- [ ] **Step 1: Research Settings & Profile Management**
  Analyze `resources/views/layout/menu/appearance.blade.php`, `security.blade.php`, `edit-profile.blade.php`, `edit-tag.blade.php`.
  Run searches to find forms and buttons. Trace to controllers like `AppearanceController`, `ProfileController`.

- [ ] **Step 2: Research Saved Posts & Notifications**
  Analyze `resources/views/layout/menu/saved-post.blade.php`, `<x-layout.noti-btn>`.

- [ ] **Step 3: Document Findings**
  Append the findings to `knowledges/remainTask.md` under a `### User Dashboard Pages` header.

- [ ] **Step 4: Commit**
```bash
git add knowledges/remainTask.md
git commit -m "docs: map button actions for user dashboard pages"
```

---

### Task 3: Research and Document Admin Dashboard Pages

**Files:**
- Modify: `knowledges/remainTask.md`

- [ ] **Step 1: Research Admin Views**
  Analyze `resources/views/layout/menu/dashboard.blade.php`, `users.blade.php`, `reports.blade.php`, `categories.blade.php`, `tags.blade.php` (if they exist).

- [ ] **Step 2: Document Findings**
  Append the findings to `knowledges/remainTask.md` under a `### Admin Dashboard Pages` header.

- [ ] **Step 3: Commit**
```bash
git add knowledges/remainTask.md
git commit -m "docs: map button actions for admin dashboard pages"
```

---

### Task 4: Compile Database Mutation Summary

**Files:**
- Modify: `knowledges/remainTask.md`

- [ ] **Step 1: Aggregate Data Mutating Actions**
  Review all documented actions from Tasks 1-3. Identify every action that performs an INSERT, UPDATE, or DELETE.

- [ ] **Step 2: Write Summary Table**
  Append the "Database Mutation Summary" section and table to `knowledges/remainTask.md`.

```markdown
## Database Mutation Summary

| Action | Route / Controller | Tables Affected | Operation |
| :--- | :--- | :--- | :--- |
| (Fill with aggregated data) | | | |
```

- [ ] **Step 3: Review and Format**
  Ensure the markdown is well-formatted and easy to read.

- [ ] **Step 4: Commit**
```bash
git add knowledges/remainTask.md
git commit -m "docs: add database mutation summary for button actions"
```