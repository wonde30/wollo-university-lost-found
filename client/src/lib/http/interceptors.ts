import type { AxiosError, InternalAxiosRequestConfig } from 'axios'
import { apiClient } from './client'
import { initCsrf } from './csrf'
import { normalizeError } from './errors'

/**
 * Track retry attempts to prevent infinite loops.
 */
const retryAttempts = new WeakMap<InternalAxiosRequestConfig, number>()

/**
 * Install request interceptor.
 * 
 * No authorization header needed - cookies handle authentication automatically.
 */
apiClient.interceptors.request.use(
  (config) => {
    // No modifications needed - credentials are automatic via withCredentials: true
    return config
  },
  (error) => {
    return Promise.reject(normalizeError(error))
  }
)

/**
 * Install response interceptor.
 * 
 * Handles:
 * - 401: Unauthenticated - clear user session and propagate error
 * - 419: CSRF token mismatch - reinitialize and retry once
 * - 422: Validation errors - preserve Laravel validation structure
 * - 500: Server errors - normalize and propagate
 */
apiClient.interceptors.response.use(
  (response) => response,
  async (error: AxiosError) => {
    const config = error.config as InternalAxiosRequestConfig
    const status = error.response?.status

    // 401: Unauthenticated
    // Clear user session on authentication failure
    if (status === 401) {
      // Dynamically import to avoid circular dependency
      const { useAuthStore } = await import('@/features/auth/stores/auth.store')
      const authStore = useAuthStore()
      authStore.clearUser()
      
      return Promise.reject(normalizeError(error))
    }

    // 419: CSRF token mismatch - attempt to refresh and retry once
    if (status === 419 && config) {
      const attempts = retryAttempts.get(config) || 0

      if (attempts === 0) {
        retryAttempts.set(config, 1)
        
        try {
          await initCsrf()
          return apiClient.request(config)
        } catch (csrfError) {
          return Promise.reject(normalizeError(csrfError))
        }
      }
    }

    // 422: Validation errors - preserve Laravel validation structure
    if (status === 422) {
      return Promise.reject(normalizeError(error))
    }

    // 500: Server errors
    if (status === 500) {
      return Promise.reject(normalizeError(error))
    }

    // All other errors
    return Promise.reject(normalizeError(error))
  }
)
