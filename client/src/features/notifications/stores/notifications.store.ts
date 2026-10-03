/**
 * Notifications store using Pinia.
 * Single source of truth for user notifications, unread counts, preferences, and real-time SSE stream.
 */

import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import type { Notification, NotificationListParams, NotificationPreference, UpdateNotificationPreferencesData } from '../types/notification.types'
import * as notificationsApi from '../api/notifications.api'
import { useUiStore } from '@/stores/ui.store'
import { t } from '@/i18n'
import { resolveNotificationMessage } from '../utils/notificationMessage'

export type ConnectionStatus = 'disconnected' | 'connecting' | 'connected' | 'reconnecting' | 'stopped'

export const useNotificationsStore = defineStore('notifications', () => {
  // State
  const notifications = ref<Notification[]>([])
  const preferences = ref<NotificationPreference | null>(null)
  const loading = ref(false)
  const error = ref<string | null>(null)
  const connectionStatus = ref<ConnectionStatus>('disconnected')
  const isPolling = ref(false)
  const serverUnreadCount = ref<number>(0)
  const pagination = ref<{ current_page: number; last_page: number; total: number } | null>(null)

  const isConnected = computed(() => connectionStatus.value === 'connected')

  let _eventSource: EventSource | null = null
  let _reconnectTimer: number | null = null
  let _reconnectAttempts = 0
  let _pollTimer: number | null = null
  let _currentUserId: number | null = null
  let _isLocalDevMode = false

  // Getters
  const unreadCount = computed(() => {
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
   * Fetch unread notification count directly from the server.
   */
  async function fetchUnreadCount(): Promise<number> {
    try {
      const count = await notificationsApi.getUnreadCount()
      serverUnreadCount.value = count
      return count
    } catch {
      return serverUnreadCount.value
    }
  }

  /**
   * Fetch notifications with fetch-once guard (bypassable via force) and inflight deduplication.
   */
  async function fetchNotifications(filters?: NotificationListParams, silent = false, force = false): Promise<void> {
    if (notifications.value.length > 0 && !filters && !force) return // already loaded
    if (_fetchingNotifications) return // request in flight
    _fetchingNotifications = true

    if (!silent && notifications.value.length === 0) {
      loading.value = true
    }

    try {
      error.value = null
      const res = await notificationsApi.getNotifications(filters, { per_page: 20 })
      notifications.value = res.data
      pagination.value = res.meta
      serverUnreadCount.value = res.unread_count ?? 0
    } catch (err: any) {
      error.value = err?.message || t('notifications.error')
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
        const msg = resolveNotificationMessage(newNotif)
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
    const target = notifications.value.find(n => String(n.id) === String(id))
    const wasUnread = target ? (!target.read_at && !target.is_read) : true

    await notificationsApi.markNotificationAsRead(id)

    if (target) {
      target.is_read = true
      target.read_at = new Date().toISOString()
    }
    if (wasUnread && serverUnreadCount.value > 0) {
      serverUnreadCount.value--
    }
  }

  const markRead = markAsRead // Alias

  async function markAllAsRead(): Promise<void> {
    await notificationsApi.markAllNotificationsAsRead()

    notifications.value.forEach(n => {
      n.is_read = true
      n.read_at = new Date().toISOString()
    })
    serverUnreadCount.value = 0
  }

  const markAllRead = markAllAsRead // Alias

  async function deleteNotification(id: string | number): Promise<void> {
    await notificationsApi.deleteNotification(id)

    const targetIndex = notifications.value.findIndex(n => String(n.id) === String(id))
    if (targetIndex !== -1) {
      const target = notifications.value[targetIndex]
      if (!target.read_at && !target.is_read && serverUnreadCount.value > 0) {
        serverUnreadCount.value--
      }
      notifications.value.splice(targetIndex, 1)
    }
  }

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

  /**
   * Idempotent user-scoped bootstrap.
   */
  async function initializeForUser(userId: number): Promise<void> {
    if (_currentUserId === userId && (connectionStatus.value === 'connected' || connectionStatus.value === 'connecting')) {
      return
    }
    _currentUserId = userId

    // Fetch initial notifications (also delivers server unread_count in a single request)
    if (notifications.value.length === 0) {
      await fetchNotifications(undefined, true).catch(() => {})
    }

    // Connect SSE stream
    connectRealtime()
  }

  function connectRealtime(): void {
    if (typeof window === 'undefined') return
    if (_eventSource && _eventSource.readyState !== EventSource.CLOSED) return

    const baseUrl = import.meta.env.VITE_API_URL || 'http://localhost:8000'
    const latestId = notifications.value.length > 0
      ? Math.max(...notifications.value.map(n => Number(n.id) || 0))
      : undefined

    const streamUrl = `${baseUrl}/api/v1/notifications/stream${latestId ? `?last_id=${latestId}` : ''}`

    connectionStatus.value = 'connecting'

    try {
      _eventSource = new EventSource(streamUrl, { withCredentials: true })

      _eventSource.onopen = () => {
        connectionStatus.value = 'connected'
        _reconnectAttempts = 0
      }

      _eventSource.addEventListener('mode', (event: MessageEvent) => {
        try {
          const modeData = JSON.parse(event.data)
          if (modeData.mode === 'local_dev') {
            _isLocalDevMode = true
            connectionStatus.value = 'connected'
          }
        } catch {}
      })

      _eventSource.addEventListener('notification', (event: MessageEvent) => {
        try {
          const newNotif = JSON.parse(event.data) as Notification
          handleIncomingNotification(newNotif)
        } catch (parseErr) {
          console.error('Failed to parse incoming notification:', parseErr)
        }
      })

      _eventSource.addEventListener('close', () => {
        // Server signaled clean end of one-shot burst (local single-worker dev mode)
        if (_isLocalDevMode && _eventSource) {
          _eventSource.close()
          _eventSource = null
          connectionStatus.value = 'connected'
        }
      })

      _eventSource.onerror = () => {
        if (_isLocalDevMode) {
          // In local dev mode, close cleanly without looping retry
          if (_eventSource) {
            _eventSource.close()
            _eventSource = null
          }
          connectionStatus.value = 'connected'
          return
        }

        // If browser is actively reconnecting in CONNECTING state, let native EventSource retry interval handle it
        if (_eventSource && _eventSource.readyState === EventSource.CONNECTING) {
          connectionStatus.value = 'reconnecting'
          return
        }

        // Permanent close / network error
        connectionStatus.value = 'disconnected'
        disconnectRealtime(false)

        // Exponential backoff reconnect: 5s, 10s, 15s... max 60s
        const delay = Math.min(5000 * Math.pow(1.5, _reconnectAttempts), 60000)
        _reconnectAttempts++

        _reconnectTimer = window.setTimeout(() => {
          connectRealtime()
        }, delay)
      }
    } catch (err) {
      console.warn('Real-time notification stream setup failed:', err)
      connectionStatus.value = 'disconnected'
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
    connectionStatus.value = 'disconnected'
  }

  const connectStream = connectRealtime
  const disconnectStream = disconnectRealtime

  // Reset all state on logout
  function reset(): void {
    _currentUserId = null
    _isLocalDevMode = false
    disconnectRealtime()
    stopPolling()
    notifications.value = []
    serverUnreadCount.value = 0
    pagination.value = null
    error.value = null
    loading.value = false
    preferences.value = null
    connectionStatus.value = 'disconnected'
  }

  // Backup reconciler polling (only runs when SSE stream is disconnected)
  function startPolling(intervalMs = 60000): void {
    if (_pollTimer !== null) return
    isPolling.value = true

    _pollTimer = window.setInterval(() => {
      if (connectionStatus.value !== 'connected' && typeof document !== 'undefined' && document.visibilityState === 'visible') {
        fetchNotifications(undefined, true, true).catch(() => {})
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
    error,
    isConnected,
    connectionStatus,
    isPolling,
    pagination,
    serverUnreadCount,

    // Getters
    unreadCount,
    unreadNotifications,
    readNotifications,

    // Actions
    initializeForUser,
    fetchNotifications,
    fetchUnreadCount,
    handleIncomingNotification,
    loadMore,
    markAsRead,
    markRead,
    markAllAsRead,
    markAllRead,
    deleteNotification,
    fetchPreferences,
    updatePreferences,
    connectRealtime,
    disconnectRealtime,
    connectStream,
    disconnectStream,
    reset,
    startPolling,
    stopPolling,
  }
})
