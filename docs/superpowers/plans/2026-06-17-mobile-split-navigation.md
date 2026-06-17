# Mobile Split Navigation Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Implement a split mobile navigation for Welcome and About Us pages, with authentication triggers at the top and primary links at the bottom, all using the laptop's horizontal button design.

**Architecture:** Update individual button components to support a horizontal "laptop-style" on mobile. Modify `nav.blade.php` to conditionally rearrange these components on specific routes.

**Tech Stack:** Laravel (Blade), Tailwind CSS.

---

### Task 1: Update Button Components for Laptop Style

**Files:**
- Modify: `resources/views/components/home-btn.blade.php`
- Modify: `resources/views/components/category-btn.blade.php`
- Modify: `resources/views/components/about-btn.blade.php`
- Modify: `resources/views/components/create-post-btn.blade.php`
- Modify: `resources/views/components/noti-btn.blade.php`

- [ ] **Step 1: Update `home-btn.blade.php`**

Add logic to support horizontal layout on mobile when on landing pages.

```blade
{{-- In home-btn.blade.php --}}
@php
    $isHome = request()->routeIs('home');
    $isLanding = request()->routeIs(['welcome', 'about-us']);
@endphp

{{-- ... @desktop stays same ... --}}

@mobile
<a href="{{ route('home') }}" 
   {{ $attributes->class([
       'mobile-nav-item', 
       'active' => $isHome,
       'flex-row gap-2 px-3 py-2 font-bold text-sm' => $isLanding,
       'flex-col' => !$isLanding
   ]) }} 
   @if($isHome) aria-current="page" @endif>
    <x-fas-home class="icon"/>
    <span>Home</span>
</a>
@endmobile
```

- [ ] **Step 2: Update `category-btn.blade.php`**

Apply similar logic to `category-btn.blade.php`.

- [ ] **Step 3: Update `about-btn.blade.php`**

Apply similar logic to `about-btn.blade.php`.

- [ ] **Step 4: Update `create-post-btn.blade.php`**

Update `create-post-btn.blade.php` to look like a standard link on landing pages rather than a floating circular button.

- [ ] **Step 5: Update `noti-btn.blade.php`**

Update `noti-btn.blade.php` for horizontal layout on landing pages.

- [ ] **Step 6: Commit**

```bash
git add resources/views/components/*.blade.php
git commit -m "feat: update nav buttons to support horizontal laptop-style on landing pages"
```

### Task 2: Implement Split Navigation in `nav.blade.php`

**Files:**
- Modify: `resources/views/components/layout/nav.blade.php`

- [ ] **Step 1: Conditional Top Header**

Update the `@mobile` section of `nav.blade.php` to include the Login button (guest) or Profile button (user) in the header on Welcome/About pages.

- [ ] **Step 2: Conditional Bottom Bar**

Rearrange the bottom navigation items on Welcome/About pages as requested.

- [ ] **Step 3: Commit**

```bash
git add resources/views/components/layout/nav.blade.php
git commit -m "feat: implement split mobile navigation for landing pages"
```

### Task 3: Verification

- [ ] **Step 1: Verify Welcome/About Us (Guest)**
- Header: Logo + Title + Login button.
- Bottom: Home + Category + About (Horizontal).

- [ ] **Step 2: Verify Welcome/About Us (User)**
- Header: Logo + Title + Profile button.
- Bottom: Home + Category + Create New + About + Alert (Horizontal).

- [ ] **Step 3: Verify Home Page (Remains standard mobile nav)**
- Top Header: Logo + Title.
- Bottom: Standard vertical buttons.
