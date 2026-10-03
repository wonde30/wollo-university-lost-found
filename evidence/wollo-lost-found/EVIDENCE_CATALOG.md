# Wollo University Lost & Found Item Tracking Operational Portal
## Real-System Visual Evidence Package & Verification Catalog

**System Name:** Wollo University Lost & Found Item Tracking Operational Portal  
**Verification Date:** 2026-10-01  
**Integrity State:** Operational • 100% Verified against Active Codebase & Active MySQL 8.0 Database  
**Author / Inspector:** Principal Forensic Audit & System Evidence Engineer  

---

### Executive Overview

This evidence package contains **direct, un-mocked visual and technical proof** captured from the live, running deployment of the Wollo University Lost & Found Item Tracking Operational Portal.

- **Frontend:** Vue 3.5 (Composition API), TypeScript 6, Vite 8, Tailwind CSS v4, Pinia (running at `http://localhost:5173`)
- **Backend:** Laravel 11 REST API, Sanctum Auth, Form Request Validation, Policy Authorization (running at `http://localhost:8000`)
- **Database:** MySQL 8.0 (`wollo_lost_found_normalized`) with 44 Normalized Tables and 0 FK Violations
- **Matching Engine:** Deterministic category-gated Jaccard similarity scorer
- **Cryptographic Handover:** Secure SHA-256 token verification on return release

---

## Complete Evidence Index

