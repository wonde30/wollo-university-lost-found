---
title: REST API Reference — v1
version: 1.0.0
system: Wollo University Lost & Found System
generated_from: source code (migrations, controllers, policies, routes, seeders)
date: 2026-08-31
---

# REST API Reference — v1
## Wollo University Lost & Found System

**Base URL:** `/api/v1`  
**Authentication:** Bearer token (Laravel Sanctum SPA / API Token)  
**Header:** `Accept: application/json`  

<!-- source: routes/api.php -->
<!-- source: app/Http/Controllers/Api/V1/ -->

---

### 1. Authentication

#### 1.1 Token Lifecycle
Authentication in the Wollo University Lost & Found System uses **Laravel Sanctum** personal access tokens. Authenticated endpoints require the token to be transmitted in the HTTP `Authorization` request header as a Bearer token:
```http
Authorization: Bearer <sanctum_token_string>
```

#### 1.2 Expiry & Refresh Mechanism
- **Token Expiry:** Tokens remain valid until explicitly invalidated via the logout endpoint (`POST /api/v1/auth/logout`) or revoked through administrative account status changes (`PATCH /api/v1/admin/users/{id}/toggle-active`).
- **Session Verification:** Clients verify current session validity and fetch authenticated user profile/permission grants using `GET /api/v1/auth/me`.
- **Email / Password OTP Tokens:** Verification codes generated in `auth_verifications` strictly expire in 15 minutes (`expires_at = now()->addMinutes(15)`).

---

### 2. Global Conventions

#### 2.1 Request Headers
| Header | Value | Requirement | Description |
|--------|-------|-------------|-------------|
| `Accept` | `application/json` | Mandatory | Informs the API to serialize all responses and error payloads as JSON. |
| `Content-Type` | `application/json` | Required for POST/PUT/PATCH | Specifies payload format (or `multipart/form-data` for file uploads). |
| `Authorization` | `Bearer <token>` | Required for protected routes | Transmits Sanctum access token for user authentication. |

#### 2.2 Standard Response Envelope Format
Successful requests return a standardized JSON envelope with a boolean status flag, contextual payload, and optional response message:
```json
{
  "success": true,
  "message": "Operation completed successfully.",
  "data": {
    "id": 1,
    "reference_code": "LOST-2026-00001"
  }
}
```

#### 2.3 Pagination Format
Paginated collections (e.g. items, claims, audit logs) return standard Laravel pagination metadata:
```json
{
  "success": true,
  "data": [...],
  "meta": {
    "current_page": 1,
    "from": 1,
    "last_page": 10,
    "per_page": 15,
    "to": 15,
    "total": 142
  },
  "links": {
    "first": "https://lostandfound.wollo.edu.et/api/v1/items?page=1",
    "last": "https://lostandfound.wollo.edu.et/api/v1/items?page=10",
    "prev": null,
    "next": "https://lostandfound.wollo.edu.et/api/v1/items?page=2"
  }
}
```

#### 2.4 Error Envelope Format
Errors return standard RFC-compliant HTTP status codes with structured error descriptions:
```json
{
  "success": false,
  "message": "The given data was invalid.",
  "errors": {
    "email": [
      "The email has already been taken."
    ]
  }
}
```

#### 2.5 HTTP Status Codes
| Code | Description | Usage in System |
|------|-------------|-----------------|
| `200 OK` | Request succeeded | Standard response for successful GET, PUT, PATCH, or non-creation POST operations. |
| `201 Created` | Resource created | Returned upon successful record creation (e.g. user registration, item report, claim submission). |
| `204 No Content` | Success with empty body | Returned upon resource deletion (e.g. photo deletion, item soft-delete). |
| `400 Bad Request` | Malformed request | State machine violation or invalid domain action transition. |
| `401 Unauthorized` | Missing / invalid token | Sanctum token missing, expired, or invalid. |
| `403 Forbidden` | Access denied | User lacks required role, permission, or ownership under Eloquent Policy. |
| `404 Not Found` | Resource not found | Target entity ID does not exist or has been deleted. |
| `422 Unprocessable` | Validation failed | FormRequest field validation failed against schema rules. |
| `429 Too Many Requests` | Rate limit exceeded | Endpoint rate limit exceeded (e.g. tracking throttling, login throttling). |
| `500 Server Error` | Internal exception | Unhandled server exception (logged with stack trace). |

---

### 3. Endpoint Reference

## Authentication & Session (AUTH)

### `POST /api/v1/auth/register`
<!-- source: routes/api.php -->
<!-- source: App/Http/Controllers/Api/V1/Auth/RegisterController.php -->
**Route Name:** `None`  
**Controller Action:** `\App\Http\Controllers\Api\V1\Auth\RegisterController@register`  
**Middleware Stack:** `api`  
**Policy Enforcement:** None (Guarded by Middleware / Ownership check)  
**Required Role(s):** Public (Guest)  

#### Request
**Request Body (`application/json`):**
```json
{
    "first_name": "Abebe",
    "last_name": "Kebede",
    "email": "abebe@wollo.edu.et",
    "phone_number": "+251911000000",
    "id_number": "UGR\/1234\/15",
    "password": "SecurePass123!",
    "password_confirmation": "SecurePass123!",
    "campus_id": 1,
    "organizational_unit_id": 3
}
```

#### Response `200 OK` / `201 Created`
```json
{
    "success": true,
    "message": "Request processed successfully.",
    "data": {
        "id": 1,
        "status": "active",
        "created_at": "2026-08-31T12:00:00.000000Z"
    }
}
```

#### Response Errors
- `401 Unauthorized`: Authentication token missing, expired, or revoked.
- `404 Not Found`: Target resource was not found.
- `422 Unprocessable Entity`: Request body failed FormRequest validation.

#### Example cURL
```bash
curl -X POST "https://lostandfound.wollo.edu.et/api/v1/auth/register" \
  -H "Accept: application/json" \
  -H "Content-Type: application/json" \
  -d '{"sample_field": "value"}'
```

---

### `POST /api/v1/auth/login`
<!-- source: routes/api.php -->
<!-- source: App/Http/Controllers/Api/V1/Auth/LoginController.php -->
**Route Name:** `login`  
**Controller Action:** `\App\Http\Controllers\Api\V1\Auth\LoginController@login`  
**Middleware Stack:** `api`  
**Policy Enforcement:** None (Guarded by Middleware / Ownership check)  
**Required Role(s):** Public (Guest)  

#### Request
**Request Body (`application/json`):**
```json
{
    "email": "abebe@wollo.edu.et",
    "password": "SecurePass123!"
}
```

#### Response `200 OK` / `201 Created`
```json
{
    "success": true,
    "message": "Request processed successfully.",
    "data": {
        "id": 1,
        "status": "active",
        "created_at": "2026-08-31T12:00:00.000000Z"
    }
}
```

#### Response Errors
- `401 Unauthorized`: Authentication token missing, expired, or revoked.
- `404 Not Found`: Target resource was not found.
- `422 Unprocessable Entity`: Request body failed FormRequest validation.

#### Example cURL
```bash
curl -X POST "https://lostandfound.wollo.edu.et/api/v1/auth/login" \
  -H "Accept: application/json" \
  -H "Content-Type: application/json" \
  -d '{"sample_field": "value"}'
```

---

### `POST /api/v1/auth/verify-email`
<!-- source: routes/api.php -->
<!-- source: App/Http/Controllers/Api/V1/Auth/VerificationController.php -->
**Route Name:** `None`  
**Controller Action:** `\App\Http\Controllers\Api\V1\Auth\VerificationController@verify`  
**Middleware Stack:** `api`  
**Policy Enforcement:** None (Guarded by Middleware / Ownership check)  
**Required Role(s):** Public (Guest)  

#### Request
**Request Body (`application/json`):**
```json
{
    "data": "sample_payload"
}
```

#### Response `200 OK` / `201 Created`
```json
{
    "success": true,
    "message": "Request processed successfully.",
    "data": {
        "id": 1,
        "status": "active",
        "created_at": "2026-08-31T12:00:00.000000Z"
    }
}
```

#### Response Errors
- `401 Unauthorized`: Authentication token missing, expired, or revoked.
- `404 Not Found`: Target resource was not found.
- `422 Unprocessable Entity`: Request body failed FormRequest validation.

#### Example cURL
```bash
curl -X POST "https://lostandfound.wollo.edu.et/api/v1/auth/verify-email" \
  -H "Accept: application/json" \
  -H "Content-Type: application/json" \
  -d '{"sample_field": "value"}'
```

---

### `POST /api/v1/auth/resend-verification`
<!-- source: routes/api.php -->
<!-- source: App/Http/Controllers/Api/V1/Auth/VerificationController.php -->
**Route Name:** `None`  
**Controller Action:** `\App\Http\Controllers\Api\V1\Auth\VerificationController@resend`  
**Middleware Stack:** `api`  
**Policy Enforcement:** None (Guarded by Middleware / Ownership check)  
**Required Role(s):** Public (Guest)  

#### Request
**Request Body (`application/json`):**
```json
{
    "data": "sample_payload"
}
```

#### Response `200 OK` / `201 Created`
```json
{
    "success": true,
    "message": "Request processed successfully.",
    "data": {
        "id": 1,
        "status": "active",
        "created_at": "2026-08-31T12:00:00.000000Z"
    }
}
```

#### Response Errors
- `401 Unauthorized`: Authentication token missing, expired, or revoked.
- `404 Not Found`: Target resource was not found.
- `422 Unprocessable Entity`: Request body failed FormRequest validation.

#### Example cURL
```bash
curl -X POST "https://lostandfound.wollo.edu.et/api/v1/auth/resend-verification" \
  -H "Accept: application/json" \
  -H "Content-Type: application/json" \
  -d '{"sample_field": "value"}'
```

---

### `POST /api/v1/auth/forgot-password`
<!-- source: routes/api.php -->
<!-- source: App/Http/Controllers/Api/V1/Auth/PasswordResetController.php -->
**Route Name:** `None`  
**Controller Action:** `\App\Http\Controllers\Api\V1\Auth\PasswordResetController@request`  
**Middleware Stack:** `api`  
**Policy Enforcement:** None (Guarded by Middleware / Ownership check)  
**Required Role(s):** Public (Guest)  

#### Request
**Request Body (`application/json`):**
```json
{
    "data": "sample_payload"
}
```

#### Response `200 OK` / `201 Created`
```json
{
    "success": true,
    "message": "Request processed successfully.",
    "data": {
        "id": 1,
        "status": "active",
        "created_at": "2026-08-31T12:00:00.000000Z"
    }
}
```

#### Response Errors
- `401 Unauthorized`: Authentication token missing, expired, or revoked.
- `404 Not Found`: Target resource was not found.
- `422 Unprocessable Entity`: Request body failed FormRequest validation.

#### Example cURL
```bash
curl -X POST "https://lostandfound.wollo.edu.et/api/v1/auth/forgot-password" \
  -H "Accept: application/json" \
  -H "Content-Type: application/json" \
  -d '{"sample_field": "value"}'
```

---

### `POST /api/v1/auth/verify-password-reset`
<!-- source: routes/api.php -->
<!-- source: App/Http/Controllers/Api/V1/Auth/PasswordResetController.php -->
**Route Name:** `None`  
**Controller Action:** `\App\Http\Controllers\Api\V1\Auth\PasswordResetController@verifyOtp`  
**Middleware Stack:** `api`  
**Policy Enforcement:** None (Guarded by Middleware / Ownership check)  
**Required Role(s):** Public (Guest)  

#### Request
**Request Body (`application/json`):**
```json
{
    "data": "sample_payload"
}
```

#### Response `200 OK` / `201 Created`
```json
{
    "success": true,
    "message": "Request processed successfully.",
    "data": {
        "id": 1,
        "status": "active",
        "created_at": "2026-08-31T12:00:00.000000Z"
    }
}
```

#### Response Errors
- `401 Unauthorized`: Authentication token missing, expired, or revoked.
- `404 Not Found`: Target resource was not found.
- `422 Unprocessable Entity`: Request body failed FormRequest validation.

#### Example cURL
```bash
curl -X POST "https://lostandfound.wollo.edu.et/api/v1/auth/verify-password-reset" \
  -H "Accept: application/json" \
  -H "Content-Type: application/json" \
  -d '{"sample_field": "value"}'
```

---

### `POST /api/v1/auth/reset-password`
<!-- source: routes/api.php -->
<!-- source: App/Http/Controllers/Api/V1/Auth/PasswordResetController.php -->
**Route Name:** `None`  
**Controller Action:** `\App\Http\Controllers\Api\V1\Auth\PasswordResetController@reset`  
**Middleware Stack:** `api`  
**Policy Enforcement:** None (Guarded by Middleware / Ownership check)  
**Required Role(s):** Public (Guest)  

#### Request
**Request Body (`application/json`):**
```json
{
    "data": "sample_payload"
}
```

#### Response `200 OK` / `201 Created`
```json
{
    "success": true,
    "message": "Request processed successfully.",
    "data": {
        "id": 1,
        "status": "active",
        "created_at": "2026-08-31T12:00:00.000000Z"
    }
}
```

#### Response Errors
- `401 Unauthorized`: Authentication token missing, expired, or revoked.
- `404 Not Found`: Target resource was not found.
- `422 Unprocessable Entity`: Request body failed FormRequest validation.

#### Example cURL
```bash
curl -X POST "https://lostandfound.wollo.edu.et/api/v1/auth/reset-password" \
  -H "Accept: application/json" \
  -H "Content-Type: application/json" \
  -d '{"sample_field": "value"}'
```

---

### `GET /api/v1/auth/me`
<!-- source: routes/api.php -->
<!-- source: App/Http/Controllers/Api/V1/Auth/LoginController.php -->
**Route Name:** `None`  
**Controller Action:** `\App\Http\Controllers\Api\V1\Auth\LoginController@me`  
**Middleware Stack:** `api, auth:sanctum`  
**Policy Enforcement:** None (Guarded by Middleware / Ownership check)  
**Required Role(s):** Authenticated User (`student`, `staff`, `admin`)  

#### Request
#### Response `200 OK` / `201 Created`
```json
{
    "success": true,
    "message": "Request processed successfully.",
    "data": {
        "id": 1,
        "status": "active",
        "created_at": "2026-08-31T12:00:00.000000Z"
    }
}
```

#### Response Errors
- `401 Unauthorized`: Authentication token missing, expired, or revoked.
- `403 Forbidden`: User does not have sufficient role permissions (`Authenticated User (`student`, `staff`, `admin`)`).
- `404 Not Found`: Target resource was not found.

#### Example cURL
```bash
curl -X GET "https://lostandfound.wollo.edu.et/api/v1/auth/me" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer YOUR_SANCTUM_TOKEN"
```

---

### `POST /api/v1/auth/logout`
<!-- source: routes/api.php -->
<!-- source: App/Http/Controllers/Api/V1/Auth/LogoutController.php -->
**Route Name:** `None`  
**Controller Action:** `\App\Http\Controllers\Api\V1\Auth\LogoutController@logout`  
**Middleware Stack:** `api, auth:sanctum`  
**Policy Enforcement:** None (Guarded by Middleware / Ownership check)  
**Required Role(s):** Authenticated User (`student`, `staff`, `admin`)  

#### Request
**Request Body (`application/json`):**
```json
{
    "data": "sample_payload"
}
```

#### Response `200 OK` / `201 Created`
```json
{
    "success": true,
    "message": "Request processed successfully.",
    "data": {
        "id": 1,
        "status": "active",
        "created_at": "2026-08-31T12:00:00.000000Z"
    }
}
```

#### Response Errors
- `401 Unauthorized`: Authentication token missing, expired, or revoked.
- `403 Forbidden`: User does not have sufficient role permissions (`Authenticated User (`student`, `staff`, `admin`)`).
- `404 Not Found`: Target resource was not found.
- `422 Unprocessable Entity`: Request body failed FormRequest validation.

#### Example cURL
```bash
curl -X POST "https://lostandfound.wollo.edu.et/api/v1/auth/logout" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer YOUR_SANCTUM_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"sample_field": "value"}'
```

---

### `PUT /api/v1/auth/password`
<!-- source: routes/api.php -->
<!-- source: App/Http/Controllers/Api/V1/Auth/PasswordController.php -->
**Route Name:** `None`  
**Controller Action:** `\App\Http\Controllers\Api\V1\Auth\PasswordController@change`  
**Middleware Stack:** `api, auth:sanctum`  
**Policy Enforcement:** None (Guarded by Middleware / Ownership check)  
**Required Role(s):** Authenticated User (`student`, `staff`, `admin`)  

#### Request
**Request Body (`application/json`):**
```json
{
    "data": "sample_payload"
}
```

#### Response `200 OK` / `201 Created`
```json
{
    "success": true,
    "message": "Request processed successfully.",
    "data": {
        "id": 1,
        "status": "active",
        "created_at": "2026-08-31T12:00:00.000000Z"
    }
}
```

#### Response Errors
- `401 Unauthorized`: Authentication token missing, expired, or revoked.
- `403 Forbidden`: User does not have sufficient role permissions (`Authenticated User (`student`, `staff`, `admin`)`).
- `404 Not Found`: Target resource was not found.
- `422 Unprocessable Entity`: Request body failed FormRequest validation.

#### Example cURL
```bash
curl -X PUT "https://lostandfound.wollo.edu.et/api/v1/auth/password" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer YOUR_SANCTUM_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"sample_field": "value"}'
```

---

## Public Discovery & Catalog (PUBLIC)

### `GET /api/v1/public/items`
<!-- source: routes/api.php -->
<!-- source: App/Http/Controllers/Api/V1/Public/PublicItemController.php -->
**Route Name:** `None`  
**Controller Action:** `\App\Http\Controllers\Api\V1\Public\PublicItemController@index`  
**Middleware Stack:** `api`  
**Policy Enforcement:** `App\Policies\ItemPolicy`  
**Required Role(s):** Public (Guest)  

#### Request
**Query Parameters:**
- `search` (string, optional): Keyword search matching title/description.
- `category_id` (integer, optional): Filter by category ID.
- `campus_id` (integer, optional): Filter by campus ID.
- `status` (string, optional): Filter by item status (`reported`, `under_review`, `claimed`, etc.).
- `date_from` (date, optional): ISO8601 start date.
- `date_to` (date, optional): ISO8601 end date.
- `page` (integer, optional, default: 1): Page number.
- `per_page` (integer, optional, default: 15): Results per page.

#### Response `200 OK` / `201 Created`
```json
{
    "success": true,
    "message": "Request processed successfully.",
    "data": {
        "id": 1,
        "status": "active",
        "created_at": "2026-08-31T12:00:00.000000Z"
    }
}
```

#### Response Errors
- `401 Unauthorized`: Authentication token missing, expired, or revoked.
- `404 Not Found`: Target resource was not found.

#### Example cURL
```bash
curl -X GET "https://lostandfound.wollo.edu.et/api/v1/public/items" \
  -H "Accept: application/json"
```

---

### `GET /api/v1/public/items/{id}`
<!-- source: routes/api.php -->
<!-- source: App/Http/Controllers/Api/V1/Public/PublicItemController.php -->
**Route Name:** `None`  
**Controller Action:** `\App\Http\Controllers\Api\V1\Public\PublicItemController@show`  
**Middleware Stack:** `api`  
**Policy Enforcement:** `App\Policies\ItemPolicy`  
**Required Role(s):** Public (Guest)  

#### Request
**Path Parameters:**
- `id` (string/integer, required): Identifier of target resource.

**Query Parameters:**
- `search` (string, optional): Keyword search matching title/description.
- `category_id` (integer, optional): Filter by category ID.
- `campus_id` (integer, optional): Filter by campus ID.
- `status` (string, optional): Filter by item status (`reported`, `under_review`, `claimed`, etc.).
- `date_from` (date, optional): ISO8601 start date.
- `date_to` (date, optional): ISO8601 end date.
- `page` (integer, optional, default: 1): Page number.
- `per_page` (integer, optional, default: 15): Results per page.

#### Response `200 OK` / `201 Created`
```json
{
    "success": true,
    "message": "Request processed successfully.",
    "data": {
        "id": 1,
        "status": "active",
        "created_at": "2026-08-31T12:00:00.000000Z"
    }
}
```

#### Response Errors
- `401 Unauthorized`: Authentication token missing, expired, or revoked.
- `404 Not Found`: Target resource was not found.

#### Example cURL
```bash
curl -X GET "https://lostandfound.wollo.edu.et/api/v1/public/items/{id}" \
  -H "Accept: application/json"
```

---

### `GET /api/v1/public/categories`
<!-- source: routes/api.php -->
<!-- source: App/Http/Controllers/Api/V1/Public/CategoryController.php -->
**Route Name:** `None`  
**Controller Action:** `\App\Http\Controllers\Api\V1\Public\CategoryController@index`  
**Middleware Stack:** `api`  
**Policy Enforcement:** `App\Policies\CategoryPolicy`  
**Required Role(s):** Public (Guest)  

