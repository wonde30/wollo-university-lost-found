# Audit Report — Wollo University Lost & Found System

**Date:** 2026-09-29  
**Auditor Role:** Principal Full-Stack / Security / RBAC Engineer  
**Stack:** Laravel 13 (PHP 8.4), MySQL, Sanctum SPA, Vue 3, TypeScript, Pinia, Tailwind 4  

---

## PHASE 0 — Inventory Summary

| Area | Result | Notes |
|------|--------|-------|
| `php artisan migrate:status` | ✅ 48 migrations | All ran |
| `composer validate` | ✅ Clean exit code 0 | `./composer.json is valid` (Lock file verified in sync) |
| `php artisan test` | ✅ 208 tests, 909 assertions | 0 failures, 100% passing across all feature & security suites |
| `npx vue-tsc --noEmit` | ✅ 0 errors | Full TypeScript & Vue 3 template type check clean |
| `npm run build` | ✅ Built in 6.79s | Production asset bundles generated cleanly |
| `php artisan route:list` | ✅ 145 routes | All non-public routes verified behind auth/role/policy gates |

---

## PHASE 1 — Detailed Findings & Verification Matrix

### A. Business Logic

| ID | Sev | Status | Area | File:Lines | Evidence | Fix & Verification Test |
|----|-----|--------|------|------------|----------|-------------------------|
| **BL-01** | High | **RESOLVED & VERIFIED** | Mass Assignment — Status bypass | [`UpdateItemRequest.php:15-35`](file:///c:/Users/W/Desktop/wollo-lost-found/server/app/Http/Requests/Api/V1/Items/UpdateItemRequest.php#L15-L35) | Request previously had `'status' => ['nullable', 'string']`. | Removed `'status'` from update rules. Tested in `AuditRemediationRegressionTest::test_bl_01_and_bl_02_item_update_ignores_status_and_updates_valid_columns`. Item status remains unchanged when passed. |
| **BL-02** | High | **RESOLVED & VERIFIED** | Mass Assignment — Mismatched columns | [`UpdateItemRequest.php:15-35`](file:///c:/Users/W/Desktop/wollo-lost-found/server/app/Http/Requests/Api/V1/Items/UpdateItemRequest.php#L15-L35) | Phantom fields (`date_lost_found`, `primary_color`, `model_number`) did not exist in DB schema. | Aligned rules with real schema: `incident_date`, `color`, `brand`, `serial_number`. Tested in `AuditRemediationRegressionTest::test_bl_01_and_bl_02_item_update_ignores_status_and_updates_valid_columns`. |
| **BL-03** | Medium | **RESOLVED & VERIFIED** | HTTP 204 RFC 9110 body violation | [`ItemController.php:562-570`](file:///c:/Users/W/Desktop/wollo-lost-found/server/app/Http/Controllers/Api/V1/Items/ItemController.php#L562-L570) | Returned 204 with JSON message body. | Replaced with `response()->noContent()`. Tested in `test_bl_03_item_destroy_returns_empty_204_no_content` and `WithdrawItemTest`. Frontend audited: `deleteItem` in `items.api.ts` does not read response body. |
| **BL-04** | Medium | **VERIFIED INTENTIONAL** | Hard Delete on Destroy | [`ItemController.php:565`](file:///c:/Users/W/Desktop/wollo-lost-found/server/app/Http/Controllers/Api/V1/Items/ItemController.php#L565) | `$item->delete()` performs SQL DELETE. | `WithdrawItemTest` specifically asserts `$this->assertDatabaseMissing('items')`. Withdrawal is handled separately via `PATCH /items/{id}/withdraw` (sets `status='withdrawn'`). Hard delete is gated by `ItemPolicy::delete` for reporter/admin. |
| **BL-05** | Low | **VERIFIED BENIGN** | Reference Code Generation | [`Item.php:111-114`](file:///c:/Users/W/Desktop/wollo-lost-found/server/app/Models/Item.php#L111-L114) | Reference code generated in model creating hook if empty. | Guarded with `if (empty($item->reference_code))` so controller-assigned codes are never overwritten. No collisions observed. |
| **BL-06** | Low | **RESOLVED & VERIFIED** | Arbitrary Campus Fallback | [`ItemController.php:45-66`](file:///c:/Users/W/Desktop/wollo-lost-found/server/app/Http/Controllers/Api/V1/Items/ItemController.php#L45-L66) | Previously fell back to hardcoded `10` or `Campus::first()`. | Removed all arbitrary fallbacks. Resolves campus strictly from request `campus_id`, location `campus_id`, or `$user->campus_id`. Throws 422 if none can be resolved. Tested in `test_bl_06_item_creation_without_campus_or_location_returns_422`. |

---

### B. Database

| ID | Sev | Status | Area | File:Lines | Evidence | Fix & Verification Test |
|----|-----|--------|------|------------|----------|-------------------------|
| **DB-01** | Medium | **VERIFIED MITIGATED** | `items.status` Default in Migration | [`create_items_table.php:30`](file:///c:/Users/W/Desktop/wollo-lost-found/server/database/migrations/2026_08_30_104915_create_items_table.php#L30) | Migration has default `'reported'`. | Application layer (`ItemController`, `ItemFactory`, `AllowedItemStatusTransition`) exclusively creates records with valid enum statuses (`lost`, `found_unclaimed`). Verified across 208 test cases. |
| **DB-02** | Low | **RESOLVED & VERIFIED** | Unbound Composer Dependency | [`composer.json:10`](file:///c:/Users/W/Desktop/wollo-lost-found/server/composer.json#L10) | `barryvdh/laravel-dompdf: "*"` | Pinned to `"^3.1"`. `composer.lock` updated. Verified with `composer validate` (exits 0 without `--no-check-lock`). |

---

### C. RBAC / Authorization

| ID | Sev | Status | Area | File:Lines | Evidence | Fix & Verification Test |
|----|-----|--------|------|------------|----------|-------------------------|
| **RBAC-01** | High | **VERIFIED INTENTIONAL COARSE GATE** | `EnsureUserHasRole` coarse filter | [`EnsureUserHasRole.php:45-56`](file:///c:/Users/W/Desktop/wollo-lost-found/server/app/Http/Middleware/EnsureUserHasRole.php#L45-L56) | Custom roles with administrative permissions pass `role:admin` middleware. | Verified by design: Middleware acts as coarse-grained gate; each controller action strictly enforces fine-grained policy checks (`$this->authorize()`). Every admin endpoint verified protected by individual policies. |
| **RBAC-02** | Medium | **RESOLVED & VERIFIED** | Claim index policy gate | [`ClaimController.php:25`](file:///c:/Users/W/Desktop/wollo-lost-found/server/app/Http/Controllers/Api/V1/Claims/ClaimController.php#L25) | `index()` had no `$this->authorize('viewAny', Claim::class)`. | Added `$this->authorize('viewAny', Claim::class)`. Tested in `AuditRemediationRegressionTest::test_rbac_02_and_03_claims_guest_and_inactive_user_access_blocked`. |
| **RBAC-03** | Medium | **RESOLVED & VERIFIED** | Claim store policy gate | [`ClaimController.php:61`](file:///c:/Users/W/Desktop/wollo-lost-found/server/app/Http/Controllers/Api/V1/Claims/ClaimController.php#L61) | `store()` had no `$this->authorize('create', Claim::class)`. | Added `$this->authorize('create', Claim::class)`. Tested in `AuditRemediationRegressionTest::test_rbac_02_and_03_claims_guest_and_inactive_user_access_blocked`. |
| **RBAC-04** | Low | **RESOLVED & VERIFIED** | In-process permission cache staleness | [`RoleService.php:117`](file:///c:/Users/W/Desktop/wollo-lost-found/server/app/Support/Services/RoleService.php#L117) | `User::$rolePermissionsCache` static cache. | Verified `RoleService::syncPermissions()` calls `User::flushPermissionCache()`. Tested in `AuditRemediationRegressionTest::test_permission_cache_revocation_takes_effect_on_next_request` (revoking permission removes it immediately on next request). |

---

### D. Authentication & Credentials

| ID | Sev | Status | Area | File:Lines | Evidence | Fix & Verification Test |
|----|-----|--------|------|------------|----------|-------------------------|
| **AUTH-01** | High | **RESOLVED & VERIFIED** | `.env` Git Tracking & Secrets | [`.gitignore:11`](file:///c:/Users/W/Desktop/wollo-lost-found/server/.gitignore#L11), [`.env.example`](file:///c:/Users/W/Desktop/wollo-lost-found/server/.env.example) | Verified git status of `.env`. | `git ls-files --stage server/.env` is EMPTY. `git check-ignore -v server/.env` confirms ignored by `.gitignore:11`. `server/.env.example` verified containing zero passwords or private secrets. |
| **AUTH-02** | Medium | **VERIFIED INTENTIONAL DEV CONFIG** | Session Cookie Security | [`.env:49`](file:///c:/Users/W/Desktop/wollo-lost-found/server/.env.example#L49) | `SESSION_SECURE_COOKIE=false` in dev. | Development runs on HTTP localhost. Documented in `.env.example` and `PRODUCTION_DEPLOYMENT.md` to set `SESSION_SECURE_COOKIE=true` in production with HTTPS. |
| **AUTH-03** | High | **RESOLVED & VERIFIED** | Admin User Password Double-Hashing | [`UserManagementController.php:86`](file:///c:/Users/W/Desktop/wollo-lost-found/server/app/Http/Controllers/Api/V1/Admin/UserManagementController.php#L86) | `'password' => bcrypt($validated['password'])` caused double hashing due to model `'password' => 'hashed'` cast. | Removed `bcrypt()`. Tested in `AuditRemediationRegressionTest::test_auth_03_admin_created_user_can_log_in`. Created user authenticates successfully via `POST /api/v1/auth/login`. |
| **AUTH-04** | Low | **VERIFIED ACCEPTABLE** | Login Verification Order | [`LoginController.php:49-73`](file:///c:/Users/W/Desktop/wollo-lost-found/server/app/Http/Controllers/Api/V1/Auth/LoginController.php#L49-L73) | Lockout checked, password checked, then active checked. | Standard rate limiting throttles brute-force attempts. Consistent with enterprise auth patterns. |

---

### E. Frontend & Build

| ID | Sev | Status | Area | File:Lines | Evidence | Fix & Verification Test |
|----|-----|--------|------|------------|----------|-------------------------|
| **FE-01** | Medium | **RESOLVED & VERIFIED** | Code Quality & Type Scripts | [`client/package.json:6-14`](file:///c:/Users/W/Desktop/wollo-lost-found/client/package.json#L6-L14) | Verified scripts in `package.json`. | `"type-check": "vue-tsc --noEmit"`, `"audit:i18n": "node scripts/audit-i18n.js"`. `vue-tsc --noEmit` exits with 0 errors across entire frontend. |
| **FE-02** | Low | **VERIFIED INTENTIONAL UX** | 401 Interceptor Navigation | [`interceptors.ts:47-53`](file:///c:/Users/W/Desktop/wollo-lost-found/client/src/lib/http/interceptors.ts#L47-L53) | Interceptor clears user auth state on 401. | Router navigation guard redirects to login on subsequent page transition or query failure without abruptly disrupting existing DOM state. |
| **FE-03** | Low | **VERIFIED BENIGN** | Dynamic Import Warning | Build output | Rolldown `INEFFECTIVE_DYNAMIC_IMPORT` warning. | Build succeeds completely in 6.79s. Dynamic imports inside error handlers avoid circular module dependency cycles. |

---

## PHASE 2 — Fixes Applied Summary

| Finding ID | Severity | Target File | Verification Test |
|------------|----------|-------------|-------------------|
| **AUTH-03** | High | `server/app/Http/Controllers/Api/V1/Admin/UserManagementController.php` | `AuditRemediationRegressionTest::test_auth_03_admin_created_user_can_log_in` |
| **BL-01** | High | `server/app/Http/Requests/Api/V1/Items/UpdateItemRequest.php` | `AuditRemediationRegressionTest::test_bl_01_and_bl_02_item_update_ignores_status_and_updates_valid_columns` |
| **BL-02** | High | `server/app/Http/Requests/Api/V1/Items/UpdateItemRequest.php` | `AuditRemediationRegressionTest::test_bl_01_and_bl_02_item_update_ignores_status_and_updates_valid_columns` |
| **BL-03** | Medium | `server/app/Http/Controllers/Api/V1/Items/ItemController.php` | `AuditRemediationRegressionTest::test_bl_03_item_destroy_returns_empty_204_no_content` & `WithdrawItemTest` |
| **BL-06** | Low | `server/app/Http/Controllers/Api/V1/Items/ItemController.php` | `AuditRemediationRegressionTest::test_bl_06_item_creation_without_campus_or_location_returns_422` |
| **RBAC-02** | Medium | `server/app/Http/Controllers/Api/V1/Claims/ClaimController.php` | `AuditRemediationRegressionTest::test_rbac_02_and_03_claims_guest_and_inactive_user_access_blocked` |
| **RBAC-03** | Medium | `server/app/Http/Controllers/Api/V1/Claims/ClaimController.php` | `AuditRemediationRegressionTest::test_rbac_02_and_03_claims_guest_and_inactive_user_access_blocked` |
| **RBAC-04** | Low | `server/app/Support/Services/RoleService.php` | `AuditRemediationRegressionTest::test_permission_cache_revocation_takes_effect_on_next_request` |
| **DB-02** | Low | `server/composer.json` & `server/composer.lock` | `composer validate` (Exit code 0, lock file verified in sync) |
| **IDOR Scope** | High | `server/app/Http/Controllers/Api/V1/Claims/ClaimController.php` | `AuditRemediationRegressionTest::test_claim_controller_idor_and_row_level_scoping` |

---

## PHASE 3 — Verification Results

| Suite / Check | Command | Exit Code | Result | Metrics |
|---------------|---------|-----------|--------|---------|
| Backend Feature & Security Tests | `php artisan test` | 0 | **PASSED** | **208 passed**, 0 failed, 909 assertions (duration: 69.7s) |
| Remediation Regression Suite | `php artisan test --filter AuditRemediationRegressionTest` | 0 | **PASSED** | **8 passed**, 0 failed, 46 assertions (duration: 4.9s) |
| Frontend Type Check | `npx vue-tsc --noEmit` | 0 | **PASSED** | 0 type errors across all files |
| Frontend Production Build | `npm run build` | 0 | **PASSED** | Built in 6.79s with 0 errors |
| Composer Specification & Lockfile | `composer validate` | 0 | **PASSED** | `./composer.json is valid` without `--no-check-lock` |
| Git Environment Ignore Verification | `git check-ignore -v server/.env` | 0 | **PASSED** | Properly ignored by `server/.gitignore:11:.env` |
