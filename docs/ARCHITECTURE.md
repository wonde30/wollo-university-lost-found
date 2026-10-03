---
title: System Architecture Reference
version: 1.0.0
system: Wollo University Lost & Found System
generated_from: source code (migrations, controllers, policies, routes, seeders)
date: 2026-08-31
---

# System Architecture Reference
## Wollo University Lost & Found System

<!-- source: composer.json -->
<!-- source: client/package.json -->
<!-- source: app/Providers/ -->

---

### 1. Technology Stack

| Component | Technology | Version | Purpose / Role in System |
|-----------|------------|---------|---------------------------|
| **Backend Framework** | Laravel | `^13.17` (Laravel 13) | RESTful API backend, routing, Eloquent ORM, middleware, queued jobs. |
| **Runtime Engine** | PHP | `^8.3` | Modern strictly-typed PHP execution runtime. |
| **Authentication** | Laravel Sanctum | `^4.0` | SPA session cookies and Bearer token issuance. |
| **Database Engine** | MySQL | `8.0+` (InnoDB) | Relational persistence, foreign keys, row locks, composite & fulltext indexing. |
| **Frontend Framework** | Vue.js | `^3.5.40` (Vue 3) | Reactive Composition API frontend application. |
| **Language** | TypeScript | `~6.0.2` | Strictly-typed frontend codebase with full type contracts. |
| **State Management** | Pinia | `^4.0.3` | Modular client-side state stores. |
| **Client Routing** | Vue Router | `^5.2.0` | Client-side navigation, history mode, auth navigation guards. |
| **Styling & UI** | Tailwind CSS | `^4.3.3` | Utility-first CSS styling system. |
| **Build Tooling** | Vite | `^8.2.0` | Frontend module bundler and hot module replacement dev server. |
| **HTTP Client** | Axios | `^1.19.0` | Promise-based HTTP client with request/response interceptors. |
| **Iconography** | Lucide Vue Next | `^1.0.0` | Scalable SVG icon components. |

---

### 2. Annotated Directory Structure

```
wollo-lost-found/
├── client/                                    # Vue 3 + TypeScript Frontend Application
│   ├── src/
│   │   ├── app/                              # Root application configuration & providers
│   │   ├── assets/                           # Static assets, styling tokens, and images
│   │   ├── components/                       # Shared UI components (modals, tables, badges)
│   │   ├── composables/                      # Vue 3 composition functions (auth, modals)
│   │   ├── constants/                        # Global system constants and enumerations
│   │   ├── directives/                       # Custom Vue directives (e.g. v-can for RBAC)
│   │   ├── features/                         # Modular feature domains (API, stores, types)
│   │   │   ├── admin/                        # Administrative hubs, campuses, RBAC management
│   │   │   ├── auth/                         # Authentication, OTP verification, password reset
│   │   │   ├── claims/                       # Claims submission, proof upload, review gates
│   │   │   ├── custody/                      # Physical custody logs & storage bin allocation
│   │   │   ├── items/                        # Item reporting (lost/found), photo management
│   │   │   ├── lookups/                      # Taxonomy references (categories, locations)
│   │   │   ├── match-suggestions/            # Suggestion review & pairing confirmation
│   │   │   ├── notifications/                # Real-time SSE listener & inbox management
│   │   │   ├── profile/                      # User profile & avatar management
│   │   │   └── returns/                      # Return processing, digital signature verification
│   │   ├── layouts/                          # App shell layouts (DashboardLayout, AuthLayout)
│   │   ├── lib/                              # HTTP client, interceptors, error parsers
│   │   ├── permissions/                      # RBAC stores, capability checkers, v-can helpers
│   │   ├── router/                           # Vue Router route map, meta tags, auth guards
│   │   ├── stores/                           # Global UI and application context Pinia stores
│   │   ├── types/                            # Global TypeScript interfaces & DTOs
│   │   └── views/                            # Route view pages categorized by role/domain
└── server/                                    # Laravel 13 REST API Backend
    ├── app/
    │   ├── Console/                          # Artisan commands & scheduled tasks
    │   ├── Domain/                           # DDD feature modules (Actions, DTOs, Services)
    │   │   ├── Administration/               # Dashboard analytics & report generators
    │   │   ├── Auth/                         # Authentication actions, OTP services
    │   │   ├── Claims/                       # Claim workflows, approvals, reversals
    │   │   ├── Custody/                      # Physical vault movements & transfers
    │   │   ├── Items/                        # Item lifecycle, search, duplicate detection
    │   │   ├── Matching/                     # NLP tokenization & Jaccard similarity engine
    │   │   ├── Notifications/                # Notification preferences & delivery
    │   │   └── Returns/                      # Handover verification, PDF receipt generator
    │   ├── Events/                           # System domain events (ItemReported, ClaimApproved)
    │   ├── Exceptions/                       # Custom domain business exceptions
    │   ├── Http/
    │   │   ├── Controllers/Api/V1/           # API v1 REST Controllers grouped by domain
    │   │   ├── Middleware/                   # Custom middleware (EnsureUserHasRole, Throttle)
    │   │   └── Requests/Api/V1/              # FormRequest validation rules
    │   ├── Jobs/                             # Asynchronous queued jobs (matching, emails, PDFs)
    │   ├── Listeners/                        # Event listeners responding to domain events
    │   ├── Mail/                             # Mailable classes for email notifications
    │   ├── Models/                           # Eloquent ORM active record models
    │   ├── Policies/                         # Authorization Gate & Policy classes
    │   ├── Providers/                        # Application service providers
    │   └── Support/Services/                 # Cross-cutting support services (Audit, Storage, RBAC)
    ├── database/
    │   ├── factories/                        # Model test factories
    │   ├── migrations/                       # 48 database schema migrations
    │   └── seeders/                         # Database seeders (roles, locations, taxonomies)
    └── routes/
        ├── api.php                           # API v1 routing table — 145 registered routes
        └── console.php                       # 5 scheduled cron tasks (artisan schedule)
```

