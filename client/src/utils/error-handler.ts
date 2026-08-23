import { ApiError } from '@/lib/http/errors'

/**
 * Extract human-readable error message from arbitrary error
 */
export function getErrorMessage(error: unknown, fallback: string = 'An unexpected error occurred'): string {
  if (error instanceof ApiError) {
    if (error.errors) {
      const firstField = Object.keys(error.errors)[0]
      if (firstField && error.errors[firstField]?.[0]) {
        return error.errors[firstField][0]
      }
    }
    return error.message || fallback
  }

  if (error instanceof Error) {
    return error.message
  }

  if (typeof error === 'string') {
    return error
  }

  return fallback
}

/**
 * Extract field-specific validation errors
 */
export function getValidationErrors(error: unknown): Record<string, string[]> | undefined {
  if (error instanceof ApiError && error.status === 422) {
    return error.errors
  }
  return undefined
}
