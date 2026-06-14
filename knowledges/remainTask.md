# SiteSphere Button Actions & Database Interactions

## Overview
This document maps every button click action and its corresponding database interaction across the SiteSphere platform.

## Page-by-Page Actions

### Public & Auth Pages

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
  - **DB Interaction:** Write to `ContactMessages` / Send email via `ContactMessageController@store`.

#### Auth Pages (`/login`, `/register`, `/forgot-password`, `/reset-password`)
- **[Login]** - Login Form
  - **Type:** Mutation
  - **Action:** Authenticates user.
  - **DB Interaction:** Read from `Users`.

- **[Register]** - Registration Form
  - **Type:** Mutation
  - **Action:** Creates a new user account.
  - **DB Interaction:** Write to `Users`.

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

#### Home Page (`/home`)
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

#### Profile Detail Page (`/profile/{slug?}`)
- **[Edit Profile]** - Profile Header
  - **Type:** Navigation
  - **Action:** Redirects to Edit Profile page.
  - **DB Interaction:** None.

- **[Give rating / View all ratings]** - Stats Section
  - **Type:** Navigation
  - **Action:** Redirects to the Home page to browse ratings.
  - **DB Interaction:** None.