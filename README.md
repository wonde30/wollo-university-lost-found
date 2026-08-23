<p align="center">
  <img src="client/public/images/wu-logo.png" alt="Wollo University Logo" width="120" onerror="this.style.display='none'"/>
</p>

<h1 align="center">
  Wollo University Digital Lost & Found System
  <br>
  <span style="font-size: 0.7em; font-weight: normal; color: #4b5563;">የወሎ ዩኒቨርሲቲ የጠፉ እና የተገኙ ንብረቶች አያያዝ እና አስተዳደር ሥርዓት</span>
</h1>

<p align="center">
  An enterprise-grade, bilingual institutional property recovery and physical custody auditing platform built for <strong>Wollo University</strong> (Dessie & Kombolcha Campuses, Ethiopia).
</p>

<p align="center">
  <a href="https://laravel.com"><img src="https://img.shields.io/badge/Laravel-13.x-FF2D20?style=for-the-badge&logo=laravel&logoColor=white" alt="Laravel 13"/></a>
  <a href="https://vuejs.org"><img src="https://img.shields.io/badge/Vue.js-3.5-4FC08D?style=for-the-badge&logo=vuedotjs&logoColor=white" alt="Vue 3"/></a>
  <a href="https://www.typescriptlang.org"><img src="https://img.shields.io/badge/TypeScript-6.0-3178C6?style=for-the-badge&logo=typescript&logoColor=white" alt="TypeScript"/></a>
  <a href="https://tailwindcss.com"><img src="https://img.shields.io/badge/Tailwind_CSS-4.3-06B6D4?style=for-the-badge&logo=tailwindcss&logoColor=white" alt="Tailwind CSS 4"/></a>
  <a href="https://vitejs.dev"><img src="https://img.shields.io/badge/Vite-8.2-646CFF?style=for-the-badge&logo=vite&logoColor=white" alt="Vite"/></a>
  <a href="https://www.mysql.com"><img src="https://img.shields.io/badge/MySQL-8.0-4479A1?style=for-the-badge&logo=mysql&logoColor=white" alt="MySQL"/></a>
</p>

<p align="center">
  <img src="https://img.shields.io/badge/Tests-78%20Passed%20%7C%20196%20Assertions-brightgreen?style=flat-square" alt="Test Status"/>
  <img src="https://img.shields.io/badge/Type_Check-0%20Errors-brightgreen?style=flat-square" alt="TypeScript Type Check"/>
  <img src="https://img.shields.io/badge/Architecture-Decoupled%20SPA%20%2B%20REST%20API-blue?style=flat-square" alt="Architecture"/>
  <img src="https://img.shields.io/badge/Auth-Sanctum%20SPA%20Session--Cookie-purple?style=flat-square" alt="Auth"/>
  <img src="https://img.shields.io/badge/Locale-EN%20%7C%20AM%20(%E1%88%9B%E1%88%AD%E1%8A%9B)-orange?style=flat-square" alt="Bilingual"/>
  <img src="https://img.shields.io/badge/License-MIT-green?style=flat-square" alt="License"/>
</p>

---

