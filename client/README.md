# Wollo University Lost & Found — Frontend Single Page Application

The frontend for the **Wollo University Lost & Found Property Management System (WU-LFMS)** is a high-performance Single Page Application (SPA) built with **Vue 3.5**, **TypeScript 6.0**, **Pinia 4**, **Tailwind CSS 4**, and **Vite 8**.

---

## 🛠️ Tech Stack & Key Components
- **Framework**: Vue 3.5 (Composition API with `<script setup lang="ts">`)
- **Language**: TypeScript 6.0 (Strict mode enabled)
- **State Management**: Pinia 4.x modular stores
- **Routing**: Vue Router 5.x with centralized asynchronous route guards
- **Styling**: Tailwind CSS 4.3 + Lucide Icons (`lucide-vue-next`)
- **HTTP Client**: Axios with automatic Sanctum CSRF cookie handling (`withCredentials: true`, `withXSRFToken: true`)
- **Build Tool**: Vite 8.2

---

## 🚀 Setup & Installation

```bash
# 1. Copy environment configuration
cp .env.example .env

# 2. Install dependencies
npm install

# 3. Start development server (proxies /api and /sanctum to localhost:8000)
npm run dev
```

---

## 🧪 Quality Control & Building

```bash
# Run TypeScript Ahead-of-Time (AOT) type checking
npm run type-check

# Compile production bundle (with full minification and code splitting)
npm run build

# Preview production build locally
npm run preview
```

---

## 📁 Feature Structure
The SPA is organized into domain-driven feature modules under `src/features/`:
- `auth`: Login, Registration, OTP email verification, Password reset, and User Profile.
- `items`: Lost and Found reporting forms, multi-image upload, item lists, timeline.
- `claims`: Ownership claim submission, evidence attachments, and staff review modals.
- `custody`: Physical property vault transfers and storage location assignment.
- `returns`: Legal property handover verification and return document handling.
- `notifications`: Real-time in-app notification bell with background polling.
- `admin`: User management, campus settings, audit log explorer, and system metrics.