---

### 3. Request Lifecycle
<!-- source: app/Http/Controllers/ -->
<!-- source: app/Http/Middleware/ -->

```mermaid
sequenceDiagram
    autonumber
    actor Client as Frontend Client (Vue 3 / Axios)
    participant Router as Laravel Route Pipeline (routes/api.php)
    participant Mid as Middleware Stack (Sanctum, EnsureUserHasRole, Throttle)
    participant Request as FormRequest (Validation Rules)
    participant Controller as REST Controller (Api\V1\*)
    participant Policy as Eloquent Policy Gate (app/Policies)
    participant Service as Domain Action / Service (app/Domain)
    participant Model as Eloquent Model / MySQL (app/Models)
    participant EventPipe as Event Pipeline (Events -> Listeners -> Jobs -> Mail)

    Client->>Router: HTTP Request (e.g. POST /api/v1/claims)
    Router->>Mid: Pass through Middleware
    Mid->>Mid: Verify Sanctum Bearer Token & Role Boundary
    Mid->>Request: Validate Request Schema & Types
    Request-->>Client: 422 Unprocessable Entity (if validation fails)
    Request->>Controller: Validated DTO / Request instance
    Controller->>Policy: $this->authorize('action', $model)
    Policy-->>Client: 403 Forbidden (if unauthorized)
    Controller->>Service: Execute Domain Action (CreateClaim::execute)
    Service->>Model: DB::transaction -> Insert records into MySQL
    Model-->>Service: Persisted Entity Instances
    Service->>EventPipe: event(new ClaimSubmitted($claim))
    EventPipe-->>EventPipe: Dispatch queued notification jobs (SSE / Email)
    Service-->>Controller: Return Domain Result DTO
    Controller-->>Client: HTTP 201 Created (Standardized JSON Envelope)
```

---

### 4. Domain Service Layer
<!-- source: app/Support/Services/ -->
<!-- source: app/Domain/ -->

#### `\App\Domain\Administration\Services\DashboardStatisticsService`
<!-- source: app/Domain/Administration/Services/DashboardStatisticsService.php -->
**File:** `app/Domain/Administration/Services/DashboardStatisticsService.php`  
**Responsibility:** Computes admin dashboard aggregated statistics (item counts by status, claims, custody records).

#### `\App\Domain\Administration\Services\ReportExportService`
<!-- source: app/Domain/Administration/Services/ReportExportService.php -->
**File:** `app/Domain/Administration/Services/ReportExportService.php`  
**Responsibility:** Handles report data export generation (CSV/PDF) for admin-triggered report downloads.

#### `\App\Domain\Administration\Services\UserAdministrationService`
<!-- source: app/Domain/Administration/Services/UserAdministrationService.php -->
**File:** `app/Domain/Administration/Services/UserAdministrationService.php`  
**Responsibility:** Manages user account operations for admins (create, update role, toggle active, sync permissions). Enforces privilege-escalation checks.

