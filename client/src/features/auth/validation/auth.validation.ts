import { required, isEmail, minLength } from '@/validation/common.validation'
import type { LoginCredentials, RegisterData, ForgotPasswordData, ResetPasswordData } from '../types/auth.types'

export function validateLoginForm(data: LoginCredentials): Record<string, string> {
  const errors: Record<string, string> = {}

  const emailReq = required(data.email, 'Email')
  if (emailReq) errors.email = emailReq
  else {
    const emailFormat = isEmail(data.email)
    if (emailFormat) errors.email = emailFormat
  }

  const passReq = required(data.password, 'Password')
  if (passReq) errors.password = passReq

  return errors
}

export function validateRegisterForm(data: RegisterData): Record<string, string> {
  const errors: Record<string, string> = {}

  const nameReq = required(data.full_name, 'Full name')
  if (nameReq) errors.full_name = nameReq

  const idReq = required(data.university_id, 'University ID')
  if (idReq) errors.university_id = idReq

  const emailReq = required(data.email, 'Email')
  if (emailReq) errors.email = emailReq
  else {
    const emailFormat = isEmail(data.email)
    if (emailFormat) errors.email = emailFormat
  }

  const passReq = required(data.password, 'Password')
  if (passReq) errors.password = passReq
  else {
    const passMin = minLength(8, data.password, 'Password')
    if (passMin) errors.password = passMin
  }

  if (data.password_confirmation !== undefined && data.password !== data.password_confirmation) {
    errors.password_confirmation = 'Passwords do not match.'
  }

  return errors
}

export function validateForgotPasswordForm(data: ForgotPasswordData): Record<string, string> {
  const errors: Record<string, string> = {}
  const emailReq = required(data.email, 'Email')
  if (emailReq) errors.email = emailReq
  else {
    const emailFormat = isEmail(data.email)
    if (emailFormat) errors.email = emailFormat
  }
  return errors
}

export function validateResetPasswordForm(data: ResetPasswordData & { password_confirmation?: string }): Record<string, string> {
  const errors: Record<string, string> = {}

  const emailReq = required(data.email, 'Email')
  if (emailReq) errors.email = emailReq

  const otpReq = required(data.otp, 'OTP code')
  if (otpReq) errors.otp = otpReq

  const passReq = required(data.password, 'Password')
  if (passReq) errors.password = passReq
  else {
    const passMin = minLength(8, data.password, 'Password')
    if (passMin) errors.password = passMin
  }

  if (data.password_confirmation && data.password !== data.password_confirmation) {
    errors.password_confirmation = 'Passwords do not match.'
  }

  return errors
}
