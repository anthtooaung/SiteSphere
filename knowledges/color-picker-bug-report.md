# Color Picker — Bug & Design Issue Report

> **Scope:** `resources/views/layout/menu/appearance.blade.php` → `x-color-picker` component
> **Component file:** `resources/views/components/color-picker.blade.php`
> **Styles:** `resources/css/appearance.css` (lines 913–1228)
> **Inspected via:** Chrome DevTools (System Chrome inspection)

---

## Overview

The appearance page exposes three `x-color-picker` instances inside the **Custom Theme** panel (Background, Text, Accent). When the custom-theme toggle is enabled and a user opens any color picker, **multiple visual, functional, and UX bugs** appear.

---

## Bug #1 — Popup Positioning Breaks Inside Scrollable Container

### What happens
When the page is scrolled down and the user clicks the color swatch, the popup appears anchored to the **viewport** (`position: fixed`) using coordinates from `getBoundingClientRect()`. However, `document.body.style.overflow = 'hidden'` is applied *after* measuring the swatch position, which can cause layout shift and re-anchoring at the wrong pixel position.

### Root cause
```js
// color-picker.blade.php — positionPopup()
let top = rect.bottom + 8;
this.popupStyle = 'position:fixed;top:' + top + 'px;left:' + left + 'px;z-index:10000;';
```
The position is computed **once** and never recalculated on scroll or resize. Setting `overflow: hidden` on `<body>` after calculating the position can shift the layout, making the popup appear displaced — especially on smaller viewports.

### Visual result in Chrome
Popup appears offset from the swatch button, sometimes clipped by the viewport edge or overlapping the header.

---

## Bug #2 — Hue Knob Visual is Decorative Only (Not Draggable)

### What happens
There are **two overlapping elements** for the hue slider:
- A visual `.color-picker-hue-knob` (`<span>`) — the circle you see
- A transparent `<input type="range" class="color-picker-hue-input">` — the actual interactive element

The `hue-input` range input is `opacity: 0` and positioned `inset: -8px 0` (extends 8px above and below the track). In Chrome, when the user tries to drag the **visible knob**, they may miss the hidden range input hitbox, causing the hue to not respond.

### Root cause (CSS)
```css
/* appearance.css L1130–1138 */
.color-picker-hue-input {
    position: absolute;
    inset: -8px 0;
    width: 100%;
    opacity: 0;
    cursor: pointer;
}
```
The invisible range input and visible knob are **not in sync on first render** — the hidden thumb position defaults to the left while the knob is positioned via `:style="hueKnobStyle"`. The range input's thumb has no size matching logic, so the draggable area doesn't visually match the knob in Chrome.

### Visual result in Chrome
Clicking the knob circle does nothing; the user must click the track line precisely. The knob appears to jump when the value finally changes.

---

## Bug #3 — Popup Does Not Reposition on Window Resize

### What happens
Once the popup is open and the user resizes the browser window or switches screen orientation, the popup stays at the original `position:fixed` coordinates, going off-screen or overlapping other UI.

### Root cause
There is no `resize` or `scroll` event listener to call `positionPopup()` again while `open === true`.

---

## Bug #4 — `@click.outside` and Backdrop Conflict (Double Close Logic)

### What happens
The color picker component has **two separate close mechanisms**:
1. `@click.outside="open = false"` on the outer div (Alpine.js directive)
2. A `.color-picker-backdrop` div that calls `closePanel()` on click

When clicking the backdrop, both handlers fire:
- The backdrop fires `closePanel()` → sets `open = false`, removes `overflow: hidden`
- AlpineJS's `@click.outside` simultaneously fires → sets `open = false` again

The two mechanisms have **inconsistent teardown** — `closePanel()` resets `showCustom = false`, but `@click.outside` does NOT. So clicking outside while in the custom picker view vs. clicking the backdrop produces different teardown states.

---

## Bug #5 — Color Watcher Fires on Init (Duplicate Dispatch)

### What happens
In `x-init`:
```js
$watch('color', val => { $dispatch('color-change', { name: '{{ $name }}', value: val }); })
```
This watcher runs immediately on **first Alpine.js init** if the color value changes during the reactive setup cycle. Combined with the appearance page's `@color-change.window` listener that updates `customBackground`, `customText`, and `customAccent`, this triggers `applyRootTheme()` **before the user has interacted**, silently overwriting the initial theme values with the picker's default state.

---

## Bug #6 — `hexToRgb()` Does Not Validate Input — Can Return `NaN`

### What happens
If `this.color` contains an invalid hex (e.g. empty string, or a short 3-char hex like `#fff`), `hexToRgb()` produces `NaN` values:

```js
hexToRgb(hex) {
    hex = hex.replace('#', '');
    return {
        r: parseInt(hex.slice(0, 2), 16),  // returns NaN if hex is '' or length < 6
        g: parseInt(hex.slice(2, 4), 16),
        b: parseInt(hex.slice(4, 6), 16)
    };
}
```

`NaN` propagates through `rgbToHsv()` → all HSV values become `NaN` → the SV area gradient and knob disappear from the custom picker view.

### Trigger path
1. User types in hex field and deletes all characters
2. `onHexInput()` only applies when `/^#[0-9a-fA-F]{6}$/` matches — but `initPicker()` is called with `this.color` which may still hold the stale invalid value

---

## Bug #7 — SV Area Pointer Capture Not Released on Panel Close

### What happens
`onSvPointerDown()` calls `this.$refs.svArea.setPointerCapture(e.pointerId)`. This correctly captures future pointer events. However, when `closePanel()` is called while `svDragging === true`, the pointer capture is **never released**. On the next open, the element may still hold the capture, preventing interaction.

