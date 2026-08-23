/**
 * Profile API client for user profile management.
 * Handles profile updates, avatar upload, and password changes.
 */

import { apiClient } from '@/lib/http/client'
import { PROFILE, AUTH } from '@/lib/api/endpoints'
import type { ApiResponse } from '@/lib/api/response'
import type { User } from '@/features/auth/types/auth.types'
import type { UpdateProfileData, ChangePasswordData } from '@/types/profile.types'

export interface ProfileApiResponse {
  message?: string
  user?: User
  data?: { user?: User } | User
}

export interface AvatarApiResponse {
  message?: string
  user?: User
  avatar_url?: string | null
  data?: { user?: User; avatar_url?: string | null }
}

// ==========================================
// Profile Management
// ==========================================

/**
 * Update user profile information.
 */
export async function updateProfile(data: UpdateProfileData): Promise<User> {
  const { data: response } = await apiClient.put<ProfileApiResponse>(PROFILE.UPDATE, data)
  
  if (response.user) return response.user
  if (response.data && typeof response.data === 'object' && 'user' in response.data && response.data.user) {
    return response.data.user
  }
  if (response.data && typeof response.data === 'object' && 'id' in response.data) {
    return response.data as User
  }
  return response as unknown as User
}

/**
 * Upload profile avatar/photo.
 */
export async function uploadAvatar(file: File): Promise<{ message: string; user?: User; avatar_url?: string }> {
  const formData = new FormData()
  formData.append('avatar', file)
  
  const { data: response } = await apiClient.post<AvatarApiResponse>(PROFILE.UPLOAD_AVATAR, formData, {
    headers: { 'Content-Type': 'multipart/form-data' },
  })
  
  const user = response.user || (response.data && 'user' in response.data ? response.data.user : undefined)
  const rawAvatarUrl = response.avatar_url || (response.data && 'avatar_url' in response.data ? response.data.avatar_url : user?.profile_photo)
  const avatarUrl = rawAvatarUrl || undefined

  return {
    message: response.message || 'Avatar uploaded successfully.',
    user,
    avatar_url: avatarUrl,
  }
}

/**
 * Change user password.
 */
export async function changePassword(data: ChangePasswordData): Promise<void> {
  await apiClient.put<ApiResponse<void>>(AUTH.PASSWORD, data)
}