#### Request
#### Response `200 OK` / `201 Created`
```json
{
    "success": true,
    "message": "Request processed successfully.",
    "data": {
        "id": 1,
        "status": "active",
        "created_at": "2026-08-31T12:00:00.000000Z"
    }
}
```

#### Response Errors
- `401 Unauthorized`: Authentication token missing, expired, or revoked.
- `404 Not Found`: Target resource was not found.

#### Example cURL
```bash
curl -X GET "https://lostandfound.wollo.edu.et/api/v1/public/categories" \
  -H "Accept: application/json"
```

---

### `GET /api/v1/public/locations`
<!-- source: routes/api.php -->
<!-- source: App/Http/Controllers/Api/V1/Public/LocationController.php -->
**Route Name:** `None`  
**Controller Action:** `\App\Http\Controllers\Api\V1\Public\LocationController@index`  
**Middleware Stack:** `api`  
**Policy Enforcement:** None (Guarded by Middleware / Ownership check)  
**Required Role(s):** Public (Guest)  

#### Request
#### Response `200 OK` / `201 Created`
```json
{
    "success": true,
    "message": "Request processed successfully.",
    "data": {
        "id": 1,
        "status": "active",
        "created_at": "2026-08-31T12:00:00.000000Z"
    }
}
```

#### Response Errors
- `401 Unauthorized`: Authentication token missing, expired, or revoked.
- `404 Not Found`: Target resource was not found.

#### Example cURL
```bash
curl -X GET "https://lostandfound.wollo.edu.et/api/v1/public/locations" \
  -H "Accept: application/json"
```

---

### `GET /api/v1/public/track/{reference_code}`
<!-- source: routes/api.php -->
<!-- source: App/Http/Controllers/Api/V1/Public/TrackingController.php -->
**Route Name:** `None`  
**Controller Action:** `\App\Http\Controllers\Api\V1\Public\TrackingController@track`  
**Middleware Stack:** `api, throttle:20,1`  
**Policy Enforcement:** None (Guarded by Middleware / Ownership check)  
**Required Role(s):** Public (Guest)  

#### Request
**Path Parameters:**
- `reference_code` (string/integer, required): Identifier of target resource.

#### Response `200 OK` / `201 Created`
```json
{
    "success": true,
    "message": "Request processed successfully.",
    "data": {
        "id": 1,
        "status": "active",
        "created_at": "2026-08-31T12:00:00.000000Z"
    }
}
```

#### Response Errors
- `401 Unauthorized`: Authentication token missing, expired, or revoked.
- `404 Not Found`: Target resource was not found.

#### Example cURL
```bash
curl -X GET "https://lostandfound.wollo.edu.et/api/v1/public/track/{reference_code}" \
  -H "Accept: application/json"
```

---

## Handover & Returns (RETURNS)

### `GET /api/v1/returns/confirm-token/{token}`
<!-- source: routes/api.php -->
<!-- source: App/Http/Controllers/Api/V1/Returns/ReturnController.php -->
**Route Name:** `None`  
**Controller Action:** `\App\Http\Controllers\Api\V1\Returns\ReturnController@getByToken`  
**Middleware Stack:** `api`  
**Policy Enforcement:** `App\Policies\ReturnPolicy`  
**Required Role(s):** Public (Guest)  

#### Request
**Path Parameters:**
- `token` (string/integer, required): Identifier of target resource.

#### Response `200 OK` / `201 Created`
```json
{
    "success": true,
    "message": "Request processed successfully.",
    "data": {
        "id": 1,
        "status": "active",
        "created_at": "2026-08-31T12:00:00.000000Z"
    }
}
```

#### Response Errors
- `401 Unauthorized`: Authentication token missing, expired, or revoked.
- `404 Not Found`: Target resource was not found.

#### Example cURL
```bash
curl -X GET "https://lostandfound.wollo.edu.et/api/v1/returns/confirm-token/{token}" \
  -H "Accept: application/json"
```

---

### `POST /api/v1/returns/confirm-token/{token}`
<!-- source: routes/api.php -->
<!-- source: App/Http/Controllers/Api/V1/Returns/ReturnController.php -->
**Route Name:** `None`  
**Controller Action:** `\App\Http\Controllers\Api\V1\Returns\ReturnController@confirmByToken`  
**Middleware Stack:** `api`  
**Policy Enforcement:** `App\Policies\ReturnPolicy`  
**Required Role(s):** Public (Guest)  

#### Request
**Path Parameters:**
- `token` (string/integer, required): Identifier of target resource.

**Request Body (`application/json`):**
```json
{
    "claim_id": 3,
    "recipient_id_type": "student_id",
    "recipient_id_number": "UGR\/1234\/15",
    "notes": "Item handed over at Security Office."
}
```

#### Response `200 OK` / `201 Created`
```json
{
    "success": true,
    "message": "Request processed successfully.",
    "data": {
        "id": 1,
        "status": "active",
        "created_at": "2026-08-31T12:00:00.000000Z"
    }
}
```

#### Response Errors
- `401 Unauthorized`: Authentication token missing, expired, or revoked.
- `404 Not Found`: Target resource was not found.
- `422 Unprocessable Entity`: Request body failed FormRequest validation.

#### Example cURL
```bash
curl -X POST "https://lostandfound.wollo.edu.et/api/v1/returns/confirm-token/{token}" \
  -H "Accept: application/json" \
  -H "Content-Type: application/json" \
  -d '{"sample_field": "value"}'
```

---

### `GET /api/v1/returns/{id}`
<!-- source: routes/api.php -->
<!-- source: App/Http/Controllers/Api/V1/Returns/ReturnController.php -->
**Route Name:** `None`  
**Controller Action:** `\App\Http\Controllers\Api\V1\Returns\ReturnController@show`  
**Middleware Stack:** `api, auth:sanctum`  
**Policy Enforcement:** `App\Policies\ReturnPolicy`  
**Required Role(s):** Authenticated User (`student`, `staff`, `admin`)  

#### Request
**Path Parameters:**
- `id` (string/integer, required): Identifier of target resource.

#### Response `200 OK` / `201 Created`
```json
{
    "success": true,
    "message": "Request processed successfully.",
    "data": {
        "id": 1,
        "status": "active",
        "created_at": "2026-08-31T12:00:00.000000Z"
    }
}
```

#### Response Errors
- `401 Unauthorized`: Authentication token missing, expired, or revoked.
- `403 Forbidden`: User does not have sufficient role permissions (`Authenticated User (`student`, `staff`, `admin`)`).
- `404 Not Found`: Target resource was not found.

#### Example cURL
```bash
curl -X GET "https://lostandfound.wollo.edu.et/api/v1/returns/{id}" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer YOUR_SANCTUM_TOKEN"
```

---

### `POST /api/v1/returns/{id}/confirm`
<!-- source: routes/api.php -->
<!-- source: App/Http/Controllers/Api/V1/Returns/ReturnController.php -->
**Route Name:** `None`  
**Controller Action:** `\App\Http\Controllers\Api\V1\Returns\ReturnController@confirm`  
**Middleware Stack:** `api, auth:sanctum`  
**Policy Enforcement:** `App\Policies\ReturnPolicy`  
**Required Role(s):** Authenticated User (`student`, `staff`, `admin`)  

#### Request
**Path Parameters:**
- `id` (string/integer, required): Identifier of target resource.

**Request Body (`application/json`):**
```json
{
    "claim_id": 3,
    "recipient_id_type": "student_id",
    "recipient_id_number": "UGR\/1234\/15",
    "notes": "Item handed over at Security Office."
}
```

#### Response `200 OK` / `201 Created`
```json
{
    "success": true,
    "message": "Request processed successfully.",
    "data": {
        "id": 1,
        "status": "active",
        "created_at": "2026-08-31T12:00:00.000000Z"
    }
}
```

#### Response Errors
- `401 Unauthorized`: Authentication token missing, expired, or revoked.
- `403 Forbidden`: User does not have sufficient role permissions (`Authenticated User (`student`, `staff`, `admin`)`).
- `404 Not Found`: Target resource was not found.
- `422 Unprocessable Entity`: Request body failed FormRequest validation.

#### Example cURL
```bash
curl -X POST "https://lostandfound.wollo.edu.et/api/v1/returns/{id}/confirm" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer YOUR_SANCTUM_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"sample_field": "value"}'
```

---

### `GET /api/v1/returns`
<!-- source: routes/api.php -->
<!-- source: App/Http/Controllers/Api/V1/Returns/ReturnController.php -->
**Route Name:** `None`  
**Controller Action:** `\App\Http\Controllers\Api\V1\Returns\ReturnController@index`  
**Middleware Stack:** `api, auth:sanctum, role:staff,admin`  
**Policy Enforcement:** `App\Policies\ReturnPolicy`  
**Required Role(s):** `staff`, `admin`  

#### Request
#### Response `200 OK` / `201 Created`
```json
{
    "success": true,
    "message": "Request processed successfully.",
    "data": {
        "id": 1,
        "status": "active",
        "created_at": "2026-08-31T12:00:00.000000Z"
    }
}
```

#### Response Errors
- `401 Unauthorized`: Authentication token missing, expired, or revoked.
- `403 Forbidden`: User does not have sufficient role permissions (``staff`, `admin``).
- `404 Not Found`: Target resource was not found.

#### Example cURL
```bash
curl -X GET "https://lostandfound.wollo.edu.et/api/v1/returns" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer YOUR_SANCTUM_TOKEN"
```

---

### `POST /api/v1/returns`
<!-- source: routes/api.php -->
<!-- source: App/Http/Controllers/Api/V1/Returns/ReturnController.php -->
**Route Name:** `None`  
**Controller Action:** `\App\Http\Controllers\Api\V1\Returns\ReturnController@store`  
**Middleware Stack:** `api, auth:sanctum, role:staff,admin`  
**Policy Enforcement:** `App\Policies\ReturnPolicy`  
**Required Role(s):** `staff`, `admin`  

#### Request
**Request Body (`application/json`):**
```json
{
    "claim_id": 3,
    "recipient_id_type": "student_id",
    "recipient_id_number": "UGR\/1234\/15",
    "notes": "Item handed over at Security Office."
}
```

#### Response `200 OK` / `201 Created`
```json
{
    "success": true,
    "message": "Request processed successfully.",
    "data": {
        "id": 1,
        "status": "active",
        "created_at": "2026-08-31T12:00:00.000000Z"
    }
}
```

#### Response Errors
- `401 Unauthorized`: Authentication token missing, expired, or revoked.
- `403 Forbidden`: User does not have sufficient role permissions (``staff`, `admin``).
- `404 Not Found`: Target resource was not found.
- `422 Unprocessable Entity`: Request body failed FormRequest validation.

#### Example cURL
```bash
curl -X POST "https://lostandfound.wollo.edu.et/api/v1/returns" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer YOUR_SANCTUM_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"sample_field": "value"}'
```

---

### `GET /api/v1/returns/export/csv`
<!-- source: routes/api.php -->
<!-- source: App/Http/Controllers/Api/V1/Returns/ReturnController.php -->
**Route Name:** `None`  
**Controller Action:** `\App\Http\Controllers\Api\V1\Returns\ReturnController@exportCsv`  
**Middleware Stack:** `api, auth:sanctum, role:staff,admin`  
**Policy Enforcement:** `App\Policies\ReturnPolicy`  
**Required Role(s):** `staff`, `admin`  

#### Request
#### Response `200 OK` / `201 Created`
```json
{
    "success": true,
    "message": "Request processed successfully.",
    "data": {
        "id": 1,
        "status": "active",
        "created_at": "2026-08-31T12:00:00.000000Z"
    }
}
```

#### Response Errors
- `401 Unauthorized`: Authentication token missing, expired, or revoked.
- `403 Forbidden`: User does not have sufficient role permissions (``staff`, `admin``).
- `404 Not Found`: Target resource was not found.

#### Example cURL
```bash
curl -X GET "https://lostandfound.wollo.edu.et/api/v1/returns/export/csv" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer YOUR_SANCTUM_TOKEN"
```

---

## User Profile & Avatar (PROFILE)

### `PUT /api/v1/profile`
<!-- source: routes/api.php -->
<!-- source: App/Http/Controllers/Api/V1/ProfileController.php -->
**Route Name:** `None`  
**Controller Action:** `\App\Http\Controllers\Api\V1\ProfileController@update`  
**Middleware Stack:** `api, auth:sanctum`  
**Policy Enforcement:** None (Guarded by Middleware / Ownership check)  
**Required Role(s):** Authenticated User (`student`, `staff`, `admin`)  

#### Request
**Request Body (`application/json`):**
```json
{
    "data": "sample_payload"
}
```

#### Response `200 OK` / `201 Created`
```json
{
    "success": true,
    "message": "Request processed successfully.",
    "data": {
        "id": 1,
        "status": "active",
        "created_at": "2026-08-31T12:00:00.000000Z"
    }
}
```

#### Response Errors
- `401 Unauthorized`: Authentication token missing, expired, or revoked.
- `403 Forbidden`: User does not have sufficient role permissions (`Authenticated User (`student`, `staff`, `admin`)`).
- `404 Not Found`: Target resource was not found.
- `422 Unprocessable Entity`: Request body failed FormRequest validation.

#### Example cURL
```bash
curl -X PUT "https://lostandfound.wollo.edu.et/api/v1/profile" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer YOUR_SANCTUM_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"sample_field": "value"}'
```

---

### `POST /api/v1/profile/avatar`
<!-- source: routes/api.php -->
<!-- source: App/Http/Controllers/Api/V1/ProfileController.php -->
**Route Name:** `None`  
**Controller Action:** `\App\Http\Controllers\Api\V1\ProfileController@uploadAvatar`  
**Middleware Stack:** `api, auth:sanctum`  
**Policy Enforcement:** None (Guarded by Middleware / Ownership check)  
**Required Role(s):** Authenticated User (`student`, `staff`, `admin`)  

#### Request
**Request Body (`application/json`):**
```json
{
    "data": "sample_payload"
}
```

#### Response `200 OK` / `201 Created`
```json
{
    "success": true,
    "message": "Request processed successfully.",
    "data": {
        "id": 1,
        "status": "active",
        "created_at": "2026-08-31T12:00:00.000000Z"
    }
}
```

#### Response Errors
- `401 Unauthorized`: Authentication token missing, expired, or revoked.
- `403 Forbidden`: User does not have sufficient role permissions (`Authenticated User (`student`, `staff`, `admin`)`).
- `404 Not Found`: Target resource was not found.
- `422 Unprocessable Entity`: Request body failed FormRequest validation.

#### Example cURL
```bash
curl -X POST "https://lostandfound.wollo.edu.et/api/v1/profile/avatar" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer YOUR_SANCTUM_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"sample_field": "value"}'
```

---

## Items & Photo Management (ITEMS)

### `POST /api/v1/items/check-duplicate`
<!-- source: routes/api.php -->
<!-- source: App/Http/Controllers/Api/V1/Items/ItemController.php -->
**Route Name:** `None`  
**Controller Action:** `\App\Http\Controllers\Api\V1\Items\ItemController@checkDuplicate`  
**Middleware Stack:** `api, auth:sanctum`  
**Policy Enforcement:** `App\Policies\ItemPolicy`  
**Required Role(s):** Authenticated User (`student`, `staff`, `admin`)  

#### Request
**Request Body (`application/json`):**
```json
{
    "data": "sample_payload"
}
```

#### Response `200 OK` / `201 Created`
```json
{
    "success": true,
    "message": "Request processed successfully.",
    "data": {
        "id": 1,
        "status": "active",
        "created_at": "2026-08-31T12:00:00.000000Z"
    }
}
```

#### Response Errors
- `401 Unauthorized`: Authentication token missing, expired, or revoked.
- `403 Forbidden`: User does not have sufficient role permissions (`Authenticated User (`student`, `staff`, `admin`)`).
- `404 Not Found`: Target resource was not found.
- `422 Unprocessable Entity`: Request body failed FormRequest validation.

#### Example cURL
```bash
curl -X POST "https://lostandfound.wollo.edu.et/api/v1/items/check-duplicate" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer YOUR_SANCTUM_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"sample_field": "value"}'
```

---

### `GET /api/v1/items`
<!-- source: routes/api.php -->
<!-- source: App/Http/Controllers/Api/V1/Items/ItemController.php -->
**Route Name:** `None`  
**Controller Action:** `\App\Http\Controllers\Api\V1\Items\ItemController@index`  
**Middleware Stack:** `api, auth:sanctum`  
**Policy Enforcement:** `App\Policies\ItemPolicy`  
**Required Role(s):** Authenticated User (`student`, `staff`, `admin`)  

#### Request
**Query Parameters:**
- `search` (string, optional): Keyword search matching title/description.
- `category_id` (integer, optional): Filter by category ID.
- `campus_id` (integer, optional): Filter by campus ID.
- `status` (string, optional): Filter by item status (`reported`, `under_review`, `claimed`, etc.).
- `date_from` (date, optional): ISO8601 start date.
- `date_to` (date, optional): ISO8601 end date.
- `page` (integer, optional, default: 1): Page number.
- `per_page` (integer, optional, default: 15): Results per page.

#### Response `200 OK` / `201 Created`
```json
{
    "success": true,
    "message": "Request processed successfully.",
    "data": {
        "id": 1,
        "status": "active",
        "created_at": "2026-08-31T12:00:00.000000Z"
    }
}
```

#### Response Errors
- `401 Unauthorized`: Authentication token missing, expired, or revoked.
- `403 Forbidden`: User does not have sufficient role permissions (`Authenticated User (`student`, `staff`, `admin`)`).
- `404 Not Found`: Target resource was not found.

#### Example cURL
```bash
curl -X GET "https://lostandfound.wollo.edu.et/api/v1/items" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer YOUR_SANCTUM_TOKEN"
```

---

### `POST /api/v1/items/lost`
<!-- source: routes/api.php -->
<!-- source: App/Http/Controllers/Api/V1/Items/ItemController.php -->
**Route Name:** `None`  
**Controller Action:** `\App\Http\Controllers\Api\V1\Items\ItemController@storeLost`  
**Middleware Stack:** `api, auth:sanctum`  
**Policy Enforcement:** `App\Policies\ItemPolicy`  
**Required Role(s):** Authenticated User (`student`, `staff`, `admin`)  

#### Request
**Request Body (`application/json`):**
```json
{
    "title": "HP Laptop 15-inch Grey",
    "description": "Lost near Library 2nd floor study area.",
    "category_id": 2,
    "campus_id": 1,
    "location_id": 4,
    "date_lost_or_found": "2026-08-30",
    "identifying_features": "University sticker on the lid"
}
```

#### Response `200 OK` / `201 Created`
```json
{
    "success": true,
    "message": "Request processed successfully.",
    "data": {
        "id": 1,
        "status": "active",
        "created_at": "2026-08-31T12:00:00.000000Z"
    }
}
```

#### Response Errors
- `401 Unauthorized`: Authentication token missing, expired, or revoked.
- `403 Forbidden`: User does not have sufficient role permissions (`Authenticated User (`student`, `staff`, `admin`)`).
- `404 Not Found`: Target resource was not found.
- `422 Unprocessable Entity`: Request body failed FormRequest validation.

#### Example cURL
```bash
curl -X POST "https://lostandfound.wollo.edu.et/api/v1/items/lost" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer YOUR_SANCTUM_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"sample_field": "value"}'
```

---

### `POST /api/v1/items/found`
<!-- source: routes/api.php -->
<!-- source: App/Http/Controllers/Api/V1/Items/ItemController.php -->
**Route Name:** `None`  
**Controller Action:** `\App\Http\Controllers\Api\V1\Items\ItemController@storeFound`  
**Middleware Stack:** `api, auth:sanctum`  
**Policy Enforcement:** `App\Policies\ItemPolicy`  
**Required Role(s):** Authenticated User (`student`, `staff`, `admin`)  

#### Request
**Request Body (`application/json`):**
```json
{
    "title": "Black Leather Wallet",
    "description": "Found near Cafeteria entrance.",
    "category_id": 1,
    "campus_id": 1,
    "location_id": 2,
    "date_lost_or_found": "2026-08-31",
    "storage_location_id": 1
}
```

#### Response `200 OK` / `201 Created`
```json
{
    "success": true,
    "message": "Request processed successfully.",
    "data": {
        "id": 1,
        "status": "active",
        "created_at": "2026-08-31T12:00:00.000000Z"
    }
}
```

#### Response Errors
- `401 Unauthorized`: Authentication token missing, expired, or revoked.
- `403 Forbidden`: User does not have sufficient role permissions (`Authenticated User (`student`, `staff`, `admin`)`).
- `404 Not Found`: Target resource was not found.
- `422 Unprocessable Entity`: Request body failed FormRequest validation.

#### Example cURL
```bash
curl -X POST "https://lostandfound.wollo.edu.et/api/v1/items/found" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer YOUR_SANCTUM_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"sample_field": "value"}'
```

---

