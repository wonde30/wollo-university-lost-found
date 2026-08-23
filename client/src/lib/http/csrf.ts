import { apiClient } from './client'

/**
 * Initialize CSRF protection by fetching the CSRF cookie from Laravel Sanctum.
 * 
 * Must be called before any authenticated requests (typically before login).
 * Laravel will set the XSRF-TOKEN cookie which Axios automatically reads.
 */
export async function initCsrf(): Promise<void> {
  await apiClient.get('/sanctum/csrf-cookie')
}
