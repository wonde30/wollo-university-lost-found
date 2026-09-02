/**
 * Dynamic RBAC permissions store with real database synchronization and reactive state management.
 * Provides instant cache display (0ms waiting), background revalidation, and reactive CRUD actions.
 */

import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import type { UserRole } from '@/constants'
import {
  PERMISSION_DEFINITIONS,
  DEFAULT_ROLE_PERMISSIONS,
  type PermissionKey,
  type PermissionDefinition,
} from '../permissions'
import * as permissionsApi from '@/features/admin/api/permissions.api'
import * as permissionGroupsApi from '@/features/admin/api/permission-groups.api'
import * as rolesApi from '@/features/admin/api/roles.api'
import type {
  Permission,
  PermissionGroup,
  Role,
  CreatePermissionPayload,
  UpdatePermissionPayload,
  CreatePermissionGroupPayload,
  UpdatePermissionGroupPayload,
  CreateRolePayload,
  UpdateRolePayload,
} from '@/features/admin/types/admin.types'

const STORAGE_KEY = 'wu_permissions_matrix_v1'
const CACHE_PERMS_KEY = 'wu_permissions_cache_v1'
const CACHE_GROUPS_KEY = 'wu_permission_groups_cache_v1'
const CACHE_ROLES_KEY = 'wu_roles_cache_v1'

