/**
 * Generic API response types based on actual Laravel responses.
 */

/**
 * Standard single resource response.
 * Used for most endpoints that return a single resource with optional message.
 */
export interface ApiResponse<T> {
  message?: string
  data?: T
  user?: T // For auth endpoints (login, register, me)
}

/**
 * Paginated response structure from Laravel.
 * Based on ItemController::index response format.
 */
export interface PaginatedResponse<T> {
  data: T[]
  meta: PaginationMeta
}

/**
 * Pagination metadata from Laravel paginator.
 */
export interface PaginationMeta {
  current_page: number
  last_page: number
  per_page: number
  total: number
}

/**
 * Validation error response (422).
 */
export interface ValidationErrorResponse {
  message: string
  errors: Record<string, string[]>
}

/**
 * Generic error response.
 */
export interface ErrorResponse {
  message: string
}