## 📌 Table of Contents
1. [Overview & Core Capabilities](#-overview--core-capabilities)
2. [System Architecture](#-system-architecture)
3. [Key Highlights & Engineering Standards](#-key-highlights--engineering-standards)
4. [Technology Stack](#-technology-stack)
5. [Repository Directory Structure](#-repository-directory-structure)
6. [Getting Started (Local Development)](#-getting-started-local-development)
7. [Running Automated Tests](#-running-automated-tests)
8. [API Documentation & Endpoints](#-api-documentation--endpoints)
9. [Production Deployment Blueprint](#-production-deployment-blueprint)
10. [Documentation & Specifications](#-documentation--specifications)
11. [Contributing & Code of Conduct](#-contributing--code-of-conduct)
12. [License](#-license)

---

## 🌟 Overview & Core Capabilities

The **Wollo University Lost & Found Management System (WU-LFMS)** replaces archaic paper-based logbooks with a centralized, cryptographically secure digital platform for reporting, claiming, matching, custody-tracking, and returning lost property across all campuses.

### Key Capabilities:
- 🔍 **Intelligent Property Matcher**: Natural language tokenization and Jaccard similarity scoring (>= 35% threshold) that automatically detects potential matches between reported lost items and newly registered found items.
- 🛡️ **Sanctum SPA Session-Cookie Authentication**: Pure cookie-based CSRF-protected stateful session architecture with rate-limiting and brute-force account lockout (5 failed attempts = 30-minute lock).
- 📦 **Physical Custody & Storage Tracking**: Complete chain-of-custody tracking across university storage rooms, shelves, and cabinets with event auditing (intake, transfer, inspection, release).
- ⚖️ **Pessimistic Locking & Dispute Reversals**: Row-level database locks (`lockForUpdate`) prevent concurrent double-approvals of competing claims, with full admin dispute reversal workflows.
- 📬 **Event-Driven Notifications**: Database alerts and transactional emails dispatched asynchronously via queue workers for claim decisions, matches, and handover confirmations.
- 🌐 **Bilingual (English & Amharic / አማርኛ)**: Native localized UI and multilingual notifications for Ethiopian university workflows.

---

## 🏗️ System Architecture

```
+-------------------------------------------------------------------------------+
|                                CLIENT SPA                                     |
|  Vue 3.5 | TypeScript 6 | Pinia Stores | Vite 8 | Tailwind CSS 4               |
|  - Axios client with automatic XSRF token handling & 401/419/422 interceptors |
+---------------------------------------+---------------------------------------+
                                        | (HTTPS / JSON / Session-Cookie)
                                        v
+-------------------------------------------------------------------------------+
|                            LARAVEL 13 REST API                                |
|  - 94 Production API Endpoints                                                |
|  - Middleware: auth:sanctum, role:staff,admin, throttle                       |
|  - Concurrency Control: lockForUpdate() on Claims & Item State Transitions    |
+-------------------+-----------------------------------+-----------------------+
                    |                                   |
                    v                                   v
+---------------------------------------+   +-----------------------------------+
|               DATABASE                |   |          ASYNC WORKERS            |
|  MySQL 8.0 (InnoDB)                   |   |  Laravel Queue Worker             |
|  - 25+ Tables with Foreign Keys       |   |  - Transactional Mail Notifications|
|  - Indexed Audit Trail Ledger         |   |  - Daily Inactive Expiry Cron     |
+---------------------------------------+   +-----------------------------------+
```

---

## 💻 Technology Stack

| Layer | Technologies |
| :--- | :--- |
| **Backend API** | PHP 8.3+, Laravel 13.x, Eloquent ORM, Laravel Sanctum SPA |
| **Frontend SPA**| Vue 3.5 (Composition API), TypeScript 6.0, Pinia 4.x, Vite 8.2 |
| **Styling & UI**| Tailwind CSS 4.3, Lucide Vue Next, Glassmorphism Tokens |
| **Database** | MySQL 8.0+ |
| **Queue & Cache** | Database Queue Workers, Database Cache & Session Drivers |
| **CI / CD** | GitHub Actions Workflow (`.github/workflows/ci.yml`) |

---

## 📁 Repository Directory Structure

```text
wollo-lost-found/
├── .github/
│   └── workflows/
│       └── ci.yml                 # Automated CI test & build pipeline
├── client/                        # Vue 3 + TypeScript Single Page Application
│   ├── src/
│   │   ├── components/            # Reusable UI & Layout Components
│   │   ├── composables/           # Vue Composition API Hooks
│   │   ├── constants/             # Central Domain Constants
│   │   ├── features/              # Feature Modules (auth, items, claims, custody, etc.)
│   │   ├── layouts/               # Default, Auth, Dashboard Layouts
│   │   ├── lib/                   # Axios HTTP client, CSRF, API endpoints
│   │   ├── router/                # Vue Router with centralized navigation guards
│   │   ├── stores/                # Global Pinia state stores
│   │   └── views/                 # Page Views (admin, staff, student, public, auth)
│   ├── package.json
│   ├── tsconfig.json
│   └── vite.config.ts
├── server/                        # Laravel 13 PHP RESTful API
│   ├── app/
│   │   ├── Console/Commands/      # Scheduled artisan commands
│   │   ├── Domain/                # Core domain DTOs & Services (Matching, Notification)
│   │   ├── Events/                # Application events
│   │   ├── Http/
│   │   │   ├── Controllers/Api/V1/# RESTful API Controllers
│   │   │   ├── Middleware/        # RBAC & active account verification
│   │   │   ├── Requests/Api/V1/   # Form validation request classes
│   │   │   └── Resources/Api/V1/  # JSON API resource transformers
│   │   ├── Jobs/                  # Asynchronous queue jobs
│   │   ├── Listeners/             # Event listeners
│   │   ├── Mail/                  # Transactional Mailable classes
│   │   ├── Models/                # Eloquent models & relationships
│   │   └── Policies/              # Gate authorization policies
│   ├── database/
│   │   ├── migrations/            # 35+ Database schema migrations
│   │   └── seeders/               # University campus, department, user seeders
│   ├── lang/                      # Amharic (am) and English (en) localization
│   ├── resources/views/emails/    # Transactional Blade email templates
│   ├── routes/
│   │   ├── api.php                # 94 versioned API endpoints
│   │   └── console.php            # Scheduled cron jobs
│   └── tests/                     # 78 Feature & Unit test suites
├── SYSTEM_DOCUMENTATION.md        # Comprehensive technical specification
└── README.md
```

---

## 🚀 Getting Started (Local Development)

### Prerequisites
- PHP 8.3+ with `pdo_mysql`, `mbstring`, `intl`, `gd`, `zip`, `bcmath`
- Composer 2.x
- Node.js 20+ and npm
- MySQL 8.0+

### 1. Backend Setup
```bash
cd server

# Copy environment configuration
cp .env.example .env

# Install PHP dependencies
composer install

# Generate application key
php artisan key:generate

# Configure database credentials in .env, then run migrations and seeders:
php artisan migrate --seed

# Create public storage symlink
php artisan storage:link

# Start the Laravel API development server
php artisan serve --port=8000
```

### 2. Frontend Setup
```bash
cd client

# Copy environment configuration
cp .env.example .env

# Install Node dependencies
npm install

# Start the Vite development server (proxies /api and /sanctum to localhost:8000)
npm run dev
```

Visit **`http://localhost:5173`** in your browser.

---

## 🧪 Running Automated Tests

### Backend Automated Test Suite
```bash
cd server
php artisan test
```
> **Result**: 78 tests passed, 196 assertions, 0 errors across 11 test suites.

### Frontend Type Checking & Build
```bash
cd client

# Run Ahead-of-Time (AOT) TypeScript type check
npm run type-check

# Run production Vite build
npm run build
```

---

## 📖 API Documentation & Endpoints

The API is fully RESTful and versioned under `/api/v1`.

### Summary of Endpoint Groups:
- **Authentication**: `POST /api/v1/auth/register`, `POST /api/v1/auth/login`, `POST /api/v1/auth/logout`, `GET /api/v1/auth/me`
- **Password & Email OTP**: `/api/v1/auth/verify-email`, `/api/v1/auth/forgot-password`, `/api/v1/auth/reset-password`
- **Public Discovery**: `GET /api/v1/public/items`, `GET /api/v1/public/items/{id}`, `GET /api/v1/public/track/{code}`
- **Items Management**: `POST /api/v1/items/lost`, `POST /api/v1/items/found`, `PUT /api/v1/items/{id}`, `DELETE /api/v1/items/{id}`
- **Claims & Decisions**: `POST /api/v1/claims`, `POST /api/v1/claims/{id}/review`, `POST /api/v1/claims/{id}/reverse`
- **Physical Custody**: `POST /api/v1/custody`, `POST /api/v1/custody/items/{id}/move`, `/api/v1/custody/storage-locations`
- **Physical Returns**: `POST /api/v1/returns`, `GET /api/v1/returns/{id}`
- **Directorate Admin**: `/api/v1/admin/dashboard/statistics`, `/api/v1/admin/users`, `/api/v1/admin/audit-logs/export`

For detailed parameter schemas, JSON structures, and security gates, refer to [`SYSTEM_DOCUMENTATION.md`](SYSTEM_DOCUMENTATION.md).

---

## 🛡️ Production Deployment Blueprint

For the full, step-by-step production deployment manual, including **email SMTP setup (Gmail, Brevo, Wollo University SMTP, Mailgun, SES)**, **6-digit OTP email verification engine**, **Supervisor queue daemon configs**, **Nginx with SSL**, and **1-click deploy scripts**, refer to:

👉 **[Complete Production Deployment & Email Verification Guide (deploysetupreadme.md)](deploysetupreadme.md)**

### Quick Checklist:
1. **Environment Variables**: Configure `APP_ENV=production`, `APP_DEBUG=false`, `SESSION_SECURE_COOKIE=true` in `server/.env`.
2. **Background Queue Worker**: Configure Supervisor for `php artisan queue:work database --tries=3` (crucial for sending OTP emails).
3. **Automated Scheduler**: Add `* * * * * cd /path/to/server && php artisan schedule:run >> /dev/null 2>&1` to system crontab.
4. **Static Optimization**:
   ```bash
   cd server
   php artisan config:cache
   php artisan route:cache
   php artisan view:cache
   ```

---

## 🤝 Contributing

Contributions to the Wollo University Lost & Found system follow strict enterprise software engineering practices:
1. Fork the Project repository.
2. Create your Feature Branch (`git checkout -b feature/AmazingFeature`).
3. Commit your Changes (`git commit -m 'feat: Add new property filter'`).
4. Ensure all tests pass (`php artisan test` and `npm run type-check`).
5. Push to the Branch (`git push origin feature/AmazingFeature`).
6. Open a Pull Request.

---

## 📄 License

This project is licensed under the **MIT License** - see the [LICENSE](LICENSE) file for details.

---

<p align="center">
  Developed with excellence for <strong>Wollo University</strong> by <strong>WONDATIR FETENE (QMT)</strong> (ቀለም ሜዳ ቴክኖሎጂስ).
  <br>
  Dessie & Kombolcha, Ethiopia &copy; 2026. All Rights Reserved.
</p>
