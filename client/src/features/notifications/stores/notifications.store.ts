/**
 * Notifications store using Pinia.
 * Manages user notifications and preferences state with background polling.
 */

import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import type { Notification, NotificationListParams, NotificationPreference, UpdateNotificationPreferencesData } from '../types/notification.types'
import * as notificationsApi from '../api/notifications.api'

export const useNotificationsStore = defineStore('notifications', () => {
  // State
  const notifications = ref<Notification[]>([])
  const preferences = ref<NotificationPreference | null>(null)
  const loading = ref(false)
  const isPolling = ref(false)
  const serverUnreadCount = ref<number>(0)
  const pagination = ref<{ current_page: number; last_page: number; total: number } | null>(null)

  let _pollTimer: number | null = null

  // Getters
  const unreadCount = computed(() => {
    if (notifications.value.length > 0) {
      return notifications.value.filter(n => !n.read_at).length
    }
    return serverUnreadCount.value
  })

  const unreadNotifications = computed(() => {
    return notifications.value.filter(n => !n.read_at)
  })

  const readNotifications = computed(() => {
    return notifications.value.filter(n => n.read_at !== null)
  })

  // Actions
  async function fetchNotifications(filters?: NotificationListParams, silent = false): Promise<void> {
    if (!silent && notifications.value.length === 0) {
      loading.value = true
    }
    try {
      const res = await notificationsApi.getNotifications(filters, { per_page: 20 })
      notifications.value = res.data
      pagination.value = res.meta
      serverUnreadCount.value = res.unread_count ?? 0
    } finally {
      if (!silent) {
        loading.value = false
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
      notifications.value.push(...res.data)
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
      if (!n.read_at) {
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

  // Polling management (30 seconds, only when tab is visible)
  function startPolling(intervalMs = 30000): void {
    if (isPolling.value) return
    isPolling.value = true

    _pollTimer = window.setInterval(() => {
      if (typeof document !== 'undefined' && document.visibilityState === 'visible') {
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
    isPolling,
    pagination,
    serverUnreadCount,

    // Getters
    unreadCount,
    unreadNotifications,
    readNotifications,

    // Actions
    fetchNotifications,
    loadMore,
    markAsRead,
    markRead,
    markAllAsRead,
    markAllRead,
    fetchPreferences,
    updatePreferences,
    startPolling,
    stopPolling,
  }
})
