/**
 * Admin Users store using Pinia.
 * Single source of truth for the admin user list and current user detail.
 * UsersPage and UserDetailPage share this state, preventing duplicate requests.
 */

import { defineStore } from 'pinia'
import { ref } from 'vue'
import * as adminApi from '../api/admin.api'
import type { User } from '@/features/auth/types/auth.types'
import type { PaginationMeta } from '@/types/common.types'
import type { UserListParams, UpdateUserData, UpdateUserRoleData } from '../types/admin.types'

export const useAdminUsersStore = defineStore('adminUsers', () => {
  // ==========================================
  // State
  // ==========================================

  const users = ref<User[]>([])
  const currentUser = ref<User | null>(null)
  const loading = ref(false)
  const error = ref<string | null>(null)
  const pagination = ref<PaginationMeta>({
    current_page: 1,
    last_page: 1,
    per_page: 20,
    total: 0,
  })

  // ==========================================
  // Actions
  // ==========================================

  async function fetchUsers(params: UserListParams = {}): Promise<void> {
    if (users.value.length === 0) {
      loading.value = true
    }
    error.value = null
    try {
      const response = await adminApi.getUsers(params)
      users.value = response.data
      pagination.value = response.meta
    } catch (err: any) {
      error.value = err.message ?? 'Failed to load users'
    } finally {
      loading.value = false
    }
  }

  async function fetchUser(id: number): Promise<User | null> {
    loading.value = true
    error.value = null
    try {
      const user = await adminApi.getUser(id)
      currentUser.value = user
      return user
    } catch (err: any) {
      error.value = err.message ?? 'Failed to load user'
      return null
    } finally {
      loading.value = false
    }
  }

  async function updateUser(id: number, data: UpdateUserData): Promise<User | null> {
    loading.value = true
    error.value = null
    try {
      const updated = await adminApi.updateUser(id, data)
      _patchInList(updated)
      if (currentUser.value?.id === id) currentUser.value = updated
      return updated
    } catch (err: any) {
      error.value = err.message ?? 'Failed to update user'
      throw err
    } finally {
      loading.value = false
    }
  }

  async function updateUserRole(userId: number, role: string): Promise<void> {
    loading.value = true
    error.value = null
    try {
      const roleData: UpdateUserRoleData = { role: role as UpdateUserRoleData['role'] }
      const updated = await adminApi.updateUserRole(userId, roleData)
      _patchInList(updated)
      if (currentUser.value?.id === userId) currentUser.value = updated
    } catch (err: any) {
      error.value = err.message ?? 'Failed to update user role'
      throw err
    } finally {
      loading.value = false
    }
  }

  async function toggleUserActive(userId: number): Promise<void> {
    loading.value = true
    error.value = null
    try {
      const updated = await adminApi.toggleUserActive(userId)
      _patchInList(updated)
      if (currentUser.value?.id === userId) currentUser.value = updated
    } catch (err: any) {
      error.value = err.message ?? 'Failed to toggle user status'
      throw err
    } finally {
      loading.value = false
    }
  }

  // ==========================================
  // Internal helpers
  // ==========================================

  function _patchInList(updated: User): void {
    const index = users.value.findIndex(u => u.id === updated.id)
    if (index !== -1) {
      users.value[index] = updated
    }
  }

  return {
    users,
    currentUser,
    loading,
    error,
    pagination,
    fetchUsers,
    fetchUser,
    updateUser,
    updateUserRole,
    toggleUserActive,
  }
})
