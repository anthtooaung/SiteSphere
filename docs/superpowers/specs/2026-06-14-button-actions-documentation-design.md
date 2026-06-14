# Button Actions & Database Interactions Documentation Design

## Overview
This document outlines the structure for `knowledges/remainTask.md`, which will contain a comprehensive mapping of every button click action and its corresponding database interaction across the SiteSphere platform.

## Structure of the Documentation

### 1. Introduction
- Brief overview of the document's purpose (mapping UI interactions to backend/DB actions).

### 2. Page-by-Page Button Actions
Grouped by view, this section will list buttons and their behaviors.
Format per page:
- **[Button Name/Icon]** - [Location on page]
  - **Type:** (Navigation | Filter | Mutation)
  - **Action:** (What it does on click)
  - **DB Interaction:** (Read/Write/None) and the tables affected.

**Sections to cover:**
- **Public & Auth:** Welcome, Register, Login, Forgot Password, Password Reset.
- **Main App (User):** Home, Post Detail, Profile Detail, Notifications.
- **User Dashboard:** Saved Posts, Appearance, Security, Edit Profile, Edit Tag, My Uploads, My Reviews.
- **Admin Dashboard:** Overview, Users, Reports, Categories, Tags.

### 3. Database Mutation Summary
A consolidated table summarizing all data-mutating actions (POST/PUT/DELETE) to provide a clear view of database impact.

| Action | Route / Controller | Tables Affected | Operation |
| :--- | :--- | :--- | :--- |
| Create Post | `PostsController@store` | `posts`, `post_tags` | Insert |
| Like Post | `CommentReactionsController` | `comment_reactions` | Insert/Delete |
| ... | ... | ... | ... |

## Implementation Plan
1. Research all Blade templates (`resources/views/`) to catalog buttons, forms, and AJAX actions (`<button>`, `<a>` styled as buttons, `wire:click`, Alpine `@click`).
2. Map actions to web routes (`routes/web.php`, `routes/auth.php`).
3. Trace route handlers (Controllers) to identify database interactions (Models, DB queries).
4. Compile the findings into the structured format.
5. Write the final content into `knowledges/remainTask.md`.