export const usePermissionsStore = defineStore('permissions', () => {
  // ==========================================
  // State: hydrated from localStorage for instantaneous 0ms display
  // ==========================================
  const permissions = ref<Permission[]>(loadFromStorage<Permission[]>(CACHE_PERMS_KEY, []))
  const permissionGroups = ref<PermissionGroup[]>(loadFromStorage<PermissionGroup[]>(CACHE_GROUPS_KEY, []))
  const roles = ref<Role[]>(loadFromStorage<Role[]>(CACHE_ROLES_KEY, []))

  const loading = ref(permissions.value.length === 0)
  const refreshing = ref(false)
  const initialized = ref(permissions.value.length > 0)
  const error = ref<string | null>(null)

  // Reactive mapping of PermissionKey -> UserRole[] for matrix grid
  const matrix = ref<Record<string, string[]>>(loadInitialMatrix())

  function loadFromStorage<T>(key: string, fallback: T): T {
    try {
      const saved = localStorage.getItem(key)
      if (saved) return JSON.parse(saved)
    } catch {
      // Fallback
    }
    return fallback
  }

  function saveToStorage(key: string, data: any): void {
    try {
      localStorage.setItem(key, JSON.stringify(data))
    } catch (e) {
      console.warn(`Failed to persist ${key}:`, e)
    }
  }

  function loadInitialMatrix(): Record<string, string[]> {
    try {
      const saved = localStorage.getItem(STORAGE_KEY)
      if (saved) {
        const parsed = JSON.parse(saved)
        const merged: Record<string, string[]> = {}
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

  function rebuildDynamicMatrix(perms: Permission[], rList: Role[]): void {
    const dynamicMatrix: Record<string, string[]> = {}
    for (const p of perms) {
      dynamicMatrix[p.name] = []
    }

    for (const r of rList) {
      for (const p of r.permissions || []) {
        if (!dynamicMatrix[p.name]) {
          dynamicMatrix[p.name] = []
        }
        if (!dynamicMatrix[p.name].includes(r.name)) {
          dynamicMatrix[p.name].push(r.name)
        }
      }
    }

    matrix.value = { ...matrix.value, ...dynamicMatrix }
    saveMatrix()
  }

  // Inflight guard to deduplicate concurrent requests
  let _fetchPromise: Promise<void> | null = null

  // ==========================================
  // Core Initializer: Stale-While-Revalidate
  // ==========================================
  async function fetchDbPermissions(force = false): Promise<void> {
    if (_fetchPromise) return _fetchPromise

    if (permissions.value.length === 0 || roles.value.length === 0 || force) {
      loading.value = true
    } else {
      refreshing.value = true
    }

    _fetchPromise = (async () => {
      try {
        const [fetchedPerms, fetchedRoles, fetchedGroups] = await Promise.all([
          permissionsApi.getAllPermissions(),
          rolesApi.getAllActiveRoles(),
          permissionGroupsApi.getAllActivePermissionGroups(),
        ])

        permissions.value = fetchedPerms
        roles.value = fetchedRoles
        permissionGroups.value = fetchedGroups

        saveToStorage(CACHE_PERMS_KEY, fetchedPerms)
        saveToStorage(CACHE_ROLES_KEY, fetchedRoles)
        saveToStorage(CACHE_GROUPS_KEY, fetchedGroups)

        rebuildDynamicMatrix(fetchedPerms, fetchedRoles)
        initialized.value = true
        error.value = null
      } catch (err: any) {
        error.value = err.message || 'Failed to sync permissions'
      } finally {
        loading.value = false
        refreshing.value = false
        _fetchPromise = null
      }
    })()

    return _fetchPromise
  }

  // ==========================================
  // Permission Group Actions
  // ==========================================
  async function addPermissionGroup(payload: CreatePermissionGroupPayload): Promise<PermissionGroup> {
    const created = await permissionGroupsApi.createPermissionGroup(payload)
    permissionGroups.value.unshift(created)
    saveToStorage(CACHE_GROUPS_KEY, permissionGroups.value)
    return created
  }

  async function updatePermissionGroup(id: number, payload: UpdatePermissionGroupPayload): Promise<PermissionGroup> {
    const updated = await permissionGroupsApi.updatePermissionGroup(id, payload)
    const idx = permissionGroups.value.findIndex(g => g.id === id)
    if (idx !== -1) {
      permissionGroups.value[idx] = { ...permissionGroups.value[idx], ...updated }
      saveToStorage(CACHE_GROUPS_KEY, permissionGroups.value)
    }
    return updated
  }

  async function toggleGroupActive(id: number): Promise<PermissionGroup> {
    const updated = await permissionGroupsApi.togglePermissionGroupActive(id)
    const idx = permissionGroups.value.findIndex(g => g.id === id)
    if (idx !== -1) {
      permissionGroups.value[idx].is_active = updated.is_active
      saveToStorage(CACHE_GROUPS_KEY, permissionGroups.value)
    }
    return updated
  }

  async function removePermissionGroup(id: number): Promise<void> {
    await permissionGroupsApi.deletePermissionGroup(id)
    permissionGroups.value = permissionGroups.value.filter(g => g.id !== id)
    saveToStorage(CACHE_GROUPS_KEY, permissionGroups.value)
  }

  // ==========================================
  // Permission Actions
  // ==========================================
  async function addPermission(payload: CreatePermissionPayload): Promise<Permission> {
    const created = await permissionsApi.createPermission(payload)
    permissions.value.unshift(created)
    saveToStorage(CACHE_PERMS_KEY, permissions.value)

    // Update group's permissions count
    const group = permissionGroups.value.find(g => g.id === created.permission_group_id)
    if (group) {
      group.permissions_count = (group.permissions_count || 0) + 1
    }

    rebuildDynamicMatrix(permissions.value, roles.value)
    return created
  }

  async function updatePermission(id: number, payload: UpdatePermissionPayload): Promise<Permission> {
    const updated = await permissionsApi.updatePermission(id, payload)
    const idx = permissions.value.findIndex(p => p.id === id)
    if (idx !== -1) {
      permissions.value[idx] = { ...permissions.value[idx], ...updated }
      saveToStorage(CACHE_PERMS_KEY, permissions.value)
    }
    rebuildDynamicMatrix(permissions.value, roles.value)
    return updated
  }

  async function togglePermActive(id: number): Promise<Permission> {
    const updated = await permissionsApi.togglePermissionActive(id)
    const idx = permissions.value.findIndex(p => p.id === id)
    if (idx !== -1) {
      permissions.value[idx].is_active = updated.is_active
      saveToStorage(CACHE_PERMS_KEY, permissions.value)
    }
    return updated
  }

  async function removePermission(id: number): Promise<void> {
    const perm = permissions.value.find(p => p.id === id)
    await permissionsApi.deletePermission(id)
    permissions.value = permissions.value.filter(p => p.id !== id)
    saveToStorage(CACHE_PERMS_KEY, permissions.value)

    if (perm && perm.permission_group_id) {
      const group = permissionGroups.value.find(g => g.id === perm.permission_group_id)
      if (group && (group.permissions_count || 0) > 0) {
        group.permissions_count = (group.permissions_count || 1) - 1
      }
    }

    rebuildDynamicMatrix(permissions.value, roles.value)
  }

  // ==========================================
  // Role Actions
  // ==========================================
  async function addRole(payload: CreateRolePayload): Promise<Role> {
    const created = await rolesApi.createRole(payload)
    roles.value.unshift(created)
    saveToStorage(CACHE_ROLES_KEY, roles.value)
    rebuildDynamicMatrix(permissions.value, roles.value)
    return created
  }

  async function updateRole(id: number, payload: UpdateRolePayload): Promise<Role> {
    const updated = await rolesApi.updateRole(id, payload)
    const idx = roles.value.findIndex(r => r.id === id)
    if (idx !== -1) {
      roles.value[idx] = { ...roles.value[idx], ...updated }
      saveToStorage(CACHE_ROLES_KEY, roles.value)
    }
    rebuildDynamicMatrix(permissions.value, roles.value)
    return updated
  }

  async function toggleRoleActive(id: number): Promise<Role> {
    const role = roles.value.find(r => r.id === id)
    if (!role) throw new Error('Role not found')
    const updated = await rolesApi.toggleRoleActive(id, role.is_active)
    const idx = roles.value.findIndex(r => r.id === id)
    if (idx !== -1) {
      roles.value[idx].is_active = updated.is_active
      saveToStorage(CACHE_ROLES_KEY, roles.value)
    }
    return updated
  }

  async function removeRole(id: number): Promise<void> {
    await rolesApi.deleteRole(id)
    roles.value = roles.value.filter(r => r.id !== id)
    saveToStorage(CACHE_ROLES_KEY, roles.value)
    rebuildDynamicMatrix(permissions.value, roles.value)
  }

  // ==========================================
  // Role & Matrix DB Synchronization Actions
  // ==========================================
  async function toggleRolePermission(roleName: string, permissionKey: string): Promise<boolean> {
    const role = roles.value.find(r => r.name === roleName)
    if (!role) {
      throw new Error(`Role "${roleName}" not found`)
    }

    const perm = permissions.value.find(p => p.name === permissionKey)
    if (!perm) {
      throw new Error(`Permission "${permissionKey}" not found`)
    }

    // Determine current permission IDs for this role
    let currentPermIds: number[] = []
    if (role.permissions && Array.isArray(role.permissions)) {
      currentPermIds = role.permissions.map(p => p.id)
    } else if (role.permission_ids && Array.isArray(role.permission_ids)) {
      currentPermIds = [...role.permission_ids]
    }

    const hasPerm = currentPermIds.includes(perm.id)
    const newPermIds = hasPerm
      ? currentPermIds.filter(id => id !== perm.id)
      : [...currentPermIds, perm.id]

    // Sync directly to the database via API
    const updated = await rolesApi.syncRolePermissions(role.id, newPermIds)

    // Update in-memory and local cache roles state
    const idx = roles.value.findIndex(r => r.id === role.id)
    if (idx !== -1) {
      roles.value[idx] = { ...roles.value[idx], ...updated }
      saveToStorage(CACHE_ROLES_KEY, roles.value)
    }

    rebuildDynamicMatrix(permissions.value, roles.value)

    return !hasPerm
  }

  // ==========================================
  // Matrix and Authorization helpers
  // ==========================================
  const allDefinitions = computed<PermissionDefinition[]>(() => {
    if (permissions.value.length > 0) {
      return permissions.value.map(p => ({
        key: p.name,
        label: p.display_name,
        description: p.description || '',
        category: (p.category as 'items' | 'claims' | 'custody' | 'admin') || 'items',
        defaultRoles: ['student', 'staff', 'admin'],
      }))
    }
    return Object.values(PERMISSION_DEFINITIONS)
  })

  function isPermissionAllowed(role: UserRole | string | undefined | null, permission: PermissionKey | string): boolean {
    if (!role) return false
    if (role === 'admin') return true

    const dbRole = roles.value.find(r => r.name === role)
    if (dbRole) {
      if (dbRole.permissions && Array.isArray(dbRole.permissions)) {
        return dbRole.permissions.some(p => p.name === permission && (p.is_active === undefined || p.is_active))
      }
      if (dbRole.permission_keys && Array.isArray(dbRole.permission_keys)) {
        return dbRole.permission_keys.includes(permission)
      }
    }

    const allowedRoles = matrix.value[permission] || DEFAULT_ROLE_PERMISSIONS[permission as PermissionKey] || []
    return allowedRoles.includes(role)
  }

  function togglePermission(role: string, permission: PermissionKey | string): boolean {
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

  function setPermission(role: string, permission: PermissionKey | string, allowed: boolean): void {
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
    // State
    permissions,
    permissionGroups,
    roles,
    loading,
    refreshing,
    initialized,
    matrix,
    allDefinitions,

    // Core Init & Sync
    fetchDbPermissions,
    fetchRoles: fetchDbPermissions,
    syncWithDatabase: fetchDbPermissions,

    // Group Actions
    addPermissionGroup,
    updatePermissionGroup,
    toggleGroupActive,
    removePermissionGroup,

    // Permission Actions
    addPermission,
    updatePermission,
    togglePermActive,
    removePermission,

    // Role Actions
    addRole,
    updateRole,
    toggleRoleActive,
    removeRole,
    toggleRolePermission,

    // RBAC Evaluation
    isPermissionAllowed,
    togglePermission,
    setPermission,
    resetToDefaults,
    getPermissionsByCategory,
  }
})
