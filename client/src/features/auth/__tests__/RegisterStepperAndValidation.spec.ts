import { describe, it, expect, beforeEach, vi } from 'vitest'
import { setActivePinia, createPinia } from 'pinia'
import { useAuthStore } from '../stores/auth.store'
import * as authApi from '../api/auth.api'
import { validateRegisterForm } from '../validation/auth.validation'
import { t, currentLocale } from '@/i18n'

describe('University Registration & Stepper Flow Tests', () => {
  beforeEach(() => {
    setActivePinia(createPinia())
    vi.restoreAllMocks()
    currentLocale.value = 'en'
  })

  it('validates required fields without client password', () => {
    const emptyErrors = validateRegisterForm({
      full_name: '',
      university_id: '',
      email: '',
    })

    expect(emptyErrors.full_name).toBeDefined()
    expect(emptyErrors.university_id).toBeDefined()
    expect(emptyErrors.email).toBeDefined()
    expect(emptyErrors.password).toBeUndefined()

    const validErrors = validateRegisterForm({
      full_name: 'Almaz Ayana',
      university_id: 'WU/554433/14',
      email: 'almaz@wu.edu.et',
    })

    expect(Object.keys(validErrors).length).toBe(0)
  })

  it('auth store initiates registration without authenticating pending user', async () => {
    const authStore = useAuthStore()

    vi.spyOn(authApi, 'register').mockResolvedValueOnce({
      message: 'Registration initiated. OTP sent.',
      data: {
        email: 'almaz@wu.edu.et',
      },
    })

    const result = await authStore.register({
      full_name: 'Almaz Ayana',
      university_id: 'WU/554433/14',
      email: 'almaz@wu.edu.et',
    })

    expect(result.email).toBe('almaz@wu.edu.et')
    expect(authStore.isAuthenticated).toBe(false)
    expect(authStore.user).toBeNull()
  })

  it('auth store verifies email OTP successfully', async () => {
    const authStore = useAuthStore()

    const verifySpy = vi.spyOn(authApi, 'verifyEmail').mockResolvedValueOnce({
      message: 'Email verified. Credentials sent.',
    })

    await authStore.verifyEmail('almaz@wu.edu.et', '654321')

    expect(verifySpy).toHaveBeenCalledWith({
      email: 'almaz@wu.edu.et',
      code: '654321',
    })
  })

  it('auth store detects must_change_password on first login', async () => {
    const authStore = useAuthStore()

    vi.spyOn(authApi, 'login').mockResolvedValueOnce({
      message: 'Login successful.',
      user: {
        id: 101,
        full_name: 'Almaz Ayana',
        name: 'Almaz Ayana',
        university_id: 'WU/554433/14',
        email: 'almaz@wu.edu.et',
        phone: null,
        role: 'student',
        role_id: 3,
        language: 'en',
        is_active: true,
        must_change_password: true,
        profile_photo: null,
      },
    })

    await authStore.login({ email: 'almaz@wu.edu.et', password: 'TempGeneratedPass123!#' })

    expect(authStore.isAuthenticated).toBe(true)
    expect(authStore.user?.must_change_password).toBe(true)
  })

  it('translates registration stepper and credentials keys symmetrically in EN and AM', () => {
    currentLocale.value = 'en'
    expect(t('auth.credentialsSent')).toContain('credentials have been sent')
    expect(t('auth.step1Title')).toBe('Institutional Details')
    expect(t('auth.step2Title')).toBe('OTP Verification')
    expect(t('auth.step3Title')).toBe('Credentials Delivered')

    currentLocale.value = 'am'
    expect(t('auth.credentialsSent')).toContain('የመለያዎ መግቢያ መረጃ')
    expect(t('auth.step1Title')).toBe('የተቋሙ መረጃ')
    expect(t('auth.step2Title')).toBe('የኢሜይል ማረጋገጫ (OTP)')
    expect(t('auth.step3Title')).toBe('ማግበር ተጠናቋል')
  })
})
