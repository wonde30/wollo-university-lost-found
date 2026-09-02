---
title: Functional Requirements Specification — Wollo University Lost & Found System
version: 1.0.0
system: Wollo University Lost & Found System
generated_from: source code (migrations, controllers, policies, routes, seeders)
date: 2026-08-31
---

<!-- source: routes/api.php:1-213 -->
<!-- source: database/seeders/RoleAndPermissionSeeder.php:1-400 -->

# Functional Requirements Specification (FRS)
## Wollo University Lost & Found System

---

### 1. Document Purpose & Scope

#### 1.1 Purpose
This document specifies the complete, verified functional requirements for the Wollo University Lost & Found System. Every requirement is derived strictly from the backend Laravel implementation, database migrations, Eloquent models, FormRequests, policies, domain services, event listeners, and Vue 3 frontend components.

#### 1.2 System Scope
The system provides end-to-end management of lost and found property across Wollo University campuses (Dessie Campus, Kombolcha Campus). The core capabilities include:
- Multi-channel item reporting (lost items reported by owners; found items registered by finders or security staff).
- Automated lexical tokenization and Jaccard similarity-based matching between lost and found item catalogues.
- Ownership claim submission, multi-photo evidence attachment, and formal staff review workflows.
- Physical custody event logging and secure storage location tracking across campus facilities.
- Multi-party physical item handover verification supporting authenticated staff sign-off and single-use cryptographic token verification with digital signatures.
- Real-time Server-Sent Events (SSE) notification streaming and asynchronous email delivery.
- Dynamic Role-Based Access Control (RBAC) with granular permission trees, role assignment, and direct user permission overrides.
- Immutable audit logging for security compliance and administrative analytical reporting.

#### 1.3 System Exclusions
The following capabilities are explicitly outside the system boundary based on codebase analysis:
- No payment gateway integration or monetary rewards processing for found items.
- No third-party OAuth2 / social login providers (authentication is strictly local institutional identity).
- No live chat or peer-to-peer direct messaging between lost-item reporters and found-item holders (all communications are routed through staff mediation and system claims).
- No GPS map geofencing or live vehicle tracking.

---

### 2. Stakeholders & User Roles

<!-- source: database/seeders/RoleAndPermissionSeeder.php:25-70 -->
<!-- source: database/migrations/2026_08_30_104858_create_roles_table.php:14-24 -->
<!-- source: app/Models/Role.php:1-55 -->

The system defines four standard roles stored in the `roles` table:

| Role Name | Display Name | System Protected | Description & Capability Boundary |
|-----------|--------------|------------------|-----------------------------------|
| `admin` | Administrator | `true` | Full administrative control. Manages system settings, campuses, organizational units, categories, locations, storage rooms, user accounts, role-permission assignments, reports, and audit logs. |
| `staff` | Staff / Security Officer | `true` | Operational authority over lost & found operations. Reviews and approves/rejects claims, manages physical custody, registers storage relocations, coordinates and confirms item handovers, exports handover CSVs, and reviews match suggestions. |
| `student` | Student / Campus Member | `true` | Standard authenticated user. Can report lost items, report found items, upload item photos, submit ownership claims with evidence, track claim progress, update notification preferences, and manage personal profile. |
| `guest` | Unauthenticated Guest | `false` | Public visitor. Can search and browse the public catalog of found items (with sensitive fields redacted), view categories and locations, track item status via public reference code, and confirm item returns via signed link. |

---

### 3. Functional Modules

#### 3.1 Authentication & Profile Module (AUTH)

##### FR-AUTH-001: User Self-Registration
<!-- source: app/Http/Controllers/Api/V1/Auth/RegisterController.php:24-52 -->
<!-- source: app/Http/Requests/Api/V1/Auth/RegisterRequest.php:14-38 -->
<!-- source: app/Domain/Auth/Actions/RegisterUser.php:21-48 -->
- **Description:** Allows prospective users to register an account using institutional identity attributes (name, email, institutional student/staff ID, phone number, password, campus, and organizational unit).
- **Trigger:** Guest submits POST request to `/api/v1/auth/register`.
- **Precondition:** Guest is unauthenticated.
- **Postcondition:** A `users` record is created with `is_active = true`, `email_verified_at = null`, a default `student` role assigned, a 6-digit numeric OTP created in `auth_verifications`, and `RegistrationOtpMail` queued.
- **Business Rules:** 
  - Email must be unique in `users` (`users,email`).
  - Password must be at least 8 characters with mixed case, numbers, and symbols.
  - Campus ID and Department/Org Unit ID must exist in `campuses` and `organizational_units`.
  - Student/Employee ID number is required and validated.
