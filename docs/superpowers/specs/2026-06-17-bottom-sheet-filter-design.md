# Design Spec: Mobile Bottom-Sheet Filter Drawer

## 1. Overview
The goal is to replace the current desktop-centric sidebar filter layout with a mobile-optimized fixed bottom-sheet drawer. This will improve usability on mobile devices by keeping interactive elements within thumb reach while maintaining spatial context.

## 2. Interaction Design
- **Trigger:** Retains the existing `sidebarToggle` button.
- **Behavior:** On mobile/small-tablet viewports, the filter drawer will slide up from the bottom, occupying 75% of the viewport height.
- **Dismissal:** 
    - Tap on the backdrop (semi-transparent overlay).
    - Tap a dedicated close button in the top-right corner of the sheet.
- **Responsiveness:** Standard desktop behavior (fixed sidebar) remains unchanged for larger breakpoints.

## 3. Visual Specifications
- **Container:** Bottom-fixed with `rounded-t-2xl` for a modern, lifted feel.
- **Backdrop:** Semi-transparent overlay (`bg-black/50`) to focus user attention.
- **Content:** Vertically scrollable container within the bottom sheet to allow for extensive filter lists without affecting the parent page.

## 4. Technical Implementation
- **Markup:** Wrap the content of `resources/views/components/layout/home-aside.blade.php` in a new container optimized for mobile positioning.
- **Styling:** Utilize Tailwind CSS for positioning (`fixed inset-x-0 bottom-0`) and transition classes for the slide-up animation.
- **Interactivity:** Maintain existing AlpineJS state management for filtering to ensure consistency.

## 5. Success Criteria
- Filter drawer opens smoothly via button trigger.
- Drawer height is locked at 75% of viewport height.
- Backdrop tap correctly dismisses the drawer.
- Existing filter functionality remains fully operational.
- Design gracefully reverts to desktop layout on larger screens.
