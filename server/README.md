# Wollo University Lost & Found — Backend REST API

The backend for the **Wollo University Lost & Found Property Management System (WU-LFMS)** is built with **Laravel 13 on PHP 8.3+** and **MySQL 8.0**, following an API-first decoupled architecture.

---

## 🛠️ Tech Stack & Key Components
- **Framework**: Laravel 13.x (^13.17)
- **PHP Version**: 8.3+
- **Database**: MySQL 8.0+ (InnoDB) — primary; SQLite supported only for isolated unit test runs
- **Auth**: Laravel Sanctum SPA Session-Cookie with automatic CSRF management
- **Queue Driver**: Database (`jobs` table with automated retries)
- **Email Engine**: Transactional Blade email templates (`resources/views/emails/`)
- **Testing**: PHPUnit 12.x / Laravel Test Suite (208 tests, 909 assertions as of 2026-09-29 audit)

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

# Verify registered routes
php artisan route:list

# Clear optimization caches
php artisan optimize:clear
```

### Scheduled Commands (registered in routes/console.php)
- `auth:cleanup-expired` — Daily 01:00 (removes expired OTP/verification records)
- `items:expire-inactive` — Daily 02:00 (marks stale unclaimed items as expired)
- `items:send-expiry-warnings` — Daily 08:00 (emails 7-day expiry warning to reporters)
- `reports:cleanup-expired` — Weekly Mon 03:00 (purges CSV/PDF exports older than 7 days)
- `reports:generate-system` — Weekly Mon 04:00 (generates system-wide analytics report)

---

## 🔒 Security Architecture
- **Brute-Force Lockout (FR-03)**: Automatically locks user accounts for 30 minutes after 5 consecutive failed login attempts.
- **Pessimistic Concurrency Locking**: Uses `lockForUpdate()` during claim reviews to prevent concurrent double-approvals.
- **Role-Based Access Control (RBAC)**: Managed via `EnsureUserHasRole` middleware supporting `student`, `staff`, and `admin` roles.
- **Audit Logging**: Every property update, claim decision, and return is permanently recorded in `audit_logs`.
