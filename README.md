# SiteSphere

> Discover, review, and rate the best development tools and resources — all in one place.

![PHP](https://img.shields.io/badge/PHP-8.3+-777BB4?logo=php&logoColor=white)
![Laravel](https://img.shields.io/badge/Laravel-13-FF2D20?logo=laravel&logoColor=white)
![Tailwind CSS](https://img.shields.io/badge/Tailwind-3-38BDF8?logo=tailwind-css&logoColor=white)

---

## 📸 Screenshots

### Homepage
![Homepage](screenshots/homepage.png)

### Login & Register
| Login | Register |
|-------|----------|
| ![Login](screenshots/login.png) | ![Register](screenshots/register.png) |

### Home Feed
![Home Feed](screenshots/home-feed.png)

### Admin Dashboard
![Admin Dashboard](screenshots/admin-dashboard.png)

### Admin User Management
![Admin Users](screenshots/admin-users.png)

### Create Post
![Create Post](screenshots/create-post.png)

### Edit Profile
![Edit Profile](screenshots/edit-profile.png)

### Appearance Settings
![Appearance](screenshots/appearance.png)

### About Page
![About](screenshots/about.png)

### Mobile View
![Mobile Home](screenshots/mobile-home.png)

---

## ✨ Features

- 🔍 **Browse & Search** — Find dev tools by category, tag, or keyword
- ⭐ **Rate & Review** — Share your experience with resources
- 👤 **User Profiles** — Track your activity and contributions
- 🏷️ **Tag System** — Filter resources by technology
- 🌙 **Dark/Light Theme** — Comfortable viewing in any environment
- 🔐 **OAuth Login** — Sign in with Google or GitHub
- 📧 **Email Notifications** — Stay updated on activity
- 👑 **Admin Dashboard** — Manage users, content, and categories
- 📱 **Responsive Design** — Works on desktop, tablet, and mobile

---

## 🚀 Quick Start

See **[INSTALL.md](INSTALL.md)** for detailed setup instructions.

### TL;DR

```bash
# Extract and setup
unzip sitesphere.zip
cd sitesphere
composer install
npm install
cp .env.example .env
php artisan key:generate

# Database
touch database/database.sqlite
php artisan migrate --seed

# Build and run
npm run build
php artisan serve
```

Visit **http://localhost:8000**

---

## 👥 Default Accounts

| Role  | Email                | Password |
|-------|----------------------|----------|
| Admin | admin@sitesphere.com | password |
| User  | user@sitesphere.com  | password |

---

## 🛠 Tech Stack

| Layer    | Technology                        |
|----------|-----------------------------------|
| Backend  | PHP 8.3+, Laravel 13              |
| Frontend | Tailwind CSS 3, Alpine.js, Vite   |
| Database | SQLite (dev), MySQL (prod)        |
| Auth     | Laravel Breeze, Socialite         |
| Email    | Resend                            |
| UI       | Flowbite, SweetAlert2, FontAwesome|

---

## 👥 Team

| Name | Role | Contribution |
|------|------|--------------|
| <!-- Your Name --> | **Team Leader / Full-Stack** | Backend development, frontend compilation, project architecture |
| <!-- Member 2 --> | Frontend Developer | UI components, responsive design |
| <!-- Member 3 --> | Frontend Developer | UI components, styling |
| <!-- Member 4 --> | Frontend Developer | UI components, interactions |

> 💡 *Fill in your team members' names and specific contributions above.*

---

## 📄 License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file.

---

## 👨‍💻 Author

**SiteSphere** — PHP Final Project
