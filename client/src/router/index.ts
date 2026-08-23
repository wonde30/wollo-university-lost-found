/**
 * Vue Router configuration.
 * Single authoritative beforeEach guard — no duplicate auth initialization.
 */

import { createRouter, createWebHistory } from 'vue-router'
import { routes } from './routes'
import { useAuthStore } from '@/features/auth/stores/auth.store'
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

  // --- Auth guard ---
  if (to.meta.requiresAuth && !authStore.isAuthenticated) {
    return { name: ROUTE_NAMES.LOGIN, query: { redirect: to.fullPath } }
  }

  // --- Guest guard: redirect authenticated users away from login/register ---
  if (to.meta.requiresGuest && authStore.isAuthenticated) {
    switch (authStore.user?.role) {
      case 'admin':  return { name: ROUTE_NAMES.ADMIN_DASHBOARD }
      case 'staff':  return { name: ROUTE_NAMES.STAFF_DASHBOARD }
      case 'student': return { name: ROUTE_NAMES.STUDENT_DASHBOARD }
      default:        return { name: ROUTE_NAMES.HOME }
    }
  }

  // --- Role guard ---
  if (to.meta.roles && (to.meta.roles as string[]).length > 0) {
    if (!authStore.isAuthenticated || !authStore.user?.role) {
      return { name: ROUTE_NAMES.FORBIDDEN }
    }
    if (!(to.meta.roles as string[]).includes(authStore.user.role)) {
      return { name: ROUTE_NAMES.FORBIDDEN }
    }
  }

  return true
})

// ==========================================
// Document title
// ==========================================

router.afterEach((to) => {
  document.title = (to.meta.title as string | undefined) ?? 'Wollo Lost & Found'
})

export default router