- **DB Artifacts:** `users`, `user_profiles`, `permission_role`, `auth_verifications`.

##### FR-AUTH-002: Email OTP Verification
<!-- source: app/Http/Controllers/Api/V1/Auth/VerificationController.php:20-56 -->
<!-- source: app/Http/Requests/Api/V1/Auth/VerifyOtpRequest.php:12-25 -->
<!-- source: app/Domain/Auth/Actions/VerifyRegistrationOtp.php:18-45 -->
- **Description:** Verifies user account using a 6-digit one-time password (OTP) sent via email.
- **Trigger:** User submits POST request to `/api/v1/auth/verify-email`.
- **Precondition:** User exists, has unverified email, and valid OTP record exists in `auth_verifications`.
- **Postcondition:** `users.email_verified_at` is set to current timestamp, `auth_verifications.consumed_at` is marked, and authentication token is issued.
- **Business Rules:** 
  - OTP expires after 15 minutes (`expires_at < now()`).
  - Maximum 5 failed verification attempts before invalidation.
- **DB Artifacts:** `users.email_verified_at`, `auth_verifications.consumed_at`.

##### FR-AUTH-003: Resend Verification OTP
<!-- source: app/Http/Controllers/Api/V1/Auth/VerificationController.php:60-95 -->
<!-- source: app/Http/Requests/Api/V1/Auth/ResendOtpRequest.php:12-25 -->
- **Description:** Re-issues a fresh 6-digit verification OTP to user's registered email address.
- **Trigger:** POST `/api/v1/auth/resend-verification`.
- **Precondition:** User registered, `email_verified_at` is null.
- **Postcondition:** Previous active OTPs for type `email_verification` invalidated, new OTP generated and dispatched via `SendRegistrationOtp` job.
- **Business Rules:** Rate-limited to prevent abuse (60-second cooldown).
- **DB Artifacts:** `auth_verifications`.

##### FR-AUTH-004: User Authentication (Login)
<!-- source: app/Http/Controllers/Api/V1/Auth/LoginController.php:23-58 -->
<!-- source: app/Http/Requests/Api/V1/Auth/LoginRequest.php:14-28 -->
<!-- source: app/Domain/Auth/Actions/LoginUser.php:22-55 -->
- **Description:** Authenticates user credentials and issues a Laravel Sanctum personal access token.
- **Trigger:** POST `/api/v1/auth/login`.
- **Precondition:** Valid credentials (email and password).
- **Postcondition:** Plaintext Bearer token returned with user profile, role permissions, and active session record.
- **Business Rules:**
  - Account must be active (`users.is_active = true`), otherwise throws `AccountInactiveException` (403).
  - Rate-limited to 5 consecutive failed attempts before temporary lockout.
- **DB Artifacts:** `personal_access_tokens`, `sessions`.

##### FR-AUTH-005: User Session Invalidation (Logout)
<!-- source: app/Http/Controllers/Api/V1/Auth/LogoutController.php:16-32 -->
<!-- source: app/Domain/Auth/Actions/LogoutUser.php:15-28 -->
- **Description:** Revokes current user's active Sanctum access token.
- **Trigger:** POST `/api/v1/auth/logout`.
- **Precondition:** Request includes valid Bearer token.
- **Postcondition:** Current access token deleted from `personal_access_tokens`.
- **DB Artifacts:** `personal_access_tokens`.

##### FR-AUTH-006: Password Reset Workflow (Request, Verify OTP, Reset)
<!-- source: app/Http/Controllers/Api/V1/Auth/PasswordResetController.php:22-90 -->
<!-- source: app/Domain/Auth/Actions/RequestPasswordReset.php:18-42 -->
<!-- source: app/Domain/Auth/Actions/ResetPassword.php:20-50 -->
- **Description:** Three-step self-service password recovery using email OTP verification.
- **Trigger:** POST `/api/v1/auth/forgot-password`, POST `/api/v1/auth/verify-password-reset`, POST `/api/v1/auth/reset-password`.
- **Precondition:** Registered user email.
- **Postcondition:** Password updated, previous password archived in `password_histories`, all existing tokens revoked, audit logged.
- **Business Rules:** New password cannot match the last 3 passwords in `password_histories`.
- **DB Artifacts:** `users.password`, `password_histories`, `auth_verifications`.

