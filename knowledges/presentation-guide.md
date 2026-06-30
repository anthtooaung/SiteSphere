# SiteSphere — Presentation Guide & PowerPoint Prompts

> A complete reference for building a consistent, professional presentation that mirrors SiteSphere's visual identity and project flow.

---

## 1. Brand Identity & Theme

### Color Palette (Must Match the Application)

| Role | Hex | Usage |
|------|-----|-------|
| **Primary / Accent** | `#6c5ce7` | Headings, CTA buttons, highlights, chart lines |
| **Dark Background** | `#0d1b2a` | Slide backgrounds (dark mode), nav bars |
| **Light Background** | `#ffffff` | Card backgrounds, content areas |
| **Surface / Gray** | `#f1f5f9` | Subtle section dividers, secondary cards |
| **Text Primary** | `#0d1b2a` | Body text on light slides |
| **Text Light** | `#e2e8f0` | Body text on dark slides |
| **Accent Red** | `#DC2626` | Alerts, ban actions, destructive operations |
| **Accent Gold** | `#f4c543` | Ratings stars, warnings |
| **Accent Green** | `#059669` | Success states, active/verified badges |

### Typography

| Element | Font | Weight | Size |
|---------|------|--------|------|
| Slide Title | Figtree / Inter | 800 | 36–44pt |
| Subtitle | Figtree / Inter | 600 | 20–24pt |
| Body Text | Figtree / Inter | 400 | 16–18pt |
| Caption / Label | Figtree / Inter | 500 | 11–12pt, uppercase, 0.08em letter-spacing |
| Code Snippet | JetBrains Mono / Fira Code | 400 | 14pt |

### Design Primitives

- **Border Radius:** 8px for all cards, buttons, containers
- **Shadows:** Subtle — `0 4px 14px rgba(15, 23, 42, 0.06)` (default), `0 10px 24px rgba(15, 23, 42, 0.10)` (hover/emphasis)
- **Spacing:** 24px padding standard, 16px for compact areas
- **Icons:** FontAwesome 6 (consistent with the project)

---

## 2. Presentation Structure (Slide-by-Slide Flow)

Follow this exact order — it mirrors the user journey through the application.

### Slide 1: Title Slide
- **Content:** SiteSphere logo + tagline
- **Tagline:** "Discover, review, and rate the best development tools and resources — all in one place."
- **Visual:** Dark background (`#0d1b2a`), large centered logo, accent purple glow behind logo
- **Bottom:** Team name, university name, date

### Slide 2: The Problem
- **Title:** "The Problem"
- **Points:**
  - Developers spend hours searching for the right tools
  - No centralized platform for honest, community-driven reviews
  - Existing solutions are cluttered or paywalled
- **Visual:** Split layout — left text, right icon illustration (magnifying glass, question marks)

### Slide 3: Our Solution — SiteSphere
- **Title:** "Introducing SiteSphere"
- **Points:**
  - Community-driven discovery platform for dev tools
  - Star ratings, reviews, and discussions
  - Personalized experience with themes and bookmarks
- **Visual:** Hero screenshot of homepage (`screenshots/homepage.png`)

### Slide 4: Tech Stack Overview
- **Title:** "Built With Modern Technology"
- **Visual:** Grid or flow diagram
  ```
  Frontend:  Tailwind CSS 3 + Alpine.js + Vite + Flowbite
  Backend:   PHP 8.3+ + Laravel 13
  Database:  SQLite (dev) / MySQL (prod)
  Auth:      Laravel Breeze + Socialite (Google, GitHub)
  Email:     Resend
  Real-time: Pusher + Laravel Echo
  ```
- **Visual:** Tech logo grid on dark background, each logo in a rounded card

### Slide 5: System Architecture
- **Title:** "System Architecture"
- **Visual:** Clean architecture diagram:
  ```
  [Browser] → [Laravel App (MVC)] → [MySQL/SQLite]
       ↕              ↕
  [Alpine.js]    [Pusher/Echo]
       ↕              ↕
  [Vite Build]   [Resend Email]
  ```
- **Style:** Use accent purple for arrows, dark navy boxes for services

### Slide 6: Database Design (ERD)
- **Title:** "Database Schema"
- **Visual:** Simplified ERD showing core tables:
  - `users` ←→ `settings`, `social_accounts`
  - `posts` ←→ `user_posts`, `ratings`, `comments`, `bookmarks`, `tags`
  - `categories` ←→ `tags` (pivot)
  - `reports` (polymorphic → users, posts, comments, user_posts)
  - `audit_logs`, `notifications`
