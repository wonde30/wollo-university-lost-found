/**
 * Auth API client for Laravel Sanctum authentication.
 * 
 * IMPORTANT: Uses session-cookie authentication, NOT bearer tokens.
 * Must call initCsrf() before any authenticated requests.
 */

import { apiClient } from '@/lib/http/client'
import { initCsrf } from '@/lib/http/csrf'
import { AUTH } from '@/lib/api/endpoints'
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
