/**
 * Notifications store using Pinia.
 * Manages user notifications and preferences state with real-time server-push stream and background recovery.
 */

import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import type { Notification, NotificationListParams, NotificationPreference, UpdateNotificationPreferencesData } from '../types/notification.types'
import * as notificationsApi from '../api/notifications.api'
import { useUiStore } from '@/stores/ui.store'
import { t } from '@/i18n'

export const useNotificationsStore = defineStore('notifications', () => {
  // State
  const notifications = ref<Notification[]>([])
  const preferences = ref<NotificationPreference | null>(null)
  const loading = ref(false)
  const isConnected = ref(false)
  const isPolling = ref(false)
  const serverUnreadCount = ref<number>(0)
  const pagination = ref<{ current_page: number; last_page: number; total: number } | null>(null)

  let _eventSource: EventSource | null = null
  let _reconnectTimer: number | null = null
  let _reconnectAttempts = 0
  let _pollTimer: number | null = null

  // Getters
  const unreadCount = computed(() => {
    if (notifications.value.length > 0) {
      return notifications.value.filter(n => !n.read_at && !n.is_read).length
    }
    return serverUnreadCount.value
  })

  const unreadNotifications = computed(() => {
    return notifications.value.filter(n => !n.read_at && !n.is_read)
  })

  const readNotifications = computed(() => {
    return notifications.value.filter(n => Boolean(n.read_at || n.is_read))
  })

  // Safe deduplication helper
  function deduplicateAndMerge(incoming: Notification[]): void {
    const existingMap = new Map<string, Notification>()
    notifications.value.forEach(n => existingMap.set(String(n.id), n))
    
    incoming.forEach(n => {
      existingMap.set(String(n.id), n)
    })

    notifications.value = Array.from(existingMap.values()).sort(
      (a, b) => new Date(b.created_at).getTime() - new Date(a.created_at).getTime()
    )
  }

  let _fetchingNotifications = false

  // Actions

  /**
   * Fetch notifications with fetch-once guard and inflight deduplication.
   */
  async function fetchNotifications(filters?: NotificationListParams, silent = false): Promise<void> {
    if (notifications.value.length > 0 && !filters) return // already loaded
    if (_fetchingNotifications) return // request in flight
    _fetchingNotifications = true

    if (!silent && notifications.value.length === 0) {
      loading.value = true
    }

    try {
      const res = await notificationsApi.getNotifications(filters, { per_page: 20 })
      notifications.value = res.data
      pagination.value = res.meta
      serverUnreadCount.value = res.unread_count ?? 0
    } finally {
      _fetchingNotifications = false
      if (!silent) {
        loading.value = false
      }
    }
  }

  function handleIncomingNotification(newNotif: Notification): void {
    const exists = notifications.value.some(n => String(n.id) === String(newNotif.id))
    if (!exists) {
      notifications.value.unshift(newNotif)
      if (!newNotif.read_at && !newNotif.is_read) {
        serverUnreadCount.value++
      }

      try {
        const uiStore = useUiStore()
        const msg = newNotif.data?.message || t('notifications.title')
        uiStore.info(msg)
      } catch {
        // UI fallback
      }
    }
  }

  async function loadMore(): Promise<void> {
    if (!pagination.value || pagination.value.current_page >= pagination.value.last_page) {
      return
    }

    loading.value = true
    try {
      const res = await notificationsApi.getNotifications(
        {},
        { page: pagination.value.current_page + 1, per_page: 20 }
      )
      deduplicateAndMerge(res.data)
      pagination.value = res.meta
      serverUnreadCount.value = res.unread_count ?? 0
    } finally {
      loading.value = false
    }
  }

  async function markAsRead(id: string | number): Promise<void> {
    await notificationsApi.markNotificationAsRead(id)

    const target = notifications.value.find(n => String(n.id) === String(id))
    if (target) {
      target.is_read = true
      target.read_at = new Date().toISOString()
    }
    if (serverUnreadCount.value > 0) {
      serverUnreadCount.value--
    }
  }

  const markRead = markAsRead // Alias

  async function markAllAsRead(): Promise<void> {
    await notificationsApi.markAllNotificationsAsRead()

    notifications.value.forEach(n => {
      if (!n.read_at && !n.is_read) {
        n.is_read = true
        n.read_at = new Date().toISOString()
      }
    })
    serverUnreadCount.value = 0
  }

  const markAllRead = markAllAsRead // Alias

  async function fetchPreferences(): Promise<void> {
    loading.value = true
    try {
      preferences.value = await notificationsApi.getNotificationPreferences()
    } finally {
      loading.value = false
    }
  }

  async function updatePreferences(data: UpdateNotificationPreferencesData): Promise<void> {
    loading.value = true
    try {
      preferences.value = await notificationsApi.updateNotificationPreferences(data)
    } finally {
      loading.value = false
    }
  }

  // ==========================================
  // Real-Time Server-Push (SSE Stream)
  // ==========================================

  function connectRealtime(): void {
    if (typeof window === 'undefined') return
    if (_eventSource && _eventSource.readyState !== EventSource.CLOSED) return

    const baseUrl = import.meta.env.VITE_API_URL || 'http://localhost:8000'
    const latestId = notifications.value.length > 0
      ? Math.max(...notifications.value.map(n => Number(n.id) || 0))
      : undefined

    const streamUrl = `${baseUrl}/api/v1/notifications/stream${latestId ? `?last_id=${latestId}` : ''}`

    try {
      _eventSource = new EventSource(streamUrl, { withCredentials: true })

      _eventSource.onopen = () => {
        isConnected.value = true
        _reconnectAttempts = 0
      }

      _eventSource.addEventListener('notification', (event: MessageEvent) => {
        try {
          const newNotif = JSON.parse(event.data) as Notification
          // Prevent duplicates by ID
          const exists = notifications.value.some(n => String(n.id) === String(newNotif.id))
          if (!exists) {
            notifications.value.unshift(newNotif)
            if (!newNotif.read_at && !newNotif.is_read) {
              serverUnreadCount.value++
            }

            // Trigger in-app toast for active user
            try {
              const uiStore = useUiStore()
              const msg = newNotif.data?.message || t('notifications.title')
              uiStore.info(msg)
            } catch {
              // UI store fallback
            }
          }
        } catch (parseErr) {
          console.error('Failed to parse incoming notification:', parseErr)
        }
      })

      _eventSource.onerror = () => {
        // If browser is natively reconnecting, do not tear down the EventSource instance
        if (_eventSource && _eventSource.readyState === EventSource.CONNECTING) {
          isConnected.value = false
          return
        }

        // Permanent close / network error
        isConnected.value = false
        disconnectRealtime(false)

        // Exponential backoff reconnect: 1.5s, 3s, 6s... max 30s
        const delay = Math.min(1500 * Math.pow(1.5, _reconnectAttempts), 30000)
        _reconnectAttempts++

        _reconnectTimer = window.setTimeout(() => {
          connectRealtime()
        }, delay)
      }
    } catch (err) {
      console.warn('Real-time notification stream setup failed, falling back to reconciler:', err)
      isConnected.value = false
    }
  }

  function disconnectRealtime(resetAttempts = true): void {
    if (_eventSource) {
      _eventSource.close()
      _eventSource = null
    }
    if (_reconnectTimer !== null) {
      clearTimeout(_reconnectTimer)
      _reconnectTimer = null
    }
    if (resetAttempts) {
      _reconnectAttempts = 0
    }
    isConnected.value = false
  }

  // Backup reconciler polling (only runs when SSE stream is disconnected)
  function startPolling(intervalMs = 60000): void {
    if (_pollTimer !== null) return
    isPolling.value = true

    _pollTimer = window.setInterval(() => {
      if (!isConnected.value && typeof document !== 'undefined' && document.visibilityState === 'visible') {
        fetchNotifications(undefined, true).catch(() => {})
      }
    }, intervalMs)
  }

  function stopPolling(): void {
    if (_pollTimer !== null) {
      clearInterval(_pollTimer)
      _pollTimer = null
    }
    isPolling.value = false
  }

  return {
    // State
    notifications,
    preferences,
    loading,
    isConnected,
    isPolling,
    pagination,
    serverUnreadCount,

    // Getters
    unreadCount,
    unreadNotifications,
    readNotifications,

    // Actions
    fetchNotifications,
    handleIncomingNotification,
    loadMore,
    markAsRead,
    markRead,
    markAllAsRead,
    markAllRead,
    fetchPreferences,
    updatePreferences,
    connectRealtime,
    disconnectRealtime,
    startPolling,
    stopPolling,
  }
})
