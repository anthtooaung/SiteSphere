# Design Spec: Mobile Bottom-Sheet Account Menu

## 1. Overview
The goal is to provide a consistent mobile experience for account and administrative actions by transforming the existing vertical menu/dropdown into a mobile-optimized bottom-sheet drawer. This aligns with the "Profile" nav item in the mobile bottom navigation bar.

## 2. Interaction Design
- **Trigger:** The "Profile" button in the `mobile-bottom-nav` (located in `nav.blade.php` via `profile-menu-btn.blade.php`).
- **Behavior:** Slides up from the bottom, occupying 75% of the viewport height.
- **Dismissal:**
    - Tap on the backdrop overlay.
    - Tap the "X" close button or pull-down handle.
- **Responsiveness:**
    - **Mobile:** Fixed bottom sheet.
    - **Desktop:** Retains existing sidebar (`layout-menu`) or dropdown (`account-menu-dropdown`) behavior.

## 3. Visual Specifications
- **Structure:**
    - **Header:** Title ("Account & Settings") and Close button.
    - **User Info:** Brief summary of the logged-in user (Avatar, Name, Status).
    - **Navigation Groups:**
        - **Profile:** View Profile, Saved Posts.
        - **Admin (Conditional):** Dashboard, Users, Reports.
        - **Settings:** Edit Profile, Appearance, Edit Tag, Security.
    - **Footer:** Logout action.
- **Styling:**
    - Rounded top corners (`rounded-t-3xl`).
    - Semi-transparent backdrop (`bg-black/50`).
    - Scrollable content area within the sheet.

## 4. Technical Implementation
- **Markup:**
    - Update `resources/views/components/layout/menu.blade.php` to include a mobile-specific container for the bottom sheet.
    - Alternatively, refactor `menu.blade.php` to be more modular so the same links can be used in both desktop and mobile contexts without duplication.
- **Styling:** Tailwind CSS for fixed positioning and animations.
- **Interactivity:** AlpineJS for state management (`open` state) and transitions.

## 5. Success Criteria
- Tapping "Profile" on mobile opens the menu sheet smoothly.
- Menu items are clearly grouped and legible.
- Backdrop correctly dimisses the menu.
- Admin items only appear for authorized users.
- Desktop layout remains unaffected.
