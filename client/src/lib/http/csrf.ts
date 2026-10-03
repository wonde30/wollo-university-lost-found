import { apiClient } from './client'

/**
 * Check if the XSRF-TOKEN cookie is already present in document.cookie.
 */
export function hasCsrfCookie(): boolean {
  if (typeof document === 'undefined') return false
  return document.cookie.split(';').some(c => c.trim().startsWith('XSRF-TOKEN='))
}

let _csrfPromise: Promise<void> | null = null

/**
 * Initialize CSRF protection by fetching the CSRF cookie from Laravel Sanctum.
 *
 * Performance Optimizations:
 * 1. Checks if XSRF-TOKEN cookie is already present in document.cookie. If present
 *    and not forced, skips the network round-trip entirely (0ms).
 * 2. Deduplicates concurrent inflight requests into a single shared promise.
 * 3. Supports `force = true` for automatic 419 token mismatch recovery.
 */
export async function initCsrf(force = false): Promise<void> {
  if (!force && hasCsrfCookie()) {
    return
  }

  if (_csrfPromise) {
    return _csrfPromise
  }

  _csrfPromise = apiClient.get('/sanctum/csrf-cookie')
    .then(() => {})
    .catch((err) => {
      throw err
    })
    .finally(() => {
      _csrfPromise = null
    })

  return _csrfPromise
}
