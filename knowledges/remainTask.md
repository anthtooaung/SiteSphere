# SiteSphere Button Actions & Database Interactions

## Overview
This document maps every button click action and its corresponding database interaction across the SiteSphere platform.

## Page-by-Page Actions

### Public & Auth Pages

#### Global Navigation & Footer
- **[Login / Register] / [Profile Menu]** - Navigation Bar
  - **Type:** Navigation / Dropdown Toggle
  - **Action:** Opens the login/register modals or the authenticated user profile dropdown menu.
  - **DB Interaction:** None (UI action).

- **Mobile Bottom Nav Items** - Mobile Navigation Bar
  - **Type:** Navigation
  - **Action:** Navigates to Home, Create Post, Notifications, or Profile.
  - **DB Interaction:** None directly (triggers page load).

- **Footer Links (Create Post, About, Social Links)** - Footer
  - **Type:** Navigation
  - **Action:** Opens new pages or external social media profiles (LinkedIn, Telegram, GitHub).
  - **DB Interaction:** None.

#### Welcome Page (`/`)
- **[Explore]** - Hero Section
  - **Type:** Navigation
  - **Action:** Scrolls down to the Most Reviewed Websites section.
  - **DB Interaction:** None

- **[See More!]** - Most Reviewed Websites Section
  - **Type:** Navigation
  - **Action:** Redirects to the Home (`/home`) page.
  - **DB Interaction:** None

- **[Submit]** - Contact Form Section
  - **Type:** Mutation
  - **Action:** Submits the contact form.
  - **DB Interaction:** Send email via `ContactMessageController@store` (No database mutation).

#### Auth Pages (`/login`, `/register`, `/forgot-password`, `/reset-password`)
- **[Login]** - Login Form
  - **Type:** Mutation
  - **Action:** Authenticates user.
  - **DB Interaction:** Read from `Users`.

- **[Register]** - Registration Form
  - **Type:** Mutation
  - **Action:** Creates a new user account.
  - **DB Interaction:** Write to `Users`.

- **[Continue with Google / GitHub]** - Social Login
  - **Type:** Mutation
  - **Action:** Authenticates or registers the user via Socialite.
  - **DB Interaction:** Read/Write `SocialAccounts` and `Users`.

- **[Continue Profile / Skip Profile]** - Registration Flow
  - **Type:** Navigation / Mutation
  - **Action:** Updates profile details or skips to the next step.
  - **DB Interaction:** Write to `Users` (Profile details).

- **[Verify OTP]** - Registration / 2FA / Password Reset
  - **Type:** Mutation
  - **Action:** Verifies the one-time password.
  - **DB Interaction:** Read/Write to `OtpVerifications`.

- **[Resend OTP]** - OTP Verification Step
  - **Type:** Mutation
  - **Action:** Resends the OTP email.
  - **DB Interaction:** Write to `OtpVerifications`.

- **[Send OTP / Reset Password]** - Forgot Password Flow
  - **Type:** Mutation
  - **Action:** Initiates password reset process or finalizes new password.
  - **DB Interaction:** Write to `OtpVerifications`, Update `Users`.

- **[showRegister] / [showLogin]** - Auth Modals/Toggles
  - **Type:** Navigation
  - **Action:** Toggles between login and registration views (often via JS).
  - **DB Interaction:** None.

- **[confirmRegisterBtn]** - Final Registration Step
  - **Type:** Mutation
  - **Action:** Submits the complete registration form.
  - **DB Interaction:** Write to `Users`.

- **[backToProfileBtn] / [closeRegistrationFlow]** - Registration UI
  - **Type:** Navigation
  - **Action:** Navigates back a step or dismisses the registration flow overlay.
  - **DB Interaction:** None.

- **[Show password]** - Auth Inputs
  - **Type:** UI Toggle
  - **Action:** Toggles visibility of the password input field.
  - **DB Interaction:** None.

#### Home Page (`/home`)
- **[Menu] / [sidebarToggle]** - Sidebar Toggles
  - **Type:** Navigation
  - **Action:** Toggles the sidebar menu via JS.
  - **DB Interaction:** None.

- **[showCategoryBtn]** - Categories Toggle
  - **Type:** UI Toggle
  - **Action:** Expands or collapses the category filter list.
  - **DB Interaction:** None.

- **Author Profile Link** - Post Card
  - **Type:** Navigation
  - **Action:** Navigates to the author's public profile (`/profile/{slug}`).
  - **DB Interaction:** None.

- **[Read Reviews] / [Visit Website]** - Post Card
  - **Type:** Navigation
  - **Action:** Redirects to the Post Detail page or opens the external website link in a new tab.
  - **DB Interaction:** None.

- **[Clear All Filters]** - Filters Section
  - **Type:** Filter
  - **Action:** Clears active category, tag, or rating filters via AlpineJS.
  - **DB Interaction:** None (Client-side trigger, may result in AJAX Read for `Posts`).

- **[Load More / Infinite Scroll]** - Posts Feed
  - **Type:** Navigation / Filter
  - **Action:** Loads the next page of posts.
  - **DB Interaction:** Read from `Posts`, `Users`, `Bookmarks`, `Categories`.

#### Post Detail Page (`/posts/{slug}`)
- **[Bookmark]** - Post Actions
  - **Type:** Mutation
  - **Action:** Saves or removes a post from user's bookmarks.
  - **DB Interaction:** Write to `Bookmarks`.

- **[Report]** - Post Actions
  - **Type:** Mutation
  - **Action:** Submits a report for moderation.
  - **DB Interaction:** Write to `Reports`.

- **[Ban]** - Post Actions (Admin)
  - **Type:** Mutation
  - **Action:** Bans the post/user.
  - **DB Interaction:** Update `Posts` or `Users`.

- **[Post Comment]** - Comments Section
  - **Type:** Mutation
  - **Action:** Adds a comment (and optionally a rating) to the post.
  - **DB Interaction:** Write to `Comments`, `Ratings`.

- **[React / Like Comment]** - Comment Actions
  - **Type:** Mutation
  - **Action:** Toggles a reaction (like) on a comment.
  - **DB Interaction:** Write/Delete from `CommentReactions`.

- **[See more]** - Comment Toggles
  - **Type:** UI Toggle
  - **Action:** Expands truncated long comments to show full text.
  - **DB Interaction:** None.

#### Profile Detail Page (`/profile/{slug?}`)
- **[Edit Profile]** - Profile Header
  - **Type:** Navigation
  - **Action:** Redirects to Edit Profile page.
  - **DB Interaction:** None.

- **[View Profile (Message)]** - Profile Card
  - **Type:** Navigation
  - **Action:** Redirects to the specific user's profile detail page.
  - **DB Interaction:** None.

- **Profile Links (Email, Phone, "View all reviews")** - Profile Card
  - **Type:** Navigation
  - **Action:** Opens the email client, initiates a phone call, or filters the profile to show all user reviews.
  - **DB Interaction:** None (Client-side triggers or mailto/tel links).