#### `\App\Domain\Auth\Services\AuthenticationService`
<!-- source: app/Domain/Auth/Services/AuthenticationService.php -->
**File:** `app/Domain/Auth/Services/AuthenticationService.php`  
**Responsibility:** Orchestrates login, logout, session creation, account lockout tracking, and Sanctum token issuance.

#### `\App\Domain\Auth\Services\OtpService`
<!-- source: app/Domain/Auth/Services/OtpService.php -->
**File:** `app/Domain/Auth/Services/OtpService.php`  
**Responsibility:** Generates, stores, validates, and expires 6-digit OTP codes in `auth_verifications` for email verification and password reset.

#### `\App\Domain\Auth\Services\PasswordService`
<!-- source: app/Domain/Auth/Services/PasswordService.php -->
**File:** `app/Domain/Auth/Services/PasswordService.php`  
**Responsibility:** Enforces password history rules, change requirements, and credential rotation.

#### `\App\Domain\Claims\Services\ClaimService`
<!-- source: app/Domain/Claims/Services/ClaimService.php -->
**File:** `app/Domain/Claims/Services/ClaimService.php`  
**Responsibility:** Manages claim creation, duplicate prevention, pessimistic locking during review, approval/rejection state transitions, and claim reversal.

#### `\App\Domain\Custody\Services\CustodyService`
<!-- source: app/Domain/Custody/Services/CustodyService.php -->
**File:** `app/Domain/Custody/Services/CustodyService.php`  
**Responsibility:** Records custody intake events, manages storage location assignments, and processes inter-location transfers.

#### `\App\Domain\Items\Services\DuplicateDetectionService`
<!-- source: app/Domain/Items/Services/DuplicateDetectionService.php -->
**File:** `app/Domain/Items/Services/DuplicateDetectionService.php`  
**Responsibility:** Detects potential duplicate item reports using description similarity and field-level comparison.

#### `\App\Domain\Items\Services\ItemReferenceCodeService`
<!-- source: app/Domain/Items/Services/ItemReferenceCodeService.php -->
**File:** `app/Domain/Items/Services/ItemReferenceCodeService.php`  
**Responsibility:** Generates unique, human-readable reference codes for item tracking (used by `/api/v1/public/track/{code}`).

#### `\App\Domain\Items\Services\ItemSearchService`
<!-- source: app/Domain/Items/Services/ItemSearchService.php -->
**File:** `app/Domain/Items/Services/ItemSearchService.php`  
**Responsibility:** Handles filtered search, pagination, and keyword matching across the item catalogue.

#### `\App\Domain\Matching\Services\ItemMatchingService`
<!-- source: app/Domain/Matching/Services/ItemMatchingService.php -->
**File:** `app/Domain/Matching/Services/ItemMatchingService.php`  
**Responsibility:** Orchestrates the full matching pipeline: tokenization → Jaccard scoring → match suggestion persistence (threshold ≥ 35%).

#### `\App\Domain\Matching\Services\JaccardSimilarityService`
<!-- source: app/Domain/Matching/Services/JaccardSimilarityService.php -->
**File:** `app/Domain/Matching/Services/JaccardSimilarityService.php`  
**Responsibility:** Computes Jaccard similarity coefficient between two token sets. Threshold: ≥ 0.35 triggers a match suggestion.

#### `\App\Domain\Matching\Services\TokenizerService`
<!-- source: app/Domain/Matching/Services/TokenizerService.php -->
**File:** `app/Domain/Matching/Services/TokenizerService.php`  
**Responsibility:** Normalizes and tokenizes item descriptions into comparable n-gram token sets for Jaccard similarity scoring.

#### `\App\Domain\Notifications\Services\NotificationPreferenceService`
<!-- source: app/Domain/Notifications/Services/NotificationPreferenceService.php -->
**File:** `app/Domain/Notifications/Services/NotificationPreferenceService.php`  
**Responsibility:** Manages per-user notification channel preferences (email, in-app) for each notification event type.

#### `\App\Domain\Notifications\Services\NotificationService`
<!-- source: app/Domain/Notifications/Services/NotificationService.php -->
**File:** `app/Domain/Notifications/Services/NotificationService.php`  
**Responsibility:** Creates and dispatches database notification records; drives SSE stream delivery.

