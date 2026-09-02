<script setup lang="ts">
import { ref, onUnmounted } from 'vue'
import { useRouter } from 'vue-router'
import { storeToRefs } from 'pinia'
import { useAuthStore } from '@/features/auth/stores/auth.store'
import { useNotificationsStore } from '@/features/notifications/stores/notifications.store'
import type { Notification } from '@/features/notifications/types/notification.types'
import { formatRelativeTime } from '@/utils/date'
import { t } from '@/i18n'
import { Bell, CheckCheck, Check, Radio } from 'lucide-vue-next'

const router = useRouter()
const authStore = useAuthStore()
const notificationsStore = useNotificationsStore()

const { notifications, unreadCount, loading, isConnected } = storeToRefs(notificationsStore)

const isOpen = ref(false)

function closeDropdown() {
  isOpen.value = false
}

onUnmounted(() => {
  isOpen.value = false
})

async function markAllRead(): Promise<void> {
  try {
    await notificationsStore.markAllAsRead()
  } catch (err) {
    console.error('Failed to mark all notifications as read', err)
  }
}

async function handleMarkSingleRead(e: Event, id: string | number): Promise<void> {
  e.stopPropagation()
  try {
    await notificationsStore.markAsRead(id)
  } catch (err) {
    console.error('Failed to mark notification as read', err)
  }
}

async function handleNotificationClick(n: Notification): Promise<void> {
  if (!n.read_at && !n.is_read) {
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
    router.push(authStore.can('REVIEW_CLAIMS') ? '/staff/review-claims' : '/student/my-claims')
  } else if (n.data?.confirmation_token) {
    router.push(`/confirm-return/${n.data.confirmation_token}`)
  }
}
</script>

<template>
  <div v-click-outside="closeDropdown" class="relative">
    <button
      type="button"
      class="relative p-2 rounded-xl text-slate-500 hover:text-slate-700 hover:bg-slate-100 dark:text-slate-400 dark:hover:text-slate-200 dark:hover:bg-slate-800 transition-colors cursor-pointer"
      :aria-label="t('notifications.title')"
      :aria-expanded="isOpen"
      @click="isOpen = !isOpen"
    >
      <Bell class="h-5 w-5" />
      
      <!-- Unread Badge -->
      <span
        v-if="unreadCount > 0"
        class="absolute top-1 right-1 flex h-4 min-w-4 px-1 items-center justify-center rounded-full bg-rose-500 text-[10px] font-bold text-white ring-2 ring-white dark:ring-slate-900 animate-pulse"
      >
        {{ unreadCount > 9 ? '9+' : unreadCount }}
      </span>

      <!-- Live Real-Time Stream Dot -->
      <span
        v-if="isConnected"
        class="absolute bottom-1 right-1 h-1.5 w-1.5 rounded-full bg-emerald-500 ring-1 ring-white dark:ring-slate-900"
        :title="t('home.hero.badge')"
      />
    </button>

    <!-- Dropdown -->
    <Transition name="fade">
      <div
        v-if="isOpen"
        class="absolute right-0 mt-2 w-84 sm:w-96 rounded-2xl bg-white dark:bg-[#111827] shadow-xl ring-1 ring-black/5 dark:ring-white/10 p-2 z-50 border border-slate-100 dark:border-slate-800 divide-y divide-slate-100 dark:divide-slate-800 animate-scale-in"
      >
        <!-- Header -->
        <div class="flex items-center justify-between p-2.5 pb-2">
          <div class="flex items-center gap-2">
            <span class="text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300">
              {{ t('notifications.title') }}
            </span>
            <span
              v-if="isConnected"
              class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded-full text-[9px] font-bold bg-[#E8F4EE] dark:bg-[#153C2D] text-[#0B5D3B] dark:text-[#75bd97] border border-[#0B5D3B]/20"
            >
              <Radio class="h-2.5 w-2.5 animate-pulse" />
              Live
            </span>
          </div>

          <button
            v-if="unreadCount > 0"
            type="button"
            class="text-xs text-[#0B5D3B] dark:text-[#75bd97] hover:underline font-medium inline-flex items-center gap-1 cursor-pointer"
            @click="markAllRead"
          >
            <CheckCheck class="h-3 w-3" />
            {{ t('notifications.markAllRead') }}
          </button>
        </div>

        <!-- Notification Items List -->
        <div class="max-h-80 overflow-y-auto py-1 divide-y divide-slate-50 dark:divide-slate-800/60">
          <div v-if="loading && notifications.length === 0" class="p-6 text-center text-xs text-slate-400">
            {{ t('common.loading') }}
          </div>
          <div v-else-if="notifications.length === 0" class="p-6 text-center text-xs text-slate-400">
            {{ t('notifications.noNotifications') }}
          </div>
          <div
            v-for="n in notifications.slice(0, 8)"
            :key="n.id"
            :class="[
              'group p-3 rounded-xl transition-all cursor-pointer text-xs flex items-start gap-2.5 relative',
              (n.read_at || n.is_read) ? 'bg-white dark:bg-[#111827] hover:bg-slate-50 dark:hover:bg-slate-800/60 opacity-80' : 'bg-[#E8F4EE]/60 dark:bg-[#153C2D]/30 hover:bg-[#E8F4EE] dark:hover:bg-[#153C2D]/50 font-medium',
            ]"
            @click="handleNotificationClick(n)"
          >
            <span
              class="mt-1 h-2 w-2 rounded-full shrink-0"
              :class="(n.read_at || n.is_read) ? 'bg-slate-300 dark:bg-slate-700' : 'bg-[#0B5D3B] dark:bg-[#75bd97] ring-2 ring-[#0B5D3B]/20'"
            />
            
            <div class="flex-1 min-w-0 pr-6">
              <p class="text-slate-800 dark:text-slate-200 line-clamp-2 leading-relaxed">
                {{ n.data?.message || n.type || t('notifications.title') }}
              </p>
              <span class="text-[10px] text-slate-400 dark:text-slate-500 mt-1 block">
                {{ formatRelativeTime(n.created_at) }}
              </span>
            </div>

            <!-- Single Mark Read Button -->
            <button
              v-if="!n.read_at && !n.is_read"
              type="button"
              class="opacity-0 group-hover:opacity-100 absolute right-2 top-3 p-1 rounded-md text-slate-400 hover:text-[#0B5D3B] dark:hover:text-[#75bd97] hover:bg-slate-100 dark:hover:bg-slate-800 transition-opacity"
              :title="t('notifications.markRead')"
              @click="handleMarkSingleRead($event, n.id)"
            >
              <Check class="h-3.5 w-3.5" />
            </button>
          </div>
        </div>
      </div>
    </Transition>
  </div>
</template>