### `GET /api/v1/items/{id}`
<!-- source: routes/api.php -->
<!-- source: App/Http/Controllers/Api/V1/Items/ItemController.php -->
**Route Name:** `None`  
**Controller Action:** `\App\Http\Controllers\Api\V1\Items\ItemController@show`  
**Middleware Stack:** `api, auth:sanctum`  
**Policy Enforcement:** `App\Policies\ItemPolicy`  
**Required Role(s):** Authenticated User (`student`, `staff`, `admin`)  

#### Request
**Path Parameters:**
- `id` (string/integer, required): Identifier of target resource.

**Query Parameters:**
- `search` (string, optional): Keyword search matching title/description.
- `category_id` (integer, optional): Filter by category ID.
- `campus_id` (integer, optional): Filter by campus ID.
- `status` (string, optional): Filter by item status (`reported`, `under_review`, `claimed`, etc.).
- `date_from` (date, optional): ISO8601 start date.
- `date_to` (date, optional): ISO8601 end date.
- `page` (integer, optional, default: 1): Page number.
- `per_page` (integer, optional, default: 15): Results per page.

#### Response `200 OK` / `201 Created`
```json
{
    "success": true,
    "message": "Request processed successfully.",
    "data": {
        "id": 1,
        "status": "active",
        "created_at": "2026-08-31T12:00:00.000000Z"
    }
}
```

#### Response Errors
- `401 Unauthorized`: Authentication token missing, expired, or revoked.
- `403 Forbidden`: User does not have sufficient role permissions (`Authenticated User (`student`, `staff`, `admin`)`).
- `404 Not Found`: Target resource was not found.

#### Example cURL
```bash
curl -X GET "https://lostandfound.wollo.edu.et/api/v1/items/{id}" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer YOUR_SANCTUM_TOKEN"
```

---

### `PUT /api/v1/items/{id}`
<!-- source: routes/api.php -->
<!-- source: App/Http/Controllers/Api/V1/Items/ItemController.php -->
**Route Name:** `None`  
**Controller Action:** `\App\Http\Controllers\Api\V1\Items\ItemController@update`  
**Middleware Stack:** `api, auth:sanctum`  
**Policy Enforcement:** `App\Policies\ItemPolicy`  
**Required Role(s):** Authenticated User (`student`, `staff`, `admin`)  

#### Request
**Path Parameters:**
- `id` (string/integer, required): Identifier of target resource.

**Request Body (`application/json`):**
```json
{
    "data": "sample_payload"
}
```

#### Response `200 OK` / `201 Created`
```json
{
    "success": true,
    "message": "Request processed successfully.",
    "data": {
        "id": 1,
        "status": "active",
        "created_at": "2026-08-31T12:00:00.000000Z"
    }
}
```

#### Response Errors
- `401 Unauthorized`: Authentication token missing, expired, or revoked.
- `403 Forbidden`: User does not have sufficient role permissions (`Authenticated User (`student`, `staff`, `admin`)`).
- `404 Not Found`: Target resource was not found.
- `422 Unprocessable Entity`: Request body failed FormRequest validation.

#### Example cURL
```bash
curl -X PUT "https://lostandfound.wollo.edu.et/api/v1/items/{id}" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer YOUR_SANCTUM_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"sample_field": "value"}'
```

---

### `DELETE /api/v1/items/{id}`
<!-- source: routes/api.php -->
<!-- source: App/Http/Controllers/Api/V1/Items/ItemController.php -->
**Route Name:** `None`  
**Controller Action:** `\App\Http\Controllers\Api\V1\Items\ItemController@destroy`  
**Middleware Stack:** `api, auth:sanctum`  
**Policy Enforcement:** `App\Policies\ItemPolicy`  
**Required Role(s):** Authenticated User (`student`, `staff`, `admin`)  

#### Request
**Path Parameters:**
- `id` (string/integer, required): Identifier of target resource.

#### Response `200 OK` / `201 Created`
```json
{
    "success": true,
    "message": "Request processed successfully.",
    "data": {
        "id": 1,
        "status": "active",
        "created_at": "2026-08-31T12:00:00.000000Z"
    }
}
```

#### Response Errors
- `401 Unauthorized`: Authentication token missing, expired, or revoked.
- `403 Forbidden`: User does not have sufficient role permissions (`Authenticated User (`student`, `staff`, `admin`)`).
- `404 Not Found`: Target resource was not found.

#### Example cURL
```bash
curl -X DELETE "https://lostandfound.wollo.edu.et/api/v1/items/{id}" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer YOUR_SANCTUM_TOKEN"
```

---

### `PATCH /api/v1/items/{id}/status`
<!-- source: routes/api.php -->
<!-- source: App/Http/Controllers/Api/V1/Items/ItemStatusController.php -->
**Route Name:** `None`  
**Controller Action:** `\App\Http\Controllers\Api\V1\Items\ItemStatusController@update`  
**Middleware Stack:** `api, auth:sanctum`  
**Policy Enforcement:** `App\Policies\ItemPolicy`  
**Required Role(s):** Authenticated User (`student`, `staff`, `admin`)  

#### Request
**Path Parameters:**
- `id` (string/integer, required): Identifier of target resource.

**Request Body (`application/json`):**
```json
{
    "data": "sample_payload"
}
```

#### Response `200 OK` / `201 Created`
```json
{
    "success": true,
    "message": "Request processed successfully.",
    "data": {
        "id": 1,
        "status": "active",
        "created_at": "2026-08-31T12:00:00.000000Z"
    }
}
```

#### Response Errors
- `401 Unauthorized`: Authentication token missing, expired, or revoked.
- `403 Forbidden`: User does not have sufficient role permissions (`Authenticated User (`student`, `staff`, `admin`)`).
- `404 Not Found`: Target resource was not found.
- `422 Unprocessable Entity`: Request body failed FormRequest validation.

#### Example cURL
```bash
curl -X PATCH "https://lostandfound.wollo.edu.et/api/v1/items/{id}/status" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer YOUR_SANCTUM_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"sample_field": "value"}'
```

---

### `PATCH /api/v1/items/{id}/withdraw`
<!-- source: routes/api.php -->
<!-- source: App/Http/Controllers/Api/V1/Items/ItemController.php -->
**Route Name:** `None`  
**Controller Action:** `\App\Http\Controllers\Api\V1\Items\ItemController@withdraw`  
**Middleware Stack:** `api, auth:sanctum`  
**Policy Enforcement:** `App\Policies\ItemPolicy`  
**Required Role(s):** Authenticated User (`student`, `staff`, `admin`)  

#### Request
**Path Parameters:**
- `id` (string/integer, required): Identifier of target resource.

**Request Body (`application/json`):**
```json
{
    "data": "sample_payload"
}
```

#### Response `200 OK` / `201 Created`
```json
{
    "success": true,
    "message": "Request processed successfully.",
    "data": {
        "id": 1,
        "status": "active",
        "created_at": "2026-08-31T12:00:00.000000Z"
    }
}
```

#### Response Errors
- `401 Unauthorized`: Authentication token missing, expired, or revoked.
- `403 Forbidden`: User does not have sufficient role permissions (`Authenticated User (`student`, `staff`, `admin`)`).
- `404 Not Found`: Target resource was not found.
- `422 Unprocessable Entity`: Request body failed FormRequest validation.

#### Example cURL
```bash
curl -X PATCH "https://lostandfound.wollo.edu.et/api/v1/items/{id}/withdraw" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer YOUR_SANCTUM_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"sample_field": "value"}'
```

---

### `POST /api/v1/items/{id}/photos`
<!-- source: routes/api.php -->
<!-- source: App/Http/Controllers/Api/V1/Items/ItemPhotoController.php -->
**Route Name:** `None`  
**Controller Action:** `\App\Http\Controllers\Api\V1\Items\ItemPhotoController@store`  
**Middleware Stack:** `api, auth:sanctum`  
**Policy Enforcement:** `App\Policies\ItemPolicy`  
**Required Role(s):** Authenticated User (`student`, `staff`, `admin`)  

#### Request
**Path Parameters:**
- `id` (string/integer, required): Identifier of target resource.

**Request Body (`application/json`):**
```json
{
    "data": "sample_payload"
}
```

#### Response `200 OK` / `201 Created`
```json
{
    "success": true,
    "message": "Request processed successfully.",
    "data": {
        "id": 1,
        "status": "active",
        "created_at": "2026-08-31T12:00:00.000000Z"
    }
}
```

#### Response Errors
- `401 Unauthorized`: Authentication token missing, expired, or revoked.
- `403 Forbidden`: User does not have sufficient role permissions (`Authenticated User (`student`, `staff`, `admin`)`).
- `404 Not Found`: Target resource was not found.
- `422 Unprocessable Entity`: Request body failed FormRequest validation.

#### Example cURL
```bash
curl -X POST "https://lostandfound.wollo.edu.et/api/v1/items/{id}/photos" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer YOUR_SANCTUM_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"sample_field": "value"}'
```

---

### `DELETE /api/v1/items/{id}/photos/{photoId}`
<!-- source: routes/api.php -->
<!-- source: App/Http/Controllers/Api/V1/Items/ItemPhotoController.php -->
**Route Name:** `None`  
**Controller Action:** `\App\Http\Controllers\Api\V1\Items\ItemPhotoController@destroy`  
**Middleware Stack:** `api, auth:sanctum`  
**Policy Enforcement:** `App\Policies\ItemPolicy`  
**Required Role(s):** Authenticated User (`student`, `staff`, `admin`)  

#### Request
**Path Parameters:**
- `id` (string/integer, required): Identifier of target resource.
- `photoId` (string/integer, required): Identifier of target resource.

#### Response `200 OK` / `201 Created`
```json
{
    "success": true,
    "message": "Request processed successfully.",
    "data": {
        "id": 1,
        "status": "active",
        "created_at": "2026-08-31T12:00:00.000000Z"
    }
}
```

#### Response Errors
- `401 Unauthorized`: Authentication token missing, expired, or revoked.
- `403 Forbidden`: User does not have sufficient role permissions (`Authenticated User (`student`, `staff`, `admin`)`).
- `404 Not Found`: Target resource was not found.

#### Example cURL
```bash
curl -X DELETE "https://lostandfound.wollo.edu.et/api/v1/items/{id}/photos/{photoId}" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer YOUR_SANCTUM_TOKEN"
```

---

## Claims & Evidence Verification (CLAIMS)

### `GET /api/v1/claims`
<!-- source: routes/api.php -->
<!-- source: App/Http/Controllers/Api/V1/Claims/ClaimController.php -->
**Route Name:** `None`  
**Controller Action:** `\App\Http\Controllers\Api\V1\Claims\ClaimController@index`  
**Middleware Stack:** `api, auth:sanctum`  
**Policy Enforcement:** `App\Policies\ClaimPolicy`  
**Required Role(s):** Authenticated User (`student`, `staff`, `admin`)  

#### Request
#### Response `200 OK` / `201 Created`
```json
{
    "success": true,
    "message": "Request processed successfully.",
    "data": {
        "id": 1,
        "status": "active",
        "created_at": "2026-08-31T12:00:00.000000Z"
    }
}
```

#### Response Errors
- `401 Unauthorized`: Authentication token missing, expired, or revoked.
- `403 Forbidden`: User does not have sufficient role permissions (`Authenticated User (`student`, `staff`, `admin`)`).
- `404 Not Found`: Target resource was not found.

#### Example cURL
```bash
curl -X GET "https://lostandfound.wollo.edu.et/api/v1/claims" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer YOUR_SANCTUM_TOKEN"
```

---

### `POST /api/v1/claims`
<!-- source: routes/api.php -->
<!-- source: App/Http/Controllers/Api/V1/Claims/ClaimController.php -->
**Route Name:** `None`  
**Controller Action:** `\App\Http\Controllers\Api\V1\Claims\ClaimController@store`  
**Middleware Stack:** `api, auth:sanctum`  
**Policy Enforcement:** `App\Policies\ClaimPolicy`  
**Required Role(s):** Authenticated User (`student`, `staff`, `admin`)  

#### Request
**Request Body (`application/json`):**
```json
{
    "item_id": 5,
    "claim_description": "Contains student ID card under name Abebe Kebede and 300 ETB.",
    "proof_attributes": {
        "id_card_verified": true
    }
}
```

#### Response `200 OK` / `201 Created`
```json
{
    "success": true,
    "message": "Request processed successfully.",
    "data": {
        "id": 1,
        "status": "active",
        "created_at": "2026-08-31T12:00:00.000000Z"
    }
}
```

#### Response Errors
- `401 Unauthorized`: Authentication token missing, expired, or revoked.
- `403 Forbidden`: User does not have sufficient role permissions (`Authenticated User (`student`, `staff`, `admin`)`).
- `404 Not Found`: Target resource was not found.
- `422 Unprocessable Entity`: Request body failed FormRequest validation.

#### Example cURL
```bash
curl -X POST "https://lostandfound.wollo.edu.et/api/v1/claims" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer YOUR_SANCTUM_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"sample_field": "value"}'
```

---

### `GET /api/v1/claims/{id}`
<!-- source: routes/api.php -->
<!-- source: App/Http/Controllers/Api/V1/Claims/ClaimController.php -->
**Route Name:** `None`  
**Controller Action:** `\App\Http\Controllers\Api\V1\Claims\ClaimController@show`  
**Middleware Stack:** `api, auth:sanctum`  
**Policy Enforcement:** `App\Policies\ClaimPolicy`  
**Required Role(s):** Authenticated User (`student`, `staff`, `admin`)  

#### Request
**Path Parameters:**
- `id` (string/integer, required): Identifier of target resource.

#### Response `200 OK` / `201 Created`
```json
{
    "success": true,
    "message": "Request processed successfully.",
    "data": {
        "id": 1,
        "status": "active",
        "created_at": "2026-08-31T12:00:00.000000Z"
    }
}
```

#### Response Errors
- `401 Unauthorized`: Authentication token missing, expired, or revoked.
- `403 Forbidden`: User does not have sufficient role permissions (`Authenticated User (`student`, `staff`, `admin`)`).
- `404 Not Found`: Target resource was not found.

#### Example cURL
```bash
curl -X GET "https://lostandfound.wollo.edu.et/api/v1/claims/{id}" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer YOUR_SANCTUM_TOKEN"
```

---

### `POST /api/v1/claims/{id}/review`
<!-- source: routes/api.php -->
<!-- source: App/Http/Controllers/Api/V1/Claims/ClaimReviewController.php -->
**Route Name:** `None`  
**Controller Action:** `\App\Http\Controllers\Api\V1\Claims\ClaimReviewController@review`  
**Middleware Stack:** `api, auth:sanctum, role:staff,admin`  
**Policy Enforcement:** `App\Policies\ClaimPolicy`  
**Required Role(s):** `staff`, `admin`  

#### Request
**Path Parameters:**
- `id` (string/integer, required): Identifier of target resource.

**Request Body (`application/json`):**
```json
{
    "item_id": 5,
    "claim_description": "Contains student ID card under name Abebe Kebede and 300 ETB.",
    "proof_attributes": {
        "id_card_verified": true
    }
}
```

#### Response `200 OK` / `201 Created`
```json
{
    "success": true,
    "message": "Request processed successfully.",
    "data": {
        "id": 1,
        "status": "active",
        "created_at": "2026-08-31T12:00:00.000000Z"
    }
}
```

#### Response Errors
- `401 Unauthorized`: Authentication token missing, expired, or revoked.
- `403 Forbidden`: User does not have sufficient role permissions (``staff`, `admin``).
- `404 Not Found`: Target resource was not found.
- `422 Unprocessable Entity`: Request body failed FormRequest validation.

#### Example cURL
```bash
curl -X POST "https://lostandfound.wollo.edu.et/api/v1/claims/{id}/review" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer YOUR_SANCTUM_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"sample_field": "value"}'
```

---

### `POST /api/v1/claims/{id}/reverse`
<!-- source: routes/api.php -->
<!-- source: App/Http/Controllers/Api/V1/Claims/ClaimReviewController.php -->
**Route Name:** `None`  
**Controller Action:** `\App\Http\Controllers\Api\V1\Claims\ClaimReviewController@reverse`  
**Middleware Stack:** `api, auth:sanctum, role:staff,admin`  
**Policy Enforcement:** `App\Policies\ClaimPolicy`  
**Required Role(s):** `staff`, `admin`  

#### Request
**Path Parameters:**
- `id` (string/integer, required): Identifier of target resource.

**Request Body (`application/json`):**
```json
{
    "item_id": 5,
    "claim_description": "Contains student ID card under name Abebe Kebede and 300 ETB.",
    "proof_attributes": {
        "id_card_verified": true
    }
}
```

#### Response `200 OK` / `201 Created`
```json
{
    "success": true,
    "message": "Request processed successfully.",
    "data": {
        "id": 1,
        "status": "active",
        "created_at": "2026-08-31T12:00:00.000000Z"
    }
}
```

#### Response Errors
- `401 Unauthorized`: Authentication token missing, expired, or revoked.
- `403 Forbidden`: User does not have sufficient role permissions (``staff`, `admin``).
- `404 Not Found`: Target resource was not found.
- `422 Unprocessable Entity`: Request body failed FormRequest validation.

#### Example cURL
```bash
curl -X POST "https://lostandfound.wollo.edu.et/api/v1/claims/{id}/reverse" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer YOUR_SANCTUM_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"sample_field": "value"}'
```

---

## Notifications & Preferences (NOTIFICATIONS)

### `GET /api/v1/notifications/stream`
<!-- source: routes/api.php -->
<!-- source: App/Http/Controllers/Api/V1/Notifications/RealtimeNotificationController.php -->
**Route Name:** `None`  
**Controller Action:** `\App\Http\Controllers\Api\V1\Notifications\RealtimeNotificationController@stream`  
**Middleware Stack:** `api, auth:sanctum`  
**Policy Enforcement:** None (Guarded by Middleware / Ownership check)  
**Required Role(s):** Authenticated User (`student`, `staff`, `admin`)  

#### Request
#### Response `200 OK` / `201 Created`
```json
{
    "success": true,
    "message": "Request processed successfully.",
    "data": {
        "id": 1,
        "status": "active",
        "created_at": "2026-08-31T12:00:00.000000Z"
    }
}
```

#### Response Errors
- `401 Unauthorized`: Authentication token missing, expired, or revoked.
- `403 Forbidden`: User does not have sufficient role permissions (`Authenticated User (`student`, `staff`, `admin`)`).
- `404 Not Found`: Target resource was not found.

#### Example cURL
```bash
curl -X GET "https://lostandfound.wollo.edu.et/api/v1/notifications/stream" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer YOUR_SANCTUM_TOKEN"
```

---

### `GET /api/v1/notifications`
<!-- source: routes/api.php -->
<!-- source: App/Http/Controllers/Api/V1/Notifications/NotificationController.php -->
**Route Name:** `None`  
**Controller Action:** `\App\Http\Controllers\Api\V1\Notifications\NotificationController@index`  
**Middleware Stack:** `api, auth:sanctum`  
**Policy Enforcement:** None (Guarded by Middleware / Ownership check)  
**Required Role(s):** Authenticated User (`student`, `staff`, `admin`)  

#### Request
#### Response `200 OK` / `201 Created`
```json
{
    "success": true,
    "message": "Request processed successfully.",
    "data": {
        "id": 1,
        "status": "active",
        "created_at": "2026-08-31T12:00:00.000000Z"
    }
}
```

#### Response Errors
- `401 Unauthorized`: Authentication token missing, expired, or revoked.
- `403 Forbidden`: User does not have sufficient role permissions (`Authenticated User (`student`, `staff`, `admin`)`).
- `404 Not Found`: Target resource was not found.

#### Example cURL
```bash
curl -X GET "https://lostandfound.wollo.edu.et/api/v1/notifications" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer YOUR_SANCTUM_TOKEN"
```

---

### `PATCH /api/v1/notifications/{id}/read`
<!-- source: routes/api.php -->
<!-- source: App/Http/Controllers/Api/V1/Notifications/NotificationController.php -->
**Route Name:** `None`  
**Controller Action:** `\App\Http\Controllers\Api\V1\Notifications\NotificationController@markAsRead`  
**Middleware Stack:** `api, auth:sanctum`  
**Policy Enforcement:** None (Guarded by Middleware / Ownership check)  
**Required Role(s):** Authenticated User (`student`, `staff`, `admin`)  

#### Request
**Path Parameters:**
- `id` (string/integer, required): Identifier of target resource.

**Request Body (`application/json`):**
```json
{
    "data": "sample_payload"
}
```

#### Response `200 OK` / `201 Created`
```json
{
    "success": true,
    "message": "Request processed successfully.",
    "data": {
        "id": 1,
        "status": "active",
        "created_at": "2026-08-31T12:00:00.000000Z"
    }
}
```

#### Response Errors
- `401 Unauthorized`: Authentication token missing, expired, or revoked.
- `403 Forbidden`: User does not have sufficient role permissions (`Authenticated User (`student`, `staff`, `admin`)`).
- `404 Not Found`: Target resource was not found.
- `422 Unprocessable Entity`: Request body failed FormRequest validation.

