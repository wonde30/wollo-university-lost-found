/**
 * User roles constants and derived types.
 * Authoritative single source of truth for user access levels.
 */

export const USER_ROLES = {
  ADMIN: 'admin',
  STAFF: 'staff',
  STUDENT: 'student',
} as const

export type UserRole = (typeof USER_ROLES)[keyof typeof USER_ROLES] | string
