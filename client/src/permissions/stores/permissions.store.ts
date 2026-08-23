import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import type { UserRole } from '@/constants'
import {
  PERMISSION_DEFINITIONS,
  DEFAULT_ROLE_PERMISSIONS,
  type PermissionKey,
  type PermissionDefinition,
} from '../permissions'

const STORAGE_KEY = 'wu_permissions_matrix_v1'

export const usePermissionsStore = defineStore('permissions', () => {
  // Reactive mapping of PermissionKey -> UserRole[]
  const matrix = ref<Record<string, UserRole[]>>(loadInitialMatrix())

  function loadInitialMatrix(): Record<string, UserRole[]> {
    try {
      const saved = localStorage.getItem(STORAGE_KEY)
      if (saved) {
        const parsed = JSON.parse(saved)
        // Merge with default definitions in case new permissions were added
        const merged: Record<string, UserRole[]> = {}
        for (const key of Object.keys(PERMISSION_DEFINITIONS)) {
          merged[key] = parsed[key] ? [...parsed[key]] : [...DEFAULT_ROLE_PERMISSIONS[key as PermissionKey]]
        }
        return merged
      }
    } catch {
      // Fallback
    }
    return JSON.parse(JSON.stringify(DEFAULT_ROLE_PERMISSIONS))
  }

  function saveMatrix(): void {
    try {
      localStorage.setItem(STORAGE_KEY, JSON.stringify(matrix.value))
    } catch (e) {
      console.warn('Failed to persist permissions matrix to localStorage:', e)
    }
  }

  const allDefinitions = computed<PermissionDefinition[]>(() => {
    return Object.values(PERMISSION_DEFINITIONS)
  })

  function isPermissionAllowed(role: UserRole | string | undefined | null, permission: PermissionKey | string): boolean {
    if (!role) return false
    const allowedRoles = matrix.value[permission] || DEFAULT_ROLE_PERMISSIONS[permission as PermissionKey] || []
    return allowedRoles.includes(role as UserRole)
  }

  function togglePermission(role: UserRole, permission: PermissionKey | string): boolean {
    if (!matrix.value[permission]) {
      matrix.value[permission] = [...(DEFAULT_ROLE_PERMISSIONS[permission as PermissionKey] || [])]
    }
    const list = matrix.value[permission]
    const idx = list.indexOf(role)
    if (idx !== -1) {
      list.splice(idx, 1)
    } else {
      list.push(role)
    }
    saveMatrix()
    return list.includes(role)
  }

  function setPermission(role: UserRole, permission: PermissionKey | string, allowed: boolean): void {
    if (!matrix.value[permission]) {
      matrix.value[permission] = [...(DEFAULT_ROLE_PERMISSIONS[permission as PermissionKey] || [])]
    }
    const list = matrix.value[permission]
    const idx = list.indexOf(role)
    if (allowed && idx === -1) {
      list.push(role)
    } else if (!allowed && idx !== -1) {
      list.splice(idx, 1)
    }
    saveMatrix()
  }

  function resetToDefaults(): void {
    matrix.value = JSON.parse(JSON.stringify(DEFAULT_ROLE_PERMISSIONS))
    saveMatrix()
  }

  function getPermissionsByCategory(category: 'items' | 'claims' | 'custody' | 'admin'): PermissionDefinition[] {
    return allDefinitions.value.filter(def => def.category === category)
  }

  return {
    matrix,
    allDefinitions,
    isPermissionAllowed,
    togglePermission,
    setPermission,
    resetToDefaults,
    getPermissionsByCategory,
  }
})
