# Report System Planning and Analysis
Date: 2026-06-23

## 1. System Design and Flow
The current report system in `SiteSphere` follows a straightforward and effective flow:

**Reporting Phase:**
1. **User Action:** A user submits a report for a Post or Comment.
2. **Data Storage:** The `ReportsController` handles the request. It stores the report in the `reports` table using a custom polymorphic structure (`target_name` as enum: 'posts', 'comments', 'users' and `target_id`). It sets `admin_read` to `false`.
3. **Notification:** Immediately after creating the report, the system loops through all users with the `admin` role and inserts a record into the `notificatioins` table to alert them of the new report.

**Review Phase:**
4. **Admin Dashboard:** Admins visit the report management page (`AdminReportsController`). They can filter reports by date and type.
5. **Action:** Admins can open reports, mark them as read/unread, and if they find the report valid, they can proceed to ban the reported entity.

## 2. Before and After a Ban

When an Admin decides to ban a target based on a report:

**Before Ban:**
- The target (Post or Comment) is fully visible to the public.
- The report sits in the admin dashboard.

**After Ban (The Banning Process):**
The ban actions are wrapped in a Database Transaction (`DB::transaction`) to ensure data consistency.

- **For Posts (`PostsController::ban`):**
  - All user descriptions (`UserPosts`) attached to the post are hidden (`user_hidden` = true).
  - The `Posts` model is Soft Deleted, removing it from public view.
  - An `AuditLogs` entry is created with the action `ban_post`, logging the admin who performed the action and the reason.
  
- **For Comments (`CommentsController::ban`):**
  - Any `Ratings` associated with this comment by the user are deleted.
  - The `Comments` model is Soft Deleted.
  - An `AuditLogs` entry is created with the action `ban_comment`.
  - A `Notificatioins` entry is created and sent to the original author of the comment, informing them that their comment was banned and providing the reason.

## 3. Reporting Tables Efficiency

**Current State:**
The `reports` table (`2026_05_14_040438_create_reports_table.php`) has the following structure:
- `id`, `user_id`
- `target_name` (enum: 'users', 'posts', 'comments')
- `target_id` (integer)
- `reason` (longText)
- `admin_read` (boolean)
- `timestamps`

**Are they efficient?**
There is room for improvement regarding database efficiency and indexing.

**Areas for Improvement (Database Efficiency):**
1. **Missing Indexes:** 
   Currently, there are no indexes on the columns used heavily for filtering and joining. To improve read efficiency for the admin dashboard, you should add:
   - A composite index on `['target_name', 'target_id']` to speed up polymorphic relationship lookups.
   - An index on `['admin_read']` to quickly filter unread reports.
   - An index on `['created_at']` because the `AdminReportsController` frequently filters reports by `reported_date`.
   
2. **Laravel Standard Morph Types:**
   While `target_name` and `target_id` work, Laravel's standard `MorphTo` uses `target_type` (storing the full class namespace like `App\Models\Posts`) and `target_id`. Changing to standard morphs makes Eloquent queries much cleaner, though the current custom implementation is acceptable as long as it's indexed.

**Conclusion:**
The logic and flow are solid and handle data integrity well using transactions. However, as the application grows, the `reports` table will become slow without the appropriate database indexes mentioned above.
