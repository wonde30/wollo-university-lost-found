import { onMounted, onUnmounted, watch } from 'vue'
import { useAuthStore } from '@/features/auth/stores/auth.store'
import { useNotificationsStore } from '@/features/notifications/stores/notifications.store'

/**
 * Root-level Notification Stream composable.
 * Delegates directly to the single-source-of-truth Pinia store.
 * Must only be invoked from root App.vue.
 */
export function useNotificationStream() {
  const authStore = useAuthStore()
  const notificationsStore = useNotificationsStore()
  let _initTimeout: number | null = null

  function initStream(): void {
    if (_initTimeout !== null) {
      clearTimeout(_initTimeout)
      _initTimeout = null
    }

    if (authStore.isAuthenticated && authStore.user?.id) {
      const userId = Number(authStore.user.id)
      // Defer background initialization to allow router navigation and primary page rendering to complete first
      _initTimeout = window.setTimeout(() => {
        notificationsStore.initializeForUser(userId)
      }, 150)
    } else {
      notificationsStore.reset()
    }
  }

  onMounted(() => {
    initStream()
  })

  watch(
    () => authStore.isAuthenticated,
    (isAuth) => {
      if (isAuth) {
        initStream()
      } else {
        if (_initTimeout !== null) {
          clearTimeout(_initTimeout)
          _initTimeout = null
        }
        notificationsStore.reset()
      }
    }
  )

  onUnmounted(() => {
    if (_initTimeout !== null) {
      clearTimeout(_initTimeout)
      _initTimeout = null
    }
    notificationsStore.disconnectRealtime()
  })

  return {
    connect: notificationsStore.connectRealtime,
    disconnect: notificationsStore.disconnectRealtime,
  }
}
