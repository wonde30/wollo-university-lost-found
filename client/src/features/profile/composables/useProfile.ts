/**
 * Composable for user profile management.
 * Provides profile update, avatar upload, and password change functionality.
 */

import { ref, computed, type Ref } from 'vue'
import { useAuthStore } from '@/features/auth/stores/auth.store'
import type { User } from '@/features/auth/types/auth.types'
import type { UpdateProfileData, ChangePasswordData } from '@/types/profile.types'
import * as profileApi from '../api/profile.api'

export function useProfile() {
  const authStore = useAuthStore()
  const loading = ref(false)
  const error: Ref<Error | null> = ref(null)

  // Current user from auth store
  const user = computed(() => authStore.user)

  /**
   * Update profile information.
   */
  async function updateProfile(data: UpdateProfileData): Promise<User> {
    loading.value = true
    error.value = null
    try {
      const updatedUser = await profileApi.updateProfile(data)
      
      if (updatedUser && typeof updatedUser === 'object' && updatedUser.id) {
        authStore.setUser(updatedUser)
      } else {
        // Fallback refresh
        await authStore.fetchUser(true)
      }
      
      return authStore.user || updatedUser
    } catch (err) {
      error.value = err as Error
      throw err
    } finally {
      loading.value = false
    }
  }

  /**
   * Upload profile avatar.
   */
  async function uploadAvatar(file: File): Promise<string> {
    loading.value = true
    error.value = null
    try {
      const response = await profileApi.uploadAvatar(file)
      
      if (response.user && response.user.id) {
        authStore.setUser(response.user)
      } else {
        await authStore.fetchUser(true)
      }
      
      return response.avatar_url || authStore.user?.profile_photo || ''
    } catch (err) {
      error.value = err as Error
      throw err
    } finally {
      loading.value = false
    }
  }

  /**
   * Change user password.
   */
  async function changePassword(data: ChangePasswordData): Promise<void> {
    loading.value = true
    error.value = null
    try {
      await profileApi.changePassword(data)
    } catch (err) {
      error.value = err as Error
      throw err
    } finally {
      loading.value = false
    }
  }

  /**
   * Save profile (combined update + avatar if provided).
   */
  async function saveProfile(data: UpdateProfileData & { avatar?: File }): Promise<User | null> {
    loading.value = true
    error.value = null
    try {
      // Upload avatar if provided
      if (data.avatar) {
        await uploadAvatar(data.avatar)
      }
      
      // Only call updateProfile if text fields were provided
      const { avatar, ...profileData } = data
      const hasTextFields = Object.values(profileData).some(val => val !== undefined && val !== null && val !== '')
      
      if (hasTextFields) {
        return await updateProfile(profileData)
      }
      
      return authStore.user
    } catch (err) {
      error.value = err as Error
      throw err
    } finally {
      loading.value = false
    }
  }

  return {
    // State
    user,
    loading,
    error,

    // Actions
    updateProfile,
    uploadAvatar,
    changePassword,
    saveProfile,
  }
}