#### `\App\Domain\Returns\Services\ReturnPdfService`
<!-- source: app/Domain/Returns/Services/ReturnPdfService.php -->
**File:** `app/Domain/Returns/Services/ReturnPdfService.php`  
**Responsibility:** Generates PDF return confirmation documents (barryvdh/laravel-dompdf ^3.1) for physical handover records.

#### `\App\Domain\Returns\Services\ReturnService`
<!-- source: app/Domain/Returns/Services/ReturnService.php -->
**File:** `app/Domain/Returns/Services/ReturnService.php`  
**Responsibility:** Orchestrates the return lifecycle: token generation, identity verification, handover confirmation, status finalization, and audit logging.

#### `\App\Support\Services\AuditLogger`
<!-- source: app/Support/Services/AuditLogger.php -->
**File:** `app/Support/Services/AuditLogger.php`  
**Responsibility:** Writes immutable records to `audit_logs` capturing actor ID, IP, action type, target model, and before/after state snapshots.

#### `\App\Support\Services\FileStorageService`
<!-- source: app/Support/Services/FileStorageService.php -->
**File:** `app/Support/Services/FileStorageService.php`  
**Responsibility:** Abstracts disk-specific file storage, UUID filename generation, MIME validation, and secure deletion across public (items, avatars) and private (evidence, signatures, PDFs) disks.

#### `\App\Support\Services\PermissionGroupService`
<!-- source: app/Support/Services/PermissionGroupService.php -->
**File:** `app/Support/Services/PermissionGroupService.php`  
**Responsibility:** Manages RBAC permission groups (enable/disable groups and their constituent permissions).

#### `\App\Support\Services\PermissionService`
<!-- source: app/Support/Services/PermissionService.php -->
**File:** `app/Support/Services/PermissionService.php`  
**Responsibility:** Fine-grained permission operations: toggle active state, sync per-user and per-role permission matrices.

#### `\App\Support\Services\RequestContext`
<!-- source: app/Support/Services/RequestContext.php -->
**File:** `app/Support/Services/RequestContext.php`  
**Responsibility:** Captures request-scoped metadata (IP, user-agent) for use by the AuditLogger.

#### `\App\Support\Services\RoleService`
<!-- source: app/Support/Services/RoleService.php -->
**File:** `app/Support/Services/RoleService.php`  
**Responsibility:** Manages role CRUD, role-permission sync, in-process cache flush, and protected system-role guards.

---

### 5. Frontend Architecture & Pinia State Stores
<!-- source: client/src/features/ -->
<!-- source: client/src/stores/ -->

| Store Name | File Path | Primary State Shape | Key Actions | Consumed API Endpoints |
|------------|-----------|---------------------|-------------|-------------------------|
| `useAuthStore` | `client/src/features/auth/stores/auth.store.ts` | `user`, `token`, `isAuthenticated`, `roles`, `permissions` | `login()`, `logout()`, `verifyEmail()`, `resetPassword()`, `fetchMe()` | `/api/v1/auth/*` |
| `useItemsStore` | `client/src/features/items/stores/items.store.ts` | `items`, `selectedItem`, `loading`, `pagination`, `filters` | `fetchItems()`, `reportLost()`, `reportFound()`, `uploadPhoto()`, `deletePhoto()` | `/api/v1/items/*` |
| `usePublicItemsStore` | `client/src/features/lookups/stores/public-items.store.ts` | `publicItems`, `currentItem`, `filters`, `loading` | `fetchPublicItems()`, `fetchItemDetails()`, `trackItem()` | `/api/v1/public/*` |
| `useClaimsStore` | `client/src/features/claims/stores/claims.store.ts` | `claims`, `currentClaim`, `reviewStatus`, `loading` | `submitClaim()`, `reviewClaim()`, `reverseClaim()` | `/api/v1/claims/*` |
| `useCustodyStore` | `client/src/features/custody/stores/custody.store.ts` | `custodyEvents`, `storageLocations`, `loading` | `recordCustodyEvent()`, `moveStorage()`, `fetchLocations()` | `/api/v1/custody/*` |
| `useReturnsStore` | `client/src/features/returns/stores/returns.store.ts` | `returns`, `activeReturn`, `verificationCode`, `signature` | `initiateReturn()`, `confirmReturn()`, `confirmByToken()`, `exportCsv()` | `/api/v1/returns/*` |
| `useMatchSuggestionsStore` | `client/src/features/match-suggestions/stores/match-suggestions.store.ts` | `suggestions`, `selectedPairing`, `loading` | `fetchSuggestions()`, `updateStatus()` | `/api/v1/match-suggestions/*` |
| `useNotificationsStore` | `client/src/features/notifications/stores/notifications.store.ts` | `notifications`, `unreadCount`, `preferences`, `sseConnected` | `initSseStream()`, `markAsRead()`, `markAllAsRead()`, `updatePreferences()` | `/api/v1/notifications/*` |
| `useAdminUsersStore` | `client/src/features/admin/stores/admin-users.store.ts` | `users`, `currentUser`, `rolesList`, `permissionsList` | `fetchUsers()`, `updateUserRole()`, `toggleActive()`, `syncPermissions()` | `/api/v1/admin/users/*` |
| `useAdminStore` | `client/src/features/admin/stores/admin.store.ts` | `statistics`, `campuses`, `categories`, `auditLogs`, `settings` | `fetchStatistics()`, `saveSettings()`, `generateReport()` | `/api/v1/admin/*` |
| `usePermissionsStore` | `client/src/permissions/stores/permissions.store.ts` | `userPermissions`, `userRoles` | `hasPermission()`, `hasRole()`, `can()` | Evaluates cached permission matrix |
| `useUiStore` | `client/src/stores/ui.store.ts` | `sidebarOpen`, `toastMessages`, `activeModal`, `theme` | `showToast()`, `toggleSidebar()`, `openModal()`, `closeModal()` | Client-only UI state |

