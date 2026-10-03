import { describe, it, expect, beforeEach, vi } from 'vitest'
import { setActivePinia, createPinia } from 'pinia'
import { useNotificationsStore } from '../notifications.store'
import type { Notification } from '../../types/notification.types'

// Mock api methods
vi.mock('../../api/notifications.api', () => ({
  getNotifications: vi.fn().mockResolvedValue({
    data: [],
    meta: { current_page: 1, last_page: 1, total: 0 },
    unread_count: 0,
  }),
  getUnreadCount: vi.fn().mockResolvedValue(2),
  markNotificationAsRead: vi.fn().mockResolvedValue({}),
  markAllNotificationsAsRead: vi.fn().mockResolvedValue({}),
}))

describe('Notifications Store', () => {
  beforeEach(() => {
    setActivePinia(createPinia())
    vi.clearAllMocks()
  })

  it('computes unread and read notifications accurately', () => {
    const store = useNotificationsStore()

    const item1: Notification = {
      id: 'notif-1',
      user_id: 1,
      type: 'item_matched',
      data: { title: 'Match found', message: 'A match was found' },
      read_at: null,
      created_at: new Date().toISOString(),
    }

    const item2: Notification = {
      id: 'notif-2',
      user_id: 1,
      type: 'claim_approved',
      data: { title: 'Claim approved', message: 'Your claim was approved' },
      read_at: new Date().toISOString(),
      created_at: new Date(Date.now() - 3600000).toISOString(),
    }

    store.notifications = [item1, item2]

    expect(store.unreadNotifications.length).toBe(1)
    expect(store.unreadNotifications[0].id).toBe('notif-1')
    expect(store.readNotifications.length).toBe(1)
    expect(store.readNotifications[0].id).toBe('notif-2')
  })

  it('marks a notification as read and decrements unread count', async () => {
    const store = useNotificationsStore()

    store.notifications = [
      {
        id: 'notif-1',
        user_id: 1,
        type: 'item_matched',
        data: { title: 'Match found', message: 'A match was found' },
        read_at: null,
        created_at: new Date().toISOString(),
      },
    ]
    store.serverUnreadCount = 1

    await store.markAsRead('notif-1')

    expect(store.notifications[0].read_at).not.toBeNull()
    expect(store.unreadNotifications.length).toBe(0)
    expect(store.serverUnreadCount).toBe(0)
  })

  it('marks all notifications as read and resets unread count to 0', async () => {
    const store = useNotificationsStore()

    store.notifications = [
      {
        id: 'notif-1',
        user_id: 1,
        type: 'item_matched',
        data: { title: 'Match 1', message: 'Match 1' },
        read_at: null,
        created_at: new Date().toISOString(),
      },
      {
        id: 'notif-2',
        user_id: 1,
        type: 'item_matched',
        data: { title: 'Match 2', message: 'Match 2' },
        read_at: null,
        created_at: new Date().toISOString(),
      },
    ]
    store.serverUnreadCount = 2

    await store.markAllAsRead()

    expect(store.serverUnreadCount).toBe(0)
    expect(store.unreadNotifications.length).toBe(0)
    expect(store.notifications.every(n => n.read_at !== null)).toBe(true)
  })
})
