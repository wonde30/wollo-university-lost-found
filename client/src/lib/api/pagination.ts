/**
 * Pagination utilities for Laravel API requests.
 */

/**
 * Pagination parameters for list endpoints.
 * Based on Laravel's standard pagination query parameters.
 */
export interface PaginationParams {
  page?: number
  per_page?: number
  [key: string]: string | number | boolean | undefined
}

/**
 * Pagination metadata from Laravel API responses.
 */
export interface PaginationMeta {
  current_page: number
  last_page: number
  per_page: number
  total: number
  from?: number | null
  to?: number | null
  path?: string
}

/**
 * Pagination link URLs included in Laravel paginator responses.
 */
export interface PaginationLinks {
  first?: string | null
  last?: string | null
  prev?: string | null
  next?: string | null
}

/**
 * Paginated response structure from Laravel.
 */
export interface PaginatedResponse<T> {
  data: T[]
  meta: PaginationMeta
  links?: PaginationLinks
}

/**
 * Build pagination query string from parameters.
 * 
 * Converts PaginationParams into URL query parameters.
 * Filters out undefined values.
 */
export function buildPaginationQuery(params: PaginationParams): string {
  const searchParams = new URLSearchParams()

  Object.entries(params).forEach(([key, value]) => {
    if (value !== undefined && value !== null) {
      searchParams.append(key, String(value))
    }
  })

  const query = searchParams.toString()
  return query ? `?${query}` : ''
}
