/**
 * Auth composable for convenient access to auth store.
 * Provides reactive authentication state and methods.
 */

import { storeToRefs } from 'pinia'
import { useAuthStore } from '../stores/auth.store'

export function useAuth() {
  const authStore = useAuthStore()
  
  // Extract reactive state
  const { user, loading, initialized, isAuthenticated, isAdmin, isStaff, isStudent, hasRole } = storeToRefs(authStore)
  
  return {
    // State
    user,
    loading,
    initialized,
    
    // Getters
    isAuthenticated,
    isAdmin,
    isStaff,
    isStudent,
    hasRole,
    
    // Actions
    login: authStore.login,
    register: authStore.register,
    fetchUser: authStore.fetchUser,
    logout: authStore.logout,
    clearUser: authStore.clearUser,
    verifyEmail: authStore.verifyEmail,
    resendVerification: authStore.resendVerification,
    forgotPassword: authStore.forgotPassword,
    verifyPasswordReset: authStore.verifyPasswordReset,
    resetPassword: authStore.resetPassword,
  }
}