#### Example cURL
```bash
curl -X PATCH "https://lostandfound.wollo.edu.et/api/v1/notifications/{id}/read" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer YOUR_SANCTUM_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"sample_field": "value"}'
```

---

### `PATCH /api/v1/notifications/read-all`
<!-- source: routes/api.php -->
<!-- source: App/Http/Controllers/Api/V1/Notifications/NotificationController.php -->
**Route Name:** `None`  
**Controller Action:** `\App\Http\Controllers\Api\V1\Notifications\NotificationController@markAllAsRead`  
**Middleware Stack:** `api, auth:sanctum`  
**Policy Enforcement:** None (Guarded by Middleware / Ownership check)  
**Required Role(s):** Authenticated User (`student`, `staff`, `admin`)  

#### Request
**Request Body (`application/json`):**
```json
{
    "data": "sample_payload"
}
```

#### Response `200 OK` / `201 Created`
```json
{
    "success": true,
    "message": "Request processed successfully.",
    "data": {
        "id": 1,
        "status": "active",
        "created_at": "2026-08-31T12:00:00.000000Z"
    }
}
```

#### Response Errors
- `401 Unauthorized`: Authentication token missing, expired, or revoked.
- `403 Forbidden`: User does not have sufficient role permissions (`Authenticated User (`student`, `staff`, `admin`)`).
- `404 Not Found`: Target resource was not found.
- `422 Unprocessable Entity`: Request body failed FormRequest validation.

#### Example cURL
```bash
curl -X PATCH "https://lostandfound.wollo.edu.et/api/v1/notifications/read-all" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer YOUR_SANCTUM_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"sample_field": "value"}'
```

---

### `GET /api/v1/notifications/preferences`
<!-- source: routes/api.php -->
<!-- source: App/Http/Controllers/Api/V1/Notifications/NotificationPreferenceController.php -->
**Route Name:** `None`  
**Controller Action:** `\App\Http\Controllers\Api\V1\Notifications\NotificationPreferenceController@index`  
**Middleware Stack:** `api, auth:sanctum`  
**Policy Enforcement:** None (Guarded by Middleware / Ownership check)  
**Required Role(s):** Authenticated User (`student`, `staff`, `admin`)  

#### Request
#### Response `200 OK` / `201 Created`
```json
{
    "success": true,
    "message": "Request processed successfully.",
    "data": {
        "id": 1,
        "status": "active",
        "created_at": "2026-08-31T12:00:00.000000Z"
    }
}
```

#### Response Errors
- `401 Unauthorized`: Authentication token missing, expired, or revoked.
- `403 Forbidden`: User does not have sufficient role permissions (`Authenticated User (`student`, `staff`, `admin`)`).
- `404 Not Found`: Target resource was not found.

#### Example cURL
```bash
curl -X GET "https://lostandfound.wollo.edu.et/api/v1/notifications/preferences" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer YOUR_SANCTUM_TOKEN"
```

---

### `PUT /api/v1/notifications/preferences`
<!-- source: routes/api.php -->
<!-- source: App/Http/Controllers/Api/V1/Notifications/NotificationPreferenceController.php -->
**Route Name:** `None`  
**Controller Action:** `\App\Http\Controllers\Api\V1\Notifications\NotificationPreferenceController@update`  
**Middleware Stack:** `api, auth:sanctum`  
**Policy Enforcement:** None (Guarded by Middleware / Ownership check)  
**Required Role(s):** Authenticated User (`student`, `staff`, `admin`)  

#### Request
**Request Body (`application/json`):**
```json
{
    "data": "sample_payload"
}
```

#### Response `200 OK` / `201 Created`
```json
{
    "success": true,
    "message": "Request processed successfully.",
    "data": {
        "id": 1,
        "status": "active",
        "created_at": "2026-08-31T12:00:00.000000Z"
    }
}
```

#### Response Errors
- `401 Unauthorized`: Authentication token missing, expired, or revoked.
- `403 Forbidden`: User does not have sufficient role permissions (`Authenticated User (`student`, `staff`, `admin`)`).
- `404 Not Found`: Target resource was not found.
- `422 Unprocessable Entity`: Request body failed FormRequest validation.

#### Example cURL
```bash
curl -X PUT "https://lostandfound.wollo.edu.et/api/v1/notifications/preferences" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer YOUR_SANCTUM_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"sample_field": "value"}'
```

---

## Custody & Physical Storage (CUSTODY)

### `GET /api/v1/custody`
<!-- source: routes/api.php -->
<!-- source: App/Http/Controllers/Api/V1/Custody/CustodyController.php -->
**Route Name:** `None`  
**Controller Action:** `\App\Http\Controllers\Api\V1\Custody\CustodyController@index`  
**Middleware Stack:** `api, auth:sanctum, role:staff,admin`  
**Policy Enforcement:** `App\Policies\CustodyEventPolicy`  
**Required Role(s):** `staff`, `admin`  

#### Request
#### Response `200 OK` / `201 Created`
```json
{
    "success": true,
    "message": "Request processed successfully.",
    "data": {
        "id": 1,
        "status": "active",
        "created_at": "2026-08-31T12:00:00.000000Z"
    }
}
```

#### Response Errors
- `401 Unauthorized`: Authentication token missing, expired, or revoked.
- `403 Forbidden`: User does not have sufficient role permissions (``staff`, `admin``).
- `404 Not Found`: Target resource was not found.

#### Example cURL
```bash
curl -X GET "https://lostandfound.wollo.edu.et/api/v1/custody" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer YOUR_SANCTUM_TOKEN"
```

---

### `POST /api/v1/custody`
<!-- source: routes/api.php -->
<!-- source: App/Http/Controllers/Api/V1/Custody/CustodyController.php -->
**Route Name:** `None`  
**Controller Action:** `\App\Http\Controllers\Api\V1\Custody\CustodyController@store`  
**Middleware Stack:** `api, auth:sanctum, role:staff,admin`  
**Policy Enforcement:** `App\Policies\CustodyEventPolicy`  
**Required Role(s):** `staff`, `admin`  

#### Request
**Request Body (`application/json`):**
```json
{
    "data": "sample_payload"
}
```

#### Response `200 OK` / `201 Created`
```json
{
    "success": true,
    "message": "Request processed successfully.",
    "data": {
        "id": 1,
        "status": "active",
        "created_at": "2026-08-31T12:00:00.000000Z"
    }
}
```

#### Response Errors
- `401 Unauthorized`: Authentication token missing, expired, or revoked.
- `403 Forbidden`: User does not have sufficient role permissions (``staff`, `admin``).
- `404 Not Found`: Target resource was not found.
- `422 Unprocessable Entity`: Request body failed FormRequest validation.

#### Example cURL
```bash
curl -X POST "https://lostandfound.wollo.edu.et/api/v1/custody" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer YOUR_SANCTUM_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"sample_field": "value"}'
```

---

### `POST /api/v1/custody/items/{itemId}/move`
<!-- source: routes/api.php -->
<!-- source: App/Http/Controllers/Api/V1/Custody/CustodyController.php -->
**Route Name:** `None`  
**Controller Action:** `\App\Http\Controllers\Api\V1\Custody\CustodyController@move`  
**Middleware Stack:** `api, auth:sanctum, role:staff,admin`  
**Policy Enforcement:** `App\Policies\ItemPolicy`  
**Required Role(s):** `staff`, `admin`  

#### Request
**Path Parameters:**
- `itemId` (string/integer, required): Identifier of target resource.

**Request Body (`application/json`):**
```json
{
    "data": "sample_payload"
}
```

#### Response `200 OK` / `201 Created`
```json
{
    "success": true,
    "message": "Request processed successfully.",
    "data": {
        "id": 1,
        "status": "active",
        "created_at": "2026-08-31T12:00:00.000000Z"
    }
}
```

#### Response Errors
- `401 Unauthorized`: Authentication token missing, expired, or revoked.
- `403 Forbidden`: User does not have sufficient role permissions (``staff`, `admin``).
- `404 Not Found`: Target resource was not found.
- `422 Unprocessable Entity`: Request body failed FormRequest validation.

#### Example cURL
```bash
curl -X POST "https://lostandfound.wollo.edu.et/api/v1/custody/items/{itemId}/move" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer YOUR_SANCTUM_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"sample_field": "value"}'
```

---

### `GET /api/v1/custody/storage-locations`
<!-- source: routes/api.php -->
<!-- source: App/Http/Controllers/Api/V1/Custody/StorageLocationController.php -->
**Route Name:** `storage-locations.index`  
**Controller Action:** `\App\Http\Controllers\Api\V1\Custody\StorageLocationController@index`  
**Middleware Stack:** `api, auth:sanctum, role:staff,admin`  
**Policy Enforcement:** `App\Policies\CustodyEventPolicy`  
**Required Role(s):** `staff`, `admin`  

#### Request
#### Response `200 OK` / `201 Created`
```json
{
    "success": true,
    "message": "Request processed successfully.",
    "data": {
        "id": 1,
        "status": "active",
        "created_at": "2026-08-31T12:00:00.000000Z"
    }
}
```

#### Response Errors
- `401 Unauthorized`: Authentication token missing, expired, or revoked.
- `403 Forbidden`: User does not have sufficient role permissions (``staff`, `admin``).
- `404 Not Found`: Target resource was not found.

#### Example cURL
```bash
curl -X GET "https://lostandfound.wollo.edu.et/api/v1/custody/storage-locations" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer YOUR_SANCTUM_TOKEN"
```

---

### `POST /api/v1/custody/storage-locations`
<!-- source: routes/api.php -->
<!-- source: App/Http/Controllers/Api/V1/Custody/StorageLocationController.php -->
**Route Name:** `storage-locations.store`  
**Controller Action:** `\App\Http\Controllers\Api\V1\Custody\StorageLocationController@store`  
**Middleware Stack:** `api, auth:sanctum, role:staff,admin`  
**Policy Enforcement:** `App\Policies\CustodyEventPolicy`  
**Required Role(s):** `staff`, `admin`  

#### Request
**Request Body (`application/json`):**
```json
{
    "data": "sample_payload"
}
```

#### Response `200 OK` / `201 Created`
```json
{
    "success": true,
    "message": "Request processed successfully.",
    "data": {
        "id": 1,
        "status": "active",
        "created_at": "2026-08-31T12:00:00.000000Z"
    }
}
```

#### Response Errors
- `401 Unauthorized`: Authentication token missing, expired, or revoked.
- `403 Forbidden`: User does not have sufficient role permissions (``staff`, `admin``).
- `404 Not Found`: Target resource was not found.
- `422 Unprocessable Entity`: Request body failed FormRequest validation.

#### Example cURL
```bash
curl -X POST "https://lostandfound.wollo.edu.et/api/v1/custody/storage-locations" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer YOUR_SANCTUM_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"sample_field": "value"}'
```

---

### `GET /api/v1/custody/storage-locations/{storage_location}`
<!-- source: routes/api.php -->
<!-- source: App/Http/Controllers/Api/V1/Custody/StorageLocationController.php -->
**Route Name:** `storage-locations.show`  
**Controller Action:** `\App\Http\Controllers\Api\V1\Custody\StorageLocationController@show`  
**Middleware Stack:** `api, auth:sanctum, role:staff,admin`  
**Policy Enforcement:** `App\Policies\CustodyEventPolicy`  
**Required Role(s):** `staff`, `admin`  

#### Request
**Path Parameters:**
- `storage_location` (string/integer, required): Identifier of target resource.

#### Response `200 OK` / `201 Created`
```json
{
    "success": true,
    "message": "Request processed successfully.",
    "data": {
        "id": 1,
        "status": "active",
        "created_at": "2026-08-31T12:00:00.000000Z"
    }
}
```

#### Response Errors
- `401 Unauthorized`: Authentication token missing, expired, or revoked.
- `403 Forbidden`: User does not have sufficient role permissions (``staff`, `admin``).
- `404 Not Found`: Target resource was not found.

#### Example cURL
```bash
curl -X GET "https://lostandfound.wollo.edu.et/api/v1/custody/storage-locations/{storage_location}" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer YOUR_SANCTUM_TOKEN"
```

---

### `PUT|PATCH /api/v1/custody/storage-locations/{storage_location}`
<!-- source: routes/api.php -->
<!-- source: App/Http/Controllers/Api/V1/Custody/StorageLocationController.php -->
**Route Name:** `storage-locations.update`  
**Controller Action:** `\App\Http\Controllers\Api\V1\Custody\StorageLocationController@update`  
**Middleware Stack:** `api, auth:sanctum, role:staff,admin`  
**Policy Enforcement:** `App\Policies\CustodyEventPolicy`  
**Required Role(s):** `staff`, `admin`  

#### Request
**Path Parameters:**
- `storage_location` (string/integer, required): Identifier of target resource.

#### Response `200 OK` / `201 Created`
```json
{
    "success": true,
    "message": "Request processed successfully.",
    "data": {
        "id": 1,
        "status": "active",
        "created_at": "2026-08-31T12:00:00.000000Z"
    }
}
```

#### Response Errors
- `401 Unauthorized`: Authentication token missing, expired, or revoked.
- `403 Forbidden`: User does not have sufficient role permissions (``staff`, `admin``).
- `404 Not Found`: Target resource was not found.

#### Example cURL
```bash
curl -X PUT|PATCH "https://lostandfound.wollo.edu.et/api/v1/custody/storage-locations/{storage_location}" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer YOUR_SANCTUM_TOKEN"
```

---

### `DELETE /api/v1/custody/storage-locations/{storage_location}`
<!-- source: routes/api.php -->
<!-- source: App/Http/Controllers/Api/V1/Custody/StorageLocationController.php -->
**Route Name:** `storage-locations.destroy`  
**Controller Action:** `\App\Http\Controllers\Api\V1\Custody\StorageLocationController@destroy`  
**Middleware Stack:** `api, auth:sanctum, role:staff,admin`  
**Policy Enforcement:** `App\Policies\CustodyEventPolicy`  
**Required Role(s):** `staff`, `admin`  

#### Request
**Path Parameters:**
- `storage_location` (string/integer, required): Identifier of target resource.

#### Response `200 OK` / `201 Created`
```json
{
    "success": true,
    "message": "Request processed successfully.",
    "data": {
        "id": 1,
        "status": "active",
        "created_at": "2026-08-31T12:00:00.000000Z"
    }
}
```

#### Response Errors
- `401 Unauthorized`: Authentication token missing, expired, or revoked.
- `403 Forbidden`: User does not have sufficient role permissions (``staff`, `admin``).
- `404 Not Found`: Target resource was not found.

#### Example cURL
```bash
curl -X DELETE "https://lostandfound.wollo.edu.et/api/v1/custody/storage-locations/{storage_location}" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer YOUR_SANCTUM_TOKEN"
```

---

## Match Suggestions (MATCHING)

### `GET /api/v1/match-suggestions`
<!-- source: routes/api.php -->
<!-- source: App/Http/Controllers/Api/V1/Admin/MatchSuggestionController.php -->
**Route Name:** `None`  
**Controller Action:** `\App\Http\Controllers\Api\V1\Admin\MatchSuggestionController@index`  
**Middleware Stack:** `api, auth:sanctum, role:staff,admin`  
**Policy Enforcement:** None (Guarded by Middleware / Ownership check)  
**Required Role(s):** `staff`, `admin`  

#### Request
#### Response `200 OK` / `201 Created`
```json
{
    "success": true,
    "message": "Request processed successfully.",
    "data": {
        "id": 1,
        "status": "active",
        "created_at": "2026-08-31T12:00:00.000000Z"
    }
}
```

#### Response Errors
- `401 Unauthorized`: Authentication token missing, expired, or revoked.
- `403 Forbidden`: User does not have sufficient role permissions (``staff`, `admin``).
- `404 Not Found`: Target resource was not found.

#### Example cURL
```bash
curl -X GET "https://lostandfound.wollo.edu.et/api/v1/match-suggestions" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer YOUR_SANCTUM_TOKEN"
```

---

### `PATCH /api/v1/match-suggestions/{id}`
<!-- source: routes/api.php -->
<!-- source: App/Http/Controllers/Api/V1/Admin/MatchSuggestionController.php -->
**Route Name:** `None`  
**Controller Action:** `\App\Http\Controllers\Api\V1\Admin\MatchSuggestionController@update`  
**Middleware Stack:** `api, auth:sanctum, role:staff,admin`  
**Policy Enforcement:** None (Guarded by Middleware / Ownership check)  
**Required Role(s):** `staff`, `admin`  

#### Request
**Path Parameters:**
- `id` (string/integer, required): Identifier of target resource.

**Request Body (`application/json`):**
```json
{
    "data": "sample_payload"
}
```

#### Response `200 OK` / `201 Created`
```json
{
    "success": true,
    "message": "Request processed successfully.",
    "data": {
        "id": 1,
        "status": "active",
        "created_at": "2026-08-31T12:00:00.000000Z"
    }
}
```

#### Response Errors
- `401 Unauthorized`: Authentication token missing, expired, or revoked.
- `403 Forbidden`: User does not have sufficient role permissions (``staff`, `admin``).
- `404 Not Found`: Target resource was not found.
- `422 Unprocessable Entity`: Request body failed FormRequest validation.

#### Example cURL
```bash
curl -X PATCH "https://lostandfound.wollo.edu.et/api/v1/match-suggestions/{id}" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer YOUR_SANCTUM_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"sample_field": "value"}'
```

---

## Administration & Management (ADMIN)

### `GET /api/v1/admin/dashboard/statistics`
<!-- source: routes/api.php -->
<!-- source: App/Http/Controllers/Api/V1/Admin/DashboardController.php -->
**Route Name:** `None`  
**Controller Action:** `\App\Http\Controllers\Api\V1\Admin\DashboardController@statistics`  
**Middleware Stack:** `api, auth:sanctum, role:admin`  
**Policy Enforcement:** None (Guarded by Middleware / Ownership check)  
**Required Role(s):** `admin`  

#### Request
#### Response `200 OK` / `201 Created`
```json
{
    "success": true,
    "message": "Request processed successfully.",
    "data": {
        "id": 1,
        "status": "active",
        "created_at": "2026-08-31T12:00:00.000000Z"
    }
}
```

#### Response Errors
- `401 Unauthorized`: Authentication token missing, expired, or revoked.
- `403 Forbidden`: User does not have sufficient role permissions (``admin``).
- `404 Not Found`: Target resource was not found.

#### Example cURL
```bash
curl -X GET "https://lostandfound.wollo.edu.et/api/v1/admin/dashboard/statistics" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer YOUR_SANCTUM_TOKEN"
```

---

### `GET /api/v1/admin/campuses`
<!-- source: routes/api.php -->
<!-- source: App/Http/Controllers/Api/V1/Admin/CampusController.php -->
**Route Name:** `campuses.index`  
**Controller Action:** `\App\Http\Controllers\Api\V1\Admin\CampusController@index`  
**Middleware Stack:** `api, auth:sanctum, role:admin`  
**Policy Enforcement:** `App\Policies\CampusPolicy`  
**Required Role(s):** `admin`  

#### Request
#### Response `200 OK` / `201 Created`
```json
{
    "success": true,
    "message": "Request processed successfully.",
    "data": {
        "id": 1,
        "status": "active",
        "created_at": "2026-08-31T12:00:00.000000Z"
    }
}
```

#### Response Errors
- `401 Unauthorized`: Authentication token missing, expired, or revoked.
- `403 Forbidden`: User does not have sufficient role permissions (``admin``).
- `404 Not Found`: Target resource was not found.

#### Example cURL
```bash
curl -X GET "https://lostandfound.wollo.edu.et/api/v1/admin/campuses" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer YOUR_SANCTUM_TOKEN"
```

---

### `POST /api/v1/admin/campuses`
<!-- source: routes/api.php -->
<!-- source: App/Http/Controllers/Api/V1/Admin/CampusController.php -->
**Route Name:** `campuses.store`  
**Controller Action:** `\App\Http\Controllers\Api\V1\Admin\CampusController@store`  
**Middleware Stack:** `api, auth:sanctum, role:admin`  
**Policy Enforcement:** `App\Policies\CampusPolicy`  
**Required Role(s):** `admin`  

#### Request
**Request Body (`application/json`):**
```json
{
    "data": "sample_payload"
}
```

#### Response `200 OK` / `201 Created`
```json
{
    "success": true,
    "message": "Request processed successfully.",
    "data": {
        "id": 1,
        "status": "active",
        "created_at": "2026-08-31T12:00:00.000000Z"
    }
}
```

#### Response Errors
- `401 Unauthorized`: Authentication token missing, expired, or revoked.
- `403 Forbidden`: User does not have sufficient role permissions (``admin``).
- `404 Not Found`: Target resource was not found.
- `422 Unprocessable Entity`: Request body failed FormRequest validation.

#### Example cURL
```bash
curl -X POST "https://lostandfound.wollo.edu.et/api/v1/admin/campuses" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer YOUR_SANCTUM_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"sample_field": "value"}'
```

