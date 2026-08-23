# Wollo University Lost & Found — Backend REST API

The backend for the **Wollo University Lost & Found Property Management System (WU-LFMS)** is built with **Laravel 13 on PHP 8.3+** and **MySQL 8.0**, following an API-first decoupled architecture.

---

## 🛠️ Tech Stack & Key Components
- **Framework**: Laravel 13.x
- **PHP Version**: 8.3+
- **Database**: MySQL 8.0+ (InnoDB) / SQLite (Testing)
- **Auth**: Laravel Sanctum SPA Session-Cookie with automatic CSRF management
- **Queue Driver**: Database (`jobs` table with automated retries)
- **Email Engine**: Transactional Blade email templates (`resources/views/emails/`)
- **Testing**: PHPUnit / Laravel Test Suite (78 Feature & Unit Tests)

---

## 🚀 Setup & Installation

```bash
# 1. Copy environment configuration
cp .env.example .env

# 2. Install dependencies
composer install

# 3. Generate key
php artisan key:generate

# 4. Run database migrations & seeders
php artisan migrate --seed

# 5. Create storage symlink
php artisan storage:link

# 6. Start the API server
php artisan serve --port=8000
```

---

## 🧪 Testing

```bash
# Execute automated test suite
php artisan test

# Verify all 94 registered routes
php artisan route:list

# Clear optimization caches
php artisan optimize:clear
```

---

## 🔒 Security Architecture
- **Brute-Force Lockout (FR-03)**: Automatically locks user accounts for 30 minutes after 5 consecutive failed login attempts.
- **Pessimistic Concurrency Locking**: Uses `lockForUpdate()` during claim reviews to prevent concurrent double-approvals.
- **Role-Based Access Control (RBAC)**: Managed via `EnsureUserHasRole` middleware supporting `student`, `staff`, and `admin` roles.
- **Audit Logging**: Every property update, claim decision, and return is permanently recorded in `audit_logs`.
