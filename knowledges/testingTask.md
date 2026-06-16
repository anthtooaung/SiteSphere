# Pending Test Fixes: Admin Reports

**Date:** June 17, 2026
**Context:** Implemented UI and backend logic changes for the Admin Reports page (auto-read on view, generic report deletion, audit logging). The core implementation is complete, but the automated tests in `tests/Feature/AdminReportsPageTest.php` require updating to match the new behavior.

## Current State
The backend routes (`reports.open`, `reports.destroy`) and controller (`AdminReportsController.php`) logic are fully implemented. The UI (`reports.blade.php`) has been updated to use these new routes.

## Failing Tests to Fix Later

1. **`test_admin_can_mark_report_as_read_and_open`**
   - **Error:** Returns a `404 Not Found` in the test environment.
   - **Cause:** The `Reports` model parameter binding in the `reports.open` route might not be resolving correctly in the test environment, or the test data setup isn't matching what the `open` method expects (e.g., `target_name` mismatch or missing `post`).

2. **`test_opening_report_notification_marks_notification_read_but_not_report_read`**
   - **Error:** Returns a `403 Forbidden` (or sometimes `419 CSRF Token Mismatch` depending on middleware state).
   - **Cause:** This test needs to be adjusted to correctly simulate the CSRF token if middleware is active, or use `withoutMiddleware()` correctly around the POST request to the notification open route.

## Next Steps When Resuming
- Revert any temporary `dd()` statements in `app/Http/Controllers/AdminReportsController.php` (specifically in `markRead` and `open`).
- Investigate route model binding in `AdminReportsPageTest.php` for the `reports.open` `GET` request.
- Ensure `TestCase.php` middleware settings are stable and consistent across the test suite.