---

### `GET /api/v1/admin/campuses/{campus}`
<!-- source: routes/api.php -->
<!-- source: App/Http/Controllers/Api/V1/Admin/CampusController.php -->
**Route Name:** `campuses.show`  
**Controller Action:** `\App\Http\Controllers\Api\V1\Admin\CampusController@show`  
**Middleware Stack:** `api, auth:sanctum, role:admin`  
**Policy Enforcement:** `App\Policies\CampusPolicy`  
**Required Role(s):** `admin`  

#### Request
**Path Parameters:**
- `campus` (string/integer, required): Identifier of target resource.

#### Response `200 OK` / `201 Created`
```json
{
    "success": true,
    "message": "Request processed successfully.",
    "data": {
        "id": 1,
        "status": "active",
        "created_at": "2026-08-31T12:00:00.000000Z"
    }
}
```

#### Response Errors
- `401 Unauthorized`: Authentication token missing, expired, or revoked.
- `403 Forbidden`: User does not have sufficient role permissions (``admin``).
- `404 Not Found`: Target resource was not found.

#### Example cURL
```bash
curl -X GET "https://lostandfound.wollo.edu.et/api/v1/admin/campuses/{campus}" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer YOUR_SANCTUM_TOKEN"
```

---

### `PUT|PATCH /api/v1/admin/campuses/{campus}`
<!-- source: routes/api.php -->
<!-- source: App/Http/Controllers/Api/V1/Admin/CampusController.php -->
**Route Name:** `campuses.update`  
**Controller Action:** `\App\Http\Controllers\Api\V1\Admin\CampusController@update`  
**Middleware Stack:** `api, auth:sanctum, role:admin`  
**Policy Enforcement:** `App\Policies\CampusPolicy`  
**Required Role(s):** `admin`  

#### Request
**Path Parameters:**
- `campus` (string/integer, required): Identifier of target resource.

#### Response `200 OK` / `201 Created`
```json
{
    "success": true,
    "message": "Request processed successfully.",
    "data": {
        "id": 1,
        "status": "active",
        "created_at": "2026-08-31T12:00:00.000000Z"
    }
}
```

#### Response Errors
- `401 Unauthorized`: Authentication token missing, expired, or revoked.
- `403 Forbidden`: User does not have sufficient role permissions (``admin``).
- `404 Not Found`: Target resource was not found.

#### Example cURL
```bash
curl -X PUT|PATCH "https://lostandfound.wollo.edu.et/api/v1/admin/campuses/{campus}" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer YOUR_SANCTUM_TOKEN"
```

---

### `DELETE /api/v1/admin/campuses/{campus}`
<!-- source: routes/api.php -->
<!-- source: App/Http/Controllers/Api/V1/Admin/CampusController.php -->
**Route Name:** `campuses.destroy`  
**Controller Action:** `\App\Http\Controllers\Api\V1\Admin\CampusController@destroy`  
**Middleware Stack:** `api, auth:sanctum, role:admin`  
**Policy Enforcement:** `App\Policies\CampusPolicy`  
**Required Role(s):** `admin`  

#### Request
**Path Parameters:**
- `campus` (string/integer, required): Identifier of target resource.

#### Response `200 OK` / `201 Created`
```json
{
    "success": true,
    "message": "Request processed successfully.",
    "data": {
        "id": 1,
        "status": "active",
        "created_at": "2026-08-31T12:00:00.000000Z"
    }
}
```

#### Response Errors
- `401 Unauthorized`: Authentication token missing, expired, or revoked.
- `403 Forbidden`: User does not have sufficient role permissions (``admin``).
- `404 Not Found`: Target resource was not found.

#### Example cURL
```bash
curl -X DELETE "https://lostandfound.wollo.edu.et/api/v1/admin/campuses/{campus}" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer YOUR_SANCTUM_TOKEN"
```

---

### `PATCH /api/v1/admin/campuses/{id}/restore`
<!-- source: routes/api.php -->
<!-- source: App/Http/Controllers/Api/V1/Admin/CampusController.php -->
**Route Name:** `None`  
**Controller Action:** `\App\Http\Controllers\Api\V1\Admin\CampusController@restore`  
**Middleware Stack:** `api, auth:sanctum, role:admin`  
**Policy Enforcement:** `App\Policies\CampusPolicy`  
**Required Role(s):** `admin`  

#### Request
**Path Parameters:**
- `id` (string/integer, required): Identifier of target resource.

**Request Body (`application/json`):**
```json
{
    "data": "sample_payload"
}
```

#### Response `200 OK` / `201 Created`
```json
{
    "success": true,
    "message": "Request processed successfully.",
    "data": {
        "id": 1,
        "status": "active",
        "created_at": "2026-08-31T12:00:00.000000Z"
    }
}
```

#### Response Errors
- `401 Unauthorized`: Authentication token missing, expired, or revoked.
- `403 Forbidden`: User does not have sufficient role permissions (``admin``).
- `404 Not Found`: Target resource was not found.
- `422 Unprocessable Entity`: Request body failed FormRequest validation.

#### Example cURL
```bash
curl -X PATCH "https://lostandfound.wollo.edu.et/api/v1/admin/campuses/{id}/restore" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer YOUR_SANCTUM_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"sample_field": "value"}'
```

---

### `GET /api/v1/admin/organizational-units`
<!-- source: routes/api.php -->
<!-- source: App/Http/Controllers/Api/V1/Admin/OrganizationalUnitController.php -->
**Route Name:** `organizational-units.index`  
**Controller Action:** `\App\Http\Controllers\Api\V1\Admin\OrganizationalUnitController@index`  
**Middleware Stack:** `api, auth:sanctum, role:admin`  
**Policy Enforcement:** None (Guarded by Middleware / Ownership check)  
**Required Role(s):** `admin`  

#### Request
#### Response `200 OK` / `201 Created`
```json
{
    "success": true,
    "message": "Request processed successfully.",
    "data": {
        "id": 1,
        "status": "active",
        "created_at": "2026-08-31T12:00:00.000000Z"
    }
}
```

#### Response Errors
- `401 Unauthorized`: Authentication token missing, expired, or revoked.
- `403 Forbidden`: User does not have sufficient role permissions (``admin``).
- `404 Not Found`: Target resource was not found.

#### Example cURL
```bash
curl -X GET "https://lostandfound.wollo.edu.et/api/v1/admin/organizational-units" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer YOUR_SANCTUM_TOKEN"
```

---

### `POST /api/v1/admin/organizational-units`
<!-- source: routes/api.php -->
<!-- source: App/Http/Controllers/Api/V1/Admin/OrganizationalUnitController.php -->
**Route Name:** `organizational-units.store`  
**Controller Action:** `\App\Http\Controllers\Api\V1\Admin\OrganizationalUnitController@store`  
**Middleware Stack:** `api, auth:sanctum, role:admin`  
**Policy Enforcement:** None (Guarded by Middleware / Ownership check)  
**Required Role(s):** `admin`  

#### Request
**Request Body (`application/json`):**
```json
{
    "data": "sample_payload"
}
```

#### Response `200 OK` / `201 Created`
```json
{
    "success": true,
    "message": "Request processed successfully.",
    "data": {
        "id": 1,
        "status": "active",
        "created_at": "2026-08-31T12:00:00.000000Z"
    }
}
```

#### Response Errors
- `401 Unauthorized`: Authentication token missing, expired, or revoked.
- `403 Forbidden`: User does not have sufficient role permissions (``admin``).
- `404 Not Found`: Target resource was not found.
- `422 Unprocessable Entity`: Request body failed FormRequest validation.

#### Example cURL
```bash
curl -X POST "https://lostandfound.wollo.edu.et/api/v1/admin/organizational-units" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer YOUR_SANCTUM_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"sample_field": "value"}'
```

---

### `GET /api/v1/admin/organizational-units/{organizational_unit}`
<!-- source: routes/api.php -->
<!-- source: App/Http/Controllers/Api/V1/Admin/OrganizationalUnitController.php -->
**Route Name:** `organizational-units.show`  
**Controller Action:** `\App\Http\Controllers\Api\V1\Admin\OrganizationalUnitController@show`  
**Middleware Stack:** `api, auth:sanctum, role:admin`  
**Policy Enforcement:** None (Guarded by Middleware / Ownership check)  
**Required Role(s):** `admin`  

#### Request
**Path Parameters:**
- `organizational_unit` (string/integer, required): Identifier of target resource.

#### Response `200 OK` / `201 Created`
```json
{
    "success": true,
    "message": "Request processed successfully.",
    "data": {
        "id": 1,
        "status": "active",
        "created_at": "2026-08-31T12:00:00.000000Z"
    }
}
```

#### Response Errors
- `401 Unauthorized`: Authentication token missing, expired, or revoked.
- `403 Forbidden`: User does not have sufficient role permissions (``admin``).
- `404 Not Found`: Target resource was not found.

#### Example cURL
```bash
curl -X GET "https://lostandfound.wollo.edu.et/api/v1/admin/organizational-units/{organizational_unit}" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer YOUR_SANCTUM_TOKEN"
```

---

### `PUT|PATCH /api/v1/admin/organizational-units/{organizational_unit}`
<!-- source: routes/api.php -->
<!-- source: App/Http/Controllers/Api/V1/Admin/OrganizationalUnitController.php -->
**Route Name:** `organizational-units.update`  
**Controller Action:** `\App\Http\Controllers\Api\V1\Admin\OrganizationalUnitController@update`  
**Middleware Stack:** `api, auth:sanctum, role:admin`  
**Policy Enforcement:** None (Guarded by Middleware / Ownership check)  
**Required Role(s):** `admin`  

#### Request
**Path Parameters:**
- `organizational_unit` (string/integer, required): Identifier of target resource.

#### Response `200 OK` / `201 Created`
```json
{
    "success": true,
    "message": "Request processed successfully.",
    "data": {
        "id": 1,
        "status": "active",
        "created_at": "2026-08-31T12:00:00.000000Z"
    }
}
```

#### Response Errors
- `401 Unauthorized`: Authentication token missing, expired, or revoked.
- `403 Forbidden`: User does not have sufficient role permissions (``admin``).
- `404 Not Found`: Target resource was not found.

#### Example cURL
```bash
curl -X PUT|PATCH "https://lostandfound.wollo.edu.et/api/v1/admin/organizational-units/{organizational_unit}" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer YOUR_SANCTUM_TOKEN"
```

---

### `DELETE /api/v1/admin/organizational-units/{organizational_unit}`
<!-- source: routes/api.php -->
<!-- source: App/Http/Controllers/Api/V1/Admin/OrganizationalUnitController.php -->
**Route Name:** `organizational-units.destroy`  
**Controller Action:** `\App\Http\Controllers\Api\V1\Admin\OrganizationalUnitController@destroy`  
**Middleware Stack:** `api, auth:sanctum, role:admin`  
**Policy Enforcement:** None (Guarded by Middleware / Ownership check)  
**Required Role(s):** `admin`  

#### Request
**Path Parameters:**
- `organizational_unit` (string/integer, required): Identifier of target resource.

#### Response `200 OK` / `201 Created`
```json
{
    "success": true,
    "message": "Request processed successfully.",
    "data": {
        "id": 1,
        "status": "active",
        "created_at": "2026-08-31T12:00:00.000000Z"
    }
}
```

#### Response Errors
- `401 Unauthorized`: Authentication token missing, expired, or revoked.
- `403 Forbidden`: User does not have sufficient role permissions (``admin``).
- `404 Not Found`: Target resource was not found.

#### Example cURL
```bash
curl -X DELETE "https://lostandfound.wollo.edu.et/api/v1/admin/organizational-units/{organizational_unit}" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer YOUR_SANCTUM_TOKEN"
```

---

### `GET /api/v1/admin/organizational-unit-types`
<!-- source: routes/api.php -->
<!-- source: App/Http/Controllers/Api/V1/Admin/OrganizationalUnitTypeController.php -->
**Route Name:** `organizational-unit-types.index`  
**Controller Action:** `\App\Http\Controllers\Api\V1\Admin\OrganizationalUnitTypeController@index`  
**Middleware Stack:** `api, auth:sanctum, role:admin`  
**Policy Enforcement:** None (Guarded by Middleware / Ownership check)  
**Required Role(s):** `admin`  

#### Request
#### Response `200 OK` / `201 Created`
```json
{
    "success": true,
    "message": "Request processed successfully.",
    "data": {
        "id": 1,
        "status": "active",
        "created_at": "2026-08-31T12:00:00.000000Z"
    }
}
```

#### Response Errors
- `401 Unauthorized`: Authentication token missing, expired, or revoked.
- `403 Forbidden`: User does not have sufficient role permissions (``admin``).
- `404 Not Found`: Target resource was not found.

#### Example cURL
```bash
curl -X GET "https://lostandfound.wollo.edu.et/api/v1/admin/organizational-unit-types" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer YOUR_SANCTUM_TOKEN"
```

---

### `POST /api/v1/admin/organizational-unit-types`
<!-- source: routes/api.php -->
<!-- source: App/Http/Controllers/Api/V1/Admin/OrganizationalUnitTypeController.php -->
**Route Name:** `organizational-unit-types.store`  
**Controller Action:** `\App\Http\Controllers\Api\V1\Admin\OrganizationalUnitTypeController@store`  
**Middleware Stack:** `api, auth:sanctum, role:admin`  
**Policy Enforcement:** None (Guarded by Middleware / Ownership check)  
**Required Role(s):** `admin`  

#### Request
**Request Body (`application/json`):**
```json
{
    "data": "sample_payload"
}
```

#### Response `200 OK` / `201 Created`
```json
{
    "success": true,
    "message": "Request processed successfully.",
    "data": {
        "id": 1,
        "status": "active",
        "created_at": "2026-08-31T12:00:00.000000Z"
    }
}
```

#### Response Errors
- `401 Unauthorized`: Authentication token missing, expired, or revoked.
- `403 Forbidden`: User does not have sufficient role permissions (``admin``).
- `404 Not Found`: Target resource was not found.
- `422 Unprocessable Entity`: Request body failed FormRequest validation.

#### Example cURL
```bash
curl -X POST "https://lostandfound.wollo.edu.et/api/v1/admin/organizational-unit-types" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer YOUR_SANCTUM_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"sample_field": "value"}'
```

---

### `GET /api/v1/admin/organizational-unit-types/{organizational_unit_type}`
<!-- source: routes/api.php -->
<!-- source: App/Http/Controllers/Api/V1/Admin/OrganizationalUnitTypeController.php -->
**Route Name:** `organizational-unit-types.show`  
**Controller Action:** `\App\Http\Controllers\Api\V1\Admin\OrganizationalUnitTypeController@show`  
**Middleware Stack:** `api, auth:sanctum, role:admin`  
**Policy Enforcement:** None (Guarded by Middleware / Ownership check)  
**Required Role(s):** `admin`  

#### Request
**Path Parameters:**
- `organizational_unit_type` (string/integer, required): Identifier of target resource.

#### Response `200 OK` / `201 Created`
```json
{
    "success": true,
    "message": "Request processed successfully.",
    "data": {
        "id": 1,
        "status": "active",
        "created_at": "2026-08-31T12:00:00.000000Z"
    }
}
```

#### Response Errors
- `401 Unauthorized`: Authentication token missing, expired, or revoked.
- `403 Forbidden`: User does not have sufficient role permissions (``admin``).
- `404 Not Found`: Target resource was not found.

#### Example cURL
```bash
curl -X GET "https://lostandfound.wollo.edu.et/api/v1/admin/organizational-unit-types/{organizational_unit_type}" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer YOUR_SANCTUM_TOKEN"
```

---

### `PUT|PATCH /api/v1/admin/organizational-unit-types/{organizational_unit_type}`
<!-- source: routes/api.php -->
<!-- source: App/Http/Controllers/Api/V1/Admin/OrganizationalUnitTypeController.php -->
**Route Name:** `organizational-unit-types.update`  
**Controller Action:** `\App\Http\Controllers\Api\V1\Admin\OrganizationalUnitTypeController@update`  
**Middleware Stack:** `api, auth:sanctum, role:admin`  
**Policy Enforcement:** None (Guarded by Middleware / Ownership check)  
**Required Role(s):** `admin`  

#### Request
**Path Parameters:**
- `organizational_unit_type` (string/integer, required): Identifier of target resource.

#### Response `200 OK` / `201 Created`
```json
{
    "success": true,
    "message": "Request processed successfully.",
    "data": {
        "id": 1,
        "status": "active",
        "created_at": "2026-08-31T12:00:00.000000Z"
    }
}
```

#### Response Errors
- `401 Unauthorized`: Authentication token missing, expired, or revoked.
- `403 Forbidden`: User does not have sufficient role permissions (``admin``).
- `404 Not Found`: Target resource was not found.

#### Example cURL
```bash
curl -X PUT|PATCH "https://lostandfound.wollo.edu.et/api/v1/admin/organizational-unit-types/{organizational_unit_type}" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer YOUR_SANCTUM_TOKEN"
```

---

### `DELETE /api/v1/admin/organizational-unit-types/{organizational_unit_type}`
<!-- source: routes/api.php -->
<!-- source: App/Http/Controllers/Api/V1/Admin/OrganizationalUnitTypeController.php -->
**Route Name:** `organizational-unit-types.destroy`  
**Controller Action:** `\App\Http\Controllers\Api\V1\Admin\OrganizationalUnitTypeController@destroy`  
**Middleware Stack:** `api, auth:sanctum, role:admin`  
**Policy Enforcement:** None (Guarded by Middleware / Ownership check)  
**Required Role(s):** `admin`  

#### Request
**Path Parameters:**
- `organizational_unit_type` (string/integer, required): Identifier of target resource.

#### Response `200 OK` / `201 Created`
```json
{
    "success": true,
    "message": "Request processed successfully.",
    "data": {
        "id": 1,
        "status": "active",
        "created_at": "2026-08-31T12:00:00.000000Z"
    }
}
```

#### Response Errors
- `401 Unauthorized`: Authentication token missing, expired, or revoked.
- `403 Forbidden`: User does not have sufficient role permissions (``admin``).
- `404 Not Found`: Target resource was not found.

#### Example cURL
```bash
curl -X DELETE "https://lostandfound.wollo.edu.et/api/v1/admin/organizational-unit-types/{organizational_unit_type}" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer YOUR_SANCTUM_TOKEN"
```

---

### `GET /api/v1/admin/categories`
<!-- source: routes/api.php -->
<!-- source: App/Http/Controllers/Api/V1/Admin/CategoryController.php -->
**Route Name:** `categories.index`  
**Controller Action:** `\App\Http\Controllers\Api\V1\Admin\CategoryController@index`  
**Middleware Stack:** `api, auth:sanctum, role:admin`  
**Policy Enforcement:** `App\Policies\CategoryPolicy`  
**Required Role(s):** `admin`  

#### Request
#### Response `200 OK` / `201 Created`
```json
{
    "success": true,
    "message": "Request processed successfully.",
    "data": {
        "id": 1,
        "status": "active",
        "created_at": "2026-08-31T12:00:00.000000Z"
    }
}
```

#### Response Errors
- `401 Unauthorized`: Authentication token missing, expired, or revoked.
- `403 Forbidden`: User does not have sufficient role permissions (``admin``).
- `404 Not Found`: Target resource was not found.

#### Example cURL
```bash
curl -X GET "https://lostandfound.wollo.edu.et/api/v1/admin/categories" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer YOUR_SANCTUM_TOKEN"
```

---

### `POST /api/v1/admin/categories`
<!-- source: routes/api.php -->
<!-- source: App/Http/Controllers/Api/V1/Admin/CategoryController.php -->
**Route Name:** `categories.store`  
**Controller Action:** `\App\Http\Controllers\Api\V1\Admin\CategoryController@store`  
**Middleware Stack:** `api, auth:sanctum, role:admin`  
**Policy Enforcement:** `App\Policies\CategoryPolicy`  
**Required Role(s):** `admin`  

#### Request
**Request Body (`application/json`):**
```json
{
    "data": "sample_payload"
}
```

#### Response `200 OK` / `201 Created`
```json
{
    "success": true,
    "message": "Request processed successfully.",
    "data": {
        "id": 1,
        "status": "active",
        "created_at": "2026-08-31T12:00:00.000000Z"
    }
}
```

#### Response Errors
- `401 Unauthorized`: Authentication token missing, expired, or revoked.
- `403 Forbidden`: User does not have sufficient role permissions (``admin``).
- `404 Not Found`: Target resource was not found.
- `422 Unprocessable Entity`: Request body failed FormRequest validation.

#### Example cURL
```bash
curl -X POST "https://lostandfound.wollo.edu.et/api/v1/admin/categories" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer YOUR_SANCTUM_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"sample_field": "value"}'
```

---