---

### 6. Event & Notification Pipeline
<!-- source: app/Events/ -->
<!-- source: app/Listeners/ -->
<!-- source: app/Jobs/ -->
<!-- source: app/Mail/ -->

| Event Class | Triggering Operation | Associated Listener | Asynchronous Job Dispatched | Notification Delivery Channel |
|-------------|----------------------|---------------------|-----------------------------|-------------------------------|
| `ItemReported` | User reports lost or found item (`CreateLostItem`, `CreateFoundItem`). | `DispatchMatchSuggestion` | `GenerateMatchSuggestions`, `SendItemConfirmationEmail` | Database Notification, Email (`ItemSubmittedMail`), SSE Stream |
| `ClaimSubmitted` | Claimant asserts ownership (`CreateClaim`). | `NotifyClaimSubmitted` | `SendClaimSubmittedNotification` | Database Notification, Email (`ClaimSubmittedMail`), SSE Stream to Staff |
| `ClaimApproved` | Staff approves claim (`ApproveClaim`). | `NotifyClaimApproved` | `SendClaimDecisionNotification` | Database Notification, Email (`ClaimApprovedMail`), Real-time SSE |
| `ClaimRejected` | Staff rejects claim (`RejectClaim`). | `NotifyClaimRejected` | `SendClaimDecisionNotification` | Database Notification, Email (`ClaimRejectedMail`), Real-time SSE |
| `ItemReturned` | Physical handover completed (`GenerateReturnAcknowledgement`). | `NotifyItemReturned` | `SendReturnNotification`, `GenerateReturnConfirmationPdf` | Database Notification, Email (`ItemReturnedMail`), Signed PDF attachment |
| `CustodyTransferred` | Item moved to new storage bin (`MoveItemToStorage`). | `HandleCustodyTransferred` | `ProcessCustodyTransfer` | Internal audit log & staff notification |
| `NotificationCreated` | New notification record created in DB. | Broadcasts to SSE client | Directly streamed | `GET /api/v1/notifications/stream` |

---

### 7. File Storage Subsystem
<!-- source: app/Support/Services/FileStorageService.php:1-40 -->
<!-- source: config/filesystems.php -->

- **Storage Disks:**
  - `public` disk (`storage/app/public/`): Stores public-facing media such as item photographs (`items/`) and user avatars (`avatars/`). Accessible via `/storage/*` symlink.
  - `local` / private disk (`storage/app/private/`): Stores sensitive proof documents (`claims/evidence/`), recipient digital signatures (`returns/signatures/`), and generated PDF receipts (`reports/`, `returns/documents/`). Protected by controller authorization gates.
- **File Naming Convention:** Cryptographically generated UUID v4 filenames (e.g. `storage/app/public/items/8f9b2d3e-4a5c-6b7d-8e9f-0a1b2c3d4e5f.webp`) to prevent file enumeration attacks.
- **Security Controls:** Strict MIME type validation (JPEG, PNG, WEBP, PDF) and max file size limits enforced via FormRequest validation rules.