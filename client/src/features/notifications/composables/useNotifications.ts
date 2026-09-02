/**
 * Notifications composable with lifecycle-managed background polling.
 */

import { onMounted, onUnmounted } from 'vue'
import { storeToRefs } from 'pinia'
import { useNotificationsStore } from '../stores/notifications.store'

export function useNotifications(autoPoll = false) {
  const store = useNotificationsStore()

  const {
    notifications,
    preferences,
    loading,
    isConnected,
    isPolling,
    pagination,
    unreadCount,
    unreadNotifications,
    readNotifications,
  } = storeToRefs(store)

  onMounted(() => {
    if (autoPoll) {
      store.startPolling()
    }
  })

  onUnmounted(() => {
    if (autoPoll) {
      store.stopPolling()
    }
  })

  return {
    // State
    notifications,
    preferences,
    loading,
    isConnected,
    isPolling,
    pagination,

    // Getters
    unreadCount,
    unreadNotifications,
    readNotifications,

    // Actions
    fetchNotifications: store.fetchNotifications,
    loadMore: store.loadMore,
    markAsRead: store.markAsRead,
    markRead: store.markRead,
    markAllAsRead: store.markAllAsRead,
    markAllRead: store.markAllRead,
    fetchPreferences: store.fetchPreferences,
    updatePreferences: store.updatePreferences,
    connectRealtime: store.connectRealtime,
    disconnectRealtime: store.disconnectRealtime,
    startPolling: store.startPolling,
    stopPolling: store.stopPolling,
  }
}