- **Note:** Show only 8–10 most important tables, not all 22

### Slide 7: User Journey — Authentication
- **Title:** "Secure Authentication Flow"
- **Flow diagram:**
  ```
  Register → Email OTP → Verify → Complete Profile
  Login → (Optional) 2FA OTP → Dashboard
  OAuth: Google / GitHub → Auto-link → Dashboard
  Banned User → Appeal Page
  ```
- **Screenshot:** Login page (`screenshots/login.png`)
- **Key points:** OTP-based registration, two-factor auth, OAuth support, custom user provider for banned users

### Slide 8: User Journey — Browse & Discover
- **Title:** "Discover Dev Tools"
- **Screenshot:** Home feed (`screenshots/home-feed.png`)
- **Features to highlight:**
  - Category and tag filtering
  - Star rating filter
  - Search by keyword
  - Sort by latest / best rating
  - AJAX infinite pagination
  - Bookmark posts for later

### Slide 9: User Journey — Rate & Review
- **Title:** "Share Your Experience"
- **Screenshot:** Post detail page
- **Features:**
  - 1–5 star ratings with distribution chart
  - Write detailed reviews (one per user per tool)
  - Comment with "helpful" reactions
  - Report inappropriate content
  - Anonymous posting option

### Slide 10: User Journey — Create Post
- **Title:** "Submit a New Tool"
- **Screenshot:** Create post page (`screenshots/create-post.png`)
- **Flow:** Paste URL → Add title & description → Select tags → Submit
- **Key point:** One review per user per URL ensures quality

### Slide 11: Personalization
- **Title:** "Make It Yours"
- **Screenshots:** Appearance page (`screenshots/appearance.png`), Edit profile (`screenshots/edit-profile.png`)
- **Features grid:**
  - Dark / Light mode toggle
  - Preset accent colors (red, gold, green) or custom color picker
  - Google Font selection (10+ fonts)
  - Menu bar position (left, right, top, bottom)
  - Toast notification position
  - Profile customization (avatar, bio, DOB, phone)

### Slide 12: Admin Dashboard
- **Title:** "Powerful Admin Tools"
- **Screenshot:** Admin dashboard (`screenshots/admin-dashboard.png`)
- **Features:**
  - Aggregate statistics (users, reviews, reports)
  - Trend charts (last 10 days, dynamic date filtering)
  - Top posts leaderboard
  - Recent audit log entries

