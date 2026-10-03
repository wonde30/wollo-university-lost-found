/**
 * Notifications API client for user notifications management.
 * Handles notification listing, marking as read, and preferences.
 */

import { apiClient } from '@/lib/http/client'
import { NOTIFICATIONS } from '@/lib/api/endpoints'
import type { PaginationParams } from '@/lib/api/pagination'
import type { ApiResponse } from '@/lib/api/response'
import type {
  Notification,
  NotificationListParams,
  NotificationPreference,
  UpdateNotificationPreferencesData,
} from '../types/notification.types'

// ==========================================
// Notifications Management
// ==========================================

export interface NotificationsResponse {
  data: Notification[]
  unread_count: number
  meta: {
    current_page: number
    last_page: number
    total: number
  }
}

/**
 * Get paginated list of notifications.
 * The server responds with { data, unread_count, meta }.
 */
export async function getNotifications(
  filters?: NotificationListParams,
  pagination?: PaginationParams
): Promise<NotificationsResponse> {
  const params = {
    ...filters,
    ...pagination,
  }

  const { data } = await apiClient.get<NotificationsResponse>(
    NOTIFICATIONS.INDEX,
    { params }
  )
  return data
}

/**
 * Get current unread notification count.
 */
export async function getUnreadCount(): Promise<number> {
  const { data } = await apiClient.get<{ unread_count: number }>(
    NOTIFICATIONS.UNREAD_COUNT
  )
  return data.unread_count ?? 0
}

/**
 * Mark a notification as read.
 */
export async function markNotificationAsRead(id: string | number): Promise<void> {
  await apiClient.post(NOTIFICATIONS.MARK_AS_READ(id))
}

/**
 * Mark all notifications as read.
 */
export async function markAllNotificationsAsRead(): Promise<void> {
  await apiClient.post(NOTIFICATIONS.MARK_ALL_AS_READ)
}

/**
 * Delete a notification.
 */
export async function deleteNotification(id: string | number): Promise<void> {
  await apiClient.delete(NOTIFICATIONS.DELETE(id))
}

// ==========================================
// Notification Preferences
// ==========================================

/**
 * Get notification preferences.
 */
export async function getNotificationPreferences(): Promise<NotificationPreference> {
  const { data } = await apiClient.get<ApiResponse<NotificationPreference>>(
    NOTIFICATIONS.PREFERENCES
  )
  return data.data!
}

/**
 * Update notification preferences.
 */
export async function updateNotificationPreferences(
  preferencesData: UpdateNotificationPreferencesData
): Promise<NotificationPreference> {
  const { data } = await apiClient.put<ApiResponse<NotificationPreference>>(
    NOTIFICATIONS.UPDATE_PREFERENCES,
    preferencesData
  )
  return data.data!
}