##### FR-AUTH-007: Authenticated Profile & Avatar Management
<!-- source: app/Http/Controllers/Api/V1/ProfileController.php:20-60 -->
<!-- source: app/Http/Requests/Api/V1/UpdateProfileRequest.php:14-30 -->
<!-- source: app/Http/Requests/Api/V1/UploadAvatarRequest.php:14-25 -->
- **Description:** Allows user to view/update profile information (phone, alternate email, notification settings) and upload avatar image.
- **Trigger:** GET/PUT `/api/v1/profile`, POST `/api/v1/profile/avatar`.
- **Precondition:** Authenticated user.
- **Postcondition:** `user_profiles` updated, old avatar deleted from storage disk, new avatar path saved.
- **Business Rules:** Avatar must be an image (JPEG, PNG, WEBP), max size 2048 KB.
- **DB Artifacts:** `user_profiles`, `users`.

---

#### 3.2 Items Management Module (ITEMS)

##### FR-ITEMS-001: Report Lost Item
<!-- source: app/Http/Controllers/Api/V1/Items/ItemController.php:48-75 -->
<!-- source: app/Http/Requests/Api/V1/Items/StoreLostItemRequest.php:14-45 -->
<!-- source: app/Domain/Items/Actions/CreateLostItem.php:24-60 -->
- **Description:** Allows an authenticated student or staff member to report a lost property item.
- **Trigger:** POST `/api/v1/items/lost`.
- **Precondition:** Authenticated user.
- **Postcondition:** Item created with `type = 'lost'`, `status = 'reported'`, auto-generated `reference_code` (e.g. `LOST-2026-XXXXX`), status history logged, and `ItemReported` event dispatched.
- **Business Rules:**
  - `date_lost_or_found` cannot be in the future.
  - `campus_id`, `category_id`, and `location_id` must exist.
  - Dispatches `DispatchMatchSuggestion` listener to find potential matching found items.
- **DB Artifacts:** `items`, `item_status_histories`, `item_tags`.

##### FR-ITEMS-002: Report Found Item
<!-- source: app/Http/Controllers/Api/V1/Items/ItemController.php:79-110 -->
<!-- source: app/Http/Requests/Api/V1/Items/StoreFoundItemRequest.php:14-50 -->
<!-- source: app/Domain/Items/Actions/CreateFoundItem.php:24-65 -->
- **Description:** Allows an authenticated user or staff member to report an item found on campus.
- **Trigger:** POST `/api/v1/items/found`.
- **Precondition:** Authenticated user.
- **Postcondition:** Item created with `type = 'found'`, `status = 'reported'`, `reference_code` (e.g. `FND-2026-XXXXX`), initial custody event logged if finder is staff, status history logged, `ItemReported` event dispatched.
- **Business Rules:**
  - `storage_location_id` is required if reported directly by staff.
  - Dispatches background matching job `GenerateMatchSuggestions`.
- **DB Artifacts:** `items`, `item_status_histories`, `custody_events`.

##### FR-ITEMS-003: Check Item Duplicate Before Submission
<!-- source: app/Http/Controllers/Api/V1/Items/ItemController.php:35-45 -->
<!-- source: app/Domain/Items/Services/DuplicateDetectionService.php:18-50 -->
- **Description:** Real-time pre-submission check that alerts user if an identical item was already reported.
- **Trigger:** POST `/api/v1/items/check-duplicate`.
- **Precondition:** Authenticated user providing `title`, `category_id`, `campus_id`, and `date_lost_or_found`.
- **Postcondition:** Returns list of potential duplicate items with similarity score.
- **Business Rules:** Checks items of the same type reported within a 14-day sliding window in the same category and campus.
- **DB Artifacts:** `items` (read-only query).

##### FR-ITEMS-004: Item Photo Upload & Management
<!-- source: app/Http/Controllers/Api/V1/Items/ItemPhotoController.php:20-65 -->
<!-- source: app/Http/Requests/Api/V1/Items/UploadItemPhotoRequest.php:14-28 -->
- **Description:** Uploads multiple photo attachments for an item and deletes existing photos.
- **Trigger:** POST `/api/v1/items/{id}/photos`, DELETE `/api/v1/items/{id}/photos/{photoId}`.
- **Precondition:** User is the item reporter, or holds `staff`/`admin` role (`ItemPolicy@update`).
- **Postcondition:** Photo stored in `storage/app/public/items/`, `item_photos` record inserted with `file_path`, `file_size`, `mime_type`, `is_primary` flag.
- **Business Rules:** Maximum 5 photos per item; allowed formats: JPEG, PNG, WEBP; max size 5120 KB per photo.
- **DB Artifacts:** `item_photos`.

