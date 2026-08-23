import type { AxiosError } from 'axios'

/**
 * Custom API error class that preserves Laravel validation errors.
 */
export class ApiError extends Error {
  status: number
  errors?: Record<string, string[]>

  constructor(message: string, status: number, errors?: Record<string, string[]>) {
    super(message)
    this.name = 'ApiError'
    this.status = status
    this.errors = errors
  }
}

/**
 * Normalize Axios/backend errors into a consistent ApiError format.
 * 
 * Preserves Laravel validation error structure (422 responses).
 */
export function normalizeError(error: unknown): ApiError {
  if (error instanceof ApiError) {
    return error
  }

  const axiosError = error as AxiosError<{
    message?: string
    errors?: Record<string, string[]>
  }>

  const status = axiosError.response?.status || 500
  const message = axiosError.response?.data?.message || axiosError.message || 'An unexpected error occurred'
  const errors = axiosError.response?.data?.errors

  return new ApiError(message, status, errors)
}
