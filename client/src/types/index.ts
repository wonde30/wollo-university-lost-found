/**
 * Central type barrel for genuinely cross-cutting types.
 *
 * Rules for what belongs here:
 *   ✅ Types used by 3+ features that don't belong to any single domain
 *   ✅ Shared infrastructure types (pagination, API responses)
 *   ✅ Admin/platform management types (admin.types)
 *   ✅ Profile types (profile.types)
 *
 * Rules for what does NOT belong here:
 *   ❌ Feature-domain types that are authoritative in their feature folder
 *      (Item, Claim, Custody, Return, Notification, User)
 *      → import those directly from @/features/<name>/types/<name>.types
 */

// Shared infrastructure
export type { PaginationParams, PaginationMeta, PaginationLinks, PaginatedResponse } from '@/lib/api/pagination'
export type { ApiResponse } from '@/lib/api/response'

// Cross-cutting domain types: used by multiple features and have no single owner
export * from './common.types'

// Platform administration types
export * from '@/features/admin/types/admin.types'

// User profile management types
export * from './profile.types'

// API infrastructure error type
export type { ApiError } from '@/lib/http/errors'
