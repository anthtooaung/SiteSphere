# 🚀 SiteSphere - Quick Start Guide

## Requirements

- PHP 8.3 or higher
- Composer
- Node.js 18+ and npm

## Installation

### 1. Extract the ZIP file

```bash
unzip sitesphere.zip
cd sitesphere
```

### 2. Install PHP dependencies

```bash
composer install
```

### 3. Install Node dependencies

```bash
npm install
```

### 4. Set up environment

```bash
cp .env.example .env
php artisan key:generate
```

### 5. Create database and run migrations

```bash
touch database/database.sqlite
php artisan migrate --seed
```

### 6. Build frontend assets

```bash
npm run build
```

### 7. Start the server

```bash
php artisan serve
```

Visit **http://localhost:8000** in your browser.

---

## Default Accounts (after seeding)

| Role    | Email                   | Password |
|---------|-------------------------|----------|
| Admin   | admin@sitesphere.com    | password |
| User    | user@sitesphere.com     | password |

---

## Features

- 🔍 Browse and search dev tools & resources
- ⭐ Rate and review resources
- 👤 User profiles with activity history
- 🏷️ Tag-based filtering
- 🌙 Dark/Light theme toggle
- 🔐 Google & GitHub OAuth login
- 📧 Email notifications
- 👑 Admin dashboard

---

## Troubleshooting

**"Could not find driver" error:**
```bash
sudo apt install php-sqlite3
# or
sudo apt install php-mysql
```

**Permission errors:**
```bash
chmod -R 775 storage bootstrap/cache
```

**npm build fails:**
```bash
rm -rf node_modules package-lock.json
npm install
npm run build
```

---

## License

MIT License - see [LICENSE](LICENSE) file.
