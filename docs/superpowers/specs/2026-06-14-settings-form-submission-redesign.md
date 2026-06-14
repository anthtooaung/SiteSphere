# Settings Page Form Submission & SweetAlert Redesign (v2)

## Goal
Redesign the form submission behavior and SweetAlert2 (Swal) interactions for the dashboard settings pages. This ensures visual consistency with the system theme (using CSS variables) and robust standard browser form submissions as requested.

## Architecture: Approach 2 (Global Helper)

### 1. Global JS Helper (`resources/js/app.js`)
Introduce a `window.sitesphereSwal` object that provides pre-configured `Swal` methods. These methods will automatically pull values from the document's computed styles for:
- `background`: `--background-color`
- `color`: `--text-color`
- `confirmButtonColor`: `--accent-color`
- `fontFamily`: `--font-family`

### 2. Standardized Confirmation Pattern
For all settings pages, use Alpine.js to intercept form submission, show a themed confirmation, and then perform a standard `form.submit()`.

**Pattern:**
```javascript
async confirmAction(formElement, options = {}) {
    const result = await window.sitesphereSwal.confirm({
        title: options.title || 'Save Changes?',
        text: options.text || 'Apply these settings?'
    });

    if (result.isConfirmed) {
        this.isSubmitting = true;
        formElement.submit();
    } else {
        this.isSubmitting = false; // Ensure button re-enables
    }
}
```

### 3. File-Specific Updates
- **`resources/js/app.js`**: Implement the helper.
- **`appearance.blade.php`**: Use helper for save confirmation and session success toast.
- **`edit-profile.blade.php`**: Use helper for save confirmation and session success toast.
- **`security.blade.php`**: Use helper for save confirmation and session success toast.
- **`edit-tag.blade.php`**: Use helper for save, publish, and reset confirmations.

## Success Criteria
- SweetAlert popups and toasts match the user's active theme colors perfectly.
- Confirmation happens *before* the page reloads.
- Page reloads fully on success.
- "Success" toast appears *after* the reload with matching theme.
- UI never locks up (isSubmitting is reset on cancel).
