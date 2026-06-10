# Rules
# 20% remaining token
- If token reach 20% then stop the current process and fill in this **reports.md** (what finished, what remain)
# instruction 
- Fill instruction that I gave into this file and then do the process .

# login account
- Email : **thegrowingtreeclan@gmail.com** | password : **123!@#123**

# Instructions 
1. The `reports-table-toolbar` must be the same style as `resources/views/layout/menu/users.blade.php`'s `admin-users-filter-container` box style.
2. When clicking the `reports-tabs-card` buttons, the `reports-table-toolbar` must not hide or remove from the page. If filtered, the filter context must not be lost.
3. The design of `reports-table-card`, `reports-table-toolbar`, and `reports-table-wrap` must be separated similarly to the `admin-users-filter-container` and `admin-users-table-card` structure in `users.blade.php`.
4. The table design in `reports.blade.php` must exactly match the `users.blade.php` table design, including the search skeleton loading animations.
5. In the table of `reports.blade.php`, when there is no data, the no-data info display must be centered.
6. The filter section must be fully functional and work well (AJAX filtering).
7. In `reports.blade.php`, the `data-label="Actions"` action text must be replaced with FontAwesome icons.
8. Check the design, and if anything does not look good, write in this `reports.md` file how to fix it to make it look professional.
10. Update backend logic for status filter: when unread show unread data, when read show read data, when all reports show all reports sorted by unread to read.
11. Update delete actions to trigger actual soft deletes in the database.
12. Allow toggling read/unread status (clicking read button on a read report changes it to unread).
13. Clicking `x-fas-eye` (View) should navigate to the actual resource (Post -> post detail, Comment -> post detail at comment, User -> profile detail).

# Tasks 
- [x] Separate `reports-table-toolbar` from the tab panels and place it persistently on the page.
- [x] Update `reports-table-toolbar` CSS in `reports.css` to match `admin-users-filter-container` (48px heights, floating labels, balanced padding).
- [x] Implement Alpine.js `fetchData` logic with `isFiltering`, `isClearing`, and `isLoading` states for AJAX reloading.
- [x] Implement the pulse animation skeleton loader inside an `x-show="isLoading"` dummy `<tbody>`.
- [x] Design the centered "No data" UI using flexbox/grid for the empty states.
- [x] Replace all text action buttons in the Actions column with `admin-users-action-btn` style square icon buttons using FontAwesome.
- [x] Assess the final design quality and log any further improvements needed here.
- [x] Remove focus outline from search input and add border to `reports-control-wrapper`.
- [x] Fix backend filter logic (status=all sends to backend, sorted unread-first).
- [x] Remove horizontal scroll from table (table-layout: fixed, column widths).
- [x] Add `SoftDeletes` trait to `Posts` and `Comments` models + migration.
- [x] Add `markUnread` controller method and route for toggling read→unread.
- [x] Add `deletePost` and `deleteComment` controller methods and routes for soft deleting.
- [x] Update view (eye) buttons to navigate to actual resources (post detail, post detail#comment, profile detail).
- [x] Update delete buttons to trigger real soft-delete forms instead of SweetAlert placeholders.

# What remain 

- **Tooltips**: Consider standardizing the tooltips across the table using a library like Tippy.js or Alpine tooltips if the default HTML `title` attributes feel too basic compared to the rest of the enterprise-grade UI.