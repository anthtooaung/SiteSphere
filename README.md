# SiteSphere

> Discover, review, and rate the best development tools and resources — all in one place.

![PHP](https://img.shields.io/badge/PHP-8.3+-777BB4?logo=php&logoColor=white)
![Laravel](https://img.shields.io/badge/Laravel-13-FF2D20?logo=laravel&logoColor=white)
![Tailwind CSS](https://img.shields.io/badge/Tailwind-3-38BDF8?logo=tailwind-css&logoColor=white)

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

## 📸 Screenshots

<!-- Add your screenshots here -->
*Screenshots coming soon...*

---

## 📄 License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file.

---

## 👨‍💻 Author

**SiteSphere** — PHP Final Project