### `GET /api/v1/admin/categories/{category}`
<!-- source: routes/api.php -->
<!-- source: App/Http/Controllers/Api/V1/Admin/CategoryController.php -->
**Route Name:** `categories.show`  
**Controller Action:** `\App\Http\Controllers\Api\V1\Admin\CategoryController@show`  
**Middleware Stack:** `api, auth:sanctum, role:admin`  
**Policy Enforcement:** `App\Policies\CategoryPolicy`  
**Required Role(s):** `admin`  

#### Request
**Path Parameters:**
- `category` (string/integer, required): Identifier of target resource.

#### Response `200 OK` / `201 Created`
```json
{
    "success": true,
    "message": "Request processed successfully.",
    "data": {
        "id": 1,
        "status": "active",
        "created_at": "2026-08-31T12:00:00.000000Z"
    }
}
```

#### Response Errors
- `401 Unauthorized`: Authentication token missing, expired, or revoked.
- `403 Forbidden`: User does not have sufficient role permissions (``admin``).
- `404 Not Found`: Target resource was not found.

#### Example cURL
```bash
curl -X GET "https://lostandfound.wollo.edu.et/api/v1/admin/categories/{category}" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer YOUR_SANCTUM_TOKEN"
```

---

### `PUT|PATCH /api/v1/admin/categories/{category}`
<!-- source: routes/api.php -->
<!-- source: App/Http/Controllers/Api/V1/Admin/CategoryController.php -->
**Route Name:** `categories.update`  
**Controller Action:** `\App\Http\Controllers\Api\V1\Admin\CategoryController@update`  
**Middleware Stack:** `api, auth:sanctum, role:admin`  
**Policy Enforcement:** `App\Policies\CategoryPolicy`  
**Required Role(s):** `admin`  

#### Request
**Path Parameters:**
- `category` (string/integer, required): Identifier of target resource.

#### Response `200 OK` / `201 Created`
```json
{
    "success": true,
    "message": "Request processed successfully.",
    "data": {
        "id": 1,
        "status": "active",
        "created_at": "2026-08-31T12:00:00.000000Z"
    }
}
```

#### Response Errors
- `401 Unauthorized`: Authentication token missing, expired, or revoked.
- `403 Forbidden`: User does not have sufficient role permissions (``admin``).
- `404 Not Found`: Target resource was not found.

#### Example cURL
```bash
curl -X PUT|PATCH "https://lostandfound.wollo.edu.et/api/v1/admin/categories/{category}" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer YOUR_SANCTUM_TOKEN"
```

---

### `DELETE /api/v1/admin/categories/{category}`
<!-- source: routes/api.php -->
<!-- source: App/Http/Controllers/Api/V1/Admin/CategoryController.php -->
**Route Name:** `categories.destroy`  
**Controller Action:** `\App\Http\Controllers\Api\V1\Admin\CategoryController@destroy`  
**Middleware Stack:** `api, auth:sanctum, role:admin`  
**Policy Enforcement:** `App\Policies\CategoryPolicy`  
**Required Role(s):** `admin`  

#### Request
**Path Parameters:**
- `category` (string/integer, required): Identifier of target resource.

#### Response `200 OK` / `201 Created`
```json
{
    "success": true,
    "message": "Request processed successfully.",
    "data": {
        "id": 1,
        "status": "active",
        "created_at": "2026-08-31T12:00:00.000000Z"
    }
}
```

#### Response Errors
- `401 Unauthorized`: Authentication token missing, expired, or revoked.
- `403 Forbidden`: User does not have sufficient role permissions (``admin``).
- `404 Not Found`: Target resource was not found.

#### Example cURL
```bash
curl -X DELETE "https://lostandfound.wollo.edu.et/api/v1/admin/categories/{category}" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer YOUR_SANCTUM_TOKEN"
```

---

### `GET /api/v1/admin/locations`
<!-- source: routes/api.php -->
<!-- source: App/Http/Controllers/Api/V1/Admin/LocationController.php -->
**Route Name:** `locations.index`  
**Controller Action:** `\App\Http\Controllers\Api\V1\Admin\LocationController@index`  
**Middleware Stack:** `api, auth:sanctum, role:admin`  
**Policy Enforcement:** None (Guarded by Middleware / Ownership check)  
**Required Role(s):** `admin`  

#### Request
#### Response `200 OK` / `201 Created`
```json
{
    "success": true,
    "message": "Request processed successfully.",
    "data": {
        "id": 1,
        "status": "active",
        "created_at": "2026-08-31T12:00:00.000000Z"
    }
}
```

#### Response Errors
- `401 Unauthorized`: Authentication token missing, expired, or revoked.
- `403 Forbidden`: User does not have sufficient role permissions (``admin``).
- `404 Not Found`: Target resource was not found.

#### Example cURL
```bash
curl -X GET "https://lostandfound.wollo.edu.et/api/v1/admin/locations" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer YOUR_SANCTUM_TOKEN"
```

---

### `POST /api/v1/admin/locations`
<!-- source: routes/api.php -->
<!-- source: App/Http/Controllers/Api/V1/Admin/LocationController.php -->
**Route Name:** `locations.store`  
**Controller Action:** `\App\Http\Controllers\Api\V1\Admin\LocationController@store`  
**Middleware Stack:** `api, auth:sanctum, role:admin`  
**Policy Enforcement:** None (Guarded by Middleware / Ownership check)  
**Required Role(s):** `admin`  

#### Request
**Request Body (`application/json`):**
```json
{
    "data": "sample_payload"
}
```

#### Response `200 OK` / `201 Created`
```json
{
    "success": true,
    "message": "Request processed successfully.",
    "data": {
        "id": 1,
        "status": "active",
        "created_at": "2026-08-31T12:00:00.000000Z"
    }
}
```

#### Response Errors
- `401 Unauthorized`: Authentication token missing, expired, or revoked.
- `403 Forbidden`: User does not have sufficient role permissions (``admin``).
- `404 Not Found`: Target resource was not found.
- `422 Unprocessable Entity`: Request body failed FormRequest validation.

#### Example cURL
```bash
curl -X POST "https://lostandfound.wollo.edu.et/api/v1/admin/locations" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer YOUR_SANCTUM_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"sample_field": "value"}'
```

---

### `GET /api/v1/admin/locations/{location}`
<!-- source: routes/api.php -->
<!-- source: App/Http/Controllers/Api/V1/Admin/LocationController.php -->
**Route Name:** `locations.show`  
**Controller Action:** `\App\Http\Controllers\Api\V1\Admin\LocationController@show`  
**Middleware Stack:** `api, auth:sanctum, role:admin`  
**Policy Enforcement:** None (Guarded by Middleware / Ownership check)  
**Required Role(s):** `admin`  

#### Request
**Path Parameters:**
- `location` (string/integer, required): Identifier of target resource.

#### Response `200 OK` / `201 Created`
```json
{
    "success": true,
    "message": "Request processed successfully.",
    "data": {
        "id": 1,
        "status": "active",
        "created_at": "2026-08-31T12:00:00.000000Z"
    }
}
```

#### Response Errors
- `401 Unauthorized`: Authentication token missing, expired, or revoked.
- `403 Forbidden`: User does not have sufficient role permissions (``admin``).
- `404 Not Found`: Target resource was not found.

#### Example cURL
```bash
curl -X GET "https://lostandfound.wollo.edu.et/api/v1/admin/locations/{location}" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer YOUR_SANCTUM_TOKEN"
```

---

### `PUT|PATCH /api/v1/admin/locations/{location}`
<!-- source: routes/api.php -->
<!-- source: App/Http/Controllers/Api/V1/Admin/LocationController.php -->
**Route Name:** `locations.update`  
**Controller Action:** `\App\Http\Controllers\Api\V1\Admin\LocationController@update`  
**Middleware Stack:** `api, auth:sanctum, role:admin`  
**Policy Enforcement:** None (Guarded by Middleware / Ownership check)  
**Required Role(s):** `admin`  

#### Request
**Path Parameters:**
- `location` (string/integer, required): Identifier of target resource.

#### Response `200 OK` / `201 Created`
```json
{
    "success": true,
    "message": "Request processed successfully.",
    "data": {
        "id": 1,
        "status": "active",
        "created_at": "2026-08-31T12:00:00.000000Z"
    }
}
```

#### Response Errors
- `401 Unauthorized`: Authentication token missing, expired, or revoked.
- `403 Forbidden`: User does not have sufficient role permissions (``admin``).
- `404 Not Found`: Target resource was not found.

#### Example cURL
```bash
curl -X PUT|PATCH "https://lostandfound.wollo.edu.et/api/v1/admin/locations/{location}" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer YOUR_SANCTUM_TOKEN"
```

---

### `DELETE /api/v1/admin/locations/{location}`
<!-- source: routes/api.php -->
<!-- source: App/Http/Controllers/Api/V1/Admin/LocationController.php -->
**Route Name:** `locations.destroy`  
**Controller Action:** `\App\Http\Controllers\Api\V1\Admin\LocationController@destroy`  
**Middleware Stack:** `api, auth:sanctum, role:admin`  
**Policy Enforcement:** None (Guarded by Middleware / Ownership check)  
**Required Role(s):** `admin`  

#### Request
**Path Parameters:**
- `location` (string/integer, required): Identifier of target resource.

#### Response `200 OK` / `201 Created`
```json
{
    "success": true,
    "message": "Request processed successfully.",
    "data": {
        "id": 1,
        "status": "active",
        "created_at": "2026-08-31T12:00:00.000000Z"
    }
}
```

#### Response Errors
- `401 Unauthorized`: Authentication token missing, expired, or revoked.
- `403 Forbidden`: User does not have sufficient role permissions (``admin``).
- `404 Not Found`: Target resource was not found.

#### Example cURL
```bash
curl -X DELETE "https://lostandfound.wollo.edu.et/api/v1/admin/locations/{location}" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer YOUR_SANCTUM_TOKEN"
```

---

### `GET /api/v1/admin/storage-locations`
<!-- source: routes/api.php -->
<!-- source: App/Http/Controllers/Api/V1/Admin/StorageLocationController.php -->
**Route Name:** `storage-locations.index`  
**Controller Action:** `\App\Http\Controllers\Api\V1\Admin\StorageLocationController@index`  
**Middleware Stack:** `api, auth:sanctum, role:admin`  
**Policy Enforcement:** None (Guarded by Middleware / Ownership check)  
**Required Role(s):** `admin`  

#### Request
#### Response `200 OK` / `201 Created`
```json
{
    "success": true,
    "message": "Request processed successfully.",
    "data": {
        "id": 1,
        "status": "active",
        "created_at": "2026-08-31T12:00:00.000000Z"
    }
}
```

#### Response Errors
- `401 Unauthorized`: Authentication token missing, expired, or revoked.
- `403 Forbidden`: User does not have sufficient role permissions (``admin``).
- `404 Not Found`: Target resource was not found.

#### Example cURL
```bash
curl -X GET "https://lostandfound.wollo.edu.et/api/v1/admin/storage-locations" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer YOUR_SANCTUM_TOKEN"
```

---

### `POST /api/v1/admin/storage-locations`
<!-- source: routes/api.php -->
<!-- source: App/Http/Controllers/Api/V1/Admin/StorageLocationController.php -->
**Route Name:** `storage-locations.store`  
**Controller Action:** `\App\Http\Controllers\Api\V1\Admin\StorageLocationController@store`  
**Middleware Stack:** `api, auth:sanctum, role:admin`  
**Policy Enforcement:** None (Guarded by Middleware / Ownership check)  
**Required Role(s):** `admin`  

#### Request
**Request Body (`application/json`):**
```json
{
    "data": "sample_payload"
}
```

#### Response `200 OK` / `201 Created`
```json
{
    "success": true,
    "message": "Request processed successfully.",
    "data": {
        "id": 1,
        "status": "active",
        "created_at": "2026-08-31T12:00:00.000000Z"
    }
}
```

#### Response Errors
- `401 Unauthorized`: Authentication token missing, expired, or revoked.
- `403 Forbidden`: User does not have sufficient role permissions (``admin``).
- `404 Not Found`: Target resource was not found.
- `422 Unprocessable Entity`: Request body failed FormRequest validation.

#### Example cURL
```bash
curl -X POST "https://lostandfound.wollo.edu.et/api/v1/admin/storage-locations" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer YOUR_SANCTUM_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"sample_field": "value"}'
```

---

### `GET /api/v1/admin/storage-locations/{storage_location}`
<!-- source: routes/api.php -->
<!-- source: App/Http/Controllers/Api/V1/Admin/StorageLocationController.php -->
**Route Name:** `storage-locations.show`  
**Controller Action:** `\App\Http\Controllers\Api\V1\Admin\StorageLocationController@show`  
**Middleware Stack:** `api, auth:sanctum, role:admin`  
**Policy Enforcement:** None (Guarded by Middleware / Ownership check)  
**Required Role(s):** `admin`  

#### Request
**Path Parameters:**
- `storage_location` (string/integer, required): Identifier of target resource.

#### Response `200 OK` / `201 Created`
```json
{
    "success": true,
    "message": "Request processed successfully.",
    "data": {
        "id": 1,
        "status": "active",
        "created_at": "2026-08-31T12:00:00.000000Z"
    }
}
```

#### Response Errors
- `401 Unauthorized`: Authentication token missing, expired, or revoked.
- `403 Forbidden`: User does not have sufficient role permissions (``admin``).
- `404 Not Found`: Target resource was not found.

#### Example cURL
```bash
curl -X GET "https://lostandfound.wollo.edu.et/api/v1/admin/storage-locations/{storage_location}" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer YOUR_SANCTUM_TOKEN"
```

---

### `PUT|PATCH /api/v1/admin/storage-locations/{storage_location}`
<!-- source: routes/api.php -->
<!-- source: App/Http/Controllers/Api/V1/Admin/StorageLocationController.php -->
**Route Name:** `storage-locations.update`  
**Controller Action:** `\App\Http\Controllers\Api\V1\Admin\StorageLocationController@update`  
**Middleware Stack:** `api, auth:sanctum, role:admin`  
**Policy Enforcement:** None (Guarded by Middleware / Ownership check)  
**Required Role(s):** `admin`  

#### Request
**Path Parameters:**
- `storage_location` (string/integer, required): Identifier of target resource.

#### Response `200 OK` / `201 Created`
```json
{
    "success": true,
    "message": "Request processed successfully.",
    "data": {
        "id": 1,
        "status": "active",
        "created_at": "2026-08-31T12:00:00.000000Z"
    }
}
```

#### Response Errors
- `401 Unauthorized`: Authentication token missing, expired, or revoked.
- `403 Forbidden`: User does not have sufficient role permissions (``admin``).
- `404 Not Found`: Target resource was not found.

#### Example cURL
```bash
curl -X PUT|PATCH "https://lostandfound.wollo.edu.et/api/v1/admin/storage-locations/{storage_location}" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer YOUR_SANCTUM_TOKEN"
```

---

### `DELETE /api/v1/admin/storage-locations/{storage_location}`
<!-- source: routes/api.php -->
<!-- source: App/Http/Controllers/Api/V1/Admin/StorageLocationController.php -->
**Route Name:** `storage-locations.destroy`  
**Controller Action:** `\App\Http\Controllers\Api\V1\Admin\StorageLocationController@destroy`  
**Middleware Stack:** `api, auth:sanctum, role:admin`  
**Policy Enforcement:** None (Guarded by Middleware / Ownership check)  
**Required Role(s):** `admin`  

#### Request
**Path Parameters:**
- `storage_location` (string/integer, required): Identifier of target resource.

#### Response `200 OK` / `201 Created`
```json
{
    "success": true,
    "message": "Request processed successfully.",
    "data": {
        "id": 1,
        "status": "active",
        "created_at": "2026-08-31T12:00:00.000000Z"
    }
}
```

#### Response Errors
- `401 Unauthorized`: Authentication token missing, expired, or revoked.
- `403 Forbidden`: User does not have sufficient role permissions (``admin``).
- `404 Not Found`: Target resource was not found.

#### Example cURL
```bash
curl -X DELETE "https://lostandfound.wollo.edu.et/api/v1/admin/storage-locations/{storage_location}" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer YOUR_SANCTUM_TOKEN"
```

---

### `GET /api/v1/admin/users`
<!-- source: routes/api.php -->
<!-- source: App/Http/Controllers/Api/V1/Admin/UserManagementController.php -->
**Route Name:** `None`  
**Controller Action:** `\App\Http\Controllers\Api\V1\Admin\UserManagementController@index`  
**Middleware Stack:** `api, auth:sanctum, role:admin`  
**Policy Enforcement:** `App\Policies\UserPolicy`  
**Required Role(s):** `admin`  

#### Request
#### Response `200 OK` / `201 Created`
```json
{
    "success": true,
    "message": "Request processed successfully.",
    "data": {
        "id": 1,
        "status": "active",
        "created_at": "2026-08-31T12:00:00.000000Z"
    }
}
```

#### Response Errors
- `401 Unauthorized`: Authentication token missing, expired, or revoked.
- `403 Forbidden`: User does not have sufficient role permissions (``admin``).
- `404 Not Found`: Target resource was not found.

#### Example cURL
```bash
curl -X GET "https://lostandfound.wollo.edu.et/api/v1/admin/users" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer YOUR_SANCTUM_TOKEN"
```

---

### `POST /api/v1/admin/users`
<!-- source: routes/api.php -->
<!-- source: App/Http/Controllers/Api/V1/Admin/UserManagementController.php -->
**Route Name:** `None`  
**Controller Action:** `\App\Http\Controllers\Api\V1\Admin\UserManagementController@store`  
**Middleware Stack:** `api, auth:sanctum, role:admin`  
**Policy Enforcement:** `App\Policies\UserPolicy`  
**Required Role(s):** `admin`  

#### Request
**Request Body (`application/json`):**
```json
{
    "data": "sample_payload"
}
```

#### Response `200 OK` / `201 Created`
```json
{
    "success": true,
    "message": "Request processed successfully.",
    "data": {
        "id": 1,
        "status": "active",
        "created_at": "2026-08-31T12:00:00.000000Z"
    }
}
```

#### Response Errors
- `401 Unauthorized`: Authentication token missing, expired, or revoked.
- `403 Forbidden`: User does not have sufficient role permissions (``admin``).
- `404 Not Found`: Target resource was not found.
- `422 Unprocessable Entity`: Request body failed FormRequest validation.

#### Example cURL
```bash
curl -X POST "https://lostandfound.wollo.edu.et/api/v1/admin/users" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer YOUR_SANCTUM_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"sample_field": "value"}'
```

---

### `GET /api/v1/admin/users/{id}`
<!-- source: routes/api.php -->
<!-- source: App/Http/Controllers/Api/V1/Admin/UserManagementController.php -->
**Route Name:** `None`  
**Controller Action:** `\App\Http\Controllers\Api\V1\Admin\UserManagementController@show`  
**Middleware Stack:** `api, auth:sanctum, role:admin`  
**Policy Enforcement:** `App\Policies\UserPolicy`  
**Required Role(s):** `admin`  

#### Request
**Path Parameters:**
- `id` (string/integer, required): Identifier of target resource.

#### Response `200 OK` / `201 Created`
```json
{
    "success": true,
    "message": "Request processed successfully.",
    "data": {
        "id": 1,
        "status": "active",
        "created_at": "2026-08-31T12:00:00.000000Z"
    }
}
```

#### Response Errors
- `401 Unauthorized`: Authentication token missing, expired, or revoked.
- `403 Forbidden`: User does not have sufficient role permissions (``admin``).
- `404 Not Found`: Target resource was not found.

#### Example cURL
```bash
curl -X GET "https://lostandfound.wollo.edu.et/api/v1/admin/users/{id}" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer YOUR_SANCTUM_TOKEN"
```

---

### `PUT /api/v1/admin/users/{id}`
<!-- source: routes/api.php -->
<!-- source: App/Http/Controllers/Api/V1/Admin/UserManagementController.php -->
**Route Name:** `None`  
**Controller Action:** `\App\Http\Controllers\Api\V1\Admin\UserManagementController@update`  
**Middleware Stack:** `api, auth:sanctum, role:admin`  
**Policy Enforcement:** `App\Policies\UserPolicy`  
**Required Role(s):** `admin`  

#### Request
**Path Parameters:**
- `id` (string/integer, required): Identifier of target resource.

**Request Body (`application/json`):**
```json
{
    "data": "sample_payload"
}
```

#### Response `200 OK` / `201 Created`
```json
{
    "success": true,
    "message": "Request processed successfully.",
    "data": {
        "id": 1,
        "status": "active",
        "created_at": "2026-08-31T12:00:00.000000Z"
    }
}
```

#### Response Errors
- `401 Unauthorized`: Authentication token missing, expired, or revoked.
- `403 Forbidden`: User does not have sufficient role permissions (``admin``).
- `404 Not Found`: Target resource was not found.
- `422 Unprocessable Entity`: Request body failed FormRequest validation.

#### Example cURL
```bash
curl -X PUT "https://lostandfound.wollo.edu.et/api/v1/admin/users/{id}" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer YOUR_SANCTUM_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"sample_field": "value"}'
```

---

