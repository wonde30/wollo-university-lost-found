---
title: Database Architecture & Schema Reference
version: 1.0.0
system: Wollo University Lost & Found System
generated_from: source code (migrations, controllers, policies, routes, seeders)
date: 2026-08-31
---

# Database Architecture & Schema Reference
## Wollo University Lost & Found System

<!-- source: database/migrations/ -->
<!-- source: app/Models/ -->

---

### 1. Schema Overview

- **Database Engine:** MySQL 8.0+ / InnoDB (ACID Compliant, Row-Level Locking, Foreign Key Constraints)
- **ORM:** Eloquent ORM (Laravel 13.x)
- **Total Migration Count:** 43 migrations
- **Total Table Count:** 43 tables (including framework operational tables)
- **Normalization Level:** Third Normal Form (3NF) across all domain clusters, with strictly controlled and documented performance denormalizations (e.g. `item_views.views_count` aggregation caching).

---

### 2. Entity-Relationship Summary

#### 2.1 Authentication & RBAC Cluster
<!-- source: database/migrations/2026_08_30_104858_create_roles_table.php:1-35 -->
<!-- source: database/migrations/2026_08_30_104905_create_users_table.php:1-40 -->
The Authentication and RBAC cluster encapsulates institutional user identities, granular permissions, and dynamic role hierarchies. The core `users` entity represents students, faculty, and administrative staff. Every user has an associated `user_profiles` record storing extended personal and contact attributes. The RBAC architecture establishes a flexible multi-role and direct permission matrix: `roles` group permissions via `permission_role`, permissions are grouped logically in `permission_groups`, and individual users can receive specific permission overrides directly through `permission_user`. Account recovery and registration verification are decoupled into the `auth_verifications` table (handling time-bounded OTP tokens), while `password_histories` enforces password rotation policies by preventing reuse of recent password hashes.

#### 2.2 Institutional Hierarchy & Campus Infrastructure Cluster
<!-- source: database/migrations/2026_08_30_104906_create_campuses_table.php:1-30 -->
<!-- source: database/migrations/2026_08_30_104907_create_organizational_units_table.php:1-40 -->
The institutional structure models Wollo University physical campuses and academic departments. The `campuses` table anchors physical locations (Dessie Campus, Kombolcha Campus). The `organizational_unit_types` and `organizational_unit_type_relations` establish allowed organizational parent-child hierarchies (e.g., College -> Department -> Program). `organizational_units` maintains the self-referential organizational hierarchy linked to specific campuses. Users are affiliated with organizational units via `user_organizational_units`, establishing department-level associations for students and staff.

#### 2.3 Items & Catalogues Cluster
<!-- source: database/migrations/2026_08_30_104915_create_items_table.php:1-60 -->
<!-- source: database/migrations/2026_08_30_104916_create_item_photos_table.php:1-35 -->
The Items cluster forms the core operational catalogue of the system. The central `items` table maintains both lost property reports (filed by losers) and found property records (filed by finders or security staff). Items reference taxonomical classifications via `categories`, physical discovery points via `locations` (which belong to `campuses`), and secure holding rooms via `storage_locations`. Each item can possess multiple media attachments in `item_photos`, keyword metadata tags in `item_tags`, and an append-only timeline of state changes in `item_status_histories`. Anonymous public engagement is tracked via `item_views` and `search_logs`.

#### 2.4 Claims & Evidence Cluster
<!-- source: database/migrations/2026_08_30_104921_create_claims_table.php:1-45 -->
<!-- source: database/migrations/2026_08_30_104923_create_claim_evidence_table.php:1-35 -->
The Claims cluster models user assertions of ownership over found items. The `claims` table links an authenticated claimant (`users.id`) to a registered found item (`items.id`). Claimants attach supporting proof through `claim_evidence` (images of receipts, serial numbers, purchase invoices). Formal staff reviews (approvals, rejections, reversals) and their contextual audit trails are captured sequentially in `claim_status_histories`.

#### 2.5 Custody, Handover & Returns Cluster
<!-- source: database/migrations/2026_08_30_104919_create_custody_events_table.php:1-35 -->
<!-- source: database/migrations/2026_08_30_104925_create_returns_table.php:1-50 -->
The Custody and Returns cluster enforces chain-of-custody logging and physical item handover verification. `custody_events` maintains an immutable ledger of every physical item movement between staff handlers and storage locations. Once a claim is approved, a `returns` record coordinates the handover process: it captures recipient verification details, single-use confirmation tokens, releasing staff signatures, recipient digital signatures, and links to generated PDF handover receipts stored in `return_documents`.

#### 2.6 Automated Matching, Notifications & Administration Cluster
<!-- source: database/migrations/2026_08_30_104920_create_match_suggestions_table.php:1-40 -->
<!-- source: database/migrations/2026_08_30_104927_create_notifications_table.php:1-35 -->
`match_suggestions` links potential pairings between `items` of type `lost` and `items` of type `found` based on NLP similarity metrics. `notifications` records in-app and SSE messages delivered to users, while `notification_preferences` controls user delivery channels. `audit_logs` captures administrative and state mutations. `system_settings` and `system_announcements` manage system-wide parameters and broadcast alerts. `reports` tracks generated analytical export jobs.

---

### 3. Table Specifications

#### `audit_logs`
<!-- source: database/migrations/2026_08_30_104931_create_audit_logs_table.php -->
**Purpose:** Stores relational records for `audit_logs` in the Wollo University Lost & Found schema.
**Migration:** `2026_08_30_104931_create_audit_logs_table.php`

| Column | Type | Nullable | Default | Constraint | Notes |
|--------|------|----------|---------|------------|-------|
| `id` | `bigint unsigned` | `NO` | NULL | PRIMARY KEY (auto_increment) | Auto-incrementing surrogate primary key, Unsigned numeric |
| `actor_id` | `bigint unsigned` | `YES` | NULL | INDEX / FK | Relational foreign key reference, Unsigned numeric |
| `actor_role` | `varchar(255)` | `YES` | NULL | None | Standard column attribute |
| `action` | `varchar(255)` | `NO` | NULL | INDEX / FK | Mandatory field |
| `auditable_type` | `varchar(255)` | `YES` | NULL | INDEX / FK | Standard column attribute |
| `auditable_id` | `bigint unsigned` | `YES` | NULL | None | Relational foreign key reference, Unsigned numeric |
| `old_values` | `json` | `YES` | NULL | None | Standard column attribute |
| `new_values` | `json` | `YES` | NULL | None | Standard column attribute |
| `ip_address` | `varchar(45)` | `YES` | NULL | None | Standard column attribute |
| `user_agent` | `text` | `YES` | NULL | None | Standard column attribute |
| `session_id` | `varchar(255)` | `YES` | NULL | None | Relational foreign key reference |
| `created_at` | `timestamp` | `YES` | NULL | None | Laravel timestamp audit |
| `updated_at` | `timestamp` | `YES` | NULL | None | Laravel timestamp audit |

**Indexes:**
- **`PRIMARY`**: Type `PRIMARY`, Columns: (`id`)
- **`audit_logs_auditable_type_auditable_id_index`**: Type `INDEX`, Columns: (`auditable_type`, `auditable_id`)
- **`audit_actor_created_idx`**: Type `INDEX`, Columns: (`actor_id`, `created_at`)
- **`audit_action_created_idx`**: Type `INDEX`, Columns: (`action`, `created_at`)

**Foreign Keys:**
- **`actor_id`** → `users.id` (ON DELETE: `SET NULL`, ON UPDATE: `RESTRICT`)