##### FR-ITEMS-005: Item Lifecycle Status Transition
<!-- source: app/Http/Controllers/Api/V1/Items/ItemStatusController.php:20-55 -->
<!-- source: app/Http/Requests/Api/V1/Items/ChangeItemStatusRequest.php:14-30 -->
<!-- source: app/Domain/Items/Actions/ChangeItemStatus.php:20-58 -->
- **Description:** Updates the status of an item along its state machine (`reported` → `under_review` → `claimed` → `returned` / `disposed` / `withdrawn`).
- **Trigger:** PATCH `/api/v1/items/{id}/status`.
- **Precondition:** Authenticated staff or admin, or item owner (only for `withdrawn`).
- **Postcondition:** `items.status` updated, new entry inserted into `item_status_histories` with `changed_by_user_id` and optional `reason`.
- **Business Rules:** Transitions strictly validated via `InvalidItemStatusTransition` exception; terminal states (`returned`, `disposed`) cannot be reverted without administrative override.
- **DB Artifacts:** `items.status`, `item_status_histories`.

##### FR-ITEMS-006: Reporter Item Withdrawal
<!-- source: app/Http/Controllers/Api/V1/Items/ItemController.php:140-160 -->
<!-- source: app/Domain/Items/Actions/WithdrawItem.php:18-42 -->
- **Description:** Allows an item reporter to voluntarily withdraw their lost/found report (e.g. found by themselves).
- **Trigger:** PATCH `/api/v1/items/{id}/withdraw`.
- **Precondition:** Authenticated user is the reporter of the item; item status is `reported` or `under_review`.
- **Postcondition:** Item status set to `withdrawn`, history logged, active match suggestions dismissed.
- **DB Artifacts:** `items.status`, `item_status_histories`.

##### FR-ITEMS-007: Public Item Discovery & Search
<!-- source: app/Http/Controllers/Api/V1/Public/PublicItemController.php:22-75 -->
<!-- source: app/Http/Requests/Api/V1/Public/ItemSearchRequest.php:14-40 -->
<!-- source: app/Domain/Items/Services/ItemSearchService.php:20-80 -->
- **Description:** Publicly discoverable catalog of found items with filtering by category, campus, location, and date range.
- **Trigger:** GET `/api/v1/public/items`, GET `/api/v1/public/items/{id}`.
- **Precondition:** None (public guest route).
- **Postcondition:** Returns paginated found items with redacted sensitive fields (e.g. storage shelf numbers, full finder identity hidden). Increments `item_views.views_count` and records `search_logs`.
- **Business Rules:** Only shows items where `type = 'found'` and `status IN ('reported', 'under_review')`.
- **DB Artifacts:** `item_views`, `search_logs`.

---

#### 3.3 Claims Management Module (CLAIMS)

##### FR-CLAIMS-001: Submit Ownership Claim
<!-- source: app/Http/Controllers/Api/V1/Claims/ClaimController.php:30-68 -->
<!-- source: app/Http/Requests/Api/V1/Claims/StoreClaimRequest.php:14-40 -->
<!-- source: app/Domain/Claims/Actions/CreateClaim.php:22-65 -->
- **Description:** Authenticated user asserts ownership of a registered found item by submitting verification details and supporting evidence photos.
- **Trigger:** POST `/api/v1/claims`.
- **Precondition:** Authenticated user (`student` or `staff`); target item exists, is of type `found`, and is not already `returned` or `disposed`.
- **Postcondition:** `claims` record created with status `submitted`, evidence photos uploaded to `claim_evidence`, `claim_status_histories` logged, `ClaimSubmitted` event dispatched.
- **Business Rules:**
  - User cannot submit multiple active claims for the same item (`DuplicateClaimException`).
  - Reporter of the found item cannot claim their own found item (`ClaimNotAllowedException`).
- **DB Artifacts:** `claims`, `claim_evidence`, `claim_status_histories`.

