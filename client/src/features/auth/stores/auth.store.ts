/**
 * Authentication store using Pinia.
 * Manages user session state for Laravel Sanctum SPA authentication.
 */

import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import type { User, LoginCredentials, RegisterData } from '../types/auth.types'
import * as authApi from '../api/auth.api'

export const useAuthStore = defineStore('auth', () => {
  // ==========================================
  // State
  // ==========================================

  const user = ref<User | null>(null)
  const loading = ref(false)
  const initialized = ref(false)

  // Inflight guard: one /auth/me at a time, no matter how many concurrent callers
  let _fetchUserPromise: Promise<void> | null = null

  // ==========================================
  // Getters
  // ==========================================

  const isAuthenticated = computed(() => user.value !== null)
  const isAdmin = computed(() => user.value?.role === 'admin')
  const isStaff = computed(() => user.value?.role === 'staff')
  const isStudent = computed(() => user.value?.role === 'student')
  const hasRole = computed(() => (role: string | string[]) => {
    if (!user.value) return false
    const roles = Array.isArray(role) ? role : [role]
    return roles.includes(user.value.role)
  })
  const hasPermission = computed(() => (permission: string | string[]) => {
    if (!user.value) return false
    if (user.value.role === 'admin') return true
    const required = Array.isArray(permission) ? permission : [permission]
    const userPerms = user.value.permissions || []
    return required.some(p => userPerms.includes(p))
  })
  const can = hasPermission

  // Dynamic portal access based on authoritative role and permissions
  const canAccessAdminPortal = computed(() => 
    isAdmin.value || hasPermission.value('ACCESS_ADMIN_DASHBOARD') || hasPermission.value('MANAGE_USERS') || hasPermission.value('MANAGE_PERMISSIONS')
  )
  const canAccessStaffPortal = computed(() => 
    isStaff.value || 
    isAdmin.value || 
    user.value?.role === 'security_supervisor' || 
    user.value?.role === 'department_head' ||
    hasPermission.value('REVIEW_CLAIMS') || 
    hasPermission.value('MANAGE_CUSTODY') || 
    hasPermission.value('PROCESS_RETURNS') || 
    hasPermission.value('MANAGE_ALL_ITEMS')
  )
  const canAccessStudentPortal = computed(() => 
    isAuthenticated.value
  )
  const dashboardRoute = computed(() => {
    if (canAccessAdminPortal.value) return '/admin/dashboard'
    if (canAccessStaffPortal.value) return '/staff/dashboard'
    return '/student/dashboard'
  })

  // ==========================================
  // Actions
  // ==========================================

  /**
   * Internal implementation — not exported.
   * Callers must use fetchUser() which deduplicates inflight requests.
   */
  async function _doFetchUser(): Promise<void> {
    loading.value = true
    try {
      const response = await authApi.getCurrentUser()
      user.value = response.user || (response as any).data?.user || (response as any).data
      initialized.value = true
    } catch (error) {
      user.value = null
      initialized.value = true
      throw error
    } finally {
      loading.value = false
    }
  }

  /**
   * Fetch current user from backend.
   * Safe to call from multiple concurrent navigation guards — only one
   * /auth/me request will be in flight at any time. Subsequent callers
   * await the same promise and share the result.
   */
  async function fetchUser(force = false): Promise<void> {
    // Already resolved — skip entirely unless forced
    if (initialized.value && !force) return

    // A request is already in flight — return the same promise
    if (_fetchUserPromise) return _fetchUserPromise

    _fetchUserPromise = _doFetchUser().finally(() => {
      _fetchUserPromise = null
    })

    return _fetchUserPromise
  }

  /**
   * Set/update user state manually (e.g. after profile/avatar update).
   */
  function setUser(newUser: Partial<User> | null): void {
    if (!newUser) {
      user.value = null
      return
    }
    if (user.value) {
      user.value = { ...user.value, ...newUser }
    } else {
      user.value = newUser as User
    }
    initialized.value = true
  }

  /**
   * Login with email and password.
   */
  async function login(credentials: LoginCredentials): Promise<void> {
    loading.value = true
    try {
      const response = await authApi.login(credentials)
      user.value = response.user
      initialized.value = true
    } finally {
      loading.value = false
    }
  }

  /**
   * Register new user account.
   */
  async function register(registerData: RegisterData): Promise<void> {
    loading.value = true
    try {
      const response = await authApi.register(registerData)
      user.value = response.data.user
      initialized.value = true
    } finally {
      loading.value = false
    }
  }

  /**
   * Logout current user and clear session.
   */
  async function logout(): Promise<void> {
    loading.value = true
    try {
      await authApi.logout()
    } finally {
      user.value = null
      initialized.value = false
      loading.value = false
    }
  }

  /**
   * Clear user state without calling backend.
   * Called by the HTTP interceptor on 401 responses.
   */
  function clearUser(): void {
    user.value = null
    initialized.value = true
  }

  async function verifyEmail(email: string, code: string): Promise<void> {
    await authApi.verifyEmail({ email, code })
  }

  async function resendVerification(email: string, type?: 'email_verification' | 'password_reset'): Promise<void> {
    await authApi.resendVerification({ email, type })
  }

  async function forgotPassword(email: string): Promise<void> {
    await authApi.forgotPassword({ email })
  }

  async function verifyPasswordReset(email: string, code: string): Promise<string> {
    const response = await authApi.verifyPasswordReset({ email, code })
    return response.token
  }

  async function resetPassword(email: string, otp: string, password: string, password_confirmation: string): Promise<void> {
    await authApi.resetPassword({ email, otp, password, password_confirmation })
  }

  async function changePassword(currentPassword: string, newPassword: string, newPasswordConfirmation?: string): Promise<void> {
    await authApi.changePassword(currentPassword, newPassword, newPasswordConfirmation)
  }

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
    hasPermission,
    can,
    canAccessAdminPortal,
    canAccessStaffPortal,
    canAccessStudentPortal,
    dashboardRoute,

    // Actions
    fetchUser,
    setUser,
    login,
    register,
    logout,
    clearUser,
    verifyEmail,
    resendVerification,
    forgotPassword,
    verifyPasswordReset,
    resetPassword,
    changePassword,
  }
})
