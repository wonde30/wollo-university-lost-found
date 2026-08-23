/**
 * Common validation rules and helper functions
 */

export function required(val: unknown, fieldName: string = 'Field'): string | null {
  if (val === undefined || val === null || val === '' || (Array.isArray(val) && val.length === 0)) {
    return `${fieldName} is required.`
  }
  return null
}

export function isEmail(val: string): string | null {
  if (!val) return null
  const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/
  if (!emailRegex.test(val)) {
    return 'Please enter a valid email address.'
  }
  return null
}

export function minLength(min: number, val: string, fieldName: string = 'Field'): string | null {
  if (!val) return null
  if (val.length < min) {
    return `${fieldName} must be at least ${min} characters.`
  }
  return null
}

export function maxLength(max: number, val: string, fieldName: string = 'Field'): string | null {
  if (!val) return null
  if (val.length > max) {
    return `${fieldName} must not exceed ${max} characters.`
  }
  return null
}
