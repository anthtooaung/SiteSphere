# SiteSphere

> Discover, review, and rate the best development tools and resources — all in one place.

![Version](https://img.shields.io/badge/version-1.0.0-blue.svg)
![PHP](https://img.shields.io/badge/PHP-8.3+-777BB4?logo=php&logoColor=white)
![Laravel](https://img.shields.io/badge/Laravel-13-FF2D20?logo=laravel&logoColor=white)
![Tailwind CSS](https://img.shields.io/badge/Tailwind-3-38BDF8?logo=tailwind-css&logoColor=white)
[![License: MIT](https://img.shields.io/badge/License-MIT-yellow.svg)](https://opensource.org/licenses/MIT)

---

## 📑 Table of Contents

- [📸 Screenshots](#-screenshots)
- [✨ Features](#-features)
- [📋 Prerequisites](#-prerequisites)
- [🚀 Quick Start](#-quick-start)
- [⚙️ Environment Variables](#️-environment-variables)
- [🧪 Testing](#-testing)
- [🌐 Live Website](#-live-website)
- [🛠 Tech Stack](#-tech-stack)
- [👥 Team](#-team)
- [🤝 Contributing](#-contributing)
- [📄 License](#-license)

---

## 📸 Screenshots

### Desktop
<table>
  <tr>
    <td align="center"><b>Homepage</b></td>
    <td align="center"><b>Registration</b></td>
    <td align="center"><b>OTP</b></td>
  </tr>
  <tr>
    <td><img src="screenshots/desktop/user_homepage.png" width="300"></td>
    <td><img src="screenshots/desktop/Registration.png" width="300"></td>
    <td><img src="screenshots/desktop/OTP.png" width="300"></td>
  </tr>
  <tr>
    <td align="center"><b>Admin Dashboard</b></td>
    <td align="center"><b>Admin Reports</b></td>
    <td align="center"><b>Create Post</b></td>
  </tr>
  <tr>
    <td><img src="screenshots/desktop/Admin_Dashboard.png" width="300"></td>
    <td><img src="screenshots/desktop/Admin_Reports.png" width="300"></td>
    <td><img src="screenshots/desktop/create_post.png" width="300"></td>
  </tr>
  <tr>
    <td align="center"><b>Security Profile</b></td>
    <td align="center"><b>Appearance</b></td>
    <td align="center"><b>Saved Posts</b></td>
  </tr>
  <tr>
    <td><img src="screenshots/desktop/user_security.png" width="300"></td>
    <td><img src="screenshots/desktop/user_appearance.png" width="300"></td>
    <td><img src="screenshots/desktop/user_saved_post.png" width="300"></td>
  </tr>
</table>

### Mobile
*(Mobile screenshots coming soon)*

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

## 📋 Prerequisites

Before you begin, ensure you have met the following requirements:
- **PHP** 8.3 or higher
- **Composer** (Dependency Manager for PHP)
- **Node.js** & **npm**
- **SQLite** (default for development) or **MySQL**

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

## ⚙️ Environment Variables

To run this project, you will need to add the following key environment variables to your `.env` file (especially for OAuth and Emails):

- `GITHUB_CLIENT_ID` / `GITHUB_CLIENT_SECRET`
- `GOOGLE_CLIENT_ID` / `GOOGLE_CLIENT_SECRET`
- `RESEND_KEY`

---

## 🧪 Testing

To run the automated test suite and ensure code quality:

```bash
# Run tests
composer run test

# Run code style checks (Laravel Pint)
./vendor/bin/pint
```

---

## 🌐 Live Website

**[https://sitesphere-production.site](https://sitesphere-production.site)**

---

## 🛠 Tech Stack

| Layer      | Technology                                    |
|------------|-----------------------------------------------|
| Backend    | PHP 8.3+, Laravel 13                          |
| Frontend   | Tailwind CSS 3, Alpine.js, Vite               |
| Database   | SQLite (dev), MySQL (prod)                    |
| Auth       | Laravel Breeze, Socialite (Google, GitHub)    |
| Email      | Resend                                        |
| Real-time  | Pusher, Laravel Echo                          |
| UI         | Flowbite, SweetAlert2, FontAwesome            |
| Detection  | Jenssegers Agent (mobile/desktop)             |

---

## 👥 Team

| Name | Role | Contribution |
|------|------|--------------|
| Ant Htoo Aung | **Team Leader / Full-Stack** | Backend development, frontend compilation, project architecture |
| Hein Aung Kyaw | Frontend Developer | Login/Register, Post Detail, Logo & Loading, Admin Dashboard |
| Eaint Nadi Kyaw | Frontend Developer | Home Page, Admin Users Page |
| Min Hein Ko | Frontend Developer | Welcome Page, Report View, Flow Charts, Use-Case Diagram |
| Lin Thant Aung | Frontend Developer | Profile Settings, Appearance, AG Design, Security Page |
| Sa Kyaw Wai Yan Htet | Frontend Developer | Saved Posts, Post Card Box, Post Upload |
| Han Htoo Lwin | Frontend Developer | Navigation, Footer, Menu Bar, Report Info |
| Zune Myat Noe | Frontend Developer | About Us, Profile Page, Profile Show Box |

---

## 🤝 Contributing

Contributions, issues, and feature requests are welcome!
Feel free to open an issue if you find any bugs or have feature requests.
Please read our [Contributing Guidelines](CONTRIBUTING.md) and [Code of Conduct](CODE_OF_CONDUCT.md) for details on the process for submitting pull requests to us.

---

## 🆘 Support

If you need help or have any questions, feel free to open an issue or reach out to the maintainers.

---

## 📄 License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file.

---

## 👨‍💻 Author

**SiteSphere** — PHP Final Project
