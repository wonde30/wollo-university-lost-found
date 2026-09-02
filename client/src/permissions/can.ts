import type { User } from '@/features/auth/types/auth.types'
import type { UserRole } from '@/constants'
import type { PermissionKey } from './permissions'

/**
 * Check if the user has permission to perform an action.
 * Evaluates against the authoritative user effective permissions from /me or login.
 */
export function can(user: User | null | undefined, permission: PermissionKey | string): boolean {
  if (!user) return false
  if (user.role === 'admin') return true
  const userPerms = user.permissions || []
  return userPerms.includes(permission)
}

/**
 * Check if the user has any of the specified roles.
 */
export function hasAnyRole(user: User | null | undefined, roles: (UserRole | string)[]): boolean {
  if (!user || !user.role) return false
  return roles.includes(user.role)
}
