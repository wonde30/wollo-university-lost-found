/**
 * Auth types based on Laravel backend contract.
 * Source: AuthUserResource, UserProfileResource, DepartmentResource
 */

export type UserRole = 'student' | 'staff' | 'admin'

export interface UserProfile {
  id: number
  user_id: number
  avatar_url: string | null
  bio: string | null
  phone_number: string | null
  student_staff_id: string | null
  gender: string | null
  address: string | null
  preferences: Record<string, unknown> | null
}

export interface Department {
  id: number
  campus_id: number
  name: string
  code: string
  description: string | null
  is_active: boolean
  campus?: Campus
  created_at: string
  updated_at: string
}

export interface Campus {
  id: number
  name: string
  code: string
  location: string | null
  is_active: boolean
  created_at: string
  updated_at: string
}

export interface User {
  id: number
  full_name: string
  name: string
  university_id: string
  email: string
  email_verified_at?: string | null
  phone: string | null
  role: UserRole
  language: string
  is_active: boolean
  profile_photo: string | null
  profile_photo_url?: string | null
  created_at?: string
  updated_at?: string
  profile?: UserProfile
  departments?: Department[]
}

// ==========================================
// Request Payloads
// ==========================================

export interface LoginCredentials {
  email: string
  password: string
  device_name?: string
}

export interface RegisterData {
  full_name: string
  university_id: string
  email: string
  password: string
  password_confirmation?: string
  phone?: string
  department_id?: number
}

export interface VerifyEmailData {
  email: string
  code: string
}

export interface ResendVerificationData {
  email: string
  type?: 'email_verification' | 'password_reset'
}

export interface ForgotPasswordData {
  email: string
}

export interface VerifyPasswordResetData {
  email: string
  code: string
}

export interface ResetPasswordData {
  email: string
  otp: string
  password: string
  password_confirmation: string
}

// ==========================================
// Response Types
// ==========================================

export interface LoginResponse {
  message: string
  user: User
}

export interface RegisterResponse {
  message: string
  data: {
    user: User
  }
}

export interface CurrentUserResponse {
  user: User
}

export interface VerifyPasswordResetResponse {
  message: string
  token: string
}

export interface MessageResponse {
  message: string
}
