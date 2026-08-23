<script setup lang="ts">
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { storeToRefs } from 'pinia'
import { useAuthStore } from '@/features/auth/stores/auth.store'
import { useNotificationsStore } from '@/features/notifications/stores/notifications.store'
import type { Notification } from '@/features/notifications/types/notification.types'
import { formatRelativeTime } from '@/utils/date'

const router = useRouter()
const authStore = useAuthStore()
const notificationsStore = useNotificationsStore()

const { notifications, unreadCount, loading } = storeToRefs(notificationsStore)

const isOpen = ref(false)

// Only fetch if authenticated and the store has no data yet.
// The store is shared across the whole app — this component never fires
// a duplicate request if another component (e.g. a notifications page)
// already populated it.
if (authStore.isAuthenticated && notifications.value.length === 0) {
  notificationsStore.fetchNotifications()
}

async function markAllRead(): Promise<void> {
  try {
    await notificationsStore.markAllAsRead()
  } catch (err) {
    console.error('Failed to mark notifications as read', err)
  }
}

async function handleNotificationClick(n: Notification): Promise<void> {
  if (!n.read_at) {
    try {
      await notificationsStore.markAsRead(n.id)
    } catch {
      // ignore
    }
  }
  isOpen.value = false

  if (n.data?.item_id) {
    router.push(`/items/${n.data.item_id}`)
  } else if (n.data?.claim_id) {
    router.push(authStore.isStudent ? '/student/my-claims' : '/staff/review-claims')
  }
}
</script>

<template>
  <div class="relative">
    <button
      type="button"
      class="relative p-2 rounded-xl text-slate-500 hover:text-slate-700 hover:bg-slate-100 transition-colors cursor-pointer"
      @click="isOpen = !isOpen"
    >
      <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
      </svg>
      <span
        v-if="unreadCount > 0"
        class="absolute top-1 right-1 flex h-4 w-4 items-center justify-center rounded-full bg-rose-500 text-[10px] font-bold text-white ring-2 ring-white"
      >
        {{ unreadCount > 9 ? '9+' : unreadCount }}
      </span>
    </button>

    <!-- Dropdown -->
    <div
      v-if="isOpen"
      class="absolute right-0 mt-2 w-80 rounded-2xl bg-white shadow-2xl ring-1 ring-black/5 p-2 z-50 border border-slate-100 divide-y divide-slate-100"
    >
      <div class="flex items-center justify-between p-2.5 pb-2">
        <span class="text-xs font-bold uppercase tracking-wider text-slate-500">Notifications</span>
        <button
          v-if="unreadCount > 0"
          type="button"
          class="text-xs text-[#0F5132] hover:underline font-medium"
          @click="markAllRead"
        >
          Mark all as read
        </button>
      </div>

      <div class="max-h-72 overflow-y-auto py-1 divide-y divide-slate-50">
        <div v-if="loading && notifications.length === 0" class="p-4 text-center text-xs text-slate-400">
          Loading notifications...
        </div>
        <div v-else-if="notifications.length === 0" class="p-6 text-center text-xs text-slate-400">
          No notifications yet
        </div>
        <div
          v-for="n in notifications.slice(0, 5)"
          :key="n.id"
          :class="[
            'p-3 rounded-xl transition-colors cursor-pointer text-xs flex items-start gap-2.5',
            n.read_at ? 'bg-white hover:bg-slate-50 opacity-75' : 'bg-[#0F5132]/5 hover:bg-[#0F5132]/10 font-medium',
          ]"
          @click="handleNotificationClick(n)"
        >
          <span class="mt-1 h-2 w-2 rounded-full shrink-0" :class="n.read_at ? 'bg-transparent' : 'bg-[#0F5132]'" />
          <div class="flex-1 min-w-0">
            <p class="text-slate-800 line-clamp-2">
              {{ n.data?.message || n.type || 'Notification alert' }}
            </p>
            <span class="text-[10px] text-slate-400 mt-1 block">
              {{ formatRelativeTime(n.created_at) }}
            </span>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
