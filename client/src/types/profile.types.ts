/**
 * Profile types for user profile management.
 * Based on Laravel backend ProfileController and related models.
 */

export interface UpdateProfileData {
  full_name?: string
  phone?: string
  bio?: string
  phone_number?: string
  student_staff_id?: string
  gender?: 'male' | 'female' | 'other'
  address?: string
  preferences?: Record<string, any>
}

export interface ChangePasswordData {
  current_password: string
  new_password: string
  new_password_confirmation: string
}

export interface UploadAvatarResponse {
  message: string
  avatar_url: string
}