### `PATCH /api/v1/admin/users/{id}/role`
<!-- source: routes/api.php -->
<!-- source: App/Http/Controllers/Api/V1/Admin/UserManagementController.php -->
**Route Name:** `None`  
**Controller Action:** `\App\Http\Controllers\Api\V1\Admin\UserManagementController@updateRole`  
**Middleware Stack:** `api, auth:sanctum, role:admin`  
**Policy Enforcement:** `App\Policies\UserPolicy`  
**Required Role(s):** `admin`  

#### Request
**Path Parameters:**
- `id` (string/integer, required): Identifier of target resource.

**Request Body (`application/json`):**
```json
{
    "data": "sample_payload"
}
```

#### Response `200 OK` / `201 Created`
```json
{
    "success": true,
    "message": "Request processed successfully.",
    "data": {
        "id": 1,
        "status": "active",
        "created_at": "2026-08-31T12:00:00.000000Z"
    }
}
```

#### Response Errors
- `401 Unauthorized`: Authentication token missing, expired, or revoked.
- `403 Forbidden`: User does not have sufficient role permissions (``admin``).
- `404 Not Found`: Target resource was not found.
- `422 Unprocessable Entity`: Request body failed FormRequest validation.

#### Example cURL
```bash
curl -X PATCH "https://lostandfound.wollo.edu.et/api/v1/admin/users/{id}/role" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer YOUR_SANCTUM_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"sample_field": "value"}'
```

---

### `PATCH /api/v1/admin/users/{id}/toggle-active`
<!-- source: routes/api.php -->
<!-- source: App/Http/Controllers/Api/V1/Admin/UserManagementController.php -->
**Route Name:** `None`  
**Controller Action:** `\App\Http\Controllers\Api\V1\Admin\UserManagementController@toggleActive`  
**Middleware Stack:** `api, auth:sanctum, role:admin`  
**Policy Enforcement:** `App\Policies\UserPolicy`  
**Required Role(s):** `admin`  

#### Request
**Path Parameters:**
- `id` (string/integer, required): Identifier of target resource.

**Request Body (`application/json`):**
```json
{
    "data": "sample_payload"
}
```

#### Response `200 OK` / `201 Created`
```json
{
    "success": true,
    "message": "Request processed successfully.",
    "data": {
        "id": 1,
        "status": "active",
        "created_at": "2026-08-31T12:00:00.000000Z"
    }
}
```

#### Response Errors
- `401 Unauthorized`: Authentication token missing, expired, or revoked.
- `403 Forbidden`: User does not have sufficient role permissions (``admin``).
- `404 Not Found`: Target resource was not found.
- `422 Unprocessable Entity`: Request body failed FormRequest validation.

#### Example cURL
```bash
curl -X PATCH "https://lostandfound.wollo.edu.et/api/v1/admin/users/{id}/toggle-active" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer YOUR_SANCTUM_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"sample_field": "value"}'
```

---

### `GET /api/v1/admin/users/{id}/permissions`
<!-- source: routes/api.php -->
<!-- source: App/Http/Controllers/Api/V1/Admin/UserManagementController.php -->
**Route Name:** `None`  
**Controller Action:** `\App\Http\Controllers\Api\V1\Admin\UserManagementController@getPermissions`  
**Middleware Stack:** `api, auth:sanctum, role:admin`  
**Policy Enforcement:** `App\Policies\UserPolicy`  
**Required Role(s):** `admin`  

#### Request
**Path Parameters:**
- `id` (string/integer, required): Identifier of target resource.

#### Response `200 OK` / `201 Created`
```json
{
    "success": true,
    "message": "Request processed successfully.",
    "data": {
        "id": 1,
        "status": "active",
        "created_at": "2026-08-31T12:00:00.000000Z"
    }
}
```

#### Response Errors
- `401 Unauthorized`: Authentication token missing, expired, or revoked.
- `403 Forbidden`: User does not have sufficient role permissions (``admin``).
- `404 Not Found`: Target resource was not found.

#### Example cURL
```bash
curl -X GET "https://lostandfound.wollo.edu.et/api/v1/admin/users/{id}/permissions" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer YOUR_SANCTUM_TOKEN"
```

---

### `POST /api/v1/admin/users/{id}/permissions`
<!-- source: routes/api.php -->
<!-- source: App/Http/Controllers/Api/V1/Admin/UserManagementController.php -->
**Route Name:** `None`  
**Controller Action:** `\App\Http\Controllers\Api\V1\Admin\UserManagementController@syncPermissions`  
**Middleware Stack:** `api, auth:sanctum, role:admin`  
**Policy Enforcement:** `App\Policies\UserPolicy`  
**Required Role(s):** `admin`  

#### Request
**Path Parameters:**
- `id` (string/integer, required): Identifier of target resource.

**Request Body (`application/json`):**
```json
{
    "data": "sample_payload"
}
```

#### Response `200 OK` / `201 Created`
```json
{
    "success": true,
    "message": "Request processed successfully.",
    "data": {
        "id": 1,
        "status": "active",
        "created_at": "2026-08-31T12:00:00.000000Z"
    }
}
```

#### Response Errors
- `401 Unauthorized`: Authentication token missing, expired, or revoked.
- `403 Forbidden`: User does not have sufficient role permissions (``admin``).
- `404 Not Found`: Target resource was not found.
- `422 Unprocessable Entity`: Request body failed FormRequest validation.

#### Example cURL
```bash
curl -X POST "https://lostandfound.wollo.edu.et/api/v1/admin/users/{id}/permissions" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer YOUR_SANCTUM_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"sample_field": "value"}'
```

---

### `GET /api/v1/admin/roles`
<!-- source: routes/api.php -->
<!-- source: App/Http/Controllers/Api/V1/Admin/RoleController.php -->
**Route Name:** `roles.index`  
**Controller Action:** `\App\Http\Controllers\Api\V1\Admin\RoleController@index`  
**Middleware Stack:** `api, auth:sanctum, role:admin`  
**Policy Enforcement:** None (Guarded by Middleware / Ownership check)  
**Required Role(s):** `admin`  

#### Request
#### Response `200 OK` / `201 Created`
```json
{
    "success": true,
    "message": "Request processed successfully.",
    "data": {
        "id": 1,
        "status": "active",
        "created_at": "2026-08-31T12:00:00.000000Z"
    }
}
```

#### Response Errors
- `401 Unauthorized`: Authentication token missing, expired, or revoked.
- `403 Forbidden`: User does not have sufficient role permissions (``admin``).
- `404 Not Found`: Target resource was not found.

#### Example cURL
```bash
curl -X GET "https://lostandfound.wollo.edu.et/api/v1/admin/roles" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer YOUR_SANCTUM_TOKEN"
```

---

### `POST /api/v1/admin/roles`
<!-- source: routes/api.php -->
<!-- source: App/Http/Controllers/Api/V1/Admin/RoleController.php -->
**Route Name:** `roles.store`  
**Controller Action:** `\App\Http\Controllers\Api\V1\Admin\RoleController@store`  
**Middleware Stack:** `api, auth:sanctum, role:admin`  
**Policy Enforcement:** None (Guarded by Middleware / Ownership check)  
**Required Role(s):** `admin`  

#### Request
**Request Body (`application/json`):**
```json
{
    "data": "sample_payload"
}
```

#### Response `200 OK` / `201 Created`
```json
{
    "success": true,
    "message": "Request processed successfully.",
    "data": {
        "id": 1,
        "status": "active",
        "created_at": "2026-08-31T12:00:00.000000Z"
    }
}
```

#### Response Errors
- `401 Unauthorized`: Authentication token missing, expired, or revoked.
- `403 Forbidden`: User does not have sufficient role permissions (``admin``).
- `404 Not Found`: Target resource was not found.
- `422 Unprocessable Entity`: Request body failed FormRequest validation.

#### Example cURL
```bash
curl -X POST "https://lostandfound.wollo.edu.et/api/v1/admin/roles" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer YOUR_SANCTUM_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"sample_field": "value"}'
```

---

### `GET /api/v1/admin/roles/{role}`
<!-- source: routes/api.php -->
<!-- source: App/Http/Controllers/Api/V1/Admin/RoleController.php -->
**Route Name:** `roles.show`  
**Controller Action:** `\App\Http\Controllers\Api\V1\Admin\RoleController@show`  
**Middleware Stack:** `api, auth:sanctum, role:admin`  
**Policy Enforcement:** None (Guarded by Middleware / Ownership check)  
**Required Role(s):** `admin`  

#### Request
**Path Parameters:**
- `role` (string/integer, required): Identifier of target resource.

#### Response `200 OK` / `201 Created`
```json
{
    "success": true,
    "message": "Request processed successfully.",
    "data": {
        "id": 1,
        "status": "active",
        "created_at": "2026-08-31T12:00:00.000000Z"
    }
}
```

#### Response Errors
- `401 Unauthorized`: Authentication token missing, expired, or revoked.
- `403 Forbidden`: User does not have sufficient role permissions (``admin``).
- `404 Not Found`: Target resource was not found.

#### Example cURL
```bash
curl -X GET "https://lostandfound.wollo.edu.et/api/v1/admin/roles/{role}" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer YOUR_SANCTUM_TOKEN"
```

---

### `PUT|PATCH /api/v1/admin/roles/{role}`
<!-- source: routes/api.php -->
<!-- source: App/Http/Controllers/Api/V1/Admin/RoleController.php -->
**Route Name:** `roles.update`  
**Controller Action:** `\App\Http\Controllers\Api\V1\Admin\RoleController@update`  
**Middleware Stack:** `api, auth:sanctum, role:admin`  
**Policy Enforcement:** None (Guarded by Middleware / Ownership check)  
**Required Role(s):** `admin`  

#### Request
**Path Parameters:**
- `role` (string/integer, required): Identifier of target resource.

#### Response `200 OK` / `201 Created`
```json
{
    "success": true,
    "message": "Request processed successfully.",
    "data": {
        "id": 1,
        "status": "active",
        "created_at": "2026-08-31T12:00:00.000000Z"
    }
}
```

#### Response Errors
- `401 Unauthorized`: Authentication token missing, expired, or revoked.
- `403 Forbidden`: User does not have sufficient role permissions (``admin``).
- `404 Not Found`: Target resource was not found.

#### Example cURL
```bash
curl -X PUT|PATCH "https://lostandfound.wollo.edu.et/api/v1/admin/roles/{role}" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer YOUR_SANCTUM_TOKEN"
```

---

### `DELETE /api/v1/admin/roles/{role}`
<!-- source: routes/api.php -->
<!-- source: App/Http/Controllers/Api/V1/Admin/RoleController.php -->
**Route Name:** `roles.destroy`  
**Controller Action:** `\App\Http\Controllers\Api\V1\Admin\RoleController@destroy`  
**Middleware Stack:** `api, auth:sanctum, role:admin`  
**Policy Enforcement:** None (Guarded by Middleware / Ownership check)  
**Required Role(s):** `admin`  

#### Request
**Path Parameters:**
- `role` (string/integer, required): Identifier of target resource.

#### Response `200 OK` / `201 Created`
```json
{
    "success": true,
    "message": "Request processed successfully.",
    "data": {
        "id": 1,
        "status": "active",
        "created_at": "2026-08-31T12:00:00.000000Z"
    }
}
```

#### Response Errors
- `401 Unauthorized`: Authentication token missing, expired, or revoked.
- `403 Forbidden`: User does not have sufficient role permissions (``admin``).
- `404 Not Found`: Target resource was not found.

#### Example cURL
```bash
curl -X DELETE "https://lostandfound.wollo.edu.et/api/v1/admin/roles/{role}" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer YOUR_SANCTUM_TOKEN"
```

---

### `POST /api/v1/admin/roles/{role}/permissions`
<!-- source: routes/api.php -->
<!-- source: App/Http/Controllers/Api/V1/Admin/RoleController.php -->
**Route Name:** `None`  
**Controller Action:** `\App\Http\Controllers\Api\V1\Admin\RoleController@syncPermissions`  
**Middleware Stack:** `api, auth:sanctum, role:admin`  
**Policy Enforcement:** None (Guarded by Middleware / Ownership check)  
**Required Role(s):** `admin`  

#### Request
**Path Parameters:**
- `role` (string/integer, required): Identifier of target resource.

**Request Body (`application/json`):**
```json
{
    "data": "sample_payload"
}
```

#### Response `200 OK` / `201 Created`
```json
{
    "success": true,
    "message": "Request processed successfully.",
    "data": {
        "id": 1,
        "status": "active",
        "created_at": "2026-08-31T12:00:00.000000Z"
    }
}
```

#### Response Errors
- `401 Unauthorized`: Authentication token missing, expired, or revoked.
- `403 Forbidden`: User does not have sufficient role permissions (``admin``).
- `404 Not Found`: Target resource was not found.
- `422 Unprocessable Entity`: Request body failed FormRequest validation.

#### Example cURL
```bash
curl -X POST "https://lostandfound.wollo.edu.et/api/v1/admin/roles/{role}/permissions" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer YOUR_SANCTUM_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"sample_field": "value"}'
```

---

### `GET /api/v1/admin/permission-groups`
<!-- source: routes/api.php -->
<!-- source: App/Http/Controllers/Api/V1/Admin/PermissionGroupController.php -->
**Route Name:** `permission-groups.index`  
**Controller Action:** `\App\Http\Controllers\Api\V1\Admin\PermissionGroupController@index`  
**Middleware Stack:** `api, auth:sanctum, role:admin`  
**Policy Enforcement:** None (Guarded by Middleware / Ownership check)  
**Required Role(s):** `admin`  

#### Request
#### Response `200 OK` / `201 Created`
```json
{
    "success": true,
    "message": "Request processed successfully.",
    "data": {
        "id": 1,
        "status": "active",
        "created_at": "2026-08-31T12:00:00.000000Z"
    }
}
```

#### Response Errors
- `401 Unauthorized`: Authentication token missing, expired, or revoked.
- `403 Forbidden`: User does not have sufficient role permissions (``admin``).
- `404 Not Found`: Target resource was not found.

#### Example cURL
```bash
curl -X GET "https://lostandfound.wollo.edu.et/api/v1/admin/permission-groups" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer YOUR_SANCTUM_TOKEN"
```

---

### `POST /api/v1/admin/permission-groups`
<!-- source: routes/api.php -->
<!-- source: App/Http/Controllers/Api/V1/Admin/PermissionGroupController.php -->
**Route Name:** `permission-groups.store`  
**Controller Action:** `\App\Http\Controllers\Api\V1\Admin\PermissionGroupController@store`  
**Middleware Stack:** `api, auth:sanctum, role:admin`  
**Policy Enforcement:** None (Guarded by Middleware / Ownership check)  
**Required Role(s):** `admin`  

#### Request
**Request Body (`application/json`):**
```json
{
    "data": "sample_payload"
}
```

#### Response `200 OK` / `201 Created`
```json
{
    "success": true,
    "message": "Request processed successfully.",
    "data": {
        "id": 1,
        "status": "active",
        "created_at": "2026-08-31T12:00:00.000000Z"
    }
}
```

#### Response Errors
- `401 Unauthorized`: Authentication token missing, expired, or revoked.
- `403 Forbidden`: User does not have sufficient role permissions (``admin``).
- `404 Not Found`: Target resource was not found.
- `422 Unprocessable Entity`: Request body failed FormRequest validation.

#### Example cURL
```bash
curl -X POST "https://lostandfound.wollo.edu.et/api/v1/admin/permission-groups" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer YOUR_SANCTUM_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"sample_field": "value"}'
```

---

### `GET /api/v1/admin/permission-groups/{permission_group}`
<!-- source: routes/api.php -->
<!-- source: App/Http/Controllers/Api/V1/Admin/PermissionGroupController.php -->
**Route Name:** `permission-groups.show`  
**Controller Action:** `\App\Http\Controllers\Api\V1\Admin\PermissionGroupController@show`  
**Middleware Stack:** `api, auth:sanctum, role:admin`  
**Policy Enforcement:** None (Guarded by Middleware / Ownership check)  
**Required Role(s):** `admin`  

#### Request
**Path Parameters:**
- `permission_group` (string/integer, required): Identifier of target resource.

#### Response `200 OK` / `201 Created`
```json
{
    "success": true,
    "message": "Request processed successfully.",
    "data": {
        "id": 1,
        "status": "active",
        "created_at": "2026-08-31T12:00:00.000000Z"
    }
}
```

#### Response Errors
- `401 Unauthorized`: Authentication token missing, expired, or revoked.
- `403 Forbidden`: User does not have sufficient role permissions (``admin``).
- `404 Not Found`: Target resource was not found.

#### Example cURL
```bash
curl -X GET "https://lostandfound.wollo.edu.et/api/v1/admin/permission-groups/{permission_group}" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer YOUR_SANCTUM_TOKEN"
```

---

### `PUT|PATCH /api/v1/admin/permission-groups/{permission_group}`
<!-- source: routes/api.php -->
<!-- source: App/Http/Controllers/Api/V1/Admin/PermissionGroupController.php -->
**Route Name:** `permission-groups.update`  
**Controller Action:** `\App\Http\Controllers\Api\V1\Admin\PermissionGroupController@update`  
**Middleware Stack:** `api, auth:sanctum, role:admin`  
**Policy Enforcement:** None (Guarded by Middleware / Ownership check)  
**Required Role(s):** `admin`  

#### Request
**Path Parameters:**
- `permission_group` (string/integer, required): Identifier of target resource.

#### Response `200 OK` / `201 Created`
```json
{
    "success": true,
    "message": "Request processed successfully.",
    "data": {
        "id": 1,
        "status": "active",
        "created_at": "2026-08-31T12:00:00.000000Z"
    }
}
```

#### Response Errors
- `401 Unauthorized`: Authentication token missing, expired, or revoked.
- `403 Forbidden`: User does not have sufficient role permissions (``admin``).
- `404 Not Found`: Target resource was not found.

#### Example cURL
```bash
curl -X PUT|PATCH "https://lostandfound.wollo.edu.et/api/v1/admin/permission-groups/{permission_group}" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer YOUR_SANCTUM_TOKEN"
```

---

### `DELETE /api/v1/admin/permission-groups/{permission_group}`
<!-- source: routes/api.php -->
<!-- source: App/Http/Controllers/Api/V1/Admin/PermissionGroupController.php -->
**Route Name:** `permission-groups.destroy`  
**Controller Action:** `\App\Http\Controllers\Api\V1\Admin\PermissionGroupController@destroy`  
**Middleware Stack:** `api, auth:sanctum, role:admin`  
**Policy Enforcement:** None (Guarded by Middleware / Ownership check)  
**Required Role(s):** `admin`  

#### Request
**Path Parameters:**
- `permission_group` (string/integer, required): Identifier of target resource.

#### Response `200 OK` / `201 Created`
```json
{
    "success": true,
    "message": "Request processed successfully.",
    "data": {
        "id": 1,
        "status": "active",
        "created_at": "2026-08-31T12:00:00.000000Z"
    }
}
```

#### Response Errors
- `401 Unauthorized`: Authentication token missing, expired, or revoked.
- `403 Forbidden`: User does not have sufficient role permissions (``admin``).
- `404 Not Found`: Target resource was not found.

#### Example cURL
```bash
curl -X DELETE "https://lostandfound.wollo.edu.et/api/v1/admin/permission-groups/{permission_group}" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer YOUR_SANCTUM_TOKEN"
```

---

### `PATCH /api/v1/admin/permission-groups/{permissionGroup}/toggle-active`
<!-- source: routes/api.php -->
<!-- source: App/Http/Controllers/Api/V1/Admin/PermissionGroupController.php -->
**Route Name:** `None`  
**Controller Action:** `\App\Http\Controllers\Api\V1\Admin\PermissionGroupController@toggleActive`  
**Middleware Stack:** `api, auth:sanctum, role:admin`  
**Policy Enforcement:** None (Guarded by Middleware / Ownership check)  
**Required Role(s):** `admin`  

#### Request
**Path Parameters:**
- `permissionGroup` (string/integer, required): Identifier of target resource.

**Request Body (`application/json`):**
```json
{
    "data": "sample_payload"
}
```

#### Response `200 OK` / `201 Created`
```json
{
    "success": true,
    "message": "Request processed successfully.",
    "data": {
        "id": 1,
        "status": "active",
        "created_at": "2026-08-31T12:00:00.000000Z"
    }
}
```

#### Response Errors
- `401 Unauthorized`: Authentication token missing, expired, or revoked.
- `403 Forbidden`: User does not have sufficient role permissions (``admin``).
- `404 Not Found`: Target resource was not found.
- `422 Unprocessable Entity`: Request body failed FormRequest validation.

#### Example cURL
```bash
curl -X PATCH "https://lostandfound.wollo.edu.et/api/v1/admin/permission-groups/{permissionGroup}/toggle-active" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer YOUR_SANCTUM_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"sample_field": "value"}'
```

---

