# Database Verification & Table Statistics
**Database Name:** `wollo_lost_found_normalized`  
**Engine:** MySQL 8.0 InnoDB  
**Verification Date:** 2026-10-01T11:54:00+03:00  
**Integrity Status:** 100% Passed • 0 Foreign Key Violations • 0 Orphaned Records

---

## 1. Table Record Counts (44 Normalized Tables)

| No. | Table Name | Type | Record Count | Description |
|:---:|:---|:---:|:---:|:---|
| 1 | `users` | Core | **25** | System users across 5 institutional roles (Admin, Staff, Supervisor, Dept Head, Student) |
| 2 | `user_profiles` | Profile | **23** | Extended user demographic & academic profile data |
| 3 | `roles` | RBAC | **5** | Dynamic roles (`admin`, `staff`, `student`, `supervisor`, `department_head`) |
| 4 | `permissions` | RBAC | **22** | Granular authorization actions |
| 5 | `permission_groups` | RBAC | **6** | Groupings for permission management |
| 6 | `permission_role` | RBAC Pivot | **60** | Role-to-permission active bindings |
| 7 | `campuses` | Org | **3** | Dessie (Main), KIoT (Kombolcha), Tita campuses |
| 8 | `organizational_units` | Org | **29** | Colleges, departments, security divisions |
| 9 | `organizational_unit_types` | Org | **6** | Division taxonomy |
| 10 | `categories` | Inventory | **11** | Standardized item classifications |
| 11 | `locations` | Geo | **18** | Specific campus zones, libraries, gates |
| 12 | `storage_locations` | Custody | **9** | Physical custody depots, secure lockers, and shelves |
| 13 | `items` | Core | **26** | Lost & Found tracked inventory records |
| 14 | `item_photos` | Media | **25** | Verified photo attachments |
| 15 | `item_tags` | Taxonomy | **126** | Search tokens & indexing keywords |
| 16 | `item_status_histories` | Audit | **41** | State transition timeline records |
| 17 | `item_views` | Analytics | **1,577** | Unique public & user view logs |
| 18 | `claims` | Core | **7** | Ownership claim verification requests |
| 19 | `claim_evidence` | Verification | **4** | Digital proof of ownership documents |
| 20 | `claim_status_histories` | Audit | **14** | Claim lifecycle state transition records |
| 21 | `returns` | Handover | **2** | Cryptographic return handover records |
| 22 | `return_documents` | Handover | **2** | Signed return receipt proofs |
| 23 | `custody_events` | Chain-of-Custody | **24** | Physical custody change log |
| 24 | `match_suggestions` | AI/Engine | **9** | Algorithmic similarity match pairs (Jaccard scored) |
| 25 | `audit_logs` | Forensic | **58** | Immutable system-wide forensic audit logs |
| 26 | `notifications` | Alerts | **87** | In-app alerts & notifications |
| 27 | `notification_preferences` | User Prefs | **23** | User notification configuration |
| 28 | `search_logs` | Analytics | **67** | Real user search query logs |
| 29 | `system_announcements` | Notice | **3** | Campus-wide broadcast announcements |
| 30 | `system_settings` | Config | **36** | System-wide operational settings |
| 31 | `university_domains` | Security | **2** | Authorized university email domains |
| 32 | `auth_verifications` | Security | **14** | One-time password verification tokens |
| 33 | `password_histories` | Security | **2** | Enforced credential history |
| 34 | `sessions` | Auth | **4** | Active user database sessions |
| 35 | `cache` | Cache | **28** | Cached operational keys |
| 36 | `cache_locks` | Concurrency | **0** | Concurrency mutex locks |
| 37 | `failed_jobs` | Queue | **0** | Dead-letter queue (0 failures) |
| 38 | `jobs` | Queue | **0** | Active background queue jobs |
| 39 | `job_batches` | Queue | **0** | Batch processing jobs |
| 40 | `migrations` | Schema | **44** | Executed database migrations |
| 41 | `organizational_unit_type_relations` | Org | **5** | Hierarchical relations |
| 42 | `permission_user` | RBAC Pivot | **0** | Direct user permission overrides (0 - clean role-based) |
| 43 | `reports` | Analytics | **20** | Generated PDF/CSV export artifacts |
| 44 | `user_organizational_units` | Org Pivot | **15** | Staff-to-department assignments |

---

## 2. Relational Integrity Checks (Automated Forensic SQL)

```sql
-- 1. Claims referencing non-existent items:
SELECT count(*) FROM claims c LEFT JOIN items i ON c.item_id = i.id WHERE i.id IS NULL;
-- Result: 0 (PASSED)

-- 2. Returns referencing non-existent claims:
SELECT count(*) FROM returns r LEFT JOIN claims c ON r.claim_id = c.id WHERE c.id IS NULL;
-- Result: 0 (PASSED)

-- 3. Custody events referencing non-existent items:
SELECT count(*) FROM custody_events ce LEFT JOIN items i ON ce.item_id = i.id WHERE i.id IS NULL;
-- Result: 0 (PASSED)

-- 4. Match suggestions referencing non-existent items:
SELECT count(*) FROM match_suggestions ms LEFT JOIN items i ON ms.lost_item_id = i.id WHERE i.id IS NULL;
-- Result: 0 (PASSED)

-- 5. Items referencing non-existent categories:
SELECT count(*) FROM items i LEFT JOIN categories c ON i.category_id = c.id WHERE c.id IS NULL;
-- Result: 0 (PASSED)
```