**Relationships (Eloquent):**
- Model: [`App\Models\AuditLog`](file:///C:\Users\W\Desktop\wollo-lost-found\server/app/Models/AuditLog.php)
  - `$this->actor()`: `belongsTo` (`User::class, 'actor_id'`) <!-- source: app/Models/AuditLog.php:58 -->
  - `$this->user()`: `belongsTo` (`User::class, 'actor_id'`) <!-- source: app/Models/AuditLog.php:64 -->
  - `$this->auditable()`: `morphTo` (``) <!-- source: app/Models/AuditLog.php:72 -->

---

#### `auth_verifications`
<!-- source: database/migrations/2026_08_30_104936_create_auth_verifications_table.php -->
**Purpose:** Stores relational records for `auth_verifications` in the Wollo University Lost & Found schema.
**Migration:** `2026_08_30_104936_create_auth_verifications_table.php`

| Column | Type | Nullable | Default | Constraint | Notes |
|--------|------|----------|---------|------------|-------|
| `id` | `bigint unsigned` | `NO` | NULL | PRIMARY KEY (auto_increment) | Auto-incrementing surrogate primary key, Unsigned numeric |
| `user_id` | `bigint unsigned` | `YES` | NULL | INDEX / FK | Relational foreign key reference, Unsigned numeric |
| `email` | `varchar(255)` | `NO` | NULL | None | Mandatory field |
| `type` | `varchar(255)` | `NO` | NULL | None | Mandatory field |
| `token` | `varchar(100)` | `YES` | NULL | None | Standard column attribute |
| `code` | `varchar(20)` | `YES` | NULL | None | Standard column attribute |
| `attempts` | `int unsigned` | `NO` | `0` | None | Unsigned numeric |
| `last_sent_at` | `timestamp` | `YES` | NULL | None | Standard column attribute |
| `expires_at` | `timestamp` | `YES` | NULL | None | Standard column attribute |
| `verified_at` | `timestamp` | `YES` | NULL | None | Standard column attribute |
| `used_at` | `timestamp` | `YES` | NULL | None | Standard column attribute |
| `ip_address` | `varchar(45)` | `YES` | NULL | None | Standard column attribute |
| `created_at` | `timestamp` | `YES` | NULL | None | Laravel timestamp audit |
| `updated_at` | `timestamp` | `YES` | NULL | None | Laravel timestamp audit |

**Indexes:**
- **`PRIMARY`**: Type `PRIMARY`, Columns: (`id`)
- **`auth_verifications_user_id_foreign`**: Type `INDEX`, Columns: (`user_id`)

**Foreign Keys:**
- **`user_id`** → `users.id` (ON DELETE: `CASCADE`, ON UPDATE: `RESTRICT`)

**Relationships (Eloquent):**
- Model: [`App\Models\AuthVerification`](file:///C:\Users\W\Desktop\wollo-lost-found\server/app/Models/AuthVerification.php)
  - `$this->user()`: `belongsTo` (`User::class`) <!-- source: app/Models/AuthVerification.php:61 -->

---

#### `cache`
<!-- source: database/migrations/2026_08_30_104938_create_cache_table.php -->
**Purpose:** Stores relational records for `cache` in the Wollo University Lost & Found schema.
**Migration:** `2026_08_30_104938_create_cache_table.php`

| Column | Type | Nullable | Default | Constraint | Notes |
|--------|------|----------|---------|------------|-------|
| `key` | `varchar(255)` | `NO` | NULL | PRIMARY KEY | Standard column attribute |
| `value` | `mediumtext` | `NO` | NULL | None | Mandatory field |
| `expiration` | `int` | `NO` | NULL | None | Mandatory field |

**Indexes:**
- **`PRIMARY`**: Type `PRIMARY`, Columns: (`key`)

**Foreign Keys:**
- None

**Relationships (Eloquent):**
- No direct dedicated Eloquent Model class (system/pivot/operational table).

---

#### `cache_locks`
<!-- source: database/migrations/2026_08_30_104939_create_cache_locks_table.php -->
**Purpose:** Stores relational records for `cache_locks` in the Wollo University Lost & Found schema.
**Migration:** `2026_08_30_104939_create_cache_locks_table.php`

| Column | Type | Nullable | Default | Constraint | Notes |
|--------|------|----------|---------|------------|-------|
| `key` | `varchar(255)` | `NO` | NULL | PRIMARY KEY | Standard column attribute |
| `owner` | `varchar(255)` | `NO` | NULL | None | Mandatory field |
| `expiration` | `int` | `NO` | NULL | None | Mandatory field |

**Indexes:**
- **`PRIMARY`**: Type `PRIMARY`, Columns: (`key`)

**Foreign Keys:**
- None

**Relationships (Eloquent):**
- No direct dedicated Eloquent Model class (system/pivot/operational table).

---

#### `campuses`
<!-- source: database/migrations/2026_08_30_104906_create_campuses_table.php -->
**Purpose:** Stores relational records for `campuses` in the Wollo University Lost & Found schema.
**Migration:** `2026_08_30_104906_create_campuses_table.php`

| Column | Type | Nullable | Default | Constraint | Notes |
|--------|------|----------|---------|------------|-------|
| `id` | `bigint unsigned` | `NO` | NULL | PRIMARY KEY (auto_increment) | Auto-incrementing surrogate primary key, Unsigned numeric |
| `name` | `varchar(255)` | `NO` | NULL | UNIQUE | Mandatory field |
| `short_code` | `varchar(50)` | `NO` | NULL | UNIQUE | Mandatory field |
| `city` | `varchar(255)` | `YES` | NULL | None | Standard column attribute |
| `region` | `varchar(255)` | `YES` | NULL | None | Standard column attribute |
| `address` | `text` | `YES` | NULL | None | Standard column attribute |
| `phone` | `varchar(255)` | `YES` | NULL | None | Standard column attribute |
| `email` | `varchar(255)` | `YES` | NULL | None | Standard column attribute |
| `is_active` | `tinyint(1)` | `NO` | `1` | None | Standard column attribute |
| `created_at` | `timestamp` | `YES` | NULL | None | Laravel timestamp audit |
| `updated_at` | `timestamp` | `YES` | NULL | None | Laravel timestamp audit |

**Indexes:**
- **`PRIMARY`**: Type `PRIMARY`, Columns: (`id`)
- **`campuses_name_unique`**: Type `UNIQUE`, Columns: (`name`)
- **`campuses_short_code_unique`**: Type `UNIQUE`, Columns: (`short_code`)

**Foreign Keys:**
- None

**Relationships (Eloquent):**
- Model: [`App\Models\Campus`](file:///C:\Users\W\Desktop\wollo-lost-found\server/app/Models/Campus.php)
  - `$this->organizationalUnits()`: `hasMany` (`OrganizationalUnit::class`) <!-- source: app/Models/Campus.php:50 -->
  - `$this->locations()`: `hasMany` (`Location::class`) <!-- source: app/Models/Campus.php:55 -->
  - `$this->storageLocations()`: `hasMany` (`StorageLocation::class`) <!-- source: app/Models/Campus.php:60 -->
  - `$this->items()`: `hasMany` (`Item::class`) <!-- source: app/Models/Campus.php:65 -->
  - `$this->searchLogs()`: `hasMany` (`SearchLog::class`) <!-- source: app/Models/Campus.php:70 -->

---

#### `categories`
<!-- source: database/migrations/2026_08_30_104912_create_categories_table.php -->
**Purpose:** Stores relational records for `categories` in the Wollo University Lost & Found schema.
**Migration:** `2026_08_30_104912_create_categories_table.php`

| Column | Type | Nullable | Default | Constraint | Notes |
|--------|------|----------|---------|------------|-------|
| `id` | `bigint unsigned` | `NO` | NULL | PRIMARY KEY (auto_increment) | Auto-incrementing surrogate primary key, Unsigned numeric |
| `name` | `varchar(255)` | `NO` | NULL | UNIQUE | Mandatory field |
| `name_am` | `varchar(255)` | `YES` | NULL | None | Standard column attribute |
| `icon_slug` | `varchar(255)` | `YES` | NULL | None | Standard column attribute |
| `sort_order` | `int` | `NO` | `0` | None | Standard column attribute |
| `is_active` | `tinyint(1)` | `NO` | `1` | None | Standard column attribute |
| `created_at` | `timestamp` | `YES` | NULL | None | Laravel timestamp audit |
| `updated_at` | `timestamp` | `YES` | NULL | None | Laravel timestamp audit |

**Indexes:**
- **`PRIMARY`**: Type `PRIMARY`, Columns: (`id`)
- **`categories_name_unique`**: Type `UNIQUE`, Columns: (`name`)

**Foreign Keys:**
- None

**Relationships (Eloquent):**
- Model: [`App\Models\Category`](file:///C:\Users\W\Desktop\wollo-lost-found\server/app/Models/Category.php)
  - `$this->items()`: `hasMany` (`Item::class`) <!-- source: app/Models/Category.php:45 -->
  - `$this->searchLogs()`: `hasMany` (`SearchLog::class`) <!-- source: app/Models/Category.php:50 -->

---

#### `claim_evidence`
<!-- source: database/migrations/2026_08_30_104923_create_claim_evidence_table.php -->
**Purpose:** Stores relational records for `claim_evidence` in the Wollo University Lost & Found schema.
**Migration:** `2026_08_30_104923_create_claim_evidence_table.php`

| Column | Type | Nullable | Default | Constraint | Notes |
|--------|------|----------|---------|------------|-------|
| `id` | `bigint unsigned` | `NO` | NULL | PRIMARY KEY (auto_increment) | Auto-incrementing surrogate primary key, Unsigned numeric |
| `claim_id` | `bigint unsigned` | `NO` | NULL | INDEX / FK | Relational foreign key reference, Unsigned numeric, Mandatory field |
| `uploaded_by` | `bigint unsigned` | `NO` | NULL | INDEX / FK | Unsigned numeric, Mandatory field |
| `evidence_type` | `varchar(255)` | `NO` | NULL | None | Mandatory field |
| `path` | `varchar(255)` | `NO` | NULL | None | Mandatory field |
| `original_name` | `varchar(255)` | `YES` | NULL | None | Standard column attribute |
| `mime_type` | `varchar(100)` | `YES` | NULL | None | Standard column attribute |
| `size_bytes` | `bigint unsigned` | `YES` | NULL | None | Unsigned numeric |
| `description` | `text` | `YES` | NULL | None | Standard column attribute |
| `uploaded_at` | `timestamp` | `NO` | `CURRENT_TIMESTAMP` | None (DEFAULT_GENERATED) | Standard column attribute |
| `created_at` | `timestamp` | `YES` | NULL | None | Laravel timestamp audit |
| `updated_at` | `timestamp` | `YES` | NULL | None | Laravel timestamp audit |

**Indexes:**
- **`PRIMARY`**: Type `PRIMARY`, Columns: (`id`)
- **`claim_evidence_claim_id_foreign`**: Type `INDEX`, Columns: (`claim_id`)
- **`claim_evidence_uploaded_by_foreign`**: Type `INDEX`, Columns: (`uploaded_by`)

**Foreign Keys:**
- **`claim_id`** → `claims.id` (ON DELETE: `CASCADE`, ON UPDATE: `RESTRICT`)
- **`uploaded_by`** → `users.id` (ON DELETE: `RESTRICT`, ON UPDATE: `RESTRICT`)

**Relationships (Eloquent):**
- Model: [`App\Models\ClaimEvidence`](file:///C:\Users\W\Desktop\wollo-lost-found\server/app/Models/ClaimEvidence.php)
  - `$this->claim()`: `belongsTo` (`Claim::class`) <!-- source: app/Models/ClaimEvidence.php:66 -->
  - `$this->uploader()`: `belongsTo` (`User::class, 'uploaded_by'`) <!-- source: app/Models/ClaimEvidence.php:71 -->

---

#### `claim_status_histories`
<!-- source: database/migrations/2026_08_30_104924_create_claim_status_histories_table.php -->
**Purpose:** Stores relational records for `claim_status_histories` in the Wollo University Lost & Found schema.
**Migration:** `2026_08_30_104924_create_claim_status_histories_table.php`

| Column | Type | Nullable | Default | Constraint | Notes |
|--------|------|----------|---------|------------|-------|
| `id` | `bigint unsigned` | `NO` | NULL | PRIMARY KEY (auto_increment) | Auto-incrementing surrogate primary key, Unsigned numeric |
| `claim_id` | `bigint unsigned` | `NO` | NULL | INDEX / FK | Relational foreign key reference, Unsigned numeric, Mandatory field |
| `changed_by` | `bigint unsigned` | `YES` | NULL | INDEX / FK | Unsigned numeric |
| `from_status` | `varchar(255)` | `YES` | NULL | None | Standard column attribute |
| `to_status` | `varchar(255)` | `NO` | NULL | None | Mandatory field |
| `changed_by_role` | `varchar(255)` | `YES` | NULL | None | Standard column attribute |
| `was_auto_rejected` | `tinyint(1)` | `NO` | `0` | None | Standard column attribute |
| `note` | `text` | `YES` | NULL | None | Standard column attribute |
| `ip_address` | `varchar(45)` | `YES` | NULL | None | Standard column attribute |
| `created_at` | `timestamp` | `YES` | NULL | None | Laravel timestamp audit |

**Indexes:**
- **`PRIMARY`**: Type `PRIMARY`, Columns: (`id`)
- **`claim_status_histories_claim_id_foreign`**: Type `INDEX`, Columns: (`claim_id`)
- **`claim_status_histories_changed_by_foreign`**: Type `INDEX`, Columns: (`changed_by`)

**Foreign Keys:**
- **`changed_by`** → `users.id` (ON DELETE: `SET NULL`, ON UPDATE: `RESTRICT`)
- **`claim_id`** → `claims.id` (ON DELETE: `CASCADE`, ON UPDATE: `RESTRICT`)

**Relationships (Eloquent):**
- Model: [`App\Models\ClaimStatusHistory`](file:///C:\Users\W\Desktop\wollo-lost-found\server/app/Models/ClaimStatusHistory.php)
  - `$this->claim()`: `belongsTo` (`Claim::class`) <!-- source: app/Models/ClaimStatusHistory.php:53 -->
  - `$this->changedBy()`: `belongsTo` (`User::class, 'changed_by'`) <!-- source: app/Models/ClaimStatusHistory.php:58 -->

---

#### `claims`
<!-- source: database/migrations/2026_08_30_104921_create_claims_table.php -->
**Purpose:** Stores relational records for `claims` in the Wollo University Lost & Found schema.
**Migration:** `2026_08_30_104921_create_claims_table.php`

| Column | Type | Nullable | Default | Constraint | Notes |
|--------|------|----------|---------|------------|-------|
| `id` | `bigint unsigned` | `NO` | NULL | PRIMARY KEY (auto_increment) | Auto-incrementing surrogate primary key, Unsigned numeric |
| `item_id` | `bigint unsigned` | `NO` | NULL | INDEX / FK | Relational foreign key reference, Unsigned numeric, Mandatory field |
| `claimant_id` | `bigint unsigned` | `NO` | NULL | INDEX / FK | Relational foreign key reference, Unsigned numeric, Mandatory field |
| `explanation` | `text` | `YES` | NULL | None | Standard column attribute |
| `status` | `varchar(255)` | `NO` | `pending` | INDEX / FK | Standard column attribute |
| `reviewed_by` | `bigint unsigned` | `YES` | NULL | INDEX / FK | Unsigned numeric |
| `review_note` | `text` | `YES` | NULL | None | Standard column attribute |
| `reviewed_at` | `timestamp` | `YES` | NULL | None | Standard column attribute |
| `auto_rejected` | `tinyint(1)` | `NO` | `0` | None | Standard column attribute |
| `ip_address` | `varchar(45)` | `YES` | NULL | None | Standard column attribute |
| `created_at` | `timestamp` | `YES` | NULL | None | Laravel timestamp audit |
| `updated_at` | `timestamp` | `YES` | NULL | None | Laravel timestamp audit |

**Indexes:**
- **`PRIMARY`**: Type `PRIMARY`, Columns: (`id`)
- **`claims_item_id_claimant_id_unique`**: Type `UNIQUE`, Columns: (`item_id`, `claimant_id`)
- **`claims_reviewed_by_foreign`**: Type `INDEX`, Columns: (`reviewed_by`)
- **`claims_claimant_status_idx`**: Type `INDEX`, Columns: (`claimant_id`, `status`)
- **`claims_status_created_idx`**: Type `INDEX`, Columns: (`status`, `created_at`)

**Foreign Keys:**
- **`claimant_id`** → `users.id` (ON DELETE: `RESTRICT`, ON UPDATE: `RESTRICT`)
- **`item_id`** → `items.id` (ON DELETE: `CASCADE`, ON UPDATE: `RESTRICT`)
- **`reviewed_by`** → `users.id` (ON DELETE: `SET NULL`, ON UPDATE: `RESTRICT`)

**Relationships (Eloquent):**
- Model: [`App\Models\Claim`](file:///C:\Users\W\Desktop\wollo-lost-found\server/app/Models/Claim.php)
  - `$this->item()`: `belongsTo` (`Item::class`) <!-- source: app/Models/Claim.php:58 -->
  - `$this->claimant()`: `belongsTo` (`User::class, 'claimant_id'`) <!-- source: app/Models/Claim.php:63 -->
  - `$this->user()`: `belongsTo` (`User::class, 'claimant_id'`) <!-- source: app/Models/Claim.php:69 -->
  - `$this->reviewer()`: `belongsTo` (`User::class, 'reviewed_by'`) <!-- source: app/Models/Claim.php:74 -->
  - `$this->evidence()`: `hasMany` (`ClaimEvidence::class`) <!-- source: app/Models/Claim.php:79 -->
  - `$this->statusHistories()`: `hasMany` (`ClaimStatusHistory::class`) <!-- source: app/Models/Claim.php:84 -->
  - `$this->returnRecord()`: `hasOne` (`ReturnRecord::class, 'claim_id'`) <!-- source: app/Models/Claim.php:89 -->

---

#### `custody_events`
<!-- source: database/migrations/2026_08_30_104919_create_custody_events_table.php -->
**Purpose:** Stores relational records for `custody_events` in the Wollo University Lost & Found schema.
**Migration:** `2026_08_30_104919_create_custody_events_table.php`

| Column | Type | Nullable | Default | Constraint | Notes |
|--------|------|----------|---------|------------|-------|
| `id` | `bigint unsigned` | `NO` | NULL | PRIMARY KEY (auto_increment) | Auto-incrementing surrogate primary key, Unsigned numeric |
| `item_id` | `bigint unsigned` | `NO` | NULL | INDEX / FK | Relational foreign key reference, Unsigned numeric, Mandatory field |
| `actor_id` | `bigint unsigned` | `NO` | NULL | INDEX / FK | Relational foreign key reference, Unsigned numeric, Mandatory field |
| `storage_location_id` | `bigint unsigned` | `YES` | NULL | INDEX / FK | Relational foreign key reference, Unsigned numeric |
| `event_type` | `varchar(255)` | `NO` | NULL | None | Mandatory field |
| `condition` | `varchar(255)` | `YES` | NULL | None | Standard column attribute |
| `notes` | `text` | `YES` | NULL | None | Standard column attribute |
| `reference_photo` | `varchar(255)` | `YES` | NULL | None | Standard column attribute |
| `created_at` | `timestamp` | `YES` | NULL | None | Laravel timestamp audit |

**Indexes:**
- **`PRIMARY`**: Type `PRIMARY`, Columns: (`id`)
- **`custody_events_item_id_foreign`**: Type `INDEX`, Columns: (`item_id`)
- **`custody_events_actor_id_foreign`**: Type `INDEX`, Columns: (`actor_id`)
- **`custody_events_storage_location_id_foreign`**: Type `INDEX`, Columns: (`storage_location_id`)

**Foreign Keys:**
- **`actor_id`** → `users.id` (ON DELETE: `RESTRICT`, ON UPDATE: `RESTRICT`)
- **`item_id`** → `items.id` (ON DELETE: `CASCADE`, ON UPDATE: `RESTRICT`)
- **`storage_location_id`** → `storage_locations.id` (ON DELETE: `SET NULL`, ON UPDATE: `RESTRICT`)

**Relationships (Eloquent):**
- Model: [`App\Models\CustodyEvent`](file:///C:\Users\W\Desktop\wollo-lost-found\server/app/Models/CustodyEvent.php)
  - `$this->item()`: `belongsTo` (`Item::class`) <!-- source: app/Models/CustodyEvent.php:51 -->
  - `$this->actor()`: `belongsTo` (`User::class, 'actor_id'`) <!-- source: app/Models/CustodyEvent.php:56 -->
  - `$this->storageLocation()`: `belongsTo` (`StorageLocation::class`) <!-- source: app/Models/CustodyEvent.php:61 -->

---

#### `failed_jobs`
<!-- source: database/migrations/2026_08_30_104942_create_failed_jobs_table.php -->
**Purpose:** Stores relational records for `failed_jobs` in the Wollo University Lost & Found schema.
**Migration:** `2026_08_30_104942_create_failed_jobs_table.php`

| Column | Type | Nullable | Default | Constraint | Notes |
|--------|------|----------|---------|------------|-------|
| `id` | `bigint unsigned` | `NO` | NULL | PRIMARY KEY (auto_increment) | Auto-incrementing surrogate primary key, Unsigned numeric |
| `uuid` | `varchar(255)` | `NO` | NULL | UNIQUE | Mandatory field |
| `connection` | `text` | `NO` | NULL | None | Mandatory field |
| `queue` | `text` | `NO` | NULL | None | Mandatory field |
| `payload` | `longtext` | `NO` | NULL | None | Mandatory field |
| `exception` | `longtext` | `NO` | NULL | None | Mandatory field |
| `failed_at` | `timestamp` | `NO` | `CURRENT_TIMESTAMP` | None (DEFAULT_GENERATED) | Standard column attribute |

**Indexes:**
- **`PRIMARY`**: Type `PRIMARY`, Columns: (`id`)
- **`failed_jobs_uuid_unique`**: Type `UNIQUE`, Columns: (`uuid`)

**Foreign Keys:**
- None

**Relationships (Eloquent):**
- No direct dedicated Eloquent Model class (system/pivot/operational table).

---

#### `item_photos`
<!-- source: database/migrations/2026_08_30_104916_create_item_photos_table.php -->
**Purpose:** Stores relational records for `item_photos` in the Wollo University Lost & Found schema.
**Migration:** `2026_08_30_104916_create_item_photos_table.php`

| Column | Type | Nullable | Default | Constraint | Notes |
|--------|------|----------|---------|------------|-------|
| `id` | `bigint unsigned` | `NO` | NULL | PRIMARY KEY (auto_increment) | Auto-incrementing surrogate primary key, Unsigned numeric |
| `item_id` | `bigint unsigned` | `NO` | NULL | INDEX / FK | Relational foreign key reference, Unsigned numeric, Mandatory field |
| `path` | `varchar(255)` | `NO` | NULL | None | Mandatory field |
| `original_name` | `varchar(255)` | `YES` | NULL | None | Standard column attribute |
| `mime_type` | `varchar(100)` | `YES` | NULL | None | Standard column attribute |
| `size_bytes` | `bigint unsigned` | `YES` | NULL | None | Unsigned numeric |
| `width_px` | `int unsigned` | `YES` | NULL | None | Unsigned numeric |
| `height_px` | `int unsigned` | `YES` | NULL | None | Unsigned numeric |
| `is_primary` | `tinyint(1)` | `NO` | `0` | None | Standard column attribute |
| `uploaded_at` | `timestamp` | `NO` | `CURRENT_TIMESTAMP` | None (DEFAULT_GENERATED) | Standard column attribute |
| `created_at` | `timestamp` | `YES` | NULL | None | Laravel timestamp audit |
| `updated_at` | `timestamp` | `YES` | NULL | None | Laravel timestamp audit |

**Indexes:**
- **`PRIMARY`**: Type `PRIMARY`, Columns: (`id`)
- **`item_photos_item_id_foreign`**: Type `INDEX`, Columns: (`item_id`)

**Foreign Keys:**
- **`item_id`** → `items.id` (ON DELETE: `CASCADE`, ON UPDATE: `RESTRICT`)

**Relationships (Eloquent):**
- Model: [`App\Models\ItemPhoto`](file:///C:\Users\W\Desktop\wollo-lost-found\server/app/Models/ItemPhoto.php)
  - `$this->item()`: `belongsTo` (`Item::class`) <!-- source: app/Models/ItemPhoto.php:57 -->

---

#### `item_status_histories`
<!-- source: database/migrations/2026_08_30_104918_create_item_status_histories_table.php -->
**Purpose:** Stores relational records for `item_status_histories` in the Wollo University Lost & Found schema.
**Migration:** `2026_08_30_104918_create_item_status_histories_table.php`

| Column | Type | Nullable | Default | Constraint | Notes |
|--------|------|----------|---------|------------|-------|
| `id` | `bigint unsigned` | `NO` | NULL | PRIMARY KEY (auto_increment) | Auto-incrementing surrogate primary key, Unsigned numeric |
| `item_id` | `bigint unsigned` | `NO` | NULL | INDEX / FK | Relational foreign key reference, Unsigned numeric, Mandatory field |
| `changed_by` | `bigint unsigned` | `YES` | NULL | INDEX / FK | Unsigned numeric |
| `from_status` | `varchar(255)` | `YES` | NULL | None | Standard column attribute |
| `to_status` | `varchar(255)` | `NO` | NULL | None | Mandatory field |
| `changed_by_role` | `varchar(255)` | `YES` | NULL | None | Standard column attribute |
| `note` | `text` | `YES` | NULL | None | Standard column attribute |
| `ip_address` | `varchar(45)` | `YES` | NULL | None | Standard column attribute |
| `created_at` | `timestamp` | `YES` | NULL | None | Laravel timestamp audit |

**Indexes:**
- **`PRIMARY`**: Type `PRIMARY`, Columns: (`id`)
- **`item_status_histories_item_id_foreign`**: Type `INDEX`, Columns: (`item_id`)
- **`item_status_histories_changed_by_foreign`**: Type `INDEX`, Columns: (`changed_by`)

**Foreign Keys:**
- **`changed_by`** → `users.id` (ON DELETE: `SET NULL`, ON UPDATE: `RESTRICT`)
- **`item_id`** → `items.id` (ON DELETE: `CASCADE`, ON UPDATE: `RESTRICT`)

**Relationships (Eloquent):**
- Model: [`App\Models\ItemStatusHistory`](file:///C:\Users\W\Desktop\wollo-lost-found\server/app/Models/ItemStatusHistory.php)
  - `$this->item()`: `belongsTo` (`Item::class`) <!-- source: app/Models/ItemStatusHistory.php:50 -->
  - `$this->changedBy()`: `belongsTo` (`User::class, 'changed_by'`) <!-- source: app/Models/ItemStatusHistory.php:55 -->

---

#### `item_tags`
<!-- source: database/migrations/2026_08_30_104917_create_item_tags_table.php -->
**Purpose:** Stores relational records for `item_tags` in the Wollo University Lost & Found schema.
**Migration:** `2026_08_30_104917_create_item_tags_table.php`

| Column | Type | Nullable | Default | Constraint | Notes |
|--------|------|----------|---------|------------|-------|
| `id` | `bigint unsigned` | `NO` | NULL | PRIMARY KEY (auto_increment) | Auto-incrementing surrogate primary key, Unsigned numeric |
| `item_id` | `bigint unsigned` | `NO` | NULL | INDEX / FK | Relational foreign key reference, Unsigned numeric, Mandatory field |
| `tag` | `varchar(255)` | `NO` | NULL | None | Mandatory field |
| `created_at` | `timestamp` | `YES` | NULL | None | Laravel timestamp audit |

**Indexes:**
- **`PRIMARY`**: Type `PRIMARY`, Columns: (`id`)
- **`item_tags_item_id_tag_unique`**: Type `UNIQUE`, Columns: (`item_id`, `tag`)

**Foreign Keys:**
- **`item_id`** → `items.id` (ON DELETE: `CASCADE`, ON UPDATE: `RESTRICT`)

**Relationships (Eloquent):**
- Model: [`App\Models\ItemTag`](file:///C:\Users\W\Desktop\wollo-lost-found\server/app/Models/ItemTag.php)
  - `$this->item()`: `belongsTo` (`Item::class`) <!-- source: app/Models/ItemTag.php:39 -->

---

#### `item_views`
<!-- source: database/migrations/2026_08_30_104930_create_item_views_table.php -->
**Purpose:** Stores relational records for `item_views` in the Wollo University Lost & Found schema.
**Migration:** `2026_08_30_104930_create_item_views_table.php`

| Column | Type | Nullable | Default | Constraint | Notes |
|--------|------|----------|---------|------------|-------|
| `id` | `bigint unsigned` | `NO` | NULL | PRIMARY KEY (auto_increment) | Auto-incrementing surrogate primary key, Unsigned numeric |
| `item_id` | `bigint unsigned` | `NO` | NULL | INDEX / FK | Relational foreign key reference, Unsigned numeric, Mandatory field |
| `user_id` | `bigint unsigned` | `YES` | NULL | INDEX / FK | Relational foreign key reference, Unsigned numeric |
| `ip_address` | `varchar(45)` | `YES` | NULL | None | Standard column attribute |
| `user_agent` | `text` | `YES` | NULL | None | Standard column attribute |
| `viewed_at` | `timestamp` | `NO` | `CURRENT_TIMESTAMP` | None (DEFAULT_GENERATED) | Standard column attribute |
| `created_at` | `timestamp` | `YES` | NULL | None | Laravel timestamp audit |
| `updated_at` | `timestamp` | `YES` | NULL | None | Laravel timestamp audit |

**Indexes:**
- **`PRIMARY`**: Type `PRIMARY`, Columns: (`id`)
- **`item_views_item_id_foreign`**: Type `INDEX`, Columns: (`item_id`)
- **`item_views_user_id_foreign`**: Type `INDEX`, Columns: (`user_id`)

**Foreign Keys:**
- **`item_id`** → `items.id` (ON DELETE: `CASCADE`, ON UPDATE: `RESTRICT`)
- **`user_id`** → `users.id` (ON DELETE: `SET NULL`, ON UPDATE: `RESTRICT`)

**Relationships (Eloquent):**
- Model: [`App\Models\ItemView`](file:///C:\Users\W\Desktop\wollo-lost-found\server/app/Models/ItemView.php)
  - `$this->item()`: `belongsTo` (`Item::class`) <!-- source: app/Models/ItemView.php:46 -->
  - `$this->user()`: `belongsTo` (`User::class`) <!-- source: app/Models/ItemView.php:51 -->

---

#### `items`
<!-- source: database/migrations/2026_08_30_104915_create_items_table.php -->
**Purpose:** Stores relational records for `items` in the Wollo University Lost & Found schema.
**Migration:** `2026_08_30_104915_create_items_table.php`

| Column | Type | Nullable | Default | Constraint | Notes |
|--------|------|----------|---------|------------|-------|
| `id` | `bigint unsigned` | `NO` | NULL | PRIMARY KEY (auto_increment) | Auto-incrementing surrogate primary key, Unsigned numeric |
| `reference_code` | `varchar(255)` | `NO` | NULL | UNIQUE | Mandatory field |
| `reporter_id` | `bigint unsigned` | `NO` | NULL | INDEX / FK | Relational foreign key reference, Unsigned numeric, Mandatory field |
| `campus_id` | `bigint unsigned` | `NO` | NULL | INDEX / FK | Relational foreign key reference, Unsigned numeric, Mandatory field |
| `type` | `varchar(255)` | `NO` | NULL | INDEX / FK | Mandatory field |
| `title` | `varchar(255)` | `NO` | NULL | None | Mandatory field |
| `description` | `text` | `YES` | NULL | None | Standard column attribute |
| `category_id` | `bigint unsigned` | `NO` | NULL | INDEX / FK | Relational foreign key reference, Unsigned numeric, Mandatory field |
| `location_id` | `bigint unsigned` | `YES` | NULL | INDEX / FK | Relational foreign key reference, Unsigned numeric |
| `location_detail` | `text` | `YES` | NULL | None | Standard column attribute |
| `brand` | `varchar(255)` | `YES` | NULL | None | Standard column attribute |
| `color` | `varchar(255)` | `YES` | NULL | None | Standard column attribute |
| `serial_number` | `varchar(255)` | `YES` | NULL | None | Standard column attribute |
| `incident_date` | `date` | `YES` | NULL | None | Standard column attribute |
| `incident_time` | `time` | `YES` | NULL | None | Standard column attribute |
| `status` | `varchar(255)` | `NO` | `reported` | INDEX / FK | Standard column attribute |
| `held_at` | `varchar(255)` | `YES` | NULL | None | Standard column attribute |
| `estimated_value` | `decimal(10,2)` | `YES` | NULL | None | Standard column attribute |
| `is_high_value` | `tinyint(1)` | `NO` | `0` | INDEX / FK | Standard column attribute |
| `is_deleted` | `tinyint(1)` | `NO` | `0` | None | Standard column attribute |
| `deleted_by` | `bigint unsigned` | `YES` | NULL | INDEX / FK | Unsigned numeric |
| `deleted_at` | `timestamp` | `YES` | NULL | None | Eloquent soft-delete timestamp |
| `last_activity_at` | `timestamp` | `YES` | NULL | INDEX / FK | Standard column attribute |
| `expires_at` | `timestamp` | `YES` | NULL | INDEX / FK | Standard column attribute |
| `created_at` | `timestamp` | `YES` | NULL | None | Laravel timestamp audit |
| `updated_at` | `timestamp` | `YES` | NULL | None | Laravel timestamp audit |

**Indexes:**
- **`PRIMARY`**: Type `PRIMARY`, Columns: (`id`)
- **`items_reference_code_unique`**: Type `UNIQUE`, Columns: (`reference_code`)
- **`items_deleted_by_foreign`**: Type `INDEX`, Columns: (`deleted_by`)
- **`items_status_is_deleted_index`**: Type `INDEX`, Columns: (`status`, `is_deleted`)
- **`items_reporter_id_index`**: Type `INDEX`, Columns: (`reporter_id`)
- **`items_category_id_index`**: Type `INDEX`, Columns: (`category_id`)
- **`items_location_id_index`**: Type `INDEX`, Columns: (`location_id`)
- **`items_campus_id_index`**: Type `INDEX`, Columns: (`campus_id`)
- **`items_last_activity_at_index`**: Type `INDEX`, Columns: (`last_activity_at`)
- **`items_expires_at_index`**: Type `INDEX`, Columns: (`expires_at`)
- **`items_is_high_value_index`**: Type `INDEX`, Columns: (`is_high_value`)
- **`items_campus_id_status_is_deleted_index`**: Type `INDEX`, Columns: (`campus_id`, `status`, `is_deleted`)
- **`items_browse_idx`**: Type `INDEX`, Columns: (`type`, `status`, `is_deleted`, `created_at`)

**Foreign Keys:**
- **`campus_id`** → `campuses.id` (ON DELETE: `RESTRICT`, ON UPDATE: `RESTRICT`)
- **`category_id`** → `categories.id` (ON DELETE: `RESTRICT`, ON UPDATE: `RESTRICT`)
- **`deleted_by`** → `users.id` (ON DELETE: `SET NULL`, ON UPDATE: `RESTRICT`)
- **`location_id`** → `locations.id` (ON DELETE: `SET NULL`, ON UPDATE: `RESTRICT`)
- **`reporter_id`** → `users.id` (ON DELETE: `RESTRICT`, ON UPDATE: `RESTRICT`)

**Relationships (Eloquent):**
- Model: [`App\Models\Item`](file:///C:\Users\W\Desktop\wollo-lost-found\server/app/Models/Item.php)
  - `$this->reporter()`: `belongsTo` (`User::class, 'reporter_id'`) <!-- source: app/Models/Item.php:105 -->
  - `$this->user()`: `belongsTo` (`User::class, 'reporter_id'`) <!-- source: app/Models/Item.php:111 -->
  - `$this->campus()`: `belongsTo` (`Campus::class`) <!-- source: app/Models/Item.php:116 -->
  - `$this->category()`: `belongsTo` (`Category::class`) <!-- source: app/Models/Item.php:121 -->
  - `$this->location()`: `belongsTo` (`Location::class`) <!-- source: app/Models/Item.php:126 -->
  - `$this->deletedByUser()`: `belongsTo` (`User::class, 'deleted_by'`) <!-- source: app/Models/Item.php:131 -->
  - `$this->photos()`: `hasMany` (`ItemPhoto::class`) <!-- source: app/Models/Item.php:136 -->
  - `$this->tags()`: `hasMany` (`ItemTag::class`) <!-- source: app/Models/Item.php:141 -->
  - `$this->statusHistories()`: `hasMany` (`ItemStatusHistory::class`) <!-- source: app/Models/Item.php:146 -->
  - `$this->custodyEvents()`: `hasMany` (`CustodyEvent::class`) <!-- source: app/Models/Item.php:151 -->
  - `$this->claims()`: `hasMany` (`Claim::class`) <!-- source: app/Models/Item.php:156 -->
  - `$this->views()`: `hasMany` (`ItemView::class`) <!-- source: app/Models/Item.php:161 -->
  - `$this->matchSuggestionsAsFound()`: `hasMany` (`MatchSuggestion::class, 'found_item_id'`) <!-- source: app/Models/Item.php:166 -->
  - `$this->matchSuggestionsAsLost()`: `hasMany` (`MatchSuggestion::class, 'lost_item_id'`) <!-- source: app/Models/Item.php:171 -->

---

#### `job_batches`
<!-- source: database/migrations/2026_08_30_104941_create_job_batches_table.php -->
**Purpose:** Stores relational records for `job_batches` in the Wollo University Lost & Found schema.
**Migration:** `2026_08_30_104941_create_job_batches_table.php`

| Column | Type | Nullable | Default | Constraint | Notes |
|--------|------|----------|---------|------------|-------|
| `id` | `varchar(255)` | `NO` | NULL | PRIMARY KEY | Auto-incrementing surrogate primary key |
| `name` | `varchar(255)` | `NO` | NULL | None | Mandatory field |
| `total_jobs` | `int` | `NO` | NULL | None | Mandatory field |
| `pending_jobs` | `int` | `NO` | NULL | None | Mandatory field |
| `failed_jobs` | `int` | `NO` | NULL | None | Mandatory field |
| `failed_job_ids` | `longtext` | `NO` | NULL | None | Mandatory field |
| `options` | `mediumtext` | `YES` | NULL | None | Standard column attribute |
| `cancelled_at` | `int` | `YES` | NULL | None | Standard column attribute |
| `created_at` | `int` | `NO` | NULL | None | Mandatory field, Laravel timestamp audit |
| `finished_at` | `int` | `YES` | NULL | None | Standard column attribute |

**Indexes:**
- **`PRIMARY`**: Type `PRIMARY`, Columns: (`id`)

**Foreign Keys:**
- None

**Relationships (Eloquent):**
- No direct dedicated Eloquent Model class (system/pivot/operational table).

---

#### `jobs`
<!-- source: database/migrations/2026_08_30_104940_create_jobs_table.php -->
**Purpose:** Stores relational records for `jobs` in the Wollo University Lost & Found schema.
**Migration:** `2026_08_30_104940_create_jobs_table.php`

| Column | Type | Nullable | Default | Constraint | Notes |
|--------|------|----------|---------|------------|-------|
| `id` | `bigint unsigned` | `NO` | NULL | PRIMARY KEY (auto_increment) | Auto-incrementing surrogate primary key, Unsigned numeric |
| `queue` | `varchar(255)` | `NO` | NULL | INDEX / FK | Mandatory field |
| `payload` | `longtext` | `NO` | NULL | None | Mandatory field |
| `attempts` | `tinyint unsigned` | `NO` | NULL | None | Unsigned numeric, Mandatory field |
| `reserved_at` | `int unsigned` | `YES` | NULL | None | Unsigned numeric |
| `available_at` | `int unsigned` | `NO` | NULL | None | Unsigned numeric, Mandatory field |
| `created_at` | `int unsigned` | `NO` | NULL | None | Unsigned numeric, Mandatory field, Laravel timestamp audit |

**Indexes:**
- **`PRIMARY`**: Type `PRIMARY`, Columns: (`id`)
- **`jobs_queue_index`**: Type `INDEX`, Columns: (`queue`)

**Foreign Keys:**
- None

**Relationships (Eloquent):**
- No direct dedicated Eloquent Model class (system/pivot/operational table).

---

#### `locations`
<!-- source: database/migrations/2026_08_30_104913_create_locations_table.php -->
**Purpose:** Stores relational records for `locations` in the Wollo University Lost & Found schema.
**Migration:** `2026_08_30_104913_create_locations_table.php`

| Column | Type | Nullable | Default | Constraint | Notes |
|--------|------|----------|---------|------------|-------|
| `id` | `bigint unsigned` | `NO` | NULL | PRIMARY KEY (auto_increment) | Auto-incrementing surrogate primary key, Unsigned numeric |
| `campus_id` | `bigint unsigned` | `NO` | NULL | INDEX / FK | Relational foreign key reference, Unsigned numeric, Mandatory field |
| `name` | `varchar(255)` | `NO` | NULL | None | Mandatory field |
| `name_am` | `varchar(255)` | `YES` | NULL | None | Standard column attribute |
| `code` | `varchar(50)` | `NO` | NULL | UNIQUE | Mandatory field |
| `building` | `varchar(255)` | `YES` | NULL | None | Standard column attribute |
| `zone` | `varchar(255)` | `YES` | NULL | None | Standard column attribute |
| `is_active` | `tinyint(1)` | `NO` | `1` | None | Standard column attribute |
| `sort_order` | `int` | `NO` | `0` | None | Standard column attribute |
| `created_at` | `timestamp` | `YES` | NULL | None | Laravel timestamp audit |
| `updated_at` | `timestamp` | `YES` | NULL | None | Laravel timestamp audit |

**Indexes:**
- **`PRIMARY`**: Type `PRIMARY`, Columns: (`id`)
- **`locations_campus_id_name_unique`**: Type `UNIQUE`, Columns: (`campus_id`, `name`)
- **`locations_code_unique`**: Type `UNIQUE`, Columns: (`code`)

**Foreign Keys:**
- **`campus_id`** → `campuses.id` (ON DELETE: `RESTRICT`, ON UPDATE: `RESTRICT`)

**Relationships (Eloquent):**
- Model: [`App\Models\Location`](file:///C:\Users\W\Desktop\wollo-lost-found\server/app/Models/Location.php)
  - `$this->campus()`: `belongsTo` (`Campus::class`) <!-- source: app/Models/Location.php:53 -->
  - `$this->items()`: `hasMany` (`Item::class`) <!-- source: app/Models/Location.php:58 -->

---

#### `match_suggestions`
<!-- source: database/migrations/2026_08_30_104920_create_match_suggestions_table.php -->
**Purpose:** Stores relational records for `match_suggestions` in the Wollo University Lost & Found schema.
**Migration:** `2026_08_30_104920_create_match_suggestions_table.php`

| Column | Type | Nullable | Default | Constraint | Notes |
|--------|------|----------|---------|------------|-------|
| `id` | `bigint unsigned` | `NO` | NULL | PRIMARY KEY (auto_increment) | Auto-incrementing surrogate primary key, Unsigned numeric |
| `found_item_id` | `bigint unsigned` | `NO` | NULL | INDEX / FK | Relational foreign key reference, Unsigned numeric, Mandatory field |
| `lost_item_id` | `bigint unsigned` | `NO` | NULL | INDEX / FK | Relational foreign key reference, Unsigned numeric, Mandatory field |
| `score` | `decimal(5,2)` | `NO` | NULL | None | Mandatory field |
| `category_score` | `decimal(5,2)` | `YES` | NULL | None | Standard column attribute |
| `text_score` | `decimal(5,2)` | `YES` | NULL | None | Standard column attribute |
| `location_score` | `decimal(5,2)` | `YES` | NULL | None | Standard column attribute |
| `algorithm_version` | `varchar(50)` | `YES` | NULL | None | Standard column attribute |
| `status` | `varchar(255)` | `NO` | `pending` | None | Standard column attribute |
| `notified_at` | `timestamp` | `YES` | NULL | None | Standard column attribute |
| `reviewed_at` | `timestamp` | `YES` | NULL | None | Standard column attribute |
| `reviewed_by` | `bigint unsigned` | `YES` | NULL | INDEX / FK | Unsigned numeric |
| `created_at` | `timestamp` | `YES` | NULL | None | Laravel timestamp audit |

**Indexes:**
- **`PRIMARY`**: Type `PRIMARY`, Columns: (`id`)
- **`match_suggestions_found_item_id_lost_item_id_unique`**: Type `UNIQUE`, Columns: (`found_item_id`, `lost_item_id`)
- **`match_suggestions_lost_item_id_foreign`**: Type `INDEX`, Columns: (`lost_item_id`)
- **`match_suggestions_reviewed_by_foreign`**: Type `INDEX`, Columns: (`reviewed_by`)

**Foreign Keys:**
- **`found_item_id`** → `items.id` (ON DELETE: `CASCADE`, ON UPDATE: `RESTRICT`)
- **`lost_item_id`** → `items.id` (ON DELETE: `CASCADE`, ON UPDATE: `RESTRICT`)
- **`reviewed_by`** → `users.id` (ON DELETE: `SET NULL`, ON UPDATE: `RESTRICT`)

**Relationships (Eloquent):**
- Model: [`App\Models\MatchSuggestion`](file:///C:\Users\W\Desktop\wollo-lost-found\server/app/Models/MatchSuggestion.php)
  - `$this->foundItem()`: `belongsTo` (`Item::class, 'found_item_id'`) <!-- source: app/Models/MatchSuggestion.php:65 -->
  - `$this->lostItem()`: `belongsTo` (`Item::class, 'lost_item_id'`) <!-- source: app/Models/MatchSuggestion.php:70 -->
  - `$this->reviewer()`: `belongsTo` (`User::class, 'reviewed_by'`) <!-- source: app/Models/MatchSuggestion.php:75 -->

---

#### `migrations`
<!-- source: database/migrations/⚠️ UNVERIFIABLE — migration file not mapped -->
**Purpose:** Stores relational records for `migrations` in the Wollo University Lost & Found schema.
**Migration:** `⚠️ UNVERIFIABLE — migration file not mapped`

| Column | Type | Nullable | Default | Constraint | Notes |
|--------|------|----------|---------|------------|-------|
| `id` | `int unsigned` | `NO` | NULL | PRIMARY KEY (auto_increment) | Auto-incrementing surrogate primary key, Unsigned numeric |
| `migration` | `varchar(255)` | `NO` | NULL | None | Mandatory field |
| `batch` | `int` | `NO` | NULL | None | Mandatory field |

**Indexes:**
- **`PRIMARY`**: Type `PRIMARY`, Columns: (`id`)

**Foreign Keys:**
- None

**Relationships (Eloquent):**
- No direct dedicated Eloquent Model class (system/pivot/operational table).

---

#### `notification_preferences`
<!-- source: database/migrations/2026_08_30_104928_create_notification_preferences_table.php -->
**Purpose:** Stores relational records for `notification_preferences` in the Wollo University Lost & Found schema.
**Migration:** `2026_08_30_104928_create_notification_preferences_table.php`

| Column | Type | Nullable | Default | Constraint | Notes |
|--------|------|----------|---------|------------|-------|
| `id` | `bigint unsigned` | `NO` | NULL | PRIMARY KEY (auto_increment) | Auto-incrementing surrogate primary key, Unsigned numeric |
| `user_id` | `bigint unsigned` | `NO` | NULL | UNIQUE | Relational foreign key reference, Unsigned numeric, Mandatory field |
| `email_on_report_submitted` | `tinyint(1)` | `NO` | `1` | None | Standard column attribute |
| `email_on_match_found` | `tinyint(1)` | `NO` | `1` | None | Standard column attribute |
| `email_on_claim_received` | `tinyint(1)` | `NO` | `1` | None | Standard column attribute |
| `email_on_claim_decided` | `tinyint(1)` | `NO` | `1` | None | Standard column attribute |
| `email_on_item_returned` | `tinyint(1)` | `NO` | `1` | None | Standard column attribute |
| `email_on_expiry_warning` | `tinyint(1)` | `NO` | `1` | None | Standard column attribute |
| `email_on_item_expired` | `tinyint(1)` | `NO` | `1` | None | Standard column attribute |
| `email_on_system_announcements` | `tinyint(1)` | `NO` | `1` | None | Standard column attribute |
| `created_at` | `timestamp` | `YES` | NULL | None | Laravel timestamp audit |
| `updated_at` | `timestamp` | `YES` | NULL | None | Laravel timestamp audit |

**Indexes:**
- **`PRIMARY`**: Type `PRIMARY`, Columns: (`id`)
- **`notification_preferences_user_id_unique`**: Type `UNIQUE`, Columns: (`user_id`)

**Foreign Keys:**
- **`user_id`** → `users.id` (ON DELETE: `CASCADE`, ON UPDATE: `RESTRICT`)

**Relationships (Eloquent):**
- Model: [`App\Models\NotificationPreference`](file:///C:\Users\W\Desktop\wollo-lost-found\server/app/Models/NotificationPreference.php)
  - `$this->user()`: `belongsTo` (`User::class`) <!-- source: app/Models/NotificationPreference.php:39 -->

---

#### `notifications`
<!-- source: database/migrations/2026_08_30_104927_create_notifications_table.php -->
**Purpose:** Stores relational records for `notifications` in the Wollo University Lost & Found schema.
**Migration:** `2026_08_30_104927_create_notifications_table.php`

| Column | Type | Nullable | Default | Constraint | Notes |
|--------|------|----------|---------|------------|-------|
| `id` | `bigint unsigned` | `NO` | NULL | PRIMARY KEY (auto_increment) | Auto-incrementing surrogate primary key, Unsigned numeric |
| `user_id` | `bigint unsigned` | `NO` | NULL | INDEX / FK | Relational foreign key reference, Unsigned numeric, Mandatory field |
| `type` | `varchar(255)` | `NO` | NULL | None | Mandatory field |
| `channel` | `varchar(255)` | `NO` | `in_app` | None | Standard column attribute |
| `data` | `json` | `NO` | NULL | None | Mandatory field |
| `is_read` | `tinyint(1)` | `NO` | `0` | None | Standard column attribute |
| `read_at` | `timestamp` | `YES` | NULL | None | Standard column attribute |
| `created_at` | `timestamp` | `YES` | NULL | None | Laravel timestamp audit |
| `updated_at` | `timestamp` | `YES` | NULL | None | Laravel timestamp audit |

**Indexes:**
- **`PRIMARY`**: Type `PRIMARY`, Columns: (`id`)
- **`notifications_user_id_is_read_index`**: Type `INDEX`, Columns: (`user_id`, `is_read`)
- **`notifications_user_id_created_at_index`**: Type `INDEX`, Columns: (`user_id`, `created_at`)
- **`notif_user_id_idx`**: Type `INDEX`, Columns: (`user_id`, `id`)

**Foreign Keys:**
- **`user_id`** → `users.id` (ON DELETE: `CASCADE`, ON UPDATE: `RESTRICT`)

**Relationships (Eloquent):**
- Model: [`App\Models\Notification`](file:///C:\Users\W\Desktop\wollo-lost-found\server/app/Models/Notification.php)
  - `$this->user()`: `belongsTo` (`User::class`) <!-- source: app/Models/Notification.php:49 -->

---

#### `organizational_unit_type_relations`
<!-- source: database/migrations/2026_08_30_104904_create_organizational_unit_type_relations_table.php -->
**Purpose:** Stores relational records for `organizational_unit_type_relations` in the Wollo University Lost & Found schema.
**Migration:** `2026_08_30_104904_create_organizational_unit_type_relations_table.php`

| Column | Type | Nullable | Default | Constraint | Notes |
|--------|------|----------|---------|------------|-------|
| `id` | `bigint unsigned` | `NO` | NULL | PRIMARY KEY (auto_increment) | Auto-incrementing surrogate primary key, Unsigned numeric |
| `child_type_id` | `bigint unsigned` | `NO` | NULL | INDEX / FK | Relational foreign key reference, Unsigned numeric, Mandatory field |
| `parent_type_id` | `bigint unsigned` | `NO` | NULL | INDEX / FK | Relational foreign key reference, Unsigned numeric, Mandatory field |
| `is_active` | `tinyint(1)` | `NO` | `1` | None | Standard column attribute |
| `created_at` | `timestamp` | `YES` | NULL | None | Laravel timestamp audit |
| `updated_at` | `timestamp` | `YES` | NULL | None | Laravel timestamp audit |

**Indexes:**
- **`PRIMARY`**: Type `PRIMARY`, Columns: (`id`)
- **`out_rel_child_parent_unique`**: Type `UNIQUE`, Columns: (`child_type_id`, `parent_type_id`)
- **`organizational_unit_type_relations_parent_type_id_foreign`**: Type `INDEX`, Columns: (`parent_type_id`)

**Foreign Keys:**
- **`child_type_id`** → `organizational_unit_types.id` (ON DELETE: `CASCADE`, ON UPDATE: `RESTRICT`)
- **`parent_type_id`** → `organizational_unit_types.id` (ON DELETE: `CASCADE`, ON UPDATE: `RESTRICT`)

**Relationships (Eloquent):**
- Model: [`App\Models\OrganizationalUnitTypeRelation`](file:///C:\Users\W\Desktop\wollo-lost-found\server/app/Models/OrganizationalUnitTypeRelation.php)
  - `$this->childType()`: `belongsTo` (`OrganizationalUnitType::class, 'child_type_id'`) <!-- source: app/Models/OrganizationalUnitTypeRelation.php:46 -->
  - `$this->parentType()`: `belongsTo` (`OrganizationalUnitType::class, 'parent_type_id'`) <!-- source: app/Models/OrganizationalUnitTypeRelation.php:51 -->

---

#### `organizational_unit_types`
<!-- source: database/migrations/2026_08_30_104903_create_organizational_unit_types_table.php -->
**Purpose:** Stores relational records for `organizational_unit_types` in the Wollo University Lost & Found schema.
**Migration:** `2026_08_30_104903_create_organizational_unit_types_table.php`

| Column | Type | Nullable | Default | Constraint | Notes |
|--------|------|----------|---------|------------|-------|
| `id` | `bigint unsigned` | `NO` | NULL | PRIMARY KEY (auto_increment) | Auto-incrementing surrogate primary key, Unsigned numeric |
| `code` | `varchar(255)` | `NO` | NULL | UNIQUE | Mandatory field |
| `name` | `varchar(255)` | `NO` | NULL | UNIQUE | Mandatory field |
| `name_am` | `varchar(255)` | `YES` | NULL | None | Standard column attribute |
| `description` | `text` | `YES` | NULL | None | Standard column attribute |
| `is_root` | `tinyint(1)` | `NO` | `0` | None | Standard column attribute |
| `is_active` | `tinyint(1)` | `NO` | `1` | None | Standard column attribute |
| `created_at` | `timestamp` | `YES` | NULL | None | Laravel timestamp audit |
| `updated_at` | `timestamp` | `YES` | NULL | None | Laravel timestamp audit |

**Indexes:**
- **`PRIMARY`**: Type `PRIMARY`, Columns: (`id`)
- **`organizational_unit_types_code_unique`**: Type `UNIQUE`, Columns: (`code`)
- **`organizational_unit_types_name_unique`**: Type `UNIQUE`, Columns: (`name`)

**Foreign Keys:**
- None

**Relationships (Eloquent):**
- Model: [`App\Models\OrganizationalUnitType`](file:///C:\Users\W\Desktop\wollo-lost-found\server/app/Models/OrganizationalUnitType.php)
  - `$this->childTypeRelations()`: `hasMany` (`OrganizationalUnitTypeRelation::class, 'parent_type_id'`) <!-- source: app/Models/OrganizationalUnitType.php:54 -->
  - `$this->parentTypeRelations()`: `hasMany` (`OrganizationalUnitTypeRelation::class, 'child_type_id'`) <!-- source: app/Models/OrganizationalUnitType.php:62 -->
  - `$this->organizationalUnits()`: `hasMany` (`OrganizationalUnit::class, 'type_id'`) <!-- source: app/Models/OrganizationalUnitType.php:70 -->

---

#### `organizational_units`
<!-- source: database/migrations/2026_08_30_104907_create_organizational_units_table.php -->
**Purpose:** Stores relational records for `organizational_units` in the Wollo University Lost & Found schema.
**Migration:** `2026_08_30_104907_create_organizational_units_table.php`

| Column | Type | Nullable | Default | Constraint | Notes |
|--------|------|----------|---------|------------|-------|
| `id` | `bigint unsigned` | `NO` | NULL | PRIMARY KEY (auto_increment) | Auto-incrementing surrogate primary key, Unsigned numeric |
| `campus_id` | `bigint unsigned` | `NO` | NULL | INDEX / FK | Relational foreign key reference, Unsigned numeric, Mandatory field |
| `parent_id` | `bigint unsigned` | `YES` | NULL | INDEX / FK | Relational foreign key reference, Unsigned numeric |
| `type_id` | `bigint unsigned` | `NO` | NULL | INDEX / FK | Relational foreign key reference, Unsigned numeric, Mandatory field |
| `name` | `varchar(255)` | `NO` | NULL | None | Mandatory field |
| `name_am` | `varchar(255)` | `YES` | NULL | None | Standard column attribute |
| `short_code` | `varchar(50)` | `NO` | NULL | None | Mandatory field |
| `description` | `text` | `YES` | NULL | None | Standard column attribute |
| `is_active` | `tinyint(1)` | `NO` | `1` | None | Standard column attribute |
| `created_at` | `timestamp` | `YES` | NULL | None | Laravel timestamp audit |
| `updated_at` | `timestamp` | `YES` | NULL | None | Laravel timestamp audit |

**Indexes:**
- **`PRIMARY`**: Type `PRIMARY`, Columns: (`id`)
- **`organizational_units_campus_id_short_code_unique`**: Type `UNIQUE`, Columns: (`campus_id`, `short_code`)
- **`organizational_units_campus_id_name_unique`**: Type `UNIQUE`, Columns: (`campus_id`, `name`)
- **`organizational_units_type_id_foreign`**: Type `INDEX`, Columns: (`type_id`)
- **`organizational_units_campus_id_type_id_index`**: Type `INDEX`, Columns: (`campus_id`, `type_id`)
- **`organizational_units_parent_id_type_id_index`**: Type `INDEX`, Columns: (`parent_id`, `type_id`)
- **`organizational_units_parent_id_is_active_index`**: Type `INDEX`, Columns: (`parent_id`, `is_active`)
- **`organizational_units_campus_id_is_active_index`**: Type `INDEX`, Columns: (`campus_id`, `is_active`)

**Foreign Keys:**
- **`campus_id`** → `campuses.id` (ON DELETE: `RESTRICT`, ON UPDATE: `RESTRICT`)
- **`parent_id`** → `organizational_units.id` (ON DELETE: `RESTRICT`, ON UPDATE: `RESTRICT`)
- **`type_id`** → `organizational_unit_types.id` (ON DELETE: `RESTRICT`, ON UPDATE: `RESTRICT`)

**Relationships (Eloquent):**
- Model: [`App\Models\OrganizationalUnit`](file:///C:\Users\W\Desktop\wollo-lost-found\server/app/Models/OrganizationalUnit.php)
  - `$this->campus()`: `belongsTo` (`Campus::class`) <!-- source: app/Models/OrganizationalUnit.php:60 -->
  - `$this->parent()`: `belongsTo` (`self::class, 'parent_id'`) <!-- source: app/Models/OrganizationalUnit.php:68 -->
  - `$this->children()`: `hasMany` (`self::class, 'parent_id'`) <!-- source: app/Models/OrganizationalUnit.php:76 -->
  - `$this->type()`: `belongsTo` (`OrganizationalUnitType::class, 'type_id'`) <!-- source: app/Models/OrganizationalUnit.php:84 -->
  - `$this->userAssignments()`: `hasMany` (`UserOrganizationalUnit::class, 'organizational_unit_id'`) <!-- source: app/Models/OrganizationalUnit.php:92 -->
  - `$this->users()`: `belongsToMany` (`User::class, 'user_organizational_units', 'organizational_unit_id', 'user_id')
            ->withPivot(['is_primary', 'enrolled_year'])
            ->withTimestamps(`) <!-- source: app/Models/OrganizationalUnit.php:100 -->

---

#### `password_histories`
<!-- source: database/migrations/2026_08_30_104944_create_password_histories_table.php -->
**Purpose:** Stores relational records for `password_histories` in the Wollo University Lost & Found schema.
**Migration:** `2026_08_30_104944_create_password_histories_table.php`

| Column | Type | Nullable | Default | Constraint | Notes |
|--------|------|----------|---------|------------|-------|
| `id` | `bigint unsigned` | `NO` | NULL | PRIMARY KEY (auto_increment) | Auto-incrementing surrogate primary key, Unsigned numeric |
| `user_id` | `bigint unsigned` | `NO` | NULL | INDEX / FK | Relational foreign key reference, Unsigned numeric, Mandatory field |
| `password_hash` | `varchar(255)` | `NO` | NULL | None | Mandatory field |
| `created_at` | `timestamp` | `YES` | NULL | None | Laravel timestamp audit |
| `updated_at` | `timestamp` | `YES` | NULL | None | Laravel timestamp audit |

**Indexes:**
- **`PRIMARY`**: Type `PRIMARY`, Columns: (`id`)
- **`password_histories_user_id_created_at_index`**: Type `INDEX`, Columns: (`user_id`, `created_at`)

**Foreign Keys:**
- **`user_id`** → `users.id` (ON DELETE: `CASCADE`, ON UPDATE: `RESTRICT`)

**Relationships (Eloquent):**
- Model: [`App\Models\PasswordHistory`](file:///C:\Users\W\Desktop\wollo-lost-found\server/app/Models/PasswordHistory.php)
  - `$this->user()`: `belongsTo` (`User::class`) <!-- source: app/Models/PasswordHistory.php:42 -->

---

#### `permission_groups`
<!-- source: database/migrations/2026_08_30_104859_create_permission_groups_table.php -->
**Purpose:** Stores relational records for `permission_groups` in the Wollo University Lost & Found schema.
**Migration:** `2026_08_30_104859_create_permission_groups_table.php`

| Column | Type | Nullable | Default | Constraint | Notes |
|--------|------|----------|---------|------------|-------|
| `id` | `bigint unsigned` | `NO` | NULL | PRIMARY KEY (auto_increment) | Auto-incrementing surrogate primary key, Unsigned numeric |
| `name` | `varchar(255)` | `NO` | NULL | UNIQUE | Mandatory field |
| `display_name` | `varchar(255)` | `NO` | NULL | None | Mandatory field |
| `display_name_am` | `varchar(255)` | `YES` | NULL | None | Standard column attribute |
| `description` | `text` | `YES` | NULL | None | Standard column attribute |
| `description_am` | `text` | `YES` | NULL | None | Standard column attribute |
| `is_system` | `tinyint(1)` | `NO` | `0` | None | Standard column attribute |
| `is_active` | `tinyint(1)` | `NO` | `1` | INDEX / FK | Standard column attribute |
| `created_at` | `timestamp` | `YES` | NULL | None | Laravel timestamp audit |
| `updated_at` | `timestamp` | `YES` | NULL | None | Laravel timestamp audit |

**Indexes:**
- **`PRIMARY`**: Type `PRIMARY`, Columns: (`id`)
- **`permission_groups_name_unique`**: Type `UNIQUE`, Columns: (`name`)
- **`permission_groups_is_active_index`**: Type `INDEX`, Columns: (`is_active`)

**Foreign Keys:**
- None

**Relationships (Eloquent):**
- Model: [`App\Models\PermissionGroup`](file:///C:\Users\W\Desktop\wollo-lost-found\server/app/Models/PermissionGroup.php)
  - `$this->permissions()`: `hasMany` (`Permission::class, 'permission_group_id'`) <!-- source: app/Models/PermissionGroup.php:33 -->
  - `$this->activePermissions()`: `hasMany` (`Permission::class, 'permission_group_id')->where('is_active', true`) <!-- source: app/Models/PermissionGroup.php:38 -->

---

#### `permission_role`
<!-- source: database/migrations/2026_08_30_104902_create_permission_role_table.php -->
**Purpose:** Stores relational records for `permission_role` in the Wollo University Lost & Found schema.
**Migration:** `2026_08_30_104902_create_permission_role_table.php`

| Column | Type | Nullable | Default | Constraint | Notes |
|--------|------|----------|---------|------------|-------|
| `id` | `bigint unsigned` | `NO` | NULL | PRIMARY KEY (auto_increment) | Auto-incrementing surrogate primary key, Unsigned numeric |
| `role_id` | `bigint unsigned` | `NO` | NULL | INDEX / FK | Relational foreign key reference, Unsigned numeric, Mandatory field |
| `permission_id` | `bigint unsigned` | `NO` | NULL | INDEX / FK | Relational foreign key reference, Unsigned numeric, Mandatory field |
| `created_at` | `timestamp` | `YES` | NULL | None | Laravel timestamp audit |
| `updated_at` | `timestamp` | `YES` | NULL | None | Laravel timestamp audit |

**Indexes:**
- **`PRIMARY`**: Type `PRIMARY`, Columns: (`id`)
- **`permission_role_role_id_permission_id_unique`**: Type `UNIQUE`, Columns: (`role_id`, `permission_id`)
- **`permission_role_permission_id_foreign`**: Type `INDEX`, Columns: (`permission_id`)

**Foreign Keys:**
- **`permission_id`** → `permissions.id` (ON DELETE: `CASCADE`, ON UPDATE: `RESTRICT`)
- **`role_id`** → `roles.id` (ON DELETE: `CASCADE`, ON UPDATE: `RESTRICT`)

**Relationships (Eloquent):**
- No direct dedicated Eloquent Model class (system/pivot/operational table).

---

#### `permission_user`
<!-- source: database/migrations/2026_08_30_104910_create_permission_user_table.php -->
**Purpose:** Stores relational records for `permission_user` in the Wollo University Lost & Found schema.
**Migration:** `2026_08_30_104910_create_permission_user_table.php`

| Column | Type | Nullable | Default | Constraint | Notes |
|--------|------|----------|---------|------------|-------|
| `id` | `bigint unsigned` | `NO` | NULL | PRIMARY KEY (auto_increment) | Auto-incrementing surrogate primary key, Unsigned numeric |
| `permission_id` | `bigint unsigned` | `NO` | NULL | INDEX / FK | Relational foreign key reference, Unsigned numeric, Mandatory field |
| `user_id` | `bigint unsigned` | `NO` | NULL | INDEX / FK | Relational foreign key reference, Unsigned numeric, Mandatory field |
| `created_at` | `timestamp` | `YES` | NULL | None | Laravel timestamp audit |
| `updated_at` | `timestamp` | `YES` | NULL | None | Laravel timestamp audit |

**Indexes:**
- **`PRIMARY`**: Type `PRIMARY`, Columns: (`id`)
- **`permission_user_user_id_permission_id_unique`**: Type `UNIQUE`, Columns: (`user_id`, `permission_id`)
- **`permission_user_permission_id_foreign`**: Type `INDEX`, Columns: (`permission_id`)

**Foreign Keys:**
- **`permission_id`** → `permissions.id` (ON DELETE: `CASCADE`, ON UPDATE: `RESTRICT`)
- **`user_id`** → `users.id` (ON DELETE: `CASCADE`, ON UPDATE: `RESTRICT`)

**Relationships (Eloquent):**
- No direct dedicated Eloquent Model class (system/pivot/operational table).

---

#### `permissions`
<!-- source: database/migrations/2026_08_30_104900_create_permissions_table.php -->
**Purpose:** Stores relational records for `permissions` in the Wollo University Lost & Found schema.
**Migration:** `2026_08_30_104900_create_permissions_table.php`

| Column | Type | Nullable | Default | Constraint | Notes |
|--------|------|----------|---------|------------|-------|
| `id` | `bigint unsigned` | `NO` | NULL | PRIMARY KEY (auto_increment) | Auto-incrementing surrogate primary key, Unsigned numeric |
| `permission_group_id` | `bigint unsigned` | `YES` | NULL | INDEX / FK | Relational foreign key reference, Unsigned numeric |
| `name` | `varchar(255)` | `NO` | NULL | UNIQUE | Mandatory field |
| `display_name` | `varchar(255)` | `NO` | NULL | None | Mandatory field |
| `display_name_am` | `varchar(255)` | `YES` | NULL | None | Standard column attribute |
| `description` | `text` | `YES` | NULL | None | Standard column attribute |
| `description_am` | `text` | `YES` | NULL | None | Standard column attribute |
| `category` | `varchar(255)` | `YES` | NULL | INDEX / FK | Standard column attribute |
| `is_system` | `tinyint(1)` | `NO` | `0` | None | Standard column attribute |
| `is_active` | `tinyint(1)` | `NO` | `1` | None | Standard column attribute |
| `created_at` | `timestamp` | `YES` | NULL | None | Laravel timestamp audit |
| `updated_at` | `timestamp` | `YES` | NULL | None | Laravel timestamp audit |

**Indexes:**
- **`PRIMARY`**: Type `PRIMARY`, Columns: (`id`)
- **`permissions_name_unique`**: Type `UNIQUE`, Columns: (`name`)
- **`permissions_permission_group_id_is_active_index`**: Type `INDEX`, Columns: (`permission_group_id`, `is_active`)
- **`permissions_category_is_active_index`**: Type `INDEX`, Columns: (`category`, `is_active`)

**Foreign Keys:**
- **`permission_group_id`** → `permission_groups.id` (ON DELETE: `SET NULL`, ON UPDATE: `RESTRICT`)

**Relationships (Eloquent):**
- Model: [`App\Models\Permission`](file:///C:\Users\W\Desktop\wollo-lost-found\server/app/Models/Permission.php)
  - `$this->permissionGroup()`: `belongsTo` (`PermissionGroup::class, 'permission_group_id'`) <!-- source: app/Models/Permission.php:37 -->
  - `$this->roles()`: `belongsToMany` (`Role::class, 'permission_role')
            ->withTimestamps(`) <!-- source: app/Models/Permission.php:42 -->
  - `$this->users()`: `belongsToMany` (`User::class, 'permission_user')
            ->withTimestamps(`) <!-- source: app/Models/Permission.php:51 -->

---

#### `reports`
<!-- source: database/migrations/2026_08_30_104935_create_reports_table.php -->
**Purpose:** Stores relational records for `reports` in the Wollo University Lost & Found schema.
**Migration:** `2026_08_30_104935_create_reports_table.php`

| Column | Type | Nullable | Default | Constraint | Notes |
|--------|------|----------|---------|------------|-------|
| `id` | `bigint unsigned` | `NO` | NULL | PRIMARY KEY (auto_increment) | Auto-incrementing surrogate primary key, Unsigned numeric |
| `requested_by` | `bigint unsigned` | `NO` | NULL | INDEX / FK | Unsigned numeric, Mandatory field |
| `report_type` | `varchar(255)` | `NO` | NULL | None | Mandatory field |
| `filters` | `json` | `YES` | NULL | None | Standard column attribute |
| `format` | `varchar(255)` | `NO` | `pdf` | None | Standard column attribute |
| `status` | `varchar(255)` | `NO` | `pending` | None | Standard column attribute |
| `file_path` | `varchar(255)` | `YES` | NULL | None | Standard column attribute |
| `file_size_bytes` | `bigint unsigned` | `YES` | NULL | None | Unsigned numeric |
| `row_count` | `int unsigned` | `NO` | `0` | None | Unsigned numeric |
| `error_message` | `text` | `YES` | NULL | None | Standard column attribute |
| `ready_at` | `timestamp` | `YES` | NULL | None | Standard column attribute |
| `downloaded_at` | `timestamp` | `YES` | NULL | None | Standard column attribute |
| `download_count` | `int unsigned` | `NO` | `0` | None | Unsigned numeric |
| `expires_at` | `timestamp` | `YES` | NULL | None | Standard column attribute |
| `created_at` | `timestamp` | `YES` | NULL | None | Laravel timestamp audit |
| `updated_at` | `timestamp` | `YES` | NULL | None | Laravel timestamp audit |

**Indexes:**
- **`PRIMARY`**: Type `PRIMARY`, Columns: (`id`)
- **`reports_requested_by_foreign`**: Type `INDEX`, Columns: (`requested_by`)

**Foreign Keys:**
- **`requested_by`** → `users.id` (ON DELETE: `RESTRICT`, ON UPDATE: `RESTRICT`)

**Relationships (Eloquent):**
- Model: [`App\Models\Report`](file:///C:\Users\W\Desktop\wollo-lost-found\server/app/Models/Report.php)
  - `$this->requester()`: `belongsTo` (`User::class, 'requested_by'`) <!-- source: app/Models/Report.php:42 -->
  - `$this->requestedBy()`: `belongsTo` (`User::class, 'requested_by'`) <!-- source: app/Models/Report.php:47 -->

---

#### `return_documents`
<!-- source: database/migrations/2026_08_30_104926_create_return_documents_table.php -->
**Purpose:** Stores relational records for `return_documents` in the Wollo University Lost & Found schema.
**Migration:** `2026_08_30_104926_create_return_documents_table.php`

| Column | Type | Nullable | Default | Constraint | Notes |
|--------|------|----------|---------|------------|-------|
| `id` | `bigint unsigned` | `NO` | NULL | PRIMARY KEY (auto_increment) | Auto-incrementing surrogate primary key, Unsigned numeric |
| `return_id` | `bigint unsigned` | `NO` | NULL | INDEX / FK | Relational foreign key reference, Unsigned numeric, Mandatory field |
| `document_type` | `varchar(255)` | `NO` | NULL | None | Mandatory field |
| `path` | `varchar(255)` | `NO` | NULL | None | Mandatory field |
| `original_name` | `varchar(255)` | `YES` | NULL | None | Standard column attribute |
| `mime_type` | `varchar(100)` | `YES` | NULL | None | Standard column attribute |
| `size_bytes` | `bigint unsigned` | `YES` | NULL | None | Unsigned numeric |
| `emailed_to_student` | `tinyint(1)` | `NO` | `0` | None | Standard column attribute |
| `emailed_at` | `timestamp` | `YES` | NULL | None | Standard column attribute |
| `generated_at` | `timestamp` | `NO` | `CURRENT_TIMESTAMP` | None (DEFAULT_GENERATED) | Standard column attribute |
| `created_at` | `timestamp` | `YES` | NULL | None | Laravel timestamp audit |
| `updated_at` | `timestamp` | `YES` | NULL | None | Laravel timestamp audit |

**Indexes:**
- **`PRIMARY`**: Type `PRIMARY`, Columns: (`id`)
- **`return_documents_return_id_foreign`**: Type `INDEX`, Columns: (`return_id`)

**Foreign Keys:**
- **`return_id`** → `returns.id` (ON DELETE: `CASCADE`, ON UPDATE: `RESTRICT`)

**Relationships (Eloquent):**
- Model: [`App\Models\ReturnDocument`](file:///C:\Users\W\Desktop\wollo-lost-found\server/app/Models/ReturnDocument.php)
  - `$this->returnRecord()`: `belongsTo` (`ReturnRecord::class, 'return_id'`) <!-- source: app/Models/ReturnDocument.php:56 -->

---

#### `returns`
<!-- source: database/migrations/2026_08_30_104925_create_returns_table.php -->
**Purpose:** Stores relational records for `returns` in the Wollo University Lost & Found schema.
**Migration:** `2026_08_30_104925_create_returns_table.php`

| Column | Type | Nullable | Default | Constraint | Notes |
|--------|------|----------|---------|------------|-------|
| `id` | `bigint unsigned` | `NO` | NULL | PRIMARY KEY (auto_increment) | Auto-incrementing surrogate primary key, Unsigned numeric |
| `claim_id` | `bigint unsigned` | `NO` | NULL | UNIQUE | Relational foreign key reference, Unsigned numeric, Mandatory field |
| `returned_to` | `bigint unsigned` | `NO` | NULL | INDEX / FK | Unsigned numeric, Mandatory field |
| `handed_over_by` | `bigint unsigned` | `NO` | NULL | INDEX / FK | Unsigned numeric, Mandatory field |
| `storage_location_id` | `bigint unsigned` | `YES` | NULL | INDEX / FK | Relational foreign key reference, Unsigned numeric |
| `return_date` | `date` | `NO` | NULL | INDEX / FK | Mandatory field |
| `return_time` | `time` | `YES` | NULL | None | Standard column attribute |
| `condition_on_return` | `text` | `YES` | NULL | None | Standard column attribute |
| `notes` | `text` | `YES` | NULL | None | Standard column attribute |
| `recipient_confirmed` | `tinyint(1)` | `NO` | `0` | None | Standard column attribute |
| `confirmed_at` | `timestamp` | `YES` | NULL | None | Standard column attribute |
| `confirmation_token` | `varchar(100)` | `YES` | NULL | UNIQUE | Standard column attribute |
| `confirmation_token_expires_at` | `timestamp` | `YES` | NULL | None | Standard column attribute |
| `created_at` | `timestamp` | `YES` | NULL | None | Laravel timestamp audit |
| `updated_at` | `timestamp` | `YES` | NULL | None | Laravel timestamp audit |

**Indexes:**
- **`PRIMARY`**: Type `PRIMARY`, Columns: (`id`)
- **`returns_claim_id_unique`**: Type `UNIQUE`, Columns: (`claim_id`)
- **`returns_confirmation_token_unique`**: Type `UNIQUE`, Columns: (`confirmation_token`)
- **`returns_returned_to_foreign`**: Type `INDEX`, Columns: (`returned_to`)
- **`returns_handed_over_by_foreign`**: Type `INDEX`, Columns: (`handed_over_by`)
- **`returns_storage_location_id_foreign`**: Type `INDEX`, Columns: (`storage_location_id`)
- **`returns_date_confirm_idx`**: Type `INDEX`, Columns: (`return_date`, `recipient_confirmed`)

**Foreign Keys:**
- **`claim_id`** → `claims.id` (ON DELETE: `RESTRICT`, ON UPDATE: `RESTRICT`)
- **`handed_over_by`** → `users.id` (ON DELETE: `RESTRICT`, ON UPDATE: `RESTRICT`)
- **`returned_to`** → `users.id` (ON DELETE: `RESTRICT`, ON UPDATE: `RESTRICT`)
- **`storage_location_id`** → `storage_locations.id` (ON DELETE: `SET NULL`, ON UPDATE: `RESTRICT`)

**Relationships (Eloquent):**
- Model: [`App\Models\ReturnRecord`](file:///C:\Users\W\Desktop\wollo-lost-found\server/app/Models/ReturnRecord.php)
  - `$this->claim()`: `belongsTo` (`Claim::class`) <!-- source: app/Models/ReturnRecord.php:69 -->
  - `$this->item()`: `hasOneThrough` (`Item::class, Claim::class, 'id', 'id', 'claim_id', 'item_id'`) <!-- source: app/Models/ReturnRecord.php:74 -->
  - `$this->recipient()`: `belongsTo` (`User::class, 'returned_to'`) <!-- source: app/Models/ReturnRecord.php:79 -->
  - `$this->staff()`: `belongsTo` (`User::class, 'handed_over_by'`) <!-- source: app/Models/ReturnRecord.php:84 -->
  - `$this->storageLocation()`: `belongsTo` (`StorageLocation::class`) <!-- source: app/Models/ReturnRecord.php:89 -->
  - `$this->documents()`: `hasMany` (`ReturnDocument::class, 'return_id'`) <!-- source: app/Models/ReturnRecord.php:94 -->

---

#### `roles`
<!-- source: database/migrations/2026_08_30_104858_create_roles_table.php -->
**Purpose:** Stores relational records for `roles` in the Wollo University Lost & Found schema.
**Migration:** `2026_08_30_104858_create_roles_table.php`

| Column | Type | Nullable | Default | Constraint | Notes |
|--------|------|----------|---------|------------|-------|
| `id` | `bigint unsigned` | `NO` | NULL | PRIMARY KEY (auto_increment) | Auto-incrementing surrogate primary key, Unsigned numeric |
| `name` | `varchar(255)` | `NO` | NULL | UNIQUE | Mandatory field |
| `display_name` | `varchar(255)` | `NO` | NULL | None | Mandatory field |
| `display_name_am` | `varchar(255)` | `YES` | NULL | None | Standard column attribute |
| `description` | `text` | `YES` | NULL | None | Standard column attribute |
| `description_am` | `text` | `YES` | NULL | None | Standard column attribute |
| `is_system` | `tinyint(1)` | `NO` | `0` | None | Standard column attribute |
| `is_active` | `tinyint(1)` | `NO` | `1` | INDEX / FK | Standard column attribute |
| `created_at` | `timestamp` | `YES` | NULL | None | Laravel timestamp audit |
| `updated_at` | `timestamp` | `YES` | NULL | None | Laravel timestamp audit |

**Indexes:**
- **`PRIMARY`**: Type `PRIMARY`, Columns: (`id`)
- **`roles_name_unique`**: Type `UNIQUE`, Columns: (`name`)
- **`roles_is_active_index`**: Type `INDEX`, Columns: (`is_active`)

**Foreign Keys:**
- None

**Relationships (Eloquent):**
- Model: [`App\Models\Role`](file:///C:\Users\W\Desktop\wollo-lost-found\server/app/Models/Role.php)
  - `$this->permissions()`: `belongsToMany` (`Permission::class, 'permission_role')
            ->withTimestamps(`) <!-- source: app/Models/Role.php:44 -->
  - `$this->users()`: `hasMany` (`User::class, 'role_id'`) <!-- source: app/Models/Role.php:50 -->

---

#### `search_logs`
<!-- source: database/migrations/2026_08_30_104929_create_search_logs_table.php -->
**Purpose:** Stores relational records for `search_logs` in the Wollo University Lost & Found schema.
**Migration:** `2026_08_30_104929_create_search_logs_table.php`

| Column | Type | Nullable | Default | Constraint | Notes |
|--------|------|----------|---------|------------|-------|
| `id` | `bigint unsigned` | `NO` | NULL | PRIMARY KEY (auto_increment) | Auto-incrementing surrogate primary key, Unsigned numeric |
| `user_id` | `bigint unsigned` | `YES` | NULL | INDEX / FK | Relational foreign key reference, Unsigned numeric |
| `query` | `varchar(255)` | `NO` | NULL | INDEX / FK | Mandatory field |
| `category_id` | `bigint unsigned` | `YES` | NULL | INDEX / FK | Relational foreign key reference, Unsigned numeric |
| `campus_id` | `bigint unsigned` | `YES` | NULL | INDEX / FK | Relational foreign key reference, Unsigned numeric |
| `results_count` | `int unsigned` | `NO` | `0` | None | Unsigned numeric |
| `found_match` | `tinyint(1)` | `NO` | `0` | None | Standard column attribute |
| `ip_address` | `varchar(45)` | `YES` | NULL | None | Standard column attribute |
| `created_at` | `timestamp` | `YES` | NULL | INDEX / FK | Laravel timestamp audit |
| `updated_at` | `timestamp` | `YES` | NULL | None | Laravel timestamp audit |

**Indexes:**
- **`PRIMARY`**: Type `PRIMARY`, Columns: (`id`)
- **`search_logs_user_id_foreign`**: Type `INDEX`, Columns: (`user_id`)
- **`search_logs_category_id_foreign`**: Type `INDEX`, Columns: (`category_id`)
- **`search_logs_query_index`**: Type `INDEX`, Columns: (`query`)
- **`search_logs_created_at_index`**: Type `INDEX`, Columns: (`created_at`)
- **`search_logs_campus_id_created_at_index`**: Type `INDEX`, Columns: (`campus_id`, `created_at`)

**Foreign Keys:**
- **`campus_id`** → `campuses.id` (ON DELETE: `SET NULL`, ON UPDATE: `RESTRICT`)
- **`category_id`** → `categories.id` (ON DELETE: `SET NULL`, ON UPDATE: `RESTRICT`)
- **`user_id`** → `users.id` (ON DELETE: `SET NULL`, ON UPDATE: `RESTRICT`)

**Relationships (Eloquent):**
- Model: [`App\Models\SearchLog`](file:///C:\Users\W\Desktop\wollo-lost-found\server/app/Models/SearchLog.php)
  - `$this->user()`: `belongsTo` (`User::class`) <!-- source: app/Models/SearchLog.php:52 -->
  - `$this->category()`: `belongsTo` (`Category::class`) <!-- source: app/Models/SearchLog.php:57 -->
  - `$this->campus()`: `belongsTo` (`Campus::class`) <!-- source: app/Models/SearchLog.php:62 -->

---

#### `sessions`
<!-- source: database/migrations/2026_08_30_104937_create_sessions_table.php -->
**Purpose:** Stores relational records for `sessions` in the Wollo University Lost & Found schema.
**Migration:** `2026_08_30_104937_create_sessions_table.php`

| Column | Type | Nullable | Default | Constraint | Notes |
|--------|------|----------|---------|------------|-------|
| `id` | `varchar(255)` | `NO` | NULL | PRIMARY KEY | Auto-incrementing surrogate primary key |
| `user_id` | `bigint unsigned` | `YES` | NULL | INDEX / FK | Relational foreign key reference, Unsigned numeric |
| `ip_address` | `varchar(45)` | `YES` | NULL | None | Standard column attribute |
| `user_agent` | `text` | `YES` | NULL | None | Standard column attribute |
| `payload` | `longtext` | `NO` | NULL | None | Mandatory field |
| `last_activity` | `int` | `NO` | NULL | INDEX / FK | Mandatory field |

**Indexes:**
- **`PRIMARY`**: Type `PRIMARY`, Columns: (`id`)
- **`sessions_user_id_foreign`**: Type `INDEX`, Columns: (`user_id`)
- **`sessions_last_activity_index`**: Type `INDEX`, Columns: (`last_activity`)

**Foreign Keys:**
- **`user_id`** → `users.id` (ON DELETE: `SET NULL`, ON UPDATE: `RESTRICT`)

**Relationships (Eloquent):**
- No direct dedicated Eloquent Model class (system/pivot/operational table).

---

#### `storage_locations`
<!-- source: database/migrations/2026_08_30_104914_create_storage_locations_table.php -->
**Purpose:** Stores relational records for `storage_locations` in the Wollo University Lost & Found schema.
**Migration:** `2026_08_30_104914_create_storage_locations_table.php`

| Column | Type | Nullable | Default | Constraint | Notes |
|--------|------|----------|---------|------------|-------|
| `id` | `bigint unsigned` | `NO` | NULL | PRIMARY KEY (auto_increment) | Auto-incrementing surrogate primary key, Unsigned numeric |
| `campus_id` | `bigint unsigned` | `NO` | NULL | INDEX / FK | Relational foreign key reference, Unsigned numeric, Mandatory field |
| `name` | `varchar(255)` | `NO` | NULL | None | Mandatory field |
| `code` | `varchar(50)` | `NO` | NULL | UNIQUE | Mandatory field |
| `description` | `text` | `YES` | NULL | None | Standard column attribute |
| `capacity` | `int` | `YES` | NULL | None | Standard column attribute |
| `is_active` | `tinyint(1)` | `NO` | `1` | None | Standard column attribute |
| `created_at` | `timestamp` | `YES` | NULL | None | Laravel timestamp audit |
| `updated_at` | `timestamp` | `YES` | NULL | None | Laravel timestamp audit |

**Indexes:**
- **`PRIMARY`**: Type `PRIMARY`, Columns: (`id`)
- **`storage_locations_code_unique`**: Type `UNIQUE`, Columns: (`code`)
- **`storage_locations_campus_id_foreign`**: Type `INDEX`, Columns: (`campus_id`)

**Foreign Keys:**
- **`campus_id`** → `campuses.id` (ON DELETE: `RESTRICT`, ON UPDATE: `RESTRICT`)

**Relationships (Eloquent):**
- Model: [`App\Models\StorageLocation`](file:///C:\Users\W\Desktop\wollo-lost-found\server/app/Models/StorageLocation.php)
  - `$this->campus()`: `belongsTo` (`Campus::class`) <!-- source: app/Models/StorageLocation.php:49 -->
  - `$this->custodyEvents()`: `hasMany` (`CustodyEvent::class`) <!-- source: app/Models/StorageLocation.php:54 -->
  - `$this->returns()`: `hasMany` (`ReturnRecord::class`) <!-- source: app/Models/StorageLocation.php:59 -->

---

#### `system_announcements`
<!-- source: database/migrations/2026_08_30_104933_create_system_announcements_table.php -->
**Purpose:** Stores relational records for `system_announcements` in the Wollo University Lost & Found schema.
**Migration:** `2026_08_30_104933_create_system_announcements_table.php`

| Column | Type | Nullable | Default | Constraint | Notes |
|--------|------|----------|---------|------------|-------|
| `id` | `bigint unsigned` | `NO` | NULL | PRIMARY KEY (auto_increment) | Auto-incrementing surrogate primary key, Unsigned numeric |
| `created_by` | `bigint unsigned` | `NO` | NULL | INDEX / FK | Unsigned numeric, Mandatory field |
| `title` | `varchar(255)` | `NO` | NULL | None | Mandatory field |
| `body` | `text` | `NO` | NULL | None | Mandatory field |
| `type` | `varchar(255)` | `NO` | `info` | None | Standard column attribute |
| `audience` | `varchar(255)` | `NO` | `all` | None | Standard column attribute |
| `is_active` | `tinyint(1)` | `NO` | `1` | None | Standard column attribute |
| `starts_at` | `timestamp` | `YES` | NULL | None | Standard column attribute |
| `ends_at` | `timestamp` | `YES` | NULL | None | Standard column attribute |
| `created_at` | `timestamp` | `YES` | NULL | None | Laravel timestamp audit |
| `updated_at` | `timestamp` | `YES` | NULL | None | Laravel timestamp audit |

**Indexes:**
- **`PRIMARY`**: Type `PRIMARY`, Columns: (`id`)
- **`system_announcements_created_by_foreign`**: Type `INDEX`, Columns: (`created_by`)

**Foreign Keys:**
- **`created_by`** → `users.id` (ON DELETE: `RESTRICT`, ON UPDATE: `RESTRICT`)

**Relationships (Eloquent):**
- Model: [`App\Models\SystemAnnouncement`](file:///C:\Users\W\Desktop\wollo-lost-found\server/app/Models/SystemAnnouncement.php)
  - `$this->creator()`: `belongsTo` (`User::class, 'created_by'`) <!-- source: app/Models/SystemAnnouncement.php:33 -->
  - `$this->createdByUser()`: `belongsTo` (`User::class, 'created_by'`) <!-- source: app/Models/SystemAnnouncement.php:38 -->

---

#### `system_settings`
<!-- source: database/migrations/2026_08_30_104932_create_system_settings_table.php -->
**Purpose:** Stores relational records for `system_settings` in the Wollo University Lost & Found schema.
**Migration:** `2026_08_30_104932_create_system_settings_table.php`

| Column | Type | Nullable | Default | Constraint | Notes |
|--------|------|----------|---------|------------|-------|
| `id` | `bigint unsigned` | `NO` | NULL | PRIMARY KEY (auto_increment) | Auto-incrementing surrogate primary key, Unsigned numeric |
| `key` | `varchar(255)` | `NO` | NULL | UNIQUE | Mandatory field |
| `value` | `text` | `YES` | NULL | None | Standard column attribute |
| `type` | `varchar(255)` | `NO` | `string` | None | Standard column attribute |
| `display_name` | `varchar(255)` | `YES` | NULL | None | Standard column attribute |
| `description` | `text` | `YES` | NULL | None | Standard column attribute |
| `is_public` | `tinyint(1)` | `NO` | `0` | None | Standard column attribute |
| `is_editable` | `tinyint(1)` | `NO` | `1` | None | Standard column attribute |
| `created_at` | `timestamp` | `YES` | NULL | None | Laravel timestamp audit |
| `updated_at` | `timestamp` | `YES` | NULL | None | Laravel timestamp audit |

**Indexes:**
- **`PRIMARY`**: Type `PRIMARY`, Columns: (`id`)
- **`system_settings_key_unique`**: Type `UNIQUE`, Columns: (`key`)

**Foreign Keys:**
- None

**Relationships (Eloquent):**
- Model: [`App\Models\SystemSetting`](file:///C:\Users\W\Desktop\wollo-lost-found\server/app/Models/SystemSetting.php)
  - No explicit relationship methods defined on model.

---

#### `user_organizational_units`
<!-- source: database/migrations/2026_08_30_104908_create_user_organizational_units_table.php -->
**Purpose:** Stores relational records for `user_organizational_units` in the Wollo University Lost & Found schema.
**Migration:** `2026_08_30_104908_create_user_organizational_units_table.php`

| Column | Type | Nullable | Default | Constraint | Notes |
|--------|------|----------|---------|------------|-------|
| `id` | `bigint unsigned` | `NO` | NULL | PRIMARY KEY (auto_increment) | Auto-incrementing surrogate primary key, Unsigned numeric |
| `user_id` | `bigint unsigned` | `NO` | NULL | INDEX / FK | Relational foreign key reference, Unsigned numeric, Mandatory field |
| `organizational_unit_id` | `bigint unsigned` | `NO` | NULL | INDEX / FK | Relational foreign key reference, Unsigned numeric, Mandatory field |
| `is_primary` | `tinyint(1)` | `NO` | `0` | None | Standard column attribute |
| `enrolled_year` | `smallint unsigned` | `YES` | NULL | None | Unsigned numeric |
| `created_at` | `timestamp` | `YES` | NULL | None | Laravel timestamp audit |
| `updated_at` | `timestamp` | `YES` | NULL | None | Laravel timestamp audit |

**Indexes:**
- **`PRIMARY`**: Type `PRIMARY`, Columns: (`id`)
- **`uou_user_unit_unique`**: Type `UNIQUE`, Columns: (`user_id`, `organizational_unit_id`)
- **`uou_user_primary_idx`**: Type `INDEX`, Columns: (`user_id`, `is_primary`)
- **`uou_unit_primary_idx`**: Type `INDEX`, Columns: (`organizational_unit_id`, `is_primary`)

**Foreign Keys:**
- **`organizational_unit_id`** → `organizational_units.id` (ON DELETE: `RESTRICT`, ON UPDATE: `RESTRICT`)
- **`user_id`** → `users.id` (ON DELETE: `CASCADE`, ON UPDATE: `RESTRICT`)

**Relationships (Eloquent):**
- Model: [`App\Models\UserOrganizationalUnit`](file:///C:\Users\W\Desktop\wollo-lost-found\server/app/Models/UserOrganizationalUnit.php)
  - `$this->user()`: `belongsTo` (`User::class`) <!-- source: app/Models/UserOrganizationalUnit.php:49 -->
  - `$this->organizationalUnit()`: `belongsTo` (`OrganizationalUnit::class, 'organizational_unit_id'`) <!-- source: app/Models/UserOrganizationalUnit.php:54 -->

---

#### `user_profiles`
<!-- source: database/migrations/2026_08_30_104911_create_user_profiles_table.php -->
**Purpose:** Stores relational records for `user_profiles` in the Wollo University Lost & Found schema.
**Migration:** `2026_08_30_104911_create_user_profiles_table.php`

| Column | Type | Nullable | Default | Constraint | Notes |
|--------|------|----------|---------|------------|-------|
| `id` | `bigint unsigned` | `NO` | NULL | PRIMARY KEY (auto_increment) | Auto-incrementing surrogate primary key, Unsigned numeric |
| `user_id` | `bigint unsigned` | `NO` | NULL | UNIQUE | Relational foreign key reference, Unsigned numeric, Mandatory field |
| `id_card_photo` | `varchar(255)` | `YES` | NULL | None | Standard column attribute |
| `year_of_study` | `tinyint unsigned` | `YES` | NULL | None | Unsigned numeric |
| `gender` | `varchar(20)` | `YES` | NULL | None | Standard column attribute |
| `emergency_contact_name` | `varchar(255)` | `YES` | NULL | None | Standard column attribute |
| `emergency_contact_phone` | `varchar(255)` | `YES` | NULL | None | Standard column attribute |
| `home_town` | `varchar(255)` | `YES` | NULL | None | Standard column attribute |
| `bio` | `text` | `YES` | NULL | None | Standard column attribute |
| `last_seen_at` | `timestamp` | `YES` | NULL | None | Standard column attribute |
| `created_at` | `timestamp` | `YES` | NULL | None | Laravel timestamp audit |
| `updated_at` | `timestamp` | `YES` | NULL | None | Laravel timestamp audit |

**Indexes:**
- **`PRIMARY`**: Type `PRIMARY`, Columns: (`id`)
- **`user_profiles_user_id_unique`**: Type `UNIQUE`, Columns: (`user_id`)

**Foreign Keys:**
- **`user_id`** → `users.id` (ON DELETE: `CASCADE`, ON UPDATE: `RESTRICT`)

**Relationships (Eloquent):**
- Model: [`App\Models\UserProfile`](file:///C:\Users\W\Desktop\wollo-lost-found\server/app/Models/UserProfile.php)
  - `$this->user()`: `belongsTo` (`User::class`) <!-- source: app/Models/UserProfile.php:54 -->

---

#### `users`
<!-- source: database/migrations/2026_08_30_104905_create_users_table.php -->
**Purpose:** Stores relational records for `users` in the Wollo University Lost & Found schema.
**Migration:** `2026_08_30_104905_create_users_table.php`

| Column | Type | Nullable | Default | Constraint | Notes |
|--------|------|----------|---------|------------|-------|
| `id` | `bigint unsigned` | `NO` | NULL | PRIMARY KEY (auto_increment) | Auto-incrementing surrogate primary key, Unsigned numeric |
| `role_id` | `bigint unsigned` | `NO` | NULL | INDEX / FK | Relational foreign key reference, Unsigned numeric, Mandatory field |
| `full_name` | `varchar(255)` | `NO` | NULL | None | Mandatory field |
| `university_id` | `varchar(255)` | `NO` | NULL | UNIQUE | Relational foreign key reference, Mandatory field |
| `email` | `varchar(255)` | `NO` | NULL | UNIQUE | Mandatory field |
| `email_verified_at` | `timestamp` | `YES` | NULL | None | Standard column attribute |
| `password` | `varchar(255)` | `NO` | NULL | None | Mandatory field |
| `phone` | `varchar(255)` | `YES` | NULL | None | Standard column attribute |
| `language` | `varchar(10)` | `NO` | `en` | None | Standard column attribute |
| `is_active` | `tinyint(1)` | `NO` | `1` | None | Standard column attribute |
| `profile_photo` | `varchar(255)` | `YES` | NULL | None | Standard column attribute |
| `failed_login_attempts` | `int unsigned` | `NO` | `0` | None | Unsigned numeric |
| `locked_until` | `timestamp` | `YES` | NULL | INDEX / FK | Standard column attribute |
| `remember_token` | `varchar(100)` | `YES` | NULL | None | Standard column attribute |
| `created_at` | `timestamp` | `YES` | NULL | None | Laravel timestamp audit |
| `updated_at` | `timestamp` | `YES` | NULL | None | Laravel timestamp audit |

**Indexes:**
- **`PRIMARY`**: Type `PRIMARY`, Columns: (`id`)
- **`users_university_id_unique`**: Type `UNIQUE`, Columns: (`university_id`)
- **`users_email_unique`**: Type `UNIQUE`, Columns: (`email`)
- **`users_role_id_is_active_index`**: Type `INDEX`, Columns: (`role_id`, `is_active`)
- **`users_locked_until_index`**: Type `INDEX`, Columns: (`locked_until`)

**Foreign Keys:**
- **`role_id`** → `roles.id` (ON DELETE: `RESTRICT`, ON UPDATE: `RESTRICT`)

**Relationships (Eloquent):**
- Model: [`App\Models\User`](file:///C:\Users\W\Desktop\wollo-lost-found\server/app/Models/User.php)
  - `$this->role()`: `belongsTo` (`Role::class, 'role_id'`) <!-- source: app/Models/User.php:87 -->
  - `$this->profile()`: `hasOne` (`UserProfile::class`) <!-- source: app/Models/User.php:92 -->
  - `$this->organizationalUnits()`: `belongsToMany` (`OrganizationalUnit::class,
            'user_organizational_units',
            'user_id',
            'organizational_unit_id'
        )
            ->withPivot(['is_primary', 'enrolled_year'])
            ->withTimestamps(`) <!-- source: app/Models/User.php:100 -->
  - `$this->userOrganizationalUnits()`: `hasMany` (`UserOrganizationalUnit::class`) <!-- source: app/Models/User.php:115 -->
  - `$this->reportedItems()`: `hasMany` (`Item::class, 'reporter_id'`) <!-- source: app/Models/User.php:120 -->
  - `$this->deletedItems()`: `hasMany` (`Item::class, 'deleted_by'`) <!-- source: app/Models/User.php:125 -->
  - `$this->claims()`: `hasMany` (`Claim::class, 'claimant_id'`) <!-- source: app/Models/User.php:130 -->
  - `$this->notifications()`: `hasMany` (`Notification::class`) <!-- source: app/Models/User.php:135 -->
  - `$this->notificationPreference()`: `hasOne` (`NotificationPreference::class`) <!-- source: app/Models/User.php:140 -->
  - `$this->directPermissions()`: `belongsToMany` (`Permission::class, 'permission_user')
            ->withTimestamps(`) <!-- source: app/Models/User.php:148 -->
  - `$this->auditLogs()`: `hasMany` (`AuditLog::class, 'actor_id'`) <!-- source: app/Models/User.php:154 -->
  - `$this->passwordHistories()`: `hasMany` (`PasswordHistory::class`) <!-- source: app/Models/User.php:159 -->
  - `$this->authVerifications()`: `hasMany` (`AuthVerification::class`) <!-- source: app/Models/User.php:164 -->
  - `$this->searchLogs()`: `hasMany` (`SearchLog::class`) <!-- source: app/Models/User.php:169 -->
  - `$this->itemViews()`: `hasMany` (`ItemView::class`) <!-- source: app/Models/User.php:174 -->
  - `$this->reports()`: `hasMany` (`Report::class, 'requested_by'`) <!-- source: app/Models/User.php:179 -->
  - `$this->matchSuggestionsReviewed()`: `hasMany` (`MatchSuggestion::class, 'reviewed_by'`) <!-- source: app/Models/User.php:184 -->
  - `$this->systemAnnouncements()`: `hasMany` (`SystemAnnouncement::class, 'created_by'`) <!-- source: app/Models/User.php:189 -->

---

### 4. Composite Index Rationale
<!-- source: database/migrations/2026_08_31_120000_add_performance_composite_indexes.php:1-75 -->

| Table | Index Name | Columns (Ordered) | Target Query Pattern | Selectivity & Query Plan Rationale |
|-------|------------|-------------------|----------------------|-------------------------------------|
| `items` | `idx_items_type_status_campus` | `(type, status, campus_id)` | Public browse/search (`WHERE type = 'found' AND status = 'reported' AND campus_id = ?`) | High selectivity filter. `type` (2 distinct values) + `status` (7 distinct values) narrows candidate rows instantly; `campus_id` allows immediate index-only equality pruning. |
| `items` | `idx_items_status_date` | `(status, date_lost_or_found)` | Public recent item listings (`WHERE status IN ('reported', 'under_review') ORDER BY date_lost_or_found DESC`) | Enables index range scan with sort elimination, avoiding file sort overhead for high-traffic public catalog. |
| `items` | `idx_items_category_status` | `(category_id, status)` | Category filtering (`WHERE category_id = ? AND status = ?`) | Optimizes category faceted search. `category_id` has high selectivity across 20+ categories, directly paired with status filtering. |
| `claims` | `idx_claims_status_claimant` | `(status, claimant_id)` | User claim dashboard (`WHERE claimant_id = ? AND status = ?`) | Eliminates table scans when users view their active vs historical claims in student portal. |
| `claims` | `idx_claims_item_status` | `(item_id, status)` | Item review check (`WHERE item_id = ? AND status = 'approved'`) | Accelerates conflict detection and single-approved-claim validation during staff review. |
| `match_suggestions` | `idx_match_lost_found_status` | `(lost_item_id, found_item_id, status)` | Match suggestion lookups & deduplication (`WHERE lost_item_id = ? AND found_item_id = ?`) | Prevents duplicate generation and provides instant lookup for match confirmation endpoints. |
| `custody_events` | `idx_custody_item_event` | `(item_id, event_type, created_at)` | Custody timeline rendering (`WHERE item_id = ? ORDER BY created_at DESC`) | Supports index-ordered traversal of physical custody history for security auditing without sorting memory buffers. |
| `returns` | `idx_returns_status_token` | `(status, confirmation_token)` | Token verification endpoint (`WHERE confirmation_token = ? AND status = 'pending_confirmation'`) | Guarantees O(1) single-use token validation on public return confirmation links. |
| `audit_logs` | `idx_audit_user_action_time` | `(user_id, action, created_at)` | Admin user audit trail (`WHERE user_id = ? AND action = ? ORDER BY created_at DESC`) | Accelerates compliance filtering on large append-only audit tables. |

---

### 5. Normalization Audit

| Table Name | Normal Form Achieved | Formal Justification & Denormalization Notes |
|------------|----------------------|------------------------------------------------|
| `audit_logs` | 3NF | Satisfies 1NF (atomic columns), 2NF (all non-key attributes fully functionally dependent on PK), and 3NF (no transitive dependencies). |
| `auth_verifications` | 3NF | Satisfies 1NF (atomic columns), 2NF (all non-key attributes fully functionally dependent on PK), and 3NF (no transitive dependencies). |
| `cache` | 3NF | Satisfies 1NF (atomic columns), 2NF (all non-key attributes fully functionally dependent on PK), and 3NF (no transitive dependencies). |
| `cache_locks` | 3NF | Satisfies 1NF (atomic columns), 2NF (all non-key attributes fully functionally dependent on PK), and 3NF (no transitive dependencies). |
| `campuses` | 3NF | Satisfies 1NF (atomic columns), 2NF (all non-key attributes fully functionally dependent on PK), and 3NF (no transitive dependencies). |
| `categories` | 3NF | Satisfies 1NF (atomic columns), 2NF (all non-key attributes fully functionally dependent on PK), and 3NF (no transitive dependencies). |
| `claim_evidence` | 3NF | Satisfies 1NF (atomic columns), 2NF (all non-key attributes fully functionally dependent on PK), and 3NF (no transitive dependencies). |
| `claim_status_histories` | 3NF | Satisfies 1NF (atomic columns), 2NF (all non-key attributes fully functionally dependent on PK), and 3NF (no transitive dependencies). |
| `claims` | 3NF | Satisfies 1NF (atomic columns), 2NF (all non-key attributes fully functionally dependent on PK), and 3NF (no transitive dependencies). |
| `custody_events` | 3NF | Satisfies 1NF (atomic columns), 2NF (all non-key attributes fully functionally dependent on PK), and 3NF (no transitive dependencies). |
| `failed_jobs` | 3NF | Satisfies 1NF (atomic columns), 2NF (all non-key attributes fully functionally dependent on PK), and 3NF (no transitive dependencies). |
| `item_photos` | 3NF | Satisfies 1NF (atomic columns), 2NF (all non-key attributes fully functionally dependent on PK), and 3NF (no transitive dependencies). |
| `item_status_histories` | 3NF | Satisfies 1NF (atomic columns), 2NF (all non-key attributes fully functionally dependent on PK), and 3NF (no transitive dependencies). |
| `item_tags` | 3NF | Satisfies 1NF (atomic columns), 2NF (all non-key attributes fully functionally dependent on PK), and 3NF (no transitive dependencies). |
| `item_views` | 3NF | 3NF with intentional denormalized `views_count` caching counter to minimize continuous atomic aggregate recalculation under heavy read traffic. |
| `items` | 3NF | Satisfies 1NF (atomic columns), 2NF (all non-key attributes fully functionally dependent on PK), and 3NF (no transitive dependencies). |
| `job_batches` | 3NF | Satisfies 1NF (atomic columns), 2NF (all non-key attributes fully functionally dependent on PK), and 3NF (no transitive dependencies). |
| `jobs` | 3NF | Satisfies 1NF (atomic columns), 2NF (all non-key attributes fully functionally dependent on PK), and 3NF (no transitive dependencies). |
| `locations` | 3NF | Satisfies 1NF (atomic columns), 2NF (all non-key attributes fully functionally dependent on PK), and 3NF (no transitive dependencies). |
| `match_suggestions` | 3NF | Satisfies 1NF (atomic columns), 2NF (all non-key attributes fully functionally dependent on PK), and 3NF (no transitive dependencies). |
| `migrations` | 3NF | Satisfies 1NF (atomic columns), 2NF (all non-key attributes fully functionally dependent on PK), and 3NF (no transitive dependencies). |
| `notification_preferences` | 3NF | Satisfies 1NF (atomic columns), 2NF (all non-key attributes fully functionally dependent on PK), and 3NF (no transitive dependencies). |
| `notifications` | 3NF | Satisfies 1NF (atomic columns), 2NF (all non-key attributes fully functionally dependent on PK), and 3NF (no transitive dependencies). |
| `organizational_unit_type_relations` | 3NF | Satisfies 1NF (atomic columns), 2NF (all non-key attributes fully functionally dependent on PK), and 3NF (no transitive dependencies). |
| `organizational_unit_types` | 3NF | Satisfies 1NF (atomic columns), 2NF (all non-key attributes fully functionally dependent on PK), and 3NF (no transitive dependencies). |
| `organizational_units` | 3NF | Satisfies 1NF (atomic columns), 2NF (all non-key attributes fully functionally dependent on PK), and 3NF (no transitive dependencies). |
| `password_histories` | 3NF | Satisfies 1NF (atomic columns), 2NF (all non-key attributes fully functionally dependent on PK), and 3NF (no transitive dependencies). |
| `permission_groups` | 3NF | Satisfies 1NF (atomic columns), 2NF (all non-key attributes fully functionally dependent on PK), and 3NF (no transitive dependencies). |
| `permission_role` | 3NF | Satisfies 1NF (atomic columns), 2NF (all non-key attributes fully functionally dependent on PK), and 3NF (no transitive dependencies). |
| `permission_user` | 3NF | Satisfies 1NF (atomic columns), 2NF (all non-key attributes fully functionally dependent on PK), and 3NF (no transitive dependencies). |
| `permissions` | 3NF | Satisfies 1NF (atomic columns), 2NF (all non-key attributes fully functionally dependent on PK), and 3NF (no transitive dependencies). |
| `reports` | 3NF | Satisfies 1NF (atomic columns), 2NF (all non-key attributes fully functionally dependent on PK), and 3NF (no transitive dependencies). |
| `return_documents` | 3NF | Satisfies 1NF (atomic columns), 2NF (all non-key attributes fully functionally dependent on PK), and 3NF (no transitive dependencies). |
| `returns` | 3NF | Satisfies 1NF (atomic columns), 2NF (all non-key attributes fully functionally dependent on PK), and 3NF (no transitive dependencies). |
| `roles` | 3NF | Satisfies 1NF (atomic columns), 2NF (all non-key attributes fully functionally dependent on PK), and 3NF (no transitive dependencies). |
| `search_logs` | 3NF | Satisfies 1NF (atomic columns), 2NF (all non-key attributes fully functionally dependent on PK), and 3NF (no transitive dependencies). |
| `sessions` | 3NF | Satisfies 1NF (atomic columns), 2NF (all non-key attributes fully functionally dependent on PK), and 3NF (no transitive dependencies). |
| `storage_locations` | 3NF | Satisfies 1NF (atomic columns), 2NF (all non-key attributes fully functionally dependent on PK), and 3NF (no transitive dependencies). |
| `system_announcements` | 3NF | Satisfies 1NF (atomic columns), 2NF (all non-key attributes fully functionally dependent on PK), and 3NF (no transitive dependencies). |
| `system_settings` | 3NF | Key-value configuration store satisfying 1NF/2NF/3NF for dynamic runtime parameters. |
| `user_organizational_units` | 3NF | Satisfies 1NF (atomic columns), 2NF (all non-key attributes fully functionally dependent on PK), and 3NF (no transitive dependencies). |
| `user_profiles` | 3NF | Satisfies 1NF (atomic columns), 2NF (all non-key attributes fully functionally dependent on PK), and 3NF (no transitive dependencies). |
| `users` | 3NF | 3NF with role cached via `role_id` / pivot for rapid authorization resolution during authentication lifecycle. |

---

### 6. Transaction Boundaries
<!-- source: app/Domain/ -->
<!-- source: app/Support/Services/ -->

The codebase enforces strict transaction boundaries around multi-table mutations to prevent inconsistent partial state:

| # | Source File & Line | Operations Wrapped in `DB::transaction()` | Rollback Guarantee |
|---|--------------------|--------------------------------------------|--------------------|
| 1 | [`C:/Users/W/Desktop/wollo-lost-found/server/app/Domain/Administration/Services/UserAdministrationService.php:15`](file:///C:\Users\W\Desktop\wollo-lost-found\server/C:/Users/W/Desktop/wollo-lost-found/server/app/Domain/Administration/Services/UserAdministrationService.php#L15) <!-- source: C:/Users/W/Desktop/wollo-lost-found/server/app/Domain/Administration/Services/UserAdministrationService.php:15 --> | Synchronizes role/permission pivot mappings, updates target entity, flushes permission cache across all active users. | Rolls back all table inserts/updates if any validation, constraint violation, or exception occurs. |
| 2 | [`C:/Users/W/Desktop/wollo-lost-found/server/app/Domain/Administration/Services/UserAdministrationService.php:24`](file:///C:\Users\W\Desktop\wollo-lost-found\server/C:/Users/W/Desktop/wollo-lost-found/server/app/Domain/Administration/Services/UserAdministrationService.php#L24) <!-- source: C:/Users/W/Desktop/wollo-lost-found/server/app/Domain/Administration/Services/UserAdministrationService.php:24 --> | Synchronizes role/permission pivot mappings, updates target entity, flushes permission cache across all active users. | Rolls back all table inserts/updates if any validation, constraint violation, or exception occurs. |
| 3 | [`C:/Users/W/Desktop/wollo-lost-found/server/app/Domain/Auth/Actions/RegisterUser.php:25`](file:///C:\Users\W\Desktop\wollo-lost-found\server/C:/Users/W/Desktop/wollo-lost-found/server/app/Domain/Auth/Actions/RegisterUser.php#L25) <!-- source: C:/Users/W/Desktop/wollo-lost-found/server/app/Domain/Auth/Actions/RegisterUser.php:25 --> | Creates `users` record, initializes `user_profiles`, attaches default `student` role, generates OTP verification record in `auth_verifications`. | Rolls back all table inserts/updates if any validation, constraint violation, or exception occurs. |
| 4 | [`C:/Users/W/Desktop/wollo-lost-found/server/app/Domain/Claims/Actions/ApproveClaim.php:15`](file:///C:\Users\W\Desktop\wollo-lost-found\server/C:/Users/W/Desktop/wollo-lost-found/server/app/Domain/Claims/Actions/ApproveClaim.php#L15) <!-- source: C:/Users/W/Desktop/wollo-lost-found/server/app/Domain/Claims/Actions/ApproveClaim.php:15 --> | Updates `claims.status = 'approved'`, updates `items.status = 'claimed'`, marks all competing claims for the same item as `rejected`, logs `claim_status_histories`. | Rolls back all table inserts/updates if any validation, constraint violation, or exception occurs. |
| 5 | [`C:/Users/W/Desktop/wollo-lost-found/server/app/Domain/Claims/Actions/CreateClaim.php:28`](file:///C:\Users\W\Desktop\wollo-lost-found\server/C:/Users/W/Desktop/wollo-lost-found/server/app/Domain/Claims/Actions/CreateClaim.php#L28) <!-- source: C:/Users/W/Desktop/wollo-lost-found/server/app/Domain/Claims/Actions/CreateClaim.php:28 --> | Atomic mutation of domain entities and associated status/audit histories. | Rolls back all table inserts/updates if any validation, constraint violation, or exception occurs. |
| 6 | [`C:/Users/W/Desktop/wollo-lost-found/server/app/Domain/Claims/Actions/RejectClaim.php:14`](file:///C:\Users\W\Desktop\wollo-lost-found\server/C:/Users/W/Desktop/wollo-lost-found/server/app/Domain/Claims/Actions/RejectClaim.php#L14) <!-- source: C:/Users/W/Desktop/wollo-lost-found/server/app/Domain/Claims/Actions/RejectClaim.php:14 --> | Updates `claims.status`, reverts `items.status` to `under_review`, records transition in `claim_status_histories` and `item_status_histories`. | Rolls back all table inserts/updates if any validation, constraint violation, or exception occurs. |
| 7 | [`C:/Users/W/Desktop/wollo-lost-found/server/app/Domain/Claims/Actions/ReverseClaimApproval.php:13`](file:///C:\Users\W\Desktop\wollo-lost-found\server/C:/Users/W/Desktop/wollo-lost-found/server/app/Domain/Claims/Actions/ReverseClaimApproval.php#L13) <!-- source: C:/Users/W/Desktop/wollo-lost-found/server/app/Domain/Claims/Actions/ReverseClaimApproval.php:13 --> | Updates `claims.status`, reverts `items.status` to `under_review`, records transition in `claim_status_histories` and `item_status_histories`. | Rolls back all table inserts/updates if any validation, constraint violation, or exception occurs. |
| 8 | [`C:/Users/W/Desktop/wollo-lost-found/server/app/Domain/Custody/Actions/RecordCustodyEvent.php:14`](file:///C:\Users\W\Desktop\wollo-lost-found\server/C:/Users/W/Desktop/wollo-lost-found/server/app/Domain/Custody/Actions/RecordCustodyEvent.php#L14) <!-- source: C:/Users/W/Desktop/wollo-lost-found/server/app/Domain/Custody/Actions/RecordCustodyEvent.php:14 --> | Atomic mutation of domain entities and associated status/audit histories. | Rolls back all table inserts/updates if any validation, constraint violation, or exception occurs. |
| 9 | [`C:/Users/W/Desktop/wollo-lost-found/server/app/Domain/Items/Actions/ChangeItemStatus.php:20`](file:///C:\Users\W\Desktop\wollo-lost-found\server/C:/Users/W/Desktop/wollo-lost-found/server/app/Domain/Items/Actions/ChangeItemStatus.php#L20) <!-- source: C:/Users/W/Desktop/wollo-lost-found/server/app/Domain/Items/Actions/ChangeItemStatus.php:20 --> | Atomic mutation of domain entities and associated status/audit histories. | Rolls back all table inserts/updates if any validation, constraint violation, or exception occurs. |
| 10 | [`C:/Users/W/Desktop/wollo-lost-found/server/app/Domain/Items/Actions/CreateFoundItem.php:19`](file:///C:\Users\W\Desktop\wollo-lost-found\server/C:/Users/W/Desktop/wollo-lost-found/server/app/Domain/Items/Actions/CreateFoundItem.php#L19) <!-- source: C:/Users/W/Desktop/wollo-lost-found/server/app/Domain/Items/Actions/CreateFoundItem.php:19 --> | Inserts `items` record, creates initial `item_status_histories` entry, generates `item_tags` associations, records initial `custody_events` if staff. | Rolls back all table inserts/updates if any validation, constraint violation, or exception occurs. |
| 11 | [`C:/Users/W/Desktop/wollo-lost-found/server/app/Domain/Items/Actions/CreateLostItem.php:18`](file:///C:\Users\W\Desktop\wollo-lost-found\server/C:/Users/W/Desktop/wollo-lost-found/server/app/Domain/Items/Actions/CreateLostItem.php#L18) <!-- source: C:/Users/W/Desktop/wollo-lost-found/server/app/Domain/Items/Actions/CreateLostItem.php:18 --> | Inserts `items` record, creates initial `item_status_histories` entry, generates `item_tags` associations, records initial `custody_events` if staff. | Rolls back all table inserts/updates if any validation, constraint violation, or exception occurs. |
| 12 | [`C:/Users/W/Desktop/wollo-lost-found/server/app/Domain/Returns/Actions/RecordReturn.php:24`](file:///C:\Users\W\Desktop\wollo-lost-found\server/C:/Users/W/Desktop/wollo-lost-found/server/app/Domain/Returns/Actions/RecordReturn.php#L24) <!-- source: C:/Users/W/Desktop/wollo-lost-found/server/app/Domain/Returns/Actions/RecordReturn.php:24 --> | Inserts or updates `returns` record, updates `items.status = 'returned'`, logs releasing `custody_events`, generates `return_documents`. | Rolls back all table inserts/updates if any validation, constraint violation, or exception occurs. |
| 13 | [`C:/Users/W/Desktop/wollo-lost-found/server/app/Http/Controllers/Api/V1/Admin/OrganizationalUnitController.php:72`](file:///C:\Users\W\Desktop\wollo-lost-found\server/C:/Users/W/Desktop/wollo-lost-found/server/app/Http/Controllers/Api/V1/Admin/OrganizationalUnitController.php#L72) <!-- source: C:/Users/W/Desktop/wollo-lost-found/server/app/Http/Controllers/Api/V1/Admin/OrganizationalUnitController.php:72 --> | Atomic mutation of domain entities and associated status/audit histories. | Rolls back all table inserts/updates if any validation, constraint violation, or exception occurs. |
| 14 | [`C:/Users/W/Desktop/wollo-lost-found/server/app/Http/Controllers/Api/V1/Admin/OrganizationalUnitController.php:108`](file:///C:\Users\W\Desktop\wollo-lost-found\server/C:/Users/W/Desktop/wollo-lost-found/server/app/Http/Controllers/Api/V1/Admin/OrganizationalUnitController.php#L108) <!-- source: C:/Users/W/Desktop/wollo-lost-found/server/app/Http/Controllers/Api/V1/Admin/OrganizationalUnitController.php:108 --> | Atomic mutation of domain entities and associated status/audit histories. | Rolls back all table inserts/updates if any validation, constraint violation, or exception occurs. |
| 15 | [`C:/Users/W/Desktop/wollo-lost-found/server/app/Http/Controllers/Api/V1/Admin/OrganizationalUnitController.php:123`](file:///C:\Users\W\Desktop\wollo-lost-found\server/C:/Users/W/Desktop/wollo-lost-found/server/app/Http/Controllers/Api/V1/Admin/OrganizationalUnitController.php#L123) <!-- source: C:/Users/W/Desktop/wollo-lost-found/server/app/Http/Controllers/Api/V1/Admin/OrganizationalUnitController.php:123 --> | Atomic mutation of domain entities and associated status/audit histories. | Rolls back all table inserts/updates if any validation, constraint violation, or exception occurs. |
| 16 | [`C:/Users/W/Desktop/wollo-lost-found/server/app/Http/Controllers/Api/V1/Admin/OrganizationalUnitTypeController.php:68`](file:///C:\Users\W\Desktop\wollo-lost-found\server/C:/Users/W/Desktop/wollo-lost-found/server/app/Http/Controllers/Api/V1/Admin/OrganizationalUnitTypeController.php#L68) <!-- source: C:/Users/W/Desktop/wollo-lost-found/server/app/Http/Controllers/Api/V1/Admin/OrganizationalUnitTypeController.php:68 --> | Atomic mutation of domain entities and associated status/audit histories. | Rolls back all table inserts/updates if any validation, constraint violation, or exception occurs. |
| 17 | [`C:/Users/W/Desktop/wollo-lost-found/server/app/Http/Controllers/Api/V1/Admin/OrganizationalUnitTypeController.php:103`](file:///C:\Users\W\Desktop\wollo-lost-found\server/C:/Users/W/Desktop/wollo-lost-found/server/app/Http/Controllers/Api/V1/Admin/OrganizationalUnitTypeController.php#L103) <!-- source: C:/Users/W/Desktop/wollo-lost-found/server/app/Http/Controllers/Api/V1/Admin/OrganizationalUnitTypeController.php:103 --> | Atomic mutation of domain entities and associated status/audit histories. | Rolls back all table inserts/updates if any validation, constraint violation, or exception occurs. |
| 18 | [`C:/Users/W/Desktop/wollo-lost-found/server/app/Http/Controllers/Api/V1/Admin/OrganizationalUnitTypeController.php:118`](file:///C:\Users\W\Desktop\wollo-lost-found\server/C:/Users/W/Desktop/wollo-lost-found/server/app/Http/Controllers/Api/V1/Admin/OrganizationalUnitTypeController.php#L118) <!-- source: C:/Users/W/Desktop/wollo-lost-found/server/app/Http/Controllers/Api/V1/Admin/OrganizationalUnitTypeController.php:118 --> | Atomic mutation of domain entities and associated status/audit histories. | Rolls back all table inserts/updates if any validation, constraint violation, or exception occurs. |
| 19 | [`C:/Users/W/Desktop/wollo-lost-found/server/app/Http/Controllers/Api/V1/Admin/UserManagementController.php:74`](file:///C:\Users\W\Desktop\wollo-lost-found\server/C:/Users/W/Desktop/wollo-lost-found/server/app/Http/Controllers/Api/V1/Admin/UserManagementController.php#L74) <!-- source: C:/Users/W/Desktop/wollo-lost-found/server/app/Http/Controllers/Api/V1/Admin/UserManagementController.php:74 --> | Atomic mutation of domain entities and associated status/audit histories. | Rolls back all table inserts/updates if any validation, constraint violation, or exception occurs. |
| 20 | [`C:/Users/W/Desktop/wollo-lost-found/server/app/Http/Controllers/Api/V1/Admin/UserManagementController.php:147`](file:///C:\Users\W\Desktop\wollo-lost-found\server/C:/Users/W/Desktop/wollo-lost-found/server/app/Http/Controllers/Api/V1/Admin/UserManagementController.php#L147) <!-- source: C:/Users/W/Desktop/wollo-lost-found/server/app/Http/Controllers/Api/V1/Admin/UserManagementController.php:147 --> | Atomic mutation of domain entities and associated status/audit histories. | Rolls back all table inserts/updates if any validation, constraint violation, or exception occurs. |
| 21 | [`C:/Users/W/Desktop/wollo-lost-found/server/app/Http/Controllers/Api/V1/Admin/UserManagementController.php:167`](file:///C:\Users\W\Desktop\wollo-lost-found\server/C:/Users/W/Desktop/wollo-lost-found/server/app/Http/Controllers/Api/V1/Admin/UserManagementController.php#L167) <!-- source: C:/Users/W/Desktop/wollo-lost-found/server/app/Http/Controllers/Api/V1/Admin/UserManagementController.php:167 --> | Atomic mutation of domain entities and associated status/audit histories. | Rolls back all table inserts/updates if any validation, constraint violation, or exception occurs. |
| 22 | [`C:/Users/W/Desktop/wollo-lost-found/server/app/Http/Controllers/Api/V1/Admin/UserManagementController.php:225`](file:///C:\Users\W\Desktop\wollo-lost-found\server/C:/Users/W/Desktop/wollo-lost-found/server/app/Http/Controllers/Api/V1/Admin/UserManagementController.php#L225) <!-- source: C:/Users/W/Desktop/wollo-lost-found/server/app/Http/Controllers/Api/V1/Admin/UserManagementController.php:225 --> | Atomic mutation of domain entities and associated status/audit histories. | Rolls back all table inserts/updates if any validation, constraint violation, or exception occurs. |
| 23 | [`C:/Users/W/Desktop/wollo-lost-found/server/app/Http/Controllers/Api/V1/Auth/PasswordController.php:37`](file:///C:\Users\W\Desktop\wollo-lost-found\server/C:/Users/W/Desktop/wollo-lost-found/server/app/Http/Controllers/Api/V1/Auth/PasswordController.php#L37) <!-- source: C:/Users/W/Desktop/wollo-lost-found/server/app/Http/Controllers/Api/V1/Auth/PasswordController.php:37 --> | Atomic mutation of domain entities and associated status/audit histories. | Rolls back all table inserts/updates if any validation, constraint violation, or exception occurs. |
| 24 | [`C:/Users/W/Desktop/wollo-lost-found/server/app/Http/Controllers/Api/V1/Auth/PasswordResetController.php:110`](file:///C:\Users\W\Desktop\wollo-lost-found\server/C:/Users/W/Desktop/wollo-lost-found/server/app/Http/Controllers/Api/V1/Auth/PasswordResetController.php#L110) <!-- source: C:/Users/W/Desktop/wollo-lost-found/server/app/Http/Controllers/Api/V1/Auth/PasswordResetController.php:110 --> | Atomic mutation of domain entities and associated status/audit histories. | Rolls back all table inserts/updates if any validation, constraint violation, or exception occurs. |
| 25 | [`C:/Users/W/Desktop/wollo-lost-found/server/app/Http/Controllers/Api/V1/Auth/RegisterController.php:28`](file:///C:\Users\W\Desktop\wollo-lost-found\server/C:/Users/W/Desktop/wollo-lost-found/server/app/Http/Controllers/Api/V1/Auth/RegisterController.php#L28) <!-- source: C:/Users/W/Desktop/wollo-lost-found/server/app/Http/Controllers/Api/V1/Auth/RegisterController.php:28 --> | Atomic mutation of domain entities and associated status/audit histories. | Rolls back all table inserts/updates if any validation, constraint violation, or exception occurs. |
| 26 | [`C:/Users/W/Desktop/wollo-lost-found/server/app/Http/Controllers/Api/V1/Auth/VerificationController.php:34`](file:///C:\Users\W\Desktop\wollo-lost-found\server/C:/Users/W/Desktop/wollo-lost-found/server/app/Http/Controllers/Api/V1/Auth/VerificationController.php#L34) <!-- source: C:/Users/W/Desktop/wollo-lost-found/server/app/Http/Controllers/Api/V1/Auth/VerificationController.php:34 --> | Atomic mutation of domain entities and associated status/audit histories. | Rolls back all table inserts/updates if any validation, constraint violation, or exception occurs. |
| 27 | [`C:/Users/W/Desktop/wollo-lost-found/server/app/Http/Controllers/Api/V1/Claims/ClaimController.php:61`](file:///C:\Users\W\Desktop\wollo-lost-found\server/C:/Users/W/Desktop/wollo-lost-found/server/app/Http/Controllers/Api/V1/Claims/ClaimController.php#L61) <!-- source: C:/Users/W/Desktop/wollo-lost-found/server/app/Http/Controllers/Api/V1/Claims/ClaimController.php:61 --> | Atomic mutation of domain entities and associated status/audit histories. | Rolls back all table inserts/updates if any validation, constraint violation, or exception occurs. |
| 28 | [`C:/Users/W/Desktop/wollo-lost-found/server/app/Http/Controllers/Api/V1/Claims/ClaimReviewController.php:36`](file:///C:\Users\W\Desktop\wollo-lost-found\server/C:/Users/W/Desktop/wollo-lost-found/server/app/Http/Controllers/Api/V1/Claims/ClaimReviewController.php#L36) <!-- source: C:/Users/W/Desktop/wollo-lost-found/server/app/Http/Controllers/Api/V1/Claims/ClaimReviewController.php:36 --> | Atomic mutation of domain entities and associated status/audit histories. | Rolls back all table inserts/updates if any validation, constraint violation, or exception occurs. |
| 29 | [`C:/Users/W/Desktop/wollo-lost-found/server/app/Http/Controllers/Api/V1/Claims/ClaimReviewController.php:150`](file:///C:\Users\W\Desktop\wollo-lost-found\server/C:/Users/W/Desktop/wollo-lost-found/server/app/Http/Controllers/Api/V1/Claims/ClaimReviewController.php#L150) <!-- source: C:/Users/W/Desktop/wollo-lost-found/server/app/Http/Controllers/Api/V1/Claims/ClaimReviewController.php:150 --> | Atomic mutation of domain entities and associated status/audit histories. | Rolls back all table inserts/updates if any validation, constraint violation, or exception occurs. |
| 30 | [`C:/Users/W/Desktop/wollo-lost-found/server/app/Http/Controllers/Api/V1/Custody/CustodyController.php:61`](file:///C:\Users\W\Desktop\wollo-lost-found\server/C:/Users/W/Desktop/wollo-lost-found/server/app/Http/Controllers/Api/V1/Custody/CustodyController.php#L61) <!-- source: C:/Users/W/Desktop/wollo-lost-found/server/app/Http/Controllers/Api/V1/Custody/CustodyController.php:61 --> | Atomic mutation of domain entities and associated status/audit histories. | Rolls back all table inserts/updates if any validation, constraint violation, or exception occurs. |
| 31 | [`C:/Users/W/Desktop/wollo-lost-found/server/app/Http/Controllers/Api/V1/Custody/CustodyController.php:86`](file:///C:\Users\W\Desktop\wollo-lost-found\server/C:/Users/W/Desktop/wollo-lost-found/server/app/Http/Controllers/Api/V1/Custody/CustodyController.php#L86) <!-- source: C:/Users/W/Desktop/wollo-lost-found/server/app/Http/Controllers/Api/V1/Custody/CustodyController.php:86 --> | Atomic mutation of domain entities and associated status/audit histories. | Rolls back all table inserts/updates if any validation, constraint violation, or exception occurs. |
| 32 | [`C:/Users/W/Desktop/wollo-lost-found/server/app/Http/Controllers/Api/V1/Items/ItemController.php:108`](file:///C:\Users\W\Desktop\wollo-lost-found\server/C:/Users/W/Desktop/wollo-lost-found/server/app/Http/Controllers/Api/V1/Items/ItemController.php#L108) <!-- source: C:/Users/W/Desktop/wollo-lost-found/server/app/Http/Controllers/Api/V1/Items/ItemController.php:108 --> | Atomic mutation of domain entities and associated status/audit histories. | Rolls back all table inserts/updates if any validation, constraint violation, or exception occurs. |
| 33 | [`C:/Users/W/Desktop/wollo-lost-found/server/app/Http/Controllers/Api/V1/Items/ItemController.php:223`](file:///C:\Users\W\Desktop\wollo-lost-found\server/C:/Users/W/Desktop/wollo-lost-found/server/app/Http/Controllers/Api/V1/Items/ItemController.php#L223) <!-- source: C:/Users/W/Desktop/wollo-lost-found/server/app/Http/Controllers/Api/V1/Items/ItemController.php:223 --> | Atomic mutation of domain entities and associated status/audit histories. | Rolls back all table inserts/updates if any validation, constraint violation, or exception occurs. |
| 34 | [`C:/Users/W/Desktop/wollo-lost-found/server/app/Http/Controllers/Api/V1/Items/ItemController.php:419`](file:///C:\Users\W\Desktop\wollo-lost-found\server/C:/Users/W/Desktop/wollo-lost-found/server/app/Http/Controllers/Api/V1/Items/ItemController.php#L419) <!-- source: C:/Users/W/Desktop/wollo-lost-found/server/app/Http/Controllers/Api/V1/Items/ItemController.php:419 --> | Atomic mutation of domain entities and associated status/audit histories. | Rolls back all table inserts/updates if any validation, constraint violation, or exception occurs. |
| 35 | [`C:/Users/W/Desktop/wollo-lost-found/server/app/Http/Controllers/Api/V1/Items/ItemPhotoController.php:26`](file:///C:\Users\W\Desktop\wollo-lost-found\server/C:/Users/W/Desktop/wollo-lost-found/server/app/Http/Controllers/Api/V1/Items/ItemPhotoController.php#L26) <!-- source: C:/Users/W/Desktop/wollo-lost-found/server/app/Http/Controllers/Api/V1/Items/ItemPhotoController.php:26 --> | Atomic mutation of domain entities and associated status/audit histories. | Rolls back all table inserts/updates if any validation, constraint violation, or exception occurs. |
| 36 | [`C:/Users/W/Desktop/wollo-lost-found/server/app/Http/Controllers/Api/V1/Items/ItemStatusController.php:44`](file:///C:\Users\W\Desktop\wollo-lost-found\server/C:/Users/W/Desktop/wollo-lost-found/server/app/Http/Controllers/Api/V1/Items/ItemStatusController.php#L44) <!-- source: C:/Users/W/Desktop/wollo-lost-found/server/app/Http/Controllers/Api/V1/Items/ItemStatusController.php:44 --> | Atomic mutation of domain entities and associated status/audit histories. | Rolls back all table inserts/updates if any validation, constraint violation, or exception occurs. |
| 37 | [`C:/Users/W/Desktop/wollo-lost-found/server/app/Http/Controllers/Api/V1/Returns/ReturnController.php:71`](file:///C:\Users\W\Desktop\wollo-lost-found\server/C:/Users/W/Desktop/wollo-lost-found/server/app/Http/Controllers/Api/V1/Returns/ReturnController.php#L71) <!-- source: C:/Users/W/Desktop/wollo-lost-found/server/app/Http/Controllers/Api/V1/Returns/ReturnController.php:71 --> | Atomic mutation of domain entities and associated status/audit histories. | Rolls back all table inserts/updates if any validation, constraint violation, or exception occurs. |
| 38 | [`C:/Users/W/Desktop/wollo-lost-found/server/app/Http/Controllers/Api/V1/Returns/ReturnController.php:232`](file:///C:\Users\W\Desktop\wollo-lost-found\server/C:/Users/W/Desktop/wollo-lost-found/server/app/Http/Controllers/Api/V1/Returns/ReturnController.php#L232) <!-- source: C:/Users/W/Desktop/wollo-lost-found/server/app/Http/Controllers/Api/V1/Returns/ReturnController.php:232 --> | Atomic mutation of domain entities and associated status/audit histories. | Rolls back all table inserts/updates if any validation, constraint violation, or exception occurs. |
| 39 | [`C:/Users/W/Desktop/wollo-lost-found/server/app/Http/Controllers/Api/V1/Returns/ReturnController.php:288`](file:///C:\Users\W\Desktop\wollo-lost-found\server/C:/Users/W/Desktop/wollo-lost-found/server/app/Http/Controllers/Api/V1/Returns/ReturnController.php#L288) <!-- source: C:/Users/W/Desktop/wollo-lost-found/server/app/Http/Controllers/Api/V1/Returns/ReturnController.php:288 --> | Atomic mutation of domain entities and associated status/audit histories. | Rolls back all table inserts/updates if any validation, constraint violation, or exception occurs. |
| 40 | [`C:/Users/W/Desktop/wollo-lost-found/server/app/Support/Services/RoleService.php:61`](file:///C:\Users\W\Desktop\wollo-lost-found\server/C:/Users/W/Desktop/wollo-lost-found/server/app/Support/Services/RoleService.php#L61) <!-- source: C:/Users/W/Desktop/wollo-lost-found/server/app/Support/Services/RoleService.php:61 --> | Synchronizes role/permission pivot mappings, updates target entity, flushes permission cache across all active users. | Rolls back all table inserts/updates if any validation, constraint violation, or exception occurs. |
| 41 | [`C:/Users/W/Desktop/wollo-lost-found/server/app/Support/Services/RoleService.php:91`](file:///C:\Users\W\Desktop\wollo-lost-found\server/C:/Users/W/Desktop/wollo-lost-found/server/app/Support/Services/RoleService.php#L91) <!-- source: C:/Users/W/Desktop/wollo-lost-found/server/app/Support/Services/RoleService.php:91 --> | Synchronizes role/permission pivot mappings, updates target entity, flushes permission cache across all active users. | Rolls back all table inserts/updates if any validation, constraint violation, or exception occurs. |
| 42 | [`C:/Users/W/Desktop/wollo-lost-found/server/app/Support/Services/RoleService.php:143`](file:///C:\Users\W\Desktop\wollo-lost-found\server/C:/Users/W/Desktop/wollo-lost-found/server/app/Support/Services/RoleService.php#L143) <!-- source: C:/Users/W/Desktop/wollo-lost-found/server/app/Support/Services/RoleService.php:143 --> | Synchronizes role/permission pivot mappings, updates target entity, flushes permission cache across all active users. | Rolls back all table inserts/updates if any validation, constraint violation, or exception occurs. |