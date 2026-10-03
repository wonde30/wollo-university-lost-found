/**
 * Auth API client for Laravel Sanctum authentication.
 * 
 * IMPORTANT: Uses session-cookie authentication, NOT bearer tokens.
 * Must call initCsrf() before any authenticated requests.
 */

import { apiClient } from '@/lib/http/client'
import { initCsrf } from '@/lib/http/csrf'
import { AUTH, PROFILE, PUBLIC, ADMIN } from '@/lib/api/endpoints'
import type {
  LoginCredentials,
  LoginResponse,
  RegisterData,
  RegisterResponse,
  CurrentUserResponse,
  VerifyEmailData,
  ResendVerificationData,
  ForgotPasswordData,
  VerifyPasswordResetData,
  VerifyPasswordResetResponse,
  ResetPasswordData,
  MessageResponse,
  PublicUniversityDomain,
  UniversityDomain,
} from '../types/auth.types'

/**
 * Authenticate user with email and password.
 * Establishes session-cookie authentication.
 */
export async function login(credentials: LoginCredentials): Promise<LoginResponse> {
  await initCsrf()
  const { data } = await apiClient.post<LoginResponse>(AUTH.LOGIN, credentials)
  return data
}

/**
 * Register new user account.
 * Automatically logs in user after registration.
 */
export async function register(registerData: RegisterData): Promise<RegisterResponse> {
  await initCsrf()
  const { data } = await apiClient.post<RegisterResponse>(AUTH.REGISTER, registerData)
  return data
}

/**
 * Get current authenticated user.
 */
export async function getCurrentUser(): Promise<CurrentUserResponse> {
  const { data } = await apiClient.get<CurrentUserResponse>(AUTH.ME)
  return data
}

/**
 * Logout current user and destroy session.
 */
export async function logout(): Promise<MessageResponse> {
  const { data } = await apiClient.post<MessageResponse>(AUTH.LOGOUT)
  return data
}

/**
 * Change password for authenticated user.
 */
export async function changePassword(
  currentPassword: string,
  newPassword: string,
  newPasswordConfirmation?: string
): Promise<MessageResponse> {
  const { data } = await apiClient.put<MessageResponse>(AUTH.PASSWORD, {
    current_password: currentPassword,
    new_password: newPassword,
    new_password_confirmation: newPasswordConfirmation || newPassword,
  })
  return data
}

/**
 * Verify email with OTP code.
 */
export async function verifyEmail(verifyData: VerifyEmailData): Promise<MessageResponse> {
  const { data } = await apiClient.post<MessageResponse>(AUTH.VERIFY_EMAIL, verifyData)
  return data
}

/**
 * Resend email verification or password reset OTP.
 */
export async function resendVerification(resendData: ResendVerificationData): Promise<MessageResponse> {
  const { data } = await apiClient.post<MessageResponse>(AUTH.RESEND_VERIFICATION, resendData)
  return data
}

/**
 * Request password reset OTP.
 */
export async function forgotPassword(forgotData: ForgotPasswordData): Promise<MessageResponse> {
  const { data } = await apiClient.post<MessageResponse>(AUTH.FORGOT_PASSWORD, forgotData)
  return data
}

/**
 * Verify password reset OTP.
 * Returns token for reset step.
 */
export async function verifyPasswordReset(verifyData: VerifyPasswordResetData): Promise<VerifyPasswordResetResponse> {
  const { data } = await apiClient.post<VerifyPasswordResetResponse>(AUTH.VERIFY_PASSWORD_RESET, verifyData)
  return data
}

/**
 * Reset password with OTP and new password.
 */
export async function resetPassword(resetData: ResetPasswordData): Promise<MessageResponse> {
  const { data } = await apiClient.post<MessageResponse>(AUTH.RESET_PASSWORD, resetData)
  return data
}

export interface UserSummaryData {
  my_lost_count: number
  my_found_count: number
  my_claims_count: number
  active_claims_count: number
  resolved_claims_count: number
  returned_items_count: number
}

/**
 * Get personal statistical summary for authenticated user.
 */
export async function getUserSummary(): Promise<UserSummaryData> {
  const { data } = await apiClient.get<{ data: UserSummaryData }>(PROFILE.SUMMARY)
  return data.data
}

/**
 * Get active university domains for registration discovery.
 */
export async function getPublicUniversityDomains(): Promise<PublicUniversityDomain[]> {
  const { data } = await apiClient.get<{ data: PublicUniversityDomain[] }>(PUBLIC.UNIVERSITY_DOMAINS)
  return data.data
}

/**
 * Admin: Get university domains list with pagination and search.
 */
export async function getAdminUniversityDomains(params?: {
  page?: number
  per_page?: number
  search?: string
  is_active?: boolean
  all?: boolean
}): Promise<{ data: UniversityDomain[]; meta?: any }> {
  const { data } = await apiClient.get(ADMIN.UNIVERSITY_DOMAINS, { params })
  return data
}

/**
 * Admin: Create university domain.
 */
export async function createAdminUniversityDomain(domainData: {
  domain: string
  institution_name: string
  campus_id?: number | null
  is_active?: boolean
  description?: string | null
}): Promise<{ message: string; data: UniversityDomain }> {
  const { data } = await apiClient.post(ADMIN.UNIVERSITY_DOMAINS, domainData)
  return data
}

/**
 * Admin: Update university domain.
 */
export async function updateAdminUniversityDomain(
  id: number,
  domainData: Partial<{
    domain: string
    institution_name: string
    campus_id?: number | null
    is_active?: boolean
    description?: string | null
  }>
): Promise<{ message: string; data: UniversityDomain }> {
  const { data } = await apiClient.put(ADMIN.UNIVERSITY_DOMAIN(id), domainData)
  return data
}

/**
 * Admin: Delete university domain.
 */
export async function deleteAdminUniversityDomain(id: number): Promise<{ message: string }> {
  const { data } = await apiClient.delete(ADMIN.UNIVERSITY_DOMAIN(id))
  return data
}

/**
 * Admin: Toggle university domain active status.
 */
export async function toggleAdminUniversityDomainActive(id: number): Promise<{ message: string; data: UniversityDomain }> {
  const { data } = await apiClient.patch(ADMIN.UNIVERSITY_DOMAIN_TOGGLE_ACTIVE(id))
  return data
}