##### FR-CLAIMS-002: Review Claim (Approve / Reject)
<!-- source: app/Http/Controllers/Api/V1/Claims/ClaimReviewController.php:22-60 -->
<!-- source: app/Http/Requests/Api/V1/Claims/ClaimReviewRequest.php:14-30 -->
<!-- source: app/Domain/Claims/Actions/ApproveClaim.php:22-55 -->
<!-- source: app/Domain/Claims/Actions/RejectClaim.php:20-48 -->
- **Description:** Staff member evaluates claim evidence and renders a formal approval or rejection decision.
- **Trigger:** POST `/api/v1/claims/{id}/review`.
- **Precondition:** Authenticated user with `staff` or `admin` role (`ClaimPolicy@review`); claim status is `submitted` or `under_review`.
- **Postcondition:** If approved: `claims.status = 'approved'`, `items.status = 'claimed'`, other competing claims on same item automatically moved to `rejected`, `ClaimApproved` event dispatched (triggers notification & email to claimant). If rejected: `claims.status = 'rejected'`, reason recorded, `ClaimRejected` event dispatched.
- **Business Rules:** Requires mandatory `remarks` when rejecting; approval locks competing claims from approval.
- **DB Artifacts:** `claims.status`, `claims.reviewed_by_user_id`, `claims.reviewed_at`, `claim_status_histories`, `items.status`.

##### FR-CLAIMS-003: Reverse Claim Approval
<!-- source: app/Http/Controllers/Api/V1/Claims/ClaimReviewController.php:64-90 -->
<!-- source: app/Http/Requests/Api/V1/Claims/ReverseClaimRequest.php:12-25 -->
<!-- source: app/Domain/Claims/Actions/ReverseClaimApproval.php:18-50 -->
- **Description:** Administrative override to reverse an approved claim prior to physical handover completion.
- **Trigger:** POST `/api/v1/claims/{id}/reverse`.
- **Precondition:** Authenticated user with `admin` role (`ClaimPolicy@reverse`); claim is `approved` and associated item has not yet completed return handover (`items.status != 'returned'`).
- **Postcondition:** Claim status reset to `under_review`, item status restored to `under_review`, audit log recorded.
- **DB Artifacts:** `claims.status`, `items.status`, `claim_status_histories`, `audit_logs`.

---

#### 3.4 Automated Item Matching Module (MATCHING)

##### FR-MATCH-001: Automated Match Suggestions Generation
<!-- source: app/Domain/Matching/Services/ItemMatchingService.php:24-85 -->
<!-- source: app/Domain/Matching/Services/TokenizerService.php:15-45 -->
<!-- source: app/Domain/Matching/Services/JaccardSimilarityService.php:12-38 -->
<!-- source: app/Jobs/GenerateMatchSuggestions.php:20-60 -->
- **Description:** Executes background text tokenization, keyword extraction, and Jaccard similarity computation between new item reports and opposing item catalogues (`lost` vs `found`).
- **Trigger:** Dispatched asynchronously via `ItemReported` event listener (`DispatchMatchSuggestion`).
- **Precondition:** New lost or found item created with valid title/description.
- **Postcondition:** Calculates composite score combining text similarity, category matching (exact match weight), campus proximity, and date delta; records pairings with similarity score ≥ 0.40 into `match_suggestions` table.
- **Business Rules:**
  - Duplicate suggestion pairings (`lost_item_id`, `found_item_id`) are deduplicated via unique database constraint.
  - High confidence matches (score ≥ 0.75) queue `SendMatchNotification` to reporters.
- **DB Artifacts:** `match_suggestions`.

##### FR-MATCH-002: Match Suggestion Staff Review & Status Update
<!-- source: app/Http/Controllers/Api/V1/Admin/MatchSuggestionController.php:20-58 -->
- **Description:** Allows staff/admin to view generated match candidates, confirm true matches, or dismiss false positives.
- **Trigger:** GET `/api/v1/match-suggestions`, PATCH `/api/v1/match-suggestions/{id}`.
- **Precondition:** Authenticated staff or admin (`role:staff,admin`).
- **Postcondition:** `match_suggestions.status` updated to `confirmed`, `dismissed`, or `resolved`.
- **DB Artifacts:** `match_suggestions.status`, `match_suggestions.reviewed_by_user_id`.

---

#### 3.5 Physical Custody & Storage Management (CUSTODY)

