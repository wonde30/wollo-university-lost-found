---
title: Data Dictionary
version: 1.0.0
system: Wollo University Lost & Found System
generated_from: source code (migrations, controllers, policies, routes, seeders)
date: 2026-08-31
---

# Data Dictionary
## Wollo University Lost & Found System

<!-- source: database/migrations/ -->
<!-- source: app/Models/ -->

This document provides the complete, field-by-field master data dictionary for every column across all 43 tables in the database schema. Descriptions reflect actual usage in Eloquent models, validation rules, and domain services.

| Table | Column | Data Type | Nullable | Default | PK | FK | Description |
|-------|--------|-----------|----------|---------|----|----|-------------|
| `audit_logs` | `id` | `bigint unsigned` | `NO` | NULL | `YES` | NO | Unique auto-incrementing primary identifier for the audit_logs entity. |
| `audit_logs` | `actor_id` | `bigint unsigned` | `YES` | NULL | `NO` | `users.id` | Foreign key reference to `users.id`. |
| `audit_logs` | `actor_role` | `varchar(255)` | `YES` | NULL | `NO` | NO | Stores relational attribute `actor_role` for `audit_logs` records. |
| `audit_logs` | `action` | `varchar(255)` | `NO` | NULL | `NO` | NO | Action descriptor (e.g. `auth.login`, `claim.approved`, `role.updated`). |
| `audit_logs` | `auditable_type` | `varchar(255)` | `YES` | NULL | `NO` | NO | Polymorphic Eloquent model class name of the target entity. |
| `audit_logs` | `auditable_id` | `bigint unsigned` | `YES` | NULL | `NO` | NO | Primary key of the target auditable model. |
| `audit_logs` | `old_values` | `json` | `YES` | NULL | `NO` | NO | JSON snapshot of model attributes before the mutation. |
| `audit_logs` | `new_values` | `json` | `YES` | NULL | `NO` | NO | JSON snapshot of model attributes after the mutation. |
| `audit_logs` | `ip_address` | `varchar(45)` | `YES` | NULL | `NO` | NO | Originating IPv4 or IPv6 address of the client. |
| `audit_logs` | `user_agent` | `text` | `YES` | NULL | `NO` | NO | Browser and device user agent string. |
| `audit_logs` | `session_id` | `varchar(255)` | `YES` | NULL | `NO` | NO | Stores relational attribute `session_id` for `audit_logs` records. |
| `audit_logs` | `created_at` | `timestamp` | `YES` | NULL | `NO` | NO | Timestamp recording when the record was initially created. |
| `audit_logs` | `updated_at` | `timestamp` | `YES` | NULL | `NO` | NO | Timestamp recording when the record was last modified. |
| `auth_verifications` | `id` | `bigint unsigned` | `NO` | NULL | `YES` | NO | Unique auto-incrementing primary identifier for the auth_verifications entity. |
| `auth_verifications` | `user_id` | `bigint unsigned` | `YES` | NULL | `NO` | `users.id` | Foreign key reference to `users.id`. |
| `auth_verifications` | `email` | `varchar(255)` | `NO` | NULL | `NO` | NO | Stores relational attribute `email` for `auth_verifications` records. |
| `auth_verifications` | `type` | `varchar(255)` | `NO` | NULL | `NO` | NO | Stores relational attribute `type` for `auth_verifications` records. |
| `auth_verifications` | `token` | `varchar(100)` | `YES` | NULL | `NO` | NO | Stores relational attribute `token` for `auth_verifications` records. |
| `auth_verifications` | `code` | `varchar(20)` | `YES` | NULL | `NO` | NO | Stores relational attribute `code` for `auth_verifications` records. |
| `auth_verifications` | `attempts` | `int unsigned` | `NO` | `0` | `NO` | NO | Stores relational attribute `attempts` for `auth_verifications` records. |
| `auth_verifications` | `last_sent_at` | `timestamp` | `YES` | NULL | `NO` | NO | Stores relational attribute `last_sent_at` for `auth_verifications` records. |
| `auth_verifications` | `expires_at` | `timestamp` | `YES` | NULL | `NO` | NO | Stores relational attribute `expires_at` for `auth_verifications` records. |
| `auth_verifications` | `verified_at` | `timestamp` | `YES` | NULL | `NO` | NO | Stores relational attribute `verified_at` for `auth_verifications` records. |
| `auth_verifications` | `used_at` | `timestamp` | `YES` | NULL | `NO` | NO | Stores relational attribute `used_at` for `auth_verifications` records. |
| `auth_verifications` | `ip_address` | `varchar(45)` | `YES` | NULL | `NO` | NO | Stores relational attribute `ip_address` for `auth_verifications` records. |
| `auth_verifications` | `created_at` | `timestamp` | `YES` | NULL | `NO` | NO | Timestamp recording when the record was initially created. |
| `auth_verifications` | `updated_at` | `timestamp` | `YES` | NULL | `NO` | NO | Timestamp recording when the record was last modified. |
| `cache` | `key` | `varchar(255)` | `NO` | NULL | `YES` | NO | Stores relational attribute `key` for `cache` records. |
| `cache` | `value` | `mediumtext` | `NO` | NULL | `NO` | NO | Stores relational attribute `value` for `cache` records. |
| `cache` | `expiration` | `int` | `NO` | NULL | `NO` | NO | Stores relational attribute `expiration` for `cache` records. |
| `cache_locks` | `key` | `varchar(255)` | `NO` | NULL | `YES` | NO | Stores relational attribute `key` for `cache_locks` records. |
| `cache_locks` | `owner` | `varchar(255)` | `NO` | NULL | `NO` | NO | Stores relational attribute `owner` for `cache_locks` records. |
| `cache_locks` | `expiration` | `int` | `NO` | NULL | `NO` | NO | Stores relational attribute `expiration` for `cache_locks` records. |
| `campuses` | `id` | `bigint unsigned` | `NO` | NULL | `YES` | NO | Unique auto-incrementing primary identifier for the campuses entity. |
| `campuses` | `name` | `varchar(255)` | `NO` | NULL | `NO` | NO | Stores relational attribute `name` for `campuses` records. |
| `campuses` | `short_code` | `varchar(50)` | `NO` | NULL | `NO` | NO | Stores relational attribute `short_code` for `campuses` records. |
| `campuses` | `city` | `varchar(255)` | `YES` | NULL | `NO` | NO | Stores relational attribute `city` for `campuses` records. |
| `campuses` | `region` | `varchar(255)` | `YES` | NULL | `NO` | NO | Stores relational attribute `region` for `campuses` records. |
| `campuses` | `address` | `text` | `YES` | NULL | `NO` | NO | Stores relational attribute `address` for `campuses` records. |
| `campuses` | `phone` | `varchar(255)` | `YES` | NULL | `NO` | NO | Stores relational attribute `phone` for `campuses` records. |
| `campuses` | `email` | `varchar(255)` | `YES` | NULL | `NO` | NO | Stores relational attribute `email` for `campuses` records. |
| `campuses` | `is_active` | `tinyint(1)` | `NO` | `1` | `NO` | NO | Boolean status flag indicating whether this entity is currently active in the system. |
| `campuses` | `created_at` | `timestamp` | `YES` | NULL | `NO` | NO | Timestamp recording when the record was initially created. |
| `campuses` | `updated_at` | `timestamp` | `YES` | NULL | `NO` | NO | Timestamp recording when the record was last modified. |
| `categories` | `id` | `bigint unsigned` | `NO` | NULL | `YES` | NO | Unique auto-incrementing primary identifier for the categories entity. |
| `categories` | `name` | `varchar(255)` | `NO` | NULL | `NO` | NO | Stores relational attribute `name` for `categories` records. |
| `categories` | `name_am` | `varchar(255)` | `YES` | NULL | `NO` | NO | Stores relational attribute `name_am` for `categories` records. |
| `categories` | `icon_slug` | `varchar(255)` | `YES` | NULL | `NO` | NO | Stores relational attribute `icon_slug` for `categories` records. |
| `categories` | `sort_order` | `int` | `NO` | `0` | `NO` | NO | Stores relational attribute `sort_order` for `categories` records. |
| `categories` | `is_active` | `tinyint(1)` | `NO` | `1` | `NO` | NO | Boolean status flag indicating whether this entity is currently active in the system. |
| `categories` | `created_at` | `timestamp` | `YES` | NULL | `NO` | NO | Timestamp recording when the record was initially created. |
| `categories` | `updated_at` | `timestamp` | `YES` | NULL | `NO` | NO | Timestamp recording when the record was last modified. |
| `claim_evidence` | `id` | `bigint unsigned` | `NO` | NULL | `YES` | NO | Unique auto-incrementing primary identifier for the claim_evidence entity. |
| `claim_evidence` | `claim_id` | `bigint unsigned` | `NO` | NULL | `NO` | `claims.id` | Foreign key reference to `claims.id`. |
| `claim_evidence` | `uploaded_by` | `bigint unsigned` | `NO` | NULL | `NO` | `users.id` | Foreign key reference to `users.id`. |
| `claim_evidence` | `evidence_type` | `varchar(255)` | `NO` | NULL | `NO` | NO | Stores relational attribute `evidence_type` for `claim_evidence` records. |
| `claim_evidence` | `path` | `varchar(255)` | `NO` | NULL | `NO` | NO | Stores relational attribute `path` for `claim_evidence` records. |
| `claim_evidence` | `original_name` | `varchar(255)` | `YES` | NULL | `NO` | NO | Stores relational attribute `original_name` for `claim_evidence` records. |
| `claim_evidence` | `mime_type` | `varchar(100)` | `YES` | NULL | `NO` | NO | Stores relational attribute `mime_type` for `claim_evidence` records. |
| `claim_evidence` | `size_bytes` | `bigint unsigned` | `YES` | NULL | `NO` | NO | Stores relational attribute `size_bytes` for `claim_evidence` records. |
| `claim_evidence` | `description` | `text` | `YES` | NULL | `NO` | NO | Stores relational attribute `description` for `claim_evidence` records. |
| `claim_evidence` | `uploaded_at` | `timestamp` | `NO` | `CURRENT_TIMESTAMP` | `NO` | NO | Stores relational attribute `uploaded_at` for `claim_evidence` records. |
| `claim_evidence` | `created_at` | `timestamp` | `YES` | NULL | `NO` | NO | Timestamp recording when the record was initially created. |
| `claim_evidence` | `updated_at` | `timestamp` | `YES` | NULL | `NO` | NO | Timestamp recording when the record was last modified. |
| `claim_status_histories` | `id` | `bigint unsigned` | `NO` | NULL | `YES` | NO | Unique auto-incrementing primary identifier for the claim_status_histories entity. |
| `claim_status_histories` | `claim_id` | `bigint unsigned` | `NO` | NULL | `NO` | `claims.id` | Foreign key reference to `claims.id`. |
| `claim_status_histories` | `changed_by` | `bigint unsigned` | `YES` | NULL | `NO` | `users.id` | Foreign key reference to `users.id`. |
| `claim_status_histories` | `from_status` | `varchar(255)` | `YES` | NULL | `NO` | NO | Stores relational attribute `from_status` for `claim_status_histories` records. |
| `claim_status_histories` | `to_status` | `varchar(255)` | `NO` | NULL | `NO` | NO | Stores relational attribute `to_status` for `claim_status_histories` records. |
| `claim_status_histories` | `changed_by_role` | `varchar(255)` | `YES` | NULL | `NO` | NO | Stores relational attribute `changed_by_role` for `claim_status_histories` records. |
| `claim_status_histories` | `was_auto_rejected` | `tinyint(1)` | `NO` | `0` | `NO` | NO | Stores relational attribute `was_auto_rejected` for `claim_status_histories` records. |
| `claim_status_histories` | `note` | `text` | `YES` | NULL | `NO` | NO | Stores relational attribute `note` for `claim_status_histories` records. |
| `claim_status_histories` | `ip_address` | `varchar(45)` | `YES` | NULL | `NO` | NO | Stores relational attribute `ip_address` for `claim_status_histories` records. |
| `claim_status_histories` | `created_at` | `timestamp` | `YES` | NULL | `NO` | NO | Timestamp recording when the record was initially created. |
| `claims` | `id` | `bigint unsigned` | `NO` | NULL | `YES` | NO | Unique auto-incrementing primary identifier for the claims entity. |
| `claims` | `item_id` | `bigint unsigned` | `NO` | NULL | `NO` | `items.id` | Target found item being claimed by the user. |
| `claims` | `claimant_id` | `bigint unsigned` | `NO` | NULL | `NO` | `users.id` | User asserting ownership of the target item. |
| `claims` | `explanation` | `text` | `YES` | NULL | `NO` | NO | Stores relational attribute `explanation` for `claims` records. |
| `claims` | `status` | `varchar(255)` | `NO` | `pending` | `NO` | NO | Claim review lifecycle: `submitted`, `under_review`, `approved`, `rejected`, `appealed`. |
| `claims` | `reviewed_by` | `bigint unsigned` | `YES` | NULL | `NO` | `users.id` | Foreign key reference to `users.id`. |
| `claims` | `review_note` | `text` | `YES` | NULL | `NO` | NO | Stores relational attribute `review_note` for `claims` records. |
| `claims` | `reviewed_at` | `timestamp` | `YES` | NULL | `NO` | NO | Timestamp when the claim was approved or rejected. |
| `claims` | `auto_rejected` | `tinyint(1)` | `NO` | `0` | `NO` | NO | Stores relational attribute `auto_rejected` for `claims` records. |
| `claims` | `ip_address` | `varchar(45)` | `YES` | NULL | `NO` | NO | Stores relational attribute `ip_address` for `claims` records. |
| `claims` | `created_at` | `timestamp` | `YES` | NULL | `NO` | NO | Timestamp recording when the record was initially created. |
| `claims` | `updated_at` | `timestamp` | `YES` | NULL | `NO` | NO | Timestamp recording when the record was last modified. |
| `custody_events` | `id` | `bigint unsigned` | `NO` | NULL | `YES` | NO | Unique auto-incrementing primary identifier for the custody_events entity. |
| `custody_events` | `item_id` | `bigint unsigned` | `NO` | NULL | `NO` | `items.id` | Foreign key reference to `items.id`. |
| `custody_events` | `actor_id` | `bigint unsigned` | `NO` | NULL | `NO` | `users.id` | Foreign key reference to `users.id`. |
| `custody_events` | `storage_location_id` | `bigint unsigned` | `YES` | NULL | `NO` | `storage_locations.id` | Foreign key reference to `storage_locations.id`. |
| `custody_events` | `event_type` | `varchar(255)` | `NO` | NULL | `NO` | NO | Custody transition type: `received`, `transferred`, `relocated`, `released`, `disposed`. |
| `custody_events` | `condition` | `varchar(255)` | `YES` | NULL | `NO` | NO | Stores relational attribute `condition` for `custody_events` records. |
| `custody_events` | `notes` | `text` | `YES` | NULL | `NO` | NO | Stores relational attribute `notes` for `custody_events` records. |
| `custody_events` | `reference_photo` | `varchar(255)` | `YES` | NULL | `NO` | NO | Stores relational attribute `reference_photo` for `custody_events` records. |
| `custody_events` | `created_at` | `timestamp` | `YES` | NULL | `NO` | NO | Timestamp recording when the record was initially created. |
| `failed_jobs` | `id` | `bigint unsigned` | `NO` | NULL | `YES` | NO | Unique auto-incrementing primary identifier for the failed_jobs entity. |
| `failed_jobs` | `uuid` | `varchar(255)` | `NO` | NULL | `NO` | NO | Stores relational attribute `uuid` for `failed_jobs` records. |
| `failed_jobs` | `connection` | `text` | `NO` | NULL | `NO` | NO | Stores relational attribute `connection` for `failed_jobs` records. |
| `failed_jobs` | `queue` | `text` | `NO` | NULL | `NO` | NO | Stores relational attribute `queue` for `failed_jobs` records. |
| `failed_jobs` | `payload` | `longtext` | `NO` | NULL | `NO` | NO | Stores relational attribute `payload` for `failed_jobs` records. |
| `failed_jobs` | `exception` | `longtext` | `NO` | NULL | `NO` | NO | Stores relational attribute `exception` for `failed_jobs` records. |
| `failed_jobs` | `failed_at` | `timestamp` | `NO` | `CURRENT_TIMESTAMP` | `NO` | NO | Stores relational attribute `failed_at` for `failed_jobs` records. |
| `item_photos` | `id` | `bigint unsigned` | `NO` | NULL | `YES` | NO | Unique auto-incrementing primary identifier for the item_photos entity. |
| `item_photos` | `item_id` | `bigint unsigned` | `NO` | NULL | `NO` | `items.id` | Foreign key reference to `items.id`. |
| `item_photos` | `path` | `varchar(255)` | `NO` | NULL | `NO` | NO | Stores relational attribute `path` for `item_photos` records. |
| `item_photos` | `original_name` | `varchar(255)` | `YES` | NULL | `NO` | NO | Stores relational attribute `original_name` for `item_photos` records. |
| `item_photos` | `mime_type` | `varchar(100)` | `YES` | NULL | `NO` | NO | MIME content type of the image (e.g. image/jpeg, image/png). |
| `item_photos` | `size_bytes` | `bigint unsigned` | `YES` | NULL | `NO` | NO | Stores relational attribute `size_bytes` for `item_photos` records. |
| `item_photos` | `width_px` | `int unsigned` | `YES` | NULL | `NO` | NO | Stores relational attribute `width_px` for `item_photos` records. |
| `item_photos` | `height_px` | `int unsigned` | `YES` | NULL | `NO` | NO | Stores relational attribute `height_px` for `item_photos` records. |
| `item_photos` | `is_primary` | `tinyint(1)` | `NO` | `0` | `NO` | NO | Boolean indicating if this image is used as the catalogue thumbnail. |
| `item_photos` | `uploaded_at` | `timestamp` | `NO` | `CURRENT_TIMESTAMP` | `NO` | NO | Stores relational attribute `uploaded_at` for `item_photos` records. |
| `item_photos` | `created_at` | `timestamp` | `YES` | NULL | `NO` | NO | Timestamp recording when the record was initially created. |
| `item_photos` | `updated_at` | `timestamp` | `YES` | NULL | `NO` | NO | Timestamp recording when the record was last modified. |
| `item_status_histories` | `id` | `bigint unsigned` | `NO` | NULL | `YES` | NO | Unique auto-incrementing primary identifier for the item_status_histories entity. |
| `item_status_histories` | `item_id` | `bigint unsigned` | `NO` | NULL | `NO` | `items.id` | Foreign key reference to `items.id`. |
| `item_status_histories` | `changed_by` | `bigint unsigned` | `YES` | NULL | `NO` | `users.id` | Foreign key reference to `users.id`. |
| `item_status_histories` | `from_status` | `varchar(255)` | `YES` | NULL | `NO` | NO | Stores relational attribute `from_status` for `item_status_histories` records. |
| `item_status_histories` | `to_status` | `varchar(255)` | `NO` | NULL | `NO` | NO | Stores relational attribute `to_status` for `item_status_histories` records. |
| `item_status_histories` | `changed_by_role` | `varchar(255)` | `YES` | NULL | `NO` | NO | Stores relational attribute `changed_by_role` for `item_status_histories` records. |
| `item_status_histories` | `note` | `text` | `YES` | NULL | `NO` | NO | Stores relational attribute `note` for `item_status_histories` records. |
| `item_status_histories` | `ip_address` | `varchar(45)` | `YES` | NULL | `NO` | NO | Stores relational attribute `ip_address` for `item_status_histories` records. |
| `item_status_histories` | `created_at` | `timestamp` | `YES` | NULL | `NO` | NO | Timestamp recording when the record was initially created. |
| `item_tags` | `id` | `bigint unsigned` | `NO` | NULL | `YES` | NO | Unique auto-incrementing primary identifier for the item_tags entity. |
| `item_tags` | `item_id` | `bigint unsigned` | `NO` | NULL | `NO` | `items.id` | Foreign key reference to `items.id`. |
| `item_tags` | `tag` | `varchar(255)` | `NO` | NULL | `NO` | NO | Extracted keyword or descriptive label for search and NLP matching. |
| `item_tags` | `created_at` | `timestamp` | `YES` | NULL | `NO` | NO | Timestamp recording when the record was initially created. |
| `item_views` | `id` | `bigint unsigned` | `NO` | NULL | `YES` | NO | Unique auto-incrementing primary identifier for the item_views entity. |
| `item_views` | `item_id` | `bigint unsigned` | `NO` | NULL | `NO` | `items.id` | Foreign key reference to `items.id`. |
| `item_views` | `user_id` | `bigint unsigned` | `YES` | NULL | `NO` | `users.id` | Foreign key reference to `users.id`. |
| `item_views` | `ip_address` | `varchar(45)` | `YES` | NULL | `NO` | NO | Stores relational attribute `ip_address` for `item_views` records. |
| `item_views` | `user_agent` | `text` | `YES` | NULL | `NO` | NO | Stores relational attribute `user_agent` for `item_views` records. |
| `item_views` | `viewed_at` | `timestamp` | `NO` | `CURRENT_TIMESTAMP` | `NO` | NO | Stores relational attribute `viewed_at` for `item_views` records. |
| `item_views` | `created_at` | `timestamp` | `YES` | NULL | `NO` | NO | Timestamp recording when the record was initially created. |
| `item_views` | `updated_at` | `timestamp` | `YES` | NULL | `NO` | NO | Timestamp recording when the record was last modified. |
| `items` | `id` | `bigint unsigned` | `NO` | NULL | `YES` | NO | Unique auto-incrementing primary identifier for the items entity. |
| `items` | `reference_code` | `varchar(255)` | `NO` | NULL | `NO` | NO | Public tracking reference code (e.g. `LOST-2026-0001` or `FND-2026-0001`). |
| `items` | `reporter_id` | `bigint unsigned` | `NO` | NULL | `NO` | `users.id` | Foreign key reference to `users.id`. |
| `items` | `campus_id` | `bigint unsigned` | `NO` | NULL | `NO` | `campuses.id` | Campus facility where the item was lost or found. |
| `items` | `type` | `varchar(255)` | `NO` | NULL | `NO` | NO | Discriminated item type: `lost` (reported by loser) or `found` (reported by finder). |
| `items` | `title` | `varchar(255)` | `NO` | NULL | `NO` | NO | Concise summary title of the item (e.g. 'HP Laptop 15-inch Grey'). |
| `items` | `description` | `text` | `YES` | NULL | `NO` | NO | Detailed narrative description of the item, features, and condition. |
| `items` | `category_id` | `bigint unsigned` | `NO` | NULL | `NO` | `categories.id` | Taxonomical classification in `categories`. |
| `items` | `location_id` | `bigint unsigned` | `YES` | NULL | `NO` | `locations.id` | Specific building, hall, or compound location where the event occurred. |
| `items` | `location_detail` | `text` | `YES` | NULL | `NO` | NO | Stores relational attribute `location_detail` for `items` records. |
| `items` | `brand` | `varchar(255)` | `YES` | NULL | `NO` | NO | Stores relational attribute `brand` for `items` records. |
| `items` | `color` | `varchar(255)` | `YES` | NULL | `NO` | NO | Stores relational attribute `color` for `items` records. |
| `items` | `serial_number` | `varchar(255)` | `YES` | NULL | `NO` | NO | Stores relational attribute `serial_number` for `items` records. |
| `items` | `incident_date` | `date` | `YES` | NULL | `NO` | NO | Stores relational attribute `incident_date` for `items` records. |
| `items` | `incident_time` | `time` | `YES` | NULL | `NO` | NO | Stores relational attribute `incident_time` for `items` records. |
| `items` | `status` | `varchar(255)` | `NO` | `reported` | `NO` | NO | Current lifecycle state: `reported`, `under_review`, `claimed`, `returned`, `disposed`, `withdrawn`. |
| `items` | `held_at` | `varchar(255)` | `YES` | NULL | `NO` | NO | Stores relational attribute `held_at` for `items` records. |
| `items` | `estimated_value` | `decimal(10,2)` | `YES` | NULL | `NO` | NO | Stores relational attribute `estimated_value` for `items` records. |
| `items` | `is_high_value` | `tinyint(1)` | `NO` | `0` | `NO` | NO | Stores relational attribute `is_high_value` for `items` records. |
| `items` | `is_deleted` | `tinyint(1)` | `NO` | `0` | `NO` | NO | Stores relational attribute `is_deleted` for `items` records. |
| `items` | `deleted_by` | `bigint unsigned` | `YES` | NULL | `NO` | `users.id` | Foreign key reference to `users.id`. |
| `items` | `deleted_at` | `timestamp` | `YES` | NULL | `NO` | NO | Soft-delete timestamp; null indicates an active record. |
| `items` | `last_activity_at` | `timestamp` | `YES` | NULL | `NO` | NO | Stores relational attribute `last_activity_at` for `items` records. |
| `items` | `expires_at` | `timestamp` | `YES` | NULL | `NO` | NO | Stores relational attribute `expires_at` for `items` records. |
| `items` | `created_at` | `timestamp` | `YES` | NULL | `NO` | NO | Timestamp recording when the record was initially created. |
| `items` | `updated_at` | `timestamp` | `YES` | NULL | `NO` | NO | Timestamp recording when the record was last modified. |
| `job_batches` | `id` | `varchar(255)` | `NO` | NULL | `YES` | NO | Unique auto-incrementing primary identifier for the job_batches entity. |
| `job_batches` | `name` | `varchar(255)` | `NO` | NULL | `NO` | NO | Stores relational attribute `name` for `job_batches` records. |
| `job_batches` | `total_jobs` | `int` | `NO` | NULL | `NO` | NO | Stores relational attribute `total_jobs` for `job_batches` records. |
| `job_batches` | `pending_jobs` | `int` | `NO` | NULL | `NO` | NO | Stores relational attribute `pending_jobs` for `job_batches` records. |
| `job_batches` | `failed_jobs` | `int` | `NO` | NULL | `NO` | NO | Stores relational attribute `failed_jobs` for `job_batches` records. |
| `job_batches` | `failed_job_ids` | `longtext` | `NO` | NULL | `NO` | NO | Stores relational attribute `failed_job_ids` for `job_batches` records. |
| `job_batches` | `options` | `mediumtext` | `YES` | NULL | `NO` | NO | Stores relational attribute `options` for `job_batches` records. |
| `job_batches` | `cancelled_at` | `int` | `YES` | NULL | `NO` | NO | Stores relational attribute `cancelled_at` for `job_batches` records. |
| `job_batches` | `created_at` | `int` | `NO` | NULL | `NO` | NO | Timestamp recording when the record was initially created. |
| `job_batches` | `finished_at` | `int` | `YES` | NULL | `NO` | NO | Stores relational attribute `finished_at` for `job_batches` records. |
| `jobs` | `id` | `bigint unsigned` | `NO` | NULL | `YES` | NO | Unique auto-incrementing primary identifier for the jobs entity. |
| `jobs` | `queue` | `varchar(255)` | `NO` | NULL | `NO` | NO | Stores relational attribute `queue` for `jobs` records. |
| `jobs` | `payload` | `longtext` | `NO` | NULL | `NO` | NO | Stores relational attribute `payload` for `jobs` records. |
| `jobs` | `attempts` | `tinyint unsigned` | `NO` | NULL | `NO` | NO | Stores relational attribute `attempts` for `jobs` records. |
| `jobs` | `reserved_at` | `int unsigned` | `YES` | NULL | `NO` | NO | Stores relational attribute `reserved_at` for `jobs` records. |
| `jobs` | `available_at` | `int unsigned` | `NO` | NULL | `NO` | NO | Stores relational attribute `available_at` for `jobs` records. |
| `jobs` | `created_at` | `int unsigned` | `NO` | NULL | `NO` | NO | Timestamp recording when the record was initially created. |
| `locations` | `id` | `bigint unsigned` | `NO` | NULL | `YES` | NO | Unique auto-incrementing primary identifier for the locations entity. |
| `locations` | `campus_id` | `bigint unsigned` | `NO` | NULL | `NO` | `campuses.id` | Foreign key reference to `campuses.id`. |
| `locations` | `name` | `varchar(255)` | `NO` | NULL | `NO` | NO | Stores relational attribute `name` for `locations` records. |
| `locations` | `name_am` | `varchar(255)` | `YES` | NULL | `NO` | NO | Stores relational attribute `name_am` for `locations` records. |
| `locations` | `code` | `varchar(50)` | `NO` | NULL | `NO` | NO | Stores relational attribute `code` for `locations` records. |
| `locations` | `building` | `varchar(255)` | `YES` | NULL | `NO` | NO | Stores relational attribute `building` for `locations` records. |
| `locations` | `zone` | `varchar(255)` | `YES` | NULL | `NO` | NO | Stores relational attribute `zone` for `locations` records. |
| `locations` | `is_active` | `tinyint(1)` | `NO` | `1` | `NO` | NO | Boolean status flag indicating whether this entity is currently active in the system. |
| `locations` | `sort_order` | `int` | `NO` | `0` | `NO` | NO | Stores relational attribute `sort_order` for `locations` records. |
| `locations` | `created_at` | `timestamp` | `YES` | NULL | `NO` | NO | Timestamp recording when the record was initially created. |
| `locations` | `updated_at` | `timestamp` | `YES` | NULL | `NO` | NO | Timestamp recording when the record was last modified. |
| `match_suggestions` | `id` | `bigint unsigned` | `NO` | NULL | `YES` | NO | Unique auto-incrementing primary identifier for the match_suggestions entity. |
| `match_suggestions` | `found_item_id` | `bigint unsigned` | `NO` | NULL | `NO` | `items.id` | Found item report candidate. |
| `match_suggestions` | `lost_item_id` | `bigint unsigned` | `NO` | NULL | `NO` | `items.id` | Lost item report candidate. |
| `match_suggestions` | `score` | `decimal(5,2)` | `NO` | NULL | `NO` | NO | Stores relational attribute `score` for `match_suggestions` records. |
| `match_suggestions` | `category_score` | `decimal(5,2)` | `YES` | NULL | `NO` | NO | Stores relational attribute `category_score` for `match_suggestions` records. |
| `match_suggestions` | `text_score` | `decimal(5,2)` | `YES` | NULL | `NO` | NO | Stores relational attribute `text_score` for `match_suggestions` records. |
| `match_suggestions` | `location_score` | `decimal(5,2)` | `YES` | NULL | `NO` | NO | Stores relational attribute `location_score` for `match_suggestions` records. |
| `match_suggestions` | `algorithm_version` | `varchar(50)` | `YES` | NULL | `NO` | NO | Stores relational attribute `algorithm_version` for `match_suggestions` records. |
| `match_suggestions` | `status` | `varchar(255)` | `NO` | `pending` | `NO` | NO | Suggestion review state: `suggested`, `confirmed`, `dismissed`, `resolved`. |
| `match_suggestions` | `notified_at` | `timestamp` | `YES` | NULL | `NO` | NO | Stores relational attribute `notified_at` for `match_suggestions` records. |
| `match_suggestions` | `reviewed_at` | `timestamp` | `YES` | NULL | `NO` | NO | Stores relational attribute `reviewed_at` for `match_suggestions` records. |
| `match_suggestions` | `reviewed_by` | `bigint unsigned` | `YES` | NULL | `NO` | `users.id` | Foreign key reference to `users.id`. |
| `match_suggestions` | `created_at` | `timestamp` | `YES` | NULL | `NO` | NO | Timestamp recording when the record was initially created. |
| `migrations` | `id` | `int unsigned` | `NO` | NULL | `YES` | NO | Unique auto-incrementing primary identifier for the migrations entity. |
| `migrations` | `migration` | `varchar(255)` | `NO` | NULL | `NO` | NO | Stores relational attribute `migration` for `migrations` records. |
| `migrations` | `batch` | `int` | `NO` | NULL | `NO` | NO | Stores relational attribute `batch` for `migrations` records. |
| `notification_preferences` | `id` | `bigint unsigned` | `NO` | NULL | `YES` | NO | Unique auto-incrementing primary identifier for the notification_preferences entity. |
| `notification_preferences` | `user_id` | `bigint unsigned` | `NO` | NULL | `NO` | `users.id` | Foreign key reference to `users.id`. |
| `notification_preferences` | `email_on_report_submitted` | `tinyint(1)` | `NO` | `1` | `NO` | NO | Stores relational attribute `email_on_report_submitted` for `notification_preferences` records. |
| `notification_preferences` | `email_on_match_found` | `tinyint(1)` | `NO` | `1` | `NO` | NO | Stores relational attribute `email_on_match_found` for `notification_preferences` records. |
| `notification_preferences` | `email_on_claim_received` | `tinyint(1)` | `NO` | `1` | `NO` | NO | Stores relational attribute `email_on_claim_received` for `notification_preferences` records. |
| `notification_preferences` | `email_on_claim_decided` | `tinyint(1)` | `NO` | `1` | `NO` | NO | Stores relational attribute `email_on_claim_decided` for `notification_preferences` records. |
| `notification_preferences` | `email_on_item_returned` | `tinyint(1)` | `NO` | `1` | `NO` | NO | Stores relational attribute `email_on_item_returned` for `notification_preferences` records. |
| `notification_preferences` | `email_on_expiry_warning` | `tinyint(1)` | `NO` | `1` | `NO` | NO | Stores relational attribute `email_on_expiry_warning` for `notification_preferences` records. |
| `notification_preferences` | `email_on_item_expired` | `tinyint(1)` | `NO` | `1` | `NO` | NO | Stores relational attribute `email_on_item_expired` for `notification_preferences` records. |
| `notification_preferences` | `email_on_system_announcements` | `tinyint(1)` | `NO` | `1` | `NO` | NO | Stores relational attribute `email_on_system_announcements` for `notification_preferences` records. |
| `notification_preferences` | `created_at` | `timestamp` | `YES` | NULL | `NO` | NO | Timestamp recording when the record was initially created. |
| `notification_preferences` | `updated_at` | `timestamp` | `YES` | NULL | `NO` | NO | Timestamp recording when the record was last modified. |
| `notifications` | `id` | `bigint unsigned` | `NO` | NULL | `YES` | NO | Unique auto-incrementing primary identifier for the notifications entity. |
| `notifications` | `user_id` | `bigint unsigned` | `NO` | NULL | `NO` | `users.id` | Foreign key reference to `users.id`. |
| `notifications` | `type` | `varchar(255)` | `NO` | NULL | `NO` | NO | Stores relational attribute `type` for `notifications` records. |
| `notifications` | `channel` | `varchar(255)` | `NO` | `in_app` | `NO` | NO | Stores relational attribute `channel` for `notifications` records. |
| `notifications` | `data` | `json` | `NO` | NULL | `NO` | NO | Stores relational attribute `data` for `notifications` records. |
| `notifications` | `is_read` | `tinyint(1)` | `NO` | `0` | `NO` | NO | Stores relational attribute `is_read` for `notifications` records. |
| `notifications` | `read_at` | `timestamp` | `YES` | NULL | `NO` | NO | Stores relational attribute `read_at` for `notifications` records. |
| `notifications` | `created_at` | `timestamp` | `YES` | NULL | `NO` | NO | Timestamp recording when the record was initially created. |
| `notifications` | `updated_at` | `timestamp` | `YES` | NULL | `NO` | NO | Timestamp recording when the record was last modified. |
| `organizational_unit_type_relations` | `id` | `bigint unsigned` | `NO` | NULL | `YES` | NO | Unique auto-incrementing primary identifier for the organizational_unit_type_relations entity. |
| `organizational_unit_type_relations` | `child_type_id` | `bigint unsigned` | `NO` | NULL | `NO` | `organizational_unit_types.id` | Foreign key reference to `organizational_unit_types.id`. |
| `organizational_unit_type_relations` | `parent_type_id` | `bigint unsigned` | `NO` | NULL | `NO` | `organizational_unit_types.id` | Foreign key reference to `organizational_unit_types.id`. |
| `organizational_unit_type_relations` | `is_active` | `tinyint(1)` | `NO` | `1` | `NO` | NO | Boolean status flag indicating whether this entity is currently active in the system. |
| `organizational_unit_type_relations` | `created_at` | `timestamp` | `YES` | NULL | `NO` | NO | Timestamp recording when the record was initially created. |
| `organizational_unit_type_relations` | `updated_at` | `timestamp` | `YES` | NULL | `NO` | NO | Timestamp recording when the record was last modified. |
| `organizational_unit_types` | `id` | `bigint unsigned` | `NO` | NULL | `YES` | NO | Unique auto-incrementing primary identifier for the organizational_unit_types entity. |
| `organizational_unit_types` | `code` | `varchar(255)` | `NO` | NULL | `NO` | NO | Stores relational attribute `code` for `organizational_unit_types` records. |
| `organizational_unit_types` | `name` | `varchar(255)` | `NO` | NULL | `NO` | NO | Stores relational attribute `name` for `organizational_unit_types` records. |
| `organizational_unit_types` | `name_am` | `varchar(255)` | `YES` | NULL | `NO` | NO | Stores relational attribute `name_am` for `organizational_unit_types` records. |
| `organizational_unit_types` | `description` | `text` | `YES` | NULL | `NO` | NO | Stores relational attribute `description` for `organizational_unit_types` records. |
| `organizational_unit_types` | `is_root` | `tinyint(1)` | `NO` | `0` | `NO` | NO | Stores relational attribute `is_root` for `organizational_unit_types` records. |
| `organizational_unit_types` | `is_active` | `tinyint(1)` | `NO` | `1` | `NO` | NO | Boolean status flag indicating whether this entity is currently active in the system. |
| `organizational_unit_types` | `created_at` | `timestamp` | `YES` | NULL | `NO` | NO | Timestamp recording when the record was initially created. |
| `organizational_unit_types` | `updated_at` | `timestamp` | `YES` | NULL | `NO` | NO | Timestamp recording when the record was last modified. |
| `organizational_units` | `id` | `bigint unsigned` | `NO` | NULL | `YES` | NO | Unique auto-incrementing primary identifier for the organizational_units entity. |
| `organizational_units` | `campus_id` | `bigint unsigned` | `NO` | NULL | `NO` | `campuses.id` | Foreign key reference to `campuses.id`. |
| `organizational_units` | `parent_id` | `bigint unsigned` | `YES` | NULL | `NO` | `organizational_units.id` | Foreign key reference to `organizational_units.id`. |
| `organizational_units` | `type_id` | `bigint unsigned` | `NO` | NULL | `NO` | `organizational_unit_types.id` | Foreign key reference to `organizational_unit_types.id`. |
| `organizational_units` | `name` | `varchar(255)` | `NO` | NULL | `NO` | NO | Stores relational attribute `name` for `organizational_units` records. |
| `organizational_units` | `name_am` | `varchar(255)` | `YES` | NULL | `NO` | NO | Stores relational attribute `name_am` for `organizational_units` records. |
| `organizational_units` | `short_code` | `varchar(50)` | `NO` | NULL | `NO` | NO | Stores relational attribute `short_code` for `organizational_units` records. |
| `organizational_units` | `description` | `text` | `YES` | NULL | `NO` | NO | Stores relational attribute `description` for `organizational_units` records. |
| `organizational_units` | `is_active` | `tinyint(1)` | `NO` | `1` | `NO` | NO | Boolean status flag indicating whether this entity is currently active in the system. |
| `organizational_units` | `created_at` | `timestamp` | `YES` | NULL | `NO` | NO | Timestamp recording when the record was initially created. |
| `organizational_units` | `updated_at` | `timestamp` | `YES` | NULL | `NO` | NO | Timestamp recording when the record was last modified. |
| `password_histories` | `id` | `bigint unsigned` | `NO` | NULL | `YES` | NO | Unique auto-incrementing primary identifier for the password_histories entity. |
| `password_histories` | `user_id` | `bigint unsigned` | `NO` | NULL | `NO` | `users.id` | Foreign key reference to `users.id`. |
| `password_histories` | `password_hash` | `varchar(255)` | `NO` | NULL | `NO` | NO | Stores relational attribute `password_hash` for `password_histories` records. |
| `password_histories` | `created_at` | `timestamp` | `YES` | NULL | `NO` | NO | Timestamp recording when the record was initially created. |
| `password_histories` | `updated_at` | `timestamp` | `YES` | NULL | `NO` | NO | Timestamp recording when the record was last modified. |
| `permission_groups` | `id` | `bigint unsigned` | `NO` | NULL | `YES` | NO | Unique auto-incrementing primary identifier for the permission_groups entity. |
| `permission_groups` | `name` | `varchar(255)` | `NO` | NULL | `NO` | NO | Stores relational attribute `name` for `permission_groups` records. |
| `permission_groups` | `display_name` | `varchar(255)` | `NO` | NULL | `NO` | NO | Stores relational attribute `display_name` for `permission_groups` records. |
| `permission_groups` | `display_name_am` | `varchar(255)` | `YES` | NULL | `NO` | NO | Stores relational attribute `display_name_am` for `permission_groups` records. |
| `permission_groups` | `description` | `text` | `YES` | NULL | `NO` | NO | Stores relational attribute `description` for `permission_groups` records. |
| `permission_groups` | `description_am` | `text` | `YES` | NULL | `NO` | NO | Stores relational attribute `description_am` for `permission_groups` records. |
| `permission_groups` | `is_system` | `tinyint(1)` | `NO` | `0` | `NO` | NO | Protected system flag; if true, prevents deletion or rename by administrators. |
| `permission_groups` | `is_active` | `tinyint(1)` | `NO` | `1` | `NO` | NO | Boolean status flag indicating whether this entity is currently active in the system. |
| `permission_groups` | `created_at` | `timestamp` | `YES` | NULL | `NO` | NO | Timestamp recording when the record was initially created. |
| `permission_groups` | `updated_at` | `timestamp` | `YES` | NULL | `NO` | NO | Timestamp recording when the record was last modified. |
| `permission_role` | `id` | `bigint unsigned` | `NO` | NULL | `YES` | NO | Unique auto-incrementing primary identifier for the permission_role entity. |
| `permission_role` | `role_id` | `bigint unsigned` | `NO` | NULL | `NO` | `roles.id` | Foreign key reference to `roles.id`. |
| `permission_role` | `permission_id` | `bigint unsigned` | `NO` | NULL | `NO` | `permissions.id` | Foreign key reference to `permissions.id`. |
| `permission_role` | `created_at` | `timestamp` | `YES` | NULL | `NO` | NO | Timestamp recording when the record was initially created. |
| `permission_role` | `updated_at` | `timestamp` | `YES` | NULL | `NO` | NO | Timestamp recording when the record was last modified. |
| `permission_user` | `id` | `bigint unsigned` | `NO` | NULL | `YES` | NO | Unique auto-incrementing primary identifier for the permission_user entity. |
| `permission_user` | `permission_id` | `bigint unsigned` | `NO` | NULL | `NO` | `permissions.id` | Foreign key reference to `permissions.id`. |
| `permission_user` | `user_id` | `bigint unsigned` | `NO` | NULL | `NO` | `users.id` | Foreign key reference to `users.id`. |
| `permission_user` | `created_at` | `timestamp` | `YES` | NULL | `NO` | NO | Timestamp recording when the record was initially created. |
| `permission_user` | `updated_at` | `timestamp` | `YES` | NULL | `NO` | NO | Timestamp recording when the record was last modified. |
| `permissions` | `id` | `bigint unsigned` | `NO` | NULL | `YES` | NO | Unique auto-incrementing primary identifier for the permissions entity. |
| `permissions` | `permission_group_id` | `bigint unsigned` | `YES` | NULL | `NO` | `permission_groups.id` | Foreign key reference to `permission_groups.id`. |
| `permissions` | `name` | `varchar(255)` | `NO` | NULL | `NO` | NO | Stores relational attribute `name` for `permissions` records. |
| `permissions` | `display_name` | `varchar(255)` | `NO` | NULL | `NO` | NO | Stores relational attribute `display_name` for `permissions` records. |
| `permissions` | `display_name_am` | `varchar(255)` | `YES` | NULL | `NO` | NO | Stores relational attribute `display_name_am` for `permissions` records. |
| `permissions` | `description` | `text` | `YES` | NULL | `NO` | NO | Stores relational attribute `description` for `permissions` records. |
| `permissions` | `description_am` | `text` | `YES` | NULL | `NO` | NO | Stores relational attribute `description_am` for `permissions` records. |
| `permissions` | `category` | `varchar(255)` | `YES` | NULL | `NO` | NO | Stores relational attribute `category` for `permissions` records. |
| `permissions` | `is_system` | `tinyint(1)` | `NO` | `0` | `NO` | NO | Protected system flag; if true, prevents deletion or rename by administrators. |
| `permissions` | `is_active` | `tinyint(1)` | `NO` | `1` | `NO` | NO | Boolean status flag indicating whether this entity is currently active in the system. |
| `permissions` | `created_at` | `timestamp` | `YES` | NULL | `NO` | NO | Timestamp recording when the record was initially created. |
| `permissions` | `updated_at` | `timestamp` | `YES` | NULL | `NO` | NO | Timestamp recording when the record was last modified. |
| `reports` | `id` | `bigint unsigned` | `NO` | NULL | `YES` | NO | Unique auto-incrementing primary identifier for the reports entity. |
| `reports` | `requested_by` | `bigint unsigned` | `NO` | NULL | `NO` | `users.id` | Foreign key reference to `users.id`. |
| `reports` | `report_type` | `varchar(255)` | `NO` | NULL | `NO` | NO | Stores relational attribute `report_type` for `reports` records. |
| `reports` | `filters` | `json` | `YES` | NULL | `NO` | NO | Stores relational attribute `filters` for `reports` records. |
| `reports` | `format` | `varchar(255)` | `NO` | `pdf` | `NO` | NO | Stores relational attribute `format` for `reports` records. |
| `reports` | `status` | `varchar(255)` | `NO` | `pending` | `NO` | NO | Stores relational attribute `status` for `reports` records. |
| `reports` | `file_path` | `varchar(255)` | `YES` | NULL | `NO` | NO | Stores relational attribute `file_path` for `reports` records. |
| `reports` | `file_size_bytes` | `bigint unsigned` | `YES` | NULL | `NO` | NO | Stores relational attribute `file_size_bytes` for `reports` records. |
| `reports` | `row_count` | `int unsigned` | `NO` | `0` | `NO` | NO | Stores relational attribute `row_count` for `reports` records. |
| `reports` | `error_message` | `text` | `YES` | NULL | `NO` | NO | Stores relational attribute `error_message` for `reports` records. |
| `reports` | `ready_at` | `timestamp` | `YES` | NULL | `NO` | NO | Stores relational attribute `ready_at` for `reports` records. |
| `reports` | `downloaded_at` | `timestamp` | `YES` | NULL | `NO` | NO | Stores relational attribute `downloaded_at` for `reports` records. |
| `reports` | `download_count` | `int unsigned` | `NO` | `0` | `NO` | NO | Stores relational attribute `download_count` for `reports` records. |
| `reports` | `expires_at` | `timestamp` | `YES` | NULL | `NO` | NO | Stores relational attribute `expires_at` for `reports` records. |
| `reports` | `created_at` | `timestamp` | `YES` | NULL | `NO` | NO | Timestamp recording when the record was initially created. |
| `reports` | `updated_at` | `timestamp` | `YES` | NULL | `NO` | NO | Timestamp recording when the record was last modified. |
| `return_documents` | `id` | `bigint unsigned` | `NO` | NULL | `YES` | NO | Unique auto-incrementing primary identifier for the return_documents entity. |
| `return_documents` | `return_id` | `bigint unsigned` | `NO` | NULL | `NO` | `returns.id` | Foreign key reference to `returns.id`. |
| `return_documents` | `document_type` | `varchar(255)` | `NO` | NULL | `NO` | NO | Stores relational attribute `document_type` for `return_documents` records. |
| `return_documents` | `path` | `varchar(255)` | `NO` | NULL | `NO` | NO | Stores relational attribute `path` for `return_documents` records. |
| `return_documents` | `original_name` | `varchar(255)` | `YES` | NULL | `NO` | NO | Stores relational attribute `original_name` for `return_documents` records. |
| `return_documents` | `mime_type` | `varchar(100)` | `YES` | NULL | `NO` | NO | Stores relational attribute `mime_type` for `return_documents` records. |
| `return_documents` | `size_bytes` | `bigint unsigned` | `YES` | NULL | `NO` | NO | Stores relational attribute `size_bytes` for `return_documents` records. |
| `return_documents` | `emailed_to_student` | `tinyint(1)` | `NO` | `0` | `NO` | NO | Stores relational attribute `emailed_to_student` for `return_documents` records. |
| `return_documents` | `emailed_at` | `timestamp` | `YES` | NULL | `NO` | NO | Stores relational attribute `emailed_at` for `return_documents` records. |
| `return_documents` | `generated_at` | `timestamp` | `NO` | `CURRENT_TIMESTAMP` | `NO` | NO | Stores relational attribute `generated_at` for `return_documents` records. |
| `return_documents` | `created_at` | `timestamp` | `YES` | NULL | `NO` | NO | Timestamp recording when the record was initially created. |
| `return_documents` | `updated_at` | `timestamp` | `YES` | NULL | `NO` | NO | Timestamp recording when the record was last modified. |
| `returns` | `id` | `bigint unsigned` | `NO` | NULL | `YES` | NO | Unique auto-incrementing primary identifier for the returns entity. |
| `returns` | `claim_id` | `bigint unsigned` | `NO` | NULL | `NO` | `claims.id` | Approved claim authorizing this physical item release. |
| `returns` | `returned_to` | `bigint unsigned` | `NO` | NULL | `NO` | `users.id` | Foreign key reference to `users.id`. |
| `returns` | `handed_over_by` | `bigint unsigned` | `NO` | NULL | `NO` | `users.id` | Foreign key reference to `users.id`. |
| `returns` | `storage_location_id` | `bigint unsigned` | `YES` | NULL | `NO` | `storage_locations.id` | Foreign key reference to `storage_locations.id`. |
| `returns` | `return_date` | `date` | `NO` | NULL | `NO` | NO | Stores relational attribute `return_date` for `returns` records. |
| `returns` | `return_time` | `time` | `YES` | NULL | `NO` | NO | Stores relational attribute `return_time` for `returns` records. |
| `returns` | `condition_on_return` | `text` | `YES` | NULL | `NO` | NO | Stores relational attribute `condition_on_return` for `returns` records. |
| `returns` | `notes` | `text` | `YES` | NULL | `NO` | NO | Stores relational attribute `notes` for `returns` records. |
| `returns` | `recipient_confirmed` | `tinyint(1)` | `NO` | `0` | `NO` | NO | Stores relational attribute `recipient_confirmed` for `returns` records. |
| `returns` | `confirmed_at` | `timestamp` | `YES` | NULL | `NO` | NO | Stores relational attribute `confirmed_at` for `returns` records. |
| `returns` | `confirmation_token` | `varchar(100)` | `YES` | NULL | `NO` | NO | Single-use 64-character cryptographic token for digital signature confirmation. |
| `returns` | `confirmation_token_expires_at` | `timestamp` | `YES` | NULL | `NO` | NO | Stores relational attribute `confirmation_token_expires_at` for `returns` records. |
| `returns` | `created_at` | `timestamp` | `YES` | NULL | `NO` | NO | Timestamp recording when the record was initially created. |
| `returns` | `updated_at` | `timestamp` | `YES` | NULL | `NO` | NO | Timestamp recording when the record was last modified. |
| `roles` | `id` | `bigint unsigned` | `NO` | NULL | `YES` | NO | Unique auto-incrementing primary identifier for the roles entity. |
| `roles` | `name` | `varchar(255)` | `NO` | NULL | `NO` | NO | Stores relational attribute `name` for `roles` records. |
| `roles` | `display_name` | `varchar(255)` | `NO` | NULL | `NO` | NO | Stores relational attribute `display_name` for `roles` records. |
| `roles` | `display_name_am` | `varchar(255)` | `YES` | NULL | `NO` | NO | Stores relational attribute `display_name_am` for `roles` records. |
| `roles` | `description` | `text` | `YES` | NULL | `NO` | NO | Stores relational attribute `description` for `roles` records. |
| `roles` | `description_am` | `text` | `YES` | NULL | `NO` | NO | Stores relational attribute `description_am` for `roles` records. |
| `roles` | `is_system` | `tinyint(1)` | `NO` | `0` | `NO` | NO | Protected system flag; if true, prevents deletion or rename by administrators. |
| `roles` | `is_active` | `tinyint(1)` | `NO` | `1` | `NO` | NO | Boolean status flag indicating whether this entity is currently active in the system. |
| `roles` | `created_at` | `timestamp` | `YES` | NULL | `NO` | NO | Timestamp recording when the record was initially created. |
| `roles` | `updated_at` | `timestamp` | `YES` | NULL | `NO` | NO | Timestamp recording when the record was last modified. |
| `search_logs` | `id` | `bigint unsigned` | `NO` | NULL | `YES` | NO | Unique auto-incrementing primary identifier for the search_logs entity. |
| `search_logs` | `user_id` | `bigint unsigned` | `YES` | NULL | `NO` | `users.id` | Foreign key reference to `users.id`. |
| `search_logs` | `query` | `varchar(255)` | `NO` | NULL | `NO` | NO | Stores relational attribute `query` for `search_logs` records. |
| `search_logs` | `category_id` | `bigint unsigned` | `YES` | NULL | `NO` | `categories.id` | Foreign key reference to `categories.id`. |
| `search_logs` | `campus_id` | `bigint unsigned` | `YES` | NULL | `NO` | `campuses.id` | Foreign key reference to `campuses.id`. |
| `search_logs` | `results_count` | `int unsigned` | `NO` | `0` | `NO` | NO | Stores relational attribute `results_count` for `search_logs` records. |
| `search_logs` | `found_match` | `tinyint(1)` | `NO` | `0` | `NO` | NO | Stores relational attribute `found_match` for `search_logs` records. |
| `search_logs` | `ip_address` | `varchar(45)` | `YES` | NULL | `NO` | NO | Stores relational attribute `ip_address` for `search_logs` records. |
| `search_logs` | `created_at` | `timestamp` | `YES` | NULL | `NO` | NO | Timestamp recording when the record was initially created. |
| `search_logs` | `updated_at` | `timestamp` | `YES` | NULL | `NO` | NO | Timestamp recording when the record was last modified. |
| `sessions` | `id` | `varchar(255)` | `NO` | NULL | `YES` | NO | Unique auto-incrementing primary identifier for the sessions entity. |
| `sessions` | `user_id` | `bigint unsigned` | `YES` | NULL | `NO` | `users.id` | Foreign key reference to `users.id`. |
| `sessions` | `ip_address` | `varchar(45)` | `YES` | NULL | `NO` | NO | Stores relational attribute `ip_address` for `sessions` records. |
| `sessions` | `user_agent` | `text` | `YES` | NULL | `NO` | NO | Stores relational attribute `user_agent` for `sessions` records. |
| `sessions` | `payload` | `longtext` | `NO` | NULL | `NO` | NO | Stores relational attribute `payload` for `sessions` records. |
| `sessions` | `last_activity` | `int` | `NO` | NULL | `NO` | NO | Stores relational attribute `last_activity` for `sessions` records. |
| `storage_locations` | `id` | `bigint unsigned` | `NO` | NULL | `YES` | NO | Unique auto-incrementing primary identifier for the storage_locations entity. |
| `storage_locations` | `campus_id` | `bigint unsigned` | `NO` | NULL | `NO` | `campuses.id` | Foreign key reference to `campuses.id`. |
| `storage_locations` | `name` | `varchar(255)` | `NO` | NULL | `NO` | NO | Stores relational attribute `name` for `storage_locations` records. |
| `storage_locations` | `code` | `varchar(50)` | `NO` | NULL | `NO` | NO | Stores relational attribute `code` for `storage_locations` records. |
| `storage_locations` | `description` | `text` | `YES` | NULL | `NO` | NO | Stores relational attribute `description` for `storage_locations` records. |
| `storage_locations` | `capacity` | `int` | `YES` | NULL | `NO` | NO | Stores relational attribute `capacity` for `storage_locations` records. |
| `storage_locations` | `is_active` | `tinyint(1)` | `NO` | `1` | `NO` | NO | Boolean status flag indicating whether this entity is currently active in the system. |
| `storage_locations` | `created_at` | `timestamp` | `YES` | NULL | `NO` | NO | Timestamp recording when the record was initially created. |
| `storage_locations` | `updated_at` | `timestamp` | `YES` | NULL | `NO` | NO | Timestamp recording when the record was last modified. |
| `system_announcements` | `id` | `bigint unsigned` | `NO` | NULL | `YES` | NO | Unique auto-incrementing primary identifier for the system_announcements entity. |
| `system_announcements` | `created_by` | `bigint unsigned` | `NO` | NULL | `NO` | `users.id` | Foreign key reference to `users.id`. |
| `system_announcements` | `title` | `varchar(255)` | `NO` | NULL | `NO` | NO | Stores relational attribute `title` for `system_announcements` records. |
| `system_announcements` | `body` | `text` | `NO` | NULL | `NO` | NO | Stores relational attribute `body` for `system_announcements` records. |
| `system_announcements` | `type` | `varchar(255)` | `NO` | `info` | `NO` | NO | Stores relational attribute `type` for `system_announcements` records. |
| `system_announcements` | `audience` | `varchar(255)` | `NO` | `all` | `NO` | NO | Stores relational attribute `audience` for `system_announcements` records. |
| `system_announcements` | `is_active` | `tinyint(1)` | `NO` | `1` | `NO` | NO | Boolean status flag indicating whether this entity is currently active in the system. |
| `system_announcements` | `starts_at` | `timestamp` | `YES` | NULL | `NO` | NO | Stores relational attribute `starts_at` for `system_announcements` records. |
| `system_announcements` | `ends_at` | `timestamp` | `YES` | NULL | `NO` | NO | Stores relational attribute `ends_at` for `system_announcements` records. |
| `system_announcements` | `created_at` | `timestamp` | `YES` | NULL | `NO` | NO | Timestamp recording when the record was initially created. |
| `system_announcements` | `updated_at` | `timestamp` | `YES` | NULL | `NO` | NO | Timestamp recording when the record was last modified. |
| `system_settings` | `id` | `bigint unsigned` | `NO` | NULL | `YES` | NO | Unique auto-incrementing primary identifier for the system_settings entity. |
| `system_settings` | `key` | `varchar(255)` | `NO` | NULL | `NO` | NO | Unique configuration parameter key name. |
| `system_settings` | `value` | `text` | `YES` | NULL | `NO` | NO | Serialized configuration value. |
| `system_settings` | `type` | `varchar(255)` | `NO` | `string` | `NO` | NO | Stores relational attribute `type` for `system_settings` records. |
| `system_settings` | `display_name` | `varchar(255)` | `YES` | NULL | `NO` | NO | Stores relational attribute `display_name` for `system_settings` records. |
| `system_settings` | `description` | `text` | `YES` | NULL | `NO` | NO | Stores relational attribute `description` for `system_settings` records. |
| `system_settings` | `is_public` | `tinyint(1)` | `NO` | `0` | `NO` | NO | Stores relational attribute `is_public` for `system_settings` records. |
| `system_settings` | `is_editable` | `tinyint(1)` | `NO` | `1` | `NO` | NO | Stores relational attribute `is_editable` for `system_settings` records. |
| `system_settings` | `created_at` | `timestamp` | `YES` | NULL | `NO` | NO | Timestamp recording when the record was initially created. |
| `system_settings` | `updated_at` | `timestamp` | `YES` | NULL | `NO` | NO | Timestamp recording when the record was last modified. |
| `user_organizational_units` | `id` | `bigint unsigned` | `NO` | NULL | `YES` | NO | Unique auto-incrementing primary identifier for the user_organizational_units entity. |
| `user_organizational_units` | `user_id` | `bigint unsigned` | `NO` | NULL | `NO` | `users.id` | Foreign key reference to `users.id`. |
| `user_organizational_units` | `organizational_unit_id` | `bigint unsigned` | `NO` | NULL | `NO` | `organizational_units.id` | Foreign key reference to `organizational_units.id`. |
| `user_organizational_units` | `is_primary` | `tinyint(1)` | `NO` | `0` | `NO` | NO | Stores relational attribute `is_primary` for `user_organizational_units` records. |
| `user_organizational_units` | `enrolled_year` | `smallint unsigned` | `YES` | NULL | `NO` | NO | Stores relational attribute `enrolled_year` for `user_organizational_units` records. |
| `user_organizational_units` | `created_at` | `timestamp` | `YES` | NULL | `NO` | NO | Timestamp recording when the record was initially created. |
| `user_organizational_units` | `updated_at` | `timestamp` | `YES` | NULL | `NO` | NO | Timestamp recording when the record was last modified. |
| `user_profiles` | `id` | `bigint unsigned` | `NO` | NULL | `YES` | NO | Unique auto-incrementing primary identifier for the user_profiles entity. |
| `user_profiles` | `user_id` | `bigint unsigned` | `NO` | NULL | `NO` | `users.id` | Foreign key reference to `users.id`. |
| `user_profiles` | `id_card_photo` | `varchar(255)` | `YES` | NULL | `NO` | NO | Stores relational attribute `id_card_photo` for `user_profiles` records. |
| `user_profiles` | `year_of_study` | `tinyint unsigned` | `YES` | NULL | `NO` | NO | Stores relational attribute `year_of_study` for `user_profiles` records. |
| `user_profiles` | `gender` | `varchar(20)` | `YES` | NULL | `NO` | NO | Stores relational attribute `gender` for `user_profiles` records. |
| `user_profiles` | `emergency_contact_name` | `varchar(255)` | `YES` | NULL | `NO` | NO | Stores relational attribute `emergency_contact_name` for `user_profiles` records. |
| `user_profiles` | `emergency_contact_phone` | `varchar(255)` | `YES` | NULL | `NO` | NO | Stores relational attribute `emergency_contact_phone` for `user_profiles` records. |
| `user_profiles` | `home_town` | `varchar(255)` | `YES` | NULL | `NO` | NO | Stores relational attribute `home_town` for `user_profiles` records. |
| `user_profiles` | `bio` | `text` | `YES` | NULL | `NO` | NO | Stores relational attribute `bio` for `user_profiles` records. |
| `user_profiles` | `last_seen_at` | `timestamp` | `YES` | NULL | `NO` | NO | Stores relational attribute `last_seen_at` for `user_profiles` records. |
| `user_profiles` | `created_at` | `timestamp` | `YES` | NULL | `NO` | NO | Timestamp recording when the record was initially created. |
| `user_profiles` | `updated_at` | `timestamp` | `YES` | NULL | `NO` | NO | Timestamp recording when the record was last modified. |
| `users` | `id` | `bigint unsigned` | `NO` | NULL | `YES` | NO | Unique auto-incrementing primary identifier for the users entity. |
| `users` | `role_id` | `bigint unsigned` | `NO` | NULL | `NO` | `roles.id` | Foreign key referencing the primary assigned role in `roles`. |
| `users` | `full_name` | `varchar(255)` | `NO` | NULL | `NO` | NO | Stores relational attribute `full_name` for `users` records. |
| `users` | `university_id` | `varchar(255)` | `NO` | NULL | `NO` | NO | Stores relational attribute `university_id` for `users` records. |
| `users` | `email` | `varchar(255)` | `NO` | NULL | `NO` | NO | Primary institutional email address used for login and notifications. |
| `users` | `email_verified_at` | `timestamp` | `YES` | NULL | `NO` | NO | Timestamp when the user verified their email address via OTP. |
| `users` | `password` | `varchar(255)` | `NO` | NULL | `NO` | NO | Bcrypt/Argon2 password hash string. |
| `users` | `phone` | `varchar(255)` | `YES` | NULL | `NO` | NO | Stores relational attribute `phone` for `users` records. |
| `users` | `language` | `varchar(10)` | `NO` | `en` | `NO` | NO | Stores relational attribute `language` for `users` records. |
| `users` | `is_active` | `tinyint(1)` | `NO` | `1` | `NO` | NO | Boolean status flag indicating whether this entity is currently active in the system. |
| `users` | `profile_photo` | `varchar(255)` | `YES` | NULL | `NO` | NO | Stores relational attribute `profile_photo` for `users` records. |
| `users` | `failed_login_attempts` | `int unsigned` | `NO` | `0` | `NO` | NO | Stores relational attribute `failed_login_attempts` for `users` records. |
| `users` | `locked_until` | `timestamp` | `YES` | NULL | `NO` | NO | Stores relational attribute `locked_until` for `users` records. |
| `users` | `remember_token` | `varchar(100)` | `YES` | NULL | `NO` | NO | Stores relational attribute `remember_token` for `users` records. |
| `users` | `created_at` | `timestamp` | `YES` | NULL | `NO` | NO | Timestamp recording when the record was initially created. |
| `users` | `updated_at` | `timestamp` | `YES` | NULL | `NO` | NO | Timestamp recording when the record was last modified. |