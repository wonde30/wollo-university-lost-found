/**
 * Thin composable wrapper around useAdminUsersStore.
 * State is shared via the store — UsersPage and UserDetailPage
 * never maintain separate user arrays.
 */

import { storeToRefs } from 'pinia'
import { useAdminUsersStore } from '../stores/admin-users.store'

export function useAdminUsers() {
  const store = useAdminUsersStore()

  const { users, currentUser, loading, error, pagination } = storeToRefs(store)

  return {
    users,
    currentUser,
    loading,
    error,
    pagination,
    fetchUsers:       store.fetchUsers,
    fetchUser:        store.fetchUser,
    createUser:       store.createUser,
    updateUser:       store.updateUser,
    updateUserRole:   store.updateUserRole,
    toggleUserActive: store.toggleUserActive,
  }
}
