import { createRouter, createWebHistory } from 'vue-router'
import { routes } from './routes'
import { useAuthStore } from '@/features/auth/stores/auth.store'
import { useSettingsStore } from '@/stores/settings.store'
import { ROUTE_NAMES } from './route-names'

export const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes,
})

// ==========================================
// Single global navigation guard
// ==========================================

router.beforeEach(async (to) => {
  const authStore = useAuthStore()

  // --- Auth initialization (runs once per session) ---
  // fetchUser() is internally deduplicated: concurrent navigations share
  // one inflight /auth/me request and it is skipped when already initialized.
  if (!authStore.initialized) {
    try {
      await authStore.fetchUser()
    } catch {
      // 401 is expected for guests — initialization still marked done inside the store
    }
  }

  // --- Guest guard: redirect authenticated users away from login/register ---
  const requiresGuest = to.matched.some(record => record.meta.requiresGuest)
  if (requiresGuest && authStore.isAuthenticated) {
    return { path: authStore.dashboardRoute }
  }

  // --- Dynamic Role, Capability & Permission Guard ---
  const isProtected = to.matched.some(record =>
    record.meta.requiresAuth ||
    record.meta.permission ||
    record.meta.capability ||
    (record.meta.roles && (record.meta.roles as string[]).length > 0)
  )

  if (isProtected) {
    if (!authStore.isAuthenticated || !authStore.user?.role) {
      return { name: ROUTE_NAMES.LOGIN, query: { redirect: to.fullPath } }
    }

    // Super-admin always has full access to all authenticated routes
    if (authStore.isAdmin) {
      return true
    }

    // 1. Explicit dynamic permission requirement (highest specificity)
    const requiredPermission = (to.meta.permission as string) || (to.matched.find(r => r.meta.permission)?.meta.permission as string)
    if (requiredPermission) {
      if (!authStore.can(requiredPermission)) {
        return { name: ROUTE_NAMES.FORBIDDEN }
      }
    }

    // 2. General portal capability requirement
    const requiredCapability = to.meta.capability || to.matched.find(r => r.meta.capability)?.meta.capability
    if (requiredCapability) {
      const cap = requiredCapability as 'admin' | 'staff' | 'student'
      if (cap === 'admin' && !authStore.canAccessAdminPortal) {
        return { name: ROUTE_NAMES.FORBIDDEN }
      }
      if (cap === 'staff' && !authStore.canAccessStaffPortal) {
        return { name: ROUTE_NAMES.FORBIDDEN }
      }
      if (cap === 'student' && !authStore.canAccessStudentPortal) {
        return { name: ROUTE_NAMES.FORBIDDEN }
      }
    }

    // 3. Fallback role check (if route explicitly demands a specific built-in role and no explicit permission was given)
    const requiredRoles = (to.meta.roles as string[]) || (to.matched.find(r => r.meta.roles)?.meta.roles as string[])
    if (requiredRoles && requiredRoles.length > 0 && !requiredPermission && !requiredCapability) {
      if (!requiredRoles.includes(authStore.user.role)) {
        return { name: ROUTE_NAMES.FORBIDDEN }
      }
    }
  }

  return true
})

// ==========================================
// Document title
// ==========================================

router.afterEach((to) => {
  const settingsStore = useSettingsStore()
  const suffix = settingsStore.siteName || 'Lost & Found'
  const pageTitle = to.meta.title as string | undefined
  document.title = pageTitle ? `${pageTitle} | ${suffix}` : suffix
})

export default router
