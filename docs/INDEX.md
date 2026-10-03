---
title: Documentation Suite Index
version: 1.1.0
system: Wollo University Lost & Found System
generated_from: source code (migrations, controllers, policies, routes, seeders)
date: 2026-10-03
---

# Wollo University Lost & Found System — Documentation Suite

This documentation suite provides a complete, production-grade technical reference for the Wollo University Lost & Found System. Every document is strictly derived and verified against active backend migrations, models, controllers, policies, routes, seeders, and frontend client architecture.

---

## Document Index

| Document | File Path | Purpose | Notes |
|---|---|---|---|
| **Functional Requirements Specification** | [`docs/FRS.md`](docs/FRS.md) | Formal functional and non-functional requirements specification defining all system modules, triggers, preconditions, postconditions, and business rules. | Source-verified |
| **Database Architecture & Schema Reference** | [`docs/DATABASE.md`](docs/DATABASE.md) | Complete MySQL schema reference detailing all **44 tables**, column constraints, composite indexes, foreign keys, normalization audit, and transaction boundaries. Includes `university_domains` table added 2026-09-15. | 44 tables (updated) |
| **REST API Reference (v1)** | [`docs/API_REFERENCE.md`](docs/API_REFERENCE.md) | Comprehensive API endpoint catalogue documenting request validation, response payloads, error codes, authentication, and executable cURL examples for all **145 registered routes**. | Route count updated |
| **Role-Based Access Control Architecture** | [`docs/RBAC.md`](docs/RBAC.md) | Technical architecture for dynamic RBAC, resolution precedence, role and permission catalogues, cache invalidation sequence, and privilege escalation prevention. | Current |
| **Data Dictionary** | [`docs/DATA_DICTIONARY.md`](docs/DATA_DICTIONARY.md) | Master attribute dictionary providing contextual descriptions, nullability, defaults, and relational links for every column across all **44 database tables**. Includes `university_domains`, `must_change_password`, and `must_change_password` additions. | 44 tables |
| **System Architecture Reference** | [`docs/ARCHITECTURE.md`](docs/ARCHITECTURE.md) | High-level system architecture guide covering the tech stack, directory structure, request lifecycle, domain service layer, Pinia stores, event pipelines, and storage. | Current |
| **Production Deployment & Hardening Guide** | [`docs/PRODUCTION_DEPLOYMENT.md`](docs/PRODUCTION_DEPLOYMENT.md) | Nginx, PHP-FPM, Supervisor queue daemon, and environment hardening configuration for production deployment. | Current |
| **Security & Code Audit Report** | [`docs/audit/AUDIT_REPORT.md`](docs/audit/AUDIT_REPORT.md) | Security audit conducted 2026-09-29 across business logic, RBAC, authentication, database, and frontend. 208 tests, 909 assertions, 145 routes verified. | Dated 2026-09-29 |
| **Documentation Sync Report** | [`docs/DOCUMENTATION_SYNC_REPORT.md`](docs/DOCUMENTATION_SYNC_REPORT.md) | Documentation synchronization audit performed 2026-10-03 verifying all docs against current codebase. | 2026-10-03 |

---

## Traceability & Verification Guarantee

- **Zero Assumptions:** Every functional requirement, database column, API endpoint, and authorization rule maps 1-to-1 with an active source artifact.
- **Source Citations:** Every claim, table specification, and endpoint documentation block contains inline code comments referencing exact source files (`<!-- source: ... -->`).
- **Engine & Version Verified:** Validated against Laravel 13.x (^13.17), PHP 8.3+, MySQL 8.0+, Vue 3.5+, TypeScript 6.0+, and Vite 8.2+.
- **Last Synchronized:** 2026-10-03 — see [`docs/DOCUMENTATION_SYNC_REPORT.md`](docs/DOCUMENTATION_SYNC_REPORT.md).
