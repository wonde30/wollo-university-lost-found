import type { User } from '@/features/auth/types/auth.types'
import type { UserRole } from '@/constants'
import type { PermissionKey } from './permissions'
import { usePermissionsStore } from './stores/permissions.store'

/**
 * Check if the user has permission to perform an action.
 * Evaluates dynamically against the active permissions store.
 */
export function can(user: User | null | undefined, permission: PermissionKey | string): boolean {
  if (!user || !user.role) return false
  const permissionsStore = usePermissionsStore()
  return permissionsStore.isPermissionAllowed(user.role as UserRole, permission)
}

/**
 * Check if the user has any of the specified roles.
 */
export function hasAnyRole(user: User | null | undefined, roles: (UserRole | string)[]): boolean {
  if (!user || !user.role) return false
  return roles.includes(user.role)
}
