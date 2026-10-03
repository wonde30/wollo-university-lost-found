/**
 * Auth types based on Laravel backend contract.
 * Source: AuthUserResource, UserResource, UserProfileResource, OrganizationalUnitResource
 */

import type { OrganizationalUnit } from '@/types/common.types'

export type UserRole = 'student' | 'staff' | 'admin' | string

export interface UserProfile {
  id: number
  user_id: number
  id_card_photo?: string | null
  year_of_study?: number | null
  gender?: string | null
  emergency_contact_name?: string | null
  emergency_contact_phone?: string | null
  home_town?: string | null
  bio?: string | null
  last_seen_at?: string | null
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
  role_id: number
  language: string
  is_active: boolean
  must_change_password?: boolean
  profile_photo: string | null
  profile_photo_url?: string | null
  avatar_url?: string | null
  permissions?: string[]
  direct_permissions?: string[]
  role_permissions?: string[]
  created_at?: string
  updated_at?: string
  profile?: UserProfile
  organizational_units?: OrganizationalUnit[]
}

// ==========================================
// Request Payloads
// ==========================================

export interface LoginCredentials {
  email: string
  password: string
  remember?: boolean
}

export interface RegisterData {
  full_name: string
  university_id: string
  email: string
  phone?: string
  organizational_unit_id?: number | null
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
// University Domain Types
// ==========================================

export interface PublicUniversityDomain {
  domain: string
  institution_name: string
}

export interface UniversityDomain {
  id: number
  domain: string
  institution_name: string
  campus_id?: number | null
  campus?: {
    id: number
    name: string
    code: string
  }
  is_active: boolean
  description?: string | null
  created_at?: string
  updated_at?: string
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
    email: string
    user?: User
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