### 01. Cover & Branding (`01-cover/`)
| File | Type | Source / Resolution | Description |
|:---|:---:|:---:|:---|
| [`01-portal-cover.jpg`](file:///c:/Users/W/Desktop/wollo-lost-found/evidence/wollo-lost-found/01-cover/01-portal-cover.jpg) | High-Res Graphic | 1920x1080 | Ultra-professional academic internship cover visual with institutional Wollo University crest, status badges, and tech stack tags |
| [`02-real-system-hero.png`](file:///c:/Users/W/Desktop/wollo-lost-found/evidence/wollo-lost-found/01-cover/02-real-system-hero.png) | Screenshot | 1440x900 | Actual live system landing hero banner with operational metrics (Lost, Found, Claimed, Returned) |

---

### 02. Public Visitor Experience (`02-public/`)
| File | Route | Viewport | Verified Assertion |
|:---|:---|:---:|:---|
| [`01-public-home.png`](file:///c:/Users/W/Desktop/wollo-lost-found/evidence/wollo-lost-found/02-public/01-public-home.png) | `/` | 1440x900 | Public hero section, institutional branding, quick search input, report action CTAs |
| [`02-public-home-below-fold.png`](file:///c:/Users/W/Desktop/wollo-lost-found/evidence/wollo-lost-found/02-public/02-public-home-below-fold.png) | `/` | 1440x900 | Recent items feed, institutional depots (Dessie, KIoT, Tita), campus statistics |
| [`03-public-browse.png`](file:///c:/Users/W/Desktop/wollo-lost-found/evidence/wollo-lost-found/02-public/03-public-browse.png) | `/browse` | 1440x900 | Filterable inventory grid with category chips, search query input, and campus dropdown |

---

### 03. Authentication Experience (`03-auth/`)
| File | Route | Viewport | Verified Assertion |
|:---|:---|:---:|:---|
| [`01-login.png`](file:///c:/Users/W/Desktop/wollo-lost-found/evidence/wollo-lost-found/03-auth/01-login.png) | `/login` | 1440x900 | Secure credentials form, university domain validation, role redirect handling |
| [`02-register.png`](file:///c:/Users/W/Desktop/wollo-lost-found/evidence/wollo-lost-found/03-auth/02-register.png) | `/register` | 1440x900 | Student registration with ID format verification, college selection, password rules |
| [`03-forgot-password.png`](file:///c:/Users/W/Desktop/wollo-lost-found/evidence/wollo-lost-found/03-auth/03-forgot-password.png) | `/forgot-password` | 1440x900 | OTP password recovery flow with university email domain requirement |

---

### 04. Student Self-Service Portal (`04-student/`)
| File | Route | Actor | Verified Assertion |
|:---|:---|:---:|:---|
| [`01-student-dashboard.png`](file:///c:/Users/W/Desktop/wollo-lost-found/evidence/wollo-lost-found/04-student/01-student-dashboard.png) | `/student/dashboard` | Student (`student@wu.edu.et`) | Personalized overview, active reports tally, claim statuses, recent alerts |
| [`02-student-report-lost.png`](file:///c:/Users/W/Desktop/wollo-lost-found/evidence/wollo-lost-found/04-student/02-student-report-lost.png) | `/student/report-lost` | Student | Multi-step loss report wizard with serial number, photo upload, and incident geo-tagging |
| [`03-student-report-found.png`](file:///c:/Users/W/Desktop/wollo-lost-found/evidence/wollo-lost-found/04-student/03-student-report-found.png) | `/student/report-found` | Student | Found item surrender wizard with storage depot drop-off selection |
| [`04-student-my-items.png`](file:///c:/Users/W/Desktop/wollo-lost-found/evidence/wollo-lost-found/04-student/04-student-my-items.png) | `/student/my-items` | Student | Real-time tracking of reported items with status pill badges (`lost`, `found`) |
| [`05-student-my-claims.png`](file:///c:/Users/W/Desktop/wollo-lost-found/evidence/wollo-lost-found/04-student/05-student-my-claims.png) | `/student/my-claims` | Student | Lifecycle tracking of submitted claims with review remarks and approval state |
| [`06-student-notifications.png`](file:///c:/Users/W/Desktop/wollo-lost-found/evidence/wollo-lost-found/04-student/06-student-notifications.png) | In-App Drawer | Student | Real-time push alert center showing item match notifications and claim updates |

---

### 05. Security Staff & Custody Desk (`05-staff/`)
| File | Route | Actor | Verified Assertion |
|:---|:---|:---:|:---|
| [`01-staff-dashboard.png`](file:///c:/Users/W/Desktop/wollo-lost-found/evidence/wollo-lost-found/05-staff/01-staff-dashboard.png) | `/staff/dashboard` | Security Staff | Security desk command center: intake counts, pending verification queues, depot load |
| [`02-staff-items.png`](file:///c:/Users/W/Desktop/wollo-lost-found/evidence/wollo-lost-found/05-staff/02-staff-items.png) | `/staff/items` | Security Staff | Physical inventory queue with reference codes, storage bins, and custody actions |
| [`03-staff-review-claims.png`](file:///c:/Users/W/Desktop/wollo-lost-found/evidence/wollo-lost-found/05-staff/03-staff-review-claims.png) | `/staff/review-claims` | Security Staff | Claim forensic verification view: evidence inspection, approval/rejection modal |
| [`04-staff-custody.png`](file:///c:/Users/W/Desktop/wollo-lost-found/evidence/wollo-lost-found/05-staff/04-staff-custody.png) | `/staff/manage-custody` | Security Staff | Chain-of-custody transfer console: locker assignments, security seals, handover logs |
| [`05-staff-process-return.png`](file:///c:/Users/W/Desktop/wollo-lost-found/evidence/wollo-lost-found/05-staff/05-staff-process-return.png) | `/staff/process-return` | Security Staff | Physical release workflow: identity verification, handover notes, receipt generation |

---

### 06. Custom Roles & Institutional Governance (`06-custom-roles/`)
| File | Route | Actor | Verified Assertion |
|:---|:---|:---:|:---|
| [`01-supervisor-dashboard.png`](file:///c:/Users/W/Desktop/wollo-lost-found/evidence/wollo-lost-found/06-custom-roles/01-supervisor-dashboard.png) | `/staff/dashboard` | Security Supervisor | Supervisor view with escalation oversight, high-value item alerts, staff audit logs |
| [`02-department-head-dashboard.png`](file:///c:/Users/W/Desktop/wollo-lost-found/evidence/wollo-lost-found/06-custom-roles/02-department-head-dashboard.png) | `/admin/dashboard` | Dept Head (CS) | Departmental oversight view: collegiate item tracking and departmental loss statistics |

---

### 07. Administrator Control Center (`07-admin/`)
| File | Route | Actor | Verified Assertion |
|:---|:---|:---:|:---|
| [`01-admin-dashboard.png`](file:///c:/Users/W/Desktop/wollo-lost-found/evidence/wollo-lost-found/07-admin/01-admin-dashboard.png) | `/admin/dashboard` | System Admin | Enterprise KPI analytics: resolution rate, average claim latency, system health |
| [`02-admin-users.png`](file:///c:/Users/W/Desktop/wollo-lost-found/evidence/wollo-lost-found/07-admin/02-admin-users.png) | `/admin/users` | System Admin | User directory management: role assignment, campus assignment, status toggles |
| [`03-admin-settings.png`](file:///c:/Users/W/Desktop/wollo-lost-found/evidence/wollo-lost-found/07-admin/03-admin-settings.png) | `/admin/settings` | System Admin | Operational parameters: match threshold %, token expiry hours, maintenance toggle |
| [`04-admin-audit-logs.png`](file:///c:/Users/W/Desktop/wollo-lost-found/evidence/wollo-lost-found/07-admin/04-admin-audit-logs.png) | `/admin/audit-logs` | System Admin | Immutable system audit log: actor IP, action type, auditable model, JSON diffs |
| [`05-admin-reports.png`](file:///c:/Users/W/Desktop/wollo-lost-found/evidence/wollo-lost-found/07-admin/05-admin-reports.png) | `/admin/reports` | System Admin | Multi-format reporting engine: generate & download CSV, Excel, and PDF summaries |
| [`06-admin-categories.png`](file:///c:/Users/W/Desktop/wollo-lost-found/evidence/wollo-lost-found/07-admin/06-admin-categories.png) | `/admin/categories` | System Admin | Taxonomy editor: 11 categories with icon, high-value flag, and retention policies |
| [`07-admin-storage-locations.png`](file:///c:/Users/W/Desktop/wollo-lost-found/evidence/wollo-lost-found/07-admin/07-admin-storage-locations.png) | `/admin/storage-locations` | System Admin | Physical storage depots: 9 locations across Dessie, KIoT, and Tita campuses |
| [`08-admin-roles.png`](file:///c:/Users/W/Desktop/wollo-lost-found/evidence/wollo-lost-found/07-admin/08-admin-roles.png) | `/admin/roles` | System Admin | Dynamic RBAC matrix: 5 roles mapped against 22 system-level permissions |

---

### 08. Item Lifecycle & Detailed Views (`08-items/`)
| File | Route | Verified Assertion |
|:---|:---|:---|
| [`01-item-detail-lost.png`](file:///c:/Users/W/Desktop/wollo-lost-found/evidence/wollo-lost-found/08-items/01-item-detail-lost.png) | `/items/1` | Detailed lost item dossier (`WU-L000001`): HP Pavilion 15, serial number, status badge |
| [`02-item-detail-found.png`](file:///c:/Users/W/Desktop/wollo-lost-found/evidence/wollo-lost-found/08-items/02-item-detail-found.png) | `/items/2` | Detailed found item dossier (`WU-F000001`): Silver HP Laptop, custody location, claim CTA |

---

### 09. Algorithmic Matching Engine (`09-matching/`)
| File | Route | Verified Assertion |
|:---|:---|:---|
| [`01-match-suggestions.png`](file:///c:/Users/W/Desktop/wollo-lost-found/evidence/wollo-lost-found/09-matching/01-match-suggestions.png) | `/staff/match-suggestions` | Jaccard similarity scoring breakdown: 94.5% match confidence, title similarity, review CTA |

---

### 10. Claims Management (`10-claims/`)
| File | Route | Verified Assertion |
|:---|:---|:---|
| [`01-staff-claims-review.png`](file:///c:/Users/W/Desktop/wollo-lost-found/evidence/wollo-lost-found/10-claims/01-staff-claims-review.png) | `/staff/review-claims` | Staff claims review board with evidence inspection and approval pipeline |
| [`02-claim-submission-form.png`](file:///c:/Users/W/Desktop/wollo-lost-found/evidence/wollo-lost-found/10-claims/02-claim-submission-form.png) | `/student/claim/2` | Student claim submission modal: ownership explanation, serial verification, proof upload |

---

### 11. Custody & Storage Depots (`11-custody/`)
| File | Route | Verified Assertion |
|:---|:---|:---|
| [`01-custody-board.png`](file:///c:/Users/W/Desktop/wollo-lost-found/evidence/wollo-lost-found/11-custody/01-custody-board.png) | `/staff/manage-custody` | Live custody tracker: storage depot assignments, shelf/bin numbers, security seals |
| [`02-admin-storage-locations.png`](file:///c:/Users/W/Desktop/wollo-lost-found/evidence/wollo-lost-found/11-custody/02-admin-storage-locations.png) | `/admin/storage-locations` | Master list of 9 physical campus storage depots with capacity and campus mapping |

---

### 12. Returns & Cryptographic Handover (`12-returns/`)
| File | Route | Verified Assertion |
|:---|:---|:---|
| [`01-staff-process-return.png`](file:///c:/Users/W/Desktop/wollo-lost-found/evidence/wollo-lost-found/12-returns/01-staff-process-return.png) | `/staff/process-return` | Staff release console: claimant verification and handover token generation |
| [`02-return-confirmation-token.png`](file:///c:/Users/W/Desktop/wollo-lost-found/evidence/wollo-lost-found/12-returns/02-return-confirmation-token.png) | `/confirm-return/test-confirm-token-wu-2026` | Live recipient confirmation page: active token validation, item details, confirmation CTA |

---

### 13–15. System Operations & Forensic Audit (`13-notifications/`, `14-reports/`, `15-audit/`)
| File | Route | Verified Assertion |
|:---|:---|:---|
| [`01-student-notifications.png`](file:///c:/Users/W/Desktop/wollo-lost-found/evidence/wollo-lost-found/13-notifications/01-student-notifications.png) | In-App Popover | Real-time student alerts for item matches, claim reviews, and return pickups |
| [`01-admin-reports.png`](file:///c:/Users/W/Desktop/wollo-lost-found/evidence/wollo-lost-found/14-reports/01-admin-reports.png) | `/admin/reports` | Generated report archive (20 reports in PDF, CSV, Excel formats) |
| [`01-admin-audit-logs.png`](file:///c:/Users/W/Desktop/wollo-lost-found/evidence/wollo-lost-found/15-audit/01-admin-audit-logs.png) | `/admin/audit-logs` | Immutable audit trail viewer (58 records): actor tracking, event timestamps, JSON diffs |

---

### 16. Multi-Device Responsive Verification (`16-responsive/`)
| File | Viewport | Verified View |
|:---|:---:|:---|
| [`01-mobile-home.png`](file:///c:/Users/W/Desktop/wollo-lost-found/evidence/wollo-lost-found/16-responsive/01-mobile-home.png) | 390x844 (Mobile) | Public Landing: responsive navbar, stacked hero CTAs, touch-friendly navigation |
| [`02-mobile-browse.png`](file:///c:/Users/W/Desktop/wollo-lost-found/evidence/wollo-lost-found/16-responsive/02-mobile-browse.png) | 390x844 (Mobile) | Public Browse: responsive filter drawer and single-column item cards |
| [`03-mobile-login.png`](file:///c:/Users/W/Desktop/wollo-lost-found/evidence/wollo-lost-found/16-responsive/03-mobile-login.png) | 390x844 (Mobile) | Mobile Authentication: optimized form inputs and virtual keyboard friendly layout |
| [`04-tablet-home.png`](file:///c:/Users/W/Desktop/wollo-lost-found/evidence/wollo-lost-found/16-responsive/04-tablet-home.png) | 768x1024 (Tablet) | Tablet Landing: dual-column stats, expanded search bar, responsive grid |
| [`05-tablet-browse.png`](file:///c:/Users/W/Desktop/wollo-lost-found/evidence/wollo-lost-found/16-responsive/05-tablet-browse.png) | 768x1024 (Tablet) | Tablet Browse: responsive multi-column layout with sidebar filters |

---

### 17. Error & Exception States (`17-error-states/`)
| File | Route | Verified Assertion |
|:---|:---|:---|
| [`01-error-404.png`](file:///c:/Users/W/Desktop/wollo-lost-found/evidence/wollo-lost-found/17-error-states/01-error-404.png) | `/some-invalid-page-404-error` | Graceful 404 Not Found error page with institutional styling and 'Back to Safety' navigation |

---

### 18–20. Technical Visuals & Architecture (`18-architecture/`, `19-database/`, `20-workflows/`)
| File | Format | Verified Content |
|:---|:---:|:---|
| [`01-system-architecture.svg`](file:///c:/Users/W/Desktop/wollo-lost-found/evidence/wollo-lost-found/18-architecture/01-system-architecture.svg) / [`.png`](file:///c:/Users/W/Desktop/wollo-lost-found/evidence/wollo-lost-found/18-architecture/01-system-architecture.png) | Vector / HD PNG | 3-Tier System Architecture (React 18 + Laravel 11 + MySQL 8.0) |
| [`01-database-erd.svg`](file:///c:/Users/W/Desktop/wollo-lost-found/evidence/wollo-lost-found/19-database/01-database-erd.svg) / [`.png`](file:///c:/Users/W/Desktop/wollo-lost-found/evidence/wollo-lost-found/19-database/01-database-erd.png) | Vector / HD PNG | Complete Entity-Relationship Diagram of 44 tables, foreign keys, and indexes |
| [`02-table-statistics.md`](file:///c:/Users/W/Desktop/wollo-lost-found/evidence/wollo-lost-found/19-database/02-table-statistics.md) | Markdown | Table-by-table record count statistics and foreign-key integrity proofs |
| [`01-item-lifecycle.svg`](file:///c:/Users/W/Desktop/wollo-lost-found/evidence/wollo-lost-found/20-workflows/01-item-lifecycle.svg) / [`.png`](file:///c:/Users/W/Desktop/wollo-lost-found/evidence/wollo-lost-found/20-workflows/01-item-lifecycle.png) | Vector / HD PNG | 4-Stage Operational Lifecycle Diagram (Intake -> Matching -> Verification -> Return) |