### Root cause
```js
closePanel() {
    this.open = false;
    this.showCustom = false;
    document.body.style.overflow = '';
    // Missing: this.svDragging = false; or releasePointerCapture()
}
```

---

## Bug #8 — Custom Panel Color Pickers Remain Operable When Disabled

### What happens
When `useCustomTheme === false`, the panel gets `is-disabled` class (via `:class="{ 'is-disabled': !useCustomTheme }"`). However, the CSS class only provides **visual dimming** — no `pointer-events: none` or `disabled` attribute is applied. The `<input>` and `<button>` elements inside remain fully interactive.

### Root cause — missing guard in appearance.blade.php (lines 265–285)
The preset theme inputs correctly receive `:disabled="useCustomTheme"`:
```html
<!-- appearance.blade.php L252 -->
:disabled="useCustomTheme"
```
But the custom panel color pickers lack the equivalent guard. The `x-color-picker` components are rendered without any disabled binding, so a user can open the color picker popup even when the custom theme is off, and their color changes silently do not apply.

---

## Design Issues (Chrome Visual Inspection)

### D1 — Popup Has No Blur/Depth Layer in Dark Mode
The popup uses `background: var(--background-color, #ffffff)` which adapts correctly, but there is **no backdrop-filter blur** or glassmorphism. On dark mode the popup feels flat and low-contrast against the background card.

**Fix suggestion:** Add `backdrop-filter: blur(12px)` + slightly transparent background to `.color-picker-popup`.

### D2 — Palette Swatches Too Small on Touch Devices
`.color-picker-palette-swatch` renders at approximately 20×20px in the 280px popup. On touch, the tap target is below the recommended 44×44px minimum. Chrome DevTools mobile simulation shows frequent mis-taps.

### D3 — No Current Color Preview Inside Popup Header
The popup header shows only "Select Color" text. There is **no live preview swatch** showing the current vs. selected color side-by-side (standard UX in Figma, Chrome DevTools, etc.).

### D4 — Hex Field Has No `#` Prefix Hint
The hex field inside the custom picker shows the value without any visible placeholder. When a user deletes and re-types, there is no cue that a `#` prefix is required. `onHexInput()` adds it programmatically, but the UX is confusing.

### D5 — AlpineJS v3 Transition Attribute Selectors Never Match → No Animation
```css
/* appearance.css L1220–1228 */
[x-transition\:enter].popup-enter { opacity: 0; transform: translateY(-4px) scale(0.98); }
[x-transition\:leave].popup-leave { opacity: 0; transform: translateY(-4px) scale(0.98); }
```
AlpineJS v3 applies its own internal generated classes during transitions. These custom `[x-transition\:enter]` attribute CSS selectors **never actually match** in Chrome — so the popup appears and disappears instantly with no animation, despite the code implying otherwise.

**Fix:** Use AlpineJS v3's proper transition directives:
```html
x-transition:enter="transition ease-out duration-150"
x-transition:enter-start="opacity-0 scale-95 -translate-y-1"
x-transition:enter-end="opacity-100 scale-100 translate-y-0"
x-transition:leave="transition ease-in duration-100"
x-transition:leave-start="opacity-100 scale-100 translate-y-0"
x-transition:leave-end="opacity-0 scale-95 -translate-y-1"
```

---

## Summary Table

| # | Type   | Severity | Location | Summary |
|---|--------|----------|----------|---------|
| 1 | Bug    | 🔴 High  | `positionPopup()` | Popup mispositioned when page is scrolled + `overflow:hidden` layout shift |
| 2 | Bug    | 🟡 Medium | Hue slider | Hue knob visual not aligned with actual range input hitbox |
| 3 | Bug    | 🟡 Medium | `positionPopup()` | Popup not repositioned on window resize |
| 4 | Bug    | 🟡 Medium | Backdrop + `@click.outside` | Dual close mechanisms with inconsistent teardown state |
| 5 | Bug    | 🟡 Medium | `$watch('color')` | Color watcher dispatches on init, triggers premature `applyRootTheme()` |
| 6 | Bug    | 🟡 Medium | `hexToRgb()` | NaN propagation when hex input is empty or short |
| 7 | Bug    | 🟢 Low   | `onSvPointerDown()` | Pointer capture not released on `closePanel()` |
| 8 | Bug    | 🔴 High  | Custom panel | Color pickers remain clickable when `useCustomTheme === false` |
| D1 | Design | 🟢 Low  | Popup | No backdrop-filter blur in dark mode |
| D2 | Design | 🟡 Medium | Palette | Swatch tap targets too small for touch (≈20px vs 44px recommended) |
| D3 | Design | 🟢 Low  | Popup header | No current vs. new color preview strip |
| D4 | Design | 🟢 Low  | Hex field | No `#` placeholder or prefix hint in custom picker |
| D5 | Design | 🔴 High  | Transitions | AlpineJS v3 `[x-transition\:enter]` CSS selectors never match → no animation |

---

## Files to Fix

| File | Lines to Address |
|------|-----------------|
| `resources/views/components/color-picker.blade.php` | `positionPopup()`, `closePanel()`, `hexToRgb()`, `$watch`, `toggle()`, transition directives |
| `resources/css/appearance.css` | Lines 913–1228 (hue input hitbox, popup backdrop, transitions, palette swatch size) |
| `resources/views/layout/menu/appearance.blade.php` | Lines 265–285 — add `:disabled` / pointer-events guard to custom panel color pickers |
