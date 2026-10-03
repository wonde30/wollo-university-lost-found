import { describe, it, expect, beforeEach, vi } from 'vitest'
import { setActivePinia, createPinia } from 'pinia'
import { useAuthStore } from '../auth.store'
import type { User } from '../../types/auth.types'

vi.mock('../../api/auth.api', () => ({
  getCurrentUser: vi.fn(),
  login: vi.fn(),
  register: vi.fn(),
  logout: vi.fn().mockResolvedValue({}),
}))

describe('Auth Store & RBAC Logic', () => {
  beforeEach(() => {
    setActivePinia(createPinia())
    vi.clearAllMocks()
  })

  it('initializes with unauthenticated state', () => {
    const store = useAuthStore()
    expect(store.isAuthenticated).toBe(false)
    expect(store.user).toBeNull()
    expect(store.dashboardRoute).toBe('/student/dashboard')
  })

  it('computes roles and portal permissions for student role', () => {
    const store = useAuthStore()
    const studentUser = {
      id: 10,
      full_name: 'Student User',
      email: 'student@wollo.edu.et',
      role: 'student',
      permissions: ['CREATE_ITEM', 'VIEW_ITEMS'],
    } as unknown as User

    store.user = studentUser

    expect(store.isAuthenticated).toBe(true)
    expect(store.isStudent).toBe(true)
    expect(store.isAdmin).toBe(false)
    expect(store.isStaff).toBe(false)
    expect(store.canAccessAdminPortal).toBe(false)
    expect(store.canAccessStudentPortal).toBe(true)
    expect(store.dashboardRoute).toBe('/student/dashboard')
    expect(store.hasPermission('CREATE_ITEM')).toBe(true)
    expect(store.hasPermission('MANAGE_USERS')).toBe(false)
  })

  it('computes roles and portal permissions for staff role', () => {
    const store = useAuthStore()
    const staffUser = {
      id: 5,
      full_name: 'Staff Officer',
      email: 'staff@wollo.edu.et',
      role: 'staff',
      permissions: ['REVIEW_CLAIMS', 'MANAGE_CUSTODY'],
    } as unknown as User

    store.user = staffUser

    expect(store.isStaff).toBe(true)
    expect(store.canAccessStaffPortal).toBe(true)
    expect(store.canAccessAdminPortal).toBe(false)
    expect(store.dashboardRoute).toBe('/staff/dashboard')
    expect(store.hasPermission('REVIEW_CLAIMS')).toBe(true)
  })

  it('grants full access to admin role unconditionally', () => {
    const store = useAuthStore()
    const adminUser = {
      id: 1,
      full_name: 'Admin User',
      email: 'admin@wollo.edu.et',
      role: 'admin',
      permissions: [],
    } as unknown as User

    store.user = adminUser

    expect(store.isAdmin).toBe(true)
    expect(store.canAccessAdminPortal).toBe(true)
    expect(store.canAccessStaffPortal).toBe(true)
    expect(store.dashboardRoute).toBe('/admin/dashboard')
    // Admin has all permissions implicitly
    expect(store.hasPermission('ANY_ARBITRARY_PERMISSION')).toBe(true)
  })

  it('resets state on logout', async () => {
    const store = useAuthStore()
    store.user = {
      id: 1,
      full_name: 'Admin',
      email: 'admin@wollo.edu.et',
      role: 'admin',
    } as unknown as User

    await store.logout()

    expect(store.user).toBeNull()
    expect(store.isAuthenticated).toBe(false)
  })
})