##### FR-CUST-001: Register Custody Intake & Transfer
<!-- source: app/Http/Controllers/Api/V1/Custody/CustodyController.php:22-65 -->
<!-- source: app/Http/Requests/Api/V1/Custody/StoreCustodyEventRequest.php:14-35 -->
<!-- source: app/Domain/Custody/Actions/RecordCustodyEvent.php:20-50 -->
- **Description:** Records physical receipt, transfer, or relocation of a found item into secure campus storage.
- **Trigger:** POST `/api/v1/custody`.
- **Precondition:** Authenticated staff/admin; item exists.
- **Postcondition:** `custody_events` record inserted with event type (`received`, `transferred`, `relocated`, `released`, `disposed`), source/destination storage IDs, handler user ID, and timestamps.
- **DB Artifacts:** `custody_events`, `items.storage_location_id`.

##### FR-CUST-002: Move Item Storage Location
<!-- source: app/Http/Controllers/Api/V1/Custody/CustodyController.php:70-95 -->
<!-- source: app/Http/Requests/Api/V1/Custody/MoveStorageRequest.php:14-28 -->
<!-- source: app/Domain/Custody/Actions/MoveItemToStorage.php:20-45 -->
- **Description:** Relocates an item to a new storage bin, shelf, or security room and logs the chain of custody.
- **Trigger:** POST `/api/v1/custody/items/{itemId}/move`.
- **Precondition:** Authenticated staff; new `storage_location_id` must exist in `storage_locations`.
- **Postcondition:** `items.storage_location_id` updated, `custody_events` entry created with `event_type = 'transferred'`, previous location archived.
- **DB Artifacts:** `items.storage_location_id`, `custody_events`.

---

#### 3.6 Item Returns & Handover Verification Module (RETURNS)

##### FR-RET-001: Initiate Return Record & Generate Token
<!-- source: app/Http/Controllers/Api/V1/Returns/ReturnController.php:35-70 -->
<!-- source: app/Http/Requests/Api/V1/Returns/StoreReturnRequest.php:14-38 -->
<!-- source: app/Domain/Returns/Actions/RecordReturn.php:22-65 -->
- **Description:** Staff prepares an approved claim for physical handover, recording recipient identity verification, ID document type/number, and generating a cryptographically secure single-use confirmation token.
- **Trigger:** POST `/api/v1/returns`.
- **Precondition:** Authenticated staff; associated `claim_id` is `approved` and `item_id` has status `claimed`.
- **Postcondition:** `returns` record created with unique `verification_code`, secure 64-char `confirmation_token`, status `pending_confirmation`, and dispatched `SendReturnNotification` job.
- **DB Artifacts:** `returns`, `return_documents`.

##### FR-RET-002: Dual-Mode Return Confirmation
<!-- source: app/Http/Controllers/Api/V1/Returns/ReturnController.php:75-135 -->
<!-- source: app/Domain/Returns/Actions/GenerateReturnAcknowledgement.php:20-55 -->
- **Description:** Finalizes item release through either:
  1. Authenticated staff confirmation with recipient base64 digital signature (`POST /api/v1/returns/{id}/confirm`).
  2. Public token confirmation link clicked by recipient with drawn signature (`POST /api/v1/returns/confirm-token/{token}`).
- **Trigger:** Submission to either confirmation endpoint.
- **Precondition:** Return record exists in `pending_confirmation` state; single-use token not expired or consumed.
- **Postcondition:** `returns.status = 'confirmed'`, `returns.returned_at = now()`, `returns.recipient_signature` stored, `items.status = 'returned'`, release custody event logged, `ItemReturned` event dispatched, return acknowledgement PDF generated.
- **Business Rules:** Once confirmed, return is immutable. Token is invalidated immediately upon first use.
- **DB Artifacts:** `returns.status`, `returns.returned_at`, `returns.recipient_signature`, `items.status`, `custody_events`.

##### FR-RET-003: Handover CSV Export
<!-- source: app/Http/Controllers/Api/V1/Returns/ReturnController.php:140-175 -->
- **Description:** Generates formatted CSV export of completed returns and physical handovers for institutional audits.
- **Trigger:** GET `/api/v1/returns/export/csv`.
- **Precondition:** Authenticated staff or admin (`role:staff,admin`).
- **Postcondition:** Streams downloadable CSV file with reference codes, recipient identity, verification IDs, handover dates, and releasing officer.
- **DB Artifacts:** `returns`, `items`, `users`, `claims`.

---

#### 3.7 Notifications Module (NOTIFICATIONS)