### Slide 13: Admin — User Management
- **Title:** "User Management"
- **Screenshot:** Admin users page (`screenshots/admin-users.png`)
- **Features:**
  - Filter by role, status, join date
  - Ban (soft-delete) / Restore / Permanently delete
  - Toggle admin role
  - Mark as "unsecure"
  - Self-action prevention (admins can't ban themselves)

### Slide 14: Admin — Reports & Moderation
- **Title:** "Content Moderation"
- **Features:**
  - Unified reporting system (posts, comments, users, reviews)
  - Read/unread status tracking
  - Resolve reports with audit trail
  - Unsecure content flagging with visual warnings
  - Complete audit log of all admin actions

### Slide 15: Security Features
- **Title:** "Security & Trust"
- **Feature list:**
  - OTP-based authentication (registration, login, password reset)
  - Two-factor authentication
  - Rate limiting on all sensitive endpoints
  - Role-based access control (15 policy classes)
  - Soft deletes for audit trails
  - OAuth via Google & GitHub
  - Custom user provider for banned user access to appeals

### Slide 16: Email & Notifications
- **Title:** "Stay Connected"
- **Features:**
  - 8 transactional email types (OTP, ban, restore, appeal, etc.)
  - Real-time notifications via Pusher + Laravel Echo
  - Configurable toast notification position
  - Unread notification badge
- **Visual:** Email template preview cards

### Slide 17: Testing & Quality
- **Title:** "Quality Assurance"
- **Points:**
  - 40+ Feature tests with PHPUnit
  - 16 model factories for test data
  - 18 seeders for database population
  - Laravel Pint for code style enforcement
  - Docker containerization for consistent environments
- **Visual:** Terminal-style test output snippet

### Slide 18: Project Statistics
- **Title:** "By the Numbers"
- **Visual:** Large number cards
  ```
  20    Eloquent Models
  40+   Controllers
  45    Database Migrations
  15    Authorization Policies
  40+   Feature Tests
  13    JavaScript Bundles
  21    CSS Stylesheets
  8     Team Members
  ```

### Slide 19: Team
- **Title:** "The Team"
- **Visual:** Team member cards with names and roles
  - Ant Htoo Aung — Team Leader / Full-Stack
  - Hein Aung Kyaw — Frontend (Login, Post Detail, Dashboard)
  - Eaint Nadi Kyaw — Frontend (Home, Admin Users)
  - Min Hein Ko — Frontend (Welcome, Reports, Diagrams)
  - Lin Thant Aung — Frontend (Profile, Appearance, Security)
  - Sa Kyaw Wai Yan Htet — Frontend (Saved Posts, Post Card, Upload)
  - Han Htoo Lwin — Frontend (Navigation, Footer, Menu)
  - Zune Myat Noe — Frontend (About, Profile Page)

### Slide 20: Live Demo
- **Title:** "Live Demo"
- **URL:** https://sitesphere-production.site
- **Credentials:** (if needed for demo)
  - Admin: admin@sitesphere.com / password
  - User: user@sitesphere.com / password
- **Visual:** QR code linking to the live site

### Slide 21: Thank You / Q&A
- **Title:** "Thank You"
- **Content:** SiteSphere logo, "Questions?", team contact info
- **Visual:** Dark background matching slide 1, accent purple glow

---

## 3. PowerPoint Prompts (AI Slide Generator Prompts)

Use these prompts with any AI presentation tool (Gamma, Beautiful.ai, SlidesGPT, ChatGPT + python-pptx, etc.). Each prompt produces one slide that matches SiteSphere's theme.

### Theme Instructions (Prefix for ALL Prompts)

```
Use this exact color scheme for every slide:
- Background: #0d1b2a (dark navy) for title/hero slides, #ffffff (white) for content slides
- Primary accent: #6c5ce7 (purple) for headings, buttons, highlights, chart elements
- Text: #e2e8f0 on dark backgrounds, #0d1b2a on light backgrounds
- Secondary accent: #059669 (green) for success/badges, #DC2626 (red) for alerts, #f4c543 (gold) for ratings
- Border radius: 8px on all cards and containers
- Font: Inter or Figtree, headings weight 800, body weight 400
- Shadows: subtle (0 4px 14px rgba(15,23,42,0.06))
- Icons: FontAwesome 6 style
```

### Slide 1 — Title
```
Create a dark navy (#0d1b2a) title slide. Center the name "SiteSphere" in large white text (44pt, weight 800). Below it, the tagline: "Discover, review, and rate the best development tools and resources — all in one place." in light gray (#e2e8f0, 20pt). Add a subtle purple (#6c5ce7) radial glow behind the title. At the bottom: "PHP Final Project | 2026" in small uppercase text.
```

### Slide 2 — The Problem
```
Create a white (#ffffff) slide titled "The Problem" in dark navy (#0d1b2a, 36pt, weight 800). Left side: three bullet points in dark text — "Developers spend hours searching for the right tools", "No centralized platform for honest community-driven reviews", "Existing solutions are cluttered or paywalled". Right side: a simple illustration area with a magnifying icon and question marks in muted purple (#6c5ce7 at 30% opacity). Use 8px border radius on any containers.
```

### Slide 3 — Introducing SiteSphere
```
Create a slide with dark navy (#0d1b2a) background. Title: "Introducing SiteSphere" in white (36pt, weight 800). Below: three feature bullets in light gray (#e2e8f0) — "Community-driven discovery platform for dev tools", "Star ratings, reviews, and discussions", "Personalized experience with themes and bookmarks". At the bottom, show a large rounded-corner (8px) screenshot placeholder labeled "Homepage" with a subtle purple (#6c5ce7) border. Add a "Powered by Laravel + Tailwind CSS" badge in a small purple pill.
```

### Slide 4 — Tech Stack
```
Create a dark navy (#0d1b2a) slide titled "Built With Modern Technology" in white. Show a 3x3 grid of technology cards. Each card has a white (#ffffff) background, 8px border radius, subtle shadow, centered icon and label. Cards: "PHP 8.3+", "Laravel 13", "Tailwind CSS 3", "Alpine.js", "Vite", "SQLite / MySQL", "Laravel Breeze + Socialite", "Pusher + Echo", "Resend Email". Accent purple (#6c5ce7) for card borders on hover.
```

### Slide 5 — System Architecture
```
Create a white slide titled "System Architecture" in dark navy. Show a clean flow diagram: [Browser] at top connects down to [Laravel MVC] in the center, which connects to [MySQL Database] on the left, [Pusher/Echo] on the right, and [Resend Email] below. Use dark navy (#0d1b2a) boxes with white text for services. Purple (#6c5ce7) arrows connecting them. Alpine.js and Vite shown as side branches from Browser. 8px border radius on all boxes.
```

### Slide 6 — Database Schema
```
Create a white slide titled "Database Schema" in dark navy. Show a simplified ERD with 8 key tables as rounded-corner (8px) boxes: "users" (center), "posts", "user_posts", "ratings", "comments", "tags", "categories", "reports". Lines connecting them with relationship labels (hasMany, belongsTo, polymorphic). Use purple (#6c5ce7) for table headers, light gray (#f1f5f9) for table body. Show "audit_logs" and "notifications" as smaller boxes connected to users.
```

### Slide 7 — Authentication Flow
```
Create a white slide titled "Secure Authentication Flow" in dark navy. Show a vertical flow diagram: Register → Email OTP → Verify OTP → Complete Profile → Dashboard. Branch: Login → (Optional 2FA) → Dashboard. Branch: OAuth (Google/GitHub) → Dashboard. Branch: Banned User → Appeal Page. Use green (#059669) for success arrows, red (#DC2626) for ban path, purple (#6c5ce7) for normal flow. Include a small login page screenshot placeholder. 8px border radius on all flow boxes.
```

### Slide 8 — Home Feed
```
Create a dark navy slide titled "Discover Dev Tools" in white. Show a large rounded (8px) screenshot placeholder labeled "Home Feed" in the center. Around it, show feature badges in small purple (#6c5ce7) pills: "Category Filter", "Tag Filter", "Star Rating Filter", "Keyword Search", "AJAX Pagination", "Bookmarks". Add a gold (#f4c543) star icon next to rating-related features.
```

### Slide 9 — Rate & Review
```
Create a white slide titled "Share Your Experience" in dark navy. Show a post card mockup with: a title, 5 gold (#f4c543) stars (3 filled), a short review text, and a "Write Review" button in purple (#6c5ce7). Below the card: feature list — "1-5 star ratings with distribution chart", "One review per user per tool", "Comment with helpful reactions", "Report inappropriate content", "Anonymous posting option". Use FontAwesome-style icons for each feature.
```

### Slide 10 — Create Post
```
Create a white slide titled "Submit a New Tool" in dark navy. Show a simple 3-step horizontal flow: Step 1: "Paste URL" (with a link icon), Step 2: "Add Title & Description" (with a pencil icon), Step 3: "Select Tags & Submit" (with a check icon). Each step in a purple (#6c5ce7) bordered card with 8px radius. Include a small screenshot placeholder for the create post page. Bottom note: "One review per user per URL ensures quality".
```

### Slide 11 — Personalization
```
Create a dark navy slide titled "Make It Yours" in white. Show a 2x3 grid of feature cards. Each card: white background, 8px radius, subtle shadow. Cards: "Dark/Light Mode" (moon/sun icon), "Custom Accent Colors" (palette icon with red, gold, green, purple dots), "Google Fonts" (font icon), "Menu Bar Position" (arrows icon showing left/right/top/bottom), "Toast Position" (bell icon), "Profile Customization" (user icon). Purple (#6c5ce7) accent on card borders.
```

### Slide 12 — Admin Dashboard
```
Create a white slide titled "Powerful Admin Tools" in dark navy. Show a dashboard mockup with: 4 stat cards at top (Total Users: 1,234 | Reviews: 5,678 | Reports: 42 | Active Today: 89) — each in a white card with purple (#6c5ce7) top border and 8px radius. Below: a line chart area with purple line showing 10-day trend. Include a screenshot placeholder labeled "Admin Dashboard". Use green (#059669) for positive metrics, red (#DC2626) for reports.
```

### Slide 13 — User Management
```
Create a dark navy slide titled "User Management" in white. Show a table mockup with columns: Name, Role, Status, Actions. 3 sample rows. Status badges: "Active" in green (#059669), "Banned" in red (#DC2626), "Unsecure" in gold (#f4c543). Action buttons: "Ban" (red), "Restore" (green), "Make Admin" (purple). Use 8px radius on the table container. Include a screenshot placeholder for admin users page.
```

### Slide 14 — Content Moderation
```
Create a white slide titled "Content Moderation" in dark navy. Show a unified report card mockup: "Report #42 — Target: Post 'Vue.js Tools' — Reason: Spam — Status: Unread". Below: 4 moderation features in purple-bordered cards: "Unified Reports" (posts, comments, users, reviews), "Read/Unread Tracking", "Resolve with Audit Trail", "Unsecure Content Flagging". 8px border radius on all cards.
```

### Slide 15 — Security
```
Create a dark navy slide titled "Security & Trust" in white. Show a vertical list of security features, each with a shield/check icon in green (#059669): "OTP-based authentication", "Two-factor authentication", "Rate limiting (5 req/min)", "15 authorization policies", "Soft deletes for audit trails", "OAuth via Google & GitHub", "Custom user provider for banned access". Use white text with purple (#6c5ce7) icons.
```

### Slide 16 — Notifications
```
Create a white slide titled "Stay Connected" in dark navy. Show two sections: Left: "Email" with 4 email type cards (OTP, Ban, Restore, Appeal) in light gray (#f1f5f9) with 8px radius. Right: "Real-time" with a notification toast mockup — a purple (#6c5ce7) bordered card showing "New comment on your review" with a bell icon. Bottom: "Pusher + Laravel Echo" tech badge in a small pill.
```

### Slide 17 — Testing
```
Create a dark navy slide titled "Quality Assurance" in white. Show a terminal-style mockup with dark background (#0d1b2a border, slightly lighter body): "Tests: 40+ Feature Tests ✓ | Factories: 16 | Seeders: 18 | Code Style: Laravel Pint". Below: "Docker containerization" badge in purple. Use green (#059669) checkmarks, monospace font for terminal text.
```

### Slide 18 — Project Statistics
```
Create a white slide titled "By the Numbers" in dark navy. Show a 4x2 grid of large number cards. Each card: white background, 8px radius, purple (#6c5ce7) top border, large number in purple (48pt, weight 800), label below in dark text. Cards: "20 Models", "40+ Controllers", "45 Migrations", "15 Policies", "40+ Tests", "13 JS Bundles", "21 CSS Files", "8 Team Members".
```

### Slide 19 — Team
```
Create a dark navy slide titled "The Team" in white. Show 8 team member cards in a 4x2 grid. Each card: white background, 8px radius, subtle shadow. Name in dark navy (16pt, weight 700), role in purple (#6c5ce7, 12pt). Cards: Ant Htoo Aung (Team Leader/Full-Stack), Hein Aung Kyaw (Frontend), Eaint Nadi Kyaw (Frontend), Min Hein Ko (Frontend), Lin Thant Aung (Frontend), Sa Kyaw Wai Yan Htet (Frontend), Han Htoo Lwin (Frontend), Zune Myat Noe (Frontend).
```

### Slide 20 — Live Demo
```
Create a white slide titled "Live Demo" in dark navy. Center: large URL "https://sitesphere-production.site" in purple (#6c5ce7, 24pt, clickable style). Below: a QR code placeholder. Bottom: credentials box with 8px radius and light gray background — "Admin: admin@sitesphere.com | User: user@sitesphere.com". Add a "Try it now!" call-to-action in a purple button.
```

### Slide 21 — Thank You
```
Create a dark navy (#0d1b2a) closing slide. Center: "Thank You" in large white text (44pt, weight 800). Below: "Questions?" in light gray (#e2e8f0, 24pt). Add the SiteSphere logo above the text with a subtle purple (#6c5ce7) glow. Bottom: "PHP Final Project | 2026" in small uppercase text. Match the style of the title slide exactly.
```

---

## 4. Slide Design Rules

### Do
- Use dark navy backgrounds for hero/intro slides (1, 3, 4, 8, 11, 13, 15, 17, 19, 21)
- Use white backgrounds for content/detail slides (2, 5, 6, 7, 9, 10, 12, 14, 16, 18, 20)
- Keep consistent 8px border radius on ALL elements
- Use purple (#6c5ce7) as the single primary accent — never mix with other purples
- Use the screenshot images from `/screenshots/` for visual proof
- Keep text concise — max 5 bullet points per slide
- Use FontAwesome icons throughout for visual consistency

### Don't
- Use gradients (flat colors only, matching the app's Tailwind design)
- Use drop shadows heavier than the specified subtle shadow
- Mix font families — stick to Inter/Figtree
- Use more than 3 colors per slide (accent + background + text)
- Add animations or transitions that don't match the app's minimal style
- Use stock photos — use screenshots and icon illustrations only

---

## 5. Export Settings

- **Aspect Ratio:** 16:9 (widescreen)
- **Resolution:** 1920x1080 minimum
- **Format:** .pptx (editable) + .pdf (distribution)
- **File Name:** `SiteSphere-Presentation-2026.pptx`
