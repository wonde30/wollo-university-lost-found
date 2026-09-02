import { ref, onMounted, onUnmounted, watch } from 'vue'
import { useAuthStore } from '@/features/auth/stores/auth.store'
import { useNotificationsStore } from '@/features/notifications/stores/notifications.store'
import type { Notification } from '@/features/notifications/types/notification.types'

// Singleton EventSource instance outside component scope
const eventSourceInstance = ref<EventSource | null>(null)
const isStreamConnected = ref(false)
let reconnectTimer: number | null = null
let reconnectAttempts = 0

/**
 * Close and tear down existing EventSource connection.
 */
function closeStream(): void {
  if (reconnectTimer !== null) {
    clearTimeout(reconnectTimer)
    reconnectTimer = null
  }
  if (eventSourceInstance.value) {
    try {
      eventSourceInstance.value.close()
    } catch {
      // ignore
    }
    eventSourceInstance.value = null
  }
  isStreamConnected.value = false
}

/**
 * Open singleton EventSource connection.
 * Guarantees previous instance is terminated before creating a new one.
 */
function openStream(): void {
  if (typeof window === 'undefined') return

  // Close any existing connection first
  closeStream()

  const authStore = useAuthStore()
  if (!authStore.isAuthenticated) return

  const notificationsStore = useNotificationsStore()
  const baseUrl = import.meta.env.VITE_API_URL || 'http://localhost:8000'
  const latestId = notificationsStore.notifications.length > 0
    ? Math.max(...notificationsStore.notifications.map(n => Number(n.id) || 0))
    : undefined

  const streamUrl = `${baseUrl}/api/v1/notifications/stream${latestId ? `?last_id=${latestId}` : ''}`

  try {
    const es = new EventSource(streamUrl, { withCredentials: true })
    eventSourceInstance.value = es

    es.onopen = () => {
      isStreamConnected.value = true
      notificationsStore.isConnected = true
      reconnectAttempts = 0
    }

    es.addEventListener('notification', (event: MessageEvent) => {
      try {
        const newNotif = JSON.parse(event.data) as Notification
        notificationsStore.handleIncomingNotification(newNotif)
      } catch (parseErr) {
        console.error('Failed to parse incoming notification:', parseErr)
      }
    })

    es.onerror = () => {
      isStreamConnected.value = false
      notificationsStore.isConnected = false

      // If browser is actively reconnecting in CONNECTING state, let native EventSource retry interval handle it
      if (eventSourceInstance.value && eventSourceInstance.value.readyState === EventSource.CONNECTING) {
        return
      }

      // If permanently closed, close cleanly and schedule a long-interval reconnect (60s)
      closeStream()

      const delay = Math.min(30000 * Math.pow(1.5, reconnectAttempts), 120000)
      reconnectAttempts++

      reconnectTimer = window.setTimeout(() => {
        const currentAuth = useAuthStore()
        if (currentAuth.isAuthenticated) {
          openStream()
        }
      }, delay)
    }
  } catch (err) {
    console.warn('Real-time notification stream initialization failed:', err)
    isStreamConnected.value = false
    notificationsStore.isConnected = false
  }
}


/**
 * Root-level Notification Stream composable.
 * Must only be invoked from root App.vue.
 */
export function useNotificationStream() {
  const authStore = useAuthStore()
  const notificationsStore = useNotificationsStore()

  onMounted(() => {
    if (authStore.isAuthenticated) {
      if (notificationsStore.notifications.length === 0) {
        notificationsStore.fetchNotifications().catch(() => {})
      }
      openStream()
    }
  })

  watch(
    () => authStore.isAuthenticated,
    (isAuth) => {
      if (isAuth) {
        if (notificationsStore.notifications.length === 0) {
          notificationsStore.fetchNotifications().catch(() => {})
        }
        openStream()
      } else {
        closeStream()
      }
    }
  )

  onUnmounted(() => {
    closeStream()
  })

  return {
    eventSource: eventSourceInstance,
    isConnected: isStreamConnected,
    connect: openStream,
    disconnect: closeStream,
  }
}