##### FR-NOTIF-001: Real-time Server-Sent Events (SSE) Stream
<!-- source: app/Http/Controllers/Api/V1/Notifications/RealtimeNotificationController.php:18-65 -->
<!-- source: app/Domain/Notifications/Services/NotificationService.php:20-60 -->
- **Description:** Persistent HTTP streaming connection delivering real-time notification events to the client without polling.
- **Trigger:** GET `/api/v1/notifications/stream`.
- **Precondition:** Authenticated user with valid Sanctum session.
- **Postcondition:** Streams `text/event-stream` formatted events (claim updates, match suggestions, system announcements).
- **Business Rules:** Auto-reconnect heartbeat every 15 seconds; fetches unread count on initial handshake.
- **DB Artifacts:** `notifications`.

##### FR-NOTIF-002: Notification Inbox & Read State Management
<!-- source: app/Http/Controllers/Api/V1/Notifications/NotificationController.php:20-65 -->
- **Description:** Lists user notifications, marks individual notification as read, and bulk-clears all unread notifications.
- **Trigger:** GET `/api/v1/notifications`, PATCH `/api/v1/notifications/{id}/read`, PATCH `/api/v1/notifications/read-all`.
- **Precondition:** Authenticated user.
- **Postcondition:** `notifications.read_at` set to current timestamp.
- **DB Artifacts:** `notifications.read_at`.

##### FR-NOTIF-003: User Notification Preferences
<!-- source: app/Http/Controllers/Api/V1/Notifications/NotificationPreferenceController.php:20-55 -->
<!-- source: app/Http/Requests/Api/V1/Notifications/UpdateNotificationPreferencesRequest.php:12-30 -->
- **Description:** Manages granular delivery channel preferences (email, in-app, SMS) per notification category (claims, matches, returns, announcements).
- **Trigger:** GET/PUT `/api/v1/notifications/preferences`.
- **Precondition:** Authenticated user.
- **Postcondition:** `notification_preferences` record updated.
- **DB Artifacts:** `notification_preferences`.

---

#### 3.8 RBAC & User Administration Module (ADMIN)

##### FR-RBAC-001: Dynamic Role & Permission Management
<!-- source: app/Http/Controllers/Api/V1/Admin/RoleController.php:20-75 -->
<!-- source: app/Http/Controllers/Api/V1/Admin/PermissionController.php:20-60 -->
<!-- source: app/Support/Services/RoleService.php:25-95 -->
<!-- source: app/Support/Services/PermissionService.php:20-85 -->
- **Description:** Complete administrative CRUD over system roles, permission groups, and permission nodes, including syncing permissions to roles.
- **Trigger:** Standard API Resource endpoints under `/api/v1/admin/roles`, `/api/v1/admin/permissions`, `/api/v1/admin/permission-groups`.
- **Precondition:** Authenticated admin (`role:admin`).
- **Postcondition:** Role/permission records updated, `User::flushPermissionCache()` automatically called to invalidate cached authorization trees.
- **Business Rules:** System roles (`is_system = true`, e.g. `admin`, `staff`, `student`) cannot be deleted or renamed.
- **DB Artifacts:** `roles`, `permissions`, `permission_groups`, `permission_role`.

##### FR-RBAC-002: User Account Administration & Role Assignment
<!-- source: app/Http/Controllers/Api/V1/Admin/UserManagementController.php:25-110 -->
<!-- source: app/Http/Requests/Api/V1/Admin/UpdateUserRoleRequest.php:14-28 -->
<!-- source: app/Domain/Administration/Services/UserAdministrationService.php:25-90 -->
- **Description:** Admin creates users, updates account attributes, assigns roles, toggles account active state, and overrides direct permissions.
- **Trigger:** POST/PUT/PATCH `/api/v1/admin/users/*`.
- **Precondition:** Authenticated admin (`role:admin`).
- **Postcondition:** User profile updated, role assigned in `roles` pivot or `users.role_id`, direct permissions synced in `permission_user`, permission cache flushed, audit logged.
- **Business Rules:** Admin cannot deactivate their own active account or revoke their own admin role.
- **DB Artifacts:** `users`, `permission_user`, `audit_logs`.

---

#### 3.9 Audit Logging & Reporting Module (AUDIT)

##### FR-AUDIT-001: Immutable Security Audit Logging
<!-- source: app/Support/Services/AuditLogger.php:15-55 -->
<!-- source: app/Http/Controllers/Api/V1/Admin/AuditLogController.php:20-60 -->
- **Description:** Automatically records structured audit entries for all state mutations (auth events, claim reviews, custody moves, return handovers, role modifications).
- **Trigger:** Invoked across domain services and controllers.
- **Precondition:** Event triggered by user or system action.
- **Postcondition:** Immutable record inserted into `audit_logs` containing `user_id`, `action`, `auditable_type`, `auditable_id`, `old_values`, `new_values`, `ip_address`, `user_agent`.
- **Business Rules:** Audit logs are strictly append-only (no update/delete routes exist).
- **DB Artifacts:** `audit_logs`.

