# SiteSphere

SiteSphere is a modern web platform built with Laravel 13, designed for discovering and reviewing development resources, tools, and libraries. It features a rich user experience with customizable themes, fonts, and a robust reporting and notification system.

## 🚀 Project Overview

- **Core Technology:** Laravel 13 (PHP 8.3+)
- **Frontend Stack:** Tailwind CSS, AlpineJS, Flowbite, Vite
- **Authentication:** Laravel Breeze with Socialite support (Google, GitHub)
- **Architecture:** Standard Laravel MVC with enhanced Model logic (attributes, lifecycle hooks) and View Composers for global state management.
- **Key Features:**
    - Resource discovery with category/tag filtering.
    - User reviews, ratings, and comment reactions.
    - Personalization: Custom tags, accent colors, and font preferences.
    - Admin Panel: User management, content moderation (reports), and tag/category management.

## 🛠 Building and Running

### Prerequisites
- PHP 8.3 or higher
- Composer
- Node.js & NPM
- SQLite (default) or MySQL

### Setup Commands
```bash
# Install PHP and JS dependencies, set up environment, and build assets
composer run setup
```

### Development
```bash
# Run the development environment (Server, Queue, Vite, Logs)
composer run dev
```

### Testing and Linting
```bash
# Run tests
composer run test

# Run code style checks (Laravel Pint)
./vendor/bin/pint
```

## 📝 Development Conventions

- **Models:** Use modern PHP features like `#[Fillable]` and `#[Hidden]` attributes. Implement business logic within models where appropriate (e.g., `provisionDefaultPreferences`).
- **Routing:** Prefer controller-based routing. Use slug-based route model binding for public-facing entities like Posts and Users.
- **Frontend:**
    - Use Tailwind CSS utility classes for styling.
    - Use AlpineJS for lightweight client-side interactivity.
    - Follow the multi-entry Vite configuration for view-specific assets located in `resources/css` and `resources/js`.
- **Global State:** Global view data (themes, preferences) is managed via `AppServiceProvider` using View Composers.
- **Security:** Always use Laravel's built-in protection (CSRF, Middleware). 2FA is supported and should be maintained.
- **Clean Code:** Adhere to Laravel Pint's styling rules.

## 📂 Key Directories

- `app/Models`: Core business entities with rich logic.
- `app/Http/Controllers`: Route handlers, often using `__invoke` for single-purpose controllers.
- `resources/views`: Blade templates, organized by feature/layout.
- `database/migrations`: Comprehensive schema definition including soft deletes and complex relationships.
- `tests/`: Automated tests (Feature and Unit).

## Rules
- IF Token usage reach the **20%** then stop the current processing task and write the documentation in the `knowledges` folder of `remainTask.md` file
- IF Token usage reach the **95%** then switch the model to either `gemini-3.1-pro` or `gemini-3.5-flash` for the remainder of the task.
- When adding a UI component or changing the design, **always** implement it by considering the dynamic user preferences stored in the database (e.g., custom themes, accent colors, menu locations, font families, dark mode). Avoid hardcoding styles that would break personalization.