### `GET /api/v1/admin/permissions`
<!-- source: routes/api.php -->
<!-- source: App/Http/Controllers/Api/V1/Admin/PermissionController.php -->
**Route Name:** `permissions.index`  
**Controller Action:** `\App\Http\Controllers\Api\V1\Admin\PermissionController@index`  
**Middleware Stack:** `api, auth:sanctum, role:admin`  
**Policy Enforcement:** None (Guarded by Middleware / Ownership check)  
**Required Role(s):** `admin`  

#### Request
#### Response `200 OK` / `201 Created`
```json
{
    "success": true,
    "message": "Request processed successfully.",
    "data": {
        "id": 1,
        "status": "active",
        "created_at": "2026-08-31T12:00:00.000000Z"
    }
}
```

#### Response Errors
- `401 Unauthorized`: Authentication token missing, expired, or revoked.
- `403 Forbidden`: User does not have sufficient role permissions (``admin``).
- `404 Not Found`: Target resource was not found.

#### Example cURL
```bash
curl -X GET "https://lostandfound.wollo.edu.et/api/v1/admin/permissions" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer YOUR_SANCTUM_TOKEN"
```

---

### `POST /api/v1/admin/permissions`
<!-- source: routes/api.php -->
<!-- source: App/Http/Controllers/Api/V1/Admin/PermissionController.php -->
**Route Name:** `permissions.store`  
**Controller Action:** `\App\Http\Controllers\Api\V1\Admin\PermissionController@store`  
**Middleware Stack:** `api, auth:sanctum, role:admin`  
**Policy Enforcement:** None (Guarded by Middleware / Ownership check)  
**Required Role(s):** `admin`  

#### Request
**Request Body (`application/json`):**
```json
{
    "data": "sample_payload"
}
```

#### Response `200 OK` / `201 Created`
```json
{
    "success": true,
    "message": "Request processed successfully.",
    "data": {
        "id": 1,
        "status": "active",
        "created_at": "2026-08-31T12:00:00.000000Z"
    }
}
```

#### Response Errors
- `401 Unauthorized`: Authentication token missing, expired, or revoked.
- `403 Forbidden`: User does not have sufficient role permissions (``admin``).
- `404 Not Found`: Target resource was not found.
- `422 Unprocessable Entity`: Request body failed FormRequest validation.

#### Example cURL
```bash
curl -X POST "https://lostandfound.wollo.edu.et/api/v1/admin/permissions" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer YOUR_SANCTUM_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"sample_field": "value"}'
```

---

### `GET /api/v1/admin/permissions/{permission}`
<!-- source: routes/api.php -->
<!-- source: App/Http/Controllers/Api/V1/Admin/PermissionController.php -->
**Route Name:** `permissions.show`  
**Controller Action:** `\App\Http\Controllers\Api\V1\Admin\PermissionController@show`  
**Middleware Stack:** `api, auth:sanctum, role:admin`  
**Policy Enforcement:** None (Guarded by Middleware / Ownership check)  
**Required Role(s):** `admin`  

#### Request
**Path Parameters:**
- `permission` (string/integer, required): Identifier of target resource.

#### Response `200 OK` / `201 Created`
```json
{
    "success": true,
    "message": "Request processed successfully.",
    "data": {
        "id": 1,
        "status": "active",
        "created_at": "2026-08-31T12:00:00.000000Z"
    }
}
```

#### Response Errors
- `401 Unauthorized`: Authentication token missing, expired, or revoked.
- `403 Forbidden`: User does not have sufficient role permissions (``admin``).
- `404 Not Found`: Target resource was not found.

#### Example cURL
```bash
curl -X GET "https://lostandfound.wollo.edu.et/api/v1/admin/permissions/{permission}" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer YOUR_SANCTUM_TOKEN"
```

---

### `PUT|PATCH /api/v1/admin/permissions/{permission}`
<!-- source: routes/api.php -->
<!-- source: App/Http/Controllers/Api/V1/Admin/PermissionController.php -->
**Route Name:** `permissions.update`  
**Controller Action:** `\App\Http\Controllers\Api\V1\Admin\PermissionController@update`  
**Middleware Stack:** `api, auth:sanctum, role:admin`  
**Policy Enforcement:** None (Guarded by Middleware / Ownership check)  
**Required Role(s):** `admin`  

#### Request
**Path Parameters:**
- `permission` (string/integer, required): Identifier of target resource.

#### Response `200 OK` / `201 Created`
```json
{
    "success": true,
    "message": "Request processed successfully.",
    "data": {
        "id": 1,
        "status": "active",
        "created_at": "2026-08-31T12:00:00.000000Z"
    }
}
```

#### Response Errors
- `401 Unauthorized`: Authentication token missing, expired, or revoked.
- `403 Forbidden`: User does not have sufficient role permissions (``admin``).
- `404 Not Found`: Target resource was not found.

#### Example cURL
```bash
curl -X PUT|PATCH "https://lostandfound.wollo.edu.et/api/v1/admin/permissions/{permission}" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer YOUR_SANCTUM_TOKEN"
```

---

### `DELETE /api/v1/admin/permissions/{permission}`
<!-- source: routes/api.php -->
<!-- source: App/Http/Controllers/Api/V1/Admin/PermissionController.php -->
**Route Name:** `permissions.destroy`  
**Controller Action:** `\App\Http\Controllers\Api\V1\Admin\PermissionController@destroy`  
**Middleware Stack:** `api, auth:sanctum, role:admin`  
**Policy Enforcement:** None (Guarded by Middleware / Ownership check)  
**Required Role(s):** `admin`  

#### Request
**Path Parameters:**
- `permission` (string/integer, required): Identifier of target resource.

#### Response `200 OK` / `201 Created`
```json
{
    "success": true,
    "message": "Request processed successfully.",
    "data": {
        "id": 1,
        "status": "active",
        "created_at": "2026-08-31T12:00:00.000000Z"
    }
}
```

#### Response Errors
- `401 Unauthorized`: Authentication token missing, expired, or revoked.
- `403 Forbidden`: User does not have sufficient role permissions (``admin``).
- `404 Not Found`: Target resource was not found.

#### Example cURL
```bash
curl -X DELETE "https://lostandfound.wollo.edu.et/api/v1/admin/permissions/{permission}" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer YOUR_SANCTUM_TOKEN"
```

---

### `PATCH /api/v1/admin/permissions/{permission}/toggle-active`
<!-- source: routes/api.php -->
<!-- source: App/Http/Controllers/Api/V1/Admin/PermissionController.php -->
**Route Name:** `None`  
**Controller Action:** `\App\Http\Controllers\Api\V1\Admin\PermissionController@toggleActive`  
**Middleware Stack:** `api, auth:sanctum, role:admin`  
**Policy Enforcement:** None (Guarded by Middleware / Ownership check)  
**Required Role(s):** `admin`  

#### Request
**Path Parameters:**
- `permission` (string/integer, required): Identifier of target resource.

**Request Body (`application/json`):**
```json
{
    "data": "sample_payload"
}
```

#### Response `200 OK` / `201 Created`
```json
{
    "success": true,
    "message": "Request processed successfully.",
    "data": {
        "id": 1,
        "status": "active",
        "created_at": "2026-08-31T12:00:00.000000Z"
    }
}
```

#### Response Errors
- `401 Unauthorized`: Authentication token missing, expired, or revoked.
- `403 Forbidden`: User does not have sufficient role permissions (``admin``).
- `404 Not Found`: Target resource was not found.
- `422 Unprocessable Entity`: Request body failed FormRequest validation.

#### Example cURL
```bash
curl -X PATCH "https://lostandfound.wollo.edu.et/api/v1/admin/permissions/{permission}/toggle-active" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer YOUR_SANCTUM_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"sample_field": "value"}'
```

---

### `GET /api/v1/admin/announcements`
<!-- source: routes/api.php -->
<!-- source: App/Http/Controllers/Api/V1/Admin/AnnouncementController.php -->
**Route Name:** `announcements.index`  
**Controller Action:** `\App\Http\Controllers\Api\V1\Admin\AnnouncementController@index`  
**Middleware Stack:** `api, auth:sanctum, role:admin`  
**Policy Enforcement:** None (Guarded by Middleware / Ownership check)  
**Required Role(s):** `admin`  

#### Request
#### Response `200 OK` / `201 Created`
```json
{
    "success": true,
    "message": "Request processed successfully.",
    "data": {
        "id": 1,
        "status": "active",
        "created_at": "2026-08-31T12:00:00.000000Z"
    }
}
```

#### Response Errors
- `401 Unauthorized`: Authentication token missing, expired, or revoked.
- `403 Forbidden`: User does not have sufficient role permissions (``admin``).
- `404 Not Found`: Target resource was not found.

#### Example cURL
```bash
curl -X GET "https://lostandfound.wollo.edu.et/api/v1/admin/announcements" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer YOUR_SANCTUM_TOKEN"
```

---

### `POST /api/v1/admin/announcements`
<!-- source: routes/api.php -->
<!-- source: App/Http/Controllers/Api/V1/Admin/AnnouncementController.php -->
**Route Name:** `announcements.store`  
**Controller Action:** `\App\Http\Controllers\Api\V1\Admin\AnnouncementController@store`  
**Middleware Stack:** `api, auth:sanctum, role:admin`  
**Policy Enforcement:** None (Guarded by Middleware / Ownership check)  
**Required Role(s):** `admin`  

#### Request
**Request Body (`application/json`):**
```json
{
    "data": "sample_payload"
}
```

#### Response `200 OK` / `201 Created`
```json
{
    "success": true,
    "message": "Request processed successfully.",
    "data": {
        "id": 1,
        "status": "active",
        "created_at": "2026-08-31T12:00:00.000000Z"
    }
}
```

#### Response Errors
- `401 Unauthorized`: Authentication token missing, expired, or revoked.
- `403 Forbidden`: User does not have sufficient role permissions (``admin``).
- `404 Not Found`: Target resource was not found.
- `422 Unprocessable Entity`: Request body failed FormRequest validation.

#### Example cURL
```bash
curl -X POST "https://lostandfound.wollo.edu.et/api/v1/admin/announcements" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer YOUR_SANCTUM_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"sample_field": "value"}'
```

---

### `GET /api/v1/admin/announcements/{announcement}`
<!-- source: routes/api.php -->
<!-- source: App/Http/Controllers/Api/V1/Admin/AnnouncementController.php -->
**Route Name:** `announcements.show`  
**Controller Action:** `\App\Http\Controllers\Api\V1\Admin\AnnouncementController@show`  
**Middleware Stack:** `api, auth:sanctum, role:admin`  
**Policy Enforcement:** None (Guarded by Middleware / Ownership check)  
**Required Role(s):** `admin`  

#### Request
**Path Parameters:**
- `announcement` (string/integer, required): Identifier of target resource.

#### Response `200 OK` / `201 Created`
```json
{
    "success": true,
    "message": "Request processed successfully.",
    "data": {
        "id": 1,
        "status": "active",
        "created_at": "2026-08-31T12:00:00.000000Z"
    }
}
```

#### Response Errors
- `401 Unauthorized`: Authentication token missing, expired, or revoked.
- `403 Forbidden`: User does not have sufficient role permissions (``admin``).
- `404 Not Found`: Target resource was not found.

#### Example cURL
```bash
curl -X GET "https://lostandfound.wollo.edu.et/api/v1/admin/announcements/{announcement}" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer YOUR_SANCTUM_TOKEN"
```

---

### `DELETE /api/v1/admin/announcements/{announcement}`
<!-- source: routes/api.php -->
<!-- source: App/Http/Controllers/Api/V1/Admin/AnnouncementController.php -->
**Route Name:** `announcements.destroy`  
**Controller Action:** `\App\Http\Controllers\Api\V1\Admin\AnnouncementController@destroy`  
**Middleware Stack:** `api, auth:sanctum, role:admin`  
**Policy Enforcement:** None (Guarded by Middleware / Ownership check)  
**Required Role(s):** `admin`  

#### Request
**Path Parameters:**
- `announcement` (string/integer, required): Identifier of target resource.

#### Response `200 OK` / `201 Created`
```json
{
    "success": true,
    "message": "Request processed successfully.",
    "data": {
        "id": 1,
        "status": "active",
        "created_at": "2026-08-31T12:00:00.000000Z"
    }
}
```

#### Response Errors
- `401 Unauthorized`: Authentication token missing, expired, or revoked.
- `403 Forbidden`: User does not have sufficient role permissions (``admin``).
- `404 Not Found`: Target resource was not found.

#### Example cURL
```bash
curl -X DELETE "https://lostandfound.wollo.edu.et/api/v1/admin/announcements/{announcement}" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer YOUR_SANCTUM_TOKEN"
```

---

### `GET /api/v1/admin/settings`
<!-- source: routes/api.php -->
<!-- source: App/Http/Controllers/Api/V1/Admin/SystemSettingController.php -->
**Route Name:** `None`  
**Controller Action:** `\App\Http\Controllers\Api\V1\Admin\SystemSettingController@index`  
**Middleware Stack:** `api, auth:sanctum, role:admin`  
**Policy Enforcement:** None (Guarded by Middleware / Ownership check)  
**Required Role(s):** `admin`  

#### Request
#### Response `200 OK` / `201 Created`
```json
{
    "success": true,
    "message": "Request processed successfully.",
    "data": {
        "id": 1,
        "status": "active",
        "created_at": "2026-08-31T12:00:00.000000Z"
    }
}
```

#### Response Errors
- `401 Unauthorized`: Authentication token missing, expired, or revoked.
- `403 Forbidden`: User does not have sufficient role permissions (``admin``).
- `404 Not Found`: Target resource was not found.

#### Example cURL
```bash
curl -X GET "https://lostandfound.wollo.edu.et/api/v1/admin/settings" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer YOUR_SANCTUM_TOKEN"
```

---

### `PUT /api/v1/admin/settings/{key}`
<!-- source: routes/api.php -->
<!-- source: App/Http/Controllers/Api/V1/Admin/SystemSettingController.php -->
**Route Name:** `None`  
**Controller Action:** `\App\Http\Controllers\Api\V1\Admin\SystemSettingController@update`  
**Middleware Stack:** `api, auth:sanctum, role:admin`  
**Policy Enforcement:** None (Guarded by Middleware / Ownership check)  
**Required Role(s):** `admin`  

#### Request
**Path Parameters:**
- `key` (string/integer, required): Identifier of target resource.

**Request Body (`application/json`):**
```json
{
    "data": "sample_payload"
}
```

#### Response `200 OK` / `201 Created`
```json
{
    "success": true,
    "message": "Request processed successfully.",
    "data": {
        "id": 1,
        "status": "active",
        "created_at": "2026-08-31T12:00:00.000000Z"
    }
}
```

#### Response Errors
- `401 Unauthorized`: Authentication token missing, expired, or revoked.
- `403 Forbidden`: User does not have sufficient role permissions (``admin``).
- `404 Not Found`: Target resource was not found.
- `422 Unprocessable Entity`: Request body failed FormRequest validation.

#### Example cURL
```bash
curl -X PUT "https://lostandfound.wollo.edu.et/api/v1/admin/settings/{key}" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer YOUR_SANCTUM_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"sample_field": "value"}'
```

---

### `GET /api/v1/admin/reports`
<!-- source: routes/api.php -->
<!-- source: App/Http/Controllers/Api/V1/Admin/ReportController.php -->
**Route Name:** `None`  
**Controller Action:** `\App\Http\Controllers\Api\V1\Admin\ReportController@index`  
**Middleware Stack:** `api, auth:sanctum, role:admin`  
**Policy Enforcement:** None (Guarded by Middleware / Ownership check)  
**Required Role(s):** `admin`  

#### Request
#### Response `200 OK` / `201 Created`
```json
{
    "success": true,
    "message": "Request processed successfully.",
    "data": {
        "id": 1,
        "status": "active",
        "created_at": "2026-08-31T12:00:00.000000Z"
    }
}
```

#### Response Errors
- `401 Unauthorized`: Authentication token missing, expired, or revoked.
- `403 Forbidden`: User does not have sufficient role permissions (``admin``).
- `404 Not Found`: Target resource was not found.

#### Example cURL
```bash
curl -X GET "https://lostandfound.wollo.edu.et/api/v1/admin/reports" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer YOUR_SANCTUM_TOKEN"
```

---

### `POST /api/v1/admin/reports/generate`
<!-- source: routes/api.php -->
<!-- source: App/Http/Controllers/Api/V1/Admin/ReportController.php -->
**Route Name:** `None`  
**Controller Action:** `\App\Http\Controllers\Api\V1\Admin\ReportController@generate`  
**Middleware Stack:** `api, auth:sanctum, role:admin`  
**Policy Enforcement:** None (Guarded by Middleware / Ownership check)  
**Required Role(s):** `admin`  

#### Request
**Request Body (`application/json`):**
```json
{
    "data": "sample_payload"
}
```

#### Response `200 OK` / `201 Created`
```json
{
    "success": true,
    "message": "Request processed successfully.",
    "data": {
        "id": 1,
        "status": "active",
        "created_at": "2026-08-31T12:00:00.000000Z"
    }
}
```

#### Response Errors
- `401 Unauthorized`: Authentication token missing, expired, or revoked.
- `403 Forbidden`: User does not have sufficient role permissions (``admin``).
- `404 Not Found`: Target resource was not found.
- `422 Unprocessable Entity`: Request body failed FormRequest validation.

#### Example cURL
```bash
curl -X POST "https://lostandfound.wollo.edu.et/api/v1/admin/reports/generate" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer YOUR_SANCTUM_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"sample_field": "value"}'
```

---

### `GET /api/v1/admin/reports/{id}/download`
<!-- source: routes/api.php -->
<!-- source: App/Http/Controllers/Api/V1/Admin/ReportController.php -->
**Route Name:** `None`  
**Controller Action:** `\App\Http\Controllers\Api\V1\Admin\ReportController@download`  
**Middleware Stack:** `api, auth:sanctum, role:admin`  
**Policy Enforcement:** None (Guarded by Middleware / Ownership check)  
**Required Role(s):** `admin`  

#### Request
**Path Parameters:**
- `id` (string/integer, required): Identifier of target resource.

#### Response `200 OK` / `201 Created`
```json
{
    "success": true,
    "message": "Request processed successfully.",
    "data": {
        "id": 1,
        "status": "active",
        "created_at": "2026-08-31T12:00:00.000000Z"
    }
}
```

#### Response Errors
- `401 Unauthorized`: Authentication token missing, expired, or revoked.
- `403 Forbidden`: User does not have sufficient role permissions (``admin``).
- `404 Not Found`: Target resource was not found.

#### Example cURL
```bash
curl -X GET "https://lostandfound.wollo.edu.et/api/v1/admin/reports/{id}/download" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer YOUR_SANCTUM_TOKEN"
```

---

### `GET /api/v1/admin/audit-logs`
<!-- source: routes/api.php -->
<!-- source: App/Http/Controllers/Api/V1/Admin/AuditLogController.php -->
**Route Name:** `None`  
**Controller Action:** `\App\Http\Controllers\Api\V1\Admin\AuditLogController@index`  
**Middleware Stack:** `api, auth:sanctum, role:admin`  
**Policy Enforcement:** None (Guarded by Middleware / Ownership check)  
**Required Role(s):** `admin`  

#### Request
#### Response `200 OK` / `201 Created`
```json
{
    "success": true,
    "message": "Request processed successfully.",
    "data": {
        "id": 1,
        "status": "active",
        "created_at": "2026-08-31T12:00:00.000000Z"
    }
}
```

#### Response Errors
- `401 Unauthorized`: Authentication token missing, expired, or revoked.
- `403 Forbidden`: User does not have sufficient role permissions (``admin``).
- `404 Not Found`: Target resource was not found.

#### Example cURL
```bash
curl -X GET "https://lostandfound.wollo.edu.et/api/v1/admin/audit-logs" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer YOUR_SANCTUM_TOKEN"
```

---

### `GET /api/v1/admin/audit-logs/export`
<!-- source: routes/api.php -->
<!-- source: App/Http/Controllers/Api/V1/Admin/AuditLogController.php -->
**Route Name:** `None`  
**Controller Action:** `\App\Http\Controllers\Api\V1\Admin\AuditLogController@exportCsv`  
**Middleware Stack:** `api, auth:sanctum, role:admin`  
**Policy Enforcement:** None (Guarded by Middleware / Ownership check)  
**Required Role(s):** `admin`  

#### Request
#### Response `200 OK` / `201 Created`
```json
{
    "success": true,
    "message": "Request processed successfully.",
    "data": {
        "id": 1,
        "status": "active",
        "created_at": "2026-08-31T12:00:00.000000Z"
    }
}
```

#### Response Errors
- `401 Unauthorized`: Authentication token missing, expired, or revoked.
- `403 Forbidden`: User does not have sufficient role permissions (``admin``).
- `404 Not Found`: Target resource was not found.

#### Example cURL
```bash
curl -X GET "https://lostandfound.wollo.edu.et/api/v1/admin/audit-logs/export" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer YOUR_SANCTUM_TOKEN"
```

---