##### FR-AUDIT-002: Dashboard Statistics & Administrative Reports
<!-- source: app/Http/Controllers/Api/V1/Admin/DashboardController.php:20-55 -->
<!-- source: app/Http/Controllers/Api/V1/Admin/ReportController.php:20-70 -->
<!-- source: app/Domain/Administration/Services/DashboardStatisticsService.php:20-80 -->
<!-- source: app/Domain/Administration/Services/ReportExportService.php:20-75 -->
- **Description:** Aggregates real-time metrics (lost vs found counts, claim resolution rates, return success ratios, campus breakdowns) and generates downloadable reports (PDF, CSV).
- **Trigger:** GET `/api/v1/admin/dashboard/statistics`, POST `/api/v1/admin/reports/generate`, GET `/api/v1/admin/reports/{id}/download`.
- **Precondition:** Authenticated admin (`role:admin`).
- **Postcondition:** Statistics JSON returned or asynchronous `GenerateReportExport` job dispatched to build report file.
- **DB Artifacts:** `reports`, `audit_logs`, `items`, `claims`, `returns`.

---

### 4. Non-Functional Requirements

#### 4.1 Performance & Indexing
<!-- source: database/migrations/2026_08_31_120000_add_performance_composite_indexes.php:14-65 -->
- Search queries over items must resolve in under 100ms for datasets exceeding 100,000 records.
- Database index coverage:
  - Composite indexes placed on high-frequency compound predicates: `items(type, status, campus_id)`, `items(status, date_lost_or_found)`, `claims(status, claimant_id)`, `match_suggestions(lost_item_id, found_item_id, status)`.
  - FULLTEXT indexes on `items(title, description)` for fast lexical matching.

#### 4.2 Security & Authorization
<!-- source: app/Http/Middleware/EnsureUserHasRole.php:15-35 -->
<!-- source: app/Models/User.php:120-210 -->
- Route-level defense-in-depth: Sanctum token authentication + `role:staff,admin` middleware + Eloquent Gate/Policy layer checks.
- CSRF protection enabled on stateful session endpoints.
- Rate limiting applied to sensitive endpoints (e.g. login: 5 req/min, public tracking: 20 req/min, OTP requests: 3 req/min).
- Sensitive fields redacted from public API serializers.

#### 4.3 Data Integrity & Transaction Boundaries
<!-- source: app/Domain/Returns/Actions/RecordReturn.php:25-50 -->
<!-- source: app/Domain/Claims/Actions/ApproveClaim.php:25-52 -->
- All multi-table mutations (such as claim approvals, custody moves, and return confirmations) are wrapped in `DB::transaction()` blocks to guarantee ACID compliance.
- Foreign keys strictly enforce referential integrity with cascading deletes for owned children (`item_photos`, `claim_evidence`) and `RESTRICT` on core relational entities (`campuses`, `categories`).

#### 4.4 Internationalization (i18n)
<!-- source: database/migrations/2026_08_30_104858_create_roles_table.php:18-20 -->
<!-- source: database/migrations/2026_08_30_104912_create_categories_table.php:18-20 -->
<!-- source: database/migrations/2026_08_30_104906_create_campuses_table.php:18-20 -->
- Bilingual data model: Reference entities (`campuses`, `categories`, `locations`, `roles`, `permissions`, `organizational_units`) store dual-language representations (`name`/`display_name` in English and `name_am`/`display_name_am` in Amharic).

---

### 5. Constraints & Exclusions

1. **Storage Subsystem:** Local or S3-compatible object storage via Laravel Storage facade. All file uploads are verified for MIME signatures to mitigate file injection attacks.
2. **Email Subsystem:** Asynchronous email dispatching through Laravel Queue worker (`jobs` table). In local development, the system falls back to `log` or `smtp` mailers.
3. **Session Lifespan:** Sanctum personal access tokens are persisted until explicit logout or administrative revocation. Verification OTPs strictly expire in 15 minutes.
4. **Zero Assumed Features:** No biometric hardware integrations or barcode scanners are mandated; all return verification relies on standard alphanumeric verification codes, signed token URLs, and touch/mouse canvas signature capture